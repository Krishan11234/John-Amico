from django import forms
from django.contrib import admin
from ..models import TaxCalculationRate, TaxCalculationRule, TaxClass, TaxCalculation
from ....utils import helper


class TaxRulesAdminForm(forms.ModelForm):
    product_tax_class = forms.MultipleChoiceField(choices=())
    tax_rates = forms.MultipleChoiceField(choices=())

    class Meta:
        model = TaxCalculationRule
        fields = ('code', 'priority', 'product_tax_class', 'tax_rates')
        required_fields = ['code', 'product_tax_class', 'tax_rates']

    def order_fields(self, field_order=None):
        super_fields = super().order_fields(field_order)
        if super_fields is None:
            super_fields = self.fields
        for field_name, field in super_fields.items():
            if field_name in self.Meta.required_fields:
                super_fields[field_name].required = True
            else:
                super_fields[field_name].required = False

        return super_fields

    def save(self, commit=True):
        tax_rule = super().save(commit)
        if hasattr(self, 'cleaned_data') and tax_rule:
            if 'product_tax_class' in self.cleaned_data and 'tax_rates' in self.cleaned_data:
                tax_calculations = {}
                tax_calculations_keys = []
                tax_calculations_q = TaxCalculation.objects.filter(tax_calculation_rule=tax_rule)
                for tc in tax_calculations_q:
                    tax_calculations[tc.hash_key] = tc
                    tax_calculations_keys.append(tc.hash_key)
                for ptc_id in self.cleaned_data['product_tax_class']:
                    ptc_id = int(ptc_id)
                    for tr_id in self.cleaned_data['tax_rates']:
                        tr_id = int(tr_id)

                        hash_key = helper.make_md5_string("{}{}{}".format(tr_id, ptc_id, tax_rule.id))
                        if hash_key in tax_calculations_keys:
                            tax_calculations_keys.remove(hash_key)
                        else:
                            TaxCalculation.objects.create(
                                tax_calculation_rate_id=tr_id,
                                tax_class_id=ptc_id,
                                tax_calculation_rule=tax_rule,
                                hash_key=hash_key
                            )

                if tax_calculations_keys:
                    for tck in tax_calculations_keys:
                        tax_calculations[tck].delete()

        return tax_rule


class TaxRulesAdmin(admin.ModelAdmin):
    search_fields = ('code',)
    form = TaxRulesAdminForm

    """
    Reason behind not defining the field choices directly into the Form:
    If the choices are initialised through the form itself, it gets cached, and 
    if the choices are updated, it doesn't get reflected in the form.   
    """
    def get_form(self, request, obj=None, change=False, **kwargs):
        form = super().get_form(request, obj, change, **kwargs)
        if form:
            product_tax_class_choices = TaxClass.objects.filter(is_active=True, type='PRODUCT').values_list('id', 'name')
            tax_rate_choices = TaxCalculationRate.objects.values_list('id', 'code')

            product_classes_initial = ()
            tax_rates_initial = ()

            if obj:
                tax_calculations_q = TaxCalculation.objects.filter(tax_calculation_rule=obj)
                if tax_calculations_q.exists():
                    product_classes_initial = tuple(tax_calculations_q.values_list('tax_class', flat=True))
                    tax_rates_initial = tuple(tax_calculations_q.values_list('tax_calculation_rate', flat=True))

            if 'product_tax_class' in form.base_fields:
                form.base_fields['product_tax_class'].choices = product_tax_class_choices
                form.base_fields['product_tax_class'].initial = product_classes_initial
            if 'tax_rates' in form.base_fields:
                form.base_fields['tax_rates'].choices = tax_rate_choices
                form.base_fields['tax_rates'].initial = tax_rates_initial

        return form


admin.site.register(TaxCalculationRule, TaxRulesAdmin)


class TaxRatesAdmin(admin.ModelAdmin):
    fieldsets = (
        (
            'General', {
                'fields': (
                    ('code',),
                    ('country_code', 'region_code'),
                    ('zip_is_range',),
                    ('postcode',),
                    ('zip_from', 'zip_to',),
                    ('percentage',),
                ),
            }
        ),
    )
    exclude = ['magento_id', 'region_id']
    search_fields = ('code', )

    class Media:
        js = ('modules/admin/js/order_tax/order_tax_rate.js',)


admin.site.register(TaxCalculationRate, TaxRatesAdmin)
