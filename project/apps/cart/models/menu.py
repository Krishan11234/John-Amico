from django.db import models
from django.contrib.auth.models import Group


class MenuType(models.Model):
    id = models.AutoField(primary_key=True)
    name = models.CharField(max_length=255, default='')
    machine_name = models.CharField(max_length=255, default='', unique=True)
    is_active = models.BooleanField(default=True)
    autoload = models.BooleanField(default=False, help_text="Load this menu object everytime a frontend page loads")
    categories = models.ManyToManyField('Category', through='MenuCategory', through_fields=('menu_type', 'category',))

    def __str__(self):
        return '{}'.format(self.name)


class MenuCategory(models.Model):
    id = models.AutoField(primary_key=True)
    menu_type = models.ForeignKey(
        'MenuType',
        on_delete=models.CASCADE,
    )
    is_active = models.BooleanField(default=True)
    category = models.ForeignKey(
        'Category',
        on_delete=models.CASCADE,
    )
    # menu_access_group = models.ManyToManyField(Group, through='MenuCategoryUserGroup', through_fields=('menu_category',
    #                                                                                                    'user_group'))
    menu_access_group = models.ForeignKey(Group, on_delete=models.CASCADE, null=True, blank=True )
    label = models.CharField('different label', max_length=255, blank=True, null=True)
    css_class = models.CharField(max_length=255, blank=True, null=True)
    order = models.IntegerField(default=0)

    class Meta:
        verbose_name_plural = 'menu types'

    def __str__(self):
        return '{} is linked with {}'.format(self.category, self.menu_type)


# class MenuCategoryUserGroup(models.Model):
#     id = models.AutoField(primary_key=True)
#     menu_category = models.ForeignKey(
#         MenuCategory,
#         on_delete=models.CASCADE,
#         db_index=True,
#     )
#     user_group = models.ForeignKey(
#         Group,
#         on_delete=models.CASCADE,
#     )
