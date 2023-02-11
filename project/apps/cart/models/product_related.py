
from django.db import models
from django.utils import timezone

from .product import Product


class ProductRelated(models.Model):
    id = models.AutoField(primary_key=True)
    product = models.ForeignKey(
        Product,
        on_delete=models.CASCADE,
        db_index=True,
        related_name='product_related'
    )
    related = models.ForeignKey(
        'Product',
        on_delete=models.CASCADE,
        related_name='related'
    )
    order = models.IntegerField(blank=True, null=True, default=0)
    magento_id = models.IntegerField(blank=True, null=True, editable=False)

    # created_at = models.DateTimeField(auto_now_add=True, blank=True)
    # updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        verbose_name_plural = 'related products'

        index_together = ['related', 'product']

    def __str__(self):
        return '{} related with {}'.format(self.product, self.related)
