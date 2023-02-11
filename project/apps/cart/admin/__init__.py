from django.contrib.auth.models import Permission
from django.contrib import admin

from .home_page_row_admin import HomePageRowAdmin
from .banner_slider_admin import BannerSliderAdmin
# from .home_page_column_admin import HomePageColumnAdmin

from .site_config_admin import SiteConfigAdmin
from .store_config_admin import StoreConfigAdmin
from .ja_shipping_config_admin import JAShippingConfigAdmin
from .authorizenet_payment_config_admin import AuthorizenetPaymentConfigAdmin
from .menu_admin import MenuTypeAdmin
from .customer_admin import CustomerAdmin

from .category_admin import CategoryAdmin
from .product_admin import ProductAdmin
from .product_review_admin import ProductReviewAdmin

from .order_admin import OrderAdmin
from .invoice_admin import InvoiceAdmin
from .shipment_admin import ShipmentAdmin
from .credit_memo_admin import CreditMemoAdmin

from ..modules.admin import *


admin.site.register(Permission)

