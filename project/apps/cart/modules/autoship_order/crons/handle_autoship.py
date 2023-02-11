import datetime
from datetime import timedelta

from celery.task.schedules import crontab
from celery.decorators import periodic_task


@periodic_task(run_every=(crontab(minute='2')), ignore_result=True)  # Hourly at every 2nd Minute
def handle_auto_ship_requests():
    handle_sending_order_confirming_emails()
    handle_order_placements()


@periodic_task(run_every=(crontab(minute='0')), ignore_result=True)  # Every Hour
def handle_adding_auto_ship_request_attempts():
    from ..models import AutoshipRequest, AutoshipRequestAttempt

    for request in AutoshipRequest.objects.filter(status__in=[1]).all():
        request.add_attempt_to_autoship_request()


def handle_sending_order_confirming_emails():
    from ..models import AutoshipRequest, AutoshipRequestAttempt

    time = datetime.datetime.now()
    prior_time = time - timedelta(hours=6)
    # $now = date('Y-m-d H:i:s', $time);

    attempts_q = AutoshipRequestAttempt.objects.filter(status__in=[0], autoship__status__in=[1],
                                                       confirming_email_sending_time__gte=time,
                                                       confirming_email_sending_time__lte=prior_time, email_sent=False)
    if not attempts_q.exists():
        for attempt in attempts_q.all():
            ar_full = attempt.autoship.get_autoship_request_full()

            # @TODO: Send Autoship Confirming Email to Customer
            # email_sent = send_order_confirming_email(ar_full)
            email_sent = False
            if email_sent:
                attempt.email_sent = True
                attempt.save()


def handle_order_placements():
    from ..models import AutoshipRequest, AutoshipRequestAttempt
    from ....utils.order import OrderUtil
    import json

    time = datetime.datetime.now()
    prior_time = time - timedelta(hours=6)
    # $now = date('Y-m-d H:i:s', $time);

    failed_halted_autoships = []

    attempts_q = AutoshipRequestAttempt.objects.filter(status__in=[1], autoship__status__in=[1],
                                                       confirming_email_sending_time__gte=time,
                                                       confirming_email_sending_time__lte=prior_time, email_sent=False)
    if attempts_q.exists():
        order_util = OrderUtil()

        for attempt in attempts_q.all():
            if attempt.autoship_id in failed_halted_autoships:
                continue

            ar_full = attempt.autoship.get_autoship_request_full(exclude_disabled=True)
            system_failure = None

            if ar_full:
                new_order_data = order_util.copy_from_order(ar_full['parent_order'], {'quantities': ar_full['orderable_products']})
                if isinstance(new_order_data, list):
                    system_failure = json.dumps(new_order_data)

                elif isinstance(new_order_data, dict):
                    try:
                        order_id = order_util.process_order(new_order_data, False)

                        if isinstance(order_id, str):
                            system_failure = order_id
                        elif isinstance(order_id, int):
                            attempt.status = 3      # Finished
                            attempt.order_id = order_id
                            attempt.save()

                    except Exception as e:
                        system_failure = str(e)

                if system_failure:
                    attempt.status = 4  # Failed
                    attempt.comment = system_failure
                    attempt.cancelled_by = 3  # System
                    attempt.cancelled_on = datetime.datetime.now()

                    attempt.save()

                    # @TODO: Send Autoship Order Placement Failed Email to Customer.
                    # @TODO: Send Autoship Order Placement Failed Email to Admin.
                    # We are going to tell Customer that, the Aotuship Request is been halted, until Admin checks
                    # the issues

                    attempt.autoship.status = 3  # Halted
                    attempt.autoship.save()

                    if attempt.autoship_id not in failed_halted_autoships:
                        failed_halted_autoships.append(attempt.autoship_id)
