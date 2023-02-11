import datetime, json, inspect

from django.shortcuts import redirect
from ..utils import helper, static, cart
from django.contrib.auth.models import User, AnonymousUser, Group
from django.forms.models import model_to_dict

from ..signals import before_order_create, after_successful_order_create


class OrderUtil(object):

    order = None
    quote = None

    def process_order(self, data, request):
        from ..models import CustomerExtra, MemberExtra, CustomerAddress, Quote, Order, PaymentTransaction, OrderPayment
        from ..utils import BasePaymentHandler, CartUtils

        request = request if request else helper.get_request()

        payment_handler = data['payment_handler'] if 'payment_handler' in data and \
                                                     isinstance(data['payment_handler'], BasePaymentHandler) else False
        customer_ext = data['customer'] if 'customer' in data and isinstance(data['customer'], CustomerExtra) or \
                                           isinstance(data['customer'], MemberExtra) else False
        quote = data['quote'] if 'quote' in data and isinstance(data['quote'], Quote) else False
        payment_data = data['payment_data'] if 'payment_data' in data and isinstance(data['payment_data'], dict) else False
        billing_address = data['billing_address'] if 'billing_address' in data and \
                                                     isinstance(data['billing_address'], dict) else False
        shipping_address = data['shipping_address'] if 'shipping_address' in data and \
                                                       isinstance(data['shipping_address'], dict) else False
        comment = data['comment'] if 'comment' in data and isinstance(data['comment'], str) else None
        cart_utils = data['cart_utils'] if 'cart_utils' in data and isinstance(data['cart_utils'], object) else None
        # total_amount = data['total_amount'] if 'total_amount' in data and (isinstance(data['total_amount'], float) or
        #                                        isinstance(data['total_amount'], int)) else False
        # subtotal = data['subtotal'] if 'subtotal' in data and (isinstance(data['subtotal'], float) or
        #                                        isinstance(data['subtotal'], int)) else False

        if not payment_handler:
            return "Payment processor not found"

        if not (customer_ext and quote and billing_address):
            return "Required data not found"

        if not cart_utils:
            cart_utils = CartUtils()
            cart_utils.customer_quote = quote

        totals = cart_utils.get_checkout_review_items(reload=True, inside_order=True)

        total_amount = float(totals['grand_total']['value']) if isinstance(totals, dict) and 'grand_total' in totals.keys() else False
        subtotal = float(totals['subtotal']['value']) if isinstance(totals, dict) and 'subtotal' in totals.keys() else False

        if not (isinstance(total_amount, int) or isinstance(total_amount, float)) and total_amount < 0:
            return "Could not process with the total amount. Please try again later"

        user = customer_ext.get_customer()

        returned_data = payment_handler.process({
            'customer_type_field': 'tbl_member' if helper.is_professional_logged_in() else 'customer',
            'customer_extra_obj': customer_ext,
            'reference': 'Quote#' + quote.token_id,
            'amount': total_amount,
            'save_card': True if 'save_card' in payment_data and payment_data['save_card'] else False
        })
        if isinstance(returned_data, list):
            payment_transaction, transaction_status_to_be, transaction_response, customer_profile_id, \
            payment_profile_id = returned_data

            if payment_transaction:
                if isinstance(payment_transaction, int) or payment_transaction.isnumeric():
                    order = self.create_order({
                        'customer': customer_ext, 'quote': quote, 'billing_address': billing_address,
                        'shipping_address': shipping_address, 'comment': comment, 'subtotal': subtotal,
                        'total_amount': total_amount, 'payment_data': payment_data,
                        # 'increment_id': order_increment_id
                    })
                    if not order or not isinstance(order, Order):
                        return 'Could not place the order. Please try again later'
                    else:
                        quote.order_id = order.id
                        quote.converted_to_order_at = datetime.datetime.now()
                        quote.save()

                        after_successful_order_create.send_robust(sender=self.__class__, order=order, request=request)

                        if request:
                            request.session['last_order_id'] = order.get_order_id()
                            request.session.modified = True

                        order_payment_transaction = PaymentTransaction.objects.create(**{
                            'order': order,
                            'amount_effected': total_amount,
                            'transaction_id': payment_transaction,
                            'transaction_type': payment_handler.get_payment_mode(),
                            'transaction_status': transaction_status_to_be,
                        })
                        order_payment = payment_handler.save_payment(order, order_payment_transaction,
                                                                     customer_profile_id, payment_profile_id)
                        if not order_payment or not isinstance(order_payment, OrderPayment):
                            return 'Could not complete the payment process for your order. Please try again later'
                        else:
                            order_payment_transaction.payment = order_payment
                            order_payment_transaction.save()

                            try:
                                order_payment_transaction.additional_information = json.dumps(transaction_response)
                                order_payment_transaction.save()
                            except Exception as e:
                                pass

                        # @TODO: Trigger Order Email for successful DB Transaction

                        return order.id
                # if isinstance(payment_transaction, str) and not payment_transaction.isnumeric():
                #     return payment_transaction
            else:
                return 'Payment failed. Please try again later'

        elif isinstance(returned_data, str):
            return returned_data
        else:
            return 'Could not process the payment. Please try again later'

    def create_order(self, data):
        from ..models import CustomerExtra, MemberExtra, CustomerAddress, Quote, Order

        # if True:
        try:
            if isinstance(data, dict):
                if 'customer' in data and (isinstance(data['customer'], CustomerExtra) or isinstance(data['customer'], MemberExtra)):
                    customer = data['customer']
                    customer_group = helper.get_current_customer_group(customer.get_customer())

                    billing_address_id = shipping_address_id = 0

                    if helper.is_customer_logged_in():
                        if 'billing_address' in data:
                            billing_address_id = data['billing_address'] if isinstance(data['billing_address'], int) else 0
                            if isinstance(data['billing_address'], CustomerAddress):
                                billing_address_id = data['billing_address'].id
                            elif isinstance(data['billing_address'], dict):
                                billing_address_id = data['billing_address']['id']
                        if 'shipping_address' in data:
                            shipping_address_id = data['shipping_address'] if isinstance(data['shipping_address'],
                                                                                         int) else 0
                            if isinstance(data['shipping_address'], CustomerAddress):
                                shipping_address_id = data['shipping_address'].id
                            elif isinstance(data['shipping_address'], dict):
                                billing_address_id = data['shipping_address']['id']

                    if 'quote' in data and isinstance(data['quote'], Quote):
                        self.quote = data['quote']
                        order_data = {
                            'status': 'pending',
                            'customer': customer if isinstance(customer, CustomerExtra) else None,
                            'customer_firstname': customer.get_customer().first_name,
                            'customer_lastname': customer.get_customer().last_name,
                            'customer_email': customer.get_customer().email,
                            'customer_gender': customer.gender if isinstance(customer, CustomerExtra) else
                            customer.int_customer.customers_gender,
                            'customer_group_id': customer_group.id if isinstance(customer.get_customer(), User) else
                            static.ANONYMOUS_GROUP_ID,
                            'order_note': data['comment'] if 'comment' in data and data['comment'] else '',
                            'quote': self.quote,
                            'shipping_amount': data['shipping_amount'] if 'shipping_amount' in data and
                                          isinstance(data['shipping_amount'], float) else self.quote.shipping_price,
                            'shipping_method': self.quote.shipping_method,
                            'shipping_method_title': self.quote.shipping_method_title,
                            'subtotal': data['subtotal'] if 'subtotal' in data and data['subtotal'] else 0,
                            'grand_total': data['total_amount'] if 'total_amount' in data and data['total_amount'] else 0,
                            'total_qty_ordered': self.quote.total_quantity,
                            'increment_id': data['increment_id'] if 'increment_id' in data else Order.fetch_new_increment_id()
                        }

                        # if billing_address_id:
                        #     order_data['billing_address_id'] = billing_address_id
                        # if shipping_address_id:
                        #     order_data['shipping_address_id'] = shipping_address_id

                        if helper.is_professional_logged_in():
                            order_data['is_professional'] = True
                            order_data['professional_id'] = customer.get_customer().amico_id

                        signal_order_data = before_order_create.recurring_send(sender=self.__class__, order_data=order_data)

                        if signal_order_data and len(signal_order_data) == 2:
                            _, order_data = signal_order_data

                        if order_data:
                            if True:
                            # try:
                                self.order = Order.objects.create(**order_data)
                                self.order.order = self.order
                                self.order.save()
                            # except Exception as e:
                            #     # print(inspect.stack())
                            #     return str(e)
                        else:
                            return "No data found!"

                        self.create_order_items()
                        self.create_order_address()

        except Exception as e:
            # print(inspect.stack())
            return str(e)

        return self.order

    def create_order_items(self):
        from ..models import Quote, Order, OrderItem

        if (self.order and isinstance(self.order, Order)) and (self.quote and isinstance(self.quote, Quote)):
            for qitem in self.quote.quoteitem_set.all():
                qitem_dict = model_to_dict(qitem)
                qitem_dict['order'] = self.order
                qitem_dict['quote_item'] = qitem
                qitem_dict['product_id'] = qitem.product.id
                del qitem_dict['id']
                del qitem_dict['remote_ip']
                del qitem_dict['quote']
                del qitem_dict['product']
                del qitem_dict['description']

                try:
                    oitem = OrderItem.objects.create(**qitem_dict)

                    product_stock = oitem.product.get_qty_info()
                    product_stock.quantity = float(product_stock.quantity) - float(oitem.quantity)
                    product_stock.save()

                except Exception as e:
                    print(inspect.stack())
                    return e

    def create_order_address(self):
        from ..models import Quote, Order
        save = False

        if (self.order and isinstance(self.order, Order)) and (self.quote and isinstance(self.quote, Quote)):
            billing_address = self.quote.get_billing_address()
            shipping_address = self.quote.get_shipping_address()
            if not shipping_address or not getattr(shipping_address, 'id'):
                shipping_address = billing_address

            a_dict = model_to_dict(billing_address)
            a_dict['quote_address'] = billing_address

            as_dict = model_to_dict(shipping_address)
            as_dict['quote_address'] = billing_address
            as_dict['address_type'] = 'shipping'

            a = self.address_save(a_dict)
            if a:
                self.order.billing_address = a
                save = True

            b = self.address_save(as_dict)
            if b:
                self.order.billing_address = b
                save = True

            if save:
                self.order.save()

    def address_save(self, address):
        from ..models import OrderAddress, CustomerAddress, AddressBook

        if isinstance(address, dict):
            address['order'] = self.order
            if helper.is_customer_logged_in() and 'customer_address_id' in address and address['customer_address_id']:
                address['customer_address_id'] = address['customer_address_id']
            if helper.is_customer_logged_in() and 'customer_address' in address and address['customer_address']:
                if isinstance(address['customer_address'], int):
                    address['customer_address_id'] = address['customer_address']
                    del address['customer_address']
                elif isinstance(address['customer_address'], CustomerAddress):
                    address['customer_address'] = address['customer_address']
                else:
                    del address['customer_address']

            if helper.is_professional_logged_in() and 'member_address_id' in address and address['member_address_id']:
                address['member_address_id'] = address['member_address_id']
            if helper.is_professional_logged_in() and 'member_address' in address and address['member_address']:
                if isinstance(address['member_address'], int):
                    address['member_address_id'] = address['member_address']
                    del address['member_address']
                elif isinstance(address['member_address'], AddressBook):
                    address['member_address'] = address['member_address']
                else:
                    del address['member_address']

            del address['id']
            del address['quote']
            del address['shipping_as_billing']

            try:
                oa = OrderAddress.objects.create(**address)
            except Exception as e:
                return e

            return oa
        return False

    def copy_from_order(self, parent_order, current_data):
        from ..models import Order, Quote, PaymentTransaction, TblMember
        from ..views.sub_views import ShippingSubView, PaymentSubView

        errors = []
        new_quote = new_order = False
        new_quote_items = {}

        new_quantities = {}
        removable_items = {}

        data_request = {}

        if current_data and isinstance(current_data, dict):
            if 'quantities' in current_data and isinstance(current_data['quantities'], dict):
                new_quantities = current_data['quantities']

        if parent_order and isinstance(parent_order, Order):
            # https://stackoverflow.com/a/4736172
            parent_order.pk = None
            new_order = parent_order

            if new_order.customer_id:
                data_request['customer'] = new_order.customer
            else:
                if new_order.professional_id:
                    tbl_q = TblMember.objects.filter(amico_id=new_order.professional_id)
                    if tbl_q.exists():
                        tbl = tbl_q.first()
                        data_request['customer'] = tbl.get_member_extra()
                    else:
                        errors.append('Professional Member: `{}` not found'.format(new_order.professional_id))
                else:
                    errors.append('No customer found in the parent order')

            if errors:
                return errors

            if new_order.quote and isinstance(new_order.quote, Quote):
                new_order.quote.pk = None
                new_quote = new_order.quote

                # Process Quote Items
                for qi in new_quote.quoteitem_set.all():
                    new_quote_items[qi.id] = qi
                    new_quote_items[qi.id].pk = None
                    new_quote_items[qi.id].row_total = qi.calculate_row_total()

                    if qi.product_id in new_quantities:
                        if qi.id in removable_items:
                            del removable_items[qi.id]

                        nq = new_quantities[qi.product_id]
                        if isinstance(nq, object) and hasattr(nq, 'quantity'):
                            new_quote_items[qi.id].quantity = float(nq.quantity)
                        elif isinstance(nq, dict) and 'quantity' in nq:
                            new_quote_items[qi.id].quantity = float(nq['quantity'])

                    else:
                        removable_items[qi.id] = qi

                # Process Quote Shipping Method

                if new_quote_items:
                    new_quote.save()
                    for nqi in new_quote_items:
                        nqi.quote = new_quote
                        nqi.save()

                    new_quote.sub_total = new_quote.calculate_subtotal()
                    new_quote.save()

                    if new_quote.shipping_method:
                        carriers_list, shipping_methods, free_shipping_methods, default_method = ShippingSubView() \
                            .get_shipping_carrier_methods(new_quote, new_quote.get_shipping_address())

                        data_request['shipping_address'] = new_quote.get_shipping_address()

                        if shipping_methods and isinstance(shipping_methods, dict):
                            if new_quote.shipping_method in list(shipping_methods.keys()):
                                shipping_price = float(shipping_methods[new_quote.shipping_method]['price'])

                                if not float(new_quote.shipping_price) == shipping_price:
                                    new_quote.shipping_price = shipping_price
                                    new_quote.save()
                            else:
                                errors.append('Shipping method: `{}` not found while processing the order'.format(new_quote.shipping_method))
                        else:
                            errors.append('No shipping method found while processing the order')

                    payment_method_code = parent_order.order_payment.method
                    if payment_method_code:
                        payment_methods, default_method = PaymentSubView().get_payment_methods(new_quote, with_html=False)

                        if payment_methods and isinstance(payment_methods, dict):
                            if payment_method_code in list(payment_methods.keys()):
                                payment_handler_dict = payment_methods[payment_method_code]
                                if 'obj' in payment_handler_dict and payment_handler_dict['obj']:
                                    if hasattr(payment_handler_dict['obj'], 'get_handler_class'):
                                        data_request['payment_handler'] = payment_handler_dict['obj'].get_handler_class()

                                        payt_q = PaymentTransaction.objects.filter(order=parent_order).order_by('created_at')
                                        if payt_q.exists():
                                            payt = payt_q.first()
                                            profiles = data_request['payment_handler'].get_payment_profiles_from_transaction(payt.transaction_id)

                                            if not profiles:
                                                errors.append('Payment data not found while processing the order')
                                        else:
                                            errors.append('Payment data not found while processing the order')
                                    else:
                                        errors.append(
                                            'Payment method: `{}` handler not found while processing the order'.format(
                                                payment_method_code))
                                else:
                                    errors.append(
                                        'Payment method: `{}` object not found while processing the order'.format(
                                            payment_method_code))
                            else:
                                errors.append('Payment method: `{}` not found while processing the order'.format(payment_method_code))
                        else:
                            errors.append('No payment method found while processing the order')
                    else:
                        errors.append('No payment method found in the parent order')

                    data_request['quote'] = new_quote
                    data_request['billing_address'] = new_quote.get_billing_address()

        if errors:
            return errors

        return data_request



