from django.contrib.auth.models import User
from ..base_view import BaseView
from ...utils import helper, validation, menu
from ...models.customer_extras import CustomerExtra


class BaseAccountView(BaseView):

    def get(self, request, *args, **kwargs):
        logged_in = self.customer_login_required()
        if not isinstance(logged_in, bool) or not logged_in:
            return logged_in

        return super().get(request, *args, **kwargs)

    def get_customer_extra(self):
        customer_extra = None
        user = helper.get_current_customer()

        if isinstance(user, User):
            customer_q = CustomerExtra.objects.filter(customer=user)
            if not customer_q.exists():
                if hasattr(user, 'id') and user.id:
                    customer_extra = CustomerExtra.objects.create(customer=user)
            else:
                customer_extra = customer_q.get()

        return customer_extra

    def get_common_context_data(self, *, object_list=None, **kwargs):
        context = {}
        context.update(self.common_contexts())

        if 'menus' in context and isinstance(context['menus'], dict):
            if 'my_account' not in list(context['menus'].keys()):
                context['menus'].update(
                    {'my_account': menu.Menu().get_single_menu('my_account')}
                )

        context['cart'] = self.cart_items_context()
        context['customer_extra'] = self.get_customer_extra()

        return context
