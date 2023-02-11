from django.dispatch import receiver
from .... import signals
from ....utils import CartUtils, OrderUtil


# Add the "Tax" for Order totals
@receiver(signals.order_totals, sender=CartUtils)
def add__tax__order_totals(*args, **kwargs):
    from ....models import Quote, TaxCalculation, TaxCalculationRate
    # from ..crons.order_tax import update__tax__order_tax

    totals = kwargs['totals']
    quote = kwargs['quote']
    inside_order = kwargs['inside_order']

    if isinstance(totals, dict) and isinstance(quote, Quote):
        tax_amount = calculate_tax(quote, inside_order=inside_order)

        if tax_amount:
            # pass
            totals['tax_amount'] = {
                'id': 'tax_amount',
                'title': 'Tax',
                'value': tax_amount,
                'order': 900,
            }

    return totals


# Add tax_amount in the Order
@receiver(signals.before_order_create, sender=OrderUtil)
def add__tax__order_details(*args, **kwargs):
    from ....models import Quote, OrderTax, OrderTaxItem

    order_data = kwargs['order_data']

    if isinstance(order_data, dict) and 'quote' in order_data and isinstance(order_data['quote'], Quote):
        tax_amount, applied_tax_rates = calculate_tax(order_data['quote'], return_tax_rates=True)

        if tax_amount:
            order_data['tax_amount'] = tax_amount

            if isinstance(applied_tax_rates, dict):
                for tr_id, tr in applied_tax_rates.items():
                    if isinstance(tr, dict) and 'tax_rate' in tr:
                        order_tax = OrderTax.objects.create(**{
                            'quote': order_data['quote'],
                            'code': tr['tax_rate'].code,
                            'title': tr['tax_rate'].code,
                            'percentage': float(tr['tax_rate'].percentage),
                            'amount': tr['total_amount'] if 'total_amount' in tr else None,
                        })

                        if 'products_tax' in tr:
                            for prod_id, tax_amount in tr['products_tax'].items():
                                order_tax_item = OrderTaxItem.objects.create(**{
                                    'order_tax': order_tax,
                                    'quote_item_id': prod_id,
                                    'tax_percent': float(tr['tax_rate'].percentage),
                                    'amount': tax_amount,
                                })

    return order_data


def calculate_tax(instance, return_tax_rates=False, inside_order=False):
    from ....models import Quote, Order, TaxCalculation, TaxCalculationRate

    tax_rates = None
    total_tax_percent = 0.00
    total_tax = 0.00
    applicable_tax_rates = {}

    if isinstance(instance, (Quote, Order)):
        if isinstance(instance, Quote):
            products = instance.quoteitem_set
        else:
            products = instance.orderitem_set

        shipping_address = instance.get_shipping_address()

        if not shipping_address or not shipping_address.state:
            if return_tax_rates:
                return [total_tax, applicable_tax_rates]
            return total_tax

        if products and products.exists():
            # tax_class = TaxClass.objects.filter(pk=2)
            tax_class_id = 2  # Taxable Goods
            tax_calculations_q = TaxCalculation.objects.filter(is_active=True, tax_class_id=tax_class_id)
            if tax_calculations_q.exists():
                tax_rates_q = TaxCalculationRate.objects.filter(pk__in=tax_calculations_q
                                                                .values_list('tax_calculation_rate_id', flat=True)
                                                                , region_code=shipping_address.state)
                if tax_rates_q.exists():
                    tax_rates = tax_rates_q.all()

            if not tax_rates:
                if return_tax_rates:
                    return [total_tax, applicable_tax_rates]
                return total_tax

            for tax_rate in tax_rates:
                if match_tax_zip_code_for_address(shipping_address, tax_rate.postcode if not tax_rate.zip_is_range
                                else tax_rate.zip_from, tax_rate.zip_to, tax_rate.zip_is_range):

                    if return_tax_rates:
                        if tax_rate.id not in applicable_tax_rates.keys():
                            applicable_tax_rates[str(tax_rate.id)] = {}
                            applicable_tax_rates[str(tax_rate.id)]['tax_rate'] = tax_rate
                            applicable_tax_rates[str(tax_rate.id)]['products_tax'] = {}
                            applicable_tax_rates[str(tax_rate.id)]['total_amount'] = 0.00

                    total_tax_percent += round(float(tax_rate.percentage), 2)

                    if total_tax_percent:
                        for qp in products.all():
                            if qp.product.tax_class == tax_class_id:

                                tax_amount = round(float((float(qp.quantity) * float(qp.price)) * (total_tax_percent/100)), 2)

                                if return_tax_rates:
                                    applicable_tax_rates[str(tax_rate.id)]['products_tax'][qp.id] = tax_amount
                                    applicable_tax_rates[str(tax_rate.id)]['total_amount'] += tax_amount

                                if inside_order:
                                    qp.tax_percent = total_tax_percent
                                    qp.tax_amount = tax_amount
                                    qp.row_total = float(qp.row_total) + tax_amount
                                    qp.save()

                                total_tax += tax_amount

    if return_tax_rates:
        return [total_tax, applicable_tax_rates]

    return total_tax


def match_tax_zip_code_for_address(address, zip_code, zip_code_last=None, is_zip_code_range=False):
    from ....models import QuoteAddress, OrderAddress

    if not isinstance(address, (QuoteAddress, OrderAddress)):
        return False

    if zip_code == '*':
        return True

    if not address.zip:
        return False

    if not is_zip_code_range and not zip_code:
        return False

    if not is_zip_code_range and zip_code:
        return zip_code == address.zip

    if is_zip_code_range and not (isinstance(address.zip, int) or address.zip.isnumeric()):
        return False
    else:
        address.zip = int(address.zip)

    if is_zip_code_range and not (isinstance(zip_code, int) or zip_code.isnumeric()):
        return False
    else:
        zip_code = int(zip_code)

    if is_zip_code_range and not (isinstance(zip_code_last, int) or zip_code_last.isnumeric()):
        return False
    else:
        zip_code_last = int(zip_code_last)

    if is_zip_code_range:
        return zip_code_last >= address.zip >= zip_code
