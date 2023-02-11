
class BasePaymentHandler:
    transaction_happens_in = 'offline'      # online / offline

    def validate_payment_data(self, data, *args, **kwargs):
        pass

    def process(self, data, *args, **kwargs):
        pass

    def set_address(self, data, type='', *args, **kwargs):
        pass

    def set_inputs(self, data, *args, **kwargs):
        pass
