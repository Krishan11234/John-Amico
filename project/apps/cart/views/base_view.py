import json
from django.core.serializers.json import DjangoJSONEncoder
from django.views import View
from django.shortcuts import render, redirect, reverse
from django.utils.text import slugify
from django.urls import reverse

from ..utils.static import ROOT_CATEGORY_ID
from ..utils import Menu, cart, helper, menu
# from ..models import HeaderNotice
# from ..models import HomePageRow


class BaseView(View):

    cart_context = {}
    cart_utils = cart.CartUtils()

    page_title = ''

    def common_contexts(self, request=None, refresh=False, recheck=False, *args, **kwargs):

        from ..models import SiteConfig, Category

        # self.my_decrypt('eyJpdiI6IjFUaElzWW5jdWxxMytDK2h1eXVENHc9PSIsImRhdGEiOiJWblVKSDMzajJSZm8xdk10cEJ5WHNDanJDVWFRU1Z3SFpGbWtPXC81Z1E3MD0ifQ', '227da206097e0dce6fb7ea27df854aba745a20638d8a4c60f72b4e8aa846b552')
        request = request if request else helper.get_request()

        context = {
            'base': {
                'url': request.get_host() if request else '',
                'home_url': reverse('cart:home')
            },
            'site_config': SiteConfig.get_solo(),
            # 'all_categories': Category.objects.filter(id__gt=ROOT_CATEGORY_ID).order_by('order').all(),
            # 'root_categories': Category.objects.filter(parent=ROOT_CATEGORY_ID).order_by('order').all(),
            'menus': Menu().get_all_autoload_menu_types(),
            'menu_extra': {
                'category_parent_ids': self.finding_menu_parent_categories(request),
            },
            'cart': self.cart_items_context(refresh, recheck),
            'html_extra': {
                'body': {
                    'class': ' '.join([
                        request.resolver_match.url_name if request and request.resolver_match.url_name else '',
                        slugify(request.path) if request else ''
                    ]),
                },
                'page_title': self.page_title,
            },
            'customer_logged_in': helper.is_customer_logged_in(),
            'professional_logged_in': helper.is_professional_logged_in(),
            'extra': {},
        }

        context['cart']['items_json'] = json.dumps(context['cart']['items'], cls=DjangoJSONEncoder)

        return context

    def cart_items_context(self, reload=False, recheck=False):
        if not reload:
            if self.cart_context:
                return self.cart_context

        quote = self.cart_utils.get_or_create_quote(reload=reload)
        cart_items, cart_items_count = self.cart_utils.get_current_quote_items_list(with_count=True, recheck=True)
        cart_total = self.cart_utils.get_current_quote_items_total()

        self.cart_context = {
            'quote': quote,
            'items': cart_items if cart_items else {},
            'items_count': cart_items_count if cart_items_count else 0,
            'price_total': cart_total if cart_total else 0,
        }
        return self.cart_context

    def finding_menu_parent_categories(self, request, *args, **kwargs):
        parents = []
        if request and request.path:
            from ..models import Category

            cat_q = Category.objects.filter(url_path=request.path.strip('/'))
            if cat_q.exists():
                cat = cat_q.get()
                current = cat

                while current.parent:
                    if current.parent and isinstance(current.parent, Category):
                        parents.append(current.parent.id)
                        current = current.parent

        return parents

    def file_renderer(self, *args, **kwargs):
        import os
        from django.http import FileResponse
        from django.conf import settings

        path = settings.BASE_DIR + self.path    # "self.path" is the path from requested URL after domain

        if os.path.isfile(path):
            response = FileResponse(open(path, 'rb'))
        else:
            response = self.page_404()

        return response

    # def dispatch(self, *args, **kwargs):
    #     return super(BaseView, self).dispatch(*args, **kwargs)

    def customer_login_required(self):
        if helper.is_professional_logged_in():
            return redirect(menu.jacustom_urls['jamember']['panel'])

        if not helper.is_customer_logged_in():
            return redirect('cart:account_login')

        return True

    def professional_member_login_required(self):
        if helper.is_customer_logged_in():
            return redirect('cart:account_dashboard')

        if not helper.is_professional_logged_in():
            return redirect(menu.jacustom_urls['jamember']['login'])

        return True

    def page_404(self, request=None, *args, **kwargs):
        from django.template import loader
        from django.http import HttpResponseNotFound

        context = self.common_contexts(request)
        template = loader.get_template('frontend/404.html')
        body = template.render(context, request)

        return HttpResponseNotFound(body, 'text/html')

