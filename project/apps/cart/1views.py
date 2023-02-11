from django.shortcuts import render
from django.views.generic import ListView, TemplateView
from django.http import HttpResponse
from .models import Product

from .scripts import migrate_from_magento as magento_migrate


class OrderListView(TemplateView):
    template_name = 'frontend/home.html'





