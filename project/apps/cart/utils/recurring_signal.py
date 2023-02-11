from django.dispatch import Signal as BaseSignal
from django.dispatch.dispatcher import _make_id


NONE_ID = _make_id(None)

# A marker for caching
NO_RECEIVERS = object()


class RecurringSignal(BaseSignal):

    def connect(self, receiver, sender=None, weak=True, dispatch_uid=None, priority=50):
        if dispatch_uid is None:
            dispatch_uid = _make_id(receiver)

        inner_uid = '{0}{1}'.format(priority, dispatch_uid)
        super().connect(receiver, sender=sender, weak=weak, dispatch_uid=inner_uid)
        self.receivers.sort()

    # def send(self, sender, **named):
    #     return self.recurring_send(sender, **named)

    def recurring_send(self, sender, **named):
        """
        Send signal from sender to all connected receivers.

        If any receiver raises an error, the error propagates back through send,
        terminating the dispatch loop. So it's possible that all receivers
        won't be called if an error is raised.

        Arguments:

            sender
                The sender of the signal. Either a specific object or None.

            named
                Named arguments which will be passed to receivers.

        Return a list of tuple pairs [(receiver, response), ... ].
        """
        if not self.receivers or self.sender_receivers_cache.get(sender) is NO_RECEIVERS:
            return []

        previous_value = None

        for receiver in self._live_receivers(sender):
            named['previous_receiver'] = previous_value
            previous_value = (receiver, receiver(signal=self, sender=sender, **named))

        return previous_value
