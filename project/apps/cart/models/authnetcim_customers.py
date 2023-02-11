from django.db import models
from django.contrib.auth import get_user_model
from django.contrib.auth.models import User
from django.utils import timezone
from django.utils.translation import gettext_lazy as _

from ..utils import static


class AuthnetcimCustomers(models.Model):
    id = models.AutoField(primary_key=True)

    customer = models.ForeignKey(get_user_model(), on_delete=models.CASCADE, blank=True, null=True)
    tbl_member = models.ForeignKey('TblMember', on_delete=models.CASCADE, blank=True, null=True)

    customer_profile_id = models.IntegerField(blank=False, null=False, unique=True)

    created_at = models.DateTimeField(auto_now_add=True, blank=True)

    class Meta:
        ordering = ['customer_profile_id']
        # unique_together = ('customer_profile_id', 'customer_id', 'tbl_member_id')

    def __str__(self):
        return 'Authnet Customer Profile ID: {} - for {}'.format(
            self.customer_profile_id,
            (self.tbl_member.get_full_name() if not self.customer else self.customer.get_full_name()))

    def get_payment_objects(self):
        import datetime, calendar
        from datetime import timedelta

        cards = {}
        cards_q = self.authnetcimcards_set.filter(is_disabled=False)
        if cards_q.exists():
            for card in cards_q.all():
                _, last_day = calendar.monthrange(card.card_exp_year, card.card_exp_month)
                date_string = "{}/{}/{} {}:{}".format(card.card_exp_year, card.card_exp_month, last_day, 23, 59)
                date_time_obj = datetime.datetime.strptime(date_string, "%y/%m/%d %H:%M")
                if date_time_obj > datetime.datetime.now() + timedelta(minutes=30):
                    cards[card.id] = {
                        'title': 'xxxx-{} - Exp: {}/{}'.format(card.card_last4, card.card_exp_month,
                                                               card.card_exp_year),
                        'payment_profile_id': card.authnetcim_payment_profile_id
                    }

        return cards
