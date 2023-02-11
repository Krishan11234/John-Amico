from django.shortcuts import reverse

from .base_account_view import BaseAccountView
from django.views.generic import FormView, UpdateView, CreateView

from django.contrib.auth.models import User
from ...models.customer_extras import CustomerExtra

from ...forms import AccountEditForm


class AccountEditView(BaseAccountView, UpdateView):
    model = User
    form_class = AccountEditForm
    template_name = 'frontend/pages/accounts/account_edit.html'  # Default: <app_label>/<model_name>_form.html

    def post(self, request, *args, **kwargs):
        logged_in = self.customer_login_required()
        if not isinstance(logged_in, bool) or not logged_in:
            return logged_in

        return super().post(request, *args, **kwargs)

    def get_context_data(self, *, object_list=None, **kwargs):
        context = super().get_context_data(**kwargs)
        context.update(self.get_common_context_data(**kwargs))
        context['html_extra']['page_title'] = "Account Information"

        return context

    def get_success_url(self):
        return reverse('cart:account_dashboard')

    def get_object(self, queryset=None):
        customer_extra = self.get_customer_extra()
        if customer_extra:
            return customer_extra.get_customer()

        return None

    def form_valid(self, form):
        from django.core.exceptions import NON_FIELD_ERRORS
        error = False

        customer_extra = self.get_customer_extra()
        if customer_extra and isinstance(customer_extra, CustomerExtra):
            gender = form.cleaned_data.get('gender', '')
            change_email = form.cleaned_data.get('change_email', False)
            change_password = form.cleaned_data.get('change_password', False)
            current_password = form.cleaned_data.get('current_password', '')
            email = form.cleaned_data.get('email', '')
            new_password = form.cleaned_data.get('new_password', '')
            confirm_new_password = form.cleaned_data.get('confirm_new_password', '')

            if gender:
                try:
                    customer_extra.gender = gender
                    customer_extra.save()
                except Exception as e:
                    form.add_error('gender', 'Invalid data submitted')
                    error = True

            current_password_validated = self.current_password_valid(current_password)

            if change_email or change_password:
                if not current_password_validated:
                    form.add_error('current_password', 'Wrong password entered!')
                    form.add_error(NON_FIELD_ERRORS, 'You must verify your current password to update your password or email')
                    error = True
                else:
                    if change_password:
                        if not new_password or not confirm_new_password or not (new_password == confirm_new_password):
                            form.add_error('new_password', 'You must match with `Confirm New Password` field')
                            form.add_error('confirm_new_password', 'You must match with `New Password` field')
                            form.add_error(NON_FIELD_ERRORS,
                                           'You must match password in `New Password` and `Confirm New Password` fields')
                            error = True
                        elif new_password and confirm_new_password and (new_password == confirm_new_password):
                            form.instance.set_password(new_password)
            else:
                if not current_password:
                    if email == form.instance.email:
                        email = ''

                if email or new_password:
                    if not current_password_validated:
                        form.add_error('current_password', 'Wrong password entered!')
                        form.add_error(NON_FIELD_ERRORS,
                                       'You must verify your current password to update your password or email')
                        error = True

        else:
            form.add_error(NON_FIELD_ERRORS, 'Invalid data submitted')

        if error:
            return super().form_invalid(form)

        return super().form_valid(form)

    def current_password_valid(self, password):
        user = self.get_object()
        if user:
            return user.check_password(password)
        return False
