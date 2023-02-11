from django.db import connections
from .base_migrator import BaseMigrator
from ...models import TaxClass

from ...utils.helper import dictfetchall


class TaxClassMigrate(BaseMigrator):
    model = TaxClass

    def get_data(self):

        sql = "SELECT DISTINCT tx.class_id, tx.class_name " \
              "FROM stws_tax_class tx " \
 \
              "WHERE tx.class_id NOT IN (SELECT magento_id FROM `johnamico`.`cart_taxclass`) "

        insert_list = []

        with connections[super().magento_db()].cursor() as cursor:
            cursor.execute(sql)

            for row in dictfetchall(cursor):
                if row:
                    fields = {
                        'id': row['class_id'],
                        'name': row['class_name'],
                        'magento_id': row['class_id']
                    }
                    insert_list.append(self.model(**fields))

        return insert_list
