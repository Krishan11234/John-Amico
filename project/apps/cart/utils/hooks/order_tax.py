from django.db.models.signals import pre_delete
from django.dispatch import receiver

from ...signals import order_totals
from ...utils.order import OrderUtil


# @receiver(order_totals, sender=OrderUtil)
def handle_order_billing_tax(request, totals, quote, customer_extra, data, **kwargs):
    billing_address = quote.get_billing_address()
    if billing_address.state:
        pass

    return totals


order_totals.connect(handle_order_billing_tax, sender=OrderUtil, weak=False)
