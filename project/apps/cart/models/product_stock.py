
from django.db import models
from django.utils import timezone

from .product import Product


class ProductStock(models.Model):
    id = models.AutoField(primary_key=True)
    product = models.ForeignKey(
        Product,
        on_delete=models.CASCADE,
        db_index=True,
        related_name='product_stock'
    )
    quantity = models.DecimalField(max_digits=12, decimal_places=4, blank=True, null=True, db_index=True, default=0)
    min_sale_qty = models.DecimalField(max_digits=12, decimal_places=4, blank=True, null=True, db_index=True, default=1)
    max_sale_qty = models.DecimalField(max_digits=12, decimal_places=4, blank=True, null=True, db_index=True)
    notify_low_stock_qty = models.DecimalField(max_digits=12, decimal_places=4, blank=True, null=True, default=1)

    # is_qty_decimal = models.BooleanField(blank=True, null=True, default=False)
    is_in_stock = models.BooleanField(blank=True, null=True, default=True)
    notify_low_stock = models.BooleanField(blank=True, null=True, default=True)

    magento_id = models.IntegerField(blank=True, null=True, editable=False)

    class Meta:
        verbose_name_plural = 'product stock'
        index_together = ['product', 'quantity']

    def __str__(self):
        return '{} has {} stock'.format(self.product, self.quantity)

    def get_quantity(self):
        quantity = self.quantity if self.is_in_stock else 0
        quantity = quantity if quantity > self.get_min_sale_qty() else 0

        return quantity

    def get_max_sale_qty(self):
        from ..models import StoreConfig
        global_config = StoreConfig.get_solo()
        return global_config.get_max_per_product_order_qty() if self.max_sale_qty is None or \
            self.max_sale_qty < 0 else self.max_sale_qty

    def get_min_sale_qty(self):
        from ..models import StoreConfig
        global_config = StoreConfig.get_solo()
        return global_config.get_min_per_product_order_qty() if self.min_sale_qty is None or \
            self.min_sale_qty < 0 else self.min_sale_qty

    def save(self, force_insert=False, force_update=False, using=None, update_fields=None):
        # @TODO: Notify Low Stock

        if self.quantity < self.get_min_sale_qty():
            self.is_in_stock = False
        else:
            self.is_in_stock = True

        super().save(force_insert, force_update, using, update_fields)
