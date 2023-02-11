import datetime
from django.shortcuts import render, redirect
from django.contrib import messages
from django.views.generic import ListView
from ....utils import helper
from ....views.account.base_account_view import BaseAccountView
from ..models import AutoshipRequest


class AutoshipRequests(BaseAccountView, ListView):
    model = AutoshipRequest
    template_name = 'cart/modules/autoship_order/templates/frontend/pages/list.html'
    context_object_name = 'autoship_requests'  # Default: object_list
    paginate_by = 10

    def get_context_data(self, *, object_list=None, **kwargs):
        context = super().get_context_data(**kwargs)
        context.update(self.get_common_context_data(**kwargs))
        context['html_extra']['page_title'] = "Autoship Requests"

        return context

    def get_queryset(self):
        super_q = super().get_queryset()
        customer = self.get_customer_extra()
        if customer:
            queryset = super_q.filter(customer=self.get_customer_extra(), status__in=[1]).order_by('created_at').all()
        else:
            queryset = super_q.none()

        return queryset


class AutoshipRequestsConfigure(BaseAccountView):
    model = AutoshipRequest
    template = 'cart/modules/autoship_order/templates/frontend/pages/configure.html'

    def get(self, request, *args, **kwargs):
        logged_in = self.customer_login_required()
        if not isinstance(logged_in, bool) or not logged_in:
            return redirect('cart:account_login')

        autoship_id = kwargs['autoship_id'] if 'autoship_id' in kwargs else False
        if autoship_id and isinstance(autoship_id, int):
            queryset = self.get_queryset(autoship_id)
            if queryset.exists():
                autoship = queryset.get()
                # autoship.get_next_order_placing_time()
                autoship_request = autoship.get_autoship_request_full()

                if not autoship_request['orderable_products']:
                    messages.add_message(request, messages.ERROR, "No products found to configure!")
                else:
                    context = self.get_context_data(queryset)
                    context['ar'] = autoship_request

                    return render(request, self.template, context)
            else:
                messages.add_message(request, messages.ERROR, "Illegal ID submitted in the request!")
        else:
            messages.add_message(request, messages.ERROR, "Illegal ID submitted in the request!")

        return redirect('cart:autoship_requests')

    def post(self, request, *args, **kwargs):
        logged_in = self.customer_login_required()
        if not isinstance(logged_in, bool) or not logged_in:
            return redirect('cart:account_login')

        error = False
        autoship_id = kwargs['autoship_id'] if 'autoship_id' in kwargs else False

        if autoship_id and isinstance(autoship_id, int):
            queryset = self.get_queryset(autoship_id)
            if queryset.exists():
                autoship_request = queryset.get().get_autoship_request_full()

                if request.POST:
                    statues = helper.get_html_input_dict(request.POST, 'autoshipProd')
                    quantities = helper.get_html_input_dict(request.POST, 'autoshipProdQty')

                    enabled_prods = {}
                    enabled_prod_ids = []

                    if statues and isinstance(statues, dict):
                        for prod_id, enabled in statues.items():
                            if int(enabled):
                                qty = int(quantities[prod_id]) if prod_id in quantities and quantities[prod_id]\
                                    .isnumeric() else False
                                if qty:
                                    enabled_prod_ids.append(int(prod_id))
                                    enabled_prods[int(prod_id)] = qty

                        if enabled_prods:
                            removable_prods = list(set(autoship_request['orderable_products'].keys()) - set(enabled_prod_ids))
                            for prod_id, prod in autoship_request['orderable_products'].items():
                                if prod_id in enabled_prod_ids:
                                    autoship_request['orderable_products'][prod_id].quantity = enabled_prods[prod_id]
                                    autoship_request['orderable_products'][prod_id].save()

                                if prod_id in removable_prods:
                                    autoship_request['orderable_products'][prod_id].status = 2
                                    autoship_request['orderable_products'][prod_id].save()

                            messages.add_message(request, messages.SUCCESS, "Successfully saved the changes")
                        else:
                            error = True
                            messages.add_message(request, messages.ERROR, "You must have at least 1 enabled item "
                                                                          "with a minimum of 1 quantity to be shipped "
                                                                          "with this Auto Shipment request")
                    else:
                        error = True
                        messages.add_message(request, messages.ERROR, "You must have at least 1 item to be shipped "
                                                                      "with this Auto Shipment request")

                    if error:
                        context = self.get_context_data(queryset)
                        context['ar'] = autoship_request

                        if quantities:
                            for prod_id, qty in quantities.items():
                                context['ar']['orderable_products'][int(prod_id)].quantity = float(qty)

                        return render(request, self.template, context)

                #     { % if ar_post %}
                #     $autoshipProd['status'] = isset($autoshipRequestPost['autoshipProd'][$autoshipProd[
                #         'autoship_product_id']]) ? 1: 0;
                #     $autoshipProd['qty'] = isset($autoshipRequestPost['autoshipProdQty'][$autoshipProd[
                #         'autoship_product_id']]) ? $autoshipRequestPost['autoshipProdQty'][$autoshipProd[
                #         'autoship_product_id']]: $autoshipProd['qty'];
                # }
                # ? >

        return self.get(request, *args, **kwargs)

    def get_context_data(self, queryset, **kwargs):
        context = {}
        context.update(self.get_common_context_data(**kwargs))
        context['html_extra']['page_title'] = "Update My Autoship Request Products"

        if queryset.exists():
            context['ar'] = queryset.get().get_autoship_request_full()

        return context

    def get_queryset(self, autoship_id):
        customer = self.get_customer_extra()
        if customer:
            queryset = self.model.objects.filter(customer=self.get_customer_extra(), status__in=[1], id=autoship_id)
        else:
            queryset = self.model.objects.none()

        return queryset
