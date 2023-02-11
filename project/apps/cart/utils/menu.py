from django.urls import reverse
from ..utils import helper


jacustom_urls = {
    'jamember': {
        'login': '//localhost:1338/jamember/',
        'panel': '//localhost:1338/jamember/',
        'logout': '//localhost:1338/jamember/logout.php',
    },
}


class Menu:

    def get_all_autoload_menu_types(self):
        from ..models import MenuType, MenuCategory, StaticMenuItem
        from ..signals import autoloaded_menus

        menus = {}

        for menuType in MenuType.objects.filter(is_active=True, autoload=True).all():
            menus[menuType.machine_name] = {
                'menu_info': menuType,
                'categories': MenuCategory.objects.filter(menu_type=menuType, is_active=True).order_by('order'),
            }

            if not menuType.machine_name == 'top_menu':
                menus[menuType.machine_name]['static_links'] = StaticMenuItem.objects.filter(
                    menu_type=menuType, is_active=True).order_by('order')
            else:
                menus[menuType.machine_name]['static_links'] = [
                    {'label': 'Main Home', 'get_absolute_url': reverse('cart:home'), 'css_class': '', },
                ]
                if helper.is_customer_logged_in() and not helper.is_professional_logged_in():
                    menus[menuType.machine_name]['static_links'].append(
                        {'label': 'Customer Panel', 'get_absolute_url': reverse('cart:account_dashboard'),
                         'css_class': '', }
                    )
                    menus[menuType.machine_name]['static_links'].append(
                        {'label': 'Logout', 'get_absolute_url': reverse('cart:account_logout'), 'css_class': '', }
                    )
                if helper.is_professional_logged_in() and not helper.is_customer_logged_in():
                    menus[menuType.machine_name]['static_links'].append(
                        {'label': 'Professional Panel', 'get_absolute_url': jacustom_urls['jamember']['panel'],
                         'css_class': '', }
                    )
                    menus[menuType.machine_name]['static_links'].append(
                        {'label': 'Logout', 'get_absolute_url': reverse('cart:account_logout'), 'css_class': '', }
                    )
                if not helper.is_user_types_logged_in(condition_type='or'):
                    menus[menuType.machine_name]['static_links'].append(
                        {'label': 'Professional Login', 'get_absolute_url': jacustom_urls['jamember']['login'],
                         'css_class': '', 'external_url': True}
                    )
                    menus[menuType.machine_name]['static_links'].append(
                        {'label': 'Customer Login', 'get_absolute_url': reverse('cart:account_login'),
                         'css_class': '', }
                    )

        signal_altered_menus = autoloaded_menus.recurring_send(sender=self.__class__, menus=menus)

        if signal_altered_menus and len(signal_altered_menus) == 2:
            _, menus = signal_altered_menus

        return menus

    def get_single_menu(self, menu_code_name):
        from ..models import MenuType, MenuCategory, StaticMenuItem
        from ..signals import single_loaded_menu

        menu_result = {}

        if menu_code_name:
            menu_q = MenuType.objects.filter(machine_name=menu_code_name, is_active=True)
            if menu_q.exists():
                menuType = menu_q.first()

                menu_result = {
                    'menu_info': menuType,
                    'categories': MenuCategory.objects.filter(menu_type=menuType, is_active=True).order_by('order'),
                    'static_links': StaticMenuItem.objects.filter(menu_type=menuType, is_active=True).order_by('order')
                }

        signal_altered_menu = single_loaded_menu.recurring_send(sender=self.__class__, menu_code=menu_code_name, menu_result=menu_result)

        if signal_altered_menu and len(signal_altered_menu) == 2:
            _, menu_result = signal_altered_menu

        return menu_result
