from django.contrib import admin

# from django_summernote.admin import SummernoteModelAdmin, SummernoteInlineModelAdmin

from adminsortable.admin import NonSortableParentAdmin, SortableStackedInline
from ..models import Product, CategoryProduct, ProductCustomerGroupPrice, ProductSize, ProductSizeValue, ProductImage, \
    ProductRelated, ReviewSummary, ProductStock


class ProductCategoryInline(admin.TabularInline):
    fields = ('category', 'order',)
    model = CategoryProduct
    extra = 1
    autocomplete_fields = ('category', )


class ProductCustomerGroupPricingInline(admin.TabularInline):
    fields = ('customer_group', 'price',)
    model = ProductCustomerGroupPrice
    extra = 1


class ProductStockInline(admin.TabularInline):
    fields = ('quantity', 'is_in_stock', 'min_sale_qty', 'max_sale_qty', 'notify_low_stock', 'notify_low_stock_qty')
    model = ProductStock
    extra = 0
    max_num = 1
    can_delete = False


class ProductSizesInline(admin.TabularInline):
    fields = ('title', 'sku', 'order')
    model = ProductSize
    extra = 1
    show_change_link = True


class ProductImagesInline(SortableStackedInline):
    fields = ('image',)
    # readonly_fields = ('large_image_path', 'medium_image_path', 'thumbnail_image_path')
    model = ProductImage
    extra = 1


class ProductSizeValueInline(admin.TabularInline):
    model = ProductSizeValue
    extra = 1


class ProductRelatedInline(admin.TabularInline):
    fields = ('related', 'order',)
    model = ProductRelated
    extra = 1
    fk_name = 'product'
    autocomplete_fields = ('related',)


class ProductAdmin(NonSortableParentAdmin):
    list_display = (
        'id',
        'name',
        'sku',
        'is_active',
        'price',
        #'quantity',
        'rating_summary'
        #'mage'
    )

    list_display_links = (
        'id',
        'name',
        'sku',
    )

    search_fields = (
        'id',
        'name',
        'sku',
    )

    # list_editable = ('quantity',)

    # raw_id_fields = ('parent', 'manufacturer', 'distributor', 'master',)

    list_filter = ('is_active', 'featured', 'member_only_product', 'customer_only_product',)

    inlines = (ProductImagesInline, ProductCategoryInline, ProductStockInline, ProductCustomerGroupPricingInline,
               ProductSizesInline, ProductRelatedInline)

    fieldsets = (
        (
            'Basic', {
                'fields': (
                    'name',
                    'sku',
                    # 'is_active',
                    'price',
                )
            }
        ),
        (
            'Details', {
                'fields': (
                    'description',
                    'short_description',
                )
            }
        ),
        (
            'Extra', {'fields': ()}
        ),
        (
            'Visibility', {
                'fields': (
                    'is_active',
                )
            }
        ),

        # (
        #     'Purchase limits', {
        #         'fields': (
        #             'minimumorder',
        #             'maximumorder',
        #         )
        #     }
        # ),
        (
            'SEO', {
                'fields': (
                    'exclude_from_sitemap',
                    'meta_title',
                    'meta_keyword',
                    'meta_description',
                    'url_key',
                    'url_path',
                )
            }
        )
    )

    def rating_summary(self, obj):
        summary = ReviewSummary.objects.filter(product=obj.id)
        if summary.exists():
            summary = summary.first()
            return "{} out of {}".format(summary.rating_summary, summary.rating_summary_base)
        else:
            return 0

    def get_list_display(self, request):
        from ..signals import product_admin_list_display

        """
        Return a sequence containing the fields to be displayed on the
        changelist.
        """
        super_list_display = self.list_display

        list_display_signals = product_admin_list_display.send(sender=self.__class__, request=request,
                                                               list_display=super_list_display, admin_obj=self)

        if list_display_signals:
            for signal_handler, inline_signal in list_display_signals:
                if isinstance(inline_signal, (list, tuple)):
                    super_list_display += inline_signal

        return tuple(set(super_list_display))

    def get_inlines(self, request, obj):
        from ..signals import product_admin_inlines

        existing_inlines = super().get_inlines(request, obj)

        inlines = existing_inlines
        inline_signals = product_admin_inlines.send(sender=self.__class__, request=request,
                                                    inlines=existing_inlines,
                                                    product_object=obj, admin_obj=self)

        if inline_signals:
            for signal_handler, inline_signal in inline_signals:
                inlines += inline_signal

        return tuple(set(inlines))

    def get_fieldsets(self, request, obj):
        from ..signals import product_admin_fieldsets__extra_field

        if not hasattr(self, 'called_fieldsets'):
            setattr(self, 'called_fieldsets', 1)
        else:
            if request.method == 'POST':
                delattr(self, 'called_fieldsets')
            else:
                self.called_fieldsets += 1
                if self.called_fieldsets > 2:
                    setattr(self, 'called_fieldsets', 1)

        super_fieldsets = super().get_fieldsets(request, obj)
        extra_fields = ()
        signals = product_admin_fieldsets__extra_field.send(sender=self.__class__, request=request,
                                                            fieldsets=super_fieldsets, product_object=obj,
                                                            admin_obj=self, extra_fields=())

        if signals:
            for signal_handler, inline_signal in signals:
                extra_fields += inline_signal

        extra_fields = tuple(set(extra_fields))

        for key, fs in enumerate(self.fieldsets):
            fs_title, fields = fs
            if fs_title == 'Extra':
                self.fieldsets[key][1]['fields'] = extra_fields
                pass

        return self.fieldsets

    def get_form(self, request, obj=None, **kwargs):
        from ..signals import product_admin_form

        super_form = super().get_form(request, obj, **kwargs)

        new_form_signal = product_admin_form.recurring_send(sender=self.__class__, request=request, form=super_form,
                                                            product_object=obj, admin_obj=self)
        if new_form_signal and isinstance(new_form_signal, tuple):
            new_form = new_form_signal[1]
        else:
            new_form = super_form

        return new_form

    def save_form(self, request, form, change):
        from ..signals import product_admin_form_save

        """
        Given a ModelForm return an unsaved instance. ``change`` is True if
        the object is being changed, and False if it's being added.
        """
        saved_form = super().save_form(request, form, change)

        product_admin_form_save.send_robust(sender=self.__class__, request=request, form=form, saved_form=saved_form,
                                            admin_obj=self)

        return saved_form


admin.site.register(Product, ProductAdmin)


class ProductSizeAdmin(admin.ModelAdmin):
    inlines = (ProductSizeValueInline,)


admin.site.register(ProductSize, ProductSizeAdmin)
