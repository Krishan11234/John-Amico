from django.contrib import admin
from solo.admin import SingletonModelAdmin

# from django_summernote.admin import SummernoteModelAdmin, SummernoteInlineModelAdmin

from ..models import SiteConfig, StoreConfig


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


class SiteConfigAdmin(SingletonModelAdmin):
    inlines = []


admin.site.register(SiteConfig, SiteConfigAdmin)
