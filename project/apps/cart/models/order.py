from django.db import models
from django.db.models import Sum, Count, Q
from django.contrib.auth.models import User, Group
from django.urls import reverse
from django.utils.html import format_html
from django.utils.translation import gettext as _, ngettext
from django.utils.safestring import mark_safe

from ..utils import static


class Order(models.Model):
    id = models.AutoField(primary_key=True)
    order = models.ForeignKey('self', on_delete=models.CASCADE, blank=True, null=True, default=None,related_name='self')
    # parent = models.ForeignKey(
    #     'self',
    #     related_name='children',
    #     blank=True,
    #     null=True,
    #     db_index=True,
    #     on_delete=models.CASCADE
    # )

    is_professional = models.BooleanField(blank=True, null=False, default=False)
    professional_id = models.CharField(max_length=255, blank=True, null=True)

    status = models.CharField(max_length=100, db_index=True, choices=static.ORDER_STATUS_CHOICES, default='pending')
    token_id = models.CharField(max_length=10, blank=True)

    customer = models.ForeignKey(
        'CustomerExtra',
        on_delete=models.CASCADE,
        db_index=True,
        blank=True,
        null=True,
        default=None,
    )
    customer_firstname = models.CharField(max_length=100, blank=True, null=True)
    customer_lastname = models.CharField(max_length=100, blank=True, null=True)
    customer_email = models.EmailField(max_length=255, blank=True, null=True)
    customer_gender = models.CharField(max_length=10, blank=True, null=True)
    customer_group = models.ForeignKey(
        Group,
        on_delete=models.CASCADE,
        db_index=True,
        blank=True,
        null=True,
        default=None,
    )
    billing_address = models.ForeignKey(
        'OrderAddress',
        on_delete=models.CASCADE,
        db_index=True,
        blank=True,
        null=True,
        default=None,
        related_name='order_billing_address'
    )
    shipping_address = models.ForeignKey(
        'OrderAddress',
        on_delete=models.CASCADE,
        db_index=True,
        blank=True,
        null=True,
        default=None,
        related_name='order_shipping_address'
    )

    order_note = models.TextField(blank=True, null=True)
    quote = models.ForeignKey(
        'Quote',
        on_delete=models.CASCADE,
        db_index=True,
        blank=True,
        null=True,
        related_name='order_from_quote'
    )

    coupon_code = models.CharField(max_length=255, blank=True, null=True)

    discount_amount = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    discount_invoiced = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)

    shipping_amount = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    shipping_invoiced = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    shipping_refunded = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    shipping_tax_amount = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    shipping_tax_invoiced = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    shipping_tax_refunded = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)

    subtotal = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    subtotal_invoiced = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    subtotal_refunded = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)

    tax_amount = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    tax_invoiced = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    tax_refunded = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)

    grand_total = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    total_invoiced = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    total_offline_refunded = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    total_online_refunded = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    total_paid = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)

    total_qty_ordered = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    total_qty_invoiced = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    total_qty_refunded = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    total_qty_cancelled = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    total_qty_shipped = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)

    shipping_method = models.CharField(max_length=100, blank=True, null=True)
    shipping_method_title = models.CharField(max_length=200, blank=True, null=True)
    increment_id = models.CharField(max_length=100, blank=False, null=True, unique=True)

    weight = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)

    currency_code = models.CharField(max_length=6, blank=True, null=True, default='USD')
    remote_ip = models.CharField(max_length=100, blank=True, null=True)

    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        verbose_name_plural = 'Orders'
        ordering = ['-created_at']

    def __str__(self):
        return 'Order Total #{} for Customer: {}'.format(self.grand_total, self.customer_firstname)

    def get_order_id(self):
        self.order_shipments.count()
        return self.increment_id if self.increment_id else self.id

    def get_view_url(self):
        return reverse('cart:account_order_view', self.id)

    @staticmethod
    def fetch_new_increment_id():
        from ..models import StoreConfig
        global_config = StoreConfig.get_solo()

        last_id = global_config.order_increment_last_id if global_config.order_increment_last_id else 1
        pad_len = global_config.order_increment_pad_length if global_config.order_increment_pad_length else 8
        pad_chr = global_config.order_increment_pad_char if global_config.order_increment_pad_char else '0'
        prefix = global_config.order_increment_prefix if global_config.order_increment_prefix else '1'

        last_id = str(last_id).lstrip(str(prefix))
        new_id_number = int(last_id) + 1

        new_id = str(prefix) + str(new_id_number).rjust(int(pad_len), str(pad_chr))

        global_config.order_increment_last_id = new_id
        global_config.save()

        return new_id

    def get_customer_name(self):
        name = self.customer_firstname
        if self.customer_lastname:
            name += " " + self.customer_lastname

        return name

    def get_address(self, address_type='billing'):
        from ..models import OrderAddress
        if address_type in list(dict(static.ADDRESS_TYPE_CHOICES).keys()):
            oa_q = OrderAddress.objects.filter(order_id=self.id, address_type=address_type)
            if oa_q.exists():
                return oa_q.first()

        return False

    def get_billing_address(self):
        return self.get_address()

    def get_shipping_address(self):
        return self.get_address('shipping')

    def can_cancel(self):
        return self.status not in ['cancelled', 'closed', 'complete']

    def can_delete(self):
        return self.status not in ['cancelled', 'closed', 'complete'] and self.total_invoiced == 0 \
               and self.total_qty_shipped == 0

    def can_invoice(self):
        able = self.status not in ['cancelled', 'closed', 'complete'] or (self.total_invoiced < self.total_paid)
        if able:
            able_items = self.get_invoiceable_items()
            able = True if len(able_items) else False

        return able

    def can_ship(self):
        able = self.status not in ['cancelled', 'closed', 'complete'] or (
                self.total_qty_shipped < self.total_qty_ordered)

        if able:
            able_items = self.get_shippable_items()
            able = True if len(able_items) else False

        return able

    def can_credit_memo(self):
        able = self.status not in ['cancelled', 'closed', 'complete', 'pending', 'refunded'] or (
                (float(self.total_online_refunded) + float(self.total_offline_refunded)) < float(self.total_paid))
        if able:
            able_items = self.get_refundable_items()
            able = True if len(able_items) else False

            if not able:
                if self.get_refundable_shipping_amount():
                    able = True

        return able

    def get_maximum_refundable_amount(self):
        return float(self.total_paid) - (float(self.total_online_refunded) + float(self.total_offline_refunded))

    def get_invoiceable_items(self, separate_ids=False):
        items = []
        ids = []
        for oi in self.orderitem_set.all():
            if oi.maximum_invoiceable_quantity():
                items.append(oi)
                ids.append(oi.id)

        return [items, ids] if separate_ids else items

    def get_refundable_items(self, separate_ids=False):
        items = []
        ids = []
        for oi in self.orderitem_set.all():
            if oi.maximum_refundable_quantity():
                items.append(oi)
                ids.append(oi.id)

        return [items, ids] if separate_ids else items

    def get_invoiceable_shipping_amount(self):
        amount = float(self.shipping_amount) - (float(self.shipping_invoiced) + float(self.shipping_refunded))
        return 0.00 if amount < 0 else amount

    def get_invoiceable_shipping_tax_amount(self):
        amount = float(self.shipping_tax_amount) - (
                float(self.shipping_tax_invoiced) + float(self.shipping_tax_refunded))
        return 0.00 if amount < 0 else amount

    def get_refundable_shipping_amount(self):
        amount = (float(self.shipping_invoiced) - float(self.shipping_refunded))
        amount = 0.00 if amount < 0 else amount
        return 0.00 if float(self.shipping_amount) < amount else amount

    def get_refundable_shipping_tax_amount(self):
        amount = (float(self.shipping_tax_invoiced) - float(self.shipping_tax_refunded))
        amount = 0.00 if amount < 0 else amount
        return 0.00 if float(self.shipping_tax_amount) > amount else amount

    def get_shippable_items(self, separate_ids=False):
        items = []
        ids = []
        for oi in self.orderitem_set.all():
            if oi.is_shippable() and oi.maximum_shippable_quantity():
                items.append(oi)
                ids.append(oi.id)

        return [items, ids] if separate_ids else items

    """
        `submitted_data` is a dict contains order_item_id and quantity pair.
        {12: '2', 20: '0'}
    """

    def prepare_shippable_data(self, submitted_data=None):
        data = {'items': {}, 'total_qty': 0.00, 'errors': [], 'backup_items': {'items': {}, 'total_qty': 0.00, }}
        submitted_keys = {} if not (submitted_data and isinstance(submitted_data, dict)) else list(
            submitted_data.keys())
        non_shippable_items = []

        shippable_items, ids = self.get_shippable_items(separate_ids=True)

        for i in shippable_items:
            qty = None
            if submitted_keys:
                if str(i.id) in submitted_keys or i.id in submitted_keys:
                    qty = submitted_data[str(i.id)]
                    max_qty = i.maximum_shippable_quantity()
                    try:
                        qty = float(qty)
                        if qty == 0:
                            non_shippable_items.append(i.id)
                        elif qty > max_qty:
                            data['errors'].append(format_html(_(
                                'Maximum Shippable quantity for SKU: ' + i.sku + ', can be: ' + str(round(max_qty))), ))
                    except Exception as e:
                        data['errors'].append(format_html(_('Please enter valid quantity for SKU: ' + i.sku), ))
                else:
                    non_shippable_items.append(i.id)

            if i.id not in non_shippable_items:
                item_obj = i.get_shippable_object(qty)
                if item_obj:
                    data['items'][i.id] = item_obj
                    data['total_qty'] += float(data['items'][i.id]['qty'])
            else:
                item_obj = i.get_shippable_object()
                if item_obj:
                    data['backup_items']['items'][i.id] = item_obj
                    if 'qty' in data['backup_items']['items'][i.id]:
                        data['backup_items']['total_qty'] += float(data['backup_items']['items'][i.id]['qty'])

        if not data['items']:
            if submitted_data:
                data['errors'].append(format_html(_('No items to be shipped! Please input at least 1 quantity of the '
                                                    'item you want to ship')))
            data.update(data['backup_items'])

        return data

    def create_shipment(self, shipment_data):
        if self.can_ship():
            from ..models import Shipment, ShipmentItem, OrderItem
            from django.forms.models import model_to_dict

            if isinstance(shipment_data, dict):
                items = shipment_data['items'] if 'items' in shipment_data and isinstance(shipment_data['items'], dict) \
                                                  and shipment_data['items'] else None
                total_qty = shipment_data['total_qty'] if 'total_qty' in shipment_data and \
                                                          isinstance(shipment_data['total_qty'], float) else 0.00

                if not (items and total_qty):
                    return _("Not all required data are available to process")
                else:
                    try:
                        shipment = Shipment.objects.create(**{
                            'order': self,
                            'customer': self.customer,
                            'billing_address': self.billing_address,
                            'shipping_address': self.shipping_address,
                            'total_qty': total_qty,
                            'increment_id': Shipment.fetch_new_increment_id(),
                            'email_sent': False,
                        })
                        if shipment:
                            # @TODO: Trigger Shipment Email for successful DB Transaction

                            self.total_qty_shipped = float(self.total_qty_shipped) + float(total_qty)
                            self.status = 'partially_shipped' if self.total_qty_shipped < self.total_qty_ordered else 'shipped'

                            self.save()

                            for i_id, i in items.items():
                                if 'obj' in i and isinstance(i['obj'], OrderItem):
                                    item_data = model_to_dict(i['obj'])
                                    for dattr in ['id', 'order', 'order_id', 'parent', 'parent_id', 'quote_item',
                                                  'quote_item_id', 'qty_canceled', 'qty_invoiced', 'qty_refunded',
                                                  'qty_shipped', 'tax_invoiced', 'discount_invoiced', 'row_invoiced',
                                                  'created_at', 'updated_at', 'product', 'product_type', 'tax_percent',
                                                  'tax_amount', 'tax_invoiced', 'discount_percent', 'discount_amount',
                                                  'discount_invoiced', 'amount_refunded', 'currency', 'row_subtotal',
                                                  'row_total', 'row_invoiced', 'created_at', 'updated_at',
                                                  'tax_refunded', 'row_refunded']:
                                        if dattr in item_data:
                                            del item_data[dattr]

                                    item_data['shipment'] = shipment
                                    item_data['quantity'] = i['qty']
                                    item_data['product'] = i['obj'].product
                                    item_data['order_item_id'] = i['obj'].id
                                    shipment_item = ShipmentItem.objects.create(**item_data)

                                    if shipment_item:
                                        i['obj'].qty_shipped = float(i['obj'].qty_shipped) + float(i['qty'])

                                        i['obj'].save()

                        return True
                    except Exception as e:
                        return _("Something went wrong while processing the shipment")

        return False

    """
        `invoiceable_data` is a dict contains `items` dict which contains order_item_id and quantity pair and 
        `confirmed` flag telling if the submitted data is confirmed to create a new invoice
        {'items': {12: '2', 20: '0'}, 'confirmed': True}
    """

    def ship(self, shippable_data=None):
        if self.can_ship():
            submitted_data = None
            confirmed = False
            if isinstance(shippable_data, dict):
                submitted_data = shippable_data['items'] if 'items' in shippable_data and shippable_data['items'] and \
                                                            isinstance(shippable_data['items'],
                                                                       dict) else submitted_data
                confirmed = True if 'confirmed' in shippable_data and shippable_data['confirmed'] else confirmed

            data = self.prepare_shippable_data(submitted_data)
            if 'errors' in data and len(data['errors']) > 0:
                confirmed = False
            data['confirmed'] = confirmed
            if not confirmed:
                return data
            else:
                return self.create_shipment(data)
                pass

        return False

    """
        `submitted_data` is a dict contains order_item_id and quantity pair.
        {12: '2', 20: '0'}
    """

    def prepare_invoicable_data(self, submitted_data=None):
        data = {'items': {}, 'total_qty': 0.00, 'sub_total': 0.00, 'tax_amount': 0.00, 'discount_amount': 0.00,
                'items_total': 0.00, 'errors': [], 'backup_items': {'items': {}, 'total_qty': 0.00, 'sub_total': 0.00,
                                                                    'tax_amount': 0.00, 'discount_amount': 0.00,
                                                                    'items_total': 0.00}
                }
        submitted_keys = {} if not (submitted_data and isinstance(submitted_data, dict)) else list(
            submitted_data.keys())
        non_invoicable_items = []

        invoiceable_items, ids = self.get_invoiceable_items(separate_ids=True)

        for i in invoiceable_items:
            qty = None
            if submitted_keys:
                if str(i.id) in submitted_keys or i.id in submitted_keys:
                    qty = submitted_data[str(i.id)]
                    max_qty = i.maximum_invoiceable_quantity()
                    try:
                        qty = float(qty)
                        if qty == 0:
                            non_invoicable_items.append(i.id)
                        elif qty > max_qty:
                            data['errors'].append(format_html(_(
                                'Maximum Invoice-able quantity for SKU: ' + i.sku + ', can be: ' + str(
                                    round(max_qty))), ))
                    except Exception as e:
                        data['errors'].append(format_html(_('Please enter valid quantity for SKU: ' + i.sku), ))
                else:
                    non_invoicable_items.append(i.id)

            if i.id not in non_invoicable_items:
                item_obj = i.get_calculative_object('invoice', qty)
                if item_obj:
                    data['items'][i.id] = item_obj
                    data['total_qty'] += float(data['items'][i.id]['qty'])
                    data['sub_total'] += float(data['items'][i.id]['sub_total'])
                    data['tax_amount'] += float(data['items'][i.id]['tax_amount'])
                    data['discount_amount'] += float(data['items'][i.id]['discount_amount'])
                    data['items_total'] += float(data['items'][i.id]['row_total'])
            else:
                item_obj = i.get_calculative_object('invoice')
                if item_obj:
                    data['backup_items']['items'][i.id] = item_obj
                    data['backup_items']['total_qty'] += float(data['backup_items']['items'][i.id]['qty'])
                    data['backup_items']['sub_total'] += float(data['backup_items']['items'][i.id]['sub_total'])
                    data['backup_items']['tax_amount'] += float(data['backup_items']['items'][i.id]['tax_amount'])
                    data['backup_items']['discount_amount'] += float(
                        data['backup_items']['items'][i.id]['discount_amount'])
                    data['backup_items']['items_total'] += float(data['backup_items']['items'][i.id]['row_total'])

        data['shipping_amount'] = float(self.get_invoiceable_shipping_amount())
        data['shipping_tax_amount'] = float(self.get_invoiceable_shipping_tax_amount())
        data['total'] = data['items_total'] + data['shipping_amount'] + data['shipping_tax_amount']

        if not data['items']:
            if submitted_data:
                data['errors'].append(format_html(_('No items to be invoiced! Please input at least 1 quantity of the '
                                                    'item you want to invoice')))
            data.update(data['backup_items'])
            data['total'] = data['items_total'] + data['shipping_amount'] + data['shipping_tax_amount']

        return data

    def create_invoice(self, invoice_data):
        if self.can_invoice():
            from ..models import Invoice, InvoiceItem, OrderItem, PaymentTransaction
            from django.forms.models import model_to_dict

            if isinstance(invoice_data, dict):
                items = invoice_data['items'] if 'items' in invoice_data and isinstance(invoice_data['items'], dict) \
                                                 and invoice_data['items'] else None
                total_qty = invoice_data['total_qty'] if 'total_qty' in invoice_data and \
                                                         isinstance(invoice_data['total_qty'], float) else 0.00
                discount_amount = invoice_data['discount_amount'] if 'discount_amount' in invoice_data and \
                                                                     isinstance(invoice_data['discount_amount'],
                                                                                float) else 0.00
                shipping_amount = invoice_data['shipping_amount'] if 'shipping_amount' in invoice_data and \
                                                                     isinstance(invoice_data['shipping_amount'],
                                                                                float) else 0.00
                shipping_tax_amount = invoice_data['shipping_tax_amount'] if 'shipping_tax_amount' in invoice_data and \
                                                                             isinstance(
                                                                                 invoice_data['shipping_tax_amount'],
                                                                                 float) else 0.00
                subtotal = invoice_data['sub_total'] if 'sub_total' in invoice_data and \
                                                        isinstance(invoice_data['sub_total'], float) else 0.00
                tax_amount = invoice_data['tax_amount'] if 'tax_amount' in invoice_data and \
                                                           isinstance(invoice_data['tax_amount'], float) else 0.00
                grand_total = invoice_data['total'] if 'total' in invoice_data and \
                                                       isinstance(invoice_data['total'], float) else 0.00

                if not (items and subtotal and grand_total):
                    return _("Not all required data are available to process")
                else:
                    trans_id = None
                    try:
                        # @TODO: 1. for capturing different amount than the Order total, void the existing authorised
                        #  transaction and then capture the new amount

                        payment = self.order_payment.first()
                        payment_invoiced = False
                        pay_trans = payment.get_active_or_last_parent_transaction()
                        if pay_trans:
                            if self.grand_total is not grand_total:
                                if not pay_trans.is_payment_info_saved:
                                    return _(
                                        "The customer did not save the payment (Card / Bank) details. So, we cannot "
                                        "process partial invoice")

                            payment_invoiced = payment.invoice(grand_total)
                        if not payment_invoiced or isinstance(payment_invoiced, str):
                            return _("Couldn't successfully process the payment transaction")
                        if isinstance(payment_invoiced, PaymentTransaction):
                            trans_id = payment_invoiced.transaction_id

                        if not trans_id:
                            new_pay_trans = payment.get_active_parent_transaction()
                            if not (new_pay_trans and isinstance(new_pay_trans, PaymentTransaction)):
                                new_pay_trans = pay_trans
                            trans_id = new_pay_trans.transaction_id

                        invoice = Invoice.objects.create(**{
                            'order_id': self.id,
                            'discount_amount': discount_amount,
                            'shipping_amount': shipping_amount,
                            'shipping_tax_amount': shipping_tax_amount,
                            'subtotal': subtotal,
                            'tax_amount': tax_amount,
                            'grand_total': grand_total,
                            'total_qty': total_qty,
                            'increment_id': Invoice.fetch_new_increment_id(),
                            'email_sent': False,
                            'status': 'paid',
                            'transaction_id': trans_id,
                        })
                        if invoice:
                            invoice.invoice = invoice
                            invoice.save()

                            # @TODO: Trigger Invoice Email for successful DB Transaction

                            self.discount_invoiced = float(self.discount_invoiced) + float(discount_amount)
                            self.shipping_invoiced = float(self.shipping_invoiced) + float(shipping_amount)
                            self.shipping_tax_invoiced = float(self.shipping_tax_invoiced) + float(shipping_tax_amount)
                            self.subtotal_invoiced = float(self.subtotal_invoiced) + float(subtotal)
                            self.tax_invoiced = float(self.tax_invoiced) + float(tax_amount)
                            self.total_invoiced = float(self.total_invoiced) + float(grand_total)
                            self.total_qty_invoiced = float(self.total_qty_invoiced) + float(total_qty)

                            self.status = 'processing'

                            self.save()

                            for i_id, i in items.items():
                                if 'obj' in i and isinstance(i['obj'], OrderItem):
                                    item_data = model_to_dict(i['obj'])
                                    for dattr in ['id', 'order', 'order_id', 'parent', 'parent_id', 'quote_item',
                                                  'quote_item_id', 'qty_canceled', 'qty_invoiced', 'qty_refunded',
                                                  'qty_shipped', 'tax_invoiced', 'discount_invoiced', 'row_invoiced',
                                                  'created_at', 'updated_at', 'product', 'created_at', 'updated_at',
                                                  'tax_refunded', 'row_refunded']:
                                        if dattr in item_data:
                                            del item_data[dattr]

                                    item_data['invoice'] = invoice
                                    item_data['quantity'] = i['qty']
                                    item_data['tax_amount'] = i['tax_amount']
                                    item_data['discount_amount'] = i['discount_amount']
                                    item_data['row_subtotal'] = i['sub_total']
                                    item_data['row_total'] = i['row_total']
                                    item_data['product'] = i['obj'].product
                                    item_data['order_item'] = i['obj']
                                    invoice_item = InvoiceItem.objects.create(**item_data)

                                    if invoice_item:
                                        i['obj'].qty_invoiced = float(i['obj'].qty_invoiced) + float(i['qty'])
                                        i['obj'].discount_invoiced = float(i['obj'].discount_invoiced) + float(
                                            i['discount_amount'])
                                        i['obj'].row_invoiced = float(i['obj'].row_invoiced) + float(i['row_total'])
                                        i['obj'].tax_invoiced = float(i['obj'].tax_invoiced) + float(i['tax_amount'])

                                        i['obj'].save()

                        return True
                    except Exception as e:
                        return _("Something went wrong while processing the invoice")

        return False

    """
        `invoiceable_data` is a dict contains `items` dict which contains order_item_id and quantity pair and 
        `confirmed` flag telling if the submitted data is confirmed to create a new invoice
        {'items': {12: '2', 20: '0'}, 'confirmed': True}
    """

    def invoice(self, invoiceable_data=None):
        if self.can_invoice():
            submitted_data = None
            confirmed = False
            if isinstance(invoiceable_data, dict):
                submitted_data = invoiceable_data['items'] if 'items' in invoiceable_data and invoiceable_data[
                    'items'] and \
                                                              isinstance(invoiceable_data['items'],
                                                                         dict) else submitted_data
                confirmed = True if 'confirmed' in invoiceable_data and invoiceable_data['confirmed'] else confirmed

            data = self.prepare_invoicable_data(submitted_data)
            if 'errors' in data and len(data['errors']) > 0:
                confirmed = False
            data['confirmed'] = confirmed
            if not confirmed:
                return data
            else:
                return self.create_invoice(data)
                pass

        return False

    """
        `submitted_data` is a dict contains order_item_id and quantity pair.
        {12: '2', 20: '0'}
    """

    def prepare_refundable_data(self, submitted_data=None):
        data = {'items': {}, 'total_qty': 0.00, 'sub_total': 0.00, 'tax_amount': 0.00, 'discount_amount': 0.00,
                'items_total': 0.00, 'errors': [], 'backup_items': {'items': {}, 'total_qty': 0.00, 'sub_total': 0.00,
                                                                    'tax_amount': 0.00, 'discount_amount': 0.00,
                                                                    'items_total': 0.00},
                'shipping_amount': 0.00, 'shipping_tax_amount': 0.00,
                }

        submitted_items_keys = []
        submitted_shipping_amount = 0.00
        non_refundable_items = []

        if submitted_data and isinstance(submitted_data, dict):
            submitted_items_keys = submitted_items_keys if not ('items' in submitted_data and
                                                                isinstance(submitted_data['items'], dict)) else list(
                submitted_data['items'].keys())

            submitted_shipping_amount = submitted_shipping_amount if not ('shipping' in submitted_data and
                                                                          isinstance(submitted_data['shipping'],
                                                                                     float)) and submitted_shipping_amount < 0 \
                else submitted_data['shipping']

        refundable_items, ids = self.get_refundable_items(separate_ids=True)

        for i in refundable_items:
            qty = None
            if submitted_items_keys:
                if str(i.id) in submitted_items_keys or i.id in submitted_items_keys:
                    qty = submitted_data['items'][str(i.id)]
                    max_qty = i.maximum_refundable_quantity()
                    try:
                        qty = float(qty)
                        if qty == 0:
                            non_refundable_items.append(i.id)
                        elif qty > max_qty:
                            data['errors'].append(format_html(_(
                                'Maximum Refundable quantity for SKU: ' + i.sku + ', can be: ' + str(
                                    round(max_qty))), ))
                    except Exception as e:
                        data['errors'].append(format_html(_('Please enter valid quantity for SKU: ' + i.sku), ))
                else:
                    non_refundable_items.append(i.id)

            if i.id not in non_refundable_items:
                item_obj = i.get_calculative_object('refund', qty)
                if item_obj:
                    data['items'][i.id] = item_obj
                    data['total_qty'] += float(data['items'][i.id]['qty'])
                    data['sub_total'] += float(data['items'][i.id]['sub_total'])
                    data['tax_amount'] += float(data['items'][i.id]['tax_amount'])
                    data['discount_amount'] += float(data['items'][i.id]['discount_amount'])
                    data['items_total'] += float(data['items'][i.id]['row_total'])
            else:
                item_obj = i.get_calculative_object('refund')
                if item_obj:
                    data['backup_items']['items'][i.id] = item_obj
                    data['backup_items']['total_qty'] += float(data['backup_items']['items'][i.id]['qty'])
                    data['backup_items']['sub_total'] += float(data['backup_items']['items'][i.id]['sub_total'])
                    data['backup_items']['tax_amount'] += float(data['backup_items']['items'][i.id]['tax_amount'])
                    data['backup_items']['discount_amount'] += float(
                        data['backup_items']['items'][i.id]['discount_amount'])
                    data['backup_items']['items_total'] += float(data['backup_items']['items'][i.id]['row_total'])

        if submitted_shipping_amount and isinstance(submitted_shipping_amount, float):
            max_ship_amount = float(self.get_refundable_shipping_amount())
            max_ship_tax_amount = float(self.get_refundable_shipping_tax_amount())

            if submitted_shipping_amount > max_ship_amount:
                data['errors'].append(format_html(_('Maximum refundable Shipping amount is: $' + str(max_ship_amount))))
            else:
                data['shipping_amount'] = submitted_shipping_amount
                data['shipping_tax_amount'] = round((submitted_shipping_amount / max_ship_amount) * max_ship_tax_amount,
                                                    2)

        if not submitted_data:
            data['shipping_amount'] = float(self.get_refundable_shipping_amount())
            data['shipping_tax_amount'] = float(self.get_refundable_shipping_tax_amount())

        data['total'] = data['items_total'] + data['shipping_amount'] + data['shipping_tax_amount']

        if not data['items']:
            if submitted_data and not data['shipping_amount']:
                data['errors'].append(format_html(_('No items to be refunded! Please input at least 1 quantity of the '
                                                    'item you want to invoice')))
            data.update(data['backup_items'])
            data['total'] = data['items_total'] + data['shipping_amount'] + data['shipping_tax_amount']

        return data

    def create_refund(self, processing_data):
        from ..models import Invoice, InvoiceItem

        if self.can_credit_memo():
            from ..models import CreditMemo, CreditMemoItem, OrderItem, PaymentTransaction, CreditMemoTransaction
            from django.forms.models import model_to_dict

            if isinstance(processing_data, dict):
                items = processing_data['items'] if 'items' in processing_data and \
                                                    isinstance(processing_data['items'], dict) and processing_data[
                                                        'items'] else None
                total_qty = processing_data['total_qty'] if 'total_qty' in processing_data and \
                                                            isinstance(processing_data['total_qty'], float) else 0.00
                discount_amount = processing_data['discount_amount'] if 'discount_amount' in processing_data and \
                                                                        isinstance(processing_data['discount_amount'],
                                                                                   float) else 0.00
                shipping_amount = processing_data['shipping_amount'] if 'shipping_amount' in processing_data and \
                                                                        isinstance(processing_data['shipping_amount'],
                                                                                   float) else 0.00
                shipping_tax_amount = processing_data[
                    'shipping_tax_amount'] if 'shipping_tax_amount' in processing_data and \
                                              isinstance(processing_data['shipping_tax_amount'], float) else 0.00
                subtotal = processing_data['sub_total'] if 'sub_total' in processing_data and \
                                                           isinstance(processing_data['sub_total'], float) else 0.00
                tax_amount = processing_data['tax_amount'] if 'tax_amount' in processing_data and \
                                                              isinstance(processing_data['tax_amount'], float) else 0.00
                grand_total = processing_data['total'] if 'total' in processing_data and \
                                                          isinstance(processing_data['total'], float) else 0.00

                if not (items or shipping_amount) and subtotal and grand_total:
                    return _("Not all required data are available to process")
                else:
                    trans_id = None
                    # if True:
                    try:
                        transaction_payments = {}
                        transaction_details = {}
                        shipping_transactions = {}
                        # transaction_order_payments = {}
                        non_transaction_payment = 0.00
                        if items:
                            ii_q = InvoiceItem.objects.filter(order_item__id__in=items.keys())

                            if ii_q.exists():
                                for invoice_item in ii_q.all():
                                    if invoice_item.order_item_id in list(items.keys()):
                                        item = items[invoice_item.order_item_id]

                                        # Following line will include the Product Tax automatically for per product
                                        value = (float(invoice_item.row_total) /
                                                 float(invoice_item.quantity)) * float(item['qty'])
                                        if not invoice_item.invoice.transaction_id:
                                            non_transaction_payment += value
                                        else:
                                            key = int(invoice_item.invoice.transaction_id)
                                            if key not in transaction_payments:
                                                transaction_payments[key] = 0.00
                                                transaction_details[key] = {}
                                                transaction_details[key]['items'] = {}
                                            # if invoice_item.invoice not in transaction_order_payments:
                                            #     transaction_details[key] = {}

                                            transaction_payments[key] += value
                                            transaction_details[key]['items'][invoice_item.id] = invoice_item

                        if shipping_amount:
                            i_q = Invoice.objects.filter(shipping_amount__gte=float(shipping_amount), order_id=self.id)
                            if i_q.exists():
                                for inv in i_q.all():
                                    shipping_tax = (float(shipping_amount) / float(inv.shipping_amount)) * float(
                                        inv.shipping_tax_amount)
                                    total_shipping_amount = float(shipping_amount) + shipping_tax

                                    if not inv.transaction_id:
                                        non_transaction_payment += total_shipping_amount
                                    else:
                                        key_ = int(inv.transaction_id)
                                        if key_ not in transaction_payments:
                                            transaction_payments[key_] = 0.00
                                            transaction_details[key_] = {}
                                            transaction_details[key_]['shipping'] = 0

                                        if key_ not in shipping_transactions:
                                            shipping_transactions[key_] = {}
                                            shipping_transactions[key_]['shipping'] = 0.00
                                            shipping_transactions[key_]['shipping_tax'] = 0.00

                                        transaction_payments[key_] += total_shipping_amount
                                        transaction_details[key_]['shipping'] = total_shipping_amount

                                        shipping_transactions[key_]['shipping'] = float(shipping_amount)
                                        shipping_transactions[key_]['shipping_tax'] = shipping_tax

                        if grand_total <= 0:
                            return "The total should be greater than 0 (Zero)"
                        if grand_total > self.get_maximum_refundable_amount():
                            return "Maximum amount available to refund is $" + str(round(
                                self.get_maximum_refundable_amount(), 2))

                        payment_transactions_q = PaymentTransaction.objects.filter(
                            transaction_id__in=transaction_payments.keys(),
                            transaction_type__in=['auth_capture', 'capture_prior_auth'], is_closed=False)

                        payment_refunded = {}
                        payment_refund_errors = {}
                        if payment_transactions_q.exists():
                            for pt in payment_transactions_q.all():
                                refunded = False

                                if pt.transaction_id:
                                    trans_key = int(pt.transaction_id)
                                    if trans_key in transaction_payments:
                                        refunded = pt.payment.refund_direct(pt, transaction_payments[trans_key])
                                    if refunded:
                                        payment_refunded[trans_key] = transaction_payments[trans_key]
                                    else:
                                        if isinstance(refunded, str):
                                            payment_refund_errors[trans_key] = refunded
                                        else:
                                            payment_refund_errors[trans_key] = "Couldn't successfully process " \
                                                   "the refund process for TransactionID: {}".format(pt.transaction_id)

                        if not payment_refunded:
                            if payment_refund_errors:
                                return mark_safe(
                                    "<div><p>{}</p></div>".format("</p><p>".join(payment_refund_errors.values())))
                            return _("Couldn't successfully process the refund process")
                        else:
                            credit_memo = CreditMemo.objects.create(**dict(order_id=self.id,
                                                                           discount_amount=discount_amount,
                                                                           shipping_amount=shipping_amount,
                                                                           shipping_tax_amount=shipping_tax_amount,
                                                                           subtotal=subtotal,
                                                                           tax_amount=tax_amount,
                                                                           grand_total=grand_total,
                                                                           total_qty=total_qty,
                                                                           increment_id=CreditMemo.fetch_new_increment_id(),
                                                                           email_sent=False))
                            if credit_memo:
                                credit_memo.credit_memo = credit_memo
                                credit_memo.save()

                                refunded_trans = []

                                total_refunded = 0.00
                                subtotal_refunded = 0.00
                                tax_refunded = 0.00
                                total_qty_refunded = 0

                                is_shipping_refunded = False
                                is_shipping_tax_refunded = False

                                for refunded_tr_id, refunded_amount in payment_refunded.items():
                                    refunded_trans.append(CreditMemoTransaction(
                                        credit_memo=credit_memo, amount=refunded_amount, transaction_id=refunded_tr_id
                                    ))

                                    total_refunded += float(refunded_amount)

                                    if not is_shipping_refunded and refunded_tr_id in shipping_transactions.keys():
                                        is_shipping_refunded = shipping_transactions[refunded_tr_id]['shipping']
                                        is_shipping_tax_refunded = shipping_transactions[refunded_tr_id]['shipping_tax']

                                        subtotal_refunded += is_shipping_refunded
                                        tax_refunded += is_shipping_tax_refunded

                                    if 'items' in transaction_details[refunded_tr_id]:
                                        for ii_id, ii in transaction_details[refunded_tr_id]['items'].items():
                                            i = items[ii.order_item_id]
                                            if i and 'obj' in i and isinstance(i['obj'], OrderItem):
                                                item_data = model_to_dict(i['obj'])
                                                for dattr in ['id', 'order', 'order_id', 'parent', 'parent_id', 'quote_item',
                                                              'quote_item_id', 'qty_canceled', 'qty_invoiced', 'qty_refunded',
                                                              'qty_shipped', 'tax_invoiced', 'discount_invoiced',
                                                              'row_invoiced','created_at', 'updated_at', 'product', 'created_at',
                                                              'updated_at', 'tax_refunded', 'row_refunded']:
                                                    if dattr in item_data:
                                                        del item_data[dattr]

                                                item_data['credit_memo'] = credit_memo
                                                item_data['quantity'] = i['qty']
                                                item_data['tax_amount'] = i['tax_amount']
                                                item_data['discount_amount'] = i['discount_amount']
                                                item_data['row_subtotal'] = i['sub_total']
                                                item_data['row_total'] = i['row_total']
                                                item_data['product'] = i['obj'].product
                                                invoice_item = CreditMemoItem.objects.create(**item_data)

                                                subtotal_refunded += i['sub_total']
                                                tax_refunded += i['tax_amount']
                                                total_qty_refunded += i['qty']

                                                if invoice_item:
                                                    i['obj'].qty_refunded = float(i['obj'].qty_refunded) + float(i['qty'])
                                                    i['obj'].tax_refunded = float(i['obj'].tax_refunded) + float(i['tax_amount'])
                                                    i['obj'].row_refunded = float(i['obj'].row_refunded) + float(i['row_total'])

                                                    i['obj'].save()

                                if refunded_trans:
                                    CreditMemoTransaction.objects.bulk_create(refunded_trans)

                                # @TODO: Trigger Credit Memo Email for successful DB Transaction

                                if is_shipping_refunded:
                                    self.shipping_refunded = float(self.shipping_refunded) + float(is_shipping_refunded)
                                if is_shipping_tax_refunded:
                                    self.shipping_tax_refunded = float(self.shipping_tax_refunded) + float(is_shipping_tax_refunded)
                                if subtotal_refunded:
                                    self.subtotal_refunded = float(self.subtotal_refunded) + float(subtotal_refunded)
                                if tax_refunded:
                                    self.tax_refunded = float(self.tax_refunded) + float(tax_refunded)
                                if total_qty_refunded:
                                    self.total_qty_refunded = float(self.total_qty_refunded) + float(total_qty_refunded)

                                self.save()

                        return True
                    except Exception as e:
                        return _("Something went wrong while processing the refund")

        return False

    """
        `invoiceable_data` is a dict contains `items` dict which contains order_item_id and quantity pair and 
        `confirmed` flag telling if the submitted data is confirmed to create a new invoice
        {'items': {12: '2', 20: '0'}, 'confirmed': True}
    """

    def refund(self, refundable_data=None):
        from ..utils import helper

        if self.can_credit_memo():
            submitted_data = None
            confirmed = False
            if isinstance(refundable_data, dict):
                items = refundable_data['items'] if 'items' in refundable_data and refundable_data['items'] and \
                                                    isinstance(refundable_data['items'], dict) else {}
                confirmed = True if 'confirmed' in refundable_data and refundable_data['confirmed'] else confirmed
                shipping = float(refundable_data['shipping']) if 'shipping' in refundable_data and \
                                                                 (helper.isfloat(refundable_data['shipping']) or
                                                                  refundable_data['shipping'].isnumeric()) else None

                submitted_data = {'items': items, 'shipping': shipping}

            data = self.prepare_refundable_data(submitted_data)
            if 'errors' in data and len(data['errors']) > 0:
                confirmed = False
            data['confirmed'] = confirmed
            if not confirmed:
                return data
            else:
                return self.create_refund(data)
                pass

        return False

    def cancel(self):
        if self.can_cancel():
            try:
                from django.db.models import F

                self.status = 'cancelled'
                self.save()

                self.orderitem_set.all().update(qty_canceled=F('quantity'))

                for payment in self.order_payment.all():
                    payment.cancel()

                for invoice in self.order_invoices.all():
                    invoice.cancel()

                # @TODO: Trigger Order Cancellation Email for successful DB Transaction

                return True
            except Exception as e:
                pass
        return False

    def save(self, force_insert=False, force_update=False, using=None, update_fields=None, external_use=False):
        from ..utils import helper

        self.token_id = self.token_id if self.token_id else helper.get_unique_string()[2:11]
        self.remote_ip = self.remote_ip if self.remote_ip else helper.get_client_ip()

        super().save(force_insert, force_update, using, update_fields)

    def delete(self, using=None, keep_parents=False):

        super().delete(using=None, keep_parents=False)
