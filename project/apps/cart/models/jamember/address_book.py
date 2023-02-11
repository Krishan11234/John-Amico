from django.db import models
from .country import Countries, Zones


class AddressBook(models.Model):
    id = models.AutoField(primary_key=True)
    customers = models.ForeignKey('Customers', on_delete=models.CASCADE, db_index=True, blank=True, null=True)
    address_book_id = models.IntegerField()
    entry_gender = models.CharField(max_length=1)
    entry_company = models.CharField(max_length=32, blank=True, null=True)
    entry_firstname = models.CharField(max_length=32)
    entry_lastname = models.CharField(max_length=32)
    entry_street_address = models.CharField(max_length=64)
    entry_street_address2 = models.CharField(max_length=255)
    entry_suburb = models.CharField(max_length=32, blank=True, null=True)
    entry_postcode = models.CharField(max_length=10)
    entry_city = models.CharField(max_length=32)
    entry_state = models.CharField(max_length=32, blank=True, null=True)
    # entry_country_id = models.IntegerField()
    # entry_zone_id = models.IntegerField()
    entry_country = models.ForeignKey(Countries, on_delete=models.CASCADE, null=True)
    entry_zone = models.ForeignKey(Zones, on_delete=models.CASCADE, null=True)

    class Meta:
        managed = False
        db_table = 'address_book'
        unique_together = (('customers_id', 'address_book_id'),)

    @staticmethod
    def get_address_types():
        return {1: 'billing', 2: 'shipping', 3: 'checking'}

    def full_name(self):
        return '{} {}'.format(self.entry_firstname, self.entry_lastname)

    @property
    def firstname(self):
        return self.entry_firstname

    @property
    def lastname(self):
        return self.entry_lastname

    @property
    def address_type(self):
        address_types = self.get_address_types()
        return address_types[self.address_book_id]

    @property
    def address1(self):
        return self.entry_street_address

    @property
    def address2(self):
        return self.entry_street_address2

    @property
    def city(self):
        return self.entry_city

    @property
    def state(self):
        return self.entry_state

    @property
    def zip(self):
        return self.entry_postcode

    @property
    def country(self):
        try:
            return self.entry_country.countries_name
        except Exception:
            try:
                return self.entry_zone.zone_country.countries_name
            except Exception:
                pass

        return 'USA'

    @property
    def zone(self):
        return self.entry_zone.zone_name

    def generalized_data(self):
        return {
            'id': self.id,
            'pk': self.pk,
            'firstname': self.firstname,
            'lastname': self.lastname,
            'address_type': self.address_type,
            'address1': self.address1,
            'address2': self.address2,
            'city': self.city,
            'state': self.state,
            'zip': self.zip,
            'country': self.country,
        }

    def to_string_format(self):
        return "{}, {}, {}, {} {}, {}".format(self.full_name(), self.address1, self.city, self.state, self.zip, self.country)

