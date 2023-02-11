import os
from django.db import connections
from django.conf import settings
from .base_migrator import BaseMigrator
from django.contrib.auth.models import User, Group
from ...models import ProductImage

from ...models.product_image import get_uploadable_image_name
from ...utils.product import upload_image
from ...utils.helper import dictfetchall


class ProductMediaMigrate(BaseMigrator):
    model = ProductImage

    def get_data(self):

        sql = "SELECT DISTINCT pm.value_id as magento_id, jp.id AS product_id, " \
              "pm.value as image, pmv.position as `order` " \
              "FROM stws_catalog_product_entity_media_gallery pm " \
\
              "LEFT JOIN stws_catalog_product_entity_media_gallery_value pmv ON pmv.value_id=pm.value_id " \
              "AND pmv.store_id=1 " \
\
              "LEFT JOIN `johnamico`.`cart_product` jp ON pm.entity_id=jp.magento_id " \
\
              "WHERE pm.attribute_id=88 "\
              "AND pm.value_id NOT IN (SELECT magento_id FROM `johnamico`.`cart_productimage`) " \
              "LIMIT 0, 400"

        insert_list = []

        with connections[super().magento_db()].cursor() as cursor:
            cursor.execute(sql)
            for row in dictfetchall(cursor):
                if row:
                    resource_image = settings.MEDIA_ROOT + '/../../Resources/product' + row['image']
                    if row['product_id'] and os.path.isfile(resource_image):
                        fields = {
                            'product_id': row['product_id'],
                            'image': resource_image,
                            'order': row['order'],
                            'magento_id': row['magento_id']
                        }
                        model_obj = self.model(**fields)
                        model_obj.save()
                        new_path = get_uploadable_image_name(model_obj, fields['image'], outside_caller=True)

                        if not os.path.isfile(new_path):
                            new_path = upload_image(fields['image'], settings.MEDIA_ROOT + "/" + new_path).replace(settings.MEDIA_ROOT + "/", '')

                        if new_path:
                            model_obj.image = new_path
                            insert_list.append(model_obj)
                        else:
                            model_obj.delete()

        return insert_list

    def run_migrate(self, data_list=[], pre_callback_method=None, post_callback_method=None):
        return super().run_migrate(data_list, pre_callback_method, post_callback_method, single_save=True)
