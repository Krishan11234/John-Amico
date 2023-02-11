from django.shortcuts import render, redirect, reverse
from django.contrib import messages
from django.http import HttpResponseRedirect

from .base_account_view import BaseAccountView
from django.views.generic import FormView, UpdateView, CreateView
from django.views.generic.edit import DeletionMixin, BaseDeleteView

from ...models.customer_address import CustomerAddress
from ...utils import helper, validation, menu
from ...forms import AddressEditForm


class AccountAddressView(BaseAccountView):
    model = CustomerAddress
    form_class = AddressEditForm
    template_name = 'frontend/pages/accounts/address_edit.html'  # Default: <app_label>/<model_name>_form.html

    def post(self, request, *args, **kwargs):
        logged_in = self.customer_login_required()
        if not isinstance(logged_in, bool) or not logged_in:
            return logged_in

        return super().post(request, *args, **kwargs)

    def get_context_data(self, *, object_list=None, **kwargs):
        context = super().get_context_data(**kwargs)
        context.update(self.get_common_context_data(**kwargs))

        return context

    def get_queryset(self):
        from ...models import CustomerExtra

        super_queryset = super().get_queryset()
        if super_queryset:
            customer_extra = self.get_customer_extra()
            if customer_extra and isinstance(customer_extra, CustomerExtra):
                return super_queryset.filter(customer_extra=customer_extra)

        return None

    def get_success_url(self):
        return reverse('cart:account_address')

    def form_valid(self, form):
        from ...models import CustomerExtra
        error = False

        customer_extra = self.get_customer_extra()
        if customer_extra and isinstance(customer_extra, CustomerExtra):
            primary_billing = form.cleaned_data.get('primary_billing', '')
            primary_shipping = form.cleaned_data.get('primary_shipping', '')

            if primary_billing or primary_shipping:
                try:
                    if primary_billing:
                        customer_extra.default_billing_address = form.instance
                    if primary_shipping:
                        customer_extra.default_shipping_address = form.instance

                    customer_extra.save()
                except Exception as e:
                    form.add_error('gender', 'Invalid data submitted')
                    error = True

        if error:
            return super().form_invalid(form)

        return super().form_valid(form)


class AccountAddressAddView(AccountAddressView, CreateView):
    def get_context_data(self, *, object_list=None, **kwargs):
        context = super().get_context_data(**kwargs)
        context['html_extra']['page_title'] = "Add new address"

        return context

    def form_valid(self, form):
        from ...models import CustomerExtra

        from django.core.exceptions import NON_FIELD_ERRORS

        customer_extra = self.get_customer_extra()
        if customer_extra and isinstance(customer_extra, CustomerExtra):
            form.instance.customer_extra_id = customer_extra.id
            form.instance.customer_id = customer_extra.get_customer().id

        try:
            return super().form_valid(form)
        except Exception as e:
            if 'Duplicate entry' in str(e):
                form.add_error(NON_FIELD_ERRORS, 'You already have saved this address in your account. '
                                                 'Please try differet address.')
            return super().form_invalid(form)


class AccountAddressEditView(AccountAddressView, UpdateView):
    def get_context_data(self, *, object_list=None, **kwargs):
        context = super().get_context_data(**kwargs)
        context['html_extra']['page_title'] = "Edit address"

        return context


class AccountAddressDeleteView(AccountAddressView, BaseDeleteView):

    def get(self, request, *args, **kwargs):
        logged_in = self.customer_login_required()
        if not isinstance(logged_in, bool) or not logged_in:
            return logged_in

        return self.post(request, *args, **kwargs)

    def post(self, request, *args, **kwargs):
        from ...models import CustomerExtra

        logged_in = self.customer_login_required()
        if not isinstance(logged_in, bool) or not logged_in:
            return logged_in

        super_object = self.get_object()
        if not super_object:
            messages.add_message(
                helper.get_request(),
                messages.ERROR,
                "We coulnd't find your address."
            )
            return HttpResponseRedirect(self.get_success_url())
        else:
            customer_extra = self.get_customer_extra()
            if customer_extra and isinstance(customer_extra, CustomerExtra):
                def_addresses = [customer_extra.default_billing_address_id, customer_extra.default_shipping_address_id]
                if super_object.id in def_addresses:
                    messages.add_message(
                        helper.get_request(),
                        messages.ERROR,
                        "You cannot remove default address."
                    )
                    return HttpResponseRedirect(self.get_success_url())

        returned = super().post(request, *args, **kwargs)
        if isinstance(returned, HttpResponseRedirect):
            messages.add_message(helper.get_request(), messages.SUCCESS, "You have successfully removed the address.")

        return returned
