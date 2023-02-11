from django.contrib.auth import authenticate, login, logout
from django.shortcuts import render, redirect, reverse
from django.contrib import messages

from ..base_view import BaseView

from ...utils import helper, validation


class LoginView(BaseView):
    em = []

    def get(self, request, *args, **kwargs):
        if helper.is_user_types_logged_in(condition_type='or'):
            return redirect('cart:home')

        context = self.common_contexts(request)
        context['html_extra']['page_title'] = "Login to your account"
        context['cart'] = super().cart_items_context()

        login_data = {}

        if not helper.is_customer_logged_in() and request.session.get('LOGIN_DATA', False):
            login_data = request.session['LOGIN_DATA']

            request.session['LOGIN_DATA'] = {}
            request.session.modified = True

        context['login_data'] = login_data

        response = render(request, 'frontend/pages/login.html', context)

        return response

    def post(self, request, *args, **kwargs):
        self.em = []
        redirect_to = request.GET['redirect_to'] if 'redirect_to' in request.GET and request.GET['redirect_to'] else reverse('cart:home')
        if helper.is_user_types_logged_in(condition_type='or'):
            return redirect(redirect_to)
        validated_data = validation.validate_account_create_data(request.POST)

        if validated_data:
            if not validated_data['success']:
                for field, mes in validated_data['message'].items():
                    for m in mes:
                        self.em.append(str(field).capitalize() + ": " + m)
                # self.em.append('The username or password is incorrect. Please try again.')
            else:
                email = request.POST.get('email')
                password = request.POST.get('password')
                user = authenticate(request, username=email, password=password)

                if user:
                    try:
                        if not user.is_active:
                            self.em.append('Your account is deactivated. Please contact adminitsrator to restore access.')
                        else:
                            quote = self.cart_utils.get_or_create_quote()
                            login(request, user)
                            request.session['is_customer'] = True
                            request.session['is_guest'] = False

                            quote.customer = user
                            quote.save()

                            self.cart_utils.unify_possible_carts()
                            super().cart_items_context(reload=True, recheck=True)

                            redirect_to = reverse('cart:account_dashboard')
                            if request.GET.get('redirect_to'):
                                redirect_to = request.GET.get('redirect_to')
                            if request.POST.get('redirect_to'):
                                redirect_to = request.POST.get('redirect_to')

                            return redirect(redirect_to)
                    except Exception as e:
                        self.em.append(str(e))
                else:
                    self.em.append('The username or password is incorrect. Please try again.')
        else:
            self.em.append("Something wrong happened while verifying your submitted data!")

        if self.em:
            messages.add_message(request, messages.ERROR, '<br/>'.join(self.em))
            self.em = []

            request.session['LOGIN_DATA'] = {}
            request.session['LOGIN_DATA'] = request.POST

            request.session.modified = True

        return redirect('cart:account_login')