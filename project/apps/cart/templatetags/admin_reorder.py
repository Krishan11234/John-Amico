from __future__ import unicode_literals

from copy import deepcopy
from django.conf import settings
from django.core.exceptions import ImproperlyConfigured
from django import template


register = template.Library()


@register.filter(name='reorder_apps', is_safe=True)
def reorder_apps(apps):
    return ModelAdminReorder().process_app_list(apps)


class ModelAdminReorder(object):
    app_list = []
    config = None
    models_list = []

    def init_config(self, app_list):
        self.app_list = app_list

        self.config = getattr(settings, 'ADMIN_REORDER', None)
        if not self.config:
            # ADMIN_REORDER settings is not defined.
            raise ImproperlyConfigured('ADMIN_REORDER config is not defined.')

        if not isinstance(self.config, (tuple, list)):
            raise ImproperlyConfigured(
                'ADMIN_REORDER config parameter must be tuple or list. '
                'Got {config}'.format(config=self.config))

        # Flatten all models from apps
        self.models_list = []
        for app in app_list:
            for model in app['models']:
                model['model_name'] = self.get_model_name(
                    app['app_label'], model['object_name'])
                self.models_list.append(model)

    def get_app_list(self):
        dict_app_list = {}
        # ordered_app_list = []
        for app_config in self.config:
            app = self.make_app(app_config)
            if app:
                key = str(app['app_label']) + "__" + str(app['name'])
                if key not in dict_app_list.keys():
                    dict_app_list[key] = app
                else:
                    dict_app_list[key]['models'] += app['models']
                # ordered_app_list.append(app)
        return list(dict_app_list.values())

    def make_app(self, app_config):
        if not isinstance(app_config, (dict, str)):
            raise TypeError('ADMIN_REORDER list item must be '
                            'dict or string. Got %s' % repr(app_config))

        if isinstance(app_config, str):
            # Keep original label and models
            return self.find_app(app_config)
        else:
            return self.process_app(app_config)

    def find_app(self, app_label):
        for app in self.app_list:
            if app['app_label'] == app_label:
                return app

    def get_model_name(self, app_name, model_name):
        if '.' not in model_name:
            model_name = '%s.%s' % (app_name, model_name)
        return model_name

    def process_app(self, app_config):
        if 'app' not in app_config:
            raise NameError('ADMIN_REORDER list item must define '
                            'a "app" name. Got %s' % repr(app_config))

        app = self.find_app(app_config['app'])
        if app:
            app = deepcopy(app)
            # Rename app
            if 'label' in app_config:
                app['name'] = app_config['label']

            # Process app models
            if 'models' in app_config:
                models_config = app_config.get('models')
                models = self.process_models(models_config)
                if models:
                    app['models'] = models
                else:
                    return None

            # Process app custom admins
            # if 'custom_admins' in app_config:
            #     custom_admins_config = app_config.get('custom_admins')
            #     custom_admins = self.process_custom_admins(custom_admins_config)
            #     if custom_admins:
            #         if 'models' in app:
            #             app['models'] = []
            #         app['models'] += custom_admins
            #     else:
            #         return None

            return app

    def process_custom_admins(self, custom_admins_config):
        if not isinstance(custom_admins_config, (dict, list, tuple)):
            raise TypeError('"custom_admins" config for ADMIN_REORDER list '
                            'item must be dict or list/tuple. '
                            'Got %s' % repr(custom_admins_config))

        ordered_custom_admins_list = []
        for custom_admin_config in custom_admins_config:
            custom_admin = None
            if isinstance(custom_admin_config, dict):
                custom_admin = self.process_custom_admin(custom_admin_config)
                if custom_admin:
                    ordered_custom_admins_list.append(custom_admin)

        return ordered_custom_admins_list

    def process_custom_admin(self, custom_admin_config):
        if 'admin_url' in custom_admin_config and custom_admin_config['admin_url']:
            pass


    def process_models(self, models_config):
        if not isinstance(models_config, (dict, list, tuple)):
            raise TypeError('"models" config for ADMIN_REORDER list '
                            'item must be dict or list/tuple. '
                            'Got %s' % repr(models_config))

        ordered_models_list = []
        for model_config in models_config:
            model = None
            if isinstance(model_config, dict):
                model = self.process_model(model_config)
            else:
                model = self.find_model(model_config)

            if model:
                ordered_models_list.append(model)

        return ordered_models_list

    def find_model(self, model_name):
        for model in self.models_list:
            if model['model_name'] == model_name:
                return model

    def process_model(self, model_config):
        # Process model defined as { model: 'model', 'label': 'label' }
        for key in ('model', 'label', ):
            if key not in model_config:
                return
        model = self.find_model(model_config['model'])
        if model:
            model['name'] = model_config['label']
            return model

    def process_app_list(self, app_list):
        if app_list:
            self.init_config(app_list)
            return self.get_app_list()

        return app_list
