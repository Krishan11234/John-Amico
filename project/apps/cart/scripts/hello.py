from .migrate_from_magento.customer_migrate import CustomerMigrate


def run():
    CustomerMigrate().get_customers()
