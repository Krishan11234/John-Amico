from django.contrib import admin
from django import forms
from django.dispatch import receiver
from .... import signals
from ....admin import ProductAdmin
from ..models import TaxClass


# Add the "Tax Class" field to the Product Edit Form in Admin
@receiver(signals.product_admin_fieldsets__extra_field, sender=ProductAdmin)
def add__product_tax_field__admin(*args, **kwargs):
    extra_fields = kwargs['extra_fields']

    if isinstance(extra_fields, (tuple, list)):
        extra_fields += ('tax_class',)

    return extra_fields


def clean_tax_class(self, *args, **kwargs):
    if hasattr(self, 'cleaned_data'):
        if 'tax_class' in self.cleaned_data:
            tax_class_data = self.cleaned_data["tax_class"]
            if isinstance(tax_class_data, TaxClass):
                return tax_class_data.id
            elif isinstance(tax_class_data, str) and tax_class_data.isnumeric():
                return int(tax_class_data)

    return None


@receiver(signals.product_admin_form, sender=ProductAdmin)
def alter__product_tax_form_field__admin(*args, **kwargs):
    form = kwargs['form']
    previous_handler = kwargs['previous_receiver']

    if previous_handler:
        previous_handler_instance, previous_handler_output = previous_handler

        if previous_handler_output:
            form = previous_handler_output

    if form and isinstance(form, object):
        if hasattr(form, 'base_fields'):
            if 'tax_class' in form.base_fields:
                form.base_fields['tax_class'].queryset = form.base_fields['tax_class'].queryset.filter(type='PRODUCT',
                                                                                                       is_active=True)
                form.clean_tax_class = clean_tax_class

    return form

# product_admin_inlines.connect(add__product_tax_inline__admin, sender=ProductAdmin)
