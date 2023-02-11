from django.db import models
from ...utils import static

from .customer import Customers


class TblMember(models.Model):
    int_member_id = models.AutoField(primary_key=True)
    amico_id = models.CharField(max_length=255)
    int_parent_id = models.IntegerField(blank=True, null=True)
    int_designation_id = models.IntegerField(blank=True, null=True)
    # int_customer_id = models.IntegerField(blank=True, null=True)
    int_customer = models.OneToOneField(Customers, on_delete=models.CASCADE, db_column='int_customer_id')
    str_title = models.CharField(max_length=10, blank=True, null=True)
    tme_best_time = models.TimeField(blank=True, null=True)
    dat_last_visit = models.DateField(blank=True, null=True)
    bit_active = models.IntegerField(blank=True, null=True)
    int_downline_price_level = models.IntegerField(blank=True, null=True)
    int_price_level = models.IntegerField()
    mtype = models.CharField(max_length=1, blank=True, null=True)
    ec_id = models.CharField(max_length=10)
    new_ec_id = models.CharField(max_length=10)
    growth = models.CharField(max_length=100)
    contest = models.CharField(max_length=100)
    ppp = models.CharField(max_length=10)
    lod = models.DateField()
    as_field = models.FloatField(db_column='as')  # Field renamed because it was a Python reserved word.
    as_c = models.IntegerField(blank=True, null=True)
    mtd = models.FloatField()
    ytd = models.FloatField()
    ytd2007 = models.FloatField()
    ytd2008 = models.FloatField()
    ytd2009 = models.FloatField()
    ytd2010 = models.FloatField()
    ytd2011 = models.FloatField()
    ytd2012 = models.FloatField()
    ytd2013 = models.FloatField()
    ytd2014 = models.FloatField(blank=True, null=True)
    ytd2015 = models.FloatField()
    ytd2016 = models.FloatField()
    ytd2017 = models.FloatField()
    ytd2018 = models.FloatField()
    ytd2019 = models.FloatField()
    reg_date = models.DateTimeField()
    miles = models.FloatField()
    chapter_id = models.CharField(max_length=250)
    sit_tly = models.FloatField()
    sit_tty = models.FloatField()
    sit_short = models.FloatField()
    is_salon = models.CharField(max_length=3)
    nickname = models.CharField(max_length=255)
    bit_no_purchase_required = models.IntegerField()
    bit_ja_mobileapp_active = models.IntegerField()
    ja_mobileapp_user_id = models.IntegerField()
    bit_custom_comission = models.IntegerField(blank=True, null=True)
    expire_custom_comission = models.DateField(blank=True, null=True)
    order_history_json = models.TextField(blank=True, null=True)
    is_deleted = models.IntegerField()
    # customer = models.OneToOneField(Customers, on_delete=models.CASCADE)

    class Meta:
        managed = False
        db_table = 'tbl_member'

    def get_addresses(self, address_type=''):
        from .address_book import AddressBook

        address_types_values = list(dict(AddressBook.get_address_types()).values())
        conditions = {'customers_id': self.int_customer.customers_id}
        if address_type in address_types_values:
            conditions['address_book_id'] = address_types_values.index(address_type)+1
        return AddressBook.objects.filter(**conditions).all()

    def convert_to_django_user_model(self):
        import re
        from ...models import UserExtended

        user = UserExtended()
        user.id = self.int_member_id
        user.email = self.int_customer.customers_email_address
        user.first_name = re.sub(r'\W+', '', self.int_customer.customers_firstname)
        user.last_name = re.sub(r'\W+', '', self.int_customer.customers_lastname)
        user.password = self.int_customer.customers_password
        user.is_professional = True
        user.tbl_member_id = self.int_member_id
        user.amico_id = self.amico_id
        user.customer_id = self.int_customer
        user.is_staff = False
        user.USERNAME_FIELD = 'amico_id'

        return user

    def get_full_name(self):
        return self.int_customer.get_full_name()

    def no_addresses(self):
        if len(self.get_addresses()) < 1:
            return True
        return False

    def get_member_extra(self):
        return self.memberextra

    @property
    def email(self):
        return self.int_customer.customers_email_address
