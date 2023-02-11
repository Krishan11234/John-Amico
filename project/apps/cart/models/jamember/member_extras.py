from django.db import models

from ..jamember import Customers, TblMember, AddressBook
from ...utils import static, helper


class MemberExtra(models.Model):
    id = models.AutoField(primary_key=True)
    member_customer = models.OneToOneField(
        Customers,
        on_delete=models.PROTECT,
        db_index=True,
        blank=True,
        null=True,
        unique=True
    )
    tbl_member = models.OneToOneField(
        TblMember,
        on_delete=models.PROTECT,
        db_index=True,
        blank=True,
        null=True,
        unique=True
    )

    # authnetcim_profile_id = models.CharField(max_length=100, blank=True, null=True)
    magento_id = models.IntegerField(blank=True, null=True)

    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        ordering = ['tbl_member']

    def __str__(self):
        return 'Extra fields # {} : {}'.format(self.id, self.tbl_member.amico_id)

    @property
    def gender(self):
        return self.member_customer.customers_gender if self.member_customer else ''

    def get_addresses(self, address_type=''):
        return self.tbl_member.get_addresses(address_type=address_type)

    # def get_authorizenet_cards(self):
    #     return helper.get_customer_authorizenet_cards(self.authnetcim_profile_id)

    def get_customer(self, for_db_save=False):
        return self.tbl_member.convert_to_django_user_model() if not for_db_save else None

    def get_fullname(self):
        return self.member_customer.get_full_name()

    @property
    def firstname(self):
        return self.member_customer.customers_firstname

    @property
    def lastname(self):
        return self.member_customer.customers_firstname
