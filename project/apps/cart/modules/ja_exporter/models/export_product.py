from ....models import Product


class ExportProduct(Product):

    class Meta:
        proxy = True
