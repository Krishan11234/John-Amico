from django.db import models


class HomePageRow(models.Model):
    id = models.AutoField(primary_key=True)
    name = models.CharField(max_length=100)
    css_class = models.CharField(max_length=100, default=None, blank=True, null=True)
    sort_order = models.IntegerField(default=0)
    is_active = models.BooleanField(default=True)

    class Meta:
        ordering = ['sort_order']

    def __str__(self):
        return self.name

    def active_columns(self):
        return self.homepagecolumn_set.filter(is_active=True)
