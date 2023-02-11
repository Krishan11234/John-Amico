from ....utils.static import *

AUTOSHIP_REQUEST_TIME_TYPES = (
    ('day', 'Day'),
    ('month', 'Month'),
    ('year', 'Year'),
)

AUTOSHIP_REQUEST_TIME_TYPES_PRIOR_TIME = (
    ('day', 10 * 60),   # 10 Hours, in minutes
    ('month', 10 * 24 * 60),   # 10 Days, in minutes
    ('year', 20 * 24 * 60),   # 20 Days, in minutes
)

AUTOSHIP_REQUEST_STATUES = (
    (1, 'Enabled'),
    (0, 'Disabled'),
    (2, 'Cancelled'),
    (3, 'Halted'),
)
AUTOSHIP_REQUEST_ATTEMPT_STATUES = (
    (0, 'Pending'),
    (1, 'Confirmed'),
    (2, 'Cancelled'),
    (3, 'Finished'),
    (4, 'Failed'),
    (5, 'Skipped'),
)
AUTOSHIP_REQUEST_PRODUCT_STATUES = (
    (1, 'Send To Next Order'),
    (2, 'Do not Send To Next Order'),
    (3, 'Cancelled'),
)
AUTOSHIP_REQUEST_PROCESSED_ORDERS_STATUES = (
    (1, 'Processed'),
    (2, 'Halt'),
)
AUTOSHIP_REQUEST_CANCELLED_BY = (
    (0, 'None'),
    (1, 'Customer'),
    (2, 'Admin'),
    (3, 'System'),
)
