from django.contrib.admin.exceptions import DisallowedModelAdminToField
from django.contrib.admin.templatetags.admin_urls import add_preserved_filters
from django.contrib.admin.utils import unquote
from django.core.exceptions import PermissionDenied
from django.db import transaction, router
from django.http.response import HttpResponseRedirect

from django.contrib import admin
from django.contrib.admin import AdminSite
from django.shortcuts import render, redirect
from django.template.response import TemplateResponse
from django.utils.html import format_html
from django.utils.http import urlquote
from django.utils.safestring import mark_safe
from django.urls import reverse
from django.utils.decorators import method_decorator
from django.utils.translation import gettext as _, ngettext
from django.contrib import messages

# from django_summernote.admin import SummernoteModelAdmin, SummernoteInlineModelAdmin
from django.views.decorators.csrf import csrf_protect
from django.conf import settings

from ..models import Order, OrderItem, OrderAddress, OrderPayment, PaymentTransaction, Invoice, Shipment, CreditMemo

csrf_protect_m = method_decorator(csrf_protect)


# class MyAdminSite(AdminSite):
#     def get_urls(self):
#         from django.conf.urls import url
#         urls = super(MyAdminSite, self).get_urls()
#         # Note that custom urls get pushed to the list (not appended)
#         # This doesn't work with urls += ...
#         urls = [
#             url(r'^my_view/$', self.admin_view(index))
#         ] + urls
#         return urls
#
# admin_site = MyAdminSite()


class OrderItemInline(admin.TabularInline):
    verbose_name_plural = "ORDER ITEMS"
    model = OrderItem
    extra = 0
    can_delete = False
    fields = (
        'name_',
        'sku',
        'quantity_details',
        'item_unit_price',
        'item_total_price',
    )
    readonly_fields = (
        'name_',
        'sku',
        'quantity_details',
        'item_unit_price',
        'item_total_price',
    )

    def has_add_permission(self, request, obj=None):
        return False

    def name_(self, obj):
        text = obj.name
        attrs = obj.get_attributes()

        if attrs:
            for key, attr in attrs.items():
                if isinstance(attr, dict):
                    if 'title' in attr.keys() and 'value' in attr.keys():
                        text += "<div style='margin-top: 5px;'><strong>{}</strong>: {}</div>".format(attr['title'], attr['value'])

        return mark_safe(text)

    def quantity(self, obj):
        if obj.quantity:
            return '{:.1f}'.format(obj.quantity)

    def quantity_details(self, obj):
        html = "<style>.qty-table td {padding-top: 4px !important;}</style>"
        html += '<table cellspacing="0" class="qty-table"><tbody>'
        if obj.quantity:
            html += '<tr><td>Ordered</td><td><strong>'+'{:.0f}'.format(obj.quantity)+'</strong></td></tr>'
        if obj.qty_invoiced:
            html += '<tr><td>Invoiced</td><td><strong>'+'{:.0f}'.format(obj.qty_invoiced)+'</strong></td></tr>'
        if obj.qty_shipped:
            html += '<tr><td>Shipped</td><td><strong>'+'{:.0f}'.format(obj.qty_shipped)+'</strong></td></tr>'
        if obj.qty_refunded:
            html += '<tr><td>Refunded</td><td><strong>'+'{:.0f}'.format(obj.qty_refunded)+'</strong></td></tr>'
        if obj.qty_canceled:
            html += '<tr><td>Cancelled</td><td><strong>'+'{:.0f}'.format(obj.qty_canceled)+'</strong></td></tr>'

        html += '</tbody></table>'

        return mark_safe(html)

    def item_unit_price(self, obj):
        if obj.price:
            return '{:.2f}'.format(obj.price)

    def item_total_price(self, obj):
        if obj.price and obj.quantity:
            total = float(obj.price) * float(obj.quantity)
            return '{:.2f}'.format(total)
        return '-'


class OrderBillingAddressInline(admin.StackedInline):
    verbose_name_plural = "Billing Address"
    model = OrderAddress
    extra = 0
    can_delete = False
    fields = (
        ('firstname', 'lastname'),
        ('address1', 'address2'),
        ('telephone', 'email'),
    )
    readonly_fields = ('firstname', 'lastname', 'address1', 'address2', 'telephone', 'email')

    def has_add_permission(self, request, obj=None):
        return False

    def get_queryset(self, request):
        import re

        path = str(request.path)
        path = re.findall(r'(\d+)', path)

        order_id = int(path[0]) if len(path) > 0 else 0

        return super().get_queryset(request).filter(address_type='billing', order_id=order_id)


