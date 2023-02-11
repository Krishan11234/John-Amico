import csv
from copy import deepcopy

from django.db.models.query import QuerySet
from django.http import StreamingHttpResponse


class Echo:
    """An object that implements just the write method of the file-like
    interface.
    """
    def write(self, value):
        """Write the value by returning it, instead of storing in a buffer."""
        return value


def make_and_force_csv(request, csv_rows, csv_name='CSV_Report.csv'):
    """A view that streams a large CSV file."""
    # Generate a sequence of rows. The range is based on the maximum number of
    # rows that can be handled by a single sheet in most spreadsheet
    # applications.
    # rows = (["Row {}".format(idx), str(idx)] for idx in range(65536))
    if csv_rows and isinstance(csv_rows, list):
        pseudo_buffer = Echo()
        writer = csv.writer(pseudo_buffer)
        response = StreamingHttpResponse((writer.writerow(row) for row in csv_rows), content_type="text/csv")
        response['Content-Disposition'] = 'attachment; filename="'+csv_name+'"'

        return response

    return False


def get_custoemr_group_name(group_id):
    group_names = {
        0: 'Price Level A',
        1: 'General Customer',
        2: 'Wholesale Customer',
        3: 'Retailer',
        4: 'Ambassador Pro',
        5: 'Price Level B',
        6: 'Elite Consumer',
        7: 'Ambassador',
    }
    return group_names[group_id] if group_id in group_names.keys() else ''


def convert_product_to_csv_rows(products):
    csv_rows = []

    if products and isinstance(products, QuerySet) and products.exists():
        csv_rows.append(['Product ID', 'Name', 'Status', 'SKU', 'Size'])

        for group_id in [0, 5, 7, 4]:
            csv_rows[0].append(get_custoemr_group_name(group_id))

        i = 1
        for product in products:
            regular_price = float(product.price)

            prod_dict = {
                'product_id': product.pk,
                'product_name': product.name,
                'product_status': 'Enabled' if product.is_active else 'Disabled',
                'product_sku': product.sku,
                'product_size': 'Default',
                'product_price_0': regular_price,
            }

            for group_id in [5, 7, 4]:
                group_price = product.get_customer_group_price(group_id)

                if group_id == 7:
                    group_price = round(regular_price * 0.65, 2)

                prod_dict['product_price_' + str(group_id)] = str(float(group_price)) if group_price else str(regular_price)

            sizes = product.get_sizes()
            if sizes:
                j = 0
                for size in sizes:
                    sku = prod_dict['product_sku'] + "-" + size.sku if size.sku else prod_dict['product_sku']
                    size_dict = deepcopy(prod_dict)
                    anonymous_price = size.get_anonymous_value()

                    if j > 0:
                        size_dict['product_id'] = ''
                        size_dict['product_name'] = ''
                        size_dict['product_status'] = ''

                    size_dict['product_sku'] = sku
                    size_dict['product_size'] = size.title
                    size_dict['product_price_0'] = round(float(anonymous_price.get_price()), 2) if anonymous_price else 0

                    for group_id in [5, 7, 4]:
                        size_group_price = size.get_customer_group_price(group_id)
                        if size_group_price:
                            size_group_price = size_group_price.get_price()

                            if group_id == 7:
                                if size_dict['product_price_0']:
                                    size_group_price = round(size_dict['product_price_0'] * 0.65, 2)

                            size_dict['product_price_' + str(group_id)] = str(float(size_group_price)) if size_group_price \
                                else str(float(size_dict['product_price_0']))

                    csv_rows.append(list(size_dict.values()))

                    j += 1
            else:
                csv_rows.append(list(prod_dict.values()))

            i += 1

    return csv_rows


