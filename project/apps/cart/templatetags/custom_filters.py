import math
from django import template
from django.template.defaultfilters import stringfilter


register = template.Library()


@register.filter(name='page_title', is_safe=True)
@stringfilter
def page_title(current_page_title, add_default_title=1, *args, **kwargs):
    from ..models import SiteConfig
    """
    @TODO: Add "Site title" field to the site configuration 
    """

    default_title = ""
    config = SiteConfig.get_solo()
    if config.site_title:
        default_title = config.site_title

    if current_page_title:
        if add_default_title:
            return '{} | {}'.format(current_page_title, default_title)
        else:
            return current_page_title
    else:
        return default_title


@register.filter
def get_dict_value(dictionary, key):
    default_value = ''
    if key in ['different_shipping']:
        default_value = 1

    if dictionary and key:
        value = dictionary.get(key)
        return value if dictionary.get(key) else default_value
    return default_value


@register.filter(name='divide')
def divide(dividend, divisor):
    return math.ceil(int(dividend) / divisor)


@register.filter(name='replacedash')
def replacedash(inputstring):
    return inputstring.replace('-', ' ')


@register.filter(name='custom_strip')
def custom_strip(inputstring, stripped_char=' '):
    return inputstring.strip(stripped_char) if inputstring else inputstring
