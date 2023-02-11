
from django.db import models
from django.db.models import Sum, Count, Q
from django.contrib.auth.models import User

from ..utils.static import PRODUCT_TYPE_CHOICES, PRODUCT_TYPE_CHOICES_NON_SHIPPABLE


class OrderItem(models.Model):
    id = models.AutoField(primary_key=True)
    order = models.ForeignKey('Order', on_delete=models.CASCADE, db_index=True,)
    quote_item = models.ForeignKey('QuoteItem', on_delete=models.CASCADE, db_index=True, null=True,)
    product = models.ForeignKey('Product', on_delete=models.CASCADE, db_index=True, null=True,)
    product_type = models.CharField(max_length=255, db_index=True, choices=PRODUCT_TYPE_CHOICES, default='simple',
                                    blank=True, null=True)
    sku = models.CharField(max_length=100, blank=True, null=True)
    name = models.CharField(max_length=255, blank=True, null=True)
    price = models.DecimalField(max_digits=15, decimal_places=4, blank=True, default=0, db_index=True)

    quantity = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=1, db_index=True)
    qty_canceled = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0, db_index=True)
    qty_invoiced = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0, db_index=True)
    qty_refunded = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0, db_index=True)
    qty_shipped = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0, db_index=True)

    option = models.IntegerField(blank=True, null=True)
    option_title = models.CharField(max_length=255, blank=True, null=True)
    option_sku = models.CharField(max_length=255, blank=True, null=True)

    tax_percent = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0, db_index=True)
    tax_amount = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0, db_index=True)
    tax_invoiced = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0, db_index=True)
    tax_refunded = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0, db_index=True)

    discount_percent = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0, db_index=True)
    discount_amount = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0, db_index=True)
    discount_invoiced = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0, db_index=True)

    amount_refunded = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0, db_index=True)

    currency = models.CharField(max_length=6, blank=True, null=True)
    row_subtotal = models.DecimalField(max_digits=15, decimal_places=4, blank=True, default=0, db_index=True)
    row_total = models.DecimalField(max_digits=15, decimal_places=4, blank=True, default=0, db_index=True)
    row_invoiced = models.DecimalField(max_digits=15, decimal_places=4, blank=True, default=0, db_index=True)
    row_refunded = models.DecimalField(max_digits=15, decimal_places=4, blank=True, default=0, db_index=True)

    is_free_product = models.BooleanField(blank=True, null=False, default=False)

    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        verbose_name_plural = 'cart'

        ordering = ['-created_at']

    def __str__(self):
        return 'Order Item #{} for Order: {}'.format(self.sku, self.order.get_order_id())

    def get_row_subtotal(self):
        subtotal = float(self.row_subtotal)
        if subtotal <= 0:
            subtotal = float(self.price) * float(self.quantity)

        return subtotal

    def is_shippable(self):
        return self.product_type not in PRODUCT_TYPE_CHOICES_NON_SHIPPABLE

    def maximum_invoiceable_quantity(self):
        qty = (self.quantity - (self.qty_invoiced + self.qty_refunded + self.qty_canceled))
        return qty if qty > 0 else 0.00

    def maximum_refundable_quantity(self):
        qty = (self.quantity - (self.qty_refunded + self.qty_canceled))
        return qty if qty > 0 else 0.00

    def maximum_cancelable_quantity(self):
        return self.maximum_invoiceable_quantity()

    def maximum_shippable_quantity(self):
        if not self.is_shippable():
            return 0.00

        qty = (self.quantity - (self.qty_shipped + self.qty_refunded + self.qty_canceled))
        return qty if qty > 0 else 0.00

    def get_calculative_object(self, calculation_type='invoice', qty=None):
        calculation_types = {
            'invoice': float(self.maximum_invoiceable_quantity()),
            'refund': float(self.maximum_refundable_quantity()),
            'cancel': float(self.maximum_cancelable_quantity()),
        }
        if calculation_type in calculation_types.keys():
            max_qty = calculation_types[calculation_type]
        else:
            max_qty = 0

        obj = {}
        
        if qty is None:
            qty = max_qty
        if qty:
            if isinstance(qty, int) or isinstance(qty, float):
                qty = qty if qty <= max_qty else max_qty
                obj = {
                    'obj': self,
                    'qty': qty,
                    'max_qty': max_qty,
                    'unit_price': float(self.price),
                    'sub_total': float(self.price) * float(qty),
                    'tax_amount': float(self.calculate_field_value('tax', float(qty))),
                    'discount_amount': float(self.calculate_field_value('discount', float(qty))),
                }
                obj['row_total'] = float(obj['sub_total']) + float(obj['tax_amount'])
                if obj['discount_amount'] < 0:
                    obj['row_total'] += float(obj['discount_amount'])
                else:
                    obj['row_total'] -= float(obj['discount_amount'])

        return obj

    def get_shippable_object(self, qty=None):
        max_qty = float(self.maximum_shippable_quantity())
        obj = {}

        if not self.is_shippable():
            return obj

        if qty is None:
            qty = max_qty
        if qty:
            if isinstance(qty, int) or isinstance(qty, float):
                qty = qty if qty <= max_qty else max_qty
                obj = {
                    'obj': self,
                    'qty': qty,
                    'max_qty': max_qty,
                }

        return obj

    def calculate_field_value(self, field, max_qty, qty=None):
        field_value = 0.00

        if not hasattr(self, field+'_amount'):
            return field_value

        model_value = float(getattr(self, field+'_amount'))
        model_percent_value = float(getattr(self, field+'_percent')) if hasattr(self, field+'_percent') else False
        model_invoiced_value = float(getattr(self, field+'_invoiced')) if hasattr(self, field+'_invoiced') else 0.00

        if qty is None:
            qty = max_qty
        if qty:
            if isinstance(qty, int) or isinstance(qty, float):
                if qty <= max_qty:
                    if model_value:
                        if not model_percent_value:
                            field_value = ((model_value - model_invoiced_value) / max_qty) * float(qty)
                        else:
                            field_value = (float(self.price) * (model_percent_value / 100)) * float(qty)
                    else:
                        field_value = model_value

        return field_value

    def get_attributes(self):
        attrs = {
            'sku': {'title': 'SKU', 'value': self.sku + ("-"+self.option_sku if self.option_sku else '')},
            # 'option_sku': {'title': 'Option SKU', 'value': self.option_sku},
        }
        if self.option_title:
            attrs['option_title'] = {'title': 'Size', 'value': self.option_title}

        from ..signals import order_item_attributes

        attrs_signals = order_item_attributes.send(sender=self.__class__, attrs=attrs, obj=self)

        if attrs_signals:
            for signal_handler, signal_attrs in attrs_signals:
                if isinstance(attrs, dict):
                    attrs = signal_attrs

        return attrs


