from django.db import models
from solo.models import SingletonModel

from ..utils.static import AUTHORIZENETCIM_PAYMENT_ACTION_TYPES, AUTHORIZENETCIM_PAYMENT_VALIDATION_TYPES, CURRENCIES


class PaymentAuthnetCIMMethod(SingletonModel):
    # site_config = models.ForeignKey(SiteConfig, blank=True, null=True, on_delete=models.CASCADE)

    is_enabled = models.BooleanField(blank=True, null=False, default=True)
    title = models.CharField(blank=False, null=False, max_length=100, verbose_name='Method Title',
                             default='Credit Card (Authorize.Net CIM)')
    api_key = models.CharField(blank=False, null=False, max_length=100, verbose_name='API Login ID')
    transaction_key = models.CharField(blank=False, null=False, max_length=100, verbose_name='Transaction ID')
    is_sandbox = models.BooleanField(blank=True, null=False, default=False, verbose_name='Sandbox Account')
    payment_action = models.CharField(blank=False, null=False, max_length=50,
                                      choices=AUTHORIZENETCIM_PAYMENT_ACTION_TYPES, default='auth_only')
    validation_type = models.CharField(blank=False, null=False, max_length=50,
                                       choices=AUTHORIZENETCIM_PAYMENT_VALIDATION_TYPES, default='testMode')
    allowed_currency = models.CharField(blank=False, null=False, max_length=10, choices=CURRENCIES, default='USD')

    def __str__(self):
        return "Authorize.net (CIM) Method"

    def get_form_html_path(self):
        return 'frontend/parts/page/checkout/payment_methods/authnetcim.html'

    def is_active(self, quote=None):
        return self.is_enabled

    @staticmethod
    def get_code():
        return 'authnetcim'

    def get_handler_class(self):
        from ..utils.payment.authnetcim import AuthnetCIM
        return AuthnetCIM()

    def get_template_context(self):
        from ..utils import static

        context = {'cc_exp_years': dict(static.CARD_EXP_YEARS), 'cc_exp_months': dict(static.CARD_EXP_MONTHS),
                   'cc_types': dict(static.CARD_TYPES), 'saved_cards': self.get_authorizenet_cards(), 'method': self, }

        return context

    def get_authorizenet_cards(self):
        from ..utils import helper
        from ..models import CustomerExtra, MemberExtra, AuthnetcimCustomers

        conditions = {}
        cards = {}

        if helper.is_customer_logged_in():
            customer_q = CustomerExtra.objects.filter(customer=helper.get_current_customer())
            if customer_q:
                if customer_q.exists():
                    customer = customer_q.get().get_customer()
                    conditions['customer'] = customer

        if helper.is_professional_logged_in():
            customer_q = MemberExtra.objects.filter(tbl_member=helper.get_current_member().get_member())
            if customer_q:
                if customer_q.exists():
                    customer = customer_q.get().get_customer()
                    conditions['tbl_member'] = customer

        if conditions:
            a_cus_q = AuthnetcimCustomers.objects.filter(**conditions)
            if a_cus_q.exists():
                cards = a_cus_q.get().get_payment_objects()

        return cards

    class Meta:
        verbose_name = "Authorize.net (CIM) Method"
