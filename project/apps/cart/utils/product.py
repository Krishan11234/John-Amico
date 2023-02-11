import os, PIL, shutil
from PIL import Image
from django.conf import settings
from django.db.models.fields.files import ImageFieldFile


def get_image_version(product_image, version):
    if isinstance(product_image.image, ImageFieldFile):
        if os.path.isfile(settings.MEDIA_ROOT + '/' + product_image.image.name):
            if version in settings.PRODUCT_IMAGE_VARIATIONS.keys():

                system_width = settings.PRODUCT_IMAGE_VARIATIONS[version]['width']

                image_name = get_image_version_name(product_image, version)

                if not os.path.isfile(settings.MEDIA_ROOT + '/' + image_name):
                    img = Image.open(settings.MEDIA_ROOT + '/' + product_image.image.name)
                    image_width = img.width
                    width = image_width if image_width < system_width else system_width

                    wpercent = (width / float(img.size[0]))
                    hsize = int((float(img.size[1]) * float(wpercent)))
                    img_new = img.resize((width, hsize), PIL.Image.ANTIALIAS)

                    img_new.save(settings.MEDIA_ROOT + '/' + image_name)

                return image_name

    return ''


def get_image_version_name(product_image, version):
    system_width = settings.PRODUCT_IMAGE_VARIATIONS[version]['width']

    filename = os.path.basename(product_image.image.name)
    file_path = product_image.image.name.replace(filename, '')
    filename = os.path.splitext(filename)
    filename = filename[0] + '__' + str(system_width) + filename[1]
    # image_name = "product_images/%s/%s" % (str(product_image.product.id), filename)

    image_name = file_path + filename

    return image_name


def upload_image(original_image_path, new_image_path):

    if not os.path.isfile(settings.MEDIA_ROOT + '/' + new_image_path):
        return shutil.copy2(original_image_path, new_image_path)        # https://stackoverflow.com/a/30359308

    return False


def upload_file(file, new_file_path):
    new_path = settings.MEDIA_ROOT + '/' + new_file_path
    if not os.path.isfile(new_path):
        try:
            new_dir = '/'.join(new_path.split('/')[:-1])
            if not os.path.isdir(new_dir):
                os.makedirs(new_dir)

            with open(new_path, 'wb+') as destination:
                for chunk in file.chunks():
                    destination.write(chunk)
            return new_path
        except Exception as e:
            return False


def get_uploadable_image_name(instance, instance_folder_name, filename, outside_caller=False):
    file = os.path.basename(filename)
    filename = os.path.splitext(filename)
    extra_path = ''

    if filename[1]:
        extra_path += file[0] + "/" if file[0] else ''
        extra_path += file[1] + "/" if file[1] else ''

    if not (instance_folder_name and isinstance(instance_folder_name, str)):
        instance_folder_name = 'undefined'

    new_filename = instance_folder_name + "/%s" % (extra_path,)

    if outside_caller:
        if not os.path.isdir(settings.MEDIA_ROOT + "/" + new_filename):
            os.makedirs(settings.MEDIA_ROOT + "/" + new_filename)

    new_filename += file

    return new_filename


def create_image_versions(instance, force=False):
    from django.db import models

    create_versions = False

    if not isinstance(instance, models.Model):
        create_versions = False

    if instance.changes_tracker.has_changed('image'):
        create_versions = True

    if force:
        create_versions = True

    if create_versions:
        instance.filename = os.path.basename(instance.image.name)

        if hasattr(instance, 'large_image_path'):
            large_path = instance.get_image_version('large')
            instance.large_image_path = large_path

        if hasattr(instance, 'thumbnail_image_path'):
            thumb_path = instance.get_image_version('thumbnail')
            instance.thumbnail_image_path = thumb_path

        if hasattr(instance, 'medium_image_path'):
            medium_path = instance.get_image_version('medium')
            instance.medium_image_path = medium_path

        # post_save.disconnect(create_image_versions, sender=ProductImage)
        # instance.save()
        # post_save.connect(create_image_versions, sender=ProductImage)


def delete_image_versions(instance, mode='image_updated', old_image=None):
    modes = ['image_updated', 'image_removed']
    remove_from = None

    if instance.id:
        if mode in modes:
            if mode == 'image_updated':
                if old_image and isinstance(old_image, ImageFieldFile):
                    remove_from = old_image
            elif mode == 'image_removed':
                remove_from = instance.image

            if remove_from:
                if isinstance(remove_from, ImageFieldFile):
                    remove_from = remove_from.name

                if os.path.isfile(os.path.join(settings.MEDIA_ROOT, remove_from)):
                    os.remove(os.path.join(settings.MEDIA_ROOT, remove_from))

                if os.path.isfile(os.path.join(settings.MEDIA_ROOT, instance.large_image_path)):
                    os.remove(os.path.join(settings.MEDIA_ROOT, instance.large_image_path))

                if os.path.isfile(os.path.join(settings.MEDIA_ROOT, instance.medium_image_path)):
                    os.remove(os.path.join(settings.MEDIA_ROOT, instance.medium_image_path))

                if os.path.isfile(os.path.join(settings.MEDIA_ROOT, instance.thumbnail_image_path)):
                    os.remove(os.path.join(settings.MEDIA_ROOT, instance.thumbnail_image_path))