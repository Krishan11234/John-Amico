from django.db import connections
from .base_migrator import BaseMigrator
from ...models import ProductSize

from ...utils.helper import dictfetchall


class ProductSizeMigrate(BaseMigrator):
    model = ProductSize

    def get_data(self):

        sql = "SELECT DISTINCT jcp.id as prod_id, po.option_id, po.is_require, pov.sku, pov.sort_order, pot.title, pov.option_type_id " \
              "FROM stws_catalog_product_option po " \
              "INNER JOIN `johnamico`.`cart_product` jcp ON jcp.magento_id=po.product_id " \
              "INNER JOIN stws_catalog_product_option_type_value pov ON po.option_id=pov.option_id " \
              "INNER JOIN stws_catalog_product_option_type_title pot ON pov.option_type_id=pot.option_type_id AND pot.store_id=0 " \
              "WHERE pov.option_type_id NOT IN (SELECT magento_id FROM `johnamico`.`cart_productsize`)"

    # "INNER JOIN stws_mageways_optionsabsolute_customer_group_price mop ON mop.option_value_id=pov.option_type_id AND mop.store_id=0 " \

        insert_list = []

        with connections[super().magento_db()].cursor() as cursor:
            cursor.execute(sql)
            for row in dictfetchall(cursor):
                if row:
                    fields = {
                        'product_id': row['prod_id'],
                        'title': row['title'],
                        'sku': row['sku'],
                        'order': row['sort_order'],
                        'is_require': row['is_require'],
                        'magento_id': row['option_type_id'],
                    }
                    insert_list.append(self.model(**fields))

        return insert_list