def convert_order_to_csv_rows(orders, export_type='member'):
    from ....models import Order

    csv_rows = []
    export_types = ['consumer', 'member', 'consumer_tax_illinois', 'consumer_tax_all_except_illinois']

    if export_type not in export_types:
        return csv_rows

    if orders and isinstance(orders, QuerySet):
        if export_type in ['member', 'customer']:
            csv_rows.append(['ID' if export_type == 'member' else 'CustomerEmail', 'InvoiceNo', 'InvoiceDate',
                             'ID_1', 'Qty', 'ExtendedPrice'])

            if export_type == 'customer':
                csv_rows[0].append('Item\'s Total Tax')
                csv_rows[0].append('Order Shipping Price')

        else:
            # Header
            dict_for_list = {
                'header': '/sohdr',
                'amico_id': 'W11111112' if export_type == 'consumer_tax_illinois' else 'W11111111',
                'sku': '0',
                'D': '',
                'E': '',
                'F': '',
                'qty_invoiced': '',
                'H': '',
                'I': '',
                'J': '',
                'K': '',
                'L': '',
                'M': '',
                'N': 'Y',
                'O': '14',
            }
            csv_rows.append(list(dict_for_list.values()))

            all_order_shipping_total = 0.00
            all_order_tax_total = 0.00
            all_order_subtotal = 0.00
            all_order_qty = 0.00

        for order in orders:
            if isinstance(order, Order):
                for order_item in order.orderitem_set.all():
                    if order_item.product_type not in ['simple']:
                        continue

                    if export_type in ['member', 'customer']:
                        list_to_append = [
                            order.professional_id if export_type == 'member' else order.customer_email,
                            order.increment_id,
                            order.created_at,
                            order_item.sku,
                            order_item.qty,
                            order_item.row_total
                        ]

                        if export_type == 'customer':
                            list_to_append.append(order_item.get('tax_amount', None))
                            list_to_append.append(order.shipping_amount)
                    else:
                        # Line Item
                        dict_for_list = {
                            'header': '/soli',
                            'amico_id': 'P',
                            'sku': order_item.sku,
                            'D': '',
                            'E': '',
                            'F': '',
                            'qty_invoiced': int(order_item.qty_invoiced),
                            'H': '',
                            'I': '',
                            'J': '',
                            'K': '1',
                            'L': '',
                            'M': '',
                            'N': '',
                            'O': '',
                        }
                        list_to_append = list(dict_for_list.values())

                    csv_rows.append(list_to_append)

                all_order_tax_total += float(getattr(order, 'tax_invoiced', 0.00))
                all_order_shipping_total += float(order.shipping_amount)
                all_order_subtotal += float(order.subtotal_invoiced) + all_order_tax_total
                all_order_subtotal -= float(getattr(order, 'discount_invoiced', 0.00))

                all_order_qty += int(order.total_qty_invoiced)

        if export_type not in ['member', 'consumer']:
            # Subtotal Row
            dict_for_list = {
                'header': '/soli',
                'amico_id': 'N',
                'sku': 'ILLINOIS WEBCUSTOMER' if export_type == 'consumer_tax_illinois' else 'WEBCUSTOMER',
                'D': '',
                'E': '',
                'F': '1',
                'qty_invoiced': all_order_qty if all_order_qty else 0.00,
                'H': '',
                'I': all_order_subtotal if all_order_subtotal else 0.00,
                'J': '1',
                'K': 'Y' if export_type == 'consumer_tax_illinois' else '',
                'L': '',
                'M': '',
                'N': '',
                'O': '',
            }

            csv_rows.append(list(dict_for_list.values()))

            # Shipping & TAX Row
            dict_for_list = {
                'header': '/sosum',
                'amico_id': 'COOK' if export_type == 'consumer_tax_illinois' else '',
                'sku': '',
                'D': '',
                'E': round(all_order_tax_total, 2) if export_type == 'consumer_tax_illinois' else '',
                'F': '',
                'qty_invoiced': '',
                'H': '',
                'I': '',
                'J': round(all_order_shipping_total, 2) if all_order_shipping_total else '',
                'K': '1',
                'L': '',
                'M': '',
                'N': '',
                'O': '',
            }
            csv_rows.append(list(dict_for_list.values()))

    return csv_rows
