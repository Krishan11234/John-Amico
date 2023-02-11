from django.http import HttpResponseBadRequest, JsonResponse
from django.shortcuts import render, redirect, reverse
from django.contrib import messages

from .base_view import BaseView


from ..utils import helper, cart


class CartView(BaseView):

    # @method_decorator(csrf_exempt)
    # def dispatch(self, *args, **kwargs):
    #     return super(CartView, self).dispatch(*args, **kwargs)

    def get(self, request, request_type=None, type_id=None, *args, **kwargs):
        response = None

        if request_type in ['add', 'update', 'updatePost']:
            return redirect('cart:checkout_cart')

        if request_type in ['delete']:
            response = self.method_switch(request, request_type, int(type_id), *args, **kwargs)

        if not response:
            context = self.common_contexts(request)
            context['html_extra']['page_title'] = "View Cart"
            context['cart'] = super().cart_items_context(reload=True, recheck=True)
            response = render(request, 'frontend/pages/cart.html', context)

        return response

    def post(self, request, request_type=None, type_id=None, *args, **kwargs):
        if request_type in ['add', 'update', 'updatePost', 'delete']:
            if request_type == 'updatePost':
                request_type = 'update'

            typeid = int(type_id) if type_id else None
            response = self.method_switch(request, request_type, typeid, *args, **kwargs)
        else:
            response = self.page_404()

        return response

    def method_switch(self, request, request_type, type_id, *args, **kwargs):
        response = None

        static_methods = ['delete']
        request_types = ['add', 'update', 'delete']
        if request_type in request_types:
            method_name = request_type + "_to_cart"
            method = getattr(self, method_name)

            if method:
                response = method(request, type_id, *args, **kwargs)

        return response

    def add_to_cart(self, request, product_id, *args, **kwargs):
        json = {}
        quote_items = []

        if product_id:
            try:
                product, quote, quote_item, error_details = self.cart_utils.cart_item_create_update(int(product_id), request)

                if 'is_ajax' in request.POST and int(request.POST['is_ajax']) == 1:

                    if product and quote and quote_item:
                        json = {
                            'added_item': self.cart_utils.get_quote_items_list([quote_item], quote)[0],
                            'cart_items_html': self.cart_items_html_maker(request),
                            'cart_items_count': super().cart_items_context()['items_count']
                            # 'cart_items': quote_items,
                        }

                    if error_details:
                        json['error_details'] = error_details

                    if json:
                        quote_json = JsonResponse(json, content_type='application/json', safe=False)
                        return quote_json

                    return HttpResponseBadRequest()

                else:

                    if error_details:
                        for em in error_details:
                            messages.add_message(request, messages.ERROR, em['messages'].join('<br/>'))

                    redir = redirect('cart:checkout_cart')
                    redir.set_cookie('cartid', helper.make_bas64en_string(quote.token_id))

                    return redir

            except Exception as e:
                pass

        return HttpResponseBadRequest()

    def update_to_cart(self, request, quote_id=None, *args, **kwargs):
        from ..models import QuoteItem

        saved = False
        if request.POST['update_cart_action'] in ['update_qty', 'empty_cart']:
            if request.POST['update_cart_action'] == 'update_qty':
                qty_posts = helper.get_html_input_dict(request.POST, 'cart_qty')

                if qty_posts and isinstance(qty_posts, dict):
                    quote = self.cart_utils.get_or_create_quote(request=request, quote_id=None, create_quote=False)

                    if quote:
                        quote_items = QuoteItem.objects.filter(id__in=qty_posts.keys(), quote=quote).all()
                        if quote_items:
                            for qi in quote_items:
                                quote_item, error_details, handled_messages, saved, removed = self.cart_utils\
                                    .handle_quote_item_quantity(qi, float(qty_posts[str(qi.id)]), 'replace', request)

                                if error_details:
                                    if isinstance(error_details, list):
                                        for ed in error_details:
                                            if isinstance(ed, dict) and 'messages' in ed:
                                                if isinstance(ed['messages'], list):
                                                    messages.error(request, '<br/>'.join(ed['messages']))
                                                else:
                                                    messages.error(request, ed['messages'])
                                            else:
                                                messages.error(request, '<br/>'.join(error_details))
                                    elif isinstance(error_details, string):
                                        messages.error(request, error_details)
                                if handled_messages:
                                    if isinstance(handled_messages, list):
                                        messages.info(request, "<br/>".join(handled_messages))
                                    elif isinstance(handled_messages, string):
                                        messages.error(request, handled_messages)

                    if saved:
                        messages.success(request, "Cart is updated successfully!")
            else:
                quote = self.cart_utils.get_or_create_quote(request=request, quote_id=None, create_quote=False)
                quote.quoteitem_set.all().delete()

                messages.success(request, "Cart is cleared successfully!")

            redirect_url = request.GET.get('redirect_to', reverse('cart:checkout_cart'))
            return redirect(redirect_url)

        return HttpResponseBadRequest()

    # @staticmethod
    def delete_to_cart(self, request, quote_item_id, *args, **kwargs):
        from ..models import Quote, QuoteItem

        # response = None
        is_ajax = removed = False

        if 'is_ajax' in request.POST and int(request.POST['is_ajax']) == 1:
            is_ajax = True

        if quote_item_id and isinstance(quote_item_id, int):
            quote = self.cart_utils.get_or_create_quote(request=request, quote_id=None, create_quote=False)

            if quote:
                quote_item = QuoteItem.objects.filter(id=quote_item_id, quote=quote)
                if quote_item.exists():
                    quote_item.delete()
                    removed = True

                    if isinstance(quote, Quote):
                        quote_items, total_count, sub_total = self.cart_utils.get_current_quote_items_list(with_count_total=True)
                        quote.total_quantity = total_count
                        quote.sub_total = sub_total
                        quote.save()

                    if not is_ajax:
                        messages.success(request, "Item removed successfully!")

                else:
                    return HttpResponseBadRequest()

        if not is_ajax:
            redirect_url = request.GET.get('redirect_to', reverse('cart:checkout_cart'))
            return redirect(redirect_url)
        else:
            return True

    def cart_items_html_maker(self, request):
        from django.template import loader

        context = {'cart': super().cart_items_context()}
        template_name = 'frontend/parts/header/cart_items.html'
        content = loader.render_to_string(template_name, context, request)

        return content
