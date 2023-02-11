#!/bin/sh

# python manage.py flush --no-input

# python manage.py migrate
# python manage.py collectstatic --no-input --clear



# https://stackoverflow.com/q/49693148
celery -A core worker -l info &
celery -A core beat -l debug &

exec "$@"
