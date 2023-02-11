from django.urls import reverse
from django.conf import settings
from django.db import models
from django.contrib.auth.models import Group

from .category import Category
# from .tax_class import TaxClass
from ..utils.static import PRODUCT_TYPE_CHOICES
from ..utils.helper import get_current_customer_group


class Product(models.Model):
    id = models.AutoField(primary_key=True)
    sku = models.CharField(max_length=255, db_index=True, unique=True, null=True)
    type = models.CharField(max_length=255, db_index=True, choices=PRODUCT_TYPE_CHOICES, default='simple')
    is_active = models.BooleanField(default=True)

    name = models.CharField(max_length=255, db_index=True)
    description = models.TextField(blank=True, null=True)
    short_description = models.TextField(blank=True, null=True)
    direction = models.TextField(blank=True, null=True)
    ingredient = models.TextField(blank=True, null=True)
    caution = models.TextField(blank=True, null=True)
    warning = models.TextField(blank=True, null=True)

    categories = models.ManyToManyField(Category, through='CategoryProduct', through_fields=('product', 'category'))
    related_products = models.ManyToManyField('self', through='ProductRelated', through_fields=('product', 'related'))

    # tax_class = models.ForeignKey(
    #     TaxClass,
    #     on_delete=models.CASCADE,
    #     blank=True, null=True
    # )

    featured = models.BooleanField(blank=True, null=True, default=False)
    member_only_product = models.BooleanField(blank=True, null=True, default=False, help_text="Ambassador Pro "
                                                                                              "Member Only Product")
    customer_only_product = models.BooleanField(blank=True, null=True, default=False)
    exclude_from_sitemap = models.BooleanField(blank=True, null=True, default=False)
    disable_purchase = models.BooleanField(blank=True, null=True, default=False)

    # bin_location = models.CharField(max_length=100, blank=True, null=True)

    meta_title = models.CharField(max_length=200, blank=True, null=True, db_index=True)
    meta_keyword = models.TextField(blank=True, null=True)
    meta_description = models.TextField(blank=True, null=True)
    url_key = models.CharField(max_length=200, blank=True, null=True, db_index=True)
    url_path = models.CharField(max_length=200, blank=True, null=True, db_index=True)

    magento_id = models.IntegerField(blank=True, null=True, editable=False)
    oldsite_product_id = models.IntegerField(blank=True, null=True, editable=False)

    price = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True, db_index=True)
    customer_group_price = models.ManyToManyField(Group, through='ProductCustomerGroupPrice', through_fields=('product', 'customer_group'))

    weight = models.FloatField(blank=True, null=True)
    height = models.FloatField(blank=True, null=True)
    width = models.FloatField(blank=True, null=True)
    depth = models.FloatField(blank=True, null=True)

    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        ordering = ['name',]

        index_together = [
            ['is_active'],
            ['name'],
        ]

    def __str__(self):
        return '{}: {} - ({})'.format(self.id, self.name, self.sku)

    def get_absolute_url(self):
        return reverse('cart:cat_prod_ref', args=(self.url_path,))

    def get_price(self):
        group_price = False
        customer_group = get_current_customer_group()

        if customer_group:
            group_price = self.get_customer_group_price(customer_group.id)

        return group_price if group_price else self.price

    def get_customer_group_price(self, group_id):
        if group_id:
            from .product_customer_group_price import ProductCustomerGroupPrice
            customer_price = ProductCustomerGroupPrice.objects.filter(customer_group_id=group_id, product=self)
            if customer_price.exists():
                customer_price = customer_price.first()
                if getattr(customer_price, 'price'):
                    return customer_price.price

        return False

    def get_sizes(self):
        from .product_size import ProductSize
        sizes = []
        for size in ProductSize.objects.filter(product=self).order_by('order', 'sku').all():
            if size.get_value():
                sizes.append(size)

        return sizes

    def get_images(self):
        from .product_image import ProductImage
        return ProductImage.objects.filter(product=self, is_active=True).order_by('order').all()

    def get_related_products(self):
        prods = self.related_products.filter(is_active=True).order_by('name').all()
        return prods

    def get_default_image(self):
        from .product_image import ProductImage
        return ProductImage.objects.filter(product=self, is_active=True).order_by('order').first()

    def get_qty_info(self):
        from .product_stock import ProductStock
        return ProductStock.objects.filter(product=self).get()

    def tab_data_obj(self):
        obj = {}

        fields = ['description', 'direction', 'ingredient', 'caution', 'warning']
        # fields.insert(1, 'short_description')
        custom_titles = {
            'description': 'Detail',
            'short_description': 'Overview',
        }
        for data in fields:
            desc = getattr(self, data)
            if desc:
                if data not in obj:
                    obj[data] = {
                        'key': data,
                        'title': data.title() if data not in custom_titles.keys() else custom_titles[data],
                        'desc': desc
                    }
        return obj

    def delete(self, *args, **kwargs):
        from .product_image import ProductImage
        # image_path = os.path.join(settings.MEDIA_ROOT, "product_images/%s" % self.id)
        # if os.path.exists(image_path):
        #     os.rmdir(image_path)
        ProductImage.objects.filter(product=self).all().delete()

        super().delete(*args, **kwargs)

    # def total_rating_reviews(self):
    #     return self.productreview_set.filter(approved=1).count()
    #
    # def average_rating(self):
    #     if self.productreview_set.filter(approved=1).count():
    #         temp_dict = (self.productreview_set.filter(approved=1).aggregate(models.Avg('rating')))
    #         return math.floor(temp_dict['rating__avg'])
    #     return 0
    #
    # def average_rating_range(self):
    #     average_rating = self.average_rating()
    #     return list(range(average_rating))
    #
    # def remaining_rating_range(self):
    #     remaining_rating = 5 - self.average_rating()
    #     return list(range(remaining_rating))
    #
    # def approved_reviews(self):
    #     return self.productreview_set.filter(approved=1)
    #
    # def approved_reviews_count(self):
    #     return self.productreview_set.filter(approved=1).count()

