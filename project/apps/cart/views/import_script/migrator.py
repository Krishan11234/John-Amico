from django.views.generic import ListView
from django.http import HttpResponse

from ...scripts import migrate_from_magento as magento_migrate


class ImportMagentoView(ListView):

    def get(self, request, import_type, migration_type='', *args, **kwargs):

        migration_types = {'i': 'INSERT', 'u': 'UPDATE'}

        execution_count = False
        migration_type = migration_types[migration_type if migration_type in migration_types.keys() else 'i']

        import_types = {
            'cus': 'CustomerMigrate',
            'cusa': 'CustomerAddressMigrate',

            'cat': 'CategoryMigrate',
            'tax': 'TaxClassMigrate',
            'prod': 'ProductMigrate',
            'prod_gp': 'ProductGroupPriceMigrate',
            'prod_qty': 'ProductStockMigrate',
            'prod_cat': 'ProductCategoryMigrate',
            'prod_rel': 'ProductRelatedMigrate',
            'prod_size': 'ProductSizeMigrate',
            'prod_size_val': 'ProductSizeValueMigrate',
            'prod_med': 'ProductMediaMigrate',

            'prod_rev': 'ProductReviewMigrate',
        }

        if import_type == 'a*':
            for itk in import_types:
                classobj = getattr(magento_migrate, import_types[itk])()
                classobj.set_migration_mode(migration_type)
                execution_count = classobj.run_migrate()

                if isinstance(execution_count, int):
                    return HttpResponse(
                        "Executed {} items as `{}` <br/>\n\r".format(execution_count, import_types[import_type]))

                else:
                    return HttpResponse("Some Error happened with: `{}`", import_types[itk])

        if import_type in import_types.keys():
            classobj = getattr(magento_migrate, import_types[import_type])()
            classobj.set_migration_mode(migration_type)
            execution_count = classobj.run_migrate()

        if isinstance(execution_count, int):
            return HttpResponse("Executed {} items as `{}` <br/>\n\r".format(execution_count, import_types[import_type]))

        return HttpResponse("Some Error happened")