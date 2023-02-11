from django.dispatch import receiver

from .... import signals
from ....utils import helper, CartUtils, OrderUtil, Menu
from ....views.checkout import CheckoutView


# Add the "Autoship Field" for Order
@receiver(signals.checkout_review_extra_html, sender=CheckoutView, priority=1000)
def add__autoship_html__checkout_review_html(*args, **kwargs):
    from ....models import Quote
    from django.template import loader

    context = kwargs['context']
    quote = kwargs['quote']
    request = kwargs['request']

    output = ''

    if isinstance(context, dict) and isinstance(quote, Quote) and isinstance(request, object):
        template_name = 'cart/modules/autoship_order/templates/frontend/checkout_reivew_autoship.html'
        output += loader.render_to_string(template_name, context, request)

    return output


@receiver(signals.single_loaded_menu, sender=Menu, priority=1000)
def add__autoship_menu__my_account_menu(*args, **kwargs):

    menu_code = kwargs['menu_code']
    menu_result = kwargs['menu_result']

    if menu_code and menu_code == 'my_account':
        if menu_result and isinstance(menu_result, dict):
            if 'static_links' in menu_result:

                autoship_menu = helper.BlankClass()
                autoship_menu.label = 'Autoship Requests'
                autoship_menu.css_class = ''
                autoship_menu.permalink = 'jaautoship/manage'
                autoship_menu.get_absolute_url = helper.get_base_url() + '/' + autoship_menu.permalink

                static_links = list(menu_result['static_links'])
                static_links.append(autoship_menu)

                menu_result['static_links'] = static_links

    return menu_result
