from django.db import models


class AuthnetcimCardsTransactions(models.Model):
    id = models.AutoField(primary_key=True)

    authnetcim_card = models.ForeignKey('AuthnetcimCards', on_delete=models.CASCADE, blank=True, null=False)
    payment_transaction = models.ForeignKey('PaymentTransaction', on_delete=models.CASCADE, blank=True, null=False)
    created_at = models.DateTimeField(auto_now_add=True, blank=True)
