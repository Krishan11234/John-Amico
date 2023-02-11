import re, json, binascii, base64, random
from datetime import datetime
from django.utils.formats import get_format
from django.contrib.auth.models import User, Group
from django_middleware_global_request.middleware import get_request as gr
from Crypto.Cipher import AES
from Crypto import Random

"""
https://docs.djangoproject.com/en/3.1/topics/db/sql/#s-executing-custom-sql-directly
"""


def dictfetchall(cursor):
    "Return all rows from a cursor as a dict"
    columns = [col[0] for col in cursor.description]
    return [
        dict(zip(columns, row))
        for row in cursor.fetchall()
    ]


def parse_date(date_str):
    """Parse date from string by DATE_INPUT_FORMATS of current language"""
    for item in get_format('DATE_INPUT_FORMATS'):
        try:
            return datetime.strptime(date_str, item).date()
        except (ValueError, TypeError):
            continue

    return None


def get_unique_string(do_base64=True):
    import hashlib
    import base64
    from datetime import datetime

    date_now = datetime.now()
    date_now_encoded = str(datetime.timestamp(date_now)).encode()
    date_now_md5 = hashlib.md5(date_now_encoded).hexdigest().encode()

    if do_base64:
        base64_bytes = base64.b64encode(date_now_md5)
        return base64_bytes.decode("ascii").rstrip('=')

    return date_now_md5


def make_md5_string(string):
    if string:
        import hashlib

        str_md5 = hashlib.md5(string.encode('utf-8')).hexdigest()
        return str_md5
    return None


def make_bas64en_string(string):
    if string:
        import base64

        base64_bytes = base64.b64encode(string.encode())
        str_base = base64_bytes.decode("ascii")
        return str_base
    return None


def make_bas64de_string(string):
    if string:
        import base64

        try:
            # base64_bytes = bytes(str(string), 'utf-8')
            # message_bytes = base64.b64decode(base64_bytes)
            message_bytes = base64.b64encode(string.encode("utf-8"))
            message = str(message_bytes, "utf-8")
            return message
        except Exception as e:
            pass
    return None


def encrypt_message(data, passphrase=''):
    """https://gist.github.com/eoli3n/d6d862feb71102588867516f3b34fef1"""
    """
         Encrypt using AES-256-CBC with random/shared iv
        'passphrase' must be in hex, generate with 'openssl rand -hex 32'
    """
    try:
        passphrase = passphrase if passphrase else '227da206097e0dce6fb7ea27df854aba745a20638d8a4c60f72b4e8aa846b552'
        key = binascii.unhexlify(passphrase)
        pad = lambda s: s + chr(16 - len(s) % 16) * (16 - len(s) % 16)
        iv = Random.get_random_bytes(16)
        cipher = AES.new(key, AES.MODE_CBC, iv)
        encrypted_64 = base64.b64encode(cipher.encrypt(pad(data))).decode('ascii')
        iv_64 = base64.b64encode(iv).decode('ascii')
        json_data = {}
        json_data['iv'] = iv_64
        json_data['data'] = encrypted_64
        clean = base64.b64encode(json.dumps(json_data).encode('ascii'))

        return clean
    except Exception as e:
        return None


def decrypt_message(data, passphrase=''):
    """https://gist.github.com/eoli3n/d6d862feb71102588867516f3b34fef1"""
    """
         Decrypt using AES-256-CBC with iv
        'passphrase' must be in hex, generate with 'openssl rand -hex 32'
        # https://stackoverflow.com/a/54166852/11061370
    """
    try:
        passphrase = passphrase if passphrase else '227da206097e0dce6fb7ea27df854aba745a20638d8a4c60f72b4e8aa846b552'
        unpad = lambda s: s[:-s[-1]]
        key = binascii.unhexlify(passphrase)
        data += "=" * ((4 - len(data) % 4) % 4)  # ugh
        encrypted = json.loads(base64.b64decode(data).decode('ascii'))
        encrypted_data = base64.b64decode(encrypted['data'])
        iv = base64.b64decode(encrypted['iv'])
        cipher = AES.new(key, AES.MODE_CBC, iv)
        decrypted = cipher.decrypt(encrypted_data)
        clean = unpad(decrypted).decode('ascii').rstrip()

        return clean
    except Exception as e:
        return None


