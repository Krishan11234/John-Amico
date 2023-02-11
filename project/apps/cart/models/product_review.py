
from django.db import models
from django.db.models import Sum, Count, Q
from django.contrib.auth.models import User


def create_or_update_rating_summary(rating_instance=None, rating_remove=False):
    from ..models import ReviewSummary

    ratings_total_dict = {}
    rating_summary_base = 5

    filter_conditions = Q(is_approved=True)

    if rating_instance:
        filter_conditions &= Q(product=rating_instance.product)
        if rating_instance.id and rating_instance.id > 0 and rating_instance.is_approved:
            filter_conditions &= ~Q(id=rating_instance.id)     # Exclude the existing instance ID, if it exists, in the grouping

        values = ['rating_base', 'product']
    else:
        values = ['rating_base', 'product']

    annotated_ratings = ProductReview.objects.values(*values) \
        .annotate(rating_sum=Sum('rating'), rating_count=Count('rating')) \
        .filter(filter_conditions) \
        .order_by('rating_base')

    for rating in annotated_ratings:
        if rating['product'] not in ratings_total_dict:
            ratings_total_dict[rating['product']] = {}

        ratings_total_dict[rating['product']][float(rating['rating_base'])] = {
            'total': float(rating['rating_sum']),
            'count': rating['rating_count']
        }

    if rating_instance:
        if rating_instance.id and rating_instance.id > 0 and rating_instance.is_approved:
            if rating_instance.product.id not in ratings_total_dict:
                ratings_total_dict[rating_instance.product.id] = {}

            if not rating_remove:
                if float(rating_instance.rating_base) in ratings_total_dict[rating_instance.product.id]:
                    ratings_total_dict[rating_instance.product.id][float(rating_instance.rating_base)]['total'] += \
                        float(rating_instance.rating)
                    ratings_total_dict[rating_instance.product.id][float(rating_instance.rating_base)]['count'] += 1
                else:
                    ratings_total_dict[rating_instance.product.id][float(rating_instance.rating_base)] = {
                        'total': float(rating_instance.rating),
                        'count': 1
                    }

    for product_id in ratings_total_dict:
        ratings_total = 0
        rating_avg = 0
        rating_count = 0

        for rating_base in ratings_total_dict[product_id]:
            rtc = ratings_total_dict[product_id][rating_base]
            ratings_total += ((rtc['total'] / rtc['count']) / rating_base) * rating_summary_base
            rating_count += rtc['count']

        all_count = len(ratings_total_dict[product_id])

        if ratings_total > 0 and all_count > 0:
            rating_avg = round(ratings_total / all_count, 2)

        obj, created = ReviewSummary.objects.get_or_create(
            product=rating_instance.product,
        )
        if obj:
            obj.reviews_count = rating_count
            obj.rating_summary = rating_avg
            obj.save()


class ProductReview(models.Model):
    id = models.AutoField(primary_key=True)
    product = models.ForeignKey(
        'Product',
        on_delete=models.CASCADE,
        db_index=True,
        related_name='review_product'
    )
    title = models.CharField(max_length=255, blank=True, null=True)
    detail = models.TextField(blank=True, null=True)
    is_approved = models.BooleanField(default=True)

    customer_id = models.ForeignKey(
        User,
        on_delete=models.CASCADE,
        db_index=True,
        blank=True,
        null=True
    )

    is_professional = models.BooleanField(blank=True, null=True)
    professional_id = models.CharField(max_length=255, blank=True, null=True)

    reviewer_name = models.CharField(max_length=100, blank=True, null=True)
    reviewer_email = models.EmailField(max_length=255, blank=True, null=True)

    rating = models.DecimalField(max_digits=3, decimal_places=2, blank=True, null=True, db_index=True)
    rating_base = models.IntegerField(blank=True, null=True, default=5)   # Rating Based on 5/10

    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        verbose_name_plural = 'product reviews'

        ordering = ['-created_at']

    def __str__(self):
        return 'Rating #{} for Product: {}'.format(self.id, self.product.name)

    def save(self, force_insert=False, force_update=False, using=None, update_fields=None, external_use=False):
        create_or_update_rating_summary(self)

        super().save(force_insert, force_update, using, update_fields)

    def delete(self, using=None, keep_parents=False):
        create_or_update_rating_summary(self, rating_remove=True)

        super().delete(using=None, keep_parents=False)


