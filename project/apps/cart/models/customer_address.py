from django.db import models
from django.contrib.auth.models import User
from ..utils.static import US_STATES, ADDRESS_TYPE_CHOICES, COUNTRIES

from ..utils import helper


class CustomerAddress(models.Model):
    id = models.AutoField(primary_key=True)
    customer = models.ForeignKey(
        User,
        on_delete=models.PROTECT,
        db_index=True,
    )
    customer_extra = models.ForeignKey(
        'CustomerExtra',
        on_delete=models.CASCADE,
        db_index=True,
        null=True
    )
    # address_type = models.CharField(max_length=50, db_index=True, choices=ADDRESS_TYPE_CHOICES, default='billing')
    # shipping_as_billing = models.BooleanField(blank=True, null=True)
    address_checksum = models.CharField(max_length=200, blank=True, null=False, unique=True)
    firstname = models.CharField(max_length=50, blank=True, null=True, db_index=True)
    lastname = models.CharField(max_length=50, blank=True, null=True, db_index=True)
    email = models.CharField(max_length=100, blank=True, null=True, db_index=True)
    address1 = models.CharField(max_length=255, blank=True, null=True)
    address2 = models.CharField(max_length=50, blank=True, null=True)
    city = models.CharField(max_length=100, blank=True, null=True)
    state = models.CharField(max_length=100, blank=True, null=True, choices=US_STATES)
    zip = models.CharField(max_length=20, blank=True, null=True)
    country = models.CharField(max_length=5, blank=True, null=True, choices=COUNTRIES, default='US')
    company = models.CharField(max_length=255, blank=True, null=True)
    telephone = models.CharField(max_length=50, blank=True, null=True)
    # phone_ext = models.CharField(max_length=45, blank=True, null=True)
    # phone_type = models.CharField(max_length=45, blank=True, null=True)
    # mobile_no = models.CharField(max_length=45, blank=True, null=True)
    comments = models.TextField(blank=True, null=True)

    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        ordering = ['firstname']

    def __str__(self):
        return 'Address of {}'.format(self.customer.email)

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

    def get_self_address_hash(self):
        if not self.address_checksum:
            checksum_str = str(self.customer_id) + self.firstname.strip() + self.lastname.strip() + self.address1.strip() \
                           + self.address2.strip() + self.city.strip() + self.state.strip() + self.country.strip() \
                           + self.telephone.strip()
            return helper.make_md5_string(checksum_str)

    @staticmethod
    def get_address_hash(data):
        if isinstance(data, dict):
            checksum_str = str(data.get('customer_id', '')) + data.get('firstname', '').strip() + \
                           data.get('lastname', '').strip() + data.get('address1', '').strip() + \
                           data.get('address2', '').strip() + data.get('city', '').strip() + data.get('state', '').strip() \
                           + data.get('country', '').strip() + data.get('telephone', '').strip()

            return helper.make_md5_string(checksum_str)

    def save(self, force_insert=False, force_update=False, using=None, update_fields=None):
        md5_checksum = self.get_self_address_hash()
        self.address_checksum = md5_checksum

        super().save(force_insert, force_update, using, update_fields)
