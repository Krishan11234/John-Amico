from django.contrib import admin
from django_summernote.admin import SummernoteModelAdmin, SummernoteInlineModelAdmin

from ..models import HomePageRow, HomePageColumn


class HomePageColumnInline(SummernoteInlineModelAdmin, admin.TabularInline):
    model = HomePageColumn
    fields = ('name', 'column_size', 'content', 'css_class', 'sort_order', 'is_active')
    extra = 0
    summernote_fields = ('content',)


class HomePageRowAdmin(SummernoteModelAdmin):
    list_display = ('id', 'name', 'sort_order',)
    list_display_links = ('id', 'name',)
    search_fields = ('name',)
    inlines = (HomePageColumnInline,)


admin.site.register(HomePageRow, HomePageRowAdmin)
