from django.db import models
from django.db.models import Sum, Count, Q
from django.contrib.auth.models import User

from ..utils.static import PRODUCT_TYPE_CHOICES


class QuoteItem(models.Model):
    id = models.AutoField(primary_key=True)
    quote = models.ForeignKey(
        'Quote',
        on_delete=models.CASCADE,
        db_index=True,
    )
    product = models.ForeignKey(
        'Product',
        on_delete=models.CASCADE,
        db_index=True,
        related_name='cart_product',
        blank=True,
        null=True
    )
    product_type = models.CharField(max_length=255, db_index=True, choices=PRODUCT_TYPE_CHOICES, default='simple',
                                    blank=True, null=True)
    sku = models.CharField(max_length=100, blank=True, null=True)
    name = models.CharField(max_length=255, blank=True, null=True)
    description = models.TextField(blank=True, null=True)
    price = models.DecimalField(max_digits=15, decimal_places=4, blank=True, default=0, db_index=True)
    quantity = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=1, db_index=True)
    option = models.ForeignKey('ProductSize', on_delete=models.CASCADE, blank=True, null=True)
    option_title = models.CharField(max_length=255, blank=True, null=True)
    option_sku = models.CharField(max_length=255, blank=True, null=True)
    currency = models.CharField(max_length=6, blank=True, null=True)
    row_total = models.DecimalField(max_digits=15, decimal_places=4, blank=True, default=0, db_index=True)

    is_free_product = models.BooleanField(blank=True, null=False, default=False)

    remote_ip = models.CharField(max_length=100, blank=True, null=True)

    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        verbose_name_plural = 'cart'

        ordering = ['-created_at']

    def __str__(self):
        return 'Quite Item #{} for Quote: {}'.format(self.id, self.quote.id)

    def calculate_row_total(self):
        return float(self.price) * float(self.quantity)

    def save(self, force_insert=False, force_update=False, using=None, update_fields=None, external_use=False):

        super().save(force_insert, force_update, using, update_fields)

    def delete(self, using=None, keep_parents=False):

        super().delete(using=None, keep_parents=False)


