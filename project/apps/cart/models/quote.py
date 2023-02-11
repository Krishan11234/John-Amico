
from django.db import models
from django.db.models import Sum, Count, Q
from django.contrib.auth.models import User
from ..utils import helper, static


class Quote(models.Model):
    id = models.AutoField(primary_key=True)
    token_id = models.CharField(max_length=10, blank=True)

    customer = models.ForeignKey(
        User,
        on_delete=models.CASCADE,
        db_index=True,
        blank=True,
        null=True
    )
    tbl_member_id = models.IntegerField(blank=True, null=True)
    order_id = models.IntegerField(blank=True, null=True)

    customer_firstname = models.CharField(max_length=100, blank=True, null=True)
    customer_lastname = models.CharField(max_length=100, blank=True, null=True)
    customer_email = models.EmailField(max_length=255, blank=True, null=True)

    sub_total = models.DecimalField(max_digits=15, decimal_places=4, blank=True, default=0, db_index=True)

    coupon_code = models.CharField(max_length=100, blank=True, null=True)
    discount = models.DecimalField(max_digits=15, decimal_places=4, blank=True, default=0, db_index=True)

    shipping_method = models.CharField(max_length=100, blank=True, null=True)
    shipping_method_title = models.CharField(max_length=200, blank=True, null=True)
    shipping_price = models.DecimalField(max_digits=15, decimal_places=4, blank=True, default=0, db_index=True)

    currency_code = models.CharField(max_length=6, blank=True, null=True)
    total_quantity = models.DecimalField(max_digits=5, decimal_places=3, blank=True, default=0, db_index=True)

    comment = models.TextField(blank=True, null=True)

    remote_ip = models.CharField(max_length=100, blank=True, null=True)

    converted_to_order_at = models.DateTimeField(blank=True, null=True)

    is_closed = models.BooleanField(blank=True, null=False, default=False)

    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        verbose_name_plural = 'cart'

        ordering = ['-created_at']

    def __str__(self):
        return 'Cart Total #{} Customer: {}'.format(self.sub_total, self.customer_firstname)

    def get_address(self, address_type='billing'):
        from ..models import QuoteAddress
        if address_type in list(dict(static.ADDRESS_TYPE_CHOICES).keys()):
            oa_q = QuoteAddress.objects.filter(quote_id=self.id, address_type=address_type)
            if oa_q.exists():
                return oa_q.first()

        return False

    def get_billing_address(self):
        return self.get_address()

    def get_shipping_address(self):
        address = self.get_address('shipping')
        return address if address else self.get_billing_address()

    def calculate_subtotal(self):
        subtotal = 0.00
        for qi in self.quoteitem_set.all():
            subtotal += float(qi.price) * float(qi.quantity)

        return subtotal

    def save(self, force_insert=False, force_update=False, using=None, update_fields=None, external_use=False):

        self.token_id = self.token_id if self.token_id else helper.get_unique_string()[2:11]
        self.remote_ip = helper.get_client_ip()

        super().save(force_insert, force_update, using, update_fields)

    def delete(self, using=None, keep_parents=False):

        super().delete(using=None, keep_parents=False)


