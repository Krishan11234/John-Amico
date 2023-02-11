from django.db import connections
from .base_migrator import BaseMigrator
from ...models import ProductReview

from ...utils.helper import dictfetchall


class ProductReviewMigrate(BaseMigrator):
    model = ProductReview

    def get_data(self):

        # stws_review_status
        # stws_review
        # stws_review_detail
        # stws_mvi_professional_review


        sql = "SELECT DISTINCT cpl.link_id, cpl.product_id AS mage_prod_id, cpl.linked_product_id AS mage_prod_link_id, " \
              "plai.value AS prod_position, jcp.id AS prod_id, jcpl.id AS linked_prod_id " \
              "FROM stws_catalog_product_link cpl " \
              "INNER JOIN `johnamico`.`cart_product` jcp ON jcp.magento_id=cpl.product_id " \
              "LEFT JOIN `johnamico`.`cart_product` jcpl ON jcpl.id=cpl.linked_product_id " \
              "LEFT JOIN stws_catalog_product_link_attribute_int plai ON plai.link_id=cpl.link_id " \
              "WHERE cpl.link_id NOT IN (SELECT magento_id FROM `johnamico`.`cart_productrelated`)"

        insert_list = []

        with connections[super().magento_db()].cursor() as cursor:
            cursor.execute(sql)
            for row in dictfetchall(cursor):
                if row:
                    fields = {
                        'product_id': row['prod_id'],
                        'related_id': row['linked_prod_id'],
                        'order': row['prod_position'],
                        'magento_id': row['link_id'],
                    }
                    insert_list.append(self.model(**fields))

        return insert_list
