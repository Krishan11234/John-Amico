from django.contrib import admin
from django import forms
from datetime import datetime
from django.utils.translation import gettext as _
from django.db.models import F
from django.contrib import messages

from solo.admin import SingletonModelAdmin

from ..models import StwOrder
from ..utils import data_to_csv


class ExportSTWOrdersAdminForm(forms.ModelForm):
    start_date = forms.CharField(widget=forms.TextInput(attrs={'type': 'date'}))
    end_date = forms.CharField(widget=forms.TextInput(attrs={'type': 'date'}))
    il_only = forms.BooleanField(label="Illinois Orders Only", required=False,
                                 help_text="If not selected, system will export all the orders except "
                                           "Illinois Orders. Please note: this selection will only work for "
                                           "Consumer Orders. For \"Professional Member\" checkbox, this field "
                                           "will not be effective ")
    professional_members_only = forms.BooleanField(label="Professional Member Orders Only", required=False)

    # customer_group = forms.MultipleChoiceField(choices=(), help_text="If no `Customer Group` is selected, system will"
    #                                                                  " export all the orders for all Customer Group. "
    #                                                              "If any group is selected, the banner only orders "
    #                                                                  "with those Customer Group will be exported",
    #                                            required=False)

    class Meta:
        model = StwOrder
        fields = ('start_date', 'end_date', 'il_only', 'professional_members_only')


class ExportSTWOrdersAdmin(SingletonModelAdmin, admin.ModelAdmin):
    change_form_template = "cart/modules/ja_exporter/templates/admin/ja_exporter.html"

    can_delete = False
    can_change = False
    can_add = False

    form = ExportSTWOrdersAdminForm

    def _changeform_view(self, request, object_id, form_url, extra_context):
        if not extra_context:
            extra_context = {}

        extra_context.update({
            'title': _("Export STW Orders")
        })
        model = self.model
        # opts = model._meta

        if request.method == 'POST':
            ModelForm = self.get_form(request)
            form = ModelForm(request.POST, request.FILES)
            form_validated = form.is_valid()
            if form_validated:
                request.method = 'GET'

                start_date = form.cleaned_data.get('start_date', '')
                end_date = form.cleaned_data.get('end_date', '')
                il_only = form.cleaned_data.get('il_only', False)
                professional_members_only = form.cleaned_data.get('professional_members_only', False)

                try:
                    if start_date and end_date:
                        start_date = datetime.strptime(start_date, '%Y-%m-%d')
                        end_date = datetime.strptime(end_date, '%Y-%m-%d')
                        end_date.replace(hour=23, minute=59, second=59)

                        conditions = {
                            'is_professional': professional_members_only,
                            'created_at__gte': start_date,
                            'created_at__lte': end_date,
                        }
                        exclude_conditions = {}

                        if not professional_members_only:
                            if il_only:
                                conditions['shipping_address__state'] = 'Illinois'
                            else:
                                exclude_conditions['shipping_address__state'] = 'Illinois'

                        orders = model.objects.filter(**conditions).exclude(**exclude_conditions)\
                            .annotate(invoice_date=F('created_at'))

                        if orders.exists():
                            csv_file_name = ''

                            if professional_members_only:
                                export_type = 'member'
                                csv_file_name += 'STW_Member__'
                            else:
                                if il_only:
                                    export_type = 'consumer_tax_illinois'
                                    csv_file_name += 'STW_Consumer_Illinois__'
                                else:
                                    export_type = 'consumer_tax_all_except_illinois'
                                    csv_file_name += 'STW_Consumer_All__'

                            csv_file_name += "Range--" + start_date.strftime('%d-%m-%Y') + "-to-" + end_date.strftime('%d-%m-%Y')
                            csv_file_name += "___" + datetime.now().strftime("%d-%m-%Y__%H-%M")
                            csv_file_name += ".csv"

                            csv_rows = data_to_csv.convert_order_to_csv_rows(orders.all(), export_type)

                            if csv_rows and isinstance(csv_rows, list) and len(csv_rows) > 1:
                                response = data_to_csv.make_and_force_csv(request, csv_rows, csv_file_name)

                                if response:
                                    return response
                            else:
                                self.message_user(request, _("No exportable order found with this date range."), messages.ERROR)
                        else:
                            self.message_user(request, _("No order found with this date range."), messages.ERROR)

                except Exception as e:
                    raise forms.ValidationError(str(e))

        # return TemplateResponse(request, "cart/modules/ja_exporter/templates/admin/ja_exporter.html", context)
        return super()._changeform_view(request, object_id, form_url, extra_context)


admin.site.register(StwOrder, ExportSTWOrdersAdmin)
