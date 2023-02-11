from django.db import models
from django.urls import reverse

from ..utils import static


class Shipment(models.Model):
    id = models.AutoField(primary_key=True)

    order = models.ForeignKey('Order', blank=True, null=False, db_index=True, on_delete=models.CASCADE,
                              related_name='order_shipments',)
    customer = models.ForeignKey('CustomerExtra', on_delete=models.CASCADE, blank=True, null=True, default=None,)
    billing_address = models.ForeignKey('OrderAddress', on_delete=models.CASCADE, blank=True, null=True, default=None,
                                        related_name='shipment_billing_address')
    shipping_address = models.ForeignKey('OrderAddress', on_delete=models.CASCADE, blank=True, null=True, default=None,
                                         related_name='shipment_shipping_address')
    status = models.CharField(max_length=100, db_index=True, choices=static.SHIPMENT_STATUS_CHOICES, default='shipped')
    tracking_number = models.CharField(max_length=100, blank=True, null=True, default=None)

    total_weight = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0)
    total_qty = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0)
    increment_id = models.CharField(max_length=100, blank=False, null=True, unique=True)

    email_sent = models.BooleanField(blank=True, null=False, default=False)

    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        verbose_name_plural = 'Shipments'
        ordering = ['-created_at']

    def __str__(self):
        return 'Shipment #{} for Order #{}'.format(self.get_id(), self.order.get_order_id())

    def get_id(self):
        return self.increment_id if self.increment_id else self.id

    def get_view_url(self):
        return reverse('cart:account_order_shipment_list', self.order.id)

    def get_customer_name(self):
        name = self.order.customer_firstname
        if self.order.customer_lastname:
            name += " " + self.order.customer_lastname

        return name

    @staticmethod
    def fetch_new_increment_id():
        from ..models import StoreConfig
        global_config = StoreConfig.get_solo()

        last_id = global_config.shipment_increment_last_id if global_config.shipment_increment_last_id else 1
        pad_len = global_config.order_increment_pad_length if global_config.order_increment_pad_length else 8
        pad_chr = global_config.order_increment_pad_char if global_config.order_increment_pad_char else '0'
        prefix = global_config.order_increment_prefix if global_config.order_increment_prefix else '1'

        last_id = str(last_id).lstrip(str(prefix))
        new_id_number = int(last_id) + 1

        new_id = str(prefix) + str(new_id_number).rjust(int(pad_len), str(pad_chr))

        global_config.shipment_increment_last_id = new_id
        global_config.save()

        return new_id

    def get_address(self, address_type='billing'):
        return self.order.get_address(address_type)

    def get_billing_address(self):
        return self.get_address()

    def get_shipping_address(self):
        return self.get_address('shipping')

    def can_delete(self):
        return False

    def can_cancel(self):
        return self.status in ['shipped']

    def cancel(self):
        if self.can_cancel():
            self.status = 'cancelled'
            self.save()