def get_request():
    return gr()


def get_base_url():
    request = get_request()
    return request.scheme + '://' + request.get_host()


def is_user_types_logged_in(condition_type='and'):
    condition_type = condition_type if condition_type in ['and', 'or'] else 'and'
    if condition_type == 'and':
        return (
                is_customer_logged_in() and
                is_professional_logged_in()
        )
    if condition_type == 'or':
        return (
                is_customer_logged_in() or
                is_professional_logged_in()
        )


def is_user_types_logged_in__condition_or():
    return is_user_types_logged_in(condition_type='or')


def get_current_customer():
    from django.db import models
    from django.contrib.auth.models import User, AnonymousUser

    customer = AnonymousUser()
    request = get_request()
    if is_customer_logged_in():
        # If Admin is logged in, in the front-end part, we will treat the user as Guest
        customer = customer if request.user.is_superuser else request.user

    return customer


def get_current_customer_extra():
    user = get_current_customer()
    if isinstance(user, User):
        from ..models import CustomerExtra

        customer_q = CustomerExtra.objects.filter(customer=user)
        if customer_q.exists():
            return customer_q.get()
        else:
            return CustomerExtra.objects.create(customer=user)

    return False


def get_current_member():
    from django.contrib.auth.models import User, AnonymousUser

    customer = AnonymousUser()
    request = get_request()
    if is_professional_logged_in():
        # If Admin is logged in, in the front-end part, we will treat the user as Guest
        customer = customer if request.user.is_superuser else request.user
        # customer = customer.get_member()

    return customer


def get_current_member_extra():
    from ..models import UserExtended

    user = get_current_member()
    if isinstance(user, UserExtended):
        return user.get_member()

    return False


def is_customer_logged_in():
    from django.db import models
    from django.contrib.auth.models import User, AnonymousUser

    request = get_request()
    customer = request.user
    if isinstance(customer, User) and not customer.is_superuser:
        if (not hasattr(customer, 'is_professional')) or \
                (hasattr(customer, 'is_professional') and not customer.is_professional):
            return True

    return False


def is_professional_logged_in():
    from django.contrib.auth.models import User, AnonymousUser

    request = get_request()
    customer = request.user
    if customer and isinstance(customer, User) and not customer.is_superuser:
        if hasattr(customer, 'is_professional') and customer.is_professional:
            return True

    return False


def get_current_customer_group(user=None):
    from django.contrib.auth.models import User, AnonymousUser
    from ..models import UserExtended

    cust_group = {}
    groups = []

    customer = user if isinstance(user, User) else get_current_customer()
    if isinstance(customer, AnonymousUser):
        customer = get_current_member()

    if isinstance(customer, UserExtended):
        groups = customer.get_groups().all()
    else:
        if customer and customer.groups:
            groups = customer.groups.all()

    if groups:
        if groups.count() > 0:
            cust_group = groups[0]

    return cust_group


def get_client_ip(request=None):
    request = request if request else get_request()

    x_forwarded_for = request.META.get('HTTP_X_FORWARDED_FOR')
    if x_forwarded_for:
        ip = x_forwarded_for.split(',')[-1].strip()
    else:
        ip = request.META.get('REMOTE_ADDR')

    return ip


def get_html_input_dict(query_dict, param):
    dictionary = {}
    regex = re.compile('%s\[([\w\d_]+)\]' % param)
    for key, value in query_dict.items():
        match = regex.match(key)
        if match:
            inner_key = match.group(1)
            dictionary[inner_key] = value

    return dictionary


def dictmerge(a, b):
    """ deep merge two dictionaries """
    ret = dict(list(a.items()) + list(b.items()))
    for key in set(a.keys()) & set(b.keys()):
        if isinstance(a[key], dict) and isinstance(b[key], dict):
            ret[key] = dictmerge(a[key], b[key])
        elif isinstance(a[key], list) and isinstance(b[key], list):
            for bl in b[key]:
                a[key].append(bl)
    return ret


class dotdict(dict):
    def __getattr__(self, name):
        return self[name]


def isfloat(value):
    try:
        float(value)
        return True
    except ValueError:
        return False


class BlankClass:
    def __init__(self):
        pass
