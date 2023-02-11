from .module_loader import import_sub_modules

import_sub_modules('admin')

# theModule = __import__("apps.cart.modules.order_tax.admin", fromlist=[''])
# locals().update(vars(theModule))
# from .order_tax.admin import *
