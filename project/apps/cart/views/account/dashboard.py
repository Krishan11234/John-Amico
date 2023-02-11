from django.shortcuts import render, redirect
from django.contrib import messages

from .base_account_view import BaseAccountView


class AccountDashboardView(BaseAccountView):
    em = []

    def get(self, request, *args, **kwargs):
        from ...models import CustomerAddress, Order

        logged_in = self.customer_login_required()
        if not isinstance(logged_in, bool) or not logged_in:
            return logged_in

        #try:
        customer_extra = self.get_customer_extra()

        if customer_extra:
            context = self.get_common_context_data()
            context['html_extra']['page_title'] = "My Dashboard"
            context['default_billing_address'] = customer_extra.get_default_billing_address()
            context['default_shipping_address'] = customer_extra.get_default_shipping_address()

            orders = Order.objects.filter(customer=customer_extra).order_by('-increment_id', '-created_at')[:5]
            if orders.exists():
                context['orders'] = orders.all()
            else:
                context['orders'] = {}

            address = CustomerAddress.objects.filter(customer_extra=customer_extra)
            if address.exists():
                context['address'] = address.all()
            else:
                context['address'] = {}

            response = render(request, 'frontend/pages/accounts/dashboard.html', context)

            return response
        else:
            messages.add_message(request, messages.ERROR, "Something went wrong while fetching your information!")
            return redirect('cart:home')
        # except Exception as e:
        #     messages.add_message(request, messages.ERROR, e)
        #     return redirect('cart:home')