class OrderShippingAddressInline(admin.StackedInline):
    verbose_name = "Shipping Address as"
    verbose_name_plural = "Shipping Address"
    model = OrderAddress
    extra = 0
    can_delete = False
    fields = (
        # ('use_billing_address_as_shipping'),
        ('firstname', 'lastname'),
        ('address1', 'address2'),
        ('telephone', 'email'),
    )
    readonly_fields = ('firstname', 'lastname', 'address1', 'address2', 'telephone', 'email')

    def has_add_permission(self, request, obj=None):
        return False

    def get_queryset(self, request):
        import re

        path = str(request.path)
        path = re.findall(r'(\d+)', path)

        order_id = int(path[0]) if len(path) > 0 else 0

        qs = super().get_queryset(request).filter(address_type='shipping', order_id=order_id)
        if not qs.exists():
            qs = super().get_queryset(request).filter(address_type='billing', order_id=order_id)

        return qs

    # def get_readonly_fields(self, request, obj=None):
    #     shipping = obj.get_shipping_address()
    #     if not shipping:
    #         return ('firstname', 'lastname', 'address1', 'address2', 'address2', 'telephone', 'email')
    #     else:
    #         # return ('use_billing_address_as_shipping')
    #         return ()

    # def get_fields(self, request, obj=None):
    #     shipping = obj.get_shipping_address()
    #     if shipping:
    #         return (('firstname', 'lastname'), ('address1', 'address2'),('telephone', 'email'),)
    #     else:
    #         return (('firstname', 'lastname'),('address1', 'address2'),('telephone', 'email'),)


class OrderTotalsInline(admin.StackedInline):
    verbose_name = "Order"
    verbose_name_plural = "Order Totals"
    model = Order
    extra = 0
    can_delete = False
    fields = ('sub_total', 'shipping_amount', 'tax_amount', 'grand__total', 'shipping_refunded', 'shipping_tax_refunded',
              'total_refunded')
    readonly_fields = ('sub_total', 'shipping_amount', 'tax_amount', 'grand__total', 'shipping_refunded', 'shipping_tax_refunded',
              'total_refunded')
    # fk_name = 'id'

    # model.__str__ = lambda self: "Totals"

    def sub_total(self, obj):
        return '{:.2f}'.format(obj.subtotal)

    def grand__total(self, obj):
        return '{:.2f}'.format(obj.grand_total)

    def shipping_amount(self, obj):
        shipping_method_ttle = str(obj.shipping_method_title)
        return '{:.2f}'.format(obj.shipping_amount) + " (" + shipping_method_ttle + ")"

    def tax_amount(self, obj):
        return '{:.2f}'.format(obj.tax_amount)

    def total_refunded(self, obj):
        return '{:.2f}'.format(float(obj.total_offline_refunded)+float(obj.total_online_refunded))

    # def get_queryset(self, request):
    #     import re
    #
    #     path = str(request.path)
    #     path = re.findall(r'(\d+)', path)
    #     order_id = int(path[0]) if len(path) > 0 else 0
    #
    #     qs = super().get_queryset(request).filter(order_id=order_id, address_type='billing')
    #
    #     return qs

    def has_add_permission(self, request, obj=None):
        return False

    def has_change_permission(self, request, obj=None):
        return False

    @property
    def media(self):
        from django import forms

        extra = '' if settings.DEBUG else '.min'
        js = ['order_view%s.js' % extra]
        statics = super().media + forms.Media(js=['admin/js/%s' % url for url in js])
        return statics

    def get_formset(self, request, obj=None, **kwargs):
        super_sets = super().get_formset(request, obj, **kwargs)
        super_sets.get_default_prefix = lambda: "ordertotal"
        return super_sets


class OrderShippingInformationInline(admin.TabularInline):
    verbose_name_plural = "Shipment Information"
    model = OrderAddress
    extra = 0
    can_delete = False
    fields = ()
    readonly_fields = ()

    @property
    def media(self):
        from django import forms

        extra = '' if settings.DEBUG else '.min'
        js = ['order_view%s.js' % extra]
        statics = super().media + forms.Media(js=['admin/js/%s' % url for url in js])
        return statics

    def get_formset(self, request, obj=None, **kwargs):
        super_sets = super().get_formset(request, obj, **kwargs)
        super_sets.get_default_prefix = lambda: "ordershipping"
        return super_sets


