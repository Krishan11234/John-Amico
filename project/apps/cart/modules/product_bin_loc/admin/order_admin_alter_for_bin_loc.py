from django.dispatch import receiver
from .... import signals
from ....models.order_item import OrderItem
from ..models import ProductBinLocation


@receiver(signals.order_item_attributes, sender=OrderItem)
def add__product_bin_location__order_attr(*args, **kwargs):
    attrs = kwargs['attrs']
    order_item_obj = kwargs['obj']

    if attrs and isinstance(attrs, dict) and isinstance(order_item_obj, OrderItem):
        prod_bin_q = ProductBinLocation.objects.filter(product=order_item_obj.product)
        if prod_bin_q.exists():
            prod_bin = prod_bin_q.get()
            if prod_bin.bin_location:
                attrs['bin_loc'] = {
                    'title': "BIN Location",
                    'value': prod_bin.bin_location
                }

    return attrs
