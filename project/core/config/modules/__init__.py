update_able_settings = ['ADMIN_REORDER']
settings = {}
modules = []

from . import cart__order_tax, cart__ja_exporter, cart__product_bin_loc
modules.append(cart__order_tax)
modules.append(cart__ja_exporter)
modules.append(cart__product_bin_loc)

for module in modules:
    for d in dir(module):
        if d in update_able_settings:
            value = getattr(module, d)
            if d not in settings.keys():
                settings[d] = value
            else:
                if isinstance(value, (list, tuple)):
                    settings[d] += value

