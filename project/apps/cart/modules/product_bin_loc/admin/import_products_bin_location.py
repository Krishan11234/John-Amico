import csv, datetime
from django.contrib import admin
from django.conf import settings
from django import forms
from datetime import datetime

from django.utils.safestring import mark_safe
from django.utils.translation import gettext as _
from django.db.models import F
from django.contrib import messages
from django.core.validators import FileExtensionValidator

from ....utils.product import upload_file
from ....utils.helper import get_unique_string

from solo.admin import SingletonModelAdmin

from ....models import Product
from ..models import ImportProductBinLocation, ProductBinLocation


class ImportProductsBinLocationAdminForm(forms.ModelForm):
    csv_file_to_import = forms.FileField(validators=[FileExtensionValidator(['csv'])],
                                         help_text=mark_safe("Check <a target='_blank' href='{}'>Sample CSV file</a>".format(
                                             settings.STATIC_URL + "modules/sample_product_bin_loc.csv")))

    class Meta:
        model = ImportProductBinLocation
        fields = ('csv_file_to_import',)


class ImportProductsBinLocationAdmin(SingletonModelAdmin, admin.ModelAdmin):
    change_form_template = "cart/modules/product_bin_loc/templates/admin/ja_importer.html"

    can_delete = False
    can_change = False
    can_add = False

    form = ImportProductsBinLocationAdminForm

    def _changeform_view(self, request, object_id, form_url, extra_context):
        if not extra_context:
            extra_context = {}

        extra_context.update({
            'title': _("Import Products Bin Location")
        })
        model = self.model
        # opts = model._meta

        if request.method == 'POST':
            ModelForm = self.get_form(request)
            form = ModelForm(request.POST, request.FILES)
            form_validated = form.is_valid()
            if form_validated:
                request.method = 'GET'

                try:
                    field = form.cleaned_data['csv_file_to_import']
                    new_file_name = datetime.now().strftime("%d-%b-%Y__%H-%M") + "__" + field.name
                    uploaded = upload_file(field, 'product_bin_location/' + new_file_name)

                    updated = 0
                    created = 0
                    not_found = 0
                    not_found_items = []

                    if uploaded:
                        with open(uploaded) as f:
                            r = csv.reader(f)
                            i = 0

                            for row in r:
                                if i < 1:
                                    i += 1
                                    continue
                                sku = str(row[0]).strip()
                                bin_location = str(row[1]).strip()

                                if sku:
                                    product_q = Product.objects.filter(sku=sku)
                                    if product_q.exists():
                                        product = product_q.get()

                                        prod_bin_q = ProductBinLocation.objects.filter(product=product)
                                        if prod_bin_q.exists():
                                            prod_bin = prod_bin_q.get()
                                            prod_bin.bin_location = bin_location

                                            updated += 1
                                        else:
                                            ProductBinLocation.objects.create(**{
                                                'product': product,
                                                'bin_location': bin_location
                                            })

                                            created += 1
                                    else:
                                        not_found += 1
                                        not_found_items.append(sku)

                                i += 1

                        if updated or created:
                            self.message_user(request, _("Total {} items updated and {} items were added".format(updated, created)), level=messages.SUCCESS)

                        if not_found and not_found_items:
                            self.message_user(request, _("Total {} SKU items were not found".format(not_found)), level=messages.ERROR)
                            self.message_user(request, _("These SKU items were not found: {}".format("; ".join(not_found_items))), level=messages.ERROR)

                    else:
                        self.message_user(request, _("Couldn't successfully process the file"), level=messages.ERROR)

                except Exception as e:
                    self.message_user(request, _(str(e)), level=messages.ERROR)

        return super()._changeform_view(request, object_id, form_url, extra_context)


admin.site.register(ImportProductBinLocation, ImportProductsBinLocationAdmin)
