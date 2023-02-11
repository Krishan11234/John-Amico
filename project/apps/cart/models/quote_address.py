
from django.db import models
from django.db.models import Sum, Count, Q
from django.contrib.auth.models import User
from ..utils.static import ADDRESS_TYPE_CHOICES


class QuoteAddress(models.Model):
    id = models.AutoField(primary_key=True)
    quote = models.OneToOneField(
        'Quote',
        on_delete=models.CASCADE,
        db_index=True,
    )
    customer_address = models.ForeignKey(
        'CustomerAddress',
        on_delete=models.SET_NULL,
        db_index=True,
        blank=True,
        null=True
    )
    member_address = models.ForeignKey(
        'AddressBook',
        to_field='id',
        on_delete=models.SET_NULL,
        db_index=True,
        blank=True,
        null=True
    )
    shipping_as_billing = models.BooleanField(blank=True, null=True)

    address_type = models.CharField(max_length=50, blank=True, null=True, choices=ADDRESS_TYPE_CHOICES, default='billing')

    firstname = models.CharField(max_length=50, blank=True, null=True, db_index=True)
    lastname = models.CharField(max_length=50, blank=True, null=True, db_index=True)
    email = models.CharField(max_length=100, blank=True, null=True, db_index=True)
    address1 = models.CharField(max_length=100, blank=True, null=True)
    address2 = models.CharField(max_length=50, blank=True, null=True)
    city = models.CharField(max_length=20, blank=True, null=True)
    state = models.CharField(max_length=10, blank=True, null=True)
    zip = models.CharField(max_length=10, blank=True, null=True)
    country = models.CharField(max_length=20, blank=True, null=True)
    company = models.CharField(max_length=100, blank=True, null=True)
    telephone = models.CharField(max_length=15, blank=True, null=True)
    # phone_ext = models.CharField(max_length=5, blank=True, null=True)
    # phone_type = models.CharField(max_length=10, blank=True, null=True)
    # mobile_no = models.CharField(max_length=15, blank=True, null=True)

    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        verbose_name_plural = 'cart'
        unique_together = ['address_type', 'quote']
        ordering = ['-created_at']

    def __str__(self):
        return 'Quote Address #{} for Quote: {}'.format(self.id, self.quote.id)

    def save(self, force_insert=False, force_update=False, using=None, update_fields=None, external_use=False):

        super().save(force_insert, force_update, using, update_fields)

    def delete(self, using=None, keep_parents=False):

        super().delete(using=None, keep_parents=False)


