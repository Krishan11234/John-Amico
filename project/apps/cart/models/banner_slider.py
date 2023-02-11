from django.db import models
from django.contrib.auth.models import Group
from django.db.models.fields.files import ImageFieldFile
from django.conf import settings
from django.utils.html import mark_safe
from ..utils import product as image_handler, static, helper

from adminsortable.models import SortableMixin
from model_utils import FieldTracker


def get_banner_uploadable_image_name(instance, filename, outside_caller=False):
    return image_handler.get_uploadable_image_name(instance, "banner_images", filename, outside_caller)


class BannerSlider(SortableMixin):
    id = models.AutoField(primary_key=True)
    title = models.CharField(max_length=100, verbose_name="Silder Title", blank=False, null=False)
    image = models.ImageField(max_length=255, upload_to=get_banner_uploadable_image_name, verbose_name='Image')
    is_active = models.BooleanField(default=True, choices=static.YESNO_BOOL_CHOICES)
    url = models.URLField(verbose_name="Slider Url", help_text="Enter URL like, http://www.domainname.com. If you provide "
                                                      "above URL then do not insert any URL in Slide Description."
                          , blank=True, null=True)
    description = models.TextField(verbose_name="Slider Description", blank=True, null=True)

    original_filename = models.CharField(max_length=255, blank=True, null=True)
    filename = models.CharField(max_length=255, blank=True, null=True)
    large_image_path = models.CharField(max_length=255, blank=True, null=True)
    medium_image_path = models.CharField(max_length=255, blank=True, null=True)
    thumbnail_image_path = models.CharField(max_length=255, blank=True, null=True)

    text_align = models.CharField(max_length=10, choices=(('left', 'Left'), ('right', 'Right'), ('center', 'Center')),
                                  default='left', blank=True)
    css_class = models.CharField(max_length=100, default=None, blank=True, null=True)
    text_color = models.CharField(max_length=10, default='#FFFFFF', blank=True, null=True)
    sort_order = models.IntegerField(default=1, blank=True,)

    all_customer_groups = models.BooleanField(blank=True, null=False, default=True)

    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    changes_tracker = FieldTracker()

    class Meta:
        ordering = ['sort_order']

    def __str__(self):
        return str(self.title)

    def get_absolute_image_url(self, version='large'):
        versions = {
            'small': self.thumbnail_image_path,
            'medium': self.medium_image_path,
            'large': self.large_image_path,
            'original': self.image
        }
        if version in versions.keys():
            return settings.MEDIA_URL + versions[version]

        return settings.MEDIA_URL + self.image

    def get_large_image_url(self):
        return self.get_absolute_image_url()

    def get_medium_image_url(self):
        return self.get_absolute_image_url('medium')

    def get_small_image_url(self):
        return self.get_absolute_image_url('small')

    def get_original_image_url(self):
        return self.get_absolute_image_url('original')

    def get_image_version(self, version):
        return image_handler.get_image_version(self, version)

    @property
    def image_display(self):
        return mark_safe('<img src="%s" width="100" height="auto" />' % (self.get_small_url()))

    def save(self, force_insert=False, force_update=False, using=None, update_fields=None, external_use=False):
        import os

        existing_image_name = None
        db_instance = None

        # self.__class__.__name__

        if self.id:
            db_instance = BannerSlider.objects.get(id=self.id)
            existing_image_name = db_instance.image.name

        if not external_use:
            if isinstance(self.image, ImageFieldFile):
                if existing_image_name != self.image.name:
                    self.original_filename = self.image.name

        super().save(force_insert, force_update, using, update_fields)

        if not external_use:
            if existing_image_name and existing_image_name != self.image.name:
                if self.id and db_instance:
                    image_handler.delete_image_versions(self, 'image_updated', db_instance.image)

                image_handler.create_image_versions(self)

            image_handler.create_image_versions(self, True)

            super().save(force_insert, force_update, using, update_fields)

    def delete(self, *args, **kwargs):
        image_handler.delete_image_versions(self, 'image_removed')
        super().delete(*args, **kwargs)


class BannerSliderCustomerGroup(models.Model):
    id = models.AutoField(primary_key=True)
    banner = models.ForeignKey('BannerSlider', on_delete=models.CASCADE)
    customer_group = models.ForeignKey(Group, on_delete=models.CASCADE,)


class BannerSliderCategory(models.Model):
    id = models.AutoField(primary_key=True)
    title = models.CharField(max_length=100, verbose_name="Silder Title", blank=False, null=False)

    status = models.BooleanField(default=1, choices=static.ENABLE_DISABLE_CHOICES)
    display_all_slide_title = models.BooleanField(default=True, choices=static.YESNO_CHOICES,
                                                  help_text="Select yes/no to display slide title in particular slider")
    display_all_slide_description = models.BooleanField(default=True, choices=static.YESNO_CHOICES,
                                                        help_text="Select yes/no to display slide description in "
                                                                  "particular slider")
    display_navigation = models.BooleanField(default=True, choices=static.YESNO_CHOICES,
                                             help_text="Enable slide navigation")
    display_pagination = models.BooleanField(default=False, choices=static.YESNO_CHOICES,
                                             help_text="Enable slide pagination")

    animation_in = models.CharField(max_length=50, default="fadeIn", choices=static.ANIMATION__IN_MODES)
    animation_out = models.CharField(max_length=50, default="fadeOut", choices=static.ANIMATION__OUT_MODES)

    navigation_bg_color = models.CharField(max_length=10, default='#FFFFFF', blank=True, null=True, help_text="Select Color")
    navigation_bg_hover_color = models.CharField(max_length=10, default='#FFFFFF', blank=True, null=True, help_text="Select Color")
    navigation_arrow_color = models.CharField(max_length=10, default='#000000', blank=True, null=True, help_text="Select Color")
    navigation_arrow_hover_color = models.CharField(max_length=10, default='#000000', blank=True, null=True, help_text="Select Color")

    auto_play = models.IntegerField(default=6000, help_text="Enter time in milliseconds, Enter 0 for false")

    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    def __str__(self):
        return str(self.title)

    def get_slides(self, for_current_user=True):
        slides = []
        conditions = {
            'banner_category': self,
            'banner__is_active': True,
            'banner__all_customer_groups': False,
        }
        conditions_cus = {}
        conditions_cus.update(conditions)

        if for_current_user:
            current_group = helper.get_current_customer_group()
            if current_group and isinstance(current_group, models.Model):
                conditions_cus['banner__bannerslidercustomergroup__customer_group'] = current_group

        slides_customer_q = BannerSliderToBannerSliderCategory.objects.filter(**conditions_cus)

        conditions['banner__all_customer_groups'] = True
        slides_non_customer_q = BannerSliderToBannerSliderCategory.objects.filter(**conditions)

        if slides_customer_q.exists():
            for slide in slides_customer_q.all():
                slides.append(slide.banner)

        if slides_non_customer_q.exists():
            for slide in slides_non_customer_q.all():
                slides.append(slide.banner)

        if slides:
            slides.sort(key=lambda e: e.sort_order)

        return slides


class BannerSliderToBannerSliderCategory(models.Model):
    id = models.AutoField(primary_key=True)
    banner = models.ForeignKey('BannerSlider', on_delete=models.CASCADE)
    banner_category = models.ForeignKey('BannerSliderCategory', on_delete=models.CASCADE,)
