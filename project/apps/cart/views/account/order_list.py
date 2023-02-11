from .base_account_view import BaseAccountView
from django.views.generic import ListView

from ...models.order import Order


class AccountOrderListView(BaseAccountView, ListView):

    model = Order
    template_name = 'frontend/pages/accounts/order_list.html'  # Default: <app_label>/<model_name>_list.html
    context_object_name = 'orders'  # Default: object_list
    paginate_by = 10

    def get_context_data(self, *, object_list=None, **kwargs):
        context = super().get_context_data(**kwargs)
        context.update(self.get_common_context_data(**kwargs))
        context['html_extra']['page_title'] = "My Orders"

        return context

    def get_queryset(self):
        super_q = super().get_queryset()
        customer = self.get_customer_extra()
        if customer:
            queryset = super_q.filter(customer=self.get_customer_extra()).all()  # Default: Model.objects.all()
        else:
            queryset = super_q.none()

        return queryset
