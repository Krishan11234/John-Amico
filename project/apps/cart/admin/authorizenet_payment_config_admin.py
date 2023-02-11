from django.contrib import admin
from solo.admin import SingletonModelAdmin

# from django_summernote.admin import SummernoteModelAdmin, SummernoteInlineModelAdmin

from ..models import PaymentAuthnetCIMMethod


class AuthorizenetPaymentConfigAdmin(SingletonModelAdmin):
    inlines = ()


admin.site.register(PaymentAuthnetCIMMethod, AuthorizenetPaymentConfigAdmin)
