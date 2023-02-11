from django.contrib import admin

from ..models import MenuType, MenuCategory, StaticMenuItem


class MenuCategoryInline(admin.TabularInline):
    fields = ('category', 'label', 'menu_access_group', 'is_active', 'css_class', 'order',)
    model = MenuCategory
    extra = 1
    autocomplete_fields = ('category', 'menu_access_group',)


class StaticMenuItemInline(admin.TabularInline):
    fields = ('label', 'permalink', 'menu_access_group', 'is_active', 'css_class', 'order',)
    model = StaticMenuItem
    extra = 1
    autocomplete_fields = ('menu_access_group',)


class MenuTypeAdmin(admin.ModelAdmin):
    search_fields = ('name', 'machine_name',)
    inlines = (MenuCategoryInline, StaticMenuItemInline,)


admin.site.register(MenuType, MenuTypeAdmin)
