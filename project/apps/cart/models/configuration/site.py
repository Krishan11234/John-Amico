import os
from django.db import models
from solo.models import SingletonModel


def get_config_uploadable_image_name(instance, filename, outside_caller=False):
    file = os.path.basename(filename)
    new_filename = "config_images/%s" % (file,)

    return new_filename


class SiteConfig(SingletonModel):
    site_name = models.CharField(max_length=255, blank=True, null=True)
    site_title = models.CharField(max_length=255, blank=True, null=True)
    site_logo = models.ImageField(blank=True, null=True, max_length=255,
                                  upload_to=get_config_uploadable_image_name, verbose_name='Logo Image')
    # site_logo = models.ImageField(upload_to=get_uploadable_image_name, verbose_name='Image')
    site_meta_title = models.CharField(max_length=255, blank=True, null=True)
    site_meta_keywords = models.TextField(blank=True, null=True)
    site_meta_description = models.TextField(blank=True, null=True)

    def __str__(self):
        return "Site Configuration"

    class Meta:
        verbose_name = "Configuration"
