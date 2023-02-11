from ..settings import *

DATABASES = {}

if RUNNING_FROM_DOCKER:
    DATABASES['default'] = {
        'ENGINE': 'django.db.backends.mysql',
        'NAME': 'johnamico',
        'USER': 'root',
        'PASSWORD': 'root',
        # 'HOST': 'mysqldb',
        'HOST': 'mariadb',
        'PORT': 3306,
    }
    DATABASES['magento'] = {
        'ENGINE': 'django.db.backends.mysql',
        'NAME': 'johnamico_magento',
        'USER': 'root',
        'PASSWORD': 'root',
        # 'HOST': 'mysqldb',
        'HOST': 'mariadb',
        'PORT': 3306,
    }
else:
    DATABASES['default'] = {
        'ENGINE': 'django.db.backends.mysql',
        'NAME': 'johnamico',
        'USER': 'root',
        'PASSWORD': 'krishan',
        'HOST': 'localhost',
        'PORT': '',
    }
    DATABASES['magento'] = {
        'ENGINE': 'django.db.backends.mysql',
        'NAME': 'johnamico_magento',
        'USER': 'root',
        'PASSWORD': 'krishan',
        'HOST': 'localhost',
        'PORT': 3306,
    }

DEV_APPS = [
    'django_extensions',

    # The following apps are required: for django-allauth
    'django.contrib.sites',
    # 'allauth',
    # 'allauth.account',
    # 'allauth.socialaccount',

    # 'background_task',
    # 'django_celery_monitor'
]

# INSTALLED_APPS += DEV_APPS

# `allauth` needs this from django
TEMPLATES = [{
    "OPTIONS": {
        "context_processors": [
            # 'django.template.context_processors.request'
        ]
    }
}]
AUTHENTICATION_BACKENDS = [
    # Needed to login by username in Django admin, regardless of `allauth`
    'django.contrib.auth.backends.ModelBackend',

    # Professional Member Authentication Backend
    'apps.cart.backends.BackendForCustomMembers',

    # `allauth` specific authentication methods, such as login by e-mail
    # 'allauth.account.auth_backends.AuthenticationBackend',
]
SITE_ID = 1


# `django_summernote` configurations


X_FRAME_OPTIONS = 'SAMEORIGIN'


# `django_middleware_global_request` configurations

MIDDLEWARE = [
    # 'django_middleware_global_request.middleware.GlobalRequestMiddleware',
]

REMOVE_MIDDLEWARE = ["django.middleware.csrf.CsrfViewMiddleware"]


from django.contrib.messages import constants as message_constants
MESSAGE_TAGS = {message_constants.DEBUG: 'debug',
                message_constants.INFO: 'info',
                message_constants.SUCCESS: 'success',
                message_constants.WARNING: 'warning',
                message_constants.ERROR: 'danger',}


SESSION_COOKIE_AGE = 14400      # 4 hours in seconds