from django.shortcuts import redirect, reverse
from django.contrib.auth.mixins import LoginRequiredMixin
from django.contrib.auth import logout

from ..base_view import BaseView

from ...utils import helper, menu


class LogoutView(LoginRequiredMixin, BaseView):

    def get(self, request, *args, **kwargs):
        redirect_to = reverse('cart:home')

        if helper.is_professional_logged_in():
            # Redirect to Professional Logout page and then redirect back to home page
            redirect_to = menu.jacustom_urls['jamember']['logout']+'?redirect_to='+reverse('cart:home')

        logout(request)
        request.session.cycle_key()

        return redirect(redirect_to)
