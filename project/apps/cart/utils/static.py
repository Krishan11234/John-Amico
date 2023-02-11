ROOT_CATEGORY_ID = 2

ANONYMOUS_GROUP_ID = 111

ADDRESS_TYPE_CHOICES = (
    ("billing", "Billing Address"),
    ("shipping", "Shipping Address"),
)

PRODUCT_TYPE_CHOICES = (
    ("simple", "Simple Product"),
    ("virtual", "Virtual Product"),
)

PRODUCT_TYPE_CHOICES_NON_SHIPPABLE = (
    ("virtual", "Virtual Product"),
)

YESNO_BOOL_CHOICES = ((True, 'Yes'), (False, 'No'))
YESNO_CHOICES = ((1, 'Yes'), (0, 'No'))
ENABLE_DISABLE_CHOICES = ((1, 'Enable'), (0, 'Disable'))

GENDER_CHOICES = (
    ("male", "Male"),
    ("female", "Female"),
    ("other", "Other"),
)

ORDER_STATUS_CHOICES = (
    ('cancelled',	'Canceled'),
    ('closed',	'Closed'),
    ('complete',	'Complete'),
    ('fraud',	'Suspected Fraud'),
    ('holded',	'On Hold'),
    # ('pending_paypal',	'Pending PayPal'),
    # ('paypal_canceled_reversal',	'PayPal Canceled Reversal'),
    # ('paypal_reversed',	'PayPal Reversed'),
    ('pending',	'Pending'),
    ('payment_review',	'Payment Review'),
    ('pending_payment',	'Pending Payment'),
    ('processing',	'Processing'),
    ("partially_refunded", "Refunded Partially"),
    ("refunded", "Fully Refunded"),
)

SHIPMENT_STATUS_CHOICES = (
    ('cancelled',	'Canceled'),
    ('on_hold',	'On Hold'),
    ('shipped',	'Shipped'),
)

INVOICE_STATUS_CHOICES = (
    ('paid',	'Paid'),
    ('refunded',	'Refunded'),
    ('cancelled',	'Cancelled'),
)

PAYMENT_STATUS_CHOICES = (
    ("pending", 'Pending'),
    ("partially_paid", "Paid Paritally"),
    ("paid", "Fully Paid"),
    ("authorized", "Authorized"),
    ("captured", "Captured"),
    ("partially_refunded", "Refunded Partially"),
    ("refunded", "Fully Refunded"),
    ("voided", "Transaction Voided"),
    ("cancelled", "Transaction Cancelled"),
)

SHIPPABLE_PRODUCT_TYPES = ['simple',]

PRODUCT_OPTION_PRICE_TYPE_CHOICES = (
    ("abs", "Absolute Pricing"),
    ("fixed", "Fixed Price (Product Price + Fixed Price)"),
    ("percent", "Percent Price (Product Price + Percent of Product Price)"),
)
AUTHORIZENETCIM_PAYMENT_ACTION_TYPE__AUTH_ONLY = 'auth_only'
AUTHORIZENETCIM_PAYMENT_ACTION_TYPE__AUTH_CAPTURE = 'auth_capture'
AUTHORIZENETCIM_PAYMENT_ACTION_TYPES = (
    (AUTHORIZENETCIM_PAYMENT_ACTION_TYPE__AUTH_ONLY, "Authorize Only"),
    (AUTHORIZENETCIM_PAYMENT_ACTION_TYPE__AUTH_CAPTURE, "Authorize and Capture"),
)
AUTHORIZENETCIM_PAYMENT_ACTION_COMPLETE_STATUS = (
    ('failed', "Failed"),
    ('authorized', "Authorized"),
    ('captured', "Captured"),
    ('sale', "Sale"),
    ('voided', "Voided"),
    ('refunded', "Full Refunded"),
    ('partially_refunded', "Partially Refunded"),
)
PAYMENT_STATUS_TO_AUTHORIZENETCIM_STATUS = {
    'authorized': 'authorized',
    'captured': 'captured',
    'paid': 'sale',
    'voided': 'voided',
    'full_refunded': 'refunded',
    'partial_refunded': 'partial_refunded',
    'cancelled': 'failed',
}
AUTHORIZENETCIM_PAYMENT_VALIDATION_TYPES = (
    ("liveMode", "Live ($0.01 test transaction)"),
    ("testMode", "Test (Card number validation only)"),
    ("none", "None (Credit cards are not validated)"),
)
CURRENCIES = (
    ("USD", "US Dollar"),
)
CARD_TYPES = (
    ("AE", "American Express",),
    ("VI", "VISA",),
    ("MC", "MasterCard",),
    ("DI", "Discover",),
)
CARD_EXP_MONTHS = (
    ("01", "January"),
    ("02", "February"),
    ("03", "March"),
    ("04", "April"),
    ("05", "May"),
    ("06", "June"),
    ("07", "July"),
    ("08", "August"),
    ("09", "September"),
    ("10", "October"),
    ("11", "November"),
    ("12", "December"),
)
import datetime
now = datetime.datetime.now()
CARD_EXP_YEARS = []
for y in range(now.year, now.year+8):
    CARD_EXP_YEARS.append((str(y)[-2:], str(y)))
