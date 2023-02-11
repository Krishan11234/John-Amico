from django.db import models
from django.db.models import Sum, Count, Q
from django.contrib.auth.models import User

from ..utils import static


class PaymentTransaction(models.Model):
    id = models.AutoField(primary_key=True)
    order = models.ForeignKey('Order', on_delete=models.CASCADE, db_index=True, blank=True, null=True,
                              related_name='order_payment_transaction')
    payment = models.ForeignKey('OrderPayment', on_delete=models.CASCADE, db_index=True, blank=True, null=True,)
    parent = models.ForeignKey('self', related_name='children', blank=True, null=True, db_index=True,
                               on_delete=models.CASCADE)
    amount_effected = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    amount_refunded = models.DecimalField(max_digits=12, decimal_places=4, blank=True, null=True)

    transaction_id = models.CharField(max_length=250)
    transaction_type = models.CharField(max_length=100, blank=True, null=True)
    transaction_status = models.CharField(max_length=100, blank=True, null=True)
    is_failed = models.BooleanField(blank=True, null=False, default=False)
    is_closed = models.BooleanField(null=False, default=False)
    is_payment_info_saved = models.BooleanField(null=False, default=False)
    additional_information = models.TextField(blank=True, null=True)

    created_at = models.DateTimeField(auto_now_add=True, blank=True)

    class Meta:
        verbose_name_plural = 'Payment Transactions'

        ordering = ['-created_at']

    def __str__(self):
        return 'Transaction for Order #{}'.format(self.order.id)

    def save(self, force_insert=False, force_update=False, using=None, update_fields=None, external_use=False):
        super().save(force_insert, force_update, using, update_fields)

    def delete(self, using=None, keep_parents=False):

        super().delete(using=None, keep_parents=False)


