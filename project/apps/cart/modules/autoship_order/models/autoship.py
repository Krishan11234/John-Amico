from django.db import models
from django.utils import timezone
from ....utils import helper
from ..utils import static


class AutoshipRequest(models.Model):
    id = models.AutoField(primary_key=True)
    protect_code = models.CharField(max_length=50, blank=True, null=False)
    parent_order = models.ForeignKey('Order', on_delete=models.CASCADE, db_index=True, blank=True, null=False)
    professional_member = models.ForeignKey('MemberExtra', on_delete=models.CASCADE, blank=True, null=True)
    customer = models.ForeignKey('CustomerExtra', on_delete=models.CASCADE, blank=True, null=True)
    order_confirmaiton_prior_time = models.IntegerField(blank=True, null=False, help_text="Number in Minutes. This is the Prior time to send the Order Confirmation Email")
    interval_period_number = models.IntegerField(blank=True, null=False, help_text="Number of Days/Months/Years")
    interval_period_type = models.CharField(max_length=11, blank=True, null=False, default='month', choices=static.AUTOSHIP_REQUEST_TIME_TYPES,
                                            help_text="Time interval type in Day/Month/Year (Singular Format)")
    status = models.SmallIntegerField(blank=True, null=False, default='1', choices=static.AUTOSHIP_REQUEST_STATUES)
    cancelled_by = models.SmallIntegerField(blank=True, null=False, default='0', choices=static.AUTOSHIP_REQUEST_CANCELLED_BY)

    magento_id = models.IntegerField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    def __str__(self):
        return 'Autoship Request: Order#{} for every {} {}'.format(self.parent_order.id, self.interval_period_number,
                                                                   self.interval_period_type)

    def add_attempt_to_autoship_request(self):
        if self.status in [1]:

            # We will add a new attempt only if there is already no pending attempt
            if AutoshipRequestAttempt.objects.filter(status__in=[0]).count() < 1:
                next_order_placing_time = self.get_next_order_placing_time()
                confirming_email_sending_time = self.get_order_confirming_email_sending_time(next_order_placing_time)

                return AutoshipRequestAttempt.objects.create(**{
                    'autoship': self,
                    'next_order_placing_time': next_order_placing_time,
                    'confirming_email_sending_time': confirming_email_sending_time,
                    'status': 0,
                })

    def get_next_order_placing_time(self):
        import datetime
        from dateutil.relativedelta import relativedelta

        time = False

        attempts_count = self.get_attempts().count()
        if attempts_count == 1:
            attempts_count += 1
        elif attempts_count <= 0:
            attempts_count = 1

        number = self.interval_period_number * attempts_count
        order_timestamp = self.parent_order.created_at

        print(order_timestamp)
        print(number, self.interval_period_type)

        if self.interval_period_type == 'day':
            time = order_timestamp + datetime.timedelta(days=number)

        elif self.interval_period_type == 'month':
            time = order_timestamp + relativedelta(months=+number)

        elif self.interval_period_type == 'year':
            time = order_timestamp + relativedelta(years=+number)

        print(time)

        return time

    def get_order_confirming_email_sending_time(self, order_placing_time):
        import datetime
        return order_placing_time - datetime.timedelta(minutes=self.order_confirmaiton_prior_time)

    def get_attempts(self):
        return self.autoshiprequestattempt_set.all()

    def get_autoship_request_full(self, filter=False, exclude_disabled=False):
        if self.status not in [1]:
            return {}

        data = {
            'autoship_request': self,
            'customer_email': self.parent_order.customer_email,
            'customer_fullname': self.parent_order.get_customer_name(),
            'parent_order': self.parent_order,
            'order_increment_id': self.parent_order.get_order_id(),
            'orderable_products': self.get_products(exclude_disabled=exclude_disabled),
            'orderable_product_ids': self.get_products_ids(exclude_disabled=exclude_disabled),
            'last_attempt': self.get_last_autoship_request_attempt(),
            'loaded_full': True,
        }

        return data

    def get_products(self, exclude_disabled=False):
        arps = {}
        statuses = [1, 2] if not exclude_disabled else [1]
        arp_q = self.autoshiprequestitem_set.filter(status__in=statuses)
        if arp_q.exists():
            for arp in arp_q.all():
                arps[arp.product_id] = arp

        return arps

    def get_products_ids(self, exclude_disabled=False):
        arps = self.get_products(exclude_disabled=exclude_disabled)
        arp_ids = ', '.join(str(x) for x in list(arps.keys())) if arps else ''

        return arp_ids

    def get_last_autoship_request_attempt(self):
        ara_q = self.autoshiprequestattempt_set.filter(status__in=[0,1])
        if ara_q.exists():
            return ara_q.order_by('-autoship__autoshiprequestattempt__id')[:1].first()
        return {}

    def save(self, force_insert=False, force_update=False, using=None, update_fields=None):
        self.protect_code = self.protect_code if self.protect_code else helper.get_unique_string()[2:11]

        return super().save(force_insert, force_update, using, update_fields)


