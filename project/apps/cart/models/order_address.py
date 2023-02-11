
from django.db import models
from django.db.models import Sum, Count, Q
from django.contrib.auth.models import User
from ..utils.static import ADDRESS_TYPE_CHOICES, US_STATES, COUNTRIES


class OrderAddress(models.Model):
    id = models.AutoField(primary_key=True)
    order = models.OneToOneField(
        'Order',
        on_delete=models.CASCADE,
        db_index=True,
    )
    customer_address = models.ForeignKey(
        'CustomerAddress',
        on_delete=models.CASCADE,
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
    quote_address = models.ForeignKey(
        'QuoteAddress',
        on_delete=models.CASCADE,
        db_index=True,
        blank=True,
        null=True
    )

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

    vat_id = models.IntegerField(blank=True, null=True)
    vat_is_valid = models.BooleanField(blank=True, null=True)

    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        verbose_name_plural = 'cart'
        ordering = ['-created_at']
        unique_together = ['order', 'address_type']

    def __str__(self):
        return '{} Address #{} for Order: {}'.format(self.address_type.capitalize(), self.id, self.order.id)

    @property
    def use_billing_address_as_shipping(self):
        shipping = self.order.get_shipping_address()
        return 'No' if shipping else 'Yes'

    def get_fullname(self):
        return self.full_name()

    def full_name(self):
        return '{} {}'.format(self.firstname, self.lastname)

    def to_string_format(self):
        return "{}, {}, {}, {} {}, {}".format(self.full_name(), self.address1, self.city, self.state, self.zip, self.country)

    def get_state(self):
        us_states = dict(US_STATES)
        return us_states[self.state] if self.state in us_states else self.state

    def get_country(self):
        countries = dict(COUNTRIES)
        return countries[self.country] if self.country in countries else self.country

    def save(self, force_insert=False, force_update=False, using=None, update_fields=None, external_use=False):

        super().save(force_insert, force_update, using, update_fields)

    def delete(self, using=None, keep_parents=False):

        super().delete(using=None, keep_parents=False)


