from django.contrib import admin
from django import forms
from datetime import datetime
from django.utils.translation import gettext as _
from django.db.models import F
from django.contrib import messages

from solo.admin import SingletonModelAdmin

from ..models import ExportProduct
from ..utils import data_to_csv


class ExportProductsAdminForm(forms.ModelForm):
    export_product_prices = forms.BooleanField(label="Export All Product Prices", required=True)

    class Meta:
        model = ExportProduct
        fields = ('export_product_prices',)


class ExportProductsAdmin(SingletonModelAdmin, admin.ModelAdmin):
    change_form_template = "cart/modules/ja_exporter/templates/admin/ja_exporter.html"

    can_delete = False
    can_change = False
    can_add = False

    form = ExportProductsAdminForm

    def _changeform_view(self, request, object_id, form_url, extra_context):
        if not extra_context:
            extra_context = {}

        extra_context.update({
            'title': _("Export Product Prices")
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
                    conditions = {}
                    exclude_conditions = {'type': 'virtual'}

                    products = model.objects.filter(**conditions).exclude(**exclude_conditions).order_by('id')

                    if products.exists():
                        csv_file_name = 'JAProducts___' + datetime.now().strftime("%d-%m-%Y__%H-%M") + ".csv"

                        csv_rows = data_to_csv.convert_product_to_csv_rows(products.all())

                        if csv_rows and isinstance(csv_rows, list) and len(csv_rows) > 1:
                            response = data_to_csv.make_and_force_csv(request, csv_rows, csv_file_name)

                            if response:
                                return response
                        else:
                            self.message_user(request, _("No exportable product found with this date range."),
                                              messages.ERROR)
                    else:
                        self.message_user(request, _("No product found with this date range."), messages.ERROR)

                except Exception as e:
                    raise forms.ValidationError(str(e))

        # return TemplateResponse(request, "cart/modules/ja_exporter/templates/admin/ja_exporter.html", context)
        return super()._changeform_view(request, object_id, form_url, extra_context)


admin.site.register(ExportProduct, ExportProductsAdmin)
