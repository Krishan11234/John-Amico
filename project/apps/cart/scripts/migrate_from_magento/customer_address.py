from django.db import connections
from .base_migrator import BaseMigrator
from django.contrib.auth.models import User, Group
from ...models import CustomerExtra, CustomerAddress

from ...utils.helper import dictfetchall


class CustomerAddressMigrate(BaseMigrator):

    def get_data(self):

        sql = "SELECT ca.entity_id, ca.parent_id AS customer_id, ca.created_at, ca.updated_at, ca.is_active, " \
              "state.code AS address_state, " \
              "state.country_id AS address_country, " \
              "ca_street.value AS address_street, " \
              "ca_fname.value AS first_name, " \
              "ca_lname.value AS last_name, " \
              "ca_city.value AS address_city, " \
              "ca_zip.value AS address_postcode, " \
              "ca_phone.value AS address_telephone, " \
              "ca_company.value AS address_company " \
              "FROM stws_customer_address_entity ca " \
                \
              "LEFT JOIN stws_customer_address_entity_int ca_state_id ON ca.entity_id=ca_state_id.entity_id " \
              "AND ca_state_id.attribute_id=29  LEFT JOIN stws_directory_country_region state " \
              "ON ca_state_id.value=state.region_id " \
                \
              "LEFT JOIN stws_customer_address_entity_text ca_street ON ca.entity_id=ca_street.entity_id " \
              "AND ca_street.attribute_id=25 " \
                \
              "LEFT JOIN stws_customer_address_entity_varchar ca_fname ON ca.entity_id=ca_fname.entity_id " \
              "AND ca_fname.attribute_id=20 " \
                \
              "LEFT JOIN stws_customer_address_entity_varchar ca_lname ON ca.entity_id=ca_lname.entity_id " \
              "AND ca_lname.attribute_id=22 " \
                \
              "LEFT JOIN stws_customer_address_entity_varchar ca_city ON ca.entity_id=ca_city.entity_id " \
              "AND ca_city.attribute_id=26 " \
                \
              "LEFT JOIN stws_customer_address_entity_varchar ca_zip ON ca.entity_id=ca_zip.entity_id " \
              "AND ca_zip.attribute_id=30 " \
                \
              "LEFT JOIN stws_customer_address_entity_varchar ca_phone ON ca.entity_id=ca_phone.entity_id " \
              "AND ca_phone.attribute_id=31 " \
                \
              "LEFT JOIN stws_customer_address_entity_varchar ca_company ON ca.entity_id=ca_company.entity_id " \
              "AND ca_company.attribute_id=24 "

        with connections[super().magento_db()].cursor() as cursor:
            cursor.execute(sql)
            for address in dictfetchall(cursor):
                if address:
                    # CustomerAddress
                    pass
                pass

    def run_migrate(self):
        pass
