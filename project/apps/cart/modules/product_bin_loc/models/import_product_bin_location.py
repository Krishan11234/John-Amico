from .product_bin_location import ProductBinLocation


class ImportProductBinLocation(ProductBinLocation):

    class Meta:
        proxy = True

