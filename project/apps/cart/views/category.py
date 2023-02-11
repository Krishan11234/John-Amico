from django.views import View
from django.shortcuts import render
from django.core.paginator import Paginator

from .base_view import BaseView


class CategoryView(BaseView):

    def get(self, request, category_id, *args, **kwargs):
        from ..models import Category, Product

        category_q = Category.objects.filter(is_active=True, id=category_id)  # Check for Category
        if category_q.exists():
            category = category_q.get()
        else:
            return self.page_404()

        per_page = int(request.GET.get('limit', 12))
        sort_field = request.GET.get('order')
        sort_order = request.GET.get('dir')

        if sort_field == 'name':
            if sort_order == 'desc':
                order_by = '-name'
                sorting = 'desc'
                sorting_by = 'name'
            else:
                order_by = 'name'
                sorting = 'asc'
                sorting_by = 'name'
        elif sort_field == 'price':
            if sort_order == 'desc':
                order_by = '-price'
                sorting = 'desc'
                sorting_by = 'price'
            else:
                order_by = 'price'
                sorting = 'asc'
                sorting_by = 'price'
        else:
            order_by = 'name'
            sorting = 'asc'
            sorting_by = 'name'

        # filter_categories = list(category.get_visible_children())
        filter_categories = []
        filter_categories.insert(0, category)

        products = Product.objects.filter(is_active=True, categories__in=filter_categories).order_by(order_by)

        paginator = Paginator(products, per_page)
        page_number = int(request.GET.get('p', 1))
        page_number = 1 if page_number < 1 else page_number
        products_paginated = paginator.get_page(page_number)

        pp = (products_paginated.paginator.num_pages - page_number)

        context = self.common_contexts(request)
        context['html_extra']['page_title'] = category.name
        context['category'] = category
        context['category_products'] = products_paginated
        context['extra'] = {
            'sorting': sorting,
            'sorting_by': sorting_by,
            'page_number': page_number,
            'per_page': int(per_page),
            'per_page_list': range(12, 72, 12),
            'remaining_pages': 0 if pp < 0 else pp,
            'total_products': products.count(),
            # 'limit_start': (per_page - (per_page-1)),
            # 'limit_end': (per_page - (per_page-1)),
        }

        return render(request, 'frontend/pages/category.html', context)
