from django.contrib import admin

# from django_summernote.admin import SummernoteModelAdmin, SummernoteInlineModelAdmin

from adminsortable.admin import NonSortableParentAdmin, SortableStackedInline

from django.utils.html import format_html
from django.utils.safestring import mark_safe
from django.urls import reverse
from django.conf import settings

from ..models import Invoice, InvoiceItem
from ..utils import helper


class InvoiceItemInline(admin.TabularInline):
    verbose_name_plural = "INVOICE ITEMS"
    model = InvoiceItem
    extra = 0
    can_delete = False
    fields = (
        'name',
        'sku',
        'quantity_details',
        'item_unit_price',
        'item_total_price',
    )
    readonly_fields = (
        'name',
        'sku',
        'quantity_details',
        'item_unit_price',
        'item_total_price',
    )

    def has_add_permission(self, request, obj=None):
        return False

    def quantity_details(self, obj):
        if obj.quantity:
            return '{:.1f}'.format(obj.quantity)

    def item_unit_price(self, obj):
        if obj.price:
            return '{:.2f}'.format(obj.price)

    def item_total_price(self, obj):
        if obj.price and obj.quantity:
            total = float(obj.price) * float(obj.quantity)
            return '{:.2f}'.format(total)
        return '-'


class InvoiceTotalsInline(admin.StackedInline):
    from django.utils.datastructures import ImmutableList
    from django.db import models

    verbose_name = "Invoice"
    verbose_name_plural = "Invoice Totals"
    model = Invoice
    extra = 0
    can_delete = False
    fields = ('sub_total', 'shipping_amount', 'tax_amount', 'grand__total')
    readonly_fields = ('sub_total', 'shipping_amount', 'tax_amount', 'grand__total')
    # fk_name = 'invoice'
    #
    # # model._meta.fields[00].remote_field = helper.dotdict({'model': InvoiceTotal})
    # invoice = models.ForeignKey(Invoice, on_delete=models.NOT_PROVIDED, blank=True, null=True,)
    # invoice.attname = 'invoice_id'
    # invoice.name = 'invoice'
    # invoice.concrete = True
    # invoice.column = 'id'
    # invoice.db_column = 'id'
    #
    # copied_fields = list(model._meta.fields)
    # copied_fields.append(invoice)
    # model._meta.fields = ImmutableList(tuple(copied_fields))
    # model._meta

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

    def has_add_permission(self, request, obj=None):
        return False

    def has_change_permission(self, request, obj=None):
        return False

    # @property
    # def media(self):
    #     from django import forms
    #
    #     extra = '' if settings.DEBUG else '.min'
    #     js = ['order_view%s.js' % extra]
    #     statics = super().media + forms.Media(js=['admin/js/%s' % url for url in js])
    #     return statics
    #
    # def get_formset(self, request, obj=None, **kwargs):
    #     super_sets = super().get_formset(request, obj, **kwargs)
    #     super_sets.get_default_prefix = lambda: "invoicetotal"
    #     return super_sets


class InvoiceAdmin(NonSortableParentAdmin):
    list_display = (
        'id',
        'increment_id',
        'order_increment_id',
        'billing_info',
        'shipping_info',
        'sub_total',
        'shipping_price',
        'grand__total',
        'status',
        'invoice_date',
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
        'order.customer_email',
        'order.customer_firstname',
        'order.customer_lastname',
    )
    readonly_fields = ('status',
                       'subtotal', 'shipping_amount', 'tax_amount',
                       'grand_total')
    list_filter = ('status', 'created_at')

    inlines = (
        # InvoiceBillingAddressInline,
        # InvoiceShippingAddressInline,
        InvoiceItemInline,
        InvoiceTotalsInline,
    )
    fieldsets = (
        (
            'BASIC INFORMATION', {
                'fields': (
                    ('increment_id', 'invoice_date'),
                    ('order_increment_id',),
                    ('status',),
                ),
            }),
        (
            'CUSTOMER INFORMATION', {
                'fields': (
                    ('customer_firstname', 'customer_lastname',),
                    ('customer_email', ),
                ),
            }
        ),
        (
            'ADDRESS INFORMATION', {
                'fields': (
                    ('billing_info', ),
                    ('shipping_info',),
                ),
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
        return False

    def has_delete_permission(self, request, obj=None):
        if obj:
            return obj.can_delete()
        return False

    def customer_firstname(self, obj):
        return obj.order.customer_firstname

    def customer_lastname(self, obj):
        return obj.order.customer_lastname

    def customer_email(self, obj):
        return obj.order.customer_email

    def sub_total(self, obj):
        return '{:.2f}'.format(obj.subtotal)

    def shipping_price(self, obj):
        return '{:.2f}'.format(obj.shipping_amount)

    def grand__total(self, obj):
        return '{:.2f}'.format(obj.grand_total)

    def increment_id(self, obj):
        return obj.get_invoice_id()

    def order_increment_id(self, obj):
        order_url = reverse('admin:{}_{}_change'.format(obj._meta.app_label, obj.order._meta.model_name),
                              args=[obj.order.id])
        return mark_safe('<a target="_blank" href="{}">{}</a>'.format(order_url, obj.order.get_order_id()))

    def address_info(self, obj, address):
        if not address:
            return '-'

        customer_edit_url = reverse(
            'admin:{}_{}_change'.format(
                obj._meta.app_label,
                obj.order.customer._meta.model_name),
            args=[obj.order.customer.id]
        ) if obj.order.customer else ''
        return format_html(
            '{}<br><br>{}<br>{}<br>{}<br>{} {}',
            mark_safe('<a href="{}">{}</a>'.format(customer_edit_url,
                                                   address.get_fullname())) if obj.order.customer else address.get_fullname(),
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

    def invoice_date(self, obj):
        return obj.created_at.strftime('%b %d, %Y %I:%M %p')

    def customer_link(self, obj):
        if obj.order.customer:
            customer_edit_url = reverse(
                'admin:{}_{}_change'.format(
                    obj._meta.app_label,
                    obj.order.customer._meta.model_name),
                args=[obj.order.customer.id]
            )
            return format_html("{}", mark_safe('<a href="{}">Edit</a>'.format(customer_edit_url)))
        else:
            return 'N/A'


admin.site.register(Invoice, InvoiceAdmin)
