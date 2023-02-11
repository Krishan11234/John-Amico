
from django.db import models

from ..utils.static import PRODUCT_TYPE_CHOICES


class CreditMemoItem(models.Model):
    id = models.AutoField(primary_key=True)
    credit_memo = models.ForeignKey(
        'CreditMemo',
        on_delete=models.CASCADE,
        db_index=True,
    )
    product = models.ForeignKey(
        'Product',
        on_delete=models.CASCADE,
        db_index=True,
        null=True,
    )
    product_type = models.CharField(max_length=255, db_index=True, choices=PRODUCT_TYPE_CHOICES, default='simple',
                                    blank=True, null=True)
    sku = models.CharField(max_length=100, blank=True, null=True)
    name = models.CharField(max_length=255, blank=True, null=True)
    price = models.DecimalField(max_digits=15, decimal_places=4, blank=True, default=0, db_index=True)

    quantity = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=1, db_index=True)

    option = models.IntegerField(blank=True, null=True)
    option_title = models.CharField(max_length=255, blank=True, null=True)
    option_sku = models.CharField(max_length=255, blank=True, null=True)

    tax_percent = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0, db_index=True)
    tax_amount = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0, db_index=True)

    discount_percent = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0, db_index=True)
    discount_amount = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0, db_index=True)

    amount_refunded = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=0, db_index=True)

    currency = models.CharField(max_length=6, blank=True, null=True)
    row_subtotal = models.DecimalField(max_digits=15, decimal_places=4, blank=True, default=0, db_index=True)
    row_total = models.DecimalField(max_digits=15, decimal_places=4, blank=True, default=0, db_index=True)

    is_free_product = models.BooleanField(blank=True, null=False, default=False)

    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        verbose_name_plural = 'Credit Memo Items'
        ordering = ['-created_at']

    def __str__(self):
        return 'Credit Memo Item #{} for Credit Memo: {}'.format(self.sku, self.credit_memo.get_credit_memo_id())

    def get_row_subtotal(self):
        subtotal = float(self.row_subtotal)
        if subtotal <= 0:
            subtotal = float(self.price) * float(self.quantity)

        return subtotal

    def get_attributes(self):
        attrs = {
            'sku': {'title': 'SKU', 'value': self.sku + ("-"+self.option_sku if self.option_sku else '')},
            # 'option_sku': {'title': 'Option SKU', 'value': self.option_sku},
        }
        if self.option_title:
            attrs['option_title'] = {'title': 'Size', 'value': self.option_title}

        return attrs

    def save(self, force_insert=False, force_update=False, using=None, update_fields=None, external_use=False):

        super().save(force_insert, force_update, using, update_fields)

    def delete(self, using=None, keep_parents=False):

        super().delete(using=None, keep_parents=False)


