from django.urls import path, re_path
from django.shortcuts import redirect
from . import views
from .views.base_view import BaseView

from .modules.urls import urlpatterns as modules_urls

app_name = "cart"
urlpatterns = [
    # path("import/<import_type>", views.ImportMagentoView.as_view(), name="import_magento"),
    # path("import/<import_type>/<migration_type>", views.ImportMagentoView.as_view(), name="import_magento_with_type"),

    path("favicon.ico", BaseView.file_renderer, name="favicon"),
    re_path('media/favicon/default/favicon.ico', lambda request: redirect('cart:favicon')),
    path("", views.Home.as_view(), name="home"),

    path("checkout/cart", views.CartView.as_view(), name="checkout_cart"),
    path("checkout/cart/<request_type>", views.CartView.as_view(), name="cart_update"),
    path("checkout/cart/<request_type>/<type_id>", views.CartView.as_view(), name="cart_add_delete"),
    path("onestepcheckout", views.CheckoutView.as_view(), name="checkout"),
    re_path(r'^onestepcheckout/index/(?P<submit_type>(saveOrder|is_valid_email|is_valid_referrer|addproduct|minusproduct|deleteproduct|save_shipping))',
            views.CheckoutView.as_view(), name="checkout_post"),
    path("checkout/onepage/success", views.CheckoutView().checkout_success, name="order_success"),

    path("customer/account/login", views.LoginView.as_view(), name="account_login"),
    path("customer/account/loginPost/", views.LoginView.as_view(), name="account_login_post"),
    path("customer/account/forgotpassword", views.Home.as_view(), name="account_forgot_password"),
    path("customer/logout", views.LogoutView.as_view(), name="account_logout"),

    path("customer/account", views.AccountDashboardView.as_view(), name="account_dashboard"),
    path("customer/account/edit", views.AccountEditView.as_view(), name="account_information"),
    path("customer/account/edit/changepass", views.AccountEditView.as_view(), name="account_change_pass"),
    path("customer/address", views.AccountAddressView.as_view(), name="account_address"),
    path("customer/address/add", views.AccountAddressAddView.as_view(), name="account_address_add"),
    path("customer/address/edit/id/<int:pk>", views.AccountAddressEditView.as_view(), name="account_address_edit"),
    path("customer/address/delete/id/<int:pk>", views.AccountAddressDeleteView.as_view(), name="account_address_delete"),

    path("sales/order/history", views.AccountOrderListView.as_view(), name="account_orders"),
    path("sales/order/view/order_id/<int:pk>", views.AccountOrderView.as_view(), name="account_order_view"),
    path("sales/order/print/order_id/<int:pk>", views.AccountOrderView.as_view(), name="account_order_print"),
    path("sales/order/invoice/order_id/<int:pk>", views.AccountOrderInvoiceView.as_view(), name="account_order_invoice_list"),
    path("sales/order/shipment/order_id/<int:pk>", views.AccountOrderShipmentView.as_view(), name="account_order_shipment_list"),
    path("sales/order/creditmemo/order_id/<int:pk>", views.AccountOrderRefundsView.as_view(), name="account_order_refund_list"),

    # path("recurringandrentalpayments/customer", views.AccountDashboardView.as_view(), name="account_subscriptions"),
    path("authnetcim/manage/", views.Home.as_view(), name="account_manage_cards"),
    # path("jaautoship/manage", views.AccountDashboardView.as_view(), name="account_autoships"),

    # path("sales/order/view/<order_id>", views.Home.as_view(), name="view_order_details"),
    # path("sales/invoice/view/<order_id>", views.Home.as_view(), name="view_invoice_details"),
    # path("sales/ship/view/<order_id>", views.Home.as_view(), name="view_shipment_details"),
]

urlpatterns += modules_urls

urlpatterns += [
    re_path(r'^jamemberrefer/setsession/(?P<submit_type>(set|unset))', views.MemberSessionView.as_view(), name="member_session"),
    re_path(r'^(?P<cat_prod_url_path>[\w\d\/\-\.html]+)$', views.CategoryOrProductOrReferrerSelector.as_view(),
            name="cat_prod_ref"),
]
