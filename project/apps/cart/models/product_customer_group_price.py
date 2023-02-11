
from django.db import models
from django.contrib.auth.models import Group
from django.utils import timezone

from .product import Product
from .category import Category


class ProductCustomerGroupPrice(models.Model):
    id = models.AutoField(primary_key=True)
    product = models.ForeignKey(
        Product,
        on_delete=models.CASCADE,
        db_index=True,
    )
    customer_group = models.ForeignKey(
        Group,
        on_delete=models.CASCADE,
    )
    price = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True, db_index=True)
    is_percent = models.BooleanField(default=False, null=False, blank=True)

    magento_id = models.IntegerField(blank=True, null=True, editable=False)

    # created_at = models.DateTimeField(auto_now_add=True, blank=True)
    # updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        verbose_name_plural = 'product customer group pricing'

        index_together = ['customer_group', 'product']
        unique_together = ['customer_group', 'product', 'price']

    def __str__(self):
        return '{} has price for Group: {}'.format(self.product, self.customer_group)
