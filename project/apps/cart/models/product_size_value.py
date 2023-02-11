
from django.db import models
from django.contrib.auth.models import Group
from django.utils import timezone

from .product_size import ProductSize

from ..utils.static import PRODUCT_OPTION_PRICE_TYPE_CHOICES


class ProductSizeValue(models.Model):
    id = models.AutoField(primary_key=True)
    product_size = models.ForeignKey(
        ProductSize,
        on_delete=models.CASCADE,
    )
    customer_group = models.ForeignKey(
        Group,
        on_delete=models.CASCADE,
    )
    price = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True, db_index=True)
    price_type = models.CharField(max_length=10, db_index=True, choices=PRODUCT_OPTION_PRICE_TYPE_CHOICES, default='abs')

    magento_id = models.IntegerField(blank=True, null=True, editable=False)

    # created_at = models.DateTimeField(auto_now_add=True, blank=True)
    # updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        verbose_name_plural = 'product customer group pricing'
        index_together = ['customer_group', 'price']

    def __str__(self):
        return '{} has price for Group: {}'.format(self.product_size, self.customer_group)

    def get_price(self):
        if self.price_type == 'abs':
            return self.price
        elif self.price_type == 'fixed':
            return self.product_size.product.price + self.price
        elif self.price_type == 'percent':
            return self.product_size.product.price + (self.product_size.product.price * (self.price/100))
