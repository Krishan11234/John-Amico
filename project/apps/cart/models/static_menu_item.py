from django.db import models
from django.urls import reverse
from django.contrib.auth.models import Group
from django.contrib.sites.shortcuts import get_current_site

from ..utils import helper


class StaticMenuItem(models.Model):
    id = models.AutoField(primary_key=True)
    is_active = models.BooleanField(default=True)
    label = models.CharField(max_length=255)
    permalink = models.CharField(max_length=255)
    css_class = models.CharField(max_length=255, blank=True, null=True)
    order = models.IntegerField(default=99)
    menu_type = models.ForeignKey(
        'MenuType',
        on_delete=models.CASCADE,
        null=True
    )
    # menu_access_group = models.ManyToManyField(Group, through='StaticMenuUserGroup', through_fields=('static_menu',
    #                                                                                                 'user_group'))
    menu_access_group = models.ForeignKey(
        Group,
        on_delete=models.CASCADE,
        null=True, blank=True
    )

    def __str__(self):
        return '{}'.format(self.label)

    def get_absolute_url(self):
        return helper.get_base_url() + '/' + self.permalink
        # return helper.get_request().build_absolute_uri(self.permalink)
        # return reverse(self.permalink)
        # return reverse('cart:home')


# class StaticMenuUserGroup(models.Model):
#     id = models.AutoField(primary_key=True)
#     static_menu = models.ForeignKey(
#         StaticMenuItem,
#         on_delete=models.CASCADE,
#         db_index=True,
#     )
#     user_group = models.ForeignKey(
#         Group,
#         on_delete=models.CASCADE,
#     )
