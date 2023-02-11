# from ..migrate_from_magento import BaseMigrator
from .base_migrator import BaseMigrator
from django.contrib.auth.models import User, Group
from ...models import CustomerExtra, CustomerAddress


class CustomerMigrate(BaseMigrator):

    def get_data(self):

        sql = "SELECT ce.entity_id, ce.email, ce.group_id, ce.created_at, ce.updated_at, ce.is_active, " \
              "ce_dob.value AS dob, " \
              "ce_default_bill.value AS default_billing_address_id, " \
              "ce_default_ship.value AS default_shipping_address_id, " \
              "ce_gender.value AS gender_id, " \
              "ce_prefix.value AS prefix, " \
              "ce_fname.value AS first_name, " \
              "ce_mname.value AS middle_name, " \
              "ce_lname.value AS last_name, " \
              "ce_suffix.value AS suffix, " \
              "ce_ref.value AS referring_amico_id, " \
              "ce_authnet_prof.value AS authnetcim_profile_id " \
              "FROM stws_customer_entity ce " \
                \
              "LEFT JOIN stws_customer_entity_datetime ce_dob ON ce.entity_id=ce_dob.entity_id " \
              "AND ce_dob.attribute_id=11 " \
                \
              "LEFT JOIN stws_customer_entity_int ce_default_bill ON ce.entity_id=ce_default_bill.entity_id " \
              "AND ce_default_bill.attribute_id=13 " \
                \
              "LEFT JOIN stws_customer_entity_int ce_default_ship ON ce.entity_id=ce_default_ship.entity_id " \
              "AND ce_default_ship.attribute_id=14 " \
                \
              "LEFT JOIN stws_customer_entity_int ce_gender ON ce.entity_id=ce_gender.entity_id " \
              "AND ce_gender.attribute_id=18 " \
                \
              "LEFT JOIN stws_customer_entity_varchar ce_fname ON ce.entity_id=ce_fname.entity_id " \
              "AND ce_fname.attribute_id=5 " \
                \
              "LEFT JOIN stws_customer_entity_varchar ce_lname ON ce.entity_id=ce_lname.entity_id " \
              "AND ce_lname.attribute_id=7 " \
                \
              "LEFT JOIN stws_customer_entity_varchar ce_ref ON ce.entity_id=ce_ref.entity_id " \
              "AND ce_ref.attribute_id=181 " \
                \
              "LEFT JOIN stws_customer_entity_varchar ce_prefix ON ce.entity_id=ce_prefix.entity_id " \
              "AND ce_prefix.attribute_id=4 " \
                \
              "LEFT JOIN stws_customer_entity_varchar ce_mname ON ce.entity_id=ce_mname.entity_id " \
              "AND ce_mname.attribute_id=6 " \
                \
              "LEFT JOIN stws_customer_entity_varchar ce_suffix ON ce.entity_id=ce_suffix.entity_id " \
              "AND ce_suffix.attribute_id=8 " \
                \
              "LEFT JOIN stws_customer_entity_varchar ce_authnet_prof ON ce.entity_id=ce_authnet_prof.entity_id " \
              "AND ce_authnet_prof.attribute_id=190"

        customers = CustomerExtra.objects.using(super().magento_db()).raw(sql)
        customers.all()

        return customers

    def run_migrate(self):
        pass
