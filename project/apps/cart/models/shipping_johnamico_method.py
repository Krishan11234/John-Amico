from django.db import models
from solo.models import SingletonModel
from django.contrib.auth.models import Group

from ..utils.helper import get_current_customer_group


class ShippingJohnamicoCarrierMethod(SingletonModel):
    # site_config = models.ForeignKey(SiteConfig, blank=True, null=True, on_delete=models.CASCADE)

    title = models.CharField(blank=False, null=False, max_length=255, verbose_name='Shipping Carrier Title',
                             default='John Amico Career')
    price = models.DecimalField(max_digits=19, decimal_places=4, blank=False, null=False, db_index=True,
                                verbose_name='Default Price', default='7.95')
    is_enabled = models.BooleanField(blank=True, null=False, default=True)
    customer_group_price = models.ManyToManyField(Group, through='JAShippingCustomerGroupPrice',
                                                  through_fields=('carrier', 'customer_group'))

    def __str__(self):
        return "John Amico Carrier"

    def get_price(self):
        customer_group = get_current_customer_group()
        if customer_group:
            from .ja_shipping_customer_group_price import JAShippingCustomerGroupPrice
            customer_price = JAShippingCustomerGroupPrice.objects.filter(customer_group=customer_group, carrier=self)
            if customer_price.exists():
                customer_price = customer_price.first()
                if getattr(customer_price, 'price'):
                    return customer_price.price

        return float(self.price)

    def get_prefix(self):
        return 'mvisolutions_jacarrier'

    def get_methods(self, with_price=True, quote_instance=None, shipping_address=None):
        if not with_price:
            return {
                self.get_prefix() + '_standard': {
                    'name': 'Standard'
                }
            }

        price = float(self.get_price())
        if price <= 0:
            return self.get_freeshipping_method()

        return {
            self.get_prefix() + '_standard': {
                'name': 'Standard',
                'price': price,
            }
        }

    def get_freeshipping_method(self):
        return {
            self.get_prefix() + '_free': {
                'name': 'Free',
                'price': 0.00
            }
        }

    class Meta:
        verbose_name = "John Amico Carrier"