CARD_EXP_YEARS = tuple(CARD_EXP_YEARS)

BOOTSTRAP_COLUMN_SIZES = (
    ("1", "1 portion of 12"),
    ("2", "2 portion of 12"),
    ("3", "3 portion of 12"),
    ("4", "4 portion of 12"),
    ("5", "5 portion of 12"),
    ("6", "6 portion of 12"),
    ("7", "7 portion of 12"),
    ("8", "8 portion of 12"),
    ("9", "9 portion of 12"),
    ("10", "11 portion of 12"),
    ("11", "11 portion of 12"),
    ("12", "12 portion of 12"),
)

COUNTRIES = (
    ('US', 'United States',),
)

US_STATES = (
    ('AK', 'Alaska',),
    ('AL', 'Alabama',),
    ('AR', 'Arkansas',),
    ('AS', 'American Samoa',),
    ('AZ', 'Arizona',),
    ('CA', 'California',),
    ('CO', 'Colorado',),
    ('CT', 'Connecticut',),
    ('DC', 'District of Columbia',),
    ('DE', 'Delaware',),
    ('FL', 'Florida',),
    ('GA', 'Georgia',),
    ('GU', 'Guam',),
    ('HI', 'Hawaii',),
    ('IA', 'Iowa',),
    ('ID', 'Idaho',),
    ('IL', 'Illinois',),
    ('IN', 'Indiana',),
    ('KS', 'Kansas',),
    ('KY', 'Kentucky',),
    ('LA', 'Louisiana',),
    ('MA', 'Massachusetts',),
    ('MD', 'Maryland',),
    ('ME', 'Maine',),
    ('MI', 'Michigan',),
    ('MN', 'Minnesota',),
    ('MO', 'Missouri',),
    ('MP', 'Northern Mariana Islands',),
    ('MS', 'Mississippi',),
    ('MT', 'Montana',),
    ('NA', 'National',),
    ('NC', 'North Carolina',),
    ('ND', 'North Dakota',),
    ('NE', 'Nebraska',),
    ('NH', 'New Hampshire',),
    ('NJ', 'New Jersey',),
    ('NM', 'New Mexico',),
    ('NV', 'Nevada',),
    ('NY', 'New York',),
    ('OH', 'Ohio',),
    ('OK', 'Oklahoma',),
    ('OR', 'Oregon',),
    ('PA', 'Pennsylvania',),
    ('PR', 'Puerto Rico',),
    ('RI', 'Rhode Island',),
    ('SC', 'South Carolina',),
    ('SD', 'South Dakota',),
    ('TN', 'Tennessee',),
    ('TX', 'Texas',),
    ('UT', 'Utah',),
    ('VA', 'Virginia',),
    ('VI', 'Virgin Islands',),
    ('VT', 'Vermont',),
    ('WA', 'Washington',),
    ('WI', 'Wisconsin',),
    ('WV', 'West Virginia',),
    ('WY', 'Wyoming'),
)

