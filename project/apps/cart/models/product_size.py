
from django.db import models
from django.contrib.auth.models import Group
from django.utils import timezone

from .product import Product

from ..utils.static import ANONYMOUS_GROUP_ID
from ..utils.helper import get_current_customer_group


OPTION_TYPE_CHOICES = (
    ("select", "Select Field"),
)


class ProductSize(models.Model):
    id = models.AutoField(primary_key=True)
    product = models.ForeignKey(
        Product,
        on_delete=models.CASCADE,
        db_index=True,
    )
    title = models.CharField(max_length=100)  # 10 Oz, 12 Oz
    sku = models.CharField(max_length=20, blank=True, null=True)  # 10, 12
    is_require = models.BooleanField(default=True, blank=True, null=True)
    order = models.IntegerField(blank=True, null=True, default=0)

    magento_id = models.IntegerField(blank=True, null=True, editable=False)

    # type = models.CharField(max_length=20, db_index=True, choices=OPTION_TYPE_CHOICES, default='select')

    # created_at = models.DateTimeField(auto_now_add=True, blank=True)
    # updated_at = models.DateTimeField(auto_now=True, blank=True)

    # product_option_value = models.ManyToOneRel("cart.ProductOptionValue")

    class Meta:
        verbose_name_plural = 'product option'
        index_together = ['product', 'title']

    def __str__(self):
        return '{} has option: {}'.format(self.product.name, self.title)

    def get_value(self):
        customer_group = get_current_customer_group()
        return self.get_customer_group_price(customer_group.id if customer_group else 0)

    def get_customer_group_price(self, group_id):
        if group_id:
            from .product_size_value import ProductSizeValue

            for value in ProductSizeValue.objects.filter(product_size=self).all():
                if group_id == value.customer_group_id:
                    return value

        return self.get_anonymous_value()

    def get_anonymous_value(self):
        from .product_size_value import ProductSizeValue

        value = ProductSizeValue.objects.filter(product_size=self, customer_group_id=ANONYMOUS_GROUP_ID)
        return value.get() if value.exists() else False
