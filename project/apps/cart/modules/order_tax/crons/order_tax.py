# https://django-background-tasks.readthedocs.io/en/latest/

from datetime import timedelta

# from background_task import background
# from background_task.models import Task

from celery.task.schedules import crontab
from celery.decorators import periodic_task


# Update tax entries in our own models
# @background(queue='order')
@periodic_task(run_every=(crontab(minute='0')), ignore_result=True)     # Hourly
def update__tax__order_tax():
    from ....models import Quote, OrderItem, QuoteItem
    from ..models import OrderTax, OrderTaxItem

    # Update TAX Order
    order_tax_q = OrderTax.objects.raw("SELECT ot.*, q.order_id AS o_order_id FROM {} ot "
                                       "INNER JOIN {} q ON ot.quote_id=q.id "
                                       "WHERE ot.order_id IS NULL ".
                                       format(OrderTax._meta.db_table, Quote._meta.db_table))

    if len(order_tax_q) > 0:
        for ot in order_tax_q.iterator():
            ot.order_id = ot.o_order_id
            ot.save()

    # Update TAX Order Items
    order_tax_item_q = OrderTaxItem.objects.raw("SELECT oti.*, oi.id AS oi_order_item_id FROM {} oti "
                                                "INNER JOIN {} qi ON oti.quote_item_id=qi.id "
                                                "INNER JOIN {} oi ON oi.quote_item_id=qi.id "
                                                "WHERE oti.order_item_id IS NULL ".
                                                format(OrderTaxItem._meta.db_table, QuoteItem._meta.db_table,
                                                       OrderItem._meta.db_table))

    if len(order_tax_item_q) > 0:
        for oti in order_tax_item_q.iterator():
            oti.order_item_id = oti.oi_order_item_id
            oti.save()


# update__tax__order_tax(repeat=Task.HOURLY, repeat_until=None)
