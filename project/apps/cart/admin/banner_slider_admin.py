from django import forms
from django.contrib import admin
from django.contrib.auth.models import Group
from ..models import BannerSlider, BannerSliderCustomerGroup, BannerSliderCategory, BannerSliderToBannerSliderCategory
from django.forms.widgets import TextInput


class BannerSliderAdminForm(forms.ModelForm):
    customer_group = forms.MultipleChoiceField(choices=(), help_text="If no `Customer Group` is selected, this "
                                                                     "banner will be available for all Customer Group. "
                                                                     "If any group is selected, the banner will be "
                                                                     "displayed to that customer group only",
                                               required=False)
    slider_category = forms.MultipleChoiceField(choices=())

    # image_display = forms.Field()

    class Meta:
        model = BannerSlider
        # fields = '__all__'
        fields = ('title', 'image', 'is_active', 'slider_category', 'description', 'url', 'customer_group',
                  'text_align', 'css_class', 'sort_order')
        required_fields = ['title', 'image']
        # exclude = ('original_filename', 'filename', 'large_image_path', 'medium_image_path', 'thumbnail_image_path')
        widgets = {
            'text_color': TextInput(attrs={'type': 'color'}),
        }


class BannerSliderAdmin(admin.ModelAdmin):
    search_fields = ('title',)
    form = BannerSliderAdminForm

    readonly_fields = ('image_display',)

    """
    Reason behind not defining the field choices directly into the Form:
    If the choices are initialised through the form itself, it gets cached, and 
    if the choices are updated, it doesn't get reflected in the form.   
    """

    def get_form(self, request, obj=None, change=False, **kwargs):
        form = super().get_form(request, obj, change, **kwargs)
        if form:
            customer_group_choices = Group.objects.values_list('id', 'name')
            customer_group_initial = ()

            category_choices = BannerSliderCategory.objects.filter(status=1).values_list('id', 'title')
            category_initial = ()

            if obj:
                customer_groups_q = BannerSliderCustomerGroup.objects.filter(banner=obj)
                if customer_groups_q.exists():
                    customer_group_initial = tuple(customer_groups_q.values_list('customer_group', flat=True))

                categories_q = BannerSliderToBannerSliderCategory.objects.filter(banner=obj)
                if categories_q.exists():
                    category_initial = tuple(categories_q.values_list('banner_category', flat=True))

            if 'customer_group' in form.base_fields:
                form.base_fields['customer_group'].choices = customer_group_choices
                form.base_fields['customer_group'].initial = customer_group_initial

                form.base_fields['slider_category'].choices = category_choices
                form.base_fields['slider_category'].initial = category_initial

        return form

    def save_model(self, request, obj, form, change):
        banner = super().save_model(request, obj, form, change)
        if hasattr(form, 'cleaned_data') and obj:
            if 'customer_group' in form.cleaned_data:

                if form.cleaned_data['customer_group']:
                    obj.all_customer_groups = False
                else:
                    obj.all_customer_groups = True

                obj.save()

                banner_customer_groups = {}
                banner_customer_groups_keys = []

                banner_customer_groups_q = BannerSliderCustomerGroup.objects.filter(banner=obj)
                for bcg in banner_customer_groups_q:
                    banner_customer_groups[bcg.customer_group_id] = bcg
                    banner_customer_groups_keys.append(bcg.customer_group_id)

                for bcg_id in form.cleaned_data['customer_group']:
                    bcg_id = int(bcg_id)

                    if bcg_id in banner_customer_groups_keys:
                        banner_customer_groups_keys.remove(bcg_id)
                    else:
                        BannerSliderCustomerGroup.objects.create(
                            banner=obj,
                            customer_group_id=bcg_id
                        )

                if banner_customer_groups_keys:
                    for bcg_id in banner_customer_groups_keys:
                        banner_customer_groups[bcg_id].delete()

            else:
                obj.all_customer_groups = True
                obj.save()

            if 'slider_category' in form.cleaned_data and form.cleaned_data['slider_category']:
                banner_categories = {}
                banner_categories_keys = []

                banner_categories_q = BannerSliderToBannerSliderCategory.objects.filter(banner=obj)
                for bcg in banner_categories_q:
                    banner_categories[bcg.banner_category_id] = bcg
                    banner_categories_keys.append(bcg.banner_category_id)

                for bcg_id in form.cleaned_data['slider_category']:
                    bcg_id = int(bcg_id)

                    if bcg_id in banner_categories_keys:
                        banner_categories_keys.remove(bcg_id)
                    else:
                        BannerSliderToBannerSliderCategory.objects.create(
                            banner=obj,
                            banner_category_id=bcg_id
                        )

                if banner_categories_keys:
                    for bcg_id in banner_categories_keys:
                        banner_categories[bcg_id].delete()

        return banner


admin.site.register(BannerSlider, BannerSliderAdmin)


class BannerCategoryAdminForm(forms.ModelForm):
    class Meta:
        widgets = {
            'navigation_bg_color': TextInput(attrs={'type': 'color'}),
            'navigation_bg_hover_color': TextInput(attrs={'type': 'color'}),
            'navigation_arrow_color': TextInput(attrs={'type': 'color'}),
            'navigation_arrow_hover_color': TextInput(attrs={'type': 'color'}),
        }


class BannerCategoryAdmin(admin.ModelAdmin):
    form = BannerCategoryAdminForm


admin.site.register(BannerSliderCategory, BannerCategoryAdmin)
