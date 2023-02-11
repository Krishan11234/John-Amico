from django.db import models
from django.db.models import Sum, Count, Q
from django.contrib.auth.models import User

from ..utils import static


class OrderPayment(models.Model):
    id = models.AutoField(primary_key=True)
    order = models.ForeignKey('Order', on_delete=models.CASCADE, db_index=True, blank=True, null=True,
                              related_name='order_payment')
    status = models.CharField(max_length=100, db_index=True, choices=static.PAYMENT_STATUS_CHOICES, default='pending')
    method = models.CharField(max_length=100, db_index=True, default='')

    card_owner = models.CharField(max_length=255, blank=True, null=True)
    card_type = models.CharField(max_length=20, blank=True, null=True, choices=static.CARD_TYPES)
    card_last4 = models.IntegerField(blank=True, null=True)
    card_exp_month = models.IntegerField(blank=True, null=True)
    card_exp_year = models.IntegerField(blank=True, null=True)

    created_at = models.DateTimeField(auto_now_add=True, blank=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)

    class Meta:
        verbose_name_plural = 'Order Payment'

        ordering = ['-created_at']

    def __str__(self):
        return 'Payment for Order #{}'.format(self.order.id)

    def get_active_parent_transaction(self):
        return self.paymenttransaction_set.filter(parent=None, is_closed=False).first()

    def get_active_or_last_parent_transaction(self):
        trans = self.paymenttransaction_set.filter(parent=None, is_closed=True).order_by('-created_at').first()
        if not trans:
            return self.get_active_parent_transaction()

        return trans

    def get_method_handler_object(self):
        from ..views import PaymentSubView
        method = PaymentSubView().get_payment_method(method_code=self.method)

        return method

    def get_method_handler(self):
        payment_model_handler = False

        method_handler = self.get_method_handler_object()
        if method_handler and isinstance(method_handler, dict):
            if 'obj' in method_handler and isinstance(method_handler['obj'], models.Model):
                payment_model = method_handler['obj']
                payment_model_handler = payment_model.get_handler_class()
                payment_model_handler.title = method_handler['title']
                # payment_model_handler.transaction_happens_in = payment_model.transaction_happens_in

                # @TODO: add Customer Profile ID
                # payment_model_handler.payment_profile_id =

        return payment_model_handler

    def get_method_title(self, handler=None):
        method = handler if handler else self.get_method_handler_object()
        return method.title if isinstance(method, object) and hasattr(method, 'title') else self.method

    def string_in_details(self, view_mode='front'):  # view_mode: admin, front
        from ..utils import BasePaymentHandler

        string_from_handler = ''

        method_handler = self.get_method_handler()
        if method_handler and isinstance(method_handler, BasePaymentHandler):
            pay_trans = self.get_active_or_last_parent_transaction()
            method_handler.set_inputs({
                'data': {
                    'card_owner': self.card_owner,
                    'card_type': self.card_type,
                    'card_last4': self.card_last4,
                    'card_exp_month': self.card_exp_month,
                    'card_exp_year': self.card_exp_year,
                    'transaction_id': pay_trans.transaction_id if pay_trans else '',
                }
            })
            string_from_handler = method_handler.get_transaction_string(view_mode)

        string = self.get_method_title(handler=method_handler) + "<br/><br/>"
        string += string_from_handler + "<br/>"

        return string

    def already_captured(self):
        return self.status in ['paid', 'captured']

    def can_capture(self):
        return self.status not in ['cancelled', 'paid', 'captured', 'partial_refunded', 'refunded', 'voided']

    def can_refund(self):
        return self.status not in ['cancelled', 'refunded', 'voided']

    def can_cancel(self):
        return self.status not in ['cancelled', 'refunded', 'voided']

    def handle_payment_transaction(self, transaction_handler_method, amount=None):
        if transaction_handler_method and transaction_handler_method in [
            'void', 'capture_prior_auth', 'refund', 'auth_capture',
        ]:
            from ..models import PaymentTransaction
            from ..utils import BasePaymentHandler

            try:
                for pt in self.paymenttransaction_set.filter(is_closed=False).all():
                    method_handler = self.get_method_handler()
                    if method_handler and isinstance(method_handler, BasePaymentHandler):
                        amount = amount if amount else pt.amount_effected
                        transaction_id, status_to_be, response, _, _ = getattr(method_handler,
                                                                               transaction_handler_method) \
                            (transaction_id=pt.transaction_id, amount=float(amount))
                        # if transaction_id and isinstance(transaction_id, int):
                        self.status = status_to_be
                        self.save()

                        pt.is_closed = True
                        pt.save()

                        parent = pt.parent if pt.parent else pt

                        PaymentTransaction.objects.create(**{
                            'order': self.order,
                            'payment': self,
                            'parent': parent,
                            'amount_effected': pt.amount_effected,
                            'transaction_id': parent.transaction_id,
                            'transaction_type': transaction_handler_method,
                            'transaction_status': status_to_be,
                        })

                        return True
            except Exception as e:
                return False

        return False

    def invoice(self, amount=None):
        from ..models import PaymentTransaction, AuthnetcimCardsTransactions, AuthnetcimCards

        if self.can_capture():
            method_handler = self.get_method_handler()

            pay_trans = self.get_active_parent_transaction()
            if method_handler and pay_trans.transaction_type in ['auth_only']:
                try:
                    if amount and (isinstance(amount, int) or isinstance(amount, float)) and (
                            amount < self.order.grand_total):
                        profiles = method_handler.get_payment_profiles_from_transaction(int(pay_trans.transaction_id))
                        if isinstance(profiles, list):
                            capture_current_trans, status_to_be, response, _, _ = method_handler.capture_prior_auth_transaction(
                                transaction_id=pay_trans.transaction_id, amount=round(amount, 2))

                            if capture_current_trans and isinstance(capture_current_trans, int):
                                pay_trans.is_closed = True
                                # pay_trans.amount_effected = round(amount, 2)
                                pay_trans.save()

                                parent = pay_trans.parent if pay_trans.parent else pay_trans
                                invoice_transaction = PaymentTransaction.objects.create(**{
                                    'order': self.order,
                                    'payment': self,
                                    'parent': parent,
                                    'amount_effected': round(amount, 2),
                                    'transaction_id': parent.transaction_id,
                                    'transaction_type': 'capture_prior_auth',
                                    'transaction_status': status_to_be,
                                })

                                self.order.total_paid = float(self.order.total_paid) + float(amount)
                                self.order.save()

                                remaining_amount = float(self.order.grand_total) - float(self.order.total_paid)
                                remaining_amount = 0.00 if remaining_amount < 0 else round(remaining_amount, 2)

                                if remaining_amount:
                                    method_handler.customer_profile_id, method_handler.payment_profile_id = profiles
                                    new_auth_transaction_id, status_to_be, response, _, _ = method_handler. \
                                        auth_only_transaction(remaining_amount)

                                    if new_auth_transaction_id and isinstance(new_auth_transaction_id, int):
                                        pay_trans_new = PaymentTransaction.objects.create(**{
                                            'order': self.order,
                                            'payment': self,
                                            'parent': None,
                                            'amount_effected': remaining_amount,
                                            'transaction_id': new_auth_transaction_id,
                                            'transaction_type': 'auth_only',
                                            'transaction_status': status_to_be,
                                        })

                                        method_handler.save_card_transaction(pay_trans_new, profiles[1], profiles[0])

                                    self.status = 'partially_paid'
                                    self.save()
                                else:
                                    self.status = 'paid'
                                    self.save()

                                return invoice_transaction
                            else:
                                return False
                        else:
                            return "Payment profile required to invoice a different amount than original"
                    else:
                        transaction_id, status_to_be, response, _, _ = method_handler.capture_prior_auth_transaction(
                            transaction_id=pay_trans.transaction_id, amount=pay_trans.amount_effected)

                        if transaction_id and isinstance(transaction_id, int):
                            pay_trans.is_closed = True
                            pay_trans.save()

                            parent = pay_trans.parent if pay_trans.parent else pay_trans
                            invoice_transaction = PaymentTransaction.objects.create(**{
                                'order': self.order,
                                'payment': self,
                                'parent': parent,
                                'amount_effected': pay_trans.amount_effected,
                                'transaction_id': parent.transaction_id,
                                'transaction_type': 'capture_prior_auth',
                                'transaction_status': status_to_be,
                            })

                            self.status = 'captured'
                            self.save()

                            self.order.total_paid = float(0 if not self.order.total_paid else self.order.total_paid) + \
                                                    float(pay_trans.amount_effected)
                            self.order.save()

                            return invoice_transaction
                        else:
                            return False
                except Exception as e:
                    return False

        return self.already_captured()

    def refund(self, amount):
        from ..models import PaymentTransaction

        if self.can_refund():
            method_handler = self.get_method_handler()

            pay_trans = self.get_active_parent_transaction()
            if method_handler and pay_trans.transaction_type in ['auth_only']:
                try:
                    if amount and (isinstance(amount, int) or isinstance(amount, float)) and amount > 0:
                        profiles = method_handler.get_payment_profiles_from_transaction(pay_trans.transaction_id)
                        if isinstance(profiles, list):
                            refund_transaction_id, status_to_be, response, _, _ = method_handler.refund_transaction(
                                transaction_id=pay_trans.transaction_id, amount=amount)
                            if refund_transaction_id and isinstance(refund_transaction_id, int):
                                pay_trans.is_closed = True
                                pay_trans.save()

                                parent = pay_trans.parent if pay_trans.parent else pay_trans
                                PaymentTransaction.objects.create(**{
                                    'order': self.order,
                                    'payment': self,
                                    'parent': parent,
                                    'amount_effected': amount,
                                    'transaction_id': parent.transaction_id,
                                    'transaction_type': 'void',
                                    'transaction_status': status_to_be,
                                })

                                self.status = 'partial_refunded' if amount is not self.order.total_paid else 'refunded'
                                self.save()

                                if method_handler.transaction_happens_in in ['online', 'offline']:
                                    if method_handler.transaction_happens_in == 'online':
                                        self.order.total_online_refunded = float(
                                            self.order.total_online_refunded) + float(amount)
                                    if method_handler.transaction_happens_in == 'offline':
                                        self.order.total_offline_refunded = float(
                                            self.order.total_offline_refunded) + float(amount)

                                    self.order.save()
                            else:
                                return False
                        else:
                            return "Payment profile not found"
                    else:
                        return "Amount not provided"

                    return True
                except Exception as e:
                    return False

        return False

    def refund_direct(self, payment_transaction, amount):
        from ..models import PaymentTransaction

        if self.can_refund():
            method_handler = self.get_method_handler()
            if method_handler and (payment_transaction and isinstance(payment_transaction, PaymentTransaction)):
                if amount and (isinstance(amount, int) or isinstance(amount, float)) and amount > 0:
                    # if True:
                    try:
                        already_refunded = float(
                            payment_transaction.amount_refunded) if payment_transaction.amount_refunded else 0.00
                        maximum_refundable_amount = float(payment_transaction.amount_effected) - already_refunded

                        if maximum_refundable_amount < amount:
                            return "Maximum {} can be refunded for TransactionID: {}".format(maximum_refundable_amount,
                                                                                             payment_transaction.transaction_id)

                        profiles = method_handler.get_payment_profiles_from_transaction(
                            int(payment_transaction.transaction_id))
                        if isinstance(profiles, list):
                            refund_transaction_id, status_to_be, response, _, _ = method_handler.refund_transaction(
                                transaction_id=int(payment_transaction.transaction_id), amount=amount)
                            if refund_transaction_id and isinstance(refund_transaction_id, int):
                                if maximum_refundable_amount == amount:
                                    payment_transaction.is_closed = True

                                payment_transaction.amount_refunded = already_refunded + amount
                                payment_transaction.save()

                                parent = payment_transaction.parent if payment_transaction.parent else payment_transaction
                                PaymentTransaction.objects.create(**{
                                    'order': self.order,
                                    'payment': self,
                                    'parent': parent,
                                    'amount_effected': amount,
                                    'transaction_id': refund_transaction_id,
                                    'transaction_type': 'refund',
                                    'is_closed': True,
                                    'transaction_status': status_to_be,
                                })

                                self.status = 'partial_refunded' if amount is not self.order.total_paid else 'refunded'
                                self.save()

                                if method_handler.transaction_happens_in in ['online', 'offline']:
                                    if method_handler.transaction_happens_in == 'online':
                                        self.order.total_online_refunded = float(
                                            self.order.total_online_refunded) + float(amount)
                                    else:
                                        self.order.total_offline_refunded = float(
                                            self.order.total_offline_refunded) + float(amount)
                                else:
                                    self.order.total_offline_refunded = float(
                                        self.order.total_offline_refunded) + float(amount)

                                if float(self.order.total_online_refunded) + float(self.order.total_offline_refunded) \
                                        == float(self.order.total_paid):
                                    self.order.status = 'refunded'
                                elif float(self.order.total_online_refunded) + float(self.order.total_offline_refunded) == 0:
                                    self.order.status = self.order.status
                                elif float(self.order.total_online_refunded) + float(self.order.total_offline_refunded) \
                                        < float(self.order.total_paid):
                                    self.order.status = 'partially_refunded'

                                self.order.save()

                                return True
                            else:
                                return False
                        else:
                            return "Payment profile not found"
                    except Exception as e:
                        return str(e)
                else:
                    return "Amount not provided"

        return False

    def cancel(self):
        if self.can_cancel():
            return self.handle_payment_transaction('void_transaction')
        return False

    def save(self, force_insert=False, force_update=False, using=None, update_fields=None, external_use=False):
        super().save(force_insert, force_update, using, update_fields)

    def delete(self, using=None, keep_parents=False):

        super().delete(using=None, keep_parents=False)
