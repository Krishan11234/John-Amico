from django.contrib import admin

# from django_summernote.admin import SummernoteModelAdmin, SummernoteInlineModelAdmin

from adminsortable.admin import NonSortableParentAdmin, SortableStackedInline

from django.utils.html import format_html
from django.utils.safestring import mark_safe
from django.urls import reverse
from django.conf import settings

from ..models import Shipment, ShipmentItem
from ..utils import helper


class ShipmentItemInline(admin.TabularInline):
    verbose_name_plural = "SHIPMENT ITEMS"
    model = ShipmentItem
    extra = 0
    can_delete = False
    fields = (
        'name',
        'sku',
        'quantity_details',
    )
    readonly_fields = (
        'name',
        'sku',
        'quantity_details',
    )

    def has_add_permission(self, request, obj=None):
        return False

    def quantity_details(self, obj):
        if obj.quantity:
            return '{:.1f}'.format(obj.quantity)


class ShipmentAdmin(NonSortableParentAdmin):
    list_display = (
        'id',
        'increment_id',
        'order_increment_id',
        'billing_info',
        'shipping_info',
        'status',
        'shipment_date',
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
    readonly_fields = ('status',)
    list_filter = ('status', 'created_at')

    inlines = (
        # InvoiceBillingAddressInline,
        # InvoiceShippingAddressInline,
        ShipmentItemInline,
    )
    fieldsets = (
        (
            'BASIC INFORMATION', {
                'fields': (
                    ('increment_id', 'shipment_date'),
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

    def increment_id(self, obj):
        return obj.get_id()

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

    def shipment_date(self, obj):
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


admin.site.register(Shipment, ShipmentAdmin)
