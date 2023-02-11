from django.db import models


class CreditMemoTransaction(models.Model):
    id = models.AutoField(primary_key=True)

    credit_memo = models.ForeignKey('CreditMemo', on_delete=models.CASCADE, blank=True, null=False)
    # order_payment = models.ForeignKey('OrderPayment', on_delete=models.CASCADE, db_index=True, blank=True, null=True)
    amount = models.DecimalField(max_digits=12, decimal_places=4, blank=True, default=0, db_index=True)
    transaction_id = models.CharField(max_length=100, blank=False, null=True, unique=False)
    created_at = models.DateTimeField(auto_now_add=True, blank=True)

    class Meta:
        verbose_name_plural = 'Credit Memo Transactions'
        ordering = ['-created_at']

    def __str__(self):
        return 'Credit Memo Transaction #{} with Total of: {} for Credit Memo #{}'.format(self.transaction_id, self.amount,
                                                                        self.credit_memo.get_credit_memo_id())


