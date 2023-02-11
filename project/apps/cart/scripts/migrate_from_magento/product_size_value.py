from django.db import connections
from .base_migrator import BaseMigrator
from ...models import ProductSizeValue

from ...utils.helper import dictfetchall


class ProductSizeValueMigrate(BaseMigrator):
    model = ProductSizeValue

    def get_data(self):

        sql = "SELECT DISTINCT jps.id, mop.option_value_id, mop.mop_id, mop.price, mop.price_type, mop.customer_group " \
              "FROM stws_mageways_optionsabsolute_customer_group_price mop " \
              "INNER JOIN `johnamico`.`cart_productsize` jps ON jps.magento_id=mop.option_value_id " \
              "WHERE mop.mop_id NOT IN (SELECT magento_id FROM `johnamico`.`cart_productsizevalue`)"

        insert_list = []

        with connections[super().magento_db()].cursor() as cursor:
            cursor.execute(sql)
            for row in dictfetchall(cursor):
                if row:
                    fields = {
                        'product_size_id': row['id'],
                        'customer_group_id': row['customer_group'] if row['customer_group'] != 0 else 111,
                        'price': row['price'],
                        'price_type': row['price_type'],
                        'magento_id': row['mop_id'],
                    }
                    insert_list.append(self.model(**fields))

        return insert_list
