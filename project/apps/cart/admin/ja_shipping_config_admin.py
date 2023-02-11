from django.contrib import admin
from solo.admin import SingletonModelAdmin

# from django_summernote.admin import SummernoteModelAdmin, SummernoteInlineModelAdmin

from ..models import ShippingJohnamicoCarrierMethod, JAShippingCustomerGroupPrice


class JAShippingCustomerGroupPricingInline(admin.TabularInline):
    fields = ('customer_group', 'price',)
    model = JAShippingCustomerGroupPrice
    extra = 1


# class SiteConfigInline(admin.TabularInline):
#     fields = ('site_title', 'site_meta_title', 'site_meta_description',)
#     model = SiteConfig
#     extra = 0
#     max_num = 1
#     can_delete = False


class JAShippingConfigAdmin(SingletonModelAdmin):
    inlines = (JAShippingCustomerGroupPricingInline, )


admin.site.register(ShippingJohnamicoCarrierMethod, JAShippingConfigAdmin)
