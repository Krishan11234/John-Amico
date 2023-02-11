from django.contrib import admin
from solo.admin import SingletonModelAdmin

# from django_summernote.admin import SummernoteModelAdmin, SummernoteInlineModelAdmin

from ..models import StoreConfig


# class StoreConfigInline(admin.TabularInline):
#     model = StoreConfig
#     extra = 0
#     max_num = 1
#     can_delete = False


# class SiteConfigInline(admin.TabularInline):
#     fields = ('site_title', 'site_meta_title', 'site_meta_description',)
#     model = SiteConfig
#     extra = 0
#     max_num = 1
#     can_delete = False


class StoreConfigAdmin(SingletonModelAdmin):
    exclude = (
        'order_increment_prefix',
        'order_increment_pad_length',
        'order_increment_pad_char',
        'order_increment_last_id',

        'display_product_stock',
        'display_product_availability',
        'default_product_image'
    )
    inlines = []


admin.site.register(StoreConfig, StoreConfigAdmin)
