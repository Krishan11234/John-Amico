<?php
global $menus;

require_once ('common_functions.php');

$menus = array(
    'admin' => array(
        'dashboard' => array(
            'name' => 'Dashboard',
            'link' => 'index.php',
            'icon' => 'fa fa-home',
        ),
        'admin_controls' => array(
            'name' => 'Admin Controls',
            'icon' => 'fa fa-cogs',
            'children' => array(
                'manage_admins' => array(
                    'name' => 'Manage Admins',
                    'link' => 'admin.php',
                    'icon' => 'fa fa-user',
                ),
                'manage_emails' => array(
                    'name' => 'Manage Emails',
                    'link' => 'admin_email.php',
                    'icon' => 'fa fa-envelope-o',
                ),
                'member_logs' => array(
                    'name' => 'View Member Logs',
                    'link' => 'admin_logs.php',
                    'icon' => 'fa fa-th-list',
                ),
                'export_contest' => array(
                    'name' => 'Export Contest Spreadsheet',
                    'link' => 'contest_export.php',
                    'icon' => 'fa fa-cloud-download',
                ),
            ),
        ),
        'mlm_controls' => array(
            'name' => 'MLM Controls',
            'icon' => 'fa fa-cogs',
            'children' => array(
                'manage_mlm' => array(
                    'name' => 'Manage MLM',
                    'link' => 'mlm_pulldown.php',
                    'icon' => 'fa fa-sitemap',
                    'children' => array(
                        'manage_member' => array(
                            'name' => 'Manage Ambassador Pro',
                            'link' => 'members.php',
                            'icon' => 'fa fa-user',
                        ),
                        'manage_ambassador' => array(
                            'name' => 'Manage Ambassadors',
                            'link' => 'ambassadors.php',
                            'icon' => 'fa fa-user',
                        ),
                        'manage_ecs' => array(
                            'name' => 'Manage ECs',
                            'link' => 'ecs.php',
                            'icon' => 'fa fa-user-md',
                        ),
                        'manage_chapter' => array(
                            'name' => 'Manage Chapter',
                            'link' => 'chap.php',
                            'icon' => 'fa fa-folder',
                        ),
                        /*'customer_contacts' => array(
                            'name' => 'Customer Contacts',
                            'link' => 'contact_info.php',
                            'icon' => 'fa fa-users',
                        ),*/
                        /*'sales_records' => array(
                            'name' => 'Sales Records',
                            'link' => 'salesrecord.php',
                            'icon' => 'fa fa-line-chart',
                        ),*/
                        'customers_extra_fields' => array(
                            'name' => 'Customer Extra Fields',
                            'link' => 'extra_fields.php',
                            'icon' => 'fa fa-files-o',
                        ),
                        'service_systems_extra_fields' => array(
                            'name' => 'Service Systems Extra Fields',
                            'link' => 'extra_service_systems_fields.php',
                            'icon' => 'fa fa-files-o',
                        ),
                        'retail_systems_extra_fields' => array(
                            'name' => 'Retail Systems Extra Fields',
                            'link' => 'extra_retail_systems_fields.php',
                            'icon' => 'fa fa-files-o',
                        ),
                        'business_systems_extra_fields' => array(
                            'name' => 'Business Systems Extra Fields',
                            'link' => 'extra_business_systems_fields.php',
                            'icon' => 'fa fa-files-o',
                        ),
                    ),
                ),
                'manage_new_stylists' => array(
                    'name' => 'Manage New Stylists',
                    'link' => 'new_stylists.php',
                    'icon' => 'fa fa-user',
                ),
                'manage_commission' => array(
                    'name' => 'Manage Commission',
                    'link' => 'commision_rules.php',
                    'icon' => 'fa fa-percent',
                ),
                'export_tasks' => array(
                    'name' => 'Export Tasks',
                    'link' => 'mlm_export.php',
                    'icon' => 'fa fa-cloud-download',
                    'children' => array(

                    ),
                ),
            ),
        ),
        'ecommerce_controls' => array(
            'name' => 'E-Commerce Controls',
            'icon' => 'fa fa-cogs',
            'children' => array(
                'manage_shop' => array(
                    'name' => 'Manage Shop',
                    'link' => 'loginoutshopauto.php?login=1&fc=1',
                    'icon' => 'fa fa-shopping-bag',
                    'newtab' => true,
                ),
                /*'manage_customer_quick_order' => array(
                    'name' => 'Manage Customer / Quick Order',
                    'link' => 'customers.php',
                    'icon' => 'fa fa-cart-plus',
                ),*/
            ),
        ),
        'stw_management' => array(
            'name' => 'STW Management',
            'icon' => 'fa fa-align-left',
            'children' => array(
                'stw_reports' => array(
                    'name' => 'STW Reports',
                    'link' => 'stw_reports.php',
                    'icon' => 'fa fa-list-alt',
                ),
                'upload_stw' => array(
                    'name' => 'Upload STW File',
                    'link' => 'csv_uploader_parser.php',
                    'icon' => 'fa fa-upload',
                ),
            ),
        ),
        'member_services' => array(
            'name' => 'Member Services',
            'icon' => 'fa fa-user',
            'children' => array(
                'member_autoship_requests' => array(
                    'name' => 'Manage AutoShip Requests',
                    'link' => 'autoship_request.php',
                    'icon' => 'fa fa-book',
                    //'depend' => 'is_autoship_enable'
                ),
                'member_docs' => array(
                    'name' => 'Manage Member Docs',
                    'link' => 'uploadpdf.php',
                    'icon' => 'fa fa-book',
                ),
                'manage_newsletter' => array(
                    'name' => 'Manage News Letter',
                    'link' => 'newsletter.php',
                    'icon' => 'fa fa-envelope',
                ),
                'manage_news' => array(
                    'name' => 'Manage News',
                    'link' => 'news.php',
                    'icon' => 'fa fa-newspaper-o',
                ),
                'manage_stw_inquiries' => array(
                    'name' => 'Manage STW Inquiries',
                    'link' => 'inquire.php',
                    'icon' => 'fa fa-info-circle',
                ),
                'manage_cruise_inquiries' => array(
                    'name' => 'Manage Cruise Inquiries',
                    'link' => 'inquire2.php',
                    'icon' => 'fa fa-question-circle',
                ),
                'mlm_error_types' => array(
                    'name' => 'MLM Error Types',
                    'link' => 'mlm_errors.php',
                    'icon' => 'fa fa-exclamation-circle',
                ),
                'tool_link_manage' => array(
                    'name' => 'Tool Link Management',
                    'link' => 'tools.php',
                    'icon' => 'fa fa-cog',
                ),
                'video_link_manage' => array(
                    'name' => 'Video Link Management',
                    //'link' => 'videos.php',
                    'link' => 'videos_new.php',
                    'icon' => 'fa fa-video-camera',
                ),
                'per_member_commissions' => array(
                    'name' => 'Per Member Commissions',
                    'link' => 'per_member_commissions.php',
                    'icon' => 'fa fa-percent',
                ),
                'contact_organiser_email_requests' => array(
                    'name' => 'Contact Organiser Email Requests',
                    'link' => 'contact_organiser_email_requests.php',
                    'icon' => 'fa fa-envelope-o',
                ),
                'princess_cruise_contest' => array(
                    //'name' => 'Princess Cruise Contest Report',
                    'name' => 'Cancun Riu Palace Incentive Contest',
                    'link' => 'princess_cruise_contest.php',
                    'icon' => 'fa fa-envelope-o',
                ),
            ),
        ),
        'import_export_functions' => array(
            'name' => 'Import/Export Functions',
            'icon' => 'fa fa-retweet',
            'children' => array(
                'export_csvs' => array(
                    'name' => 'Export CSV\'s',
                    'link' => 'csv_pulldowns.php',
                    'icon' => 'fa fa-retweet',
                ),
                'sync_bwgold' => array(
                    'name' => 'Synchronize w/BWGold',
                    'link' => 'bw_sync.php',
                    'icon' => 'fa fa-refresh',
                ),
                'import_call_log' => array(
                    'name' => 'Import Call Log',
                    'link' => 'call_log.php',
                    'icon' => 'fa fa-phone-square',
                ),
                'update_ec_id' => array(
                    'name' => 'Update EC ID',
                    'link' => 'update_ec_id.php',
                    'icon' => 'fa fa-cloud-upload',
                ),
            ),
        ),
        'newsletter_management' => array(
            'name' => 'NewsLetter Management',
            'icon' => 'fa fa-newspaper-o',
            'children' => array(
                'newsletter_manage_new' => array(
                    'name' => 'Newsletter Management',
                    'link' => 'newsletter_new.php',
                    'icon' => 'fa fa-cloud-upload',
                ),
            ),
        ),
        'report' => array(
            'name' => 'Report',
            'icon' => 'fa fa-area-chart',
            'children' => array(
                /*'forum_admin' => array(
                    'name' => 'Discussion Forum Admin',
                    'link' => 'forum/admin/index.php',
                    'icon' => 'fa fa-users',
                ),
                'forum_posts' => array(
                    'name' => 'Manage Forum Posts',
                    'link' => 'fposts.php',
                    'icon' => 'fa fa-users',
                ),*/
                'ecco_report' => array(
                    'name' => 'ECCO Report',
                    'link' => 'ecco.php',
                    'icon' => 'fa fa-list-alt',
                ),
                'cpco_report' => array(
                    'name' => 'CPCO Report',
                    'link' => 'cpco.php',
                    'icon' => 'fa fa-area-chart',
                ),
            ),
        ),
        'bounced_emails' => array(
            'name' => 'Bounced Emails',
            'icon' => 'fa fa-envelope',
            'children' => array(
                'bounced_emails_upload' => array(
                    'name' => 'Upload CSV File',
                    'link' => 'bounced_emails_uploader.php',
                    'icon' => 'fa fa-cloud-upload',
                ),
                'bounced_emails_campaign_list' => array(
                    'name' => 'Bounced Email Campaigns',
                    'link' => 'bounced_emails_campaigns.php',
                    'icon' => 'fa fa-list-alt',
                ),
            ),
        ),
        'other_1' => array(
            'name' => 'Other',
            'icon' => 'fa fa-asterisk',
            'children' => array(
                'adjust_non_member_purchases' => array(
                    'name' => 'Adjust Non-Member Purchases',
                    //'link' => 'script4.php',
                    'link' => 'update_order_total.php',
                    'icon' => 'fa fa-credit-card-alt',
                ),
                'price_updater' => array(
                    'name' => 'Price Updater',
                    'link' => 'price_updater.php',
                    'icon' => 'fa fa-usd',
                ),
                'global_password' => array(
                    'name' => 'Set Global Password',
                    'link' => 'set_global_password.php',
                    'icon' => 'fa fa-key',
                ),
            ),
        ),
        /*
        'other_2' => array(
            'name' => 'Other',
            'icon' => 'fa fa-asterisk',
            'children' => array(
                'global_password' => array(
                    'name' => 'Set Global Password',
                    'link' => 'set_global_password.php',
                    'icon' => 'fa fa-key',
                ),
                'per_member_commissions' => array(
                    'name' => 'Per Member Commissions',
                    'link' => 'per_member_commissions.php',
                    'icon' => 'fa fa-percent',
                ),
            ),
        ),*/
    ),
    'member' => array(
        'member_dashboard' => array(
            'name' => 'Dashboard',
            'link' => 'index.php',
            'icon' => 'fa fa-home',
        ),
        /*'member_contact_organizer' => array(
            'name' => 'Contact Organizer',
            'icon' => 'fa fa-users',
            'children' => array(
                'member_contact_Organizer' => array(
                    'name' => 'Contact Organizer',
                    'link' => 'contact_organizer/contact_organizer.php',
                    'redirect_to' => 'contact_organizer.php',
                    'icon' => 'fa fa-list-ol',
                ),
                'member_customer_manage' => array(
                    'name' => 'Customer Management',
                    'link' => 'contact_organizer/customers.php',
                    'redirect_to' => 'customers.php',
                    'icon' => 'fa fa-users',
                ),
                'member_events_calender' => array(
                    'name' => 'Calender of Events',
                    'link' => 'contact_organizer/calender.php',
                    'redirect_to' => 'calender.php',
                    'icon' => 'fa fa-calendar-check-o',
                ),
                'member_task_list' => array(
                    'name' => 'Task List',
                    'link' => 'contact_organizer/task_list.php',
                    'redirect_to' => 'task_list.php',
                    'icon' => 'fa fa-tasks',
                ),
                'member_annotate_note' => array(
                    'name' => 'Annotate a Note',
                    'link' => 'contact_organizer/annotate_a_note_start.php',
                    'redirect_to' => 'notes.php?add=1',
                    'icon' => 'fa fa-sticky-note',
                ),
                'member_notes' => array(
                    'name' => 'Notes',
                    'link' => 'contact_organizer/notes.php',
                    'redirect_to' => 'notes.php',
                    'icon' => 'fa fa-th-list',
                ),
            ),
        ),
        'member_commission_structure' => array(
            'name' => 'Commission Structure',
            'icon' => 'fa fa-usd',
            'children' => array(
                'member_commission_overview' => array(
                    'name' => 'Overview',
                    'link' => 'commission_strucure/commission_structure.php',
                    'redirect_to' => 'commission_structure.php',
                    'icon' => 'fa fa-pie-chart',
                ),
                'member_commission_payout' => array(
                    'name' => 'Actual Commission Payout',
                    'link' => 'commission_strucure/payout.php',
                    'redirect_to' => 'payout.php',
                    'icon' => 'fa fa-usd',
                ),
                'member_commission_next_level' => array(
                    'name' => 'Next Level',
                    'link' => 'commission_strucure/next_level.php',
                    'redirect_to' => 'next_level.php',
                    'icon' => 'fa fa-step-forward',
                ),
            ),
        ),*/
        'member_ecomerce' => array(
            'name' => 'Ecommerce',
            'icon' => 'fa fa-shopping-cart',
            'children' => array(
                'member_quick_order' => array(
                    'name' => 'Quick Orders',
                    'link' => base_shop_url() . 'quick-order',
                    'icon' => 'fa fa-cart-plus',
                    'newtab' => true,
                    'stand_alone' => true,
                ),
                'member_goto_shop' => array(
                    'name' => 'Go to Shop',
                    'link' => base_shop_url(),
                    'icon' => 'fa fa-shopping-cart',
                    'newtab' => true,
                    'stand_alone' => true,
                ),
                'member_genealogy_invoice_report' => array(
                    'name' => 'View Invoice Report',
                    'link' => 'genealogy/invoice_report.php',
                    'redirect_to' => 'invoice_report.php',
                    'icon' => 'fa fa-bar-chart-o',
                ),
                'member_autoship_requests' => array(
                    'name' => 'Autoship Requests',
                    'link' => 'autoship_request.php',
                    'icon' => 'fa fa-book',
                    //'depend' => 'is_autoship_enable'
                ),
                'member_credit_cards' => array(
                    'name' => 'Mange Credit Cards',
                    'link' => 'manage_cards.php',
                    'icon' => 'fa fa-cc',
                ),
                'member_subscriptions' => array(
                    'name' => 'Your Subscriptions',
                    'link' => 'manage_subscriptions.php',
                    'icon' => 'fa fa-refresh',
                ),
                /*'member_featured_products' => array(
                    'name' => 'Featured Products',
                    'link' => 'featured_products.php',
                    'icon' => 'fa fa-book',
                    'depend' => 'is_featured_enable'
                ),*/
                /*'member_shop_price_level' => array(
                    'name' => 'Set Shop Price Level',
                    'link' => 'set_shop_price_level.php',
                    'icon' => 'fa fa-usd',
                ),*/
            ),
        ),
        'member_genealogy_main' => array(
            'name' => 'Genealogy',
            'icon' => 'fa fa-tree',
            'children' => array(
                'member_contact_Organizer' => array(
                    'name' => 'Contact Organizer',
                    //'link' => 'contact_organizer/contact_organizer.php',
                    'link' => 'contact_organizer.php',
                    'redirect_to' => 'contact_organizer.php',
                    'icon' => 'fa fa-list-ol',
                ),
                /*'member_genealogy' => array(
                    'name' => 'View Genealogy',
                    'icon' => 'fa fa-table',
                    'link' => 'genealogy_menu.php',
                    'children' => array(
                        'member_genealogy_view' => array(
                            'name' => 'View Genealogy',
                            'link' => 'genealogy/genealogy_user.php',
                            'redirect_to' => 'genealogy_user.php',
                            'icon' => 'fa fa-table',
                        ),
                        'member_genealogy_comm_owner' => array(
                            'name' => 'Communicate With Site Owner',
                            'link' => 'mailto:john.amicojr@johnamico.com',
                            'icon' => 'fa fa-envelope',
                            'stand_alone' => true,
                        ),
                        'member_genealogy_comm_member' => array(
                            'name' => 'Communicate With Other Members',
                            'link' => 'genealogy/communicate.php',
                            'redirect_to' => 'communicate.php',
                            'icon' => 'fa fa-envelope',
                        ),
                        'member_genealogy_monthly_earning' => array(
                            'name' => 'Monthly Earnings Summary',
                            'link' => 'genealogy/view_monthly_summary.php',
                            'redirect_to' => 'view_monthly_summary.php',
                            'icon' => 'fa fa-usd',
                        ),
                    ),
                ),*/
                'member_view_tree' => array(
                    'name' => 'Tree View',
                    'link' => 'show_family_tree.php',
                    'redirect_to' => 'show_family_tree.php',
                    'icon' => 'fa fa-align-left',
                ),
                'member_genealogy_monthly_earning' => array(
                    'name' => 'Monthly Earnings Summary',
                    //'link' => 'genealogy/view_monthly_summary.php',
                    'link' => 'view_monthly_summary.php',
                    'redirect_to' => 'view_monthly_summary.php',
                    'icon' => 'fa fa-usd',
                ),
                'member_commission_overview' => array(
                    'name' => 'Commission Structure',
                    //'link' => 'commission_strucure/commission_structure.php',
                    'link' => 'commission_structure.php',
                    'redirect_to' => 'commission_structure.php',
                    'icon' => 'fa fa-pie-chart',
                ),
            ),
        ),
        'member_contests' => array(
            'name' => 'Contests',
            'icon' => 'fa fa-gift',
            'children' => array(
                /*'member_contests' => array(
                    'name' => 'Contests',
                    'link' => '#',
                    'icon' => 'fa fa-gift',
                ),*/
                'member_princess_cruise_incentive_contest' => array(
                    //'name' => 'Princess Cruise Incentive Contest',
                    'name' => 'Cancun Riu Palace Incentive Contest',
                    'link' => 'princess_cruise_incentive_contest.php',
                    'icon' => 'fa fa-gift',
                ),
            ),
        ),
        'member_learning_tools' => array(
            'name' => 'Learning and Building Tools',
            'icon' => 'fa fa-cogs',
            'children' => array(
                'member_news' => array(
                    'name' => 'News',
                    'link' => 'important_docs/news.php',
                    'redirect_to' => 'news.php',
                    'icon' => 'fa fa-newspaper-o',
                ),
                'member_download_docs' => array(
                    'name' => 'Download Important Documents',
                    //'link' => 'important_docs/imp_docs.php',
                    'link' => 'imp_docs.php',
                    'redirect_to' => 'imp_docs.php',
                    'icon' => 'fa fa-book',
                ),
                'member_videos' => array(
                    'name' => 'Video Education',
                    //'link' => 'videos.php',
                    'link' => 'videos_new.php',
                    'icon' => 'fa fa-video-camera',
                ),
                'member_tools' => array(
                    'name' => 'Helpful Business Building Tools',
                    'link' => 'tools.php',
                    'icon' => 'fa fa-cog',
                ),
                'member_cruise_card' => array(
                    'name' => 'Your Cruise Score Card',
                    //'link' => 'important_docs/scorecard.php',
                    'link' => 'scorecard.php',
                    'redirect_to' => 'scorecard.php',
                    'icon' => 'fa fa-usd',
                ),
                'member_forum' => array(
                    'name' => 'Forum',
                    'link' => 'http://johnamico.proboards.com/',
                    'icon' => 'fa fa-users',
                    'newtab' => true,
                    'stand_alone' => true,
                ),
            ),
        ),
        /*'member_media_artist' => array(
            'name' => 'Media Artist Program',
            'link' => 'media_artist_menu.php',
            'icon' => 'fa fa-paint-brush',
            'children' => array(
                'member_media_artist_page' => array(
                    'name' => 'Media Artist Program',
                    'link' => 'media_artist_menu.php',
                    'icon' => 'fa fa-paint-brush',
                ),
            ),
            *//*'children' => array(
                'buy_media_artist_program' => array(
                    'name' => 'Buy Media Artist Program',
                    'link' => '../mediaartist/stylist',
                    'icon' => 'fa fa-cart-plus',
                    'newtab' => true,
                ),
                'login_media_artist_program' => array(
                    'name' => 'Login to Media Artist Program',
                    'link' => '#',
                    'icon' => 'fa fa-paint-brush',
                    'newtab' => true,
                ),
            ),*//*
        ),*/
        'member_account_settings' => array(
            'name' => 'Account Settings',
            'icon' => 'fa fa-user',
            'children' => array(
                'member_my_profile' => array(
                    'name' => 'My Profile',
                    'link' => 'update_my_info.php',
                    'redirect_to' => 'update_my_info.php',
                    'icon' => 'fa fa-user',
                ),
            ),
        ),
        'member_my_sites' => array(
            'name' => 'My Sites',
            'icon' => 'fa fa-globe',
            'children' => array(
                'member_my_sites' => array(
                    'name' => 'My Sites',
                    'link' => 'my_sites.php',
                    'icon' => 'fa fa-globe',
                ),
            ),
        ),
    ),
);