class TransactionInline(admin.TabularInline):
    model = PaymentTransaction
    extra = 0
    fields = (
        'created_at',
        # 'type_of_transaction',
        'status_of_transaction',
        'transaction_id',
        'amount',
        # 'parent',
        'is_closed',
    )
    readonly_fields = (
        'created_at',
        # 'type_of_transaction',
        'status_of_transaction',
        'transaction_id',
        # 'parent',
        'is_closed',
        'amount',
    )
    can_delete = False

    def has_add_permission(self, request, obj=None):
        return False

    def type_of_transaction(self, obj):
        if obj.transaction_type:
            from ..utils import static
            types = dict(static.AUTHORIZENETCIM_PAYMENT_ACTION_TYPES)
            if obj.transaction_type in list(types.keys()):
                return types[obj.transaction_type]
            else:
                return '-'
        else:
            return '-'

    def status_of_transaction(self, obj):
        if obj.transaction_status:
            from ..utils import static
            types = dict(static.AUTHORIZENETCIM_PAYMENT_ACTION_COMPLETE_STATUS)
            if obj.transaction_status in list(types.keys()):
                # return format_html("{}", mark_safe('{} <img src="{}"/>'.
                #        format(types[obj.transaction_status],
                #               settings.STATIC_URL + 'admin/img/icon-'+('yes' if not obj.is_failed else 'no')+'.svg')))
                return types[obj.transaction_status]
            else:
                return '-'
        else:
            return '-'

    def amount(self, obj):
        return '{:.2f}'.format(obj.amount_effected)


class OrderPaymentInformationInline(admin.TabularInline):
    verbose_name_plural = "Payment Information"
    model = OrderPayment
    # inlines = (TransactionInline,)
    extra = 0
    can_delete = False
    fields = (
        ('payment_method', 'status',),
        ('card_owner', 'card_type'),
        ('card_last4', 'car_exp'),
    )
    readonly_fields = ('payment_method', 'method', 'status', 'card_owner', 'card_type', 'card_last4', 'car_exp')

    def has_add_permission(self, request, obj=None):
        return False

    def car_exp(self, obj):
        return "{}/{}".format(obj.card_exp_month, obj.card_exp_year)

    def payment_method(self, obj):
        if obj:
            from ..views import PaymentSubView
            method = PaymentSubView().get_payment_method(method_code=obj.method)

            return method['title'] if isinstance(method, dict) else obj.method

        return '-'


class OrderInvoicesInline(admin.TabularInline):
    verbose_name_plural = "Invoices"
    model = Invoice
    # inlines = (TransactionInline,)
    extra = 0
    can_delete = False
    fields = ('invoice_increment_id', 'bill_to_name', 'invoice_date', 'status', 'amount')
    readonly_fields = ('invoice_increment_id', 'bill_to_name', 'invoice_date', 'status', 'amount')

    def has_add_permission(self, request, obj=None):
        return False

    def invoice_increment_id(self, obj):
        invoice_url = reverse('admin:{}_{}_change'.format(obj._meta.app_label, obj._meta.model_name), args=[obj.id])
        return mark_safe('<a target="_blank" href="{}">{}</a>'.format(invoice_url, obj.get_invoice_id()))

    def bill_to_name(self, obj):
        return obj.get_customer_name()

    def invoice_date(self, obj):
        return obj.created_at

    def amount(self, obj):
        return obj.grand_total


class OrderShipmentsInline(admin.TabularInline):
    verbose_name_plural = "Shipments"
    model = Shipment
    extra = 0
    can_delete = False
    fields = ('shipment_increment_id', 'bill_to_name', 'shipment_date', 'status')
    readonly_fields = ('shipment_increment_id', 'bill_to_name', 'shipment_date', 'status')

    def has_add_permission(self, request, obj=None):
        return False

    def shipment_increment_id(self, obj):
        url = reverse('admin:{}_{}_change'.format(obj._meta.app_label, obj._meta.model_name), args=[obj.id])
        return mark_safe('<a target="_blank" href="{}">{}</a>'.format(url, obj.get_id()))

    def bill_to_name(self, obj):
        return obj.get_customer_name()

    def shipment_date(self, obj):
        return obj.created_at


class OrderCreditMemosInline(admin.TabularInline):
    verbose_name_plural = "Credit Memos"
    model = CreditMemo
    extra = 0
    can_delete = False
    fields = ('credit_memo_increment_id', 'bill_to_name', 'credit_memo_date', 'amount')
    readonly_fields = ('credit_memo_increment_id', 'bill_to_name', 'credit_memo_date', 'amount')

    def has_add_permission(self, request, obj=None):
        return False

    def credit_memo_increment_id(self, obj):
        url = reverse('admin:{}_{}_change'.format(obj._meta.app_label, obj._meta.model_name), args=[obj.id])
        return mark_safe('<a target="_blank" href="{}">{}</a>'.format(url, obj.get_credit_memo_id()))

    def bill_to_name(self, obj):
        return obj.get_customer_name()

    def credit_memo_date(self, obj):
        return obj.created_at

    def amount(self, obj):
        return obj.grand_total


