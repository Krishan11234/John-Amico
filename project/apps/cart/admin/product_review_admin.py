from django.contrib import admin

from ..models import ProductReview


class ProductReviewAdmin(admin.ModelAdmin):
    readonly_fields = ('rating_base', )

    # def delete_queryset(self, request, queryset):
    #     for obj in queryset:
    #         obj.delete()


admin.site.register(ProductReview, ProductReviewAdmin)
