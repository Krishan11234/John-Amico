import logging
# import authorize
from authorizenet import apicontractsv1
from authorizenet.apicontrollers import createTransactionController, createCustomerProfileController, \
    createCustomerPaymentProfileController, createCustomerProfileFromTransactionController, getTransactionDetailsController
from decimal import *
from django.contrib.auth.models import User
from django.forms.models import model_to_dict
from django.utils.html import format_html
from .base_payment_handler import BasePaymentHandler
from django.shortcuts import reverse

from ...utils import static

logger = logging.getLogger(__name__)


class AuthnetCIM(BasePaymentHandler):
    transaction_happens_in = 'online'

    method_code = ''
    api_merchant_auth = None
    api_payment_obj = None
    api_payment_profile_obj = None
    api_settings_obj = None
    api_billing_address = None
    api_shipping_address = None

    inputs = {}
    payment_profile_id = False
    customer_profile_id = False
    sandbox = False
    save_card_info = False
    payment_mode = None
    validation_mode = None
    configurations = {}
    credentials = {
        'login_id': '',
        'transaction_key': '',
    }
    user = None
    address_fields = ['firstName', 'lastName', 'address', 'city', 'state', 'zip', 'country', 'phoneNumber', ]
    billing_address = {}
    shipping_address = {}
    card_info = {}

    def __getitem__(self, item):
        return getattr(self, item)

    def __init__(self):
        self.load_config()
        self.load_credentials()
        pass

    def load_config(self):
        from ...models import PaymentAuthnetCIMMethod
        self.configurations = PaymentAuthnetCIMMethod.get_solo()
        self.method_code = PaymentAuthnetCIMMethod.get_code()

    def set_customer(self, user):
        if isinstance(user, User):
            self.user = user

        return self

    def set_address(self, address, address_type='billing'):
        from django.db import models
        from ...models import CustomerAddress
        types = list(dict(static.ADDRESS_TYPE_CHOICES).keys())

        if address_type in types:
            if isinstance(address, dict):
                for af in self.address_fields:
                    if af in address:
                        self[address_type + '_address'][af] = address[af]
            else:
                if isinstance(address, models.Model):
                    address_obj = address
                elif isinstance(address, int):
                    if self.user:
                        address_obj = CustomerAddress.objects.filter(customer=self.user, id=address)
                        if address_obj.exists():
                            address_obj = address_obj.get()
                        else:
                            logger.error('Address ID (%s) not found for User ID (%s)', address, self.user.id,
                                         extra={'address_type': address_type})
                            return {'success': False, 'message': {'address_id': ['Invalid Address submitted']}}
                    else:
                        logger.error('User not found', extra={'address_type': address_type, 'address': address})
                        return {'success': False, 'message': {'user': ['User not found']}}
                else:
                    logger.error('Invalid address format passed', extra={'address': address, 'address_type': address_type})
                    return {'success': False, 'message': {'address': ['Invalid address format']}}

                if address_obj:
                    address_field_mappings = {
                        'firstname': 'firstName',
                        'lastname': 'lastName',
                        'address': 'address',
                        'city': 'city',
                        'state': 'state',
                        'zip': 'zip',
                        'country': 'country',
                        'phone': 'phoneNumber',
                    }

                    for afm in address_field_mappings:
                        if hasattr(address_obj, afm):
                            value = getattr(address_obj, afm)
                            if afm == 'address':
                                value = value + ', ' + address_obj.address2 if address_obj.address2 else value
                            if afm == 'country':
                                pass

                            self[address_type + '_address'][afm] = value

        else:
            logger.error('Address Type: %s, Not Found', address_type, extra={'address': address})
            return {'success': False, 'message': {'address': ['Invalid address type']}}

        return True

    def set_card_info(self, data, *args, **kwargs):
        from .. import validation as data_validation

        data_to_be = {}
        validated = False

        if isinstance(data, dict):
            for dk, dv in data.items():
                data_to_be[dk] = dv

            if 'save_card' in data:
                self.save_card_info = bool(data['save_card'])
            if 'cc_number' in data:
                data_to_be['cc_number'] = int(data['cc_number'])
            if 'cc_type' in data:
                data_to_be['cc_type'] = data['cc_type']
            if 'cc_exp_month' in data:
                data_to_be['cc_exp_month'] = data['cc_exp_month'].zfill(2)
            if 'cc_exp_year' in data:
                data_to_be['cc_exp_year'] = data['cc_exp_year'][-2:]

            validated = data_validation.validate_card(data_to_be)

        if validated and validated['success']:
            self.set_inputs(validated)
            return {'success': True}
        else:
            logger.error('Invalid Card data passed', extra={'data': data, 'errors': validated['message']})
            return validated

    def set_inputs(self, data, *args, **kwargs):
        if 'data' in data and isinstance(data['data'], dict):
            mapping = {
                'cc_owner': 'card_owner',
                'cc_number': 'card_number',
                'cc_exp_month': 'card_exp_month',
                'cc_exp_year': 'card_exp_year',
                'cc_cid': 'card_cvv',
                'cc_type': 'card_type',
                'card_owner': 'card_owner',
                'card_type': 'card_type',
                'card_last4': 'card_number',
                'card_exp_month': 'card_exp_month',
                'card_exp_year': 'card_exp_year',
                'transaction_id': 'transaction_id',
            }
            mapping_keys = list(mapping.keys())
            for fk, fv in data['data'].items():
                if fk in mapping_keys:
                    self.inputs[mapping[fk]] = fv

            if 'payment_profile_id' in data['data']:
                self.payment_profile_id = data['data']['payment_profile_id'] if 'payment_profile_id' in data['data'] \
                    else self.payment_profile_id

            if 'customer_profile_id' in data['data']:
                self.customer_profile_id = data['data']['customer_profile_id'] if 'customer_profile_id' in data['data'] \
                    else self.customer_profile_id

    def load_credentials(self):
        if self.configurations:
            if self.configurations.is_enabled:
                if self.configurations.api_key and self.configurations.transaction_key:
                    self.credentials['login_id'] = self.configurations.api_key
                    self.credentials['transaction_key'] = self.configurations.transaction_key

                self.sandbox = True if self.configurations.is_sandbox else False

    def validate_payment_data(self, data, *args, **kwargs):
        from ...models import CustomerExtra, MemberExtra, AuthnetcimCards, AuthnetcimCustomers

        validated = {}

        if 'saved_card_id' in data and data['saved_card_id'] and data['saved_card_id'].isnumeric():
            if 'customer_extra_obj' in data and (isinstance(data['customer_extra_obj'], CustomerExtra) or
                                                 isinstance(data['customer_extra_obj'], MemberExtra)):
                customer = data['customer_extra_obj']

                conditions = {}
                profile_id = profile = None

                if isinstance(customer, CustomerExtra):
                    conditions['customer'] = customer.customer
                if isinstance(customer, MemberExtra):
                    conditions['tbl_member'] = customer.tbl_member

                if conditions:
                    profile_id_q = AuthnetcimCustomers.objects.filter(**conditions)
                    if profile_id_q.exists():
                        profile = profile_id_q.get()
                        profile_id = profile.customer_profile_id if profile.customer_profile_id else None

                        conditions['id'] = int(data['saved_card_id'])

                if profile_id and profile:
                    payment_profile_q = AuthnetcimCards.objects.filter(authnetcim_customer=profile,
                                                                       id=data['saved_card_id'])
                    if payment_profile_q.exists():
                        payment_profile = payment_profile_q.get()

                        payment_profile_dict = model_to_dict(payment_profile)
                        del payment_profile_dict['authnetcim_customer']
                        del payment_profile_dict['id']
                        del payment_profile_dict['is_disabled']

                        self.payment_profile_id = int(payment_profile.authnetcim_payment_profile_id)
                        self.set_inputs({'data': payment_profile_dict})
                        self.customer_profile_id = int(profile.customer_profile_id)

                        validated['success'] = True
                        validated['data'] = payment_profile_dict
                        validated['data']['customer_profile_id'] = profile.customer_profile_id

                    else:
                        validated['success'] = False
                        validated['message'] = {
                            'saved_card_id': ["Your selected card data is invalid. Please remove card in <a href='{}'>"
                                       "here</a> or enter your valid card information"
                                                  .format(reverse('cart:account_manage_cards'))]
                        }
                else:
                    validated['success'] = False
                    validated['message'] = {
                        'saved_card_id': ["Your have submitted invalid card data. Please fix and try again"]
                    }
            else:
                validated['success'] = False
                validated['message'] = {
                    'saved_card_id': ["Your have submitted invalid card data. Please fix and try again"]
                }

            return validated

        validated = self.set_card_info(data, *args, **kwargs)

        if isinstance(validated, dict):
            if 'success' in validated:
                if not validated['success']:
                    return validated
                else:
                    card_validated = self.validate_card_details()
                    if not card_validated:
                        validated['success'] = False
                        validated['message'] = {
                            'cc_number': ["Card Number is not valid"]
                        }
                        del validated['data']

                        return validated
                    else:
                        return True

        return False

    def process(self, data, save_card_only=False, *args, **kwargs):
        from ...models import CustomerExtra, MemberExtra, AuthnetcimCards, AuthnetcimCustomers, AuthnetcimCardsTransactions

        if 'customer_extra_obj' in data and (isinstance(data['customer_extra_obj'], CustomerExtra) or
                                             isinstance(data['customer_extra_obj'], MemberExtra)):
            customer = data['customer_extra_obj']

            conditions = {}
            profile_id = profile = None

            if not self.customer_profile_id:
                if isinstance(customer, CustomerExtra):
                    conditions['customer'] = customer.customer
                if isinstance(customer, MemberExtra):
                    conditions['tbl_member'] = customer.tbl_member

                if conditions:
                    self.set_inputs(data={'data': {'card_owner': customer.get_fullname()}})
                    profile_id_q = AuthnetcimCustomers.objects.filter(**conditions)
                    if profile_id_q.exists():
                        profile = profile_id_q.get()
                        profile_id = profile.customer_profile_id if profile.customer_profile_id else None

                if not profile_id:
                    self.create_customer_profile(customer)
                    if self.customer_profile_id:
                        if profile:
                            profile.customer_profile_id = self.customer_profile_id
                            profile.save()
                        else:
                            profile_data = {'customer_profile_id': self.customer_profile_id,}
                            profile_data.update(conditions)
                            profile = AuthnetcimCustomers.objects.create(**profile_data)

                        data['customer_profile_id'] = self.customer_profile_id
                else:
                    self.customer_profile_id = int(profile_id)

            data['customer_profile_id'] = self.customer_profile_id

            if self.customer_profile_id:
                if not self.payment_profile_id:
                    if 'save_card' in data and data['save_card']:
                        self.create_payment_profile(self.customer_profile_id)
                        if self.payment_profile_id and self.get_payment_mode():
                            data['payment_profile_id'] = self.payment_profile_id

                            card_pp = AuthnetcimCards.objects.filter(authnetcim_payment_profile_id=self.payment_profile_id)
                            if not card_pp.exists():
                                card_pp = AuthnetcimCards.objects.create(**{
                                    'authnetcim_customer': profile,
                                    'authnetcim_payment_profile_id': self.payment_profile_id,
                                    'card_owner': self.get_card_owner(),
                                    'card_type': self.get_card_type(),
                                    'card_last4': int(self.get_card_number_last4()),
                                    'card_exp_month': int(self.get_card_exp_month()),
                                    'card_exp_year': int(self.get_card_exp_year()),
                                })
                                data['payment'] = card_pp
                            else:
                                card_pp = card_pp.get()
                                data['payment'] = card_pp

                            if save_card_only:
                                return self.payment_profile_id
                    elif 'transaction_id' in data and data['transaction_id']:
                        self.payment_profile_id = self.get_payment_profiles_from_transaction(data['transaction_id'])
                else:
                    payment_profile_q = AuthnetcimCards.objects.filter(authnetcim_payment_profile_id=self.payment_profile_id)
                    if payment_profile_q.exists():
                        data['payment'] = payment_profile_q.get()

        if 'amount' in data and isinstance(data['amount'], float) or (isinstance(data['amount'], int) or
                (isinstance(data['amount'], str)) and data['amount'].isnumeric()):

            if self.get_payment_mode() == static.AUTHORIZENETCIM_PAYMENT_ACTION_TYPE__AUTH_ONLY:
                return self.auth_only_transaction(data['amount'], data)
            elif self.get_payment_mode() == static.AUTHORIZENETCIM_PAYMENT_ACTION_TYPE__AUTH_CAPTURE:
                return self.auth_capture_transaction(data['amount'], data)

        else:
            return "Amount not specified"

        return False

    def save_payment(self, order_obj, payment_transaction, customer_id=None, payment_id=None):
        from ...models import Order, OrderPayment, PaymentTransaction

        order_payment = None

        if isinstance(order_obj, Order) and isinstance(payment_transaction, PaymentTransaction):
            status = static.PAYMENT_STATUS_TO_AUTHORIZENETCIM_STATUS[payment_transaction.transaction_type] \
                if payment_transaction.transaction_type in static.PAYMENT_STATUS_TO_AUTHORIZENETCIM_STATUS else ''

            order_payment = OrderPayment.objects.create(**{
                'method': self.method_code,
                'order': order_obj,
                'status': status,
                'card_owner': order_obj.get_customer_name(),
                'card_type': self.get_card_type() if self.get_card_type() else None,
                'card_last4': self.get_card_number_last4() if self.get_card_number_last4() else None,
                'card_exp_month': self.get_card_exp_month() if self.get_card_exp_month() else None,
                'card_exp_year': self.get_card_exp_year() if self.get_card_exp_year() else None,
            })

            self.save_card_transaction(payment_transaction, payment_id, customer_id)

        return order_payment

    def save_card_transaction(self, payment_transaction, payment_id=None, customer_id=None ):
        from ...models import AuthnetcimCards, AuthnetcimCardsTransactions, PaymentTransaction

        if not payment_id:
            payment_id = self.payment_profile_id
        if not customer_id:
            payment_id = self.customer_profile_id

        if payment_id and customer_id:
            if payment_transaction and isinstance(payment_transaction, PaymentTransaction):
                card_pp = AuthnetcimCards.objects.filter(authnetcim_payment_profile_id=payment_id,
                                                         authnetcim_customer__customer_profile_id=customer_id)
                if card_pp.exists():
                    card_pp = card_pp.get()
                    AuthnetcimCardsTransactions.objects.create(**{
                        'authnetcim_card': card_pp,
                        'payment_transaction': payment_transaction,
                    })

                    payment_transaction.is_payment_info_saved = True
                    payment_transaction.save()

    def create_customer_profile(self, customer):
        from ...models import CustomerExtra, MemberExtra

        if isinstance(customer, CustomerExtra) or isinstance(customer, MemberExtra):
            customer = customer.get_customer()

        if self.api_auth() and isinstance(customer, User):
            create_customer_profile = apicontractsv1.createCustomerProfileRequest()
            create_customer_profile.merchantAuthentication = self.api_auth()
            create_customer_profile.profile = apicontractsv1.customerProfileType(customer.first_name, customer.last_name,
                                                                                 customer.email)
            if self.api_settings():
                create_customer_profile.profileSettings = self.api_settings()

            controller = createCustomerProfileController(create_customer_profile)
            controller.execute()

            response = controller.getresponse()

            if response.messages.resultCode == "Ok":
                self.customer_profile_id = int(response.customerProfileId)
                return self.customer_profile_id
            else:
                # 'A duplicate record already exists.'
                response_text = self.parse_api_response(response)
                if isinstance(response_text, int):
                    self.customer_profile_id = response_text
                    return self.customer_profile_id

                return "Failed to create customer profile %s" % response_text

        return self.customer_profile_id

    def create_payment_profile(self, customer_profile):
        if self.api_auth() and isinstance(customer_profile, int) and self.api_payment() \
                and self.api_customer_billing_address():

            bill_to = self.api_customer_billing_address()

            profile = apicontractsv1.customerPaymentProfileType()
            profile.payment = self.api_payment()
            profile.billTo = bill_to

            create_customer_payment_profile = apicontractsv1.createCustomerPaymentProfileRequest()
            create_customer_payment_profile.merchantAuthentication = self.api_auth()
            create_customer_payment_profile.paymentProfile = profile
            create_customer_payment_profile.customerProfileId = str(customer_profile)

            controller = createCustomerPaymentProfileController(create_customer_payment_profile)
            controller.execute()

            response = controller.getresponse()

            if response.messages.resultCode == "Ok":
                self.payment_profile_id = int(response.customerPaymentProfileId)
                return self.payment_profile_id
            else:
                # 'A duplicate record already exists.'
                response_text = self.parse_api_response(response)
                if isinstance(response_text, int):
                    self.payment_profile_id = response_text
                    return self.payment_profile_id
                return "Failed to create customer payment profile %s" % response_text

        return self.payment_profile_id

    def get_transaction_details(self, transaction_id):
        if self.api_auth() and isinstance(transaction_id, int):
            create_customer_profile_from_trans = apicontractsv1.getTransactionDetailsRequest()
            create_customer_profile_from_trans.merchantAuthentication = self.api_auth()
            create_customer_profile_from_trans.transId = str(transaction_id)

            controller = getTransactionDetailsController(create_customer_profile_from_trans)
            controller.execute()

            response = controller.getresponse()

            if response.messages.resultCode == "Ok":
                return {
                    'customer': {
                        'email': response.transaction.customer.email,
                    },
                    'billTo': {
                        'city': response.transaction.billTo.city,
                        'state': response.transaction.billTo.state,
                        'country': response.transaction.billTo.country,
                        'zip': response.transaction.billTo.zip,
                    },
                    'AVS': response.transaction.AVSResponse.text,
                    'amount': response.transaction.authAmount,
                    'profile': {
                        'customer_profile_id': response.transaction.profile.customerProfileId,
                        'customer_payment_profile_id': response.transaction.profile.customerPaymentProfileId,
                    },
                    'recurring_billing': response.transaction.recurringBilling.pyval,
                    'transaction_type': response.transaction.transactionType.text,
                }
            else:
                return "Failed to get transaction details for TransactionID: %s" % str(transaction_id)

    def api_auth(self):
        if not self.api_merchant_auth:
            if self.credentials['login_id'] and self.credentials['transaction_key']:
                merchant_auth = apicontractsv1.merchantAuthenticationType()
                merchant_auth.name = self.credentials['login_id']
                merchant_auth.transactionKey = self.credentials['transaction_key']

                self.api_merchant_auth = merchant_auth

        return self.api_merchant_auth

    def api_customer_billing_address(self):
        if not self.api_billing_address:
            if self.billing_address:
                # Set the customer's Bill To address
                customer_address = apicontractsv1.customerAddressType()
                for ak, av in self.billing_address.items():
                    setattr(customer_address, ak, av)
                # customer_address.firstName = "Ellen"
                # customer_address.lastName = "Johnson"
                # customer_address.company = "Souveniropolis"
                # customer_address.address = "14 Main Street"
                # customer_address.city = "Pecan Springs"
                # customer_address.state = "TX"
                # customer_address.zip = "44628"
                # customer_address.country = "USA"

                self.api_billing_address = customer_address

        return self.api_billing_address

    def api_customer_shipping_address(self):
        if not self.api_shipping_address:
            if self.shipping_address:
                # Set the customer's Ship To address
                customer_address = apicontractsv1.customerAddressType()
                for ak, av in self.shipping_address.items():
                    setattr(customer_address, ak, av)

                self.api_shipping_address = customer_address

        return self.api_shipping_address

    def api_payment(self):
        if not self.api_payment_obj:
            # Create the payment data for a credit card
            credit_card = apicontractsv1.creditCardType()
            credit_card.cardNumber = str(self.get_card_number())
            credit_card.expirationDate = self.get_card_exp()
            credit_card.cardCode = self.get_card_cvv()

            # Add the payment data to a paymentType object
            payment = apicontractsv1.paymentType()
            payment.creditCard = credit_card

            self.api_payment_obj = payment

        return self.api_payment_obj

    def api_payment_profile(self, customer_profile_id, payment_profile_id):
        if not self.api_payment_profile_obj:
            # create a customer payment profile
            profile_to_charge = apicontractsv1.customerProfilePaymentType()
            profile_to_charge.customerProfileId = str(customer_profile_id)
            profile_to_charge.paymentProfile = apicontractsv1.paymentProfile()
            profile_to_charge.paymentProfile.paymentProfileId = str(payment_profile_id)

            self.api_payment_profile_obj = profile_to_charge

        return self.api_payment_profile_obj

    def api_settings(self):
        if not self.api_settings_obj:
            # Add values for transaction settings
            duplicate_window_setting = apicontractsv1.settingType()
            duplicate_window_setting.settingName = "duplicateWindow"
            duplicate_window_setting.settingValue = "600"
            settings = apicontractsv1.ArrayOfSetting()
            settings.setting.append(duplicate_window_setting)

            self.api_settings_obj = settings

        return self.api_settings_obj

    def api_transaction(self, data, need_payment=True):
        from ...models import AuthnetcimCards

        transaction_request = apicontractsv1.transactionRequestType()
        if self.api_settings():
            transaction_request.transactionSettings = self.api_settings()

        if need_payment:
            payment_id = customer_id = None
            if 'payment' in data and data['payment'] and isinstance(data['payment'], AuthnetcimCards):
                payment_id = data['payment'].authnetcim_payment_profile_id
                customer_id = data['payment'].authnetcim_customer.customer_profile_id

            if not payment_id:
                payment_id = self.payment_profile_id
            if not customer_id:
                customer_id = self.customer_profile_id

            if payment_id and customer_id:
                if self.api_payment_profile(customer_id, payment_id):
                    transaction_request.profile = self.api_payment_profile(customer_id, payment_id)
                else:
                    transaction_request.billTo = self.api_customer_billing_address()
                    transaction_request.shipTo = self.api_customer_shipping_address()
                    transaction_request.payment = self.api_payment()
            else:
                transaction_request.payment = self.api_payment()

        return transaction_request

    def auth_only_transaction(self, amount, data={}):
        response = None
        customer_id = None
        payment_id = None

        if self.api_transaction(data):
            transaction_request = self.api_transaction(data)
            transaction_request.transactionType = "authOnlyTransaction"
            transaction_request.amount = str(amount)

            response = self.execute_transaction(transaction_request, data)

            if hasattr(transaction_request, 'profile') and transaction_request.profile:
                customer_id = transaction_request.profile.customerProfileId
                payment_id = transaction_request.profile.paymentProfile.paymentProfileId

        return [self.parse_api_response(response), 'authorized', response, customer_id, payment_id]

    def auth_capture_transaction(self, amount, data={}):
        response = None
        customer_id = None
        payment_id = None

        if self.api_transaction(data):
            transaction_request = self.api_transaction(data)
            transaction_request.transactionType = "authCaptureTransaction"
            transaction_request.amount = str(amount)

            response = self.execute_transaction(transaction_request, data)

            if hasattr(transaction_request, 'profile') and transaction_request.profile:
                customer_id = transaction_request.profile.customerProfileId
                payment_id = transaction_request.profile.paymentProfile.paymentProfileId

        return [self.parse_api_response(response), 'authorized', response, customer_id, payment_id]

    def capture_prior_auth_transaction(self, amount, transaction_id, data={}):
        response = None

        if self.api_transaction(data, need_payment=False):
            transaction_request = self.api_transaction(data, need_payment=False)
            transaction_request.transactionType = "priorAuthCaptureTransaction"
            transaction_request.refTransId = transaction_id
            transaction_request.amount = str(amount)

            response = self.execute_transaction(transaction_request, data)
            response_text = self.parse_api_response(response)
            if response_text == 0:      # If the authorized amount is already been captured, Authorize.net returns 0
                response_text = int(response.transactionResponse.refTransID.text)

        return [response_text, 'captured', response, None, None]

    def refund_transaction(self, amount, transaction_id, data={}):
        response = None

        if self.api_transaction(data):
            transaction_request = self.api_transaction(data)
            transaction_request.transactionType = "refundTransaction"
            transaction_request.amount = str(amount)
            transaction_request.refTransId = str(transaction_id)

            response = self.execute_transaction(transaction_request, data)

        return [self.parse_api_response(response), 'refunded', response, None, None]

    def void_transaction(self, transaction_id, amount=None, data={}):
        response = None

        if transaction_id and isinstance(transaction_id, int) and self.api_transaction(data):
            transaction_request = self.api_transaction(data)
            transaction_request.transactionType = "voidTransaction"
            transaction_request.refTransId = str(transaction_id)

            response = self.execute_transaction(transaction_request, data)

        return [self.parse_api_response(response), 'voided', response, None, None]

    def execute_transaction(self, transaction_request, data):
        response = None
        if transaction_request and self.api_auth():
            # transaction_request.profile = profileToCharge
            create_transaction_request = apicontractsv1.createTransactionRequest()
            create_transaction_request.merchantAuthentication = self.api_auth()
            if 'reference' in data:
                # create_transaction_request.refId = "TestCard-" + self.get_card_number_last4()
                create_transaction_request.refId = data['reference']

            create_transaction_request.transactionRequest = transaction_request
            create_transaction_controller = createTransactionController(create_transaction_request)
            create_transaction_controller.execute()

            response = create_transaction_controller.getresponse()

        return response

    def validate_card_details(self):
        if self.get_validation_mode():
            if self.get_validation_mode() == 'none':
                return True
            elif self.get_validation_mode() == 'testMode':
                return self.valid_card_number_locally(int(self.get_card_number()))
            elif self.get_validation_mode() == 'liveMode':
                if self.valid_card_number_locally(int(self.get_card_number())):
                    auth, status_to_be, response, customer_id, payment_id = self.auth_only_transaction("0.01",
                                                       {'reference': "ValidateCard-" + self.get_card_number_last4()})
                    if isinstance(auth, int):
                        voided = self.void_transaction(auth)

                        return True
        return False

    def valid_card_number_locally(self, number: int) -> bool:
        """Determines whether the CC number is valid based on Luhn's algorithm."""
        if number <= 0:
            return False

        as_string = str(number)
        reverse = as_string[::-1]

        odd_digits = reverse[::2]
        odd_sum = sum(int(i) for i in odd_digits)

        even_digits = reverse[1::2]
        doubled_even_digits = [int(i) * 2 for i in even_digits]
        summed_digits_for_even_doubles = [i // 10 + i % 10 for i in doubled_even_digits]
        sum_of_even_digit_sums = sum(summed_digits_for_even_doubles)

        return (sum_of_even_digit_sums + odd_sum) % 10 == 0

    def get_transaction_string(self, view_mode='front'):     # view_mode: admin, front
        html = format_html("Credit Card Type:	{}<br/>Credit Card Number:	XXXX-{}<br/>",
                           self.get_card_type(full_name=True),
                           self.get_card_number_last4()
                           )
        if view_mode == 'admin':
            html += format_html("Transaction ID:	{}", self.get_transaction_id())

        return html

    def get_payment_mode(self):
        if not self.payment_mode:
            modes = list(dict(static.AUTHORIZENETCIM_PAYMENT_ACTION_TYPES).keys())
            system_config = self.configurations.payment_action
            if system_config in modes:
                self.payment_mode = system_config

        return self.payment_mode

    def get_validation_mode(self):
        if not self.validation_mode:
            modes = list(dict(static.AUTHORIZENETCIM_PAYMENT_VALIDATION_TYPES).keys())
            system_config = self.configurations.validation_type
            if system_config in modes:
                self.validation_mode = system_config

        return self.validation_mode

    def get_card_owner(self):
        owner = self.inputs['card_owner'] if 'card_owner' in self.inputs else ''
        if not owner and self.api_customer_billing_address():
            owner = self.api_customer_billing_address().firstName + ' ' + self.api_customer_billing_address().lastName

        return owner

    def get_card_number(self):
        return self.inputs['card_number'] if 'card_number' in self.inputs else ''

    def get_card_cvv(self):
        return self.inputs['card_cvv'] if 'card_cvv' in self.inputs else ''

    def get_card_exp_month(self):
        return self.inputs['card_exp_month'] if 'card_exp_month' in self.inputs else ''

    def get_card_exp_year(self):
        return self.inputs['card_exp_year'] if 'card_exp_year' in self.inputs else ''

    def get_card_type(self, full_name=False):
        card_type = self.inputs['card_type'] if 'card_type' in self.inputs else ''
        if full_name:
            card_types = dict(static.CARD_TYPES)
            if card_type in card_types:
                return card_types[card_type]

        return card_type

    def get_card_number_last4(self):
        return str(self.get_card_number())[-4:] if self.get_card_number() else ''

    def get_card_exp(self):
        return self.get_card_exp_month() + self.get_card_exp_year()

    def get_transaction_id(self):
        return self.inputs['transaction_id'] if 'transaction_id' in self.inputs else ''

    def get_payment_profiles_from_transaction(self, transaction_id):
        from ...models import AuthnetcimCardsTransactions, PaymentTransaction
        pay = None

        if transaction_id:
            if isinstance(transaction_id, PaymentTransaction):
                pay = transaction_id
            elif isinstance(transaction_id, int):
                pay_q = PaymentTransaction.objects.filter(transaction_id=transaction_id, is_payment_info_saved=True)
                if pay_q.exists():
                    pay = pay_q.first()

            if pay:
                auth_q = AuthnetcimCardsTransactions.objects.filter(payment_transaction=pay)
                if auth_q.exists():
                    auth = auth_q.get()
                    profile = [int(auth.authnetcim_card.authnetcim_customer.customer_profile_id),
                            int(auth.authnetcim_card.authnetcim_payment_profile_id)]

                    self.set_inputs({'data': {'payment_profile_id': profile[1], 'customer_profile_id': profile[0]}})

                    return profile

        return False

    def parse_api_response(self, response):
        if response is not None:
            # Check to see if the API request was successfully received and acted upon
            if response.messages.resultCode == "Ok":
                # Since the API request was successful, look for a transaction response
                # and parse it to display the results of authorizing the card
                if hasattr(response.transactionResponse, 'messages') is True:
                    return int(response.transactionResponse.transId)
                else:
                    if hasattr(response.transactionResponse, 'errors') is True:
                        return "Error Code# " + str(response.transactionResponse.errors.error[0].errorCode) + " :  " + \
                               response.transactionResponse.errors.error[0].errorText

            else:
                if hasattr(response, 'transactionResponse') is True and hasattr(response.transactionResponse,
                                                                                'errors') is True:
                    """
                    We will get the transaction ID even from following Errors. 
                    'Error Code# 11 :  A duplicate transaction has been submitted.'
                    """
                    if int(response.transactionResponse.errors.error[0].errorCode) in [11]:
                        return int(response.transactionResponse.transId)

                    return "Error Code# " + str(response.transactionResponse.errors.error[0].errorCode) + " :  " + \
                           response.transactionResponse.errors.error[0].errorText
                else:
                    message_text = response.messages.message[0]['text'].text
                    checking_text = "A duplicate"
                    if checking_text in message_text:
                        import re
                        groups = re.search(r'\d+', message_text)
                        if groups:
                            id_number = int(groups.group())
                            return id_number

                    return "Error Code# " + response.messages.message[0]['code'].text + " :  " + \
                           response.messages.message[0]['text'].text

        return False



