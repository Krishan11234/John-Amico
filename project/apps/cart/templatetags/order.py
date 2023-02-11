from django import template
from django.template.defaulttags import register
# register = template.Library()


@register.inclusion_tag('admin/order_change_form__submit_line.html', takes_context=True)
def custom_submit_row(context):
    """
    Displays the row of buttons for delete and save.
    """
    opts = context['opts']
    change = context['change']
    is_popup = context['is_popup']
    save_as = context['save_as']
    has_add_permission = context['has_add_permission']
    has_change_permission = context['has_change_permission']
    has_delete_permission = context['has_delete_permission']
    obj = context['original']
    has_invoice_permission = obj.can_invoice() if obj else False
    has_ship_permission = obj.can_ship() if obj else False
    has_cancel_permission = obj.can_cancel() if obj else False
    has_credit_memo_permission = obj.can_credit_memo() if obj else False
    ctx = {
        'opts': opts,
        'show_delete_link': (not is_popup and has_delete_permission and change and context.get('show_delete', True)),
        'show_save_as_new': not is_popup and change and save_as,
        'show_save_and_add_another': (has_add_permission and not is_popup and (not save_as or context['add'])),
        'show_save_and_continue': not is_popup and has_change_permission,
        'is_popup': is_popup,
        'show_save': has_change_permission,
        'show_invoice': has_invoice_permission,
        'show_shipment': has_ship_permission,
        'show_cancel': has_cancel_permission,
        'show_refund': has_credit_memo_permission,
        'preserved_filters': context.get('preserved_filters'),
        # ''
    }
    if context.get('original') is not None:
        ctx['original'] = context['original']
    return ctx
