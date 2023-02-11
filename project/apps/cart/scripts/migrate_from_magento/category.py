from django.db import connections
from .base_migrator import BaseMigrator
from django.contrib.auth.models import User, Group
from ...models import Category

from ...utils.helper import dictfetchall


class CategoryMigrate(BaseMigrator):
    model = Category

    def get_data(self):

        sql = "SELECT DISTINCT ce.entity_id, ce.parent_id, ce.created_at, ce.updated_at, ce.position, " \
              "ce_active.value AS is_active, " \
              "ce_name.value AS name, " \
              "ce_desc.value AS description, " \
              "ce_metat.value AS meta_title, " \
              "cs_metak.value AS meta_keywords, " \
              "cs_metad.value AS meta_description, " \
              "ce_uk.value AS url_key, " \
              "ce_up.value AS url_path, " \
              "ce_image.value AS image " \
              "FROM stws_catalog_category_entity ce " \
 \
              "LEFT JOIN stws_catalog_category_entity_int ce_active ON ce.entity_id=ce_active.entity_id " \
              "AND ce_active.attribute_id=42 " \
 \
              "LEFT JOIN stws_catalog_category_entity_text ce_desc ON ce.entity_id=ce_desc.entity_id " \
              "AND ce_desc.attribute_id=13 " \
 \
              "LEFT JOIN stws_catalog_category_entity_text cs_metak ON ce.entity_id=cs_metak.entity_id " \
              "AND cs_metak.attribute_id=47 " \
 \
              "LEFT JOIN stws_catalog_category_entity_text cs_metad ON ce.entity_id=cs_metad.entity_id " \
              "AND cs_metad.attribute_id=48 " \
 \
              "LEFT JOIN stws_catalog_category_entity_varchar ce_name ON ce.entity_id=ce_name.entity_id " \
              "AND ce_name.attribute_id=41 " \
 \
              "LEFT JOIN stws_catalog_category_entity_varchar ce_uk ON ce.entity_id=ce_uk.entity_id " \
              "AND ce_uk.attribute_id=43 " \
 \
              "LEFT JOIN stws_catalog_category_entity_varchar ce_image ON ce.entity_id=ce_image.entity_id " \
              "AND ce_image.attribute_id=45 " \
 \
              "LEFT JOIN stws_catalog_category_entity_varchar ce_metat ON ce.entity_id=ce_metat.entity_id " \
              "AND ce_metat.attribute_id=46 " \
 \
              "LEFT JOIN stws_catalog_category_entity_varchar ce_up ON ce.entity_id=ce_up.entity_id " \
              "AND ce_up.attribute_id=57 " \
 \
              "WHERE ce.entity_id NOT IN (SELECT magento_id FROM `johnamico`.`cart_category`) "

        insert_list = []

        with connections[super().magento_db()].cursor() as cursor:
            cursor.execute(sql)

            for row in dictfetchall(cursor):
                if row:
                    fields = {
                        'name': row['name'],
                        'description': row['description'],
                        'is_active': row['is_active'],
                        'parent_id': row['parent_id'] if row['parent_id'] else None,
                        'meta_title': row['meta_title'],
                        'meta_keywords': row['meta_keywords'],
                        'meta_description': row['meta_description'],
                        'url_key': row['url_key'],
                        'url_path': row['url_path'],
                        'order': row['position'],
                        'magento_id': row['entity_id']
                    }
                    insert_list.append(self.model(**fields))

        return insert_list