ANIMATION__IN_MODES = (
    ("bounce", "bounce"),
    ("flash", "flash"),
    ("pulse", "pulse"),
    ("rubberBand", "rubberBand"),
    ("shake", "shake"),
    ("swing", "swing"),
    ("tada", "tada"),
    ("wobble", "wobble"),
    ("jello", "jello"),
    ("bounceIn", "bounceIn"),
    ("bounceInDown", "bounceInDown"),
    ("bounceInLeft", "bounceInLeft"),
    ("bounceInRight", "bounceInRight"),
    ("bounceInUp", "bounceInUp"),
    ("fadeIn", "fadeIn"),
    ("fadeInDown", "fadeInDown"),
    ("fadeInDownBig", "fadeInDownBig"),
    ("fadeInLeft", "fadeInLeft"),
    ("fadeInLeftBig", "fadeInLeftBig"),
    ("fadeInRight", "fadeInRight"),
    ("fadeInRightBig", "fadeInRightBig"),
    ("fadeInUp", "fadeInUp"),
    ("fadeInUpBig", "fadeInUpBig"),
    ("flipInX", "flipInX"),
    ("flipInY", "flipInY"),
    ("lightSpeedIn", "lightSpeedIn"),
    ("rotateIn", "rotateIn"),
    ("rotateInDownLeft", "rotateInDownLeft"),
    ("rotateInDownRight", "rotateInDownRight"),
    ("rotateInUpLeft", "rotateInUpLeft"),
    ("rotateInUpRight", "rotateInUpRight"),
    ("hinge", "hinge"),
    ("rollIn", "rollIn"),
    ("zoomIn", "zoomIn"),
    ("zoomInDown", "zoomInDown"),
    ("zoomInLeft", "zoomInLeft"),
    ("zoomInRight", "zoomInRight"),
    ("zoomInUp", "zoomInUp"),
    ("slideInDown", "slideInDown"),
    ("slideInLeft", "slideInLeft"),
    ("slideInRight", "slideInRight"),
    ("slideInUp", "slideInUp"),
)

ANIMATION__OUT_MODES = (
    ("bounce", "bounce"),
    ("flash", "flash"),
    ("pulse", "pulse"),
    ("rubberBand", "rubberBand"),
    ("shake", "shake"),
    ("swing", "swing"),
    ("tada", "tada"),
    ("wobble", "wobble"),
    ("jello", "jello"),
    ("bounceOut", "bounceOut"),
    ("bounceOutDown", "bounceOutDown"),
    ("bounceOutLeft", "bounceOutLeft"),
    ("bounceOutRight", "bounceOutRight"),
    ("bounceOutUp", "bounceOutUp"),
    ("fadeOut", "fadeOut"),
    ("fadeOutDown", "fadeOutDown"),
    ("fadeOutDownBig", "fadeOutDownBig"),
    ("fadeOutLeft", "fadeOutLeft"),
    ("fadeOutLeftBig", "fadeOutLeftBig"),
    ("fadeOutRight", "fadeOutRight"),
    ("fadeOutRightBig", "fadeOutRightBig"),
    ("fadeOutUp", "fadeOutUp"),
    ("fadeOutUpBig", "fadeOutUpBig"),
    ("flipOutX", "flipOutX"),
    ("flipOutY", "flipOutY"),
    ("lightSpeedOut", "lightSpeedOut"),
    ("rotateOut", "rotateOut"),
    ("rotateOutDownLeft", "rotateOutDownLeft"),
    ("rotateOutDownRight", "rotateOutDownRight"),
    ("rotateOutUpLeft", "rotateOutUpLeft"),
    ("rotateOutUpRight", "rotateOutUpRight"),
    ("hinge", "hinge"),
    ("rollOut", "rollOut"),
    ("zoomOut", "zoomOut"),
    ("zoomOutDown", "zoomOutDown"),
    ("zoomOutLeft", "zoomOutLeft"),
    ("zoomOutRight", "zoomOutRight"),
    ("zoomOutUp", "zoomOutUp"),
    ("slideOutDown", "slideOutDown"),
    ("slideOutLeft", "slideOutLeft"),
    ("slideOutRight", "slideOutRight"),
    ("slideOutUp", "slideOutUp"),
)