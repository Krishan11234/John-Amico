from django.contrib.auth.backends import BaseBackend, ModelBackend
from django.contrib.auth.hashers import check_password
from .models import UserExtended, TblMember


class BackendForCustomMembers(ModelBackend):

    def get_user(self, user_id):
        try:
            member_q = TblMember.objects.filter(pk=user_id)
            if member_q.exists():
                member = member_q.get()
                user = member.convert_to_django_user_model()

                return user
        except TblMember.DoesNotExist:
            return None
