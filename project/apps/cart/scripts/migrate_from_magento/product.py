# SELECT * FROM `stws_eav_attribute` WHERE `attribute_id` IN (94,93,77,78,75,80,76,121,173,132,205,203,189,182,169,
# 72,73,83,84,71,97,82,83,84) LIMIT 50

from django.db import connections
from .base_migrator import BaseMigrator
from ...models import Product

from ...utils.helper import dictfetchall, parse_date


class ProductMigrate(BaseMigrator):
    model = Product
    update_fields = ['short_description', 'direction', 'ingredient', 'warning']

    def get_data(self):

        sql = "SELECT DISTINCT pe.entity_id, pe.type_id, pe.sku, pe.created_at, pe.updated_at, " \
              "pe_price.value AS price, " \
              "pe_special_price.value AS special_price, " \
              "pe_cost.value AS cost, " \
              "pe_weight.value AS weight, " \
              "pe_status.value AS is_active, " \
              "pe_mop.value AS member_only_product, " \
              "pe_cop.value AS customer_only_product, " \
              "pe_tx.value AS tax_class_id, " \
              "pe_name.value AS name, " \
              "pe_desc.value AS description, " \
              "pe_sdesc.value AS short_description, " \
              "pe_directio.value AS direction, " \
              "pe_ing.value AS ingredient, " \
              "pe_cau.value AS caution, " \
              "pe_warn.value AS warning, " \
              "pe_metat.value AS meta_title, " \
              "pe_metak.value AS meta_keywords, " \
              "pe_metad.value AS meta_description, " \
              "pe_image.value AS image, " \
              "pe_small_image.value AS small_image, " \
              "pe_thumb_image.value AS thumbnail, " \
              "pe_uk.value AS url_key, " \
              "pe_up.value AS url_path, " \
              "pe_gma.value AS gift_message_available, " \
              "pe_binl.value AS bin_location " \
              "FROM stws_catalog_product_entity pe " \
 \
              "LEFT JOIN stws_catalog_product_entity_decimal pe_price ON pe.entity_id=pe_price.entity_id " \
              "AND pe_price.attribute_id=75 AND pe_price.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_decimal pe_special_price ON pe.entity_id=pe_special_price.entity_id " \
              "AND pe_special_price.attribute_id=76 AND pe_special_price.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_decimal pe_cost ON pe.entity_id=pe_cost.entity_id " \
              "AND pe_cost.attribute_id=79 AND pe_cost.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_decimal pe_weight ON pe.entity_id=pe_weight.entity_id " \
              "AND pe_weight.attribute_id=80 AND pe_weight.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_int pe_status ON pe.entity_id=pe_status.entity_id " \
              "AND pe_status.attribute_id=96 AND pe_status.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_int pe_mop ON pe.entity_id=pe_mop.entity_id " \
              "AND pe_mop.attribute_id=173 AND pe_mop.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_int pe_cop ON pe.entity_id=pe_cop.entity_id " \
              "AND pe_cop.attribute_id=189 AND pe_cop.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_int pe_tx ON pe.entity_id=pe_tx.entity_id " \
              "AND pe_tx.attribute_id=121 AND pe_tx.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_text pe_desc ON pe.entity_id=pe_desc.entity_id " \
              "AND pe_desc.attribute_id=72 AND pe_desc.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_text pe_sdesc ON pe.entity_id=pe_sdesc.entity_id " \
              "AND pe_sdesc.attribute_id=73 AND pe_sdesc.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_text pe_metak ON pe.entity_id=pe_metak.entity_id " \
              "AND pe_metak.attribute_id=83 AND pe_metak.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_text pe_directio ON pe.entity_id=pe_directio.entity_id " \
              "AND pe_directio.attribute_id=171 AND pe_directio.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_text pe_ing ON pe.entity_id=pe_ing.entity_id " \
              "AND pe_ing.attribute_id=172 AND pe_ing.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_text pe_cau ON pe.entity_id=pe_cau.entity_id " \
              "AND pe_cau.attribute_id=174 AND pe_cau.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_text pe_warn ON pe.entity_id=pe_warn.entity_id " \
              "AND pe_warn.attribute_id=175 AND pe_warn.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_varchar pe_name ON pe.entity_id=pe_name.entity_id " \
              "AND pe_name.attribute_id=71 AND pe_name.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_varchar pe_image ON pe.entity_id=pe_image.entity_id " \
              "AND pe_image.attribute_id=85 AND pe_image.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_varchar pe_small_image ON pe.entity_id=pe_small_image.entity_id " \
              "AND pe_small_image.attribute_id=86 AND pe_small_image.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_varchar pe_thumb_image ON pe.entity_id=pe_thumb_image.entity_id " \
              "AND pe_thumb_image.attribute_id=87 AND pe_thumb_image.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_varchar pe_uk ON pe.entity_id=pe_uk.entity_id " \
              "AND pe_uk.attribute_id=97 AND pe_uk.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_varchar pe_metat ON pe.entity_id=pe_metat.entity_id " \
              "AND pe_metat.attribute_id=82 AND pe_metat.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_varchar pe_metad ON pe.entity_id=pe_metad.entity_id " \
              "AND pe_metad.attribute_id=84 AND pe_metad.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_varchar pe_up ON pe.entity_id=pe_up.entity_id " \
              "AND pe_up.attribute_id=98 AND pe_up.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_varchar pe_gma ON pe.entity_id=pe_gma.entity_id " \
              "AND pe_gma.attribute_id=122 AND pe_gma.store_id=0 " \
 \
              "LEFT JOIN stws_catalog_product_entity_varchar pe_binl ON pe.entity_id=pe_binl.entity_id " \
              "AND pe_binl.attribute_id=185 AND pe_binl.store_id=0 "

        if self.migration_mode == 'INSERT':
            sql += " WHERE pe.entity_id NOT IN (SELECT magento_id FROM `johnamico`.`cart_product`)"
        elif self.migration_mode == 'INSERT':
            sql += " WHERE pe.entity_id IN (SELECT magento_id FROM `johnamico`.`cart_product`)"

        data_list = []

        with connections[super().magento_db()].cursor() as cursor:
            cursor.execute(sql)

            for row in dictfetchall(cursor):
                if row:
                    fields = {}

                    if self.migration_mode == 'UPDATE':
                        for f in self.update_fields:
                            if f in row:
                                fields[f] = row[f]

                        fields['magento_id'] = row['entity_id']

                    elif self.migration_mode == 'INSERT':
                        fields = {
                            'name': row['name'],
                            'description': row['description'],
                            'short_description': row['short_description'],
                            'direction': row['direction'],
                            'ingredient': row['ingredient'],
                            'warning': row['warning'],

                            'is_active': 1 if row['is_active'] == 1 else 0,
                            'sku': row['sku'],
                            'type': row['type_id'],
                            'tax_class_id': row['tax_class_id'] if row['tax_class_id'] != 0 else 3,

                            'price': row['price'],
                            'weight': row['weight'],

                            'member_only_product': row['member_only_product'],
                            'customer_only_product': row['customer_only_product'],
                            'exclude_from_sitemap': 0,
                            'bin_location': row['bin_location'],

                            'meta_title': row['meta_title'],
                            'meta_keyword': row['meta_keywords'],
                            'meta_description': row['meta_description'],
                            'url_key': row['url_key'],
                            'url_path': row['url_path'],

                            'created_at': parse_date(row['created_at']),
                            'updated_at': parse_date(row['updated_at']),
                            'magento_id': row['entity_id']
                        }

                    model_obj = None
                    if self.migration_mode == 'UPDATE':
                        model_obj = self.model.objects.filter(magento_id=fields['magento_id']).get()
                        for (key, value) in fields.items():
                            setattr(model_obj, key, value)

                    elif self.migration_mode == 'INSERT':
                        model_obj = self.model(**fields)

                    if model_obj:
                        data_list.append(model_obj)

        return data_list

