import os
from django.db import models
from django.core.validators import MinValueValidator, MaxValueValidator
from django.core.exceptions import ValidationError
from .site import SiteConfig, get_config_uploadable_image_name
from solo.models import SingletonModel


class StoreConfig(SingletonModel):
    # site_config = models.ForeignKey(SiteConfig, blank=True, null=True, on_delete=models.CASCADE)

    default_product_image = models.ImageField(blank=True, null=True, max_length=255,
                          upload_to=get_config_uploadable_image_name, verbose_name='Default Media Image', default=None)

    # stock_alert = models.IntegerField(blank=True, null=True)
    display_product_stock = models.BooleanField(blank=True, null=False, default=False)
    display_product_availability = models.BooleanField(blank=True, null=False, default=False)
    minimum_per_product_order_qty = models.DecimalField(max_digits=19, decimal_places=1, blank=True, null=False,
                                                        default=1, validators=[MinValueValidator(1)])
    maximum_per_product_order_qty = models.DecimalField(max_digits=19, decimal_places=1, blank=True, null=False, default=10)
    maximum_cart_qty = models.DecimalField(max_digits=19, decimal_places=1, blank=True, null=False, default=200)

    order_increment_prefix = models.IntegerField(blank=True, null=False, default=1)
    order_increment_pad_length = models.IntegerField(blank=True, null=False, default=8)
    order_increment_pad_char = models.CharField(max_length=2, blank=True, null=False, default=0)

    order_increment_last_id = models.CharField(max_length=20, blank=True, null=False, default=000000)
    invoice_increment_last_id = models.CharField(max_length=20, blank=True, null=False, default=000000)
    shipment_increment_last_id = models.CharField(max_length=20, blank=True, null=False, default=000000)
    credit_memo_increment_last_id = models.CharField(max_length=20, blank=True, null=False, default=000000)
    # reminders_enabled = models.BooleanField(blank=True, null=False, default=False)
    # reminders_frequency_in_hours = models.FloatField(blank=True, null=True)

    def clean(self):
        if self.minimum_per_product_order_qty > self.maximum_per_product_order_qty:
            raise ValidationError("'Minimum per product order qty' should be less than 'Maximum per product order qty'")
        if self.maximum_cart_qty < 2:
            raise ValidationError("'Maximum cart qty' should be more than 1")

    def get_max_per_product_order_qty(self):
        return 0.0 if self.maximum_per_product_order_qty is None or self.maximum_per_product_order_qty < 0 \
            else self.maximum_per_product_order_qty

    def get_min_per_product_order_qty(self):
        return 1.0 if self.minimum_per_product_order_qty is None or self.minimum_per_product_order_qty < 0 \
            else self.minimum_per_product_order_qty

    def get_max_cart_qty(self):
        return 2.0 if self.maximum_cart_qty is None or self.maximum_cart_qty < 1 else self.maximum_cart_qty
