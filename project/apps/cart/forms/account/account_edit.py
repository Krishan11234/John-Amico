import datetime

from django import forms
from django.core.exceptions import ValidationError
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.models import User

from ...utils import sectioned_form, static

from .address_edit import AddressEditForm
from .user_edit import UserEditForm


class AccountEditForm(AddressEditForm):
    change_email = forms.BooleanField(required=False)
    change_password = forms.BooleanField(required=False)
    gender = forms.ChoiceField(choices=static.GENDER_CHOICES, required=False)
    current_password = forms.CharField(widget=forms.PasswordInput())
    new_password = forms.CharField(widget=forms.PasswordInput())
    confirm_new_password = forms.CharField(widget=forms.PasswordInput())

    class Meta:
        model = User
        fields = ['first_name', 'last_name', 'gender', 'email', 'change_email', 'change_password', 'current_password',
                  'new_password', 'confirm_new_password', ]
        # exclude = ['las_login', 'is_superuser', 'username', 'is_staff', 'is_active', 'date_joined']
        required_fields = ['first_name', 'last_name', 'email']
        fieldsets = [
            ('account_information',
             {
                 'fields': ['first_name', 'last_name', 'gender', 'change_email', 'change_password'],
                 'legend': 'Account Information'
             }),
            ('change_email',
             {
                 'fields': ['email', 'current_password'],
                 'legend': 'Change Email'
             }),
            ('change_password',
             {
                 'fields': ['current_password', 'new_password', 'confirm_new_password'],
                 'legend': 'Change Password'
             }),
            ('change_email_password',
             {
                 'fields': ['email', 'current_password', 'new_password', 'confirm_new_password'],
                 'legend': 'Change Email & Password'
             }),
        ]

    def full_clean(self):
        data = self.data
        if data:
            if 'change_email' not in data:
                self.fields['email'].required = False

            if 'change_password' not in data:
                self.fields['new_password'].required = False
                self.fields['confirm_new_password'].required = False

            if 'change_email' not in data and 'change_password' not in data:
                self.fields['current_password'].required = False

        super().full_clean()
