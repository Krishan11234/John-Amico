import os, hashlib, PIL
from django.conf import settings
from django.db import models
from django.db.models.fields.files import ImageFieldFile

from adminsortable.models import SortableMixin
from model_utils import FieldTracker

from .product import Product
from ..utils import get_image_version as giv, get_image_version_name, get_uploadable_image_name, create_image_versions, \
    delete_image_versions


def get_product_uploadable_image_name(instance, filename, outside_caller=False):
    return get_uploadable_image_name(instance, "product_images", filename, outside_caller)


class ProductImage(SortableMixin):
    id = models.AutoField(primary_key=True)
    product = models.ForeignKey(Product, on_delete=models.CASCADE,)
    is_active = models.BooleanField(null=False, default=True)
    image = models.ImageField(max_length=255, upload_to=get_product_uploadable_image_name, verbose_name='Image')
    original_filename = models.CharField(max_length=255, blank=True, null=True)
    filename = models.CharField(max_length=255, blank=True, null=True)
    large_image_path = models.CharField(max_length=255, blank=True, null=True)
    medium_image_path = models.CharField(max_length=255, blank=True, null=True)
    thumbnail_image_path = models.CharField(max_length=255, blank=True, null=True)
    order = models.PositiveSmallIntegerField(default=0, editable=False, db_index=True)
    magento_id = models.IntegerField(blank=True, null=True, editable=False)
    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    changes_tracker = FieldTracker()

    class Meta:
        ordering = ['order']

    def __str__(self):
        return '{} Image# {}'.format(self.product, self.id)

    def get_absolute_url(self, version='large'):
        versions = {
            'small': self.thumbnail_image_path,
            'medium': self.medium_image_path,
            'large': self.large_image_path,
            'original': self.image
        }
        if version in versions.keys():
            return settings.MEDIA_URL + versions[version]

        return settings.MEDIA_URL + self.image

    def get_medium_url(self):
        return self.get_absolute_url('medium')

    def get_small_url(self):
        return self.get_absolute_url('small')

    def get_original_url(self):
        return self.get_absolute_url('original')

    def get_image_version(self, version):
        return giv(self, version)

    def save(self, force_insert=False, force_update=False, using=None, update_fields=None, external_use=False):

        existing_image_name = None
        db_instance = None

        if self.id:
            db_instance = ProductImage.objects.get(id=self.id)
            existing_image_name = db_instance.image.name

        if not external_use:
            if isinstance(self.image, ImageFieldFile):
                if existing_image_name is not self.image.name:
                    self.original_filename = self.image.name

        super().save(force_insert, force_update, using, update_fields)

        if not external_use:
            if existing_image_name and existing_image_name is not self.image.name:
                if self.id and db_instance:
                    delete_image_versions(self, 'image_updated', db_instance.image)

                create_image_versions(self)

            if not existing_image_name:
                create_image_versions(self)

    def delete(self, *args, **kwargs):
        delete_image_versions(self, 'image_removed')
        super().delete(*args, **kwargs)


# @receiver(post_save, sender=ProductImage)
# def create_image_versions(sender, instance, created, update_fields, **kwargs):

