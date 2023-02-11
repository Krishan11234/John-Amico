from django import forms
from django.contrib import admin
from ..models import ProductBinLocation
from ....utils import helper


class ProductBinLocationAdmin(admin.ModelAdmin):
    list_display = ('get_product_sku', 'bin_location')
    search_fields = ('bin_location',)
    autocomplete_fields = ('product',)

    def get_product_sku(self, obj):
        return obj.product.sku
    get_product_sku.admin_order_field = 'product'  # Allows column order sorting
    get_product_sku.short_description = 'Product SKU'  # Renames column head


admin.site.register(ProductBinLocation, ProductBinLocationAdmin)
