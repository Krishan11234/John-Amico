from django.db import models
from django.conf import settings
from django.urls import reverse

from .listing_display_type import ListingDisplayType
from .shipping_category import ShippingCategory
from ..storage.category_image_google_file_storage import CategoryImageGoogleFileStorage

class Category(models.Model):
    id = models.AutoField(primary_key=True)
    category_name = models.CharField(max_length=150, blank=True, null=True, db_index=True)
    category_description = models.TextField(blank=True, null=True)
    category_main = models.IntegerField(default=0)
    parent = models.ForeignKey(
        'self',
        on_delete=models.SET(0),
        db_column='category_parent',
        related_name='children',
        blank=True,
        null=True,
        db_index=True,
    )
    category_header = models.TextField(blank=True, null=True)
    category_footer = models.TextField(blank=True, null=True)
    category_title = models.TextField(blank=True, null=True)
    category_meta = models.TextField(blank=True, null=True)
    sorting = models.IntegerField(blank=True, null=True)
    numtolist = models.IntegerField(blank=True, null=True)
    displaytype = models.IntegerField(blank=True, null=True)
    columnum = models.IntegerField(blank=True, null=True)
    iconimage = models.ImageField(max_length=100, blank=True, null=True, storage=CategoryImageGoogleFileStorage())
    special_numtolist = models.IntegerField(blank=True, null=True)
    special_displaytype = models.IntegerField(blank=True, null=True)
    special_columnum = models.IntegerField(blank=True, null=True)
    category_columnum = models.IntegerField(blank=True, null=True)
    category_displaytype = models.IntegerField(blank=True, null=True)
    related_displaytype = models.IntegerField(blank=True, null=True)
    related_columnum = models.IntegerField(blank=True, null=True)
    listing_display_type = models.ForeignKey(
        ListingDisplayType,
        on_delete=models.SET_NULL,
        db_column='listing_displaytype',
        blank=True,
        null=True
    )
    hide = models.BooleanField(blank=True, null=True)
    category_defaultsorting = models.IntegerField(blank=True, null=True)
    userid = models.CharField(max_length=50, blank=True, null=True)
    last_update = models.DateTimeField(blank=True, null=True, auto_now=True)
    itemicon = models.IntegerField(blank=True, null=True)
    redirectto = models.CharField(max_length=150, blank=True, null=True)
    accessgroup = models.CharField(max_length=250, blank=True, null=True)
    link = models.TextField(blank=True, null=True)
    link_target = models.CharField(max_length=50, blank=True, null=True)
    upsellitems_displaytype = models.IntegerField(blank=True, null=True)
    upsellitems_columnum = models.IntegerField(blank=True, null=True)
    filename = models.CharField(max_length=255, blank=True, null=True)
    isfilter = models.IntegerField(db_column='isFilter', blank=True, null=True)  # Field name made lowercase.
    keywords = models.TextField(blank=True, null=True)
    promo_image = models.CharField(max_length=200, blank=True, null=True)
    sdc_catid = models.IntegerField(blank=True, null=True)  # Seadwelling db
    shipping_category = models.ForeignKey(
        ShippingCategory,
        on_delete=models.SET_NULL,
        db_column='shipping_category_id',
        blank=True,
        null=True
    )
    is_eibi_category = models.BooleanField(default=False)
    is_on_sale_menu_item = models.BooleanField(default=False)

    class Meta:
        managed = False
        db_table = 'category'
        verbose_name_plural = 'categories'
        ordering = ['category_name']

    def __str__(self):
        category_genealogy = []
        current = self
        category_genealogy.insert(0, current.category_name)
        try:
            while current.parent:
                category_genealogy.insert(0, current.parent.category_name)
                current = current.parent
        except Category.DoesNotExist:
            pass

        return '{}'.format(' > '.join(category_genealogy))

    def cloud_icon_image(self):
        file_name = self.iconimage.name
        if len(file_name.strip()) < 1:
            return None
        elif 'https' in file_name:
            return file_name
        else:
            return '{}{}'.format(settings.CLOUD_STATIC_URL, file_name.replace('assets/images/', ''))

    def get_absolute_url(self):
        if self.is_eibi_category:
            return reverse('cart:eibi_products.index', args=(self.link,))

        if self.parent and self.parent.shipping_category:
            if self.parent.shipping_category.internal_name in ['marine_life', 'aquarium_supplies']:
                return reverse('cart:products.index1', args=(self.parent.link, self.link,))

        if self.listing_display_type.internal_name == 'product_listing':
            return reverse('cart:products.index2', args=(self.link,))

        return reverse('cart:categories.index', args=(self.link,))

    def get_visible_children(self):
        return self.children.filter(hide=False)
