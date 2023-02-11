from django.db import models
from django.contrib.auth.models import User
from django.utils import timezone
from django.utils.translation import gettext_lazy as _

from .customer_address import CustomerAddress
from ..utils import static, helper


class CustomerExtra(models.Model):
    id = models.AutoField(primary_key=True)
    customer = models.OneToOneField(
        User,
        on_delete=models.PROTECT,
        db_index=True,
        blank=True,
        null=True,
        unique=True
    )

    prefix = models.CharField(_('prefix'), max_length=30, blank=True, null=True)
    middle_name = models.CharField(_('middle name'), max_length=30, blank=True, null=True)
    suffix = models.CharField(_('suffix'), max_length=30, blank=True, null=True)
    gender = models.CharField(max_length=10, blank=True, null=True, choices=static.GENDER_CHOICES)

    date_of_birth = models.DateField(blank=True, null=True)
    referring_amico_id = models.CharField(max_length=50, blank=True, null=True)
    # authnetcim_profile_id = models.CharField(max_length=100, blank=True, null=True)
    magento_id = models.IntegerField(blank=True, null=True)

    default_billing_address = models.ForeignKey(
        CustomerAddress,
        on_delete=models.PROTECT,
        blank=True,
        null=True,
        related_name="customer_default_billing_address"
    )
    default_shipping_address = models.ForeignKey(
        CustomerAddress,
        on_delete=models.PROTECT,
        blank=True,
        null=True,
        related_name="customer_default_shipping_address"
    )

    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        ordering = ['customer']

    def __str__(self):
        return 'Extra fields # {} : {}'.format(self.id, self.customer.email)

    def get_addresses(self, address_type=''):
        from .customer_address import CustomerAddress

        exclude_ids = []
        default_shipping_address = None
        conditions = {'customer': self.customer}

        default_billing_address = self.get_default_billing_address()
        if default_billing_address:
            exclude_ids.append(str(default_billing_address.id))

        if address_type in list(dict(static.ADDRESS_TYPE_CHOICES).keys()):
            if address_type == 'shipping':
                default_shipping_address = self.get_default_shipping_address()
                if default_shipping_address:
                    exclude_ids.append(str(default_shipping_address.id))

        addresses_q = CustomerAddress.objects.filter(**conditions).exclude(id__in=exclude_ids)
        addresses = list(addresses_q.all())

        if len(exclude_ids) > 0:
            if default_billing_address and default_shipping_address:
                if default_billing_address.id == default_shipping_address.id :
                    addresses.insert(0, default_billing_address)
                    return addresses
                else:
                    addresses.append(default_shipping_address)
                    addresses.append(default_billing_address)
                    return addresses

            if default_shipping_address:
                addresses.insert(0, default_shipping_address)

            if default_billing_address:
                addresses.insert(0, default_billing_address)

        return addresses

    # def get_authorizenet_cards(self):
    #     return helper.get_customer_authorizenet_cards(self.authnetcim_profile_id)

    def get_customer(self, for_db_save=True):
        return self.customer

    def get_fullname(self):
        return self.customer.get_full_name()

    @property
    def firstname(self):
        return self.customer.first_name

    @property
    def lastname(self):
        return self.customer.last_name

    def get_default_billing_address(self):
        from .customer_address import CustomerAddress

        if self.default_billing_address and isinstance(self.default_billing_address, CustomerAddress):
            return self.default_billing_address

        conditions = {'customer': self.customer}
        custa_q = CustomerAddress.objects.filter(**conditions)
        if custa_q.exists():
            first = custa_q.first()
            self.default_billing_address = first
            self.save()

            return self.default_billing_address

        return False

    def get_default_shipping_address(self):
        from .customer_address import CustomerAddress

        if self.default_shipping_address and isinstance(self.default_shipping_address, CustomerAddress):
            return self.default_shipping_address

        conditions = {'customer': self.customer}
        custa_q = CustomerAddress.objects.filter(**conditions)
        if custa_q.exists():
            first = custa_q.first()
        else:
            first = self.get_default_billing_address()

        if first:
            self.default_shipping_address = first
            self.save()

        return self.default_shipping_address

    def no_addresses(self):
        if len(self.get_addresses()) < 1:
            return True
        return False

    @property
    def email(self):
        return self.customer.email
