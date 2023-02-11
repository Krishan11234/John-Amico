import re
from django.db import models


class Customers(models.Model):
    customers_id = models.AutoField(primary_key=True)
    customers_gender = models.CharField(max_length=1)
    customers_firstname = models.CharField(max_length=32, blank=True, null=True)
    customers_lastname = models.CharField(max_length=32, blank=True, null=True)
    customers_dob = models.DateField()
    customers_email_address = models.CharField(max_length=200)
    customers_default_address_id = models.IntegerField()
    customers_telephone = models.CharField(max_length=32)
    mobile_phone = models.CharField(max_length=32)
    operator_id = models.CharField(max_length=11, blank=True, null=True)
    customers_telephone1 = models.CharField(max_length=32)
    customers_telephone2 = models.CharField(max_length=32)
    customers_fax = models.CharField(max_length=32, blank=True, null=True)
    customers_password = models.CharField(max_length=40)
    customers_newsletter = models.CharField(max_length=1, blank=True, null=True)
    int_price_level = models.IntegerField(blank=True, null=True)
    exported = models.CharField(max_length=1)
    ssn = models.CharField(max_length=15, blank=True, null=True)
    license_number = models.CharField(max_length=50, blank=True, null=True)
    type = models.CharField(max_length=25, blank=True, null=True)
    cc_type = models.CharField(max_length=255)
    cc_number = models.CharField(max_length=255)
    cc_expiry_date = models.CharField(max_length=255)
    cc_cvv = models.CharField(max_length=255)
    tickets = models.CharField(max_length=50)
    tickets_dinner = models.CharField(max_length=50)
    guests = models.TextField()
    chick_tickets = models.IntegerField()
    chick_dinner_tickets = models.IntegerField()
    chick_dinner_tickets2 = models.IntegerField()
    chick_guests1 = models.TextField()
    chick_guests2 = models.TextField()
    chick_guests3 = models.TextField()
    change_ec = models.CharField(max_length=1)

    class Meta:
        managed = False
        db_table = 'customers'

    def get_full_name(self):
        full_name = '%s %s' % (re.sub(r'\W+', '', self.customers_firstname), re.sub(r'\W+', '', self.customers_lastname))
        return full_name.strip()
