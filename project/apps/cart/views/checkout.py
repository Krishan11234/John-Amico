import json, datetime, time
from django.shortcuts import render, redirect, reverse, resolve_url
from django.http import HttpResponse, HttpResponseBadRequest, JsonResponse
from django.contrib import messages
from django.contrib.auth.models import User, AnonymousUser, Group
from django.contrib.auth import authenticate, login, logout
from django.forms.models import model_to_dict

from .base_view import BaseView
from .sub_views import ShippingSubView, PaymentSubView

from ..utils import helper, static, cart, validation, OrderUtil

from .. import signals


class CheckoutView(BaseView):
    em = []
    post_submit_types = ['saveOrder', 'is_valid_email', 'is_valid_referrer', 'deleteproduct', 'addproduct', 'minusproduct', 'save_shipping']
    order_utils = OrderUtil()

    def get(self, request, submit_type=None, *args, **kwargs):

        if submit_type == 'saveOrder':
            return redirect('cart:checkout')

        if submit_type in self.post_submit_types:
            return HttpResponseBadRequest()

        if request.path == '/checkout/onepage/success':
            return self.checkout_success(request, *args, **kwargs)

        context = self.common_contexts(request)
        # context = self.common_contexts(request, refresh=True, recheck=True)

        if 'cart' not in context or 'items' not in context['cart']:
            return redirect('cart:checkout_cart')
        if not context['cart']['items']:
            return redirect('cart:checkout_cart')

        context['html_extra']['page_title'] = "Checkout"
        context['cart']['review_total_items'] = self.cart_utils.get_checkout_review_items()
        quote = self.cart_utils.get_or_create_quote()
        checkout_data = {}

        from ..models import QuoteAddress, CustomerAddress, CustomerExtra, TblMember

        if request.session.get('CHECKOUT_POST_DATA', False):
            checkout_data = request.session['CHECKOUT_POST_DATA']
            checkout_data = checkout_data if 'quote_id' in checkout_data and checkout_data['quote_id'] == quote.id else {}

            request.session['CHECKOUT_POST_DATA'] = {}
            request.session.modified = True
        else:
            from django.forms.models import model_to_dict

            # quote = self.cart_utils.get_or_create_quote()
            quote_addresses = QuoteAddress.objects.filter(quote=quote)
            quote_addresses = quote_addresses.all() if quote_addresses.exists() else False

            if quote_addresses and not isinstance(quote_addresses, bool):
                for qa in quote_addresses:
                    if 'billing' not in checkout_data and qa.address_type == 'billing':
                        checkout_data['billing'] = model_to_dict(qa)
                        if 'zip' in checkout_data['billing']:
                            checkout_data['billing']['postcode'] = checkout_data['billing']['zip']

                    if 'shipping' not in checkout_data and qa.address_type == 'shipping':
                        checkout_data['shipping'] = model_to_dict(qa)
                        if 'zip' in checkout_data['shipping']:
                            checkout_data['shipping']['postcode'] = checkout_data['shipping']['zip']

            checkout_data['comment'] = quote.comment

        if checkout_data:
            context['data'] = {}
            context['data']['billing'] = checkout_data['billing'] if 'billing' in checkout_data else {}
            context['data']['shipping'] = checkout_data['shipping'] if 'shipping' in checkout_data else {}
            context['data']['comment'] = checkout_data['comment'] if 'comment' in checkout_data else ''

        context['saved_addresses'] = {}
        if helper.is_customer_logged_in():
            context['customer'] = CustomerExtra.objects.filter(customer=helper.get_current_customer()).get()
            context['data']['billing']['customer_addresses'] = context['customer'].get_addresses('billing')
            context['data']['shipping']['customer_addresses'] = context['customer'].get_addresses('shipping')
        elif helper.is_professional_logged_in():
            # customer = helper.get_current_member()
            # member = TblMember.objects.filter(int_member_id=customer.id).get()
            # context['customer'] = member.convert_to_django_user_model()
            context['customer'] = helper.get_current_member()
            member = context['customer'].get_member()
            context['data']['billing']['customer_addresses'] = member.get_addresses('billing')
            context['data']['shipping']['customer_addresses'] = member.get_addresses('shipping')

        context['shipping_method_html'] = ShippingSubView().get_html()
        context['payment_method_html'] = PaymentSubView().get_html()
        context['extra'] = context['extra'] if 'extra' in context else {}
        context['extra']['address_types'] = dict(static.ADDRESS_TYPE_CHOICES).keys()
        context['extra']['countries'] = dict(static.COUNTRIES)
        context['extra']['us_states'] = dict(static.US_STATES)
        context['checkout_url'] = reverse('cart:checkout')
        context['review_extra_html'] = ''

        extra_htmls = signals.checkout_review_extra_html.send(sender=self.__class__, context=context, quote=quote, request=request)

        if extra_htmls:
            for handler, extra_html in extra_htmls:
                context['review_extra_html'] += extra_html

        return render(request, 'frontend/pages/checkout.html', context)

    def checkout_success(self, request, *args, **kwargs):
        from ..models import Order

        last_order_id = request.session.get('last_order_id', False)

        request.session['last_order_id'] = False
        request.session.modified = True

        # last_order_id = '100000113'

        if last_order_id:
            context = self.common_contexts(request)
            context['html_extra']['page_title'] = "Thank you for your order"
            context['order_id'] = last_order_id

            if helper.is_customer_logged_in():
                context['customer'] = helper.get_current_customer()
                order_q = Order.objects.filter(increment_id=last_order_id)
                if order_q.exists():
                    context['order'] = order_q.get()

            return render(request, 'frontend/pages/order_success.html', context)

        return redirect('cart:checkout_cart')

    def post(self, request, submit_type, *args, **kwargs):
        if submit_type in self.post_submit_types:
            response = self.method_switch(request, submit_type, *args, **kwargs)
        else:
            response = self.page_404()

        return response

    def method_switch(self, request, request_type, *args, **kwargs):
        response = None

        if request_type in self.post_submit_types:
            method_name = "handle__" + request_type
            method = getattr(self, method_name)

            if method:
                response = method(request, *args, **kwargs)

        return response

    def handle__addproduct(self, request, *args, **kwargs):
        return self.handle_update_cart_product(request, 'add', *args, **kwargs)

    def handle__minusproduct(self, request, *args, **kwargs):
        return self.handle_update_cart_product(request, 'minus', *args, **kwargs)

    def handle__deleteproduct(self, request, *args, **kwargs):
        return self.handle_update_cart_product(request, 'remove', *args, **kwargs)

    def handle__is_valid_email(self, request, *args, **kwargs):
        if request.POST and 'billing[email]' in request.POST:
            email = request.POST.get('billing[email]', False)

            if email:
                if 'emails_validity' not in request.session:
                    request.session['emails_validity'] = {}
                    request.session.modified = True
                else:
                    if helper.is_user_types_logged_in(condition_type='or'):
                        validity = False
                    else:
                        validity = request.session['emails_validity'].get(email, '')

                    if validity:
                        return HttpResponse(validity)

                validated_data = validation.validate_account_create_data({'email': email}, 'email')
                if not validated_data['success']:
                    request.session['emails_validity'][email] = 'false'
                    request.session.modified = True
                    return HttpResponse("false")
                else:
                    exclude = {}
                    customer = None
                    if helper.is_customer_logged_in():
                        customer = helper.get_current_customer()
                    elif helper.is_professional_logged_in():
                        customer = helper.get_current_member()

                    if customer:
                        exclude.update({'email': customer.email})

                    user = User.objects.filter(email=email).exclude(**exclude)
                    if user.exists():
                        request.session['emails_validity'][email] = 'false'
                        request.session.modified = True
                        return HttpResponse("false")
                    else:
                        request.session['emails_validity'][email] = 'true'
                        request.session.modified = True

        return HttpResponse("true")

    def handle__is_valid_referrer(self, request, *args, **kwargs):
        from ..models import TblMember

        if request.POST and 'billing[ja_referrer_id]' in request.POST:
            referrer = request.POST.get('billing[ja_referrer_id]', False)

            if referrer:
                if 'referrer_validity' not in request.session:
                    request.session['referrer_validity'] = {}
                    request.session.modified = True
                else:
                    validity = request.session['referrer_validity'].get(referrer, '')
                    if validity:
                        return HttpResponse(validity)

                member = TblMember.objects.filter(amico_id=referrer)
                if member.exists():
                    request.session['referrer_validity'][referrer] = 'true'
                    request.session.modified = True
                    return HttpResponse("true")
                else:
                    request.session['referrer_validity'][referrer] = 'false'
                    request.session.modified = True

            else:
                return HttpResponse("true")

        return HttpResponse("false")

    def handle__save_shipping(self, request, *args, **kwargs):
        m = {'error': [], 'info': [], 'success': 'false'}

        self.em = []
        if request.POST and 'shipping_method' in request.POST:
            billing_data = helper.get_html_input_dict(request.POST, 'billing')
            quote_billing_address, quote_billing_address_data = self.quote_address_save(billing_data, 'billing')
            if 'different_shipping' in billing_data:
                if isinstance(billing_data['different_shipping'], str) and billing_data['different_shipping'].isnumeric():
                    billing_data['different_shipping'] = int(billing_data['different_shipping'])
                if billing_data['different_shipping']:
                    shipping_data = helper.get_html_input_dict(request.POST, 'shipping')
                    quote_shipping_address, quote_shipping_address_data = self.quote_address_save(shipping_data, 'shipping')
                else:
                    quote_shipping_address, quote_shipping_address_data = self.quote_address_save(billing_data, 'shipping', display_error=False)
            else:
                quote_shipping_address, quote_shipping_address_data = self.quote_address_save(billing_data, 'shipping', display_error=False)

            shipping_method, shipping_price, quote = self.quote_shipping_save(request.POST['shipping_method'].strip())

            if shipping_method:
                m['success'] = 'true'
                m['items_html'] = self.order_review_items_html_maker(request, reload=True)

        else:
            self.em.append("Shipping method is not defined")

        if self.em:
            m['error'] = self.em

        return JsonResponse(m, content_type='application/json', safe=False)

    def handle__saveOrder(self, request, *args, **kwargs):
        print("#1:  " + str(int(round(time.time() * 1000))))
        from ..models import CustomerExtra, CustomerAddress, AuthnetcimCards, MemberExtra

        self.em = []

        quote = self.cart_utils.get_or_create_quote()

        validated_payment_data = False
        payment_handler = False
        payment_methods = {}
        customer = False

        billing_data = helper.get_html_input_dict(request.POST, 'billing')
        comment = billing_data['onestepcheckout_comment']
        del billing_data['onestepcheckout_comment']

        if not helper.is_user_types_logged_in(condition_type='or'):
            user_data_validated = self.validate_customer_create_data(request, billing_data)
        else:
            customer = helper.get_current_member() if helper.is_professional_logged_in() else helper.get_current_customer()

        shipping_data = billing_data if 'different_shipping' not in billing_data or not billing_data['different_shipping'] \
            else helper.get_html_input_dict(request.POST, 'shipping')

        quote_billing_address, quote_billing_address_data = self.quote_address_save(billing_data, 'billing')

        if 'different_shipping' in billing_data and billing_data['different_shipping']:
            quote_shipping_address, quote_shipping_address_data = self.quote_address_save(shipping_data, 'shipping')
        else:
            quote_shipping_address = quote_billing_address
            quote_shipping_address_data = quote_billing_address_data
            quote_shipping_address_data['address_type'] = 'shipping'

        print("#2:  " + str(int(round(time.time() * 1000))))

        if self.em:
            return self.reload_with_message(request, {'billing': billing_data, 'shipping': shipping_data,
                                                      'comment': comment, 'quote_id': quote.id})

        shipping_method, shipping_price, quote = self.quote_shipping_save(request.POST['shipping_method'].strip(), quote, quote_shipping_address)

        payment_method = request.POST['payment[method]'].strip()
        if payment_method:
            payment_methods, default_method = PaymentSubView().get_payment_methods(with_html=False)

            if payment_method not in list(payment_methods.keys()):
                payment_method = False
                self.em.append('Selected payment method does not exist')
        else:
            self.em.append('Selected payment method does not exist')

        print("#3:  " + str(int(round(time.time() * 1000))))

        # If there's error in the submitted Data, redirect Customer tot he Checkout page with the Errors
        if self.em:
            return self.reload_with_message(request, {'billing': billing_data, 'shipping': shipping_data,
                                                      'comment': comment, 'quote_id': quote.id})

        # If there's no error in the submitted Data, process the payment
        if payment_methods and isinstance(payment_methods, dict) and 'obj' in payment_methods[payment_method]:
            payment_handler = payment_methods[payment_method]['obj'].get_handler_class()

        payment_data = helper.get_html_input_dict(request.POST, 'payment')
        if payment_handler:
            if quote_billing_address:
                address_set = payment_handler.set_address(quote_billing_address)
                if isinstance(address_set, dict) and 'success' in address_set and not address_set['success']:
                    self.em += "<br/>".join(address_set['message'].values())

            if quote_shipping_address:
                address_set = payment_handler.set_address(quote_shipping_address, 'shipping')
                if isinstance(address_set, dict) and 'success' in address_set and not address_set['success']:
                    self.em += "<br/>".join(address_set['message'].values())

            if helper.is_user_types_logged_in(condition_type='or'):
                if helper.is_professional_logged_in():
                    member = helper.get_current_member()
                    member_extra_q = MemberExtra.objects.filter(tbl_member_id=member.tbl_member_id)
                    if member_extra_q.exists():
                        member_extra = member_extra_q.get()
                        payment_data['customer_extra_obj'] = member_extra

                if helper.is_customer_logged_in():
                    customer = helper.get_current_customer()
                    customer_extra_q = CustomerExtra.objects.filter(customer=customer)
                    if customer_extra_q.exists():
                        customer_extra = customer_extra_q.get()
                        payment_data['customer_extra_obj'] = customer_extra

            validated_payment_data = payment_handler.validate_payment_data(payment_data)
            if isinstance(validated_payment_data, dict):
                if 'success' in validated_payment_data:
                    if not validated_payment_data['success']:
                        for field, mes in validated_payment_data['message'].items():
                            for m in mes:
                                self.em.append('Payment Data: ' + field + ": " + m)
                    else:
                        if 'data' in validated_payment_data and validated_payment_data['data'] \
                                and isinstance(validated_payment_data['data'], dict):

                            payment_data.update(validated_payment_data['data'])
                            payment_handler.set_inputs(payment_data)

        print("#4:  " + str(int(round(time.time() * 1000))))

        if self.em:
            return self.reload_with_message(request, {'billing': billing_data, 'shipping': shipping_data,
                                                      'comment': comment, 'quote_id': quote.id})

        if True:
        # try:
            if payment_handler and payment_data:
                customer, billing_address, shipping_address = self.get_or_create_customer(request,
                                                            {
                                                                'billing_data': billing_data,
                                                                'billing_address': quote_billing_address,
                                                                'shipping_address': quote_shipping_address
                                                            }
                                                       )
                if isinstance(customer, CustomerExtra) or isinstance(customer, MemberExtra):
                    from ..models import Order, OrderPayment, PaymentTransaction

                    if not (helper.is_user_types_logged_in(condition_type='or')) and isinstance(customer, CustomerExtra):
                        login(request, customer.get_customer(), backend='django.contrib.auth.backends.ModelBackend')

                    if isinstance(customer, CustomerExtra):
                        quote.customer = customer.get_customer(for_db_save=True)
                        quote.tbl_member_id = None
                    if isinstance(customer, MemberExtra):
                        quote.customer_id = None
                        quote.tbl_member = customer.tbl_member

                    quote.save()
                    super().cart_items_context(reload=True, recheck=True)

                    # total_amount = self.cart_utils.get_checkout_review_items(final_amount_only=True, reload=True)
                    # subtotal = self.cart_utils.get_checkout_review_items(subtotal_only=True, reload=False)

                    print("#5:  " + str(int(round(time.time() * 1000))))

                    # if isinstance(total_amount, int) or isinstance(total_amount, float) and total_amount >= 0:
                    order_id = self.order_utils.process_order({
                        'payment_handler': payment_handler, 'customer': customer, 'quote': quote, 'comment': comment,
                        'billing_address': billing_address, 'shipping_address': shipping_address,
                        # 'subtotal': subtotal, 'total_amount': total_amount,
                        'payment_data': payment_data, 'cart_utils': self.cart_utils
                    }, request)

                    print("#6:  " + str(int(round(time.time() * 1000))))
                    print("\n\n\n")

                    if isinstance(order_id, str):
                        self.em.append(order_id)
                    elif isinstance(order_id, int):
                        return redirect('cart:order_success')
                    else:
                        self.em.append('Could not process order with submitted data. Please try again later')
                    # else:
                    #     self.em.append('Could not process with the total amount. Please try again later')
                else:
                    self.em.append('Could not process with user. Please try again later')
            else:
                self.em.append('Could not process the payment for your order request. Please try again later')

            if self.em:
                return self.reload_with_message(request, {'billing': billing_data, 'shipping': shipping_data,
                                                          'comment': comment, 'quote_id': quote.id})
        # except Exception as e:
        #     self.em.append(e)
        #
        #     customer = customer if customer else False
        #     if not (helper.is_user_types_logged_in(condition_type='or')) and isinstance(customer, CustomerExtra):
        #         login(request, customer.get_customer(), backend='django.contrib.auth.backends.ModelBackend')
        #
        #     return self.reload_with_message(request, {'billing': billing_data, 'shipping': shipping_data,
        #                                               'comment': comment, 'quote_id': quote.id})


        # coupon_code = request.POST['coupon_code']
        # newsletter_signup = billing_data['newsletter_subscriber_checkbox']
        # autoship_enable = billing_data['jaautoship_subscriber_checkbox']
        # autoship_time = billing_data['jaautoship_subscribe_time']
        # autoship_time = autoship_time if autoship_time else 1

        # del billing_data['newsletter_subscriber_checkbox']
        # del billing_data['jaautoship_subscriber_checkbox']
        # del billing_data['jaautoship_subscribe_time']

        return redirect('cart:checkout')

    def handle_update_cart_product(self, request, request_type, *args, **kwargs):
        quote_item_id = request.POST.get('id', False)
        m = {'error': [], 'info': []}
        is_ajax = saved = False

        if quote_item_id:
            from .cart import CartView

            if request_type == 'remove':
                removed = CartView().delete_to_cart(request, int(quote_item_id), *args, **kwargs)

                if removed and isinstance(removed, bool):
                    m['success'] = 'true'
                    m['removed'] = 1
            else:
                from ..models import QuoteItem

                if request_type in ['add', 'minus']:
                    if 'is_ajax' in request.POST and int(request.POST['is_ajax']) == 1:
                        is_ajax = True

                    if quote_item_id and isinstance(quote_item_id, str) and quote_item_id.isnumeric():
                        quote_item_id = int(quote_item_id)
                        quote = self.cart_utils.get_or_create_quote(request=request, quote_id=None, create_quote=False)

                        if quote:
                            quote_item = QuoteItem.objects.filter(id=quote_item_id, quote=quote)
                            if quote_item.exists():
                                quote_item = quote_item.get()

                                quote_item, error_details, handled_messages, saved, removed = self.cart_utils \
                                    .handle_quote_item_quantity(quote_item, 1, request_type, request)

                                if error_details:
                                    for em in error_details:
                                        if is_ajax:
                                            m['error'] = em['messages']
                                        else:
                                            messages.error(request, em['messages'].join('<br/>'))
                                if handled_messages:
                                    if is_ajax:
                                        m['info'] = handled_messages
                                    else:
                                        messages.info(request, handled_messages.join('<br/>'))

                                if removed:
                                    m['removed'] = 1

                            else:
                                mess = 'Item not found'
                                if is_ajax:
                                    m['error'].append(mess)
                                else:
                                    messages.error(request, mess)

                            if saved:
                                m['success'] = 'true'
                                m['item_qty'] = quote_item.quantity
                                # m['total_qty'] = self.cart_utils.get_current_quote_items_list(count_only=True)
                                m['items_html'] = self.order_review_items_html_maker(request)

                                return JsonResponse(m, content_type='application/json', safe=False)

        m['success'] = 'false' if 'success' not in m else m['success']
        m['items_html'] = self.order_review_items_html_maker(request)

        return JsonResponse(m, content_type='application/json', safe=False)

    def quote_address_save(self, address_data, address_type, display_error=True):
        from ..models import QuoteAddress, CustomerAddress, AddressBook, CustomerExtra, TblMember

        quote = cart.CartUtils().get_or_create_quote()
        quote_address = customer_address = False

        if 'address_id' in address_data and address_data['address_id'] and address_data['address_id'].isnumeric():
            if helper.is_customer_logged_in():
                customer = helper.get_current_customer()
                customer_address = CustomerAddress.objects.filter(customer=customer, id=address_data['address_id'])
                if customer_address.exists():
                    customer_address = model_to_dict(customer_address.first())
                    customer_address['customer_address_id'] = customer_address['id']

                    del customer_address['customer']
                    del customer_address['customer_extra']
                    del customer_address['comments']
                    del customer_address['id']
                    del customer_address['address_checksum']
                    if 'pk' in customer_address:
                        del customer_address['pk']

            elif helper.is_professional_logged_in():
                customer = helper.get_current_member()
                customer_address = AddressBook.objects.filter(customers_id=customer.customer_id, id=address_data['address_id'])
                if customer_address.exists():
                    customer_address = customer_address.first().generalized_data()
                    customer_address['member_address_id'] = customer_address['id']

                    del customer_address['id']
                    if 'pk' in customer_address:
                        del customer_address['pk']

            if not customer_address:
                if display_error:
                    self.em.append('Selected ' + address_type.capitalize() + ' Address does not exist')
                return [quote_address, {}]
            else:
                q_add_q = QuoteAddress.objects.filter(address_type=address_type, quote=quote)

                if q_add_q.exists():
                    q_add = q_add_q.first()
                    if 'customer_address_id' in customer_address and customer_address['customer_address_id']:
                        if q_add.customer_address_id != customer_address['customer_address_id']:
                            for k in customer_address:
                                setattr(q_add, k, customer_address[k])
                            q_add.save()
                    if 'member_address_id' in customer_address and customer_address['member_address_id']:
                        if q_add.member_address_id != customer_address['member_address_id']:
                            for k in customer_address:
                                setattr(q_add, k, customer_address[k])
                            q_add.save()

                    return [q_add, customer_address]

                model_data = customer_address
                model_data['quote'] = quote
                model_data['address_type'] = address_type

        else:
            customer = None
            if helper.is_customer_logged_in():
                customer = helper.get_current_customer_extra()
            elif helper.is_professional_logged_in():
                customer = helper.get_current_member_extra()

            if isinstance(customer, (CustomerExtra, TblMember)):
                if customer.no_addresses() and (address_type == 'billing') and ('email' not in address_data or not address_data['email']):
                    address_data['email'] = customer.email

            validated_address = validation.validate_address(address_data, address_type)
            if not validated_address['success']:
                if display_error:
                    for field, mes in validated_address['message'].items():
                        for m in mes:
                            self.em.append(address_type.capitalize() + ' Address: ' + str(field).capitalize() + ": " + m)

                return [quote_address, {}]

            else:
                quote_address = QuoteAddress.objects.filter(quote=quote, address_type=address_type)
                if quote_address:
                    quote_address = quote_address.first() if quote_address.exists() else False

                model_data = {
                    'firstname': address_data['firstname'],
                    'lastname': address_data['lastname'],
                    'email': address_data['email'] if 'email' in address_data else '',
                    'address1': address_data['address1'] if 'address1' in address_data else '',
                    'address2': address_data['address2'] if 'address2' in address_data else '',
                    'city': address_data['city'] if 'city' in address_data else '',
                    'state': address_data['state'] if 'state' in address_data else '',
                    'zip': address_data['zip'] if 'zip' in address_data else '',
                    'country': address_data['country'] if 'country' in address_data else '',
                    'company': address_data['company'] if 'company' in address_data else '',
                    'telephone': address_data['telephone'] if 'telephone' in address_data else '',
                    'address_type': address_type,
                    'quote': quote
                }
                if address_type == 'billing' and ('different_shipping' in address_data and address_data['different_shipping']):
                    model_data['shipping_as_billing'] = True

        if model_data:
            if 'customer_address_id' in model_data and model_data['customer_address_id']:
                quote_address_f = QuoteAddress.objects.filter(quote=quote, customer_address_id=model_data['customer_address_id'])
                if quote_address_f.exists():
                    quote_address = quote_address_f.get()

            if 'member_address_id' in model_data and model_data['member_address_id']:
                quote_address_f = QuoteAddress.objects.filter(quote=quote, member_address_id=model_data['member_address_id'])
                if quote_address_f.exists():
                    quote_address = quote_address_f.get()

            if quote_address:
                for key, value in model_data.items():
                    setattr(quote_address, key, value)

                quote_address.save()
            else:
                quote_address = QuoteAddress.objects.create(**model_data)

            return [quote_address, model_data]

    def get_or_create_customer(self, request, data, *args, **kwargs):
        from ..models import CustomerExtra, MemberExtra, CustomerAddress, QuoteAddress

        quote = self.cart_utils.get_or_create_quote()
        customer_extra = b_address = s_address = customer = None

        billing_data = data['billing_data'] if 'billing_data' in data else {}
        billing_address = data['billing_address'] if 'billing_address' in data else {}
        shipping_address = data['shipping_address'] if 'shipping_address' in data else {}

        if not helper.is_user_types_logged_in(condition_type='or'):
            validated_user_data = self.validate_customer_create_data(request, billing_data)

            if validated_user_data:
                email = validated_user_data['email'] if 'email' in validated_user_data else ''
                password = validated_user_data['password'] if 'password' in validated_user_data else ''

                if email and password:
                    user_fields = {}
                    if isinstance(billing_address, dict):
                        user_fields.update({
                            'first_name': billing_address.get('firstname', ''),
                            'last_name': billing_address.get('lastname', ''),
                        })
                    if isinstance(billing_address, QuoteAddress):
                        user_fields.update({
                            'first_name': getattr(billing_address, 'firstname'),
                            'last_name': getattr(billing_address, 'lastname'),
                        })

                    user_fields.update({'email': email, 'username': email, 'is_active': True})

                    try:
                        customer = User.objects.create(**user_fields)
                        customer.set_password(password)
                        customer.save()

                        regular_group = Group.objects.get(id=1)
                        regular_group.user_set.add(customer)

                        if 'emails_validity' in request.session:
                            if email in request.session['emails_validity']:
                                del request.session['emails_validity'][email]
                                request.session.modified = True

                    except Exception as e:
                        self.em.append("User exists with this email address ("+email+"). Please <a href='"+reverse('cart:account_login')+"'>login</a>")
                        return False

                    customer_extra = CustomerExtra.objects.create(customer=customer)

        if not customer:
            customer = helper.get_current_member() if helper.is_professional_logged_in() else helper.get_current_customer()

        if isinstance(customer, User):
            id = customer.id if customer else None
            id = id[0] if id and isinstance(id, tuple) else id

            if id:
                if helper.is_professional_logged_in():
                    quote.tbl_member_id = id
                else:
                    quote.customer = customer
                quote.save()

            if helper.is_customer_logged_in():
                customer_extra = CustomerExtra.objects.filter(customer=customer)
                if customer_extra.exists():
                    customer_extra = customer_extra.get()
                else:
                    customer_extra = CustomerExtra.objects.create(customer=customer)

            if helper.is_professional_logged_in():
                member = customer.get_member()
                customer_extra = MemberExtra.objects.filter(tbl_member=member)
                if customer_extra.exists():
                    customer_extra = customer_extra.get()
                else:
                    customer_extra = MemberExtra.objects.create(tbl_member=member)

            if isinstance(billing_address, QuoteAddress):
                billing_address_dict = model_to_dict(billing_address)
                del billing_address_dict['id']
                del billing_address_dict['quote']
                del billing_address_dict['customer_address']
                del billing_address_dict['shipping_as_billing']
                if 'member_address' in billing_address_dict:
                    del billing_address_dict['member_address']
                if 'member_address_id' in billing_address_dict:
                    del billing_address_dict['member_address_id']
                if 'address_type' in billing_address_dict:
                    del billing_address_dict['address_type']
                if 'phone_ext' in billing_address_dict:
                    del billing_address_dict['phone_ext']
                if 'phone_type' in billing_address_dict:
                    del billing_address_dict['phone_type']
                if 'mobile_no' in billing_address_dict:
                    del billing_address_dict['mobile_no']

                b_address = billing_address_dict
                if helper.is_customer_logged_in():
                    b_address['id'] = billing_address.customer_address_id

                if helper.is_customer_logged_in() and not billing_address.customer_address:
                    billing_address_dict['customer_id'] = customer.id
                    billing_address_dict['customer_extra_id'] = customer_extra.id

                    address_checksum = CustomerAddress.get_address_hash(billing_address_dict)

                    add_q = CustomerAddress().objects.filter(address_checksum=address_checksum)
                    if not add_q.exists():
                        CustomerAddress.objects.create(**billing_address_dict)

            if isinstance(shipping_address, QuoteAddress):
                shipping_address_dict = model_to_dict(shipping_address)
                del shipping_address_dict['id']
                del shipping_address_dict['quote']
                del shipping_address_dict['customer_address']
                del shipping_address_dict['shipping_as_billing']
                if 'member_address' in shipping_address_dict:
                    del shipping_address_dict['member_address']
                if 'member_address_id' in shipping_address_dict:
                    del shipping_address_dict['member_address_id']
                if 'address_type' in shipping_address_dict:
                    del shipping_address_dict['address_type']
                if 'phone_ext' in shipping_address_dict:
                    del shipping_address_dict['phone_ext']
                if 'phone_type' in shipping_address_dict:
                    del shipping_address_dict['phone_type']
                if 'mobile_no' in shipping_address_dict:
                    del shipping_address_dict['mobile_no']

                s_address = shipping_address_dict
                if helper.is_customer_logged_in():
                    s_address['id'] = shipping_address.customer_address_id

                if helper.is_customer_logged_in() and not shipping_address.customer_address:
                    shipping_address_dict['customer_id'] = customer.id
                    shipping_address_dict['customer_extra_id'] = customer_extra.id

                    address_checksum = CustomerAddress.get_address_hash(shipping_address_dict)

                    add_q = CustomerAddress().objects.filter(address_checksum=address_checksum)
                    if not add_q.exists():
                        CustomerAddress.objects.create(**shipping_address_dict)

        return [customer_extra, b_address, s_address]

    def validate_customer_create_data(self, request, data):
        password = data['customer_password'] if 'customer_password' in data else ''
        email = data['email'] if 'email' in data else ''

        if 'confirm_password' not in data or not data['confirm_password'] or not (password == data['confirm_password']):
            self.em.append('Confirm Password is not matched with Password')

        validated_data = validation.validate_account_create_data({'email': email, 'password': password})
        if not validated_data['success']:
            for field, mes in validated_data['message'].items():
                for m in mes:
                    self.em.append(str(field).capitalize() + ": " + m)

        else:
            if 'data' in validated_data:
                email = validated_data['data']['email']
                self.handle__is_valid_email(request)
                user = User.objects.filter(email=email)
                if user.exists():
                    self.em.append("User exists with this email address ("+email+"). Please <a href='"+reverse('cart:account_login')+"'>login</a>")
                else:
                    return validated_data['data']

        return False

    def quote_shipping_save(self, shipping_method, quote=None, shipping_address=None):
        from ..models import Quote
        if not quote or not isinstance(quote, Quote):
            quote = self.cart_utils.get_or_create_quote()

        if not shipping_address:
            shipping_address = quote.get_shipping_address()

        if shipping_method:
            carriers_list, shipping_methods, free_shipping_methods, default_method = ShippingSubView() \
                .get_shipping_carrier_methods(quote, shipping_address)

            if isinstance(shipping_methods, dict):
                if shipping_method not in list(shipping_methods.keys()):
                    shipping_method = False
                    self.em.append('Selected shipping method does not exist')
                else:
                    if isinstance(quote, Quote):
                        quote.shipping_method = shipping_method
                        quote.shipping_method_title = shipping_methods[shipping_method]['name_with_carrier_title']
                        quote.shipping_price = shipping_methods[shipping_method]['price']

                        quote.save()

                    return [shipping_method, quote.shipping_price, quote]
            else:
                shipping_method = False
                self.em.append('Selected shipping method does not exist')
        else:
            self.em.append('No shipping method specified')

        return ['', '', {}]

    def reload_with_message(self, request, data={}):
        if self.em:
            messages.add_message(request, messages.ERROR, '<br/>'.join(self.em))

        self.em = []

        request.session['CHECKOUT_POST_DATA'] = {}
        request.session['CHECKOUT_POST_DATA'] = data

        request.session.modified = True

        return redirect('cart:checkout')

    def order_review_items_html_maker(self, request, reload=False):
        from django.template import loader

        context = {'cart': super().cart_items_context(reload=reload)}
        context['cart']['review_total_items'] = self.cart_utils.get_checkout_review_items()
        template_name = 'frontend/parts/page/checkout/form_review_items.html'
        content = loader.render_to_string(template_name, context, request)

        return content.strip()
