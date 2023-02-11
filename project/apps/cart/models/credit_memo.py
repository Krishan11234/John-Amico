from django.db import models
from django.urls import reverse

from ..utils import static


class CreditMemo(models.Model):
    id = models.AutoField(primary_key=True)

    credit_memo = models.ForeignKey('self', on_delete=models.CASCADE, blank=True, null=True, default=None, related_name='self')

    order = models.ForeignKey(
        'Order',
        blank=True,
        null=False,
        db_index=True,
        on_delete=models.CASCADE,
        related_name='order_credit_memos',
    )

    discount_amount = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    discount_description = models.TextField(blank=True, null=True)

    shipping_amount = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    shipping_tax_amount = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)

    # status = models.CharField(max_length=100, db_index=True, choices=static.INVOICE_STATUS_CHOICES, default='paid')

    subtotal = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    tax_amount = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    grand_total = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)

    total_qty = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    increment_id = models.CharField(max_length=100, blank=False, null=True, unique=True)

    # transaction_id = models.CharField(max_length=100, blank=False, null=True, unique=False)

    email_sent = models.BooleanField(blank=True, null=False, default=False)
    remote_ip = models.CharField(max_length=100, blank=True, null=True)

    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        verbose_name_plural = 'Credit Memos'
        ordering = ['-created_at']

    def __str__(self):
        return 'Credit Memo #{} with Total of: {} for Order #{}'.format(self.get_credit_memo_id(), self.grand_total,
                                                                        self.order.get_order_id())

    def get_credit_memo_id(self):
        return self.increment_id if self.increment_id else self.id

    def get_view_url(self):
        self.creditmemoitem_set
        return reverse('cart:account_order_refund_list', self.order.id)

    def get_customer_name(self):
        name = self.order.customer_firstname
        if self.order.customer_lastname:
            name += " " + self.order.customer_lastname

        return name

    @staticmethod
    def fetch_new_increment_id():
        from ..models import StoreConfig
        global_config = StoreConfig.get_solo()

        last_id = global_config.credit_memo_increment_last_id if global_config.credit_memo_increment_last_id else 1
        pad_len = global_config.order_increment_pad_length if global_config.order_increment_pad_length else 8
        pad_chr = global_config.order_increment_pad_char if global_config.order_increment_pad_char else '0'
        prefix = global_config.order_increment_prefix if global_config.order_increment_prefix else '1'

        last_id = str(last_id).lstrip(str(prefix))
        new_id_number = int(last_id) + 1

        new_id = str(prefix) + str(new_id_number).rjust(int(pad_len), str(pad_chr))

        global_config.credit_memo_increment_last_id = new_id
        global_config.save()

        return new_id

    def get_address(self, address_type='billing'):
        from ..models import OrderAddress
        if address_type in list(dict(static.ADDRESS_TYPE_CHOICES).keys()):
            oa_q = OrderAddress.objects.filter(order=self.order, address_type=address_type)
            if oa_q.exists():
                return oa_q.first()

        return False

    def get_billing_address(self):
        return self.get_address()

    def get_shipping_address(self):
        return self.get_address('shipping')

    def can_delete(self):
        return False

    def save(self, force_insert=False, force_update=False, using=None, update_fields=None, external_use=False):
        from ..utils import helper
        if not self.id:
            self.remote_ip = helper.get_client_ip()

        # self.credit_memo = self

        super().save(force_insert, force_update, using, update_fields)

    def delete(self, using=None, keep_parents=False):

        super().delete(using=None, keep_parents=False)


