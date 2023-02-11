from django.urls import path, include, re_path
from . import views

urlpatterns = [
    path("jaautoship/index", views.AutoshipRequests.as_view(), name="autoship_requests"),
    path("jaautoship/manage", views.AutoshipRequests.as_view(), name="autoship_requests"),
    path("jaautoship/manage/edit/id/<int:autoship_id>", views.AutoshipRequestsConfigure.as_view(), name="autoship_requests_configure"),
    path("jaautoship/index/cancelnext/id/<int:autoship_id>", views.AutoshipRequests.as_view(), name="autoship_requests_cancel_next"),
    path("jaautoship/index/output/id/<int:autoship_id>", views.AutoshipRequests.as_view(), name="autoship_requests_optout"),
]
