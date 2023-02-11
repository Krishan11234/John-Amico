import re
from django.shortcuts import render
from ..base_view import BaseView
from ...utils import cart
from ...utils.static import SHIPPABLE_PRODUCT_TYPES


class ShippingSubView(BaseView):

    def get_html(self):
        from django.template import loader

        context = {}

        carriers_list, shipping_methods, free_shipping_methods, default_method = self.get_shipping_carrier_methods()

        context['shipping_carriers'] = carriers_list
        context['shipping_methods'] = shipping_methods
        context['free_shipping_methods'] = free_shipping_methods
        context['default_method'] = default_method

        template_name = 'frontend/parts/page/checkout/form_shipping.html'
        content = loader.render_to_string(template_name, context)

        return content

    def get_shipping_carrier_methods(self, quote_instance=None, shipping_address=None):
        from ....cart import models
        from ... import signals

        existing_carriers_list = []
        free_methods_list = {}
        methods_list = {}
        carriers_methods_list = {}
        default_method = False

        for m in dir(models):
            if re.search("Shipping\w+Method", m):
                existing_carriers_list.append(m)

        # cart_items_price = get_shippable_quote_items_total()

        for sc in existing_carriers_list:
            sc_obj = getattr(models, sc)()
            if sc_obj:
                if sc_obj.is_enabled:
                    methods = sc_obj.get_methods(quote_instance=quote_instance, shipping_address=shipping_address)

                    if isinstance(methods, list):
                        for method in methods:
                            if method not in methods_list:
                                methods_list[method] = {}
                            methods_list[method]['price'] = sc_obj.get_price()
                            methods_list[method]['method'] = method
                            methods_list[method]['model'] = sc
                    elif isinstance(methods, dict):
                        for method in methods.keys():
                            if method not in methods_list:
                                methods_list[method] = {}
                            methods_list[method]['method'] = method
                            methods_list[method]['price'] = methods[method]['price'] if 'price' in methods[method] else 0
                            methods_list[method]['name'] = methods[method]['name']
                            methods_list[method]['name_with_carrier_title'] = sc_obj.title + ' - ' + methods[method]['name']
                            methods_list[method]['model'] = sc

                    free_methods_list[sc] = sc_obj.get_freeshipping_method()

                    if sc not in carriers_methods_list:
                        carriers_methods_list[sc] = {}
                    carriers_methods_list[sc]['title'] = sc_obj.title
                    carriers_methods_list[sc]['methods'] = methods_list
                    carriers_methods_list[sc]['code_prefix'] = sc_obj.get_prefix()

        if methods_list:
            signal_methods_list = signals.shipping_methods.recurring_send(sender=self.__class__, methods=methods_list,
                                                                   quote=quote_instance, shipping_address=shipping_address)

            if signal_methods_list and len(signal_methods_list) == 2:
                _, methods_list = signal_methods_list

            if not methods_list or not isinstance(methods_list, dict):
                methods_list = {}
                default_method = {}
            else:
                default_method = list(methods_list.keys())[0]

        return [carriers_methods_list, methods_list, free_methods_list, default_method]

    def get_shippable_quote_items_total(self):
        quote_items = self.cart_utils.get_current_quote_items_list()
        total = 0

        if quote_items:
            for qi in quote_items:
                if 'price' in qi:
                    if 'product_type' in qi and qi['product_type'] in SHIPPABLE_PRODUCT_TYPES:
                        total += (qi['price'] * qi['quantity'])

        return total

    # def get_
