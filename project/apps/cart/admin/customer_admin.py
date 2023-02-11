from django.contrib import admin
from django.urls import reverse
from django.utils.html import format_html
from django.utils.safestring import mark_safe

from django.contrib.auth.models import User, AnonymousUser

from ..models import CustomerExtra, CustomerAddress


class CustomerAddressInline(admin.TabularInline):
    fields = ('address_type', 'firstname', 'lastname', 'email', 'address1', 'address2', 'city', 'state', 'zip',
              'company', 'telephone')
    readonly_fields = ('country',)
    model = CustomerAddress
    extra = 1


class CustomerAdmin(admin.ModelAdmin):
    list_display = ('customer', 'name', 'email', 'is_active')
    inlines = (CustomerAddressInline,)

    def name(self, obj):
        name = obj.customer.get_full_name()
        return name if name else 'Not Available'

    def email(self, obj):
        return obj.customer.email

    def is_active(self, obj):
        return obj.customer.is_active


admin.site.register(CustomerExtra, CustomerAdmin)
