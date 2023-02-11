modules = {
    'order_tax': {'enabled': True},
    'autoship_order': {'enabled': True},
    'ja_exporter': {'enabled': True},
    'product_bin_loc': {'enabled': True},
}


def get_modules():
    from django.conf import settings

    all_modules = {}
    this_dir = "/".join(__file__.split('/')[:-1]) + "/"
    this_relative_dir = this_dir.replace(settings.BASE_DIR, '').lstrip('/')
    this_relative_import_dir = this_relative_dir.replace('/', '.')

    if modules:
        for module_name, module_config in modules.items():
            if 'enabled' in module_config and module_config['enabled']:
                if module_name not in all_modules:
                    all_modules[module_name] = {
                        'base_dir': this_dir + module_name,
                        'relative_dir': this_relative_dir + module_name,
                        'import_dir': this_relative_import_dir + module_name,
                    }

    return all_modules


def import_sub_modules(module_type, **kwargs):
    import os

    if module_type and isinstance(module_type, str):

        all_modules = get_modules()
        if all_modules:
            for module, module_config in all_modules.items():
                sub_module_path = module_config['base_dir'] + "/" + module_type
                sub_module_import_path = module_config['import_dir'] + "." + module_type

                if os.path.isdir(sub_module_path) or os.path.isfile(sub_module_path + ".py"):
                    theModule = __import__(sub_module_import_path, fromlist=[''])

                    if module_type == 'urls':
                        if 'urlpatterns' in kwargs:
                            kwargs['urlpatterns'] += getattr(theModule, 'urlpatterns', [])

    if module_type == 'urls':
        if 'urlpatterns' in kwargs:
            return kwargs['urlpatterns']
