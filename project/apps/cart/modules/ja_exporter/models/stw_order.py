from ....models import Order


class StwOrder(Order):

    class Meta:
        proxy = True
