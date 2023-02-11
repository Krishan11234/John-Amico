from django.contrib import admin
from django.urls import reverse
from django.utils.html import format_html
from django.utils.safestring import mark_safe

from ..models import Category, Product, CategoryProduct

from ..utils.static import ROOT_CATEGORY_ID


class ProductCategoryInline(admin.TabularInline):
    fields = ('product', 'order',)
    model = CategoryProduct
    extra = 1
    autocomplete_fields = ('product', )


class VisibleFilter(admin.SimpleListFilter):
    title = 'Visibility'

    parameter_name = 'visible'

    def lookups(self, request, model_admin):
        return (
            ('yes', 'Visible'),
            ('no', 'Hidden'),
        )

    def queryset(self, request, queryset):
        if self.value() == 'yes':
            return queryset.filter(hide=0)

        if self.value() == 'no':
            return queryset.filter(hide=1)


class ParentFilter(admin.SimpleListFilter):
    title = 'Parent'

    parameter_name = 'has_parent'

    def lookups(self, request, model_admin):
        return (
            ('no', 'Root categories'),
            ('yes', 'Child categories'),
        )

    def queryset(self, request, queryset):
        if self.value() == 'yes':
            return queryset.filter(parent__gt=ROOT_CATEGORY_ID)

        if self.value() == 'no':
            return queryset.filter(parent=ROOT_CATEGORY_ID)


class CategoryAdmin(admin.ModelAdmin):
    list_display = ('name', 'parent', 'child_categories', 'linked_products', 'order')
    list_display_links = ('name',)
    search_fields = ('name',)
    list_filter = (VisibleFilter, ParentFilter,)
    inlines = (ProductCategoryInline, )
    fieldsets = (
        (
            'Basic', {
                'fields': (
                    'name',
                    'parent',
                    'requires_customer_group_authentication',
                    'description',
                    'order',
                )
            }
        ),
        # (
        #     'Visibility', {
        #         'fields': (
        #             'listing_display_type',
        #         )
        #     }
        # ),
        # (
        #     'Image', {
        #         'fields': (
        #             'iconimage',
        #         )
        #     }
        # ),
        (
            'SEO', {
                'fields': (
                    'meta_title',
                    'meta_keywords',
                    'meta_description',
                    'url_key',
                    'url_path',
                )
            }
        ),
    )

    def get_queryset(self, request):
        query = super(CategoryAdmin, self).get_queryset(request)
        filtered_query = query.filter(id__gt=ROOT_CATEGORY_ID)
        return filtered_query

    def child_categories(self, obj):
        child_categories_count = Category.objects.filter(parent=obj.id).count()
        if child_categories_count:
            child_categories_href = '{}?parent={}'.format(
                reverse('admin:{}_category_changelist'.format(obj._meta.app_label)),
                obj.id
            )
            child_categories_link = '<a href="{}">{} {}</a>'.format(
                child_categories_href,
                child_categories_count,
                'child categories'
            )
            return format_html(
                '{}',
                mark_safe(child_categories_link)
            )
        else:
            return '-'

    def linked_products(self, obj):
        linked_products_count = Product.objects.filter(categories=obj.id).count()

        if linked_products_count:
            linked_products_href = '{}?categories={}'.format(
                reverse('admin:{}_product_changelist'.format(obj._meta.app_label)),
                obj.id
            )
            linked_products_link = '<a href="{}">{} {}</a>'.format(
                linked_products_href,
                linked_products_count,
                'products'
            )
            return format_html(
                '{}',
                mark_safe(linked_products_link)
            )
        else:
            return '-'

        return '{} products'.format(linked_products_count)


admin.site.register(Category, CategoryAdmin)
