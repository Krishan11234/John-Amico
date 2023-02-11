from django.dispatch import Signal
from .utils.recurring_signal import RecurringSignal


autoloaded_menus = RecurringSignal(providing_args=["menus"])
single_loaded_menu = RecurringSignal(providing_args=["menu_code", "menu_result"])

shipping_methods = RecurringSignal(providing_args=["methods", "quote", "shipping_address"])
checkout_review_extra_html = RecurringSignal(providing_args=["context", "quote", "request"])

order_totals = RecurringSignal(providing_args=["totals", "quote"])
before_order_create = RecurringSignal(providing_args=["order_data"])

after_successful_order_create = Signal(providing_args=["order", "request"])
after_successful_invoice_create = Signal(providing_args=["object"])
after_successful_credit_memo_create = Signal(providing_args=["object"])


# product_admin_fields = RecurringSignal(providing_args=["fields"])
product_admin_list_display = RecurringSignal(providing_args=["list_display"])
product_admin_fieldsets__extra_field = Signal(providing_args=["fields"])
product_admin_inlines = Signal(providing_args=["inlines"])
product_admin_form = RecurringSignal(providing_args=["form"])
product_admin_form_save = Signal(providing_args=["form", "saved_form"])

order_item_attributes = RecurringSignal(providing_args=["attrs"])


