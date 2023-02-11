
from django.db import models
from django.utils import timezone

from .product import Product
from .category import Category


class CategoryProduct(models.Model):
    id = models.AutoField(primary_key=True)
    product = models.ForeignKey(
        Product,
        on_delete=models.CASCADE,
        db_index=True,
    )
    category = models.ForeignKey(
        Category,
        on_delete=models.CASCADE,
    )
    order = models.IntegerField(blank=True, null=True, default=0)

    # created_at = models.DateTimeField(auto_now_add=True, blank=True)
    # updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        verbose_name_plural = 'Product Categories'

        index_together = ['category', 'product']

    def __str__(self):
        return '{} linked with {}'.format(self.product, self.category)