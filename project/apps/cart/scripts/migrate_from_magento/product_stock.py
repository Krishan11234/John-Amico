from django.db import connections
from .base_migrator import BaseMigrator
from ...models import ProductStock

from ...utils.helper import dictfetchall


class ProductStockMigrate(BaseMigrator):
    model = ProductStock

    def get_data(self):

        sql = "SELECT DISTINCT jcp.id AS prod_id, ps.product_id AS mage_prod_id, ps.item_id, ps.* " \
              "FROM stws_cataloginventory_stock_item ps " \
              "INNER JOIN `johnamico`.`cart_product` jcp ON jcp.magento_id=ps.product_id " \
              "WHERE ps.item_id NOT IN (SELECT magento_id FROM `johnamico`.`cart_productstock`)"

        insert_list = []

        with connections[super().magento_db()].cursor() as cursor:
            cursor.execute(sql)
            for row in dictfetchall(cursor):
                if row:
                    fields = {
                        'product_id': row['prod_id'],
                        'quantity': row['qty'],
                        'min_sale_qty': row['min_sale_qty'],
                        'max_sale_qty': row['max_sale_qty'],
                        'notify_low_stock_qty': row['notify_stock_qty'],
                        'is_qty_decimal': row['is_qty_decimal'],
                        'is_in_stock': row['is_in_stock'],
                        'magento_id': row['item_id'],
                    }
                    insert_list.append(self.model(**fields))

        return insert_list
