from .module_loader import import_sub_modules

urlpatterns = import_sub_modules('urls', urlpatterns=[])
