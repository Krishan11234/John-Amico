from . import static
from cerberus import Validator
from cerberus import errors


class CustomErrorHandler(errors.BasicErrorHandler):
    messages = errors.BasicErrorHandler.messages.copy()
    messages[errors.REGEX_MISMATCH.code] = 'Value is not in allowed format.'


def get_payment_validators():
    return {
        'creditcard': 'validate_card',
    }


def validate_card(card_data):
    schema = {
        'cc_number': {'required': True, 'empty': False, 'type': 'number', 'minlength': 13, 'maxlength': 16},
        'cc_type': {'required': True, 'empty': False, 'type': 'string', 'allowed': list(dict(static.CARD_TYPES).keys())},
        'cc_cid': {'required': True, 'empty': False, 'type': 'string', 'minlength': 3, 'maxlength': 4},
        'cc_exp_year': {'required': True, 'empty': False, 'type': 'string', 'minlength': 2, 'maxlength': 2,
                        'allowed': list(dict(static.CARD_EXP_YEARS).keys())},
        'cc_exp_month': {'required': True, 'empty': False, 'type': 'string', 'minlength': 2, 'maxlength': 2,
                         'allowed': list(dict(static.CARD_EXP_MONTHS).keys())},
    }

    validator = Validator(schema, allow_unknown=True, error_handler=CustomErrorHandler)
    is_valid = validator.validate(card_data)

    payload = {
        'success': bool(is_valid),
        'message': validator.errors if not is_valid else ""
    }
    if is_valid:
        for c_key, cv in schema.items():
            if 'data' not in payload:
                payload['data'] = {}

            if c_key in card_data:
                payload['data'][c_key] = card_data[c_key]

    return payload


def validate_method(method, allowed_methods=[]):
    schema = {
        'method': {'required': True, 'empty': False, 'type': 'string', 'allowed': allowed_methods},
    }

    validator = Validator(schema, allow_unknown=True, error_handler=CustomErrorHandler)
    is_valid = validator.validate(method)

    payload = {
        'success': bool(is_valid),
        'message': validator.errors if not is_valid else ""
    }
    if is_valid:
        payload['data'] = {}
        payload['data']['method'] = method['method']

    return payload


def validate_account_create_data(data, single_filed=''):
    schema = {
        "email": {
            "type": "string",
            "minlength": 8,
            "maxlength": 100,
            "required": True,
            "regex": "^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\\.[a-zA-Z0-9-.]+$"
        },
        'password': {'type': 'string', 'required': True, 'empty': False, 'min': 6, }
    }
    if single_filed and single_filed in schema:
        schema = {single_filed: schema[single_filed]}

    validator = Validator(schema, allow_unknown=True, error_handler=CustomErrorHandler)
    is_valid = validator.validate(data)

    payload = {
        'success': bool(is_valid),
        'message': validator.errors if not is_valid else ""
    }
    if is_valid:
        for sc_key, sc in schema.items():
            if 'data' not in payload:
                payload['data'] = {}

            if sc_key in data:
                payload['data'][sc_key] = data[sc_key]

    return payload


def validate_address(address_data, address_type='billing'):
    schema = {
        "email": {
            "type": "string",
            "minlength": 5,
            "maxlength": 100,
            "required": True if address_type == 'billing' else False,
            "regex": "^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\\.[a-zA-Z0-9-.]+$"
        },
        'firstname': {'required': True, 'empty': False, 'type': 'string', 'minlength': 2, 'maxlength': 20},
        'lastname': {'required': True, 'empty': False, 'type': 'string', 'minlength': 2, 'maxlength': 20},
        'company': {'required': False, 'type': 'string', 'maxlength': 40},
        'address1': {'required': True, 'empty': False, 'type': 'string', 'minlength': 2, 'maxlength': 80},
        'address2': {'required': False, 'type': 'string', 'maxlength': 80},
        'city': {'required': True, 'empty': False, 'type': 'string', 'minlength': 2, 'maxlength': 50},
        'state': {'required': True, 'empty': False, 'type': 'string', 'allowed': list(dict(static.US_STATES).keys())},
        'zip': {'required': True, 'empty': False, 'type': 'string', "regex": "^\d{5}(?:[-\s]\d{4})?$",
                     'minlength': 4, 'maxlength': 10},
        'country': {'required': True, 'empty': False, 'type': 'string', 'allowed': list(dict(static.COUNTRIES).keys())},
        'telephone': {
            'required': True,
            "type": "string",
            "minlength": 10,
            "maxlength": 12,
            "regex": "^\(?([0-9]{3})\)?[-.]?([0-9]{3})[-.]?([0-9]{4})$",
        },
    }

    validator = Validator(schema, allow_unknown=True, error_handler=CustomErrorHandler)
    is_valid = validator.validate(address_data)

    payload = {
        'success': bool(is_valid),
        'message': validator.errors if not is_valid else ""
    }
    if is_valid:
        for sc_key, sc in schema.items():
            if 'data' not in payload:
                payload['data'] = {}

            if sc_key in address_data:
                payload['data'][sc_key] = address_data[sc_key]

    return payload
