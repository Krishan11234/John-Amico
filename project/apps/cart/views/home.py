from django.views import View
from django.shortcuts import render

from.base_view import BaseView


class Home(BaseView):

    def get(self, request, *args, **kwargs):
        from ..models import HomePageRow, BannerSliderCategory

        context = self.common_contexts(request)
        context['home_page_rows'] = HomePageRow.objects.filter(is_active=True).order_by('sort_order')
        context['home_page_banners'] = BannerSliderCategory.objects.filter(status=1, title="Home").all()

        return render(request, 'frontend/pages/home.html', context)
