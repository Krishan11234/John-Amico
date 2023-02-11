from django.dispatch import receiver
from .... import signals
from ....utils import CartUtils, OrderUtil
from ..utils import static


# Add tax_amount in the Order
@receiver(signals.after_successful_order_create, sender=OrderUtil)
def save_autoship_consumer_request(*args, **kwargs):
    from ....models import AutoshipRequest, AutoshipRequestItem, Order, OrderItem, TblMember

    order = kwargs['order']
    request = kwargs['request']

    enabled = False
    time = False

    if isinstance(order, Order):
        autoship_exists = validate_autoship_request_by_order_id(order.id)
        if autoship_exists:
            return order

        if isinstance(request, object) and hasattr(request, 'POST') and request.POST:
            if 'billing[jaautoship_subscriber_checkbox]' in request.POST \
                    and request.POST.get('billing[jaautoship_subscriber_checkbox]'):

                enabled = True
                time = str(request.POST.get('billing[jaautoship_subscribe_time]', ''))

        if enabled and time:
            number, time_type = '_'.split(time)
            if time_type in list(dict(static.AUTOSHIP_REQUEST_TIME_TYPES).keys()):
                prior_time = dict(static.AUTOSHIP_REQUEST_TIME_TYPES_PRIOR_TIME)[time_type]
                professional_member = None

                if order.professional_id:
                    professional_member_q = TblMember.objects.filter(amico_id=order.professional_id)
                    if professional_member_q.exists():
                        professional_member = professional_member_q.get().get_member_extra()

                data = {
                    'parent_order': order,
                    'professional_member': professional_member,
                    'customer': order.customer,
                    'order_confirmaiton_prior_time': prior_time,
                    'interval_period_number': int(number),
                    'interval_period_type': time_type,
                }
                autoship_request = AutoshipRequest.objects.create(**data)

                if autoship_request:
                    for oi in order.orderitem_set.all():
                        AutoshipRequestItem.objects.create(**{
                            'autoship': autoship_request,
                            'parent_order_item': oi,
                            'product': oi.product,
                            'quantity': oi.quantity,
                        })

                    # We are running CRON to add attempts to Autoship Requests
                    # autoship_request.add_attempt_to_autoship_request()

                    # @TODO: Send email to Customer and Admin about this Autoship Request

    return order


def validate_autoship_request_by_order_id(parent_order_id):
    from ..models import AutoshipRequest

    if parent_order_id and isinstance(parent_order_id, int):
        auto_q = AutoshipRequest.objects.filter(parent_order_id=parent_order_id)

        if auto_q.exists():
            return auto_q.first()

    return False
