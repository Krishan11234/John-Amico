from django.db import models
from django.conf import settings
from django.urls import reverse

from django.contrib.auth.models import Group


class Category(models.Model):
    id = models.AutoField(primary_key=True)
    name = models.CharField(max_length=150, db_index=True)
    description = models.TextField(blank=True, null=True)
    is_active = models.BooleanField(blank=True, null=True, default=True)
    parent = models.ForeignKey(
        'self',
        related_name='children',
        blank=True,
        null=True,
        db_index=True,
        on_delete=models.CASCADE
    )
    requires_customer_group_authentication = models.ForeignKey(Group, on_delete=models.CASCADE, null=True, blank=True)
    products = models.ManyToManyField("Product", through='CategoryProduct', through_fields=('category', 'product'))
    meta_title = models.CharField(max_length=255, blank=True, null=True)
    meta_keywords = models.TextField(blank=True, null=True)
    meta_description = models.TextField(blank=True, null=True)
    url_key = models.CharField(max_length=200, blank=True, null=True)
    url_path = models.CharField(max_length=200, blank=True, null=True)
    order = models.IntegerField(blank=True, null=True)
    magento_id = models.IntegerField(blank=True, null=True, unique=True)

    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        verbose_name_plural = 'categories'
        ordering = ['name']
        unique_together = ('url_path',)

    def __str__(self):
        return '{}'.format(' > '.join(self.get_genealogy()))

    def get_genealogy(self, list_of_ids=False):
        category_genealogy = []
        current = self
        if list_of_ids:
            category_genealogy.insert(0, current.id)
        else:
            category_genealogy.insert(0, current.name)

        while current.parent:
            if current.parent and isinstance(current.parent, Category):
                if current.parent.id not in [1, 2]:  # Exclude "Root Category" and "Default Category"
                    if list_of_ids:
                        category_genealogy.insert(0, current.parent.id)
                    else:
                        category_genealogy.insert(0, current.parent.name)
                current = current.parent

        return category_genealogy

    def get_absolute_url(self):
        return reverse('cart:cat_prod_ref', args=(self.url_path,))

    def get_visible_children(self):
        return self.children.filter(is_active=True).all()

    def get_visible_children_count(self):
        return self.children.filter(is_active=True).all().count
