import traceback


class BaseMigrator(object):

    DB_TO_USE = 'magento'
    model = None
    migration_mode = 'INSERT'    # INSERT, UPDATE
    update_fields = []

    def magento_db(self):
        return self.DB_TO_USE

    def set_migration_mode(self, mode='INSERT'):
        if mode in ['INSERT', 'UPDATE']:
            self.migration_mode = mode

    def get_data(self):
        return []

    def run_migrate(self, data_list=[], pre_callback_method=None, post_callback_method=None, single_save=False):
        data_list_of_objects = data_list if data_list else self.get_data()

        if self.model and isinstance(data_list_of_objects, list) and data_list_of_objects:
            try:
                if pre_callback_method:
                    getattr(self, pre_callback_method)()

                if single_save:
                    for data_obj in data_list_of_objects:
                        data_obj.save()
                else:
                    if self.migration_mode == 'INSERT':
                        self.model.objects.bulk_create(data_list_of_objects)
                    elif self.migration_mode == 'UPDATE':
                        if isinstance(self.update_fields, list) and self.update_fields:
                            self.model.objects.bulk_update(data_list_of_objects, self.update_fields)

                if post_callback_method:
                    getattr(self, post_callback_method)()

                return len(data_list_of_objects)
            except Exception as e:
                print(e)
                traceback.print_exc()
                return False
        else:
            return 0


if __name__ == '__main__':
    BaseMigrator()
