from django.contrib import admin
from django.urls import path, include

from django.conf import settings
from django.conf.urls.static import static


admin.site.index_template = 'admin/admin_index.html'
admin.site.app_index_template = 'admin/admin_index.html'

urlpatterns = []

# if settings.DEBUG:
urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)
urlpatterns += static(settings.STATIC_URL, document_root=settings.STATIC_ROOT)

urlpatterns += [

    path(settings.ADMIN_URL_NAME + "/", admin.site.urls),
    # path('accounts/', include(('allauth.urls', "allauth"), namespace="accounts"), name="accounts", ),
    path('accounts/', include('django.contrib.auth.urls'), name="accounts", ),
    path('summernote/', include('django_summernote.urls')),
    path("", include("apps.cart.urls")),

]