/*if( function_exists('is_display_admin_newStylist_page') ) {
    if( is_display_admin_newStylist_page() === false ) {
        unset($menus['admin']['mlm_controls']['children']['manage_new_stylists']);
    }
}*/
if( function_exists('is_autoship_enable') ) {
//     if( is_autoship_enable() === false ) {
//         unset($menus['admin']['member_services']['children']['member_autoship_requests']);
//         unset($menus['member']['member_ecomerce']['children']['member_autoship_requests']);
//     }
}

//$link_parents = get_menu_link_parents($menus);
//echo '<pre>'; print_r($link_parents); die();

function get_menus() {
    global $menus;

    if( !is_display_page() ) {
        $displayable_items = array('dashboard', 'admin_controls');

        if(!empty($menus)) {
            foreach ($menus as $menu_key => $menu) {
                if(is_array($menu)) {
                    foreach ($menu as $menu_items_key => $menu_items) {

                        if( !empty($menu_items['depend']) ) {
                            if( function_exists($menu_items['depend']) ) {
                                if( !$menu_items['depend']() ) {
                                    unset( $menus[$menu_key][$menu_items_key] );
                                    continue;
                                }
                            }
                        }

                        if( in_array($menu_items_key, array('member_media_artist')) && !is_mediaArtist_enabled() ) {
                            //echo '<pre>'; var_dump( $menu_items_key ); die();

                            unset( $menus[$menu_key][$menu_items_key] );
                            continue;
                        }

                        if(!empty($menu_items['link'])) {
                            $link = ( !empty($menu_items['redirect_to']) ? $menu_items['redirect_to'] : $menu_items['link'] );
                            $link_parents[ $link ][] = $menu_items_key;
                        }

                        if(!empty($menu_items['children']) && is_array($menu_items['children'])) {
                            foreach ($menu_items['children'] as $menu_item_key => $menu_item_sub1) {
                                //echo '<pre>'; print_r($menu_items_key); die();
                                //echo '<pre>'; var_dump( !in_array($menu_item_key, $displayable_items)  ); die();

                                //echo '<pre>'; var_dump( $menu_item_sub1  ); die();

                                if( !empty($menu_item_sub1['depend']) ) {
                                    if( function_exists($menu_item_sub1['depend']) ) {
                                        if( !$menu_item_sub1['depend']() ) {
                                            unset( $menus[$menu_key][$menu_items_key]['children'][$menu_item_key] );
                                            continue;
                                        }
                                    }
                                }


                                if( !in_array($menu_items_key, $displayable_items) ) {
                                    $menus[$menu_key][$menu_items_key]['children'][$menu_item_key]['link'] = '#';
                                }
                                //echo '<pre>'; print_r($menus[$menu_key][$menu_items_key]); die();

                                if(!empty($menu_item_sub1['children']) && is_array($menu_item_sub1['children'])) {
                                    foreach ($menu_item_sub1['children'] as $menu_item_sub1_item_key => $menu_item_sub1_item)
                                    {
                                        if( !empty($menu_item_sub1_item['depend']) ) {
                                            if( function_exists($menu_item_sub1_item['depend']) ) {
                                                if( !$menu_item_sub1_item['depend']() ) {
                                                    unset( $menus[$menu_key][$menu_items_key]['children'][$menu_item_key]['children'][$menu_item_sub1_item_key] );
                                                    continue;
                                                }
                                            }
                                        }
                                        if( !in_array($menu_items_key, $displayable_items) ) {
                                            $menus[$menu_key][$menu_items_key]['children'][$menu_item_key]['children'][$menu_item_sub1_item_key]['link'] = '#';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    //echo '<pre>'; print_r($menus); die();

    return $menus;
}
function get_menu_link_parents($menus) {
    $link_parents = array();

    if(!empty($menus)) {
        foreach ($menus as $menu) {
            if(is_array($menu)) {
                foreach ($menu as $menu_items_key => $menu_items) {

                    if(!empty($menu_items['link'])) {
                        $link = ( !empty($menu_items['redirect_to']) ? $menu_items['redirect_to'] : $menu_items['link'] );
                        $link_parents[ $link ][] = $menu_items_key;
                    }

                    if(!empty($menu_items['children']) && is_array($menu_items['children'])) {
                        foreach ($menu_items['children'] as $menu_item_key => $menu_item_sub1) {
                            //echo '<pre>'; print_r($menu_item_sub1); die();

                            if(!empty($menu_item_sub1['link'])) {
                                $link = ( !empty($menu_item_sub1['redirect_to']) ? $menu_item_sub1['redirect_to'] : $menu_item_sub1['link'] );
                                $link_parents[ $link ][] = $menu_items_key;
                                $link_parents[ $link ][] = $menu_item_key;
                            }

                            if(!empty($menu_item_sub1['children']) && is_array($menu_item_sub1['children'])) {
                                foreach ($menu_item_sub1['children'] as $menu_item_sub1_item_key => $menu_item_sub1_item) {
                                    if(!empty($menu_item_sub1_item['link'])) {
                                        $link = ( !empty($menu_item_sub1_item['redirect_to']) ? $menu_item_sub1_item['redirect_to'] : $menu_item_sub1_item['link'] );
                                        $link_parents[ $link ][] = $menu_item_key;
                                        $link_parents[ $link ][] = $menu_items_key;
                                        $link_parents[ $link ][] = $menu_item_sub1_item_key;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    //echo '<pre>'; print_r($link_parents); die();

    return $link_parents;
}
function get_active_item_link() {
    //echo '<pre>'; print_r($_SERVER); die();

    global $admin_path, $member_path;

    $link = 'index.php';

    if(!empty($_SERVER['SCRIPT_NAME'])) {
        $link = str_replace(array('/'.$admin_path.'/', '/'.$member_path.'/'), '', $_SERVER['SCRIPT_NAME']);
    }

    //echo '<pre>'; print_r($link); die();

    return $link;
}