"""
Django settings for core project.

Generated by 'django-admin startproject' using Django 3.0.4.

For more information on this file, see
https://docs.djangoproject.com/en/3.0/topics/settings/

For the full list of settings and their values, see
https://docs.djangoproject.com/en/3.0/ref/settings/
"""

import os

# Build paths inside the project like this: os.path.join(BASE_DIR, ...)
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))


# Quick-start development settings - unsuitable for production
# See https://docs.djangoproject.com/en/3.0/howto/deployment/checklist/

# SECRET_KEY = os.environ.get("SECRET_KEY")
SECRET_KEY = 'dbaa1_i7%*3r9-=z-+_mz4r-!qeed@(-a_r(g@k8jo8y3r27%m'

# DEBUG = int(os.environ.get("DEBUG", default=0))
DEBUG = True

RUNNING_FROM_DOCKER = int(os.environ.get("RUNNING_FROM_DOCKER", default=0))

# 'DJANGO_ALLOWED_HOSTS' should be a single string of hosts with a space between each.
# For example: 'DJANGO_ALLOWED_HOSTS=localhost 127.0.0.1 [::1]'
# ALLOWED_HOSTS = os.environ.get("DJANGO_ALLOWED_HOSTS").split(" ")
ALLOWED_HOSTS = "localhost 127.0.0.1 [::1] johnamico.dev4.mvisolutions.com".split(" ")


def _dictmerge(a, b):
    """ deep merge two dictionaries """
    ret = dict(list(a.items()) + list(b.items()))
    for key in set(a.keys()) & set(b.keys()):
        if isinstance(a[key], dict) and isinstance(b[key], dict):
            ret[key] = _dictmerge(a[key], b[key])
        elif isinstance(a[key], list) and isinstance(b[key], list):
            for bl in b[key]:
                a[key].append(bl)
    return ret


if DEBUG:
    from .config.development import DEV_APPS, DATABASES as DEV_DATABASES, TEMPLATES as DEV_TEMPLATES, \
        AUTHENTICATION_BACKENDS, SITE_ID, X_FRAME_OPTIONS, MIDDLEWARE as DEV_MIDDLEWARE, \
        REMOVE_MIDDLEWARE as REMOVE_DEV_MIDDLEWARE, MESSAGE_TAGS


# Application definition

BASE_APPS = [
    "django.contrib.admin",
    "django.contrib.auth",
    "django.contrib.contenttypes",
    "django.contrib.sessions",
    "django.contrib.messages",
    "django.contrib.staticfiles",
]

THIRD_PARTY_APPS = [
    # 'mptt',
    # 'django_countries',
    'adminsortable',
    'admin_reorder',
    'solo',
    'django_summernote',
    'django_middleware_global_request',
    'authorizenet',
    # 'authorize',
]

LOCAL_APPS = [
    "apps.cart",
]

INSTALLED_APPS = BASE_APPS + THIRD_PARTY_APPS + LOCAL_APPS
if DEV_APPS:
    INSTALLED_APPS += DEV_APPS

MIDDLEWARE = [
    "django.middleware.security.SecurityMiddleware",
    "django.contrib.sessions.middleware.SessionMiddleware",
    "django.middleware.common.CommonMiddleware",
    "django.middleware.csrf.CsrfViewMiddleware",
    "django.contrib.auth.middleware.AuthenticationMiddleware",
    "django.contrib.messages.middleware.MessageMiddleware",
    "django.middleware.clickjacking.XFrameOptionsMiddleware",
    # "admin_reorder.middleware.ModelAdminReorder",
    "django_middleware_global_request.middleware.GlobalRequestMiddleware",
]
if DEV_MIDDLEWARE:
    MIDDLEWARE += DEV_MIDDLEWARE

if REMOVE_DEV_MIDDLEWARE:
    for rm in REMOVE_DEV_MIDDLEWARE:
        MIDDLEWARE.remove(rm)


ROOT_URLCONF = "core.urls"

TEMPLATES = [
    {
        "BACKEND": "django.template.backends.django.DjangoTemplates",
        "DIRS": [
            os.path.join(BASE_DIR, "templates"),
            os.path.join(BASE_DIR, "apps")
        ],
        "APP_DIRS": True,
        "OPTIONS": {
            "context_processors": [
                "django.template.context_processors.debug",
                "django.template.context_processors.request",
                "django.template.context_processors.static",
                "django.contrib.auth.context_processors.auth",
                "django.contrib.messages.context_processors.messages",
            ],
        },
    },
]

if DEV_TEMPLATES:
    for index, DT in enumerate(DEV_TEMPLATES):
        if index in TEMPLATES:
            if isinstance(DT, dict) and isinstance(TEMPLATES[index], dict):
                TEMPLATES[index] = _dictmerge(TEMPLATES[index], DT)


WSGI_APPLICATION = "core.wsgi.application"


# Database
# https://docs.djangoproject.com/en/3.0/ref/settings/#databases

if DEV_DATABASES:
    DATABASES = DEV_DATABASES
