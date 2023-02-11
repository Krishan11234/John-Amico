from django.shortcuts import render
from .base_account_view import BaseAccountView


class AccountAddressView(BaseAccountView):
    def get(self, request, *args, **kwargs):
        from ...models import CustomerAddress, Order

        logged_in = self.customer_login_required()
        if not isinstance(logged_in, bool) or not logged_in:
            return logged_in

        customer_extra = self.get_customer_extra()
        if customer_extra:
            context = self.get_common_context_data()
            context['html_extra']['page_title'] = "Address Book"
            context['default_billing_address'] = customer_extra.get_default_billing_address()
            context['default_shipping_address'] = customer_extra.get_default_shipping_address()

            not_in = []
            if context['default_billing_address'] and isinstance(context['default_billing_address'], CustomerAddress):
                not_in.append(context['default_billing_address'].id)

            if context['default_shipping_address'] and isinstance(context['default_shipping_address'], CustomerAddress):
                not_in.append(context['default_shipping_address'].id)

            address = CustomerAddress.objects.filter(customer_extra=customer_extra).exclude(id__in=not_in)
            if address.exists():
                context['addresses'] = address.all()
            else:
                context['addresses'] = {}

            response = render(request, 'frontend/pages/accounts/address_list.html', context)

            return response
