from django.shortcuts import render, redirect, reverse
from django.middleware.csrf import rotate_token
from django.contrib.auth import authenticate, login, logout
from ..base_view import BaseView
from ...utils import helper


class MemberSessionView(BaseView):

    def get(self, request, submit_type='', *args, **kwargs):
        response = self.method_switch(request, submit_type, *args, **kwargs)

        return response

    def method_switch(self, request, request_type, *args, **kwargs):
        response = None

        request_types = ['set', 'unset']
        if request_type in request_types:
            method_name = request_type + "__session_handle"
            method = getattr(self, method_name)

            if method:
                response = method(request, *args, **kwargs)

        return response

    def set__session_handle(self, request, *args, **kwargs):
        from ...models import Customers, TblMember, UserExtended, MemberExtra

        aid = request.GET.get('aid', '')
        aid = helper.decrypt_message(aid) if aid else ''

        redirect_to = request.GET.get('redirect_to', '')

        if aid and '.' in aid:
            amico_id, that_time = aid.split('.')
            if amico_id:
                member_q = TblMember.objects.filter(amico_id=amico_id, bit_active=1)
                if member_q.exists():

                    quote = self.cart_utils.get_or_create_quote()

                    member = member_q.get()

                    memex_q = MemberExtra.objects.filter(tbl_member=member)
                    if not memex_q.exists():
                        MemberExtra.objects.create(tbl_member=member, member_customer=member.int_customer)

                    user = member.convert_to_django_user_model()

                    quote.tbl_member_id = member.int_member_id
                    quote.save()

                    request.user = user
                    request.session['_auth_user_id'] = user._meta.pk.value_to_string(user)
                    request.session['_auth_user_backend'] = 'apps.cart.backends.BackendForCustomMembers'
                    request.session['_auth_user_hash'] = user.get_session_auth_hash()
                    rotate_token(request)
                    request.session.modified = True

                    self.cart_utils.unify_possible_carts()
                    super().cart_items_context(reload=True, recheck=True)

                    if redirect_to:
                        return redirect(redirect_to)

        return redirect('cart:home')

    def unset__session_handle(self, request, *args, **kwargs):
        logout(request)
        request.session.cycle_key()

        redirect_to = request.GET.get('redirect_to', '')
        if redirect_to:
            return redirect(redirect_to)
        return redirect('cart:home')