class OrderAdmin(admin.ModelAdmin):
    # A template for a customized change view:
    change_form_template = 'admin/order_change_form.html'

    list_display = (
        'id',
        'increment_id',
        # 'invoice',
        'billing_info',
        'shipping_info',
        'sub_total',
        'shipping_price',
        'grand__total',
        'order_status',
        # 'status',
        'order_date',
        'is_professional',
        # 'delivery_date_by_type',
        # 'order_status_by_type',
        # 'reward_points',
    )
    list_display_links = (
        'id',
        # 'invoice',
        'increment_id',
    )
    search_fields = (
        'id',
        'increment_id',
        # 'invoicenum',
        'customer_email',
        'customer_firstname',
        'customer_lastname',
    )
    readonly_fields = ('customer_link', 'is_member_order', 'member_amico_id', 'increment_id', 'customer',
                       'order_date', 'customer_firstname', 'customer_lastname', 'customer_email', 'order_status',
                       'customer_gender', 'customer_group', 'subtotal', 'shipping_amount', 'tax_amount', 'grand_total')
    list_filter = ('status', 'created_at')
    inlines = (
        OrderBillingAddressInline,
        OrderShippingAddressInline,
        OrderItemInline,
        # OrderShippingInformationInline,
        OrderPaymentInformationInline,
        TransactionInline,
        OrderInvoicesInline,
        OrderShipmentsInline,
        OrderCreditMemosInline,
        OrderTotalsInline,
        # OrderPromotionInline,
        # ShippingTupleInline,
        # OrderShippingInfoInline,
    )

    fieldsets = (
        (
            'BASIC INFORMATION', {
                'fields': (
                    ('increment_id', 'order_date'),
                    ('is_member_order', 'member_amico_id'),
                    ('order_status',),
                ),
            }),
        (
            'CUSTOMER INFORMATION', {
                'fields': (
                    ('customer_firstname', 'customer_lastname',),
                    ('customer_email', 'customer_group', 'customer_gender',),
                    ('customer_link',),
                ),

            }
        ),
        (
            'COMMENTS', {
                'fields': (
                    'order_note',
                    #'internal_comment',
                )
            }
        ),
        # (
        #     'ORDER TOTALS', {
        #         'fields': ('sub_total', 'shipping_amount', 'tax_amount', 'grand__total')
        #     }
        # )
    )

    def has_add_permission(self, request, obj=None):
        return False

    def has_change_permission(self, request, obj=None):
        if obj:
            return obj.can_cancel()
        return super().has_change_permission(request, obj)

    def has_delete_permission(self, request, obj=None):
        if obj:
            return obj.can_delete()
        return super().has_delete_permission(request, obj)

    def has_invoice_permission(self, request, obj=None):
        if obj:
            return obj.can_invoice()
        return False

    def has_cancel_permission(self, request, obj=None):
        if obj:
            return obj.can_cancel()
        return False

    def has_ship_permission(self, request, obj=None):
        if obj:
            return obj.can_ship()
        return False

    def has_credit_memo_permission(self, request, obj=None):
        if obj:
            return obj.can_credit_memo()
        return False

    # def order_status(self, obj):
    #     if obj.status in ['cancelled']:
    #         html = '<style type="text/css">td.field-order_status {color: #842029;background-color: #f8d7da;border-color: #f5c2c7;}</style>'
    #         html += '<div>' + str(obj.status).capitalize() + '</div>'
    #     else:
    #         html = str(obj.status).capitalize()
    #
    #     return mark_safe(html)


    def sub_total(self, obj):
        return '{:.2f}'.format(obj.subtotal)

    def shipping_price(self, obj):
        return '{:.2f}'.format(obj.shipping_amount)

    def grand__total(self, obj):
        return '{:.2f}'.format(obj.grand_total)

    def orderid(self, obj):
        return obj.id

    def invoice(self, obj):
        return '{}{}'.format(obj.invoicenum_prefix, obj.invoicenum)

    def address_info(self, obj, address):
        if not address:
            return '-'

        customer_edit_url = reverse(
            'admin:{}_{}_change'.format(
                obj._meta.app_label,
                obj.customer._meta.model_name),
            args=[obj.customer.id]
        ) if obj.customer else ''
        return format_html(
            '{}<br><br>{}<br>{}<br>{}<br>{} {}',
            mark_safe('<a target="_blank" href="{}">{}</a>'.format(customer_edit_url,
                                                   address.get_fullname())) if obj.customer else address.get_fullname(),
            address.address1,
            address.address2,
            address.city,
            address.state,
            address.zip
        )

    def billing_info(self, obj):
        billing_address = obj.get_billing_address()
        return self.address_info(obj, billing_address)

    def shipping_info(self, obj):
        shipping_address = obj.get_shipping_address()
        if not shipping_address:
            return self.billing_info(obj)
        return self.address_info(obj, shipping_address)

    def order_date(self, obj):
        return obj.created_at.strftime('%b %d, %Y %I:%M %p')

    def order_status(self, obj):
        return obj.status.title().replace('_', ' ')

    def customer_link(self, obj):
        if obj.customer:
            customer_edit_url = reverse(
                'admin:{}_{}_change'.format(
                    obj._meta.app_label,
                    obj.customer._meta.model_name),
                args=[obj.customer.id]
            )
            return format_html("{}", mark_safe('<a target="_blank" href="{}">Edit</a>'.format(customer_edit_url)))
        else:
            return 'N/A'

    def is_member_order(self, obj):
        return 'No' if not obj.is_professional else format_html("{}", mark_safe('<strong>Yes</strong>'))

    def member_amico_id(self, obj):
        return 'N/A' if not obj.professional_id else format_html("{}", mark_safe('<strong>{}</strong>'.format(obj.professional_id)))

    def get_urls(self):
        from django.urls import path
        from functools import update_wrapper

        def wrap(view):
            def wrapper(*args, **kwargs):
                return self.admin_site.admin_view(view)(*args, **kwargs)
            wrapper.model_admin = self
            return update_wrapper(wrapper, view)

        info = self.model._meta.app_label, self.model._meta.model_name
        super_urls = super().get_urls()
        urls = [
            path('<path:object_id>/invoice/', wrap(self.invoice_view), name='%s_%s_invoice' % info),
            path('<path:object_id>/ship/', wrap(self.ship_view), name='%s_%s_ship' % info),
            path('<path:object_id>/cancel/', wrap(self.cancel_view), name='%s_%s_cancel' % info),
            path('<path:object_id>/credit_memo/', wrap(self.refund_view), name='%s_%s_credit_memo' % info),
        ]

        return urls + super_urls

    @csrf_protect_m
    def invoice_view(self, request, object_id, extra_context=None):
        with transaction.atomic(using=router.db_for_write(self.model)):
            return self._invoice_view(request, object_id, extra_context)

    @csrf_protect_m
    def refund_view(self, request, object_id, extra_context=None):
        with transaction.atomic(using=router.db_for_write(self.model)):
            return self._refund_view(request, object_id, extra_context)

    @csrf_protect_m
    def ship_view(self, request, object_id, extra_context=None):
        with transaction.atomic(using=router.db_for_write(self.model)):
            return self._ship_view(request, object_id, extra_context)

    @csrf_protect_m
    def cancel_view(self, request, object_id, extra_context=None):
        with transaction.atomic(using=router.db_for_write(self.model)):
            return self._cancel_view(request, object_id, extra_context)

    def _ship_view(self, request, object_id, extra_context):
        from ..utils import helper

        redirect_url = ''
        opts = self.model._meta

        to_field = request.POST.get('_to_field', request.GET.get('_to_field'))
        if to_field and not self.to_field_allowed(request, to_field):
            raise DisallowedModelAdminToField("The field %s cannot be referenced." % to_field)

        obj = self.get_object(request, unquote(object_id), to_field)

        if not self.has_ship_permission(request, obj):
            raise PermissionDenied

        if obj is None:
            return self._get_obj_does_not_exist_redirect(request, opts, object_id)

        title = _("New Shipment for Order #" + obj.get_order_id())
        context = {
            **self.admin_site.each_context(request),
            'title': title,
            'object': obj,
            'opts': obj._meta,
            'to_field': to_field,
            'shipping_amount': obj.shipping_amount,
            'shipping_tax_amount': obj.shipping_tax_amount,
            **(extra_context or {}),
        }

        msg_dict = {
            'name': opts.verbose_name,
            'obj': format_html('<a href="{}">{}</a>', urlquote(request.path), obj),
        }

        request.current_app = self.admin_site.name
        context.update(to_field_var='_to_field', media=self.media, )

        if request.POST:  # The user has confirmed the deletion.
            ship_items_post_data = helper.get_html_input_dict(request.POST, 'shipping_items')
            confirmed = True if 'confirmation_checked' in request.POST and request.POST['confirmation_checked'] == '1' else False
            if ship_items_post_data and isinstance(ship_items_post_data, dict):
                ret = obj.ship({'items': ship_items_post_data, 'confirmed': confirmed})
                if isinstance(ret, dict):
                    context['check_confirmation'] = True
                    context.update(ret)
                    if ret['errors']:
                        context['check_confirmation'] = False
                        for er in ret['errors']:
                            self.message_user(request, er, messages.ERROR)

                    if context['check_confirmation']:
                        context['title'] = 'Confirm ' + context['title']
                    return TemplateResponse(request, 'admin/order_ship_intermediate.html', context, )
                    # return redirect(request.path_info)
                elif isinstance(ret, str):
                    context['check_confirmation'] = False
                    self.message_user(request, ret, messages.ERROR)
                    # return TemplateResponse(request, 'admin/order_ship_intermediate.html', context, )
                    return redirect(request.path_info)
                elif isinstance(ret, bool):
                    if ret:
                        msg = format_html(
                            _('Shipment created for {name} “{obj}”.'),
                            **msg_dict
                        )
                        self.message_user(request, msg, messages.SUCCESS)
                    else:
                        msg = format_html(_('Could not process the shipment for {name} “{obj}”'), **msg_dict)
                        self.message_user(request, msg, messages.ERROR)
                else:
                    msg = format_html(_('Could not process the shipment for {name} “{obj}”'), **msg_dict)
                    self.message_user(request, msg, messages.ERROR)

            if not redirect_url:
                redirect_url = reverse('admin:%s_%s_change' % (opts.app_label, opts.model_name), args=(obj.pk,),
                                       current_app=self.admin_site.name)
            preserved_filters = self.get_preserved_filters(request)
            redirect_url = add_preserved_filters({'preserved_filters': preserved_filters, 'opts': opts}, redirect_url)
            return HttpResponseRedirect(redirect_url)

        else:
            context.update(obj.prepare_shippable_data())

        return TemplateResponse(request, 'admin/order_ship_intermediate.html', context,)

        # return self.render_delete_form(request, context)

    def _invoice_view(self, request, object_id, extra_context):
        from ..utils import helper

        redirect_url = ''
        opts = self.model._meta

        to_field = request.POST.get('_to_field', request.GET.get('_to_field'))
        if to_field and not self.to_field_allowed(request, to_field):
            raise DisallowedModelAdminToField("The field %s cannot be referenced." % to_field)

        obj = self.get_object(request, unquote(object_id), to_field)

        if not self.has_invoice_permission(request, obj):
            raise PermissionDenied

        if obj is None:
            return self._get_obj_does_not_exist_redirect(request, opts, object_id)

        title = _("New Invoice for Order #" + obj.get_order_id())
        context = {
            **self.admin_site.each_context(request),
            'title': title,
            'object': obj,
            'opts': obj._meta,
            'to_field': to_field,
            'shipping_amount': obj.get_invoiceable_shipping_amount(),
            'shipping_tax_amount': obj.get_invoiceable_shipping_tax_amount(),
            **(extra_context or {}),
        }

        msg_dict = {
            'name': opts.verbose_name,
            'obj': format_html('<a href="{}">{}</a>', urlquote(request.path), obj),
        }

        request.current_app = self.admin_site.name
        context.update(to_field_var='_to_field', media=self.media, )

        if request.POST:  # The user has confirmed the deletion.
            invoice_items_post_data = helper.get_html_input_dict(request.POST, 'invoice_items')
            confirmed = True if 'confirmation_checked' in request.POST and request.POST['confirmation_checked'] == '1' else False
            if invoice_items_post_data and isinstance(invoice_items_post_data, dict):
                ret = obj.invoice({'items': invoice_items_post_data, 'confirmed': confirmed})
                if isinstance(ret, dict):
                    context['check_confirmation'] = True
                    context.update(ret)
                    if ret['errors']:
                        context['check_confirmation'] = False
                        for er in ret['errors']:
                            self.message_user(request, er, messages.ERROR)

                    if context['check_confirmation']:
                        context['title'] = 'Confirm ' + context['title']
                    return TemplateResponse(request, 'admin/order_invoice_intermediate.html', context, )
                elif isinstance(ret, str):
                    context['check_confirmation'] = False
                    self.message_user(request, ret, messages.ERROR)
                    # return TemplateResponse(request, 'admin/order_invoice_intermediate.html', context, )
                    return redirect(request.path_info)
                elif isinstance(ret, bool):
                    if ret:
                        msg = format_html(
                            _('Invoice created for {name} “{obj}”. If the payment was previously been authorized '
                              'it\'s also been captured'),
                            **msg_dict
                        )
                        self.message_user(request, msg, messages.SUCCESS)
                    else:
                        msg = format_html(_('Could not process the invoice for {name} “{obj}”'), **msg_dict)
                        self.message_user(request, msg, messages.ERROR)
                else:
                    msg = format_html(_('Could not process the invoice for {name} “{obj}”'), **msg_dict)
                    self.message_user(request, msg, messages.ERROR)

            if not redirect_url:
                redirect_url = reverse('admin:%s_%s_change' % (opts.app_label, opts.model_name), args=(obj.pk,),
                                       current_app=self.admin_site.name)
            preserved_filters = self.get_preserved_filters(request)
            redirect_url = add_preserved_filters({'preserved_filters': preserved_filters, 'opts': opts}, redirect_url)
            return HttpResponseRedirect(redirect_url)

        else:
            context.update(obj.prepare_invoicable_data())

        return TemplateResponse(request, 'admin/order_invoice_intermediate.html', context,)

        # return self.render_delete_form(request, context)

    def _refund_view(self, request, object_id, extra_context):
        from ..utils import helper

        redirect_url = ''
        opts = self.model._meta

        to_field = request.POST.get('_to_field', request.GET.get('_to_field'))
        if to_field and not self.to_field_allowed(request, to_field):
            raise DisallowedModelAdminToField("The field %s cannot be referenced." % to_field)

        obj = self.get_object(request, unquote(object_id), to_field)

        if not self.has_credit_memo_permission(request, obj):
            raise PermissionDenied

        if obj is None:
            return self._get_obj_does_not_exist_redirect(request, opts, object_id)

        title = _("New Credit Memo for Order #" + obj.get_order_id())
        context = {
            **self.admin_site.each_context(request),
            'title': title,
            'object': obj,
            'opts': obj._meta,
            'to_field': to_field,
            'shipping_amount': obj.get_refundable_shipping_amount(),
            'shipping_tax_amount': obj.get_refundable_shipping_tax_amount(),
            **(extra_context or {}),
        }

        msg_dict = {
            'name': opts.verbose_name,
            'obj': format_html('<a href="{}">{}</a>', urlquote(request.path), obj),
        }

        request.current_app = self.admin_site.name
        context.update(to_field_var='_to_field', media=self.media, )

        if request.POST:  # The user has confirmed the deletion.
            items_post_data = helper.get_html_input_dict(request.POST, 'items')
            shipping_post_data = request.POST['shipping'] if 'shipping' in request.POST else 0

            confirmed = True if 'confirmation_checked' in request.POST and request.POST['confirmation_checked'] == '1' else False
            if (items_post_data and isinstance(items_post_data, dict)) or shipping_post_data:
                ret = obj.refund({'items': items_post_data, 'confirmed': confirmed, 'shipping': shipping_post_data})
                if isinstance(ret, dict):
                    context['check_confirmation'] = True
                    context.update(ret)
                    if ret['errors']:
                        context['check_confirmation'] = False
                        for er in ret['errors']:
                            self.message_user(request, er, messages.ERROR)

                    if context['check_confirmation']:
                        context['title'] = 'Confirm ' + context['title']
                    return TemplateResponse(request, 'admin/order_credit_memo_intermediate.html', context, )
                elif isinstance(ret, str):
                    context['check_confirmation'] = False
                    self.message_user(request, ret, messages.ERROR)
                    # return TemplateResponse(request, 'admin/order_invoice_intermediate.html', context, )
                    return redirect(request.path_info)
                elif isinstance(ret, bool):
                    if ret:
                        msg = format_html(
                            _('Credit Memo created for {name} “{obj}”. If the payment was previously been authorized '
                              'it\'s also been voided'),
                            **msg_dict
                        )
                        self.message_user(request, msg, messages.SUCCESS)
                    else:
                        msg = format_html(_('Could not process the credit memo for {name} “{obj}”'), **msg_dict)
                        self.message_user(request, msg, messages.ERROR)
                else:
                    msg = format_html(_('Could not process the credit memo for {name} “{obj}”'), **msg_dict)
                    self.message_user(request, msg, messages.ERROR)

            if not redirect_url:
                redirect_url = reverse('admin:%s_%s_change' % (opts.app_label, opts.model_name), args=(obj.pk,),
                                       current_app=self.admin_site.name)
            preserved_filters = self.get_preserved_filters(request)
            redirect_url = add_preserved_filters({'preserved_filters': preserved_filters, 'opts': opts}, redirect_url)
            return HttpResponseRedirect(redirect_url)

        else:
            context.update(obj.prepare_refundable_data())

        return TemplateResponse(request, 'admin/order_credit_memo_intermediate.html', context,)

        # return self.render_delete_form(request, context)

    def _cancel_view(self, request, object_id, extra_context):

        # from ..utils import AuthnetCIM
        # details = AuthnetCIM().get_transaction_details(60159583522)

        opts = self.model._meta
        app_label = opts.app_label

        to_field = request.POST.get('_to_field', request.GET.get('_to_field'))
        if to_field and not self.to_field_allowed(request, to_field):
            raise DisallowedModelAdminToField("The field %s cannot be referenced." % to_field)

        obj = self.get_object(request, unquote(object_id), to_field)

        if not self.has_cancel_permission(request, obj):
            raise PermissionDenied

        if obj is None:
            return self._get_obj_does_not_exist_redirect(request, opts, object_id)

        if request.POST:  # The user has confirmed the deletion.
            # obj_display = str(obj)
            # attr = str(to_field) if to_field else opts.pk.attname
            # obj_id = obj.serializable_value(attr)

            cancelled = obj.cancel()
            msg_dict = {
                'name': opts.verbose_name,
                'obj': format_html('<a href="{}">{}</a>', urlquote(request.path), obj),
            }
            if cancelled:
                msg = format_html(
                    _('{name} “{obj}” was cancelled successfully'),
                    **msg_dict
                )
                self.message_user(request, msg, messages.SUCCESS)
            else:
                msg = format_html(_('Could not process the cancellation for {name} “{obj}”'), **msg_dict)
                self.message_user(request, msg, messages.ERROR)

            redirect_url = reverse('admin:%s_%s_change' % (opts.app_label, opts.model_name), args=(obj.pk,),
                                   current_app=self.admin_site.name)
            preserved_filters = self.get_preserved_filters(request)
            redirect_url = add_preserved_filters({'preserved_filters': preserved_filters, 'opts': opts}, redirect_url)
            return HttpResponseRedirect(redirect_url)

        object_name = str(opts.verbose_name)
        title = _("Are you sure to cancel: Order #" + obj.get_order_id())

        context = {
            **self.admin_site.each_context(request),
            'title': title,
            'object_name': object_name,
            'object': obj,
            'opts': opts,
            'app_label': app_label,
            'preserved_filters': self.get_preserved_filters(request),
            'is_popup': False,
            'to_field': to_field,
            **(extra_context or {}),
        }

        request.current_app = self.admin_site.name
        context.update(to_field_var='_to_field', media=self.media,)

        return TemplateResponse(request, 'admin/order_cancel_intermediate.html', context,)

        # return self.render_delete_form(request, context)

    # def response_change(self, request, obj):
    #     from django.utils.translation import gettext as _, ngettext
    #
    #     opts = self.model._meta
    #     pk_value = obj._get_pk_val()
    #     preserved_filters = self.get_preserved_filters(request)
    #
    #     msg_dict = {
    #         'name': opts.verbose_name,
    #         'obj': format_html('<a href="{}">{}</a>', urlquote(request.path), obj),
    #     }
    #
    #     if "_invoice" in request.POST or "_ship" in request.POST:
    #         if "_invoice_apply" in request.POST:
    #             invoiced = obj.invoice()
    #             if invoiced:
    #                 msg = format_html(
    #                     _('Invoice created for {name} “{obj}”. If the payment was previously been authorized '
    #                       'it\'s also been captured'),
    #                     **msg_dict
    #                 )
    #                 self.message_user(request, msg, messages.SUCCESS)
    #             else:
    #                 msg = format_html(_('Could not process the invoice for {name} “{obj}”'), **msg_dict)
    #                 self.message_user(request, msg, messages.ERROR)
    #
    #         if "_invoice" in request.POST:
    #             return render(request, 'admin/order_invoice_intermediate.html', context={})
    #
    #         # handle the action on your obj
    #         redirect_url = reverse('admin:%s_%s_change' % (opts.app_label, opts.model_name), args=(pk_value,),
    #                                current_app=self.admin_site.name)
    #         redirect_url = add_preserved_filters({'preserved_filters': preserved_filters, 'opts': opts}, redirect_url)
    #         return HttpResponseRedirect(redirect_url)
    #     else:
    #         return super(OrderAdmin, self).response_change(request, obj)


admin.site.register(Order, OrderAdmin)