else:
    DATABASES = {
        "default": {
            "ENGINE": "django.db.backends.sqlite3",
            "NAME": os.path.join(BASE_DIR, "db.sqlite3"),
        },
    }


# Password validation
# https://docs.djangoproject.com/en/3.0/ref/settings/#auth-password-validators

AUTH_PASSWORD_VALIDATORS = [
    {
        "NAME": "django.contrib.auth.password_validation.UserAttributeSimilarityValidator",
    },
    {"NAME": "django.contrib.auth.password_validation.MinimumLengthValidator",},
    {"NAME": "django.contrib.auth.password_validation.CommonPasswordValidator",},
    {"NAME": "django.contrib.auth.password_validation.NumericPasswordValidator",},
]


# Internationalization
# https://docs.djangoproject.com/en/3.0/topics/i18n/

LANGUAGE_CODE = "en-us"

TIME_ZONE = "UTC"

USE_I18N = True

USE_L10N = True

USE_TZ = True

CSRF_COOKIE_HTTPONLY = False

DATA_UPLOAD_MAX_NUMBER_FIELDS = 5240    # higher than the count of fields


# Static files (CSS, JavaScript, Images)
# https://docs.djangoproject.com/en/3.0/howto/static-files/

STATIC_URL = "/staticfiles/"
STATIC_ROOT = os.path.join(BASE_DIR, 'staticfiles')

MEDIA_URL = "/mediafiles/"
MEDIA_ROOT = os.path.join(BASE_DIR, 'mediafiles')

CELERY_BROKER_URL = "redis://redis:6379"
CELERY_RESULT_BACKEND = "redis://redis:6379"
# CELERY_TIMEZONE = 'America/NewYork'


# MailCatcher
if DEBUG:
    if RUNNING_FROM_DOCKER:
        EMAIL_HOST = 'sendria'
        EMAIL_PORT = 8025
    else:
        EMAIL_HOST = '127.0.0.1'
        EMAIL_PORT = 1025
    EMAIL_HOST_USER = ''
    EMAIL_HOST_PASSWORD = ''
    EMAIL_USE_TLS = False


PRODUCT_IMAGE_VARIATIONS = {
    'large': {
        'width': 800
    },
    'medium': {
        'width': 300
    },
    'thumbnail': {
        'width': 100
    }
}

APPEND_SLASH = True

#https://github.com/mishbahr/django-modeladmin-reorder/blob/38283ae7898829b5e414a9f6fd1fc8e53edd9376/admin_reorder/middleware.py#L58

ADMIN_REORDER = (
    'auth',
    # 'cart',
    {
        'app': 'cart',
        'label': 'Customers',
        'models': (
            'cart.CustomerAddress',
            'cart.CustomerExtra',
        )
    },
    {
        'app': 'cart',
        'label': 'Sales',
        'models': (
            'cart.Quote',
            'cart.Order',
            'cart.Invoice',
            'cart.Shipment',
            'cart.CreditMemo',
        )
    },
    {
        'app': 'cart',
        'label': 'Catalog',
        'models': (
            'cart.Category',
            'cart.Product',
            'cart.ProductSize',
            'cart.ProductReview',
            'cart.ProductStock',
        )
    },
    {
        'app': 'cart',
        'label': 'Content',
        'models': (
            'cart.HomePageRow',
            'cart.BannerSliderCategory',
            'cart.BannerSlider',
        )
    },
    {
        'app': 'cart',
        'label': 'Shipping Carriers',
        'models': (
            'cart.ShippingJohnamicoCarrierMethod',
        )
    },
    {
        'app': 'cart',
        'label': 'Payment Methods',
        'models': (
            'cart.PaymentAuthnetCIMMethod',
        )
    },
    {
        'app': 'cart',
        'label': 'Configuration',
        'models': (
            {'model': 'cart.MenuType', 'label': 'Menu Configuration'},
            {'model': 'cart.SiteConfig', 'label': 'Site Configuration'},
            {'model': 'cart.StoreConfig', 'label': 'Store Configuration'},
        )
    },
)

SUMMERNOTE_CONFIG = {
    'summernote': {
        'popover': {
            'image': [
                ['custom', ['imageAttributes']],
                ['imagesize', ['imageSize100', 'imageSize50', 'imageSize25']],
                ['float', ['floatLeft', 'floatRight', 'floatNone']],
                ['remove', ['removeMedia']]
            ],
        },
        'imageAttributes': {
            'icon': '<i class="note-icon-link"/>',
            'removeEmpty': False,
            'disableUpload': False
        },
    },
    'js': (
        '/static/frontend/js/summernote-image-attributes.js',
    ),
}

STATICFILES_DIRS = [
    ('modules', os.path.join(BASE_DIR, 'apps/cart/modules/static'))
]

ADMIN_URL_NAME = 'admin'

from .config.modules import settings as module_settings, update_able_settings

for setting in module_settings:
    if setting in locals():
        if setting in update_able_settings:
            if isinstance(module_settings[setting], (list, tuple)):
                locals()[setting] += module_settings[setting]

# from .config.production import *