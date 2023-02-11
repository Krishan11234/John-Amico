from django.db import connections
from .base_migrator import BaseMigrator
from ...models import ProductCustomerGroupPrice

from ...utils.helper import dictfetchall


class ProductGroupPriceMigrate(BaseMigrator):
    model = ProductCustomerGroupPrice

    def get_data(self):

        sql = "SELECT DISTINCT jcp.id AS prod_id, pgp.entity_id AS mage_prod_id, pgp.* " \
              "FROM stws_catalog_product_entity_group_price pgp " \
              "INNER JOIN `johnamico`.`cart_product` jcp ON jcp.magento_id=pgp.entity_id " \
              "WHERE pgp.value_id NOT IN (SELECT magento_id FROM `johnamico`.`cart_productcustomergroupprice`)"

        insert_list = []

        with connections[super().magento_db()].cursor() as cursor:
            cursor.execute(sql)
            for row in dictfetchall(cursor):
                if row:
                    fields = {
                        'product_id': row['prod_id'],
                        'customer_group_id': row['customer_group_id'],
                        'price': row['value'],
                        'is_percent': row['is_percent'],
                        'magento_id': row['value_id'],
                    }
                    insert_list.append(self.model(**fields))

        return insert_list
