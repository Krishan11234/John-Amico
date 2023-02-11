from django.contrib import admin

from django_summernote.admin import SummernoteModelAdmin

from ..models import HomePageColumn


class HomePageColumnAdmin(SummernoteModelAdmin):
    list_display = ('id', 'row', 'name', 'sort_order')
    list_display_links = ('id', 'name',)
    search_fields = ('name', 'content',)
    summernote_fields = ('content',)


admin.site.register(HomePageColumn, HomePageColumnAdmin)
