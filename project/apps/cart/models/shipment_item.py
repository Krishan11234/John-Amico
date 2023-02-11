from django.db import models

from ..utils.static import PRODUCT_TYPE_CHOICES


class ShipmentItem(models.Model):
    id = models.AutoField(primary_key=True)
    shipment = models.ForeignKey('Shipment', on_delete=models.CASCADE, db_index=True,)
    product = models.ForeignKey('Product', on_delete=models.DO_NOTHING, db_index=True, null=True)
    order_item = models.ForeignKey('OrderItem', on_delete=models.DO_NOTHING, blank=True, null=False)

    sku = models.CharField(max_length=100, blank=True, null=True)
    name = models.CharField(max_length=255, blank=True, null=True)
    price = models.DecimalField(max_digits=15, decimal_places=4, blank=True, default=0, db_index=True)

    weight = models.DecimalField(max_digits=15, decimal_places=2, blank=True, null=True, default=0)
    quantity = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0)

    option = models.IntegerField(blank=True, null=True)
    option_title = models.CharField(max_length=255, blank=True, null=True)
    option_sku = models.CharField(max_length=255, blank=True, null=True)

    is_free_product = models.BooleanField(blank=True, null=False, default=False)

    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        verbose_name_plural = 'Shipment Items'
        ordering = ['-created_at']

    def __str__(self):
        return 'Shipment Item #{} for Shipment: {}'.format(self.sku, self.shipment.get_id())

    def get_attributes(self):
        attrs = {
            'sku': {'title': 'SKU', 'value': self.sku + ("-"+self.option_sku if self.option_sku else '')},
            # 'option_sku': {'title': 'Option SKU', 'value': self.option_sku},
        }
        if self.option_title:
            attrs['option_title'] = {'title': 'Size', 'value': self.option_title}

        return attrs


