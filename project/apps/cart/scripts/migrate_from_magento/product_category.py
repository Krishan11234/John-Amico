from django.db import connections
from .base_migrator import BaseMigrator
from ...models import CategoryProduct

from ...utils.helper import dictfetchall


class ProductCategoryMigrate(BaseMigrator):
    model = CategoryProduct

    def get_data(self):

        sql = "SELECT DISTINCT cp.category_id AS mage_cat_id, cp.product_id AS mage_prod_id, cp.position, " \
              "jcp.id AS prod_id, jcc.id AS cat_id " \
              "FROM stws_catalog_category_product cp " \
              "INNER JOIN `johnamico`.`cart_product` jcp ON jcp.magento_id=cp.product_id " \
              "INNER JOIN `johnamico`.`cart_category` jcc ON jcc.magento_id=cp.category_id " \
              "WHERE cp.product_id NOT IN (SELECT magento_id FROM `johnamico`.`cart_categoryproduct`)"

        insert_list = []

        with connections[super().magento_db()].cursor() as cursor:
            cursor.execute(sql)
            for row in dictfetchall(cursor):
                if row:
                    fields = {
                        'product_id': row['prod_id'],
                        'category_id': row['cat_id'],
                        'order': row['position']
                    }
                    insert_list.append(self.model(**fields))

        return insert_list
