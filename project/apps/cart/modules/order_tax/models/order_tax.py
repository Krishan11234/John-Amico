from django.db import models
from django.utils import timezone
from ....utils import static, helper


class OrderTax(models.Model):
    id = models.AutoField(primary_key=True)
    order = models.ForeignKey('Order', on_delete=models.SET_NULL, db_index=True, blank=True, null=True)
    quote = models.ForeignKey('Quote', on_delete=models.SET_NULL, db_index=True, blank=True, null=True)
    code = models.CharField(max_length=100, blank=True, null=True, db_index=True)
    title = models.CharField(max_length=100, blank=True, null=True, db_index=True)
    percentage = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0,
                                     verbose_name='Rate Percentage')
    amount = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0,)
    priority = models.IntegerField(default=1)

    magento_id = models.IntegerField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    def __str__(self):
        return 'TAX: "{}" applied to Order #'.format(self.title, self.order_id)


class OrderTaxItem(models.Model):
    id = models.AutoField(primary_key=True)
    order_tax = models.ForeignKey('OrderTax', on_delete=models.SET_NULL, db_index=True, blank=True, null=True)
    quote_item = models.ForeignKey('QuoteItem', on_delete=models.SET_NULL, db_index=True, blank=True, null=True)
    order_item = models.ForeignKey('OrderItem', on_delete=models.SET_NULL, db_index=True, blank=True, null=True)
    tax_percent = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0)
    amount = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, )

    magento_id = models.IntegerField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    def __str__(self):
        return 'Item {} for Order Tax {}'.format(self.order_item.id, self.order_tax.order_id)
