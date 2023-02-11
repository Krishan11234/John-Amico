from django.db import models
from ..utils.static import BOOTSTRAP_COLUMN_SIZES


class HomePageColumn(models.Model):
    id = models.AutoField(primary_key=True)
    row = models.ForeignKey('HomePageRow', on_delete=models.CASCADE)
    name = models.CharField(max_length=100)
    column_size = models.CharField(max_length=10, blank=True, choices=BOOTSTRAP_COLUMN_SIZES, default="6")
    css_class = models.CharField(max_length=100, default=None, blank=True, null=True)
    content = models.TextField(blank=True, null=True)
    sort_order = models.IntegerField(default=0)
    is_active = models.BooleanField(default=True)

    class Meta:
        ordering = ['sort_order']

    def __str__(self):
        return '{} > {}'.format(self.row.name, self.name)
