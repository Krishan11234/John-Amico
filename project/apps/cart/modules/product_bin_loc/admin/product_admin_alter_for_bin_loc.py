from django.contrib import admin
from django import forms
from django.dispatch import receiver
from .... import signals
from ....admin import ProductAdmin
from ..models import ProductBinLocation


# Add the "Tax Class" field to the Product Edit Form in Admin
@receiver(signals.product_admin_fieldsets__extra_field, sender=ProductAdmin)
def add__product_tax_field__admin(*args, **kwargs):
    extra_fields = kwargs['extra_fields']
    admin_obj = kwargs['admin_obj']

    if hasattr(admin_obj, 'called_fieldsets'):
        if admin_obj.called_fieldsets > 1 :
            if isinstance(extra_fields, (tuple, list)):
                extra_fields += ('bin_location',)

    return extra_fields


@receiver(signals.product_admin_form_save, sender=ProductAdmin)
def save__product_bin_location__form_admin(*args, **kwargs):
    form = kwargs['form']
    saved_form = kwargs['saved_form']

    if hasattr(form, 'cleaned_data') and form.cleaned_data :
        if 'bin_location' in form.cleaned_data:
            form_bin_location = str(form.cleaned_data['bin_location']).strip()

            bin_exists_q = ProductBinLocation.objects.filter(product=saved_form)
            if bin_exists_q.exists():
                product_bin = bin_exists_q.get()
                if product_bin.bin_location != form_bin_location:
                    product_bin.bin_location = form_bin_location
                    product_bin.save()
            else:
                ProductBinLocation.objects.create(**{
                    'product': saved_form,
                    'bin_location': form_bin_location
                })


@receiver(signals.product_admin_form, sender=ProductAdmin)
def alter__product_tax_form_field__admin(*args, **kwargs):
    form = kwargs['form']
    product_object = kwargs['product_object']
    previous_handler = kwargs['previous_receiver']

    if previous_handler:
        previous_handler_instance, previous_handler_output = previous_handler

        if previous_handler_output:
            form = previous_handler_output

    if form and isinstance(form, object):
        if hasattr(form, 'base_fields'):
            if 'bin_location' not in form.base_fields.keys():
                initial = '2'
                if product_object:
                    bin_exists_q = ProductBinLocation.objects.filter(product=product_object)
                    if bin_exists_q.exists():
                        product_bin = bin_exists_q.get()
                        initial = product_bin.bin_location

                form.base_fields['bin_location'] = forms.CharField(initial=initial, required=False)

            # if 'bin_location' in form.base_fields.keys():
            #     form.clean_bin_location = clean_bin_location

    return form

# product_admin_inlines.connect(add__product_tax_inline__admin, sender=ProductAdmin)
