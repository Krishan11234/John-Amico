from django.db import models
from django.contrib.auth.models import User, AnonymousUser, Group
from ..utils import helper


class UserExtended(User):

    is_professional = False
    amico_id = ''
    tbl_member_id = ''
    customer_id = ''

    class Meta:
        proxy = True

    def get_groups(self):
        current_groups = self.groups.all()
        if current_groups.count() > 0:
            return self.groups
        elif helper.is_professional_logged_in():
            return Group.objects.filter(name='Ambassador Pro')
        else:
            return Group.objects.filter(name='NOT_LOGGED_IN')

    def get_member(self):
        from ..models import TblMember
        return TblMember.objects.filter(amico_id=self.amico_id).get()

