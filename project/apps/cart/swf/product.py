import math

from django.conf import settings
from django.urls import reverse

from django.db import models
from django.core.validators import MinValueValidator, MaxValueValidator

from ..storage.product_image_google_file_storage import ProductImageGoogleFileStorage

from .manufacturer import Manufacturer
from .distributor import Distributor
from .category import Category
from .shipping_category import ShippingCategory


# Products = Product
class Product(models.Model):
    catalogid = models.AutoField(primary_key=True)
    id = models.CharField(max_length=50, blank=True, null=True, db_index=True)
    name = models.CharField(max_length=255, blank=True, null=True, db_index=True)
    categoriesaaa = models.CharField(max_length=100, blank=True, null=True)
    parent = models.ForeignKey(
        'self',
        on_delete=models.SET_NULL,
        db_column='product_parent',
        db_index=True,
        related_name='children',
        blank=True, null=True
    )
    mfgid = models.CharField(max_length=50, blank=True, null=True)
    manufacturer = models.ForeignKey(
        Manufacturer,
        on_delete=models.SET_NULL,
        db_column='manufacturer',
        blank=True, null=True
    )
    distributor = models.ForeignKey(
        Distributor,
        on_delete=models.SET_NULL,
        db_column='distributor',
        blank=True, null=True
    )
    cost = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True)
    price = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True, db_index=True)
    price2 = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True)
    price3 = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True)
    saleprice = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True)
    onsale = models.BooleanField(blank=True, null=True)
    stock = models.FloatField(blank=True, null=True)
    stock_alert = models.IntegerField(blank=True, null=True)
    display_stock = models.IntegerField(blank=True, null=True)
    weight = models.FloatField(blank=True, null=True)
    minimumorder = models.IntegerField(blank=True, null=True, validators=[MinValueValidator(1)])
    maximumorder = models.IntegerField(blank=True, null=True)
    date_created = models.DateTimeField(blank=True, null=True, db_index=True, auto_now_add=True)
    description = models.TextField(blank=True, null=True)
    extended_description = models.TextField(blank=True, null=True)
    keywords = models.TextField(blank=True, null=True)
    sorting = models.IntegerField(blank=True, null=True)
    thumbnail = models.ImageField(blank=True, null=True, storage=ProductImageGoogleFileStorage())
    image1 = models.ImageField(blank=True, null=True, storage=ProductImageGoogleFileStorage())
    image2 = models.ImageField(blank=True, null=True, storage=ProductImageGoogleFileStorage())
    image3 = models.ImageField(blank=True, null=True, storage=ProductImageGoogleFileStorage())
    image4 = models.ImageField(blank=True, null=True, storage=ProductImageGoogleFileStorage())
    realmedia = models.CharField(max_length=255, blank=True, null=True)
    related = models.CharField(max_length=50, blank=True, null=True)
    shipcost = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True)
    imagecaption1 = models.TextField(blank=True, null=True)
    imagecaption2 = models.TextField(blank=True, null=True)
    imagecaption3 = models.TextField(blank=True, null=True)
    imagecaption4 = models.TextField(blank=True, null=True)
    title = models.CharField(max_length=150, blank=True, null=True)
    metatags = models.TextField(blank=True, null=True)
    displaytext = models.CharField(max_length=50, blank=True, null=True)
    eproduct_password = models.CharField(max_length=15, blank=True, null=True)
    eproduct_random = models.IntegerField(blank=True, null=True)
    eproduct_expire = models.IntegerField(blank=True, null=True)
    eproduct_path = models.TextField(blank=True, null=True)
    eproduct_serial = models.IntegerField(blank=True, null=True)
    eproduct_instructions = models.TextField(blank=True, null=True)
    homespecial = models.IntegerField(blank=True, null=True)
    categoryspecial = models.IntegerField(blank=True, null=True)
    hide = models.BooleanField(blank=True, null=True)
    free_shipping = models.IntegerField(blank=True, null=True)
    nontax = models.IntegerField(blank=True, null=True)
    notforsale = models.IntegerField(blank=True, null=True)
    giftcertificate = models.IntegerField(blank=True, null=True)
    userid = models.CharField(max_length=50, blank=True, null=True)
    last_update = models.DateTimeField(blank=True, null=True, auto_now=True)
    extra_field_1 = models.CharField(max_length=150, blank=True, null=True)
    extra_field_2 = models.CharField(max_length=150, blank=True, null=True)
    extra_field_3 = models.CharField(max_length=150, blank=True, null=True)
    extra_field_4 = models.CharField(max_length=150, blank=True, null=True)
    extra_field_5 = models.CharField(max_length=150, blank=True, null=True)
    extra_field_6 = models.TextField(blank=True, null=True)
    extra_field_7 = models.TextField(blank=True, null=True)
    extra_field_8 = models.TextField(blank=True, null=True)
    extra_field_9 = models.TextField(blank=True, null=True)
    extra_field_10 = models.TextField(blank=True, null=True)
    usecatoptions = models.IntegerField(blank=True, null=True)
    qtyoptions = models.CharField(max_length=250, blank=True, null=True)
    price_1 = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True)
    price_2 = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True)
    price_3 = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True)
    price_4 = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True)
    price_5 = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True)
    price_6 = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True)
    price_7 = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True)
    price_8 = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True)
    price_9 = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True)
    price_10 = models.DecimalField(max_digits=19, decimal_places=4, blank=True, null=True)
    hide_1 = models.IntegerField(blank=True, null=True)
    hide_2 = models.IntegerField(blank=True, null=True)
    hide_3 = models.IntegerField(blank=True, null=True)
    hide_4 = models.IntegerField(blank=True, null=True)
    hide_5 = models.IntegerField(blank=True, null=True)
    hide_6 = models.IntegerField(blank=True, null=True)
    hide_7 = models.IntegerField(blank=True, null=True)
    hide_8 = models.IntegerField(blank=True, null=True)
    hide_9 = models.IntegerField(blank=True, null=True)
    hide_10 = models.IntegerField(blank=True, null=True)
    minorderpkg = models.IntegerField(blank=True, null=True)
    listing_displaytype = models.IntegerField(blank=True, null=True)
    show_out_stock = models.IntegerField(blank=True, null=True)
    pricing_groupopt = models.IntegerField(blank=True, null=True)
    qtydiscount_opt = models.IntegerField(blank=True, null=True)
    loginlevel = models.IntegerField(blank=True, null=True)
    redirectto = models.CharField(max_length=150, blank=True, null=True)
    accessgroup = models.CharField(max_length=250, blank=True, null=True)
    self_ship = models.SmallIntegerField(blank=True, null=True)
    tax_code = models.CharField(max_length=3, blank=True, null=True)
    eproduct_reuseserial = models.FloatField(blank=True, null=True)
    nonsearchable = models.BooleanField(blank=True, null=True)
    instock_message = models.CharField(max_length=150, blank=True, null=True)
    outofstock_message = models.CharField(max_length=150, blank=True, null=True)
    backorder_message = models.CharField(max_length=150, blank=True, null=True)
    height = models.IntegerField(blank=True, null=True)
    width = models.IntegerField(blank=True, null=True)
    depth = models.IntegerField(blank=True, null=True)
    reward_points = models.IntegerField(blank=True, null=True)
    reward_disable = models.BooleanField(blank=True, null=True)
    reward_redeem = models.IntegerField(blank=True, null=True)
    filename = models.CharField(max_length=255, blank=True, null=True)
    rma_maxperiod = models.IntegerField(blank=True, null=True)
    recurring_order = models.IntegerField(blank=True, null=True)
    fractional_qty = models.IntegerField(blank=True, null=True)
    reminders_enabled = models.IntegerField(blank=True, null=True)
    reminders_frequency = models.IntegerField(blank=True, null=True)
    review_average = models.FloatField(blank=True, null=True)
    review_count = models.IntegerField(blank=True, null=True)
    is_bundle = models.BooleanField(blank=True, null=True)
    discount_percent = models.DecimalField(max_digits=6, decimal_places=2, blank=True, null=True)
    custom_points = models.DecimalField(max_digits=6, decimal_places=2, blank=True, null=True)
    disable = models.BooleanField(blank=True, null=True)
    reefpackage = models.IntegerField(blank=True, null=True)
    start_date = models.DateTimeField(blank=True, null=True)
    end_date = models.DateTimeField(blank=True, null=True)
    disable_purchases = models.IntegerField(blank=True, null=True)
    url = models.CharField(max_length=300, blank=True, null=True, db_index=True)
    master = models.ForeignKey(
        'self',
        on_delete=models.SET_NULL,
        db_column='master_pid',
        related_name='slaves',
        blank=True, null=True
    )
    drop_shipper_sku = models.CharField(max_length=45, blank=True, null=True, db_index=True)
    match_first_ten = models.IntegerField(blank=True, null=True)
    is_parent = models.BooleanField(blank=True, null=True)
    hide_on_subcategory = models.BooleanField(blank=True, null=True)
    popup_show = models.IntegerField(blank=True, null=True)
    date_added = models.DateTimeField(blank=True, null=True)
    date_removed = models.DateTimeField(blank=True, null=True)
    lowest_price = models.FloatField(blank=True, null=True)
    onsale_group = models.IntegerField(blank=True, null=True)
    binlocation = models.CharField(max_length=10, blank=True, null=True)
    managed_inv = models.CharField(max_length=3, blank=True, null=True)
    opt1 = models.CharField(max_length=45, blank=True, null=True)
    categories = models.ManyToManyField(Category, through='ProductCategory', through_fields=('product', 'category'))
    shipping_categories = models.ManyToManyField(
        ShippingCategory,
        through='ProductShippingCategory',
        through_fields=('product', 'shipping_category')
    )

    class Meta:
        managed = False
        db_table = 'products'
        ordering = ['-catalogid',]

        index_together = [
            ['stock', 'homespecial', 'disable', 'hide'],
            ['disable', 'stock', 'homespecial', 'hide'],
            ['hide', 'disable', 'name', 'stock', 'catalogid'],
            ['catalogid', 'stock', 'onsale', 'hide', 'disable', 'name']
        ]

    def __str__(self):
        return '{} ({})'.format(self.name, self.id)

    def cloud_thumbnail(self):
        file_name = self.thumbnail.name
        if len(file_name.strip()) < 1:
            return None
        elif 'https' in file_name:
            return file_name
        else:
            return '{}{}'.format(settings.CLOUD_STATIC_URL, file_name.replace('assets/images/', ''))

    def cloud_image1(self):

        file_name = self.image1.name
        if len(file_name.strip()) < 1:
            return None
        elif 'https' in file_name:
            return file_name
        else:
            return '{}{}'.format(settings.CLOUD_STATIC_URL, file_name.replace('assets/images/', ''))

    def cloud_image2(self):

        file_name = self.image2.name
        if len(file_name.strip()) < 1:
            return None
        elif 'https' in file_name:
            return file_name
        else:
            return '{}{}'.format(settings.CLOUD_STATIC_URL, file_name.replace('assets/images/', ''))

    def cloud_image3(self):

        file_name = self.image3.name
        if len(file_name.strip()) < 1:
            return None
        elif 'https' in file_name:
            return file_name
        else:
            return '{}{}'.format(settings.CLOUD_STATIC_URL, file_name.replace('assets/images/', ''))

    def total_rating_reviews(self):
        return self.productreview_set.filter(approved=1).count()

    def average_rating(self):
        if self.productreview_set.filter(approved=1).count():
            temp_dict = (self.productreview_set.filter(approved=1).aggregate(models.Avg('rating')))
            return math.floor(temp_dict['rating__avg'])
        return 0

    def average_rating_range(self):
        average_rating = self.average_rating()
        return list(range(average_rating))

    def remaining_rating_range(self):
        remaining_rating = 5 - self.average_rating()
        return list(range(remaining_rating))

    def key_facts_list(self):
        # SWF legacy issue:
        # We have list of key facts in a single field as: <b>Care Level :</b>Easy<br><b>Temperament :</b>Peaceful
        # We need to have a list to loop through
        # Later we should move key facts to another table

        key_facts_dict_list = []
        key_facts_str = self.extra_field_7

        # Get key facts with html list by splitting the string with <br>
        key_facts_with_html_list = key_facts_str.split('<br>')

        # Loop through key_facts_with_html_list, remove tags, then push into key_facts_without_html_list
        key_facts_without_html_list = []
        for key_fact_with_html in key_facts_with_html_list:
            key_fact_without_html = key_fact_with_html.replace('<b>', '').replace('</b>', '')
            key_facts_without_html_list.append(key_fact_without_html)

        # Loop through key_facts_without_html_list and prepare a final list with key value dicts for all key facts
        for key_fact_without_html in key_facts_without_html_list:
            key_fact_key_value_list = key_fact_without_html.split(':')
            if len(key_fact_key_value_list) == 2:
                key_facts_dict_list.append({
                    'key': key_fact_key_value_list[0].strip(),
                    'value': key_fact_key_value_list[1].strip()
                })
        return key_facts_dict_list

    def is_on_sale(self):
        on_sale = False
        children_count = self.children.count()
        if children_count > 0:
            for child in self.children.all():
                if child.onsale:
                    on_sale = True
                    break
        else:
            if self.onsale:
                on_sale = True

        return on_sale

    def get_starting_price(self):
        starting_price = self.price

        children_count = self.children.count()

        if children_count > 0:
            for child in self.children.all():
                if child.onsale:
                    if child.saleprice < starting_price:
                        starting_price = child.saleprice
                else:
                    if child.price < starting_price:
                        starting_price = child.price
        else:
            if self.onsale:
                starting_price = self.saleprice

        return starting_price

    def is_stock_available(self):
        stock_available = False

        children_count = self.children.count()
        if children_count > 0:
            for child in self.children.all():
                if child.stock > 0:
                    stock_available = True
                    break
        else:
            if self.stock > 0:
                stock_available = True

        return stock_available

    def get_available_reward_points(self):
        reward_points = 0

        children_count = self.children.count()
        if children_count > 0:
            children_prices = []
            for child in self.children.all():
                if child.onsale:
                    children_prices.append(math.floor(child.saleprice))
                else:
                    children_prices.append(math.floor(child.price))
            reward_points = min(children_prices)
        else:
            if self.onsale:
                reward_points = math.floor(self.saleprice)
            else:
                reward_points = math.floor(self.price)

        return reward_points

    def get_absolute_url(self):
        return reverse('cart:products.show', args=(self.url,))

    def approved_reviews(self):
        return self.productreview_set.filter(approved=1)

    def approved_reviews_count(self):
        return self.productreview_set.filter(approved=1).count()


class ProductCategory(models.Model):
    id = models.AutoField(primary_key=True)
    product = models.ForeignKey(
        Product,
        on_delete=models.CASCADE,
        db_column='catalogid',
        db_index=True,
        blank=True, null=True)
    category = models.ForeignKey(
        Category,
        on_delete=models.CASCADE,
        db_column='categoryid',
        blank=True, null=True)
    is_main = models.CharField(db_column='ismain', max_length=50, blank=True, null=True)
    sorting = models.IntegerField(blank=True, null=True, default=0)

    class Meta:
        managed = False
        db_table = 'product_category'
        verbose_name_plural = 'product categories'

        index_together = ['category', 'product']

    def __str__(self):
        return '{} linked with {}'.format(self.product, self.category)


# ProductsShippingCategory = ProductShippingCategory
class ProductShippingCategory(models.Model):
    id = models.BigAutoField(primary_key=True)
    product = models.ForeignKey(
        Product,
        on_delete=models.CASCADE,
        db_column='catalogid',
        db_index=True
    )
    shipping_category = models.ForeignKey(
        ShippingCategory,
        on_delete=models.CASCADE,
        db_column='shipping_category_id',
        db_index=True
    )

    class Meta:
        managed = False
        db_table = 'products_shipping_category'
