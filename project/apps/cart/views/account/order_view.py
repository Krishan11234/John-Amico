from django.contrib.auth import authenticate, login, logout
from django.shortcuts import render, redirect, reverse
from django.contrib import messages

from .base_account_view import BaseAccountView
from django.views.generic import DetailView

from django.contrib.auth.models import User
from ...models.order import Order

# class DetailViewAltered(DetailView):
#


class AccountOrderView(BaseAccountView, DetailView):
    model = Order
    template_name = 'frontend/pages/accounts/order_view.html'  # Default: <app_label>/<model_name>_detail.html

    def get_context_data(self, *, object_list=None, **kwargs):
        context = super().get_context_data(**kwargs)
        context.update(self.get_common_context_data(**kwargs))
        context['html_extra']['page_title'] = "Order #" + self.get_object().get_order_id()

        order_object = self.get_object()
        context['extra']['invoices_count'] = order_object.order_invoices.count()
        context['extra']['shipments_count'] = order_object.order_shipments.count()
        context['extra']['refunds_count'] = order_object.order_credit_memos.count()

        return context

    def get_queryset(self):
        super_q = super().get_queryset()
        customer = self.get_customer_extra()
        if customer:
            queryset = super_q.filter(customer=self.get_customer_extra())  # Default: Model.objects.all()
        else:
            queryset = super_q.none()

        return queryset


class AccountOrderInvoiceView(AccountOrderView):
    template_name = 'frontend/pages/accounts/order_invoice_view.html'

    def get(self, request, *args, **kwargs):
        logged_in = self.customer_login_required()
        if not isinstance(logged_in, bool) or not logged_in:
            return logged_in

        order_object = self.get_object()
        if order_object.order_invoices.count() < 1:
            return redirect('cart:account_order_view', order_object.id)

        return super().get(request, *args, **kwargs)

    def get_context_data(self, *, object_list=None, **kwargs):
        context = super().get_context_data(**kwargs)
        context['html_extra']['page_title'] = "Invoices for Order #" + self.get_object().get_order_id()

        return context


class AccountOrderShipmentView(AccountOrderView):
    template_name = 'frontend/pages/accounts/order_shipment_view.html'

    def get(self, request, *args, **kwargs):
        logged_in = self.customer_login_required()
        if not isinstance(logged_in, bool) or not logged_in:
            return logged_in

        order_object = self.get_object()
        if order_object.order_shipments.count() < 1:
            return redirect('cart:account_order_view', order_object.id)

        return super().get(request, *args, **kwargs)

    def get_context_data(self, *, object_list=None, **kwargs):
        context = super().get_context_data(**kwargs)
        context['html_extra']['page_title'] = "Shipments for Order #" + self.get_object().get_order_id()

        return context


class AccountOrderRefundsView(AccountOrderView):
    template_name = 'frontend/pages/accounts/order_refund_view.html'

    def get(self, request, *args, **kwargs):
        logged_in = self.customer_login_required()
        if not isinstance(logged_in, bool) or not logged_in:
            return logged_in

        order_object = self.get_object()
        if order_object.order_credit_memos.count() < 1:
            return redirect('cart:account_order_view', order_object.id)

        return super().get(request, *args, **kwargs)

    def get_context_data(self, *, object_list=None, **kwargs):
        context = super().get_context_data(**kwargs)
        context['html_extra']['page_title'] = "Refunds for Order #" + self.get_object().get_order_id()

        return context
