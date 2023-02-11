from django.views import View
from django.shortcuts import render

from .base_view import BaseView


class ProductView(BaseView):

    def get(self, request, product_id, *args, **kwargs):
        from ..models import Product
        from ..utils import helper

        product_q = Product.objects.filter(is_active=True, id=product_id)  # Check for Category
        if product_q.exists():
            product = product_q.get()
        else:
            return self.page_404()

        context = self.common_contexts(request)
        context['product'] = product
        context['html_extra']['page_title'] = product.name
        context['extra'] = {
            'random_str': helper.get_unique_string()
        }

        # header_menu_categories = MenuHelper.get_header_menu_categories()
        # footer_menus = MenuHelper.get_footer_menus()
        # on_sale_categories = MenuHelper.get_on_sale_categories()
        # header_notices = HeaderNotice.objects.filter(is_active=True)
        # home_page_rows = HomePageRow.objects.filter(is_active=True).order_by('sort_order')
        # template_data = {
        #     'header_menu_categories': header_menu_categories,
        #     'footer_menus': footer_menus,
        #     'header_notices': header_notices,
        #     'home_page_rows': home_page_rows,
        #     'on_sale_categories': on_sale_categories,
        # }

        return render(request, 'frontend/pages/product.html', context)
