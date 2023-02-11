import datetime

from django import forms
from django.core.exceptions import ValidationError
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.models import User

from ...models.customer_address import CustomerAddress
from ...models.customer_extras import CustomerExtra

from ...utils import sectioned_form, helper, form_widgets


class AddressEditForm(forms.ModelForm, sectioned_form.SectionedForm):
    use_required_attribute = True

    primary_billing = forms.BooleanField(required=False, label="Use as my default billing address.")
    primary_billing_already_set = forms.CharField(required=False, widget=form_widgets.PlainTextWidget, label='',
                                                  initial="It's a default billing address.")
    primary_shipping = forms.BooleanField(required=False, label="Use as my default shipping address.")
    primary_shipping_already_set = forms.CharField(required=False, widget=form_widgets.PlainTextWidget, label='',
                                                   initial="It's a default shipping address.")

    class Meta:
        model = CustomerAddress
        fields = '__all__'
        exclude = ['customer', 'customer_extra', 'address_type', 'shipping_as_billing', 'email', 'phone_ext',
                   'phone_type', 'mobile_no', 'comments', 'address_checksum']
        required_fields = ['firstname', 'lastname', 'address1', 'city', 'state', 'zip', 'country', 'telephone']
        labels = {
            'firstname': 'First Name',
            'lastname': 'Last Name',
            'address1': 'Street Address',
            'address2': 'Street Address 2',
            'zip': 'Post Code',
        }
        fieldsets = [
            ('contact_information',
                {
                    'fields': ['firstname', 'lastname', 'company', 'telephone'],
                    'legend': 'Contact Information'
                }),
            ('address',
                {
                    'fields': ['address1', 'address2', 'city', 'state', 'zip', 'country', 'primary_billing',
                               'primary_billing_already_set', 'primary_shipping', 'primary_shipping_already_set'],
                    'legend': 'Address'
                }),
        ]

    def order_fields(self, field_order=None):
        super_fields = super().order_fields(field_order)
        if super_fields is None:
            super_fields = self.fields
        for field_name, field in super_fields.items():
            if field_name in self.Meta.required_fields:
                super_fields[field_name].required = True
                super_fields[field_name].label_suffix = '<span class="required">*</span>'

        if isinstance(self.instance, CustomerAddress):
            if isinstance(self.instance.customer_extra, CustomerExtra):
                if self.instance.id == self.instance.customer_extra.default_billing_address_id:
                    del super_fields['primary_billing']
                else:
                    del super_fields['primary_billing_already_set']

                if self.instance.id == self.instance.customer_extra.default_shipping_address_id:
                    del super_fields['primary_shipping']
                else:
                    del super_fields['primary_shipping_already_set']
            else:
                del super_fields['primary_billing_already_set']
                del super_fields['primary_shipping_already_set']
        else:
            del super_fields['primary_billing']
            del super_fields['primary_shipping']
            del super_fields['primary_billing_already_set']
            del super_fields['primary_shipping_already_set']

        return super_fields

    def _html_output(self, *args, **kwargs):
        return sectioned_form.SectionedForm._html_output(self, *args, **kwargs)
