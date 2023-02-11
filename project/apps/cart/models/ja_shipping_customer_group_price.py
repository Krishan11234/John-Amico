
from django.db import models
from django.contrib.auth.models import Group
from django.utils import timezone


class JAShippingCustomerGroupPrice(models.Model):
    id = models.AutoField(primary_key=True)
    carrier = models.ForeignKey(
        'ShippingJohnamicoCarrierMethod',
        on_delete=models.CASCADE,
        db_index=True,
    )
    customer_group = models.ForeignKey(
        Group,
        on_delete=models.CASCADE,
    )
    price = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True, db_index=True)

    # created_at = models.DateTimeField(auto_now_add=True, blank=True)
    # updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        verbose_name_plural = 'John Amico Shipping: customer group pricing'

        index_together = ['customer_group', 'carrier']
        unique_together = ['customer_group', 'carrier', 'price']

    def __str__(self):
        return '{} has price for Group: {}'.format(self.carrier, self.customer_group)