class AutoshipRequestItem(models.Model):
    id = models.AutoField(primary_key=True)
    autoship = models.ForeignKey('AutoshipRequest', on_delete=models.CASCADE, db_index=True, blank=True, null=False)
    parent_order_item = models.ForeignKey('OrderItem', on_delete=models.CASCADE, db_index=True, null=False,)
    product = models.ForeignKey('Product', on_delete=models.CASCADE, db_index=True, null=True,)
    quantity = models.DecimalField(max_digits=15, decimal_places=2, blank=True, default=1, db_index=True)
    status = models.SmallIntegerField(blank=True, null=False, default='1',
                                      choices=static.AUTOSHIP_REQUEST_PRODUCT_STATUES)

    magento_id = models.IntegerField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    def __str__(self):
        return 'Item {} for Autoship #{}'.format(self.id, self.autoship_id)


# class AutoshipRequestProcessedOrders(models.Model):
#     id = models.AutoField(primary_key=True)
#     autoship = models.ForeignKey('AutoshipRequest', on_delete=models.CASCADE, db_index=True, blank=True, null=False)
#     autoship_attempt = models.ForeignKey('AutoshipRequestAttempt', on_delete=models.CASCADE, db_index=True, blank=True, null=False)
#     status = models.SmallIntegerField(blank=True, null=False, default='1',
#                                       choices=static.AUTOSHIP_REQUEST_PROCESSED_ORDERS_STATUES)
#     order = models.ForeignKey('Order', on_delete=models.SET_NULL, db_index=True, blank=True, null=True, help_text="ID of the Order that was placed against the Autoship Request. If the ID is 0(zero) or NULL, that interval_order was skipped.")
#     comment = models.TextField(blank=True, null=True, help_text="The reason behind skipping this attempt. Ex: Autoship Request was disabled / Items were not available / Could not add {ITEM} because of \"Out Of Stock\"")
#
#     magento_id = models.IntegerField(blank=True, null=True)
#     created_at = models.DateTimeField(auto_now_add=True, blank=True)
#     updated_at = models.DateTimeField(auto_now=True, blank=True)
#
#     def __str__(self):
#         return 'Order #{} from Autoship #{}'.format(self.order_id, self.autoship_id)


class AutoshipRequestAttempt(models.Model):
    id = models.AutoField(primary_key=True)
    autoship = models.ForeignKey('AutoshipRequest', on_delete=models.CASCADE, db_index=True, blank=True, null=False)
    protect_code = models.CharField(max_length=50, blank=True, null=False)
    email_sent = models.BooleanField(blank=True, null=False, default=False)
    confirming_email_sending_time = models.DateTimeField(blank=True)
    next_order_placing_time = models.DateTimeField(blank=True)
    status = models.SmallIntegerField(blank=True, null=False, default='0', choices=static.AUTOSHIP_REQUEST_ATTEMPT_STATUES)
    autoship_processed_order_id = models.IntegerField(blank=True, null=True, help_text="Order ID for the successful attempt")
    cancelled_by = models.SmallIntegerField(blank=True, null=False, default='0',
                                            choices=static.AUTOSHIP_REQUEST_CANCELLED_BY)
    cancelled_on = models.DateTimeField(blank=True, null=True)

    order = models.ForeignKey('Order', on_delete=models.SET_NULL, db_index=True, blank=True, null=True,
                              help_text="ID of the Order that was placed against the Autoship Request. If the ID is 0(zero) or NULL, that interval_order was skipped.")
    comment = models.TextField(blank=True, null=True,
                               help_text="The reason behind skipping this attempt. Ex: Autoship Request was disabled / Items were not available / Could not add {ITEM} because of \"Out Of Stock\"")

    magento_id = models.IntegerField(blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    def __str__(self):
        return 'Attempt #{} for Autoship #{}'.format(self.id, self.autoship_id)

    def save(self, force_insert=False, force_update=False, using=None, update_fields=None):
        self.protect_code = self.protect_code if self.protect_code else helper.get_unique_string()[2:11]

        return super().save(force_insert, force_update, using, update_fields)
