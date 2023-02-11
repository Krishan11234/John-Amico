from django.shortcuts import render, redirect

from .base_view import BaseView
from .. import views


class CategoryOrProductOrReferrerSelector(BaseView):

    def get(self, request, cat_prod_url_path, *args, **kwargs):
        from ..models import Category, Product, TblMember

        cat_prod_url_path = cat_prod_url_path.strip('/')

        if cat_prod_url_path:
            try:
                category_q = Category.objects.filter(url_path=cat_prod_url_path)        # Check for Category
                if category_q.exists():
                    category = category_q.get()
                    return views.CategoryView().get(request, category.id, *args, **kwargs)
            except Exception as e:
                pass

            try:
                product_q = Product.objects.filter(url_path=cat_prod_url_path)          # Check for Product
                if product_q.exists():
                    product = product_q.get()
                    return views.ProductView().get(request, product.id, *args, **kwargs)
            except Exception as e:
                pass

            try:
                referrer_q = TblMember.objects.filter(amico_id=cat_prod_url_path)
                if referrer_q.exists():
                    referrer = referrer_q.get()
                    # @TODO: Set referrer in Session

                    return redirect('cart:home', *args, **kwargs)
            except Exception as e:
                pass

        return self.page_404()
