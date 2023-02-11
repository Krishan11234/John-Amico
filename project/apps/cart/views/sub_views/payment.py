import re
from django.template import loader
from django.shortcuts import render
from ..base_view import BaseView
from ...utils import cart
from ...utils.static import SHIPPABLE_PRODUCT_TYPES


class PaymentSubView(BaseView):

    def get_html(self):
        context = {}

        payment_methods, default_method = self.get_payment_methods()

        context['payment_methods'] = payment_methods
        context['default_method'] = default_method

        template_name = 'frontend/parts/page/checkout/form_payment.html'
        content = loader.render_to_string(template_name, context)

        return content

    def get_payment_methods(self, quote=None, with_html=True):
        from ....cart import models

        existing_payment_list = []
        free_methods_list = {}
        methods_list = {}
        carriers_methods_list = {}
        default_method = False

        for m in dir(models):
            if re.search("Payment\w+Method", m):
                existing_payment_list.append(m)

        # cart_items_price = get_shippable_quote_items_total()

        for sc in existing_payment_list:
            sc_obj = getattr(models, sc)()
            if sc_obj:
                if sc_obj.is_active(quote):
                    code = sc_obj.get_code()
                    if sc not in methods_list:
                        methods_list[code] = {}
                    methods_list[code]['obj'] = sc_obj
                    methods_list[code]['title'] = sc_obj.title

                    if with_html:
                        methods_list[code]['html'] = self.render_method_html(sc_obj, sc_obj.get_form_html_path())

        if methods_list:
            default_method = list(methods_list.keys())[0]

        return [methods_list, default_method]

    def get_payment_method(self, method_code, with_html=False):
        methods_list, default_method = self.get_payment_methods(with_html)
        if isinstance(methods_list, dict):
            if method_code in list(methods_list.keys()):
                return methods_list[method_code]

        return method_code

    def render_method_html(self, method_obj, template_file):
        content = ''

        if method_obj and template_file:
            context = method_obj.get_template_context()
            content += loader.render_to_string(template_file, context)

        return content
