from django.db import models
from django.contrib.auth.models import User
from django.utils import timezone
from django.utils.translation import gettext_lazy as _

from ..utils import static


class AuthnetcimCards(models.Model):
    id = models.AutoField(primary_key=True)
    # customer = models.ForeignKey(
    #     User,
    #     on_delete=models.CASCADE,
    #     db_index=True,
    #     blank=True,
    #     null=True
    # )
    # customer_extra = models.ForeignKey(
    #     'CustomerExtra',
    #     on_delete=models.CASCADE,
    #     db_index=True,
    #     blank=True,
    #     null=True
    # )
    authnetcim_customer = models.ForeignKey('AuthnetcimCustomers', on_delete=models.CASCADE, blank=True, null=False)
    authnetcim_payment_profile_id = models.CharField(max_length=100, blank=False, null=False)
    card_owner = models.CharField(max_length=200, blank=True, null=True)
    card_type = models.CharField(max_length=20, blank=True, null=True, choices=static.CARD_TYPES)
    card_last4 = models.IntegerField(blank=False, null=False)
    card_exp_month = models.IntegerField(blank=False, null=False)
    card_exp_year = models.IntegerField(blank=False, null=False)
    is_disabled = models.BooleanField(blank=True, null=False, default=False)

    created_at = models.DateTimeField(auto_now_add=True, blank=True)

    class Meta:
        ordering = ['authnetcim_payment_profile_id']
        unique_together = ('authnetcim_customer_id', 'authnetcim_payment_profile_id')

    def __str__(self):
        return 'Customer Profile: {} - Card# {} - xxxxx-{} - Exp: {}/{}'.format(
            self.authnetcim_customer.customer_profile_id, self.card_type, self.card_last4, self.card_exp_month, self.card_exp_year)
