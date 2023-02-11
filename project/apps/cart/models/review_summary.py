
from django.db import models


class ReviewSummary(models.Model):
    id = models.AutoField(primary_key=True)
    product = models.OneToOneField(
        'Product',
        on_delete=models.CASCADE,
        db_index=True,
        related_name='product_review'
    )
    reviews_count = models.IntegerField(default=0)
    rating_summary = models.DecimalField(max_digits=3, decimal_places=2, blank=True, null=True, db_index=True)
    rating_summary_base = models.IntegerField(default=5)

    class Meta:
        verbose_name_plural = 'product reviews summary'
        ordering = ['-product']

    def __str__(self):
        return 'Rating Summary #{} for Product: {}'.format(self.rating_summary, self.product.name)
