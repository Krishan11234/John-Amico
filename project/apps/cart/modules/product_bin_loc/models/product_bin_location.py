from django.db import models


class ProductBinLocation(models.Model):
    id = models.AutoField(primary_key=True)
    product = models.OneToOneField(
        'Product',
        on_delete=models.CASCADE,
        db_index=True
    )
    bin_location = models.CharField(max_length=100, blank=False, null=True)

    class Meta:
        ordering = ['product__sku', ]

    def __str__(self):
        return "{} - BIN Location: {}".format(self.product.sku, self.bin_location)

