from django.db import models
from django.utils import timezone
from ....utils import static, helper


class TaxClass(models.Model):
    id = models.AutoField(primary_key=True)
    name = models.CharField(max_length=50, blank=True, null=True, db_index=True)
    type = models.CharField(max_length=50, blank=True, null=True, db_index=True)
    is_active = models.BooleanField(blank=True, null=False, default=True)

    tax_calculation_rule = models.ManyToManyField("TaxCalculationRule", through='TaxCalculation', through_fields=('tax_class', 'tax_calculation_rule'))

    magento_id = models.IntegerField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    def __str__(self):
        return '{}'.format(self.name)


class TaxCalculationRate(models.Model):
    id = models.AutoField(primary_key=True)
    country_code = models.CharField(max_length=5, blank=True, null=False, default='US', choices=static.COUNTRIES, verbose_name='Country')
    region_code = models.CharField(max_length=5, blank=True, null=True, choices=static.US_STATES, verbose_name='Region')
    region_id = models.IntegerField(blank=True, null=True)
    postcode = models.CharField(max_length=15, blank=True, null=True, help_text="' * ' - matches any. 'xyz*' - matches any that begins on 'xyz' and not longer than 10.")
    code = models.CharField(max_length=50, blank=False, null=True, verbose_name='Name')
    percentage = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, verbose_name='Rate Percentage')
    zip_is_range = models.BooleanField(null=False, default=False, blank=True)
    zip_from = models.CharField(max_length=50, null=True, blank=True)
    zip_to = models.CharField(max_length=50, null=True, blank=True)

    tax_calculation_rule = models.ManyToManyField("TaxCalculationRule", through='TaxCalculation',
                                                  through_fields=('tax_calculation_rate', 'tax_calculation_rule'))

    magento_id = models.IntegerField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    def __str__(self):
        return '{} ({})'.format(self.code, self.percentage)


class TaxCalculationRule(models.Model):
    id = models.AutoField(primary_key=True)
    code = models.CharField(max_length=255, blank=True, null=True, verbose_name='Name')
    priority = models.IntegerField(default=1)

    tax_class = models.ManyToManyField(TaxClass, through='TaxCalculation', through_fields=('tax_calculation_rule', 'tax_class'))
    tax_calculation_rate = models.ManyToManyField(TaxCalculationRate, through='TaxCalculation', through_fields=('tax_calculation_rule', 'tax_calculation_rate'))

    magento_id = models.IntegerField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    def __str__(self):
        return '{} ({})'.format(self.code, self.id)


class TaxCalculation(models.Model):
    id = models.AutoField(primary_key=True)
    tax_calculation_rate = models.ForeignKey('TaxCalculationRate', on_delete=models.SET_NULL, db_index=True,
                                             blank=True, null=True)
    tax_calculation_rule = models.ForeignKey('TaxCalculationRule', on_delete=models.SET_NULL, db_index=True,
                                             blank=True, null=True)
    tax_class = models.ForeignKey('TaxClass', on_delete=models.SET_NULL, db_index=True, blank=True, null=True)
    hash_key = models.CharField(max_length=50, blank=True, null=False, default='')
    is_active = models.BooleanField(blank=True, null=False, default=True)

    magento_id = models.IntegerField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        unique_together = ['tax_calculation_rate', 'tax_calculation_rule', 'tax_class']

    def __str__(self):
        return '{} ({})'.format(self.tax_calculation_rate.code, self.id)

    def save(self, force_insert=False, force_update=False, using=None, update_fields=None):
        if not self.hash_key:
            self.hash_key = helper.make_md5_string("{}{}{}".format(self.tax_calculation_rate_id, self.tax_class_id, self.tax_calculation_rule_id))

        super().save(force_insert, force_update, using, update_fields)


class AlterModelFieldsForTAX(object):
    fake_init = False

    def run(self):
        self.alter_product_model()
        self.alter_quote_item_model()

    def alter_product_model(self):
        self.fake_init = True

        from ....models import Product

        local_fields = Product._meta.local_fields

        def add__tax_class__field(fields):
            tax_class = models.ForeignKey(TaxClass, on_delete=models.CASCADE, blank=True, null=True, default=2)
            tax_class = self.model_field_properties(tax_class, 'tax_class', Product, is_foreign=True)

            fields.append(tax_class)
            return fields

        local_fields = add__tax_class__field(local_fields)
        Product._meta.local_fields = local_fields

    def alter_quote_item_model(self):
        self.fake_init = True

        from ....models import QuoteItem

        local_fields = QuoteItem._meta.local_fields

        def add__tax_percent__field(fields):
            tax_percent = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0, db_index=True)
            tax_percent = self.model_field_properties(tax_percent, 'tax_percent', QuoteItem)

            fields.append(tax_percent)
            return fields

        def add__tax_amount__field(fields):
            tax_amount = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0, db_index=True)
            tax_amount = self.model_field_properties(tax_amount, 'tax_amount', QuoteItem)

            fields.append(tax_amount)
            return fields

        local_fields = add__tax_percent__field(local_fields)
        local_fields = add__tax_amount__field(local_fields)
        QuoteItem._meta.local_fields = local_fields

    def model_field_properties(self, field, name, model, is_foreign=False):
        field.attname = name
        field.name = name
        field.column = name if not is_foreign else '{}_id'.format(name)
        field.concrete = True
        field.model = model

        # As this is a Foreign Key field, we need to add "opts" to this field definition
        if is_foreign:
            field.opts = model._meta

        return field


AlterModelFieldsForTAX().run()
