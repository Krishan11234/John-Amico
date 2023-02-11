import re
from ..utils import helper
from ..utils.helper import get_current_customer, get_request, get_client_ip, make_bas64de_string, is_customer_logged_in


"""
@TODO: Complete this method. Accept Customer argument. Fill the appropriate Customer details (Name, Email)
"""


class CartUtils(object):

    customer_quote = None
    store_config = {}
    review_items = {}

    def get_or_create_quote(self, request=None, quote_id=None, create_quote=True, reload=False):
        from ..models import Quote

        # if isinstance(self.customer_quote, Quote):
        #     if reload:
        #         self.customer_quote = Quote.objects.filter(id=self.customer_quote.id)
        #         if self.customer_quote.exists():
        #             self.customer_quote = self.customer_quote.get()
        #
        #     return self.customer_quote

        conditions = {}
        new_quote_data = {}
        request = request if request else get_request()

        if quote_id and (isinstance(quote_id, int) or (isinstance(quote_id, str) and quote_id.isnumeric())):
            conditions['id'] = quote_id
        else:
            user_ip = get_client_ip(request)
            # quote_token = request.COOKIES.get('cartid')
            quote_token_key = request.session.session_key
            quote_token = make_bas64de_string(quote_token_key)[2:11] if quote_token_key else None

            new_quote_data = {
                'order_id': None,
                'sub_total': 0.00,
                'total_quantity': 0,
                'customer_firstname': None,
                'customer_lastname': None,
                'customer_email': None,
                'remote_ip': user_ip,
            }

            conditions['order_id'] = None
            conditions['is_closed'] = False

            if is_customer_logged_in():
                customer = get_current_customer()
                conditions['customer'] = customer

                if create_quote:
                    id = customer.id if customer else None
                    id = id[0] if id and isinstance(id, tuple) else id

                    new_quote_data['customer_id'] = id

            elif helper.is_professional_logged_in():
                customer = helper.get_current_member()
                id = customer.id if customer else None
                id = id[0] if id and isinstance(id, tuple) else id
                conditions['tbl_member_id'] = id
                if create_quote:
                    new_quote_data['customer_id'] = id
            else:
                conditions['customer'] = None
                if quote_token:
                    conditions['token_id'] = quote_token

            if quote_token:
                new_quote_data['token_id'] = quote_token

        try:
            customer_quote = None
            if conditions:
                customer_quote = Quote.objects.filter(**conditions).order_by('-created_at')[:2]
            if not customer_quote.exists():
                if new_quote_data:
                    customer_quote = Quote.objects.create(**new_quote_data)
            else:
                customer_quote = customer_quote.first()

            self.customer_quote = customer_quote
        except Exception as e:
            if new_quote_data:
                self.customer_quote = Quote.objects.create(**new_quote_data)

        return self.customer_quote

    @staticmethod
    def unify_possible_carts():
        from ..models import Quote

        conditions = {}
        if helper.is_user_types_logged_in(condition_type='or'):
            conditions['order_id'] = None
            conditions['is_closed'] = False

            if helper.is_customer_logged_in():
                conditions['customer'] = helper.get_current_customer()

            if helper.is_professional_logged_in():
                conditions['tbl_member_id'] = helper.get_current_member().tbl_member_id

        if conditions:
            customer_quotes = Quote.objects.filter(**conditions).order_by('-created_at')

            if customer_quotes.count() > 1:
                first_q = customer_quotes.first()
                for q in customer_quotes:
                    if not (q.id == first_q.id):
                        for qi in q.quoteitem_set.all():
                            qi.quote = first_q
                            qi.save()

                        q.is_closed = True
                        q.save()

    def cart_item_create_update(self, product_id, request=None, update=False, quote_instance=False):
        from ..models import Product, ProductSize, QuoteItem, Quote

        quote = quote_item = product_size = product = None
        error_details = []

        requested_quantity = 1
        min_sale_qty = 1.0

        if 'qty' in request.POST and isinstance(request.POST['qty'], int) and float(request.POST['qty']) > 0:
            requested_quantity = request.POST['qty']

        product = Product.objects.filter(id=product_id, is_active=True)

        try:
            if product.exists():
                product = product.get()

                size_name = 'options[' + str(product.id) + ']'
                if request and (size_name in request.POST):
                    size_value = request.POST[size_name]

                    product_size = ProductSize.objects.filter(id=size_value, product=product)
                    if product_size.exists():
                        product_size = product_size.get()

                product_qty_info = product.get_qty_info()
                if product_qty_info.get_quantity() and ((isinstance(product_qty_info.max_sale_qty, float)
                   and product_qty_info.max_sale_qty > 0) or product_qty_info.max_sale_qty is None):

                    product_qty_info.max_sale_qty = product_qty_info.get_max_sale_qty()
                    product_qty_info.min_sale_qty = product_qty_info.get_min_sale_qty()

                    quote = quote_instance if quote_instance else self.get_or_create_quote(request)
                    # if quote.quoteitem_set.count() > 0:
                    quote_item_conditions = {'quote': quote, 'product': product}
                    if product_size:
                        quote_item_conditions['option_id'] = product_size.id

                    if not quote_item:
                        quote_item = QuoteItem.objects.filter(**quote_item_conditions)
                        if quote_item.exists():
                            quote_item = quote_item.get()

                            quote_item, error_details, handled_messages, saved, removed = \
                                self.handle_quote_item_quantity(quote_item, requested_quantity, '', request)

                            if handled_messages and isinstance(handled_messages, list):
                                for hm in handled_messages:
                                    error_details = error_details if error_details else []
                                    error_details.append({'status': 'ERROR', 'messages': [hm]})

                else:
                    error_details.append({
                        'status': 'ERROR',
                        'reason': 'OUT_OF_STOCK',
                        'messages': [
                            'This product either is Out Of Stock or cannot be added to Cart this time.'
                        ]
                    })

                if product_qty_info:
                    min_sale_qty = product_qty_info.get_min_sale_qty()

                if not update and not quote_item and not error_details:
                    qty = requested_quantity if min_sale_qty >= requested_quantity else min_sale_qty
                    error_details = self.check_maximum_cart_quantity(qty, error_details)

                    if not error_details:
                        new_quote_item = {
                            'quote': quote,
                            'product_id': product.id,
                            'quantity': qty,
                            'product_type': product.type,
                            'sku': product.sku,
                            'name': product.name,
                            'remote_ip': get_client_ip(request),
                        }
                        if not product_size:
                            new_quote_item['price'] = product.get_price()
                        else:
                            new_quote_item['price'] = product_size.get_value().get_price()
                            new_quote_item['option_id'] = product_size.id
                            new_quote_item['option_title'] = product_size.title
                            new_quote_item['option_sku'] = product_size.sku

                        new_quote_item['row_total'] = new_quote_item['quantity'] * new_quote_item['price']

                        quote_item = QuoteItem.objects.create(**new_quote_item)

                        if isinstance(quote, Quote):
                            quote_items, total_count, sub_total = self.get_current_quote_items_list(with_count_total=True)
                            quote.total_quantity = total_count
                            quote.sub_total = sub_total
                            quote.save()

        except Exception as e:
            product = False

        return [product, quote, quote_item, error_details]

    def handle_quote_item_quantity(self, quote_item, requested_quantity=1, qty_update_type='replace', request=None, product_qty_info=None):
        from ..models import QuoteItem, Quote

        save = saved = removed = False
        error_details = []
        all_messages = []

        if not isinstance(quote_item, QuoteItem):
            return [False, error_details.append({
                            'status': 'ERROR',
                            'reason': 'UNKNOWN_CART_ITEM',
                            'messages': ['The cart item is unknown']
                        }), all_messages, saved, removed]

        if not (isinstance(requested_quantity, float) or isinstance(requested_quantity, int)):
            return [False, error_details.append({
                            'status': 'ERROR',
                            'reason': 'INVALID_QUANTITY_REQUESTED',
                            'messages': ['Invalid quantity requested']
                        }), all_messages, saved, removed]

        if not product_qty_info:
            product_qty_info = quote_item.product.get_qty_info()

        if product_qty_info:
            original_quantity = 0

            if qty_update_type == 'replace':
                new_quantity = requested_quantity
            elif qty_update_type == 'minus':
                new_quantity = round(quote_item.quantity) - requested_quantity
            else:
                new_quantity = round(quote_item.quantity) + requested_quantity

            if new_quantity:
                if product_qty_info.get_quantity():
                    product_max_sale_qty = product_qty_info.get_max_sale_qty()
                    product_min_sale_qty = product_qty_info.get_min_sale_qty()

                    if product_min_sale_qty and not new_quantity >= product_min_sale_qty:
                        new_quantity = product_min_sale_qty
                        all_messages.append('"Minimum Quantity" restriction applied to cart item: <em>' + quote_item.name + "</em>")

                    if product_max_sale_qty and not new_quantity <= product_max_sale_qty:
                        new_quantity = product_max_sale_qty
                        all_messages.append('"Maximum Quantity" restriction applied to cart item: <em>' + quote_item.name + "</em>")

                    original_quantity = quote_item.quantity
                    quote_item.quantity = new_quantity
                    save = True
                else:
                    error_details.append({
                        'status': 'ERROR',
                        'reason': 'OUT_OF_STOCK',
                        'messages': [
                            'This product either is Out Of Stock or cannot be added to Cart this time.'
                        ]
                    })

                    quote_item.delete()
                    removed = True
            else:
                quote_item.delete()
                all_messages.append('Item removed successfully')
                removed = True

            if save:
                if qty_update_type not in ['minus']:
                    error_details = self.check_maximum_cart_quantity((new_quantity - original_quantity), error_details)
                if not error_details:
                    saved = True
                    quote_item.row_total = float(quote_item.quantity) * float(quote_item.price)
                    quote_item.save()

            quote = self.get_or_create_quote()
            if isinstance(quote, Quote):
                quote_items, total_count, sub_total = self.get_current_quote_items_list(with_count_total=True)
                quote.total_quantity = float(total_count)
                quote.sub_total = float(sub_total)
                quote.save()

        return [quote_item, error_details, all_messages, saved, removed]

    def check_maximum_cart_quantity(self, add_qty, error_details=[]):
        current_cart_total_qty = self.get_current_quote_items_list(count_only=True)
        store_config = self.get_store_config()
        maximum_cart_total_qty = store_config.maximum_cart_qty
        maximum_cart_total_qty_in_number = round(maximum_cart_total_qty // 1)
        if maximum_cart_total_qty and ((float(current_cart_total_qty) + float(add_qty)) > maximum_cart_total_qty):
            error_details.append({
                'status': 'ERROR',
                'reason': 'MAXIMUM_QUANTITY_ADDED_TO_CART',
                'messages': ['We know you love shopping with us, but, we can process ' + 'maximum ' + str(
                    maximum_cart_total_qty_in_number) + ' items in a single order']
            })

        return error_details

    def check_current_cart_item_quantity(self, quote_item, quote_product={}):
        from ..models import QuoteItem, Product

        if isinstance(quote_item, QuoteItem):
            quote_product = quote_product if isinstance(quote_product, Product) else quote_item.product
            product_qty_info = quote_product.get_qty_info()

            cq = quote_item.quantity
            new_quantity = cq

            if product_qty_info.get_quantity():
                product_max_sale_qty = product_qty_info.get_max_sale_qty()
                product_min_sale_qty = product_qty_info.get_min_sale_qty()

                if product_min_sale_qty and not cq >= product_min_sale_qty:
                    new_quantity = product_min_sale_qty

                if product_max_sale_qty and not cq <= product_max_sale_qty:
                    new_quantity = product_max_sale_qty

            return new_quantity

        return 0

    def get_quote_items_list(self, items, quote_instance, with_count=False):
        from django.db import models

        quote_items = []
        count = 0

        if quote_instance:
            for qi in items:
                new_item = {
                    'id': qi.id,
                    'name': qi.name,
                    'sku': qi.sku,
                    'price': float(round(qi.price, 2)),
                    'quantity': round(qi.quantity),
                    'row_total': round(round(qi.price, 2) * round(qi.quantity), 2),
                    'is_free': qi.is_free_product,
                    'image': qi.product.get_default_image().get_small_url(),
                    'url': qi.product.get_absolute_url(),
                    'quote': quote_instance.id,
                    'product_id': qi.product.id,
                    'product_type': qi.product.type,
                }
                if qi.option:
                    new_item['option'] = qi.option.id if isinstance(qi.option, models.Model) else None
                    new_item['option_title'] = qi.option_title
                    new_item['option_sku'] = qi.option_sku

                count += round(qi.quantity)
                quote_items.append(new_item)

        if with_count:
            return [quote_items, count]

        return quote_items

    def get_current_quote_items_list(self, with_count=False, count_only=False, with_count_total=False, total_only=False,
                                     recheck=False):
        items = {}
        items_count = 0
        updated_price_total = 0

        quote = self.get_or_create_quote()
        if quote:
            items_collection = quote.quoteitem_set.order_by('created_at').all()

            if items_collection.count() > 0 and items_collection and recheck:
                for item in items_collection:
                    item_product = item.product
                    newq = self.check_current_cart_item_quantity(item, item_product)

                    if not newq:
                        item.delete()
                    else:
                        item.quantity = newq
                        price = item_product.get_price()
                        if item.option:
                            price = item.option.get_value().get_price()

                        item.price = price
                        item.save()

                        updated_price_total += (item.quantity * price)

                items_collection = quote.quoteitem_set.order_by('created_at').all()

                if updated_price_total > 0:
                    quote.sub_total = updated_price_total
                    quote.save()

            items, items_count = self.get_quote_items_list(items_collection, quote, True)

        if with_count:
            return [items, items_count]

        if count_only:
            return items_count

        if with_count_total:
            return [items, items_count, self.get_current_quote_items_total(items)]

        if total_only:
            return self.get_current_quote_items_total(items)

        return items

    def get_current_quote_items_total(self, items=None):
        total = 0
        items = items if items else self.get_current_quote_items_list()
        if items:
            for item in items:
                if 'price' in item:
                    total += (item['price'] * item['quantity'])

        return total

    def get_checkout_review_items(self, quote_instance=None, reload=True, final_amount_only=False, subtotal_only=False,
                                  inside_order=False):
        from ..signals import order_totals

        if reload or not self.review_items:
            from ..models import Quote

            if not quote_instance or not isinstance(quote_instance, Quote):
                quote_instance = self.customer_quote

            if not isinstance(quote_instance, Quote):
                quote_instance = self.get_or_create_quote()

            items = {
                'subtotal': {
                    'id': 'subtotal',
                    'title': 'Subtotal',
                    'value': round(float(quote_instance.sub_total), 2),
                    'order': 0,
                },
            }
            if quote_instance.shipping_method_title:
                items['shipping'] = {
                    'id': 'shipping',
                    'title': 'Shipping & Handling' + (" ("+quote_instance.shipping_method_title + ")"),
                    'value': round(float(quote_instance.shipping_price), 2),
                    'order': 1,
                }

            # from .hooks import checkout
            # for hook_def in dir(checkout):
            #     if re.search("checkout_review_items", hook_def):
            #         items = getattr(checkout, hook_def)(items)

            signal_items = order_totals.recurring_send(sender=self.__class__, totals=items, quote=quote_instance,
                                                   inside_order=inside_order)

            if signal_items and len(signal_items) == 2:
                _, items = signal_items

            if items and isinstance(items, dict):
                total = 0
                for item_k, item in items.items():
                    if isinstance(item, dict) and 'value' in item:
                        total += round(float(item['value']), 2)

                items['grand_total'] = {
                    'id': 'grand_total',
                    'title': 'Grand Total',
                    'value': round(total, 2) if total >= 0 else 0,
                    'order': 1000,
                }

            self.review_items = items

        items = self.review_items if self.review_items else {}
        if subtotal_only:
            if 'subtotal' in items and isinstance(items['subtotal'], dict):
                return items['subtotal']['value']
            else:
                return False

        if final_amount_only:
            if 'grand_total' in items and isinstance(items['grand_total'], dict):
                return items['grand_total']['value']
            else:
                return False

        return self.review_items

    def get_store_config(self):
        if self.store_config:
            return self.store_config

        from ..models import StoreConfig
        return StoreConfig.get_solo()
