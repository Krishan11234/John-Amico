<?php

date_default_timezone_set('America/New_York');

if( session_status() === PHP_SESSION_NONE ) {
    session_start();
}

global $base_url, $base_path, $admin_path;

include_once(dirname(__FILE__) . '/config.php');

$host = $_SERVER['HTTP_HOST'];

$admin_path = 'jaadmin';
$member_path = 'jamember';
$shop_path = '';
$library_path = 'library';
$theme_assets_path = 'theme_assets';
$common_files__folder = '/common_files';


//$_custom_encryption_key = file_get_contents('../encryption.key');


if(in_array($host, array('sharethewealth.localhost.com'))) {
    //$admin_path = 'admin_renovated';
    //$admin_path = 'admin';
    //$member_path = 'jamember';
}


//$base_url = base_url();
//$base_path = base_path();
//echo '<pre>'; print_r($base_url); die();

if( !function_exists('base_url') ) {
    function base_url($forceHttps=false) {
        /*$https = ( !empty($_SERVER['https']) && ($_SERVER['HTTPS'] != 'off')) ? 'https' : 'http';
        return sprintf(
            "%s://%s",
            $https,
            $_SERVER['HTTP_HOST']
        );*/
        $scheme = '';

        if(!empty($_SERVER['HTTPS']) ) {
            if($_SERVER['HTTPS'] == 'on') {
                $scheme = 'https:';
            } else {
                $scheme = 'http:';
            }
        } elseif (!empty($_SERVER['REQUEST_SCHEME'])) {
            $scheme = "{$_SERVER['REQUEST_SCHEME']}:";
        }
        if($forceHttps) {
            $scheme = 'https:';
        }
        if(is_in_local()) {
            $scheme = 'http:';
        }
        $string = $scheme . sprintf( "//%s", $_SERVER['HTTP_HOST'] );

        return $string;
    }
}

if( !function_exists('base_admin_url') ) {
    function base_admin_url() {
        global $admin_path;

        //echo '<pre>'; print_r($_SERVER); die();
        return base_url() . '/' . $admin_path;
    }
}

if( !function_exists('base_member_url') ) {
    function base_member_url() {
        global $member_path;

        //echo '<pre>'; print_r($_SERVER); die();
        return base_url() . '/' . $member_path;
    }
}

if( !function_exists('base_cdn_url') ) {
    function base_cdn_url() {

        $base = base_url();
        $cdn = str_replace(array('http://www.', 'http://', 'http://store.', 'http://shop.'), 'http://cdn.', $base);
        $cdn = str_replace(array('https://www.', 'https://', 'https://store.', 'https://shop.'), 'https://cdn.', $cdn);

        return $cdn ;
    }
}

if( !function_exists('base_shop_url') ) {
    function base_shop_url($loadHttps=false) {
        /*global $shop_path;
        return base_url($loadHttps) . '/' . $shop_path;*/
        $scheme = 'http:';
        if($loadHttps) {
            $scheme = 'https:';
        }
        return $scheme."//".SHOP_URL_WITHOUT_SCHEME."/";
    }
}

if( !function_exists('base_shop_member_order_view_url') ) {
    function base_shop_member_order_view_url() {
        global $shop_path;

        //echo '<pre>'; print_r($_SERVER); die();
        //return base_url() . '/' . $shop_path . "jaorder/order/set/id/";
        return base_shop_url() . "jaorder/order/view/id/";
    }
}

if( !function_exists('base_path') ) {
    function base_path() {
        return $_SERVER["DOCUMENT_ROOT"];
    }
}

if( !function_exists('base_admin_path') ) {
    function base_admin_path() {
        global $admin_path;

        return base_path() . '/' . $admin_path;
    }
}

if( !function_exists('base_member_path') ) {
    function base_member_path() {
        global $member_path;

        return base_path() . '/' . $member_path;
    }
}

if( !function_exists('base_admin_temp_path') ) {
    function base_admin_temp_path() {
        create_temporary_path( base_admin_path() );
        return base_admin_path() . '/temporary';
    }
}

if( !function_exists('base_member_temp_path') ) {
    function base_member_temp_path() {
        create_temporary_path( base_member_path() );
        return base_member_path() . '/temporary';
    }
}

if( !function_exists('base_shop_path') ) {
    function base_shop_path() {
        global $shop_path;

        return base_path() . '/' . $shop_path;
    }
}

if( !function_exists('base_library_path') ) {
    function base_library_path() {
        global $library_path, $common_files__folder;

        return base_path() . "/$common_files__folder/"  . $library_path;
    }
}

if( !function_exists('base_library_url') ) {
    function base_library_url() {
        global $library_path, $common_files__folder;

        return base_url() . "$common_files__folder/"  . $library_path;
    }
}

if( !function_exists('base_theme_assets_path') ) {
    function base_theme_assets_path() {
        global $theme_assets_path, $common_files__folder;

        return base_path() . "/$common_files__folder/" . $theme_assets_path;
    }
}

if( !function_exists('base_theme_assets_url') ) {
    function base_theme_assets_url() {
        global $theme_assets_path, $common_files__folder;

        $baseUrl = base_url();
        if( defined('CDN_ENABLED') && CDN_ENABLED ) {
            $baseUrl = base_cdn_url();
        }

        return $baseUrl . "$common_files__folder/" . $theme_assets_path;
    }
}

if( !function_exists('base_js_path') ) {
    function base_js_path() {
        global $common_files__folder;
        return base_path() . "/$common_files__folder/js";
    }
}

if( !function_exists('base_js_url') ) {
    function base_js_url() {
        global $common_files__folder;
        return base_url() . "$common_files__folder/js";
    }
}

if( !function_exists('base_images_path') ) {
    function base_images_path() {
        global $common_files__folder;
        return base_path() . "/$common_files__folder/images";
    }
}

if( !function_exists('base_images_url') ) {
    function base_images_url() {
        global $common_files__folder;
        return base_url() . "$common_files__folder/images";
    }
}

if( !function_exists('create_temporary_path') ) {
    function create_temporary_path($root_path) {
        if( !empty($root_path) ) {

            if( !is_dir( $root_path . "/temporary" ) ) {
                mkdir($root_path . "/temporary");
            }
        }
    }
}

if( !function_exists('currentPageURL') ) {
    function currentPageURL() {

        //$pageURL = $_SERVER['REQUEST_SCHEME']."://";
        $pageURL = "//";
        //$scheme = !empty($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';

        if( filter_var($_SERVER["REQUEST_URI"], FILTER_VALIDATE_URL) ) {
            $requestUriParts = parse_url($_SERVER["REQUEST_URI"]);
            $requestUri = trim($requestUriParts['path'], '/') . '/';
        } else {
            $requestUri = trim($_SERVER["REQUEST_URI"], '/') . '/';
        }

        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"] ."/". $requestUri;
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] ."/".$requestUri;
        }
        $pageURL = (!empty($scheme) ? "{$scheme}:" : '') . $pageURL;

        //echo '<per>'; var_dump( $pageURL, $host, $_SERVER["REQUEST_URI"], $_SERVER ); die();

        return $pageURL;
    }
}

if( !function_exists('currentPanel') ) {
    function currentPanel() {

        if( strpos( currentPageURL(), base_admin_url() ) !== false ) {
            return 'admin';
        }
        if( strpos( currentPageURL(), base_member_path() ) !== false ) {
            return 'member';
        }

        return false;
    }
}

if( !function_exists('session_unregister') ) {
    function session_unregister($key) {
        if(!empty($key) && isset($_SESSION[$key]) ) {
            unset($_SESSION[$key]);
        }
    }
}

if( !function_exists('filesize_formatted') ) {
    function filesize_formatted($size) {
        if(!empty($size)) {
            $units = array('Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
            $power = $size > 0 ? floor(log($size, 1024)) : 0;
            return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
        }
        elseif( ($size == 0) ) {
            return '0 Bytes';
        }
    }
}

if( !function_exists('is_session_started') ) {
    function is_session_started() {
        if (php_sapi_name() !== 'cli') {
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
            }
            else {
                return session_id() === '' ? FALSE : TRUE;
            }
        }
        return FALSE;
    }
}

if( !function_exists('session_is_registered') ) {
    function session_is_registered($element) {
        if( isset($_SESSION[$element]) ) {
            return true;
        }
        return FALSE;
    }
}

if( !function_exists('session_register') ) {
    function session_register($element) {
        global $$element;

        if( isset($$element) ) {
            if(!is_session_started()) {
                session_start();
            }

            $_SESSION[$element] = $$element;
        }
    }
}

if( !function_exists('is_admin_logged_in') ) {
    function is_admin_logged_in() {
        if( session_status() == PHP_SESSION_NONE ) {
            session_start();
        }
        $logged_in = false;

        if (!empty($_SESSION['admin']['is_admin'])) {
            if (!empty($_SESSION['admin']['current_session']) or !empty($_SESSION['admin']['session_user']) or !empty($_SESSION['admin']['session_key'])) {
                if ($_SESSION['admin']['current_session'] == $_SESSION['admin']['session_user'] . "=" . $_SESSION['admin']['session_key']) {
                    $logged_in = true;
                }
            }
        }

        return $logged_in;
    }
}

if( !function_exists('is_member_logged_in') ) {
    function is_member_logged_in() {
        if( session_status() == PHP_SESSION_NONE ) {
            session_start();
        }
        $logged_in = false;

        if (!empty($_SESSION['member']['is_member'])) {
            if (!empty($_SESSION['member']['current_session']) or !empty($_SESSION['member']['session_user']) or !empty($_SESSION['member']['session_key'])) {
                if ($_SESSION['member']['current_session'] == $_SESSION['member']['session_user'] . "=" . $_SESSION['member']['session_key']) {
                    $logged_in = true;
                }
            }
        }

        if( empty($_COOKIE['membid']) ) {
            $logged_in = false;
            unset($_SESSION['member']);
        }

        return $logged_in;
    }
}

if( !function_exists('is_member_a_stylist') ) {
    function is_member_a_stylist() {
        if( !is_member_logged_in() ) {
            return false;
        }

        if( !class_exists('Mage') ) {
            require_once( base_shop_path() . '/app/Mage.php' );
            Mage::app();
        }

        if(class_exists('Mage')) {
            $loggedIn = Mage::helper('mvijastylist')->getLoggedInMemberIsStylist($_SESSION['member']['session_user']);

            //echo '<pre>'; var_dump($loggedIn); die();
            return $loggedIn;
        }

        return false;
    }
}

if( !function_exists('is_member_an_ambassador') ) {
    function is_amico_an_ambassador($amico_id) {
        if( empty($amico_id) ) {
            return false;
        }
        global $conn;

        $sql = " SELECT mtype FROM tbl_member WHERE amico_id='$amico_id' AND bit_active=1 LIMIT 1";
        $query = mysqli_query($conn, $sql);
        $rows = mysqli_num_rows($query);

        if( mysqli_num_rows($query) > 0 ) {
            list($mtype) = mysqli_fetch_row($query);

            if($mtype == 'a') {
                return true;
            }
        }

        return false;
    }
}

if( !function_exists('is_mediaArtist_enabled') ) {
    function is_mediaArtist_enabled() {
        if( !is_member_logged_in() ) {
            return false;
        }

        if( !class_exists('Mage') ) {
            require_once( base_shop_path() . '/app/Mage.php' );
            Mage::app();
        }

        if(class_exists('Mage')) {
            $enabled = Mage::helper('mvijastylist')->getIsMediaArtistEnabled();
            //echo '<pre>'; var_dump($enabled); die();
            return $enabled;
        }

        return false;
    }
}

if( !function_exists('is_autoship_enable') ) {
    function is_autoship_enable() {
        if( !class_exists('Mage') ) {
            require_once( base_shop_path() . '/app/Mage.php' );
            Mage::app();
        }

        if(class_exists('Mage')) {
            $enabled = Mage::helper('core')->isModuleEnabled('Mvisolutions_Jaautoship');
            if( $enabled ) {
                $enabled = Mage::helper('mvijaautoship')->isAutoshipEnabledOnOutSideMagento();
                //echo '<pre>'; var_dump($enabled); die();
            }
            //echo '<pre>'; var_dump($enabled); die();

            return $enabled;
        }

        return false;
    }
}

if( !function_exists('is_featured_enable') ) {
    function is_featured_enable() {
        if( !class_exists('Mage') ) {
            require_once( base_shop_path() . '/app/Mage.php' );
            Mage::app();
        }

        if(class_exists('Mage')) {
            $enabled = Mage::helper('core')->isModuleEnabled('Mvisolutions_Featuredproducts');
            if( $enabled ) {
                $enabled = Mage::helper('jafeaturedprods')->isEnabled();
                //echo '<pre>'; var_dump($enabled); die();
            }
            //echo '<pre>'; var_dump($enabled); die();

            return $enabled;
        }

        return false;
    }
}

if( !function_exists('is_vipconsumer_enable') ) {
    function is_vipconsumer_enable() {
        $enabled = false;

        if( !class_exists('Mage') ) {
            require_once( base_shop_path() . '/app/Mage.php' );
            Mage::app();
        }

        if(class_exists('Mage')) {
            $vipenabled = Mage::helper('core')->isModuleEnabled('Mvisolutions_Javipconsumer');
            $recurenabled = Mage::helper('core')->isModuleEnabled('Indies_Recurringandrentalpayments');

            if( $vipenabled && $recurenabled )
            {
                $vipenabled = Mage::helper('javipconsumer')->isEnabled();
                $recurenabled = Mage::helper('recurringandrentalpayments')->isEnabled();

                $enabled = $vipenabled && $recurenabled;
                //echo '<pre>'; var_dump($enabled); die();
            }
            //echo '<pre>'; var_dump($enabled); die();

            return $enabled;
        }

        return false;
    }
}

if( !function_exists('get_vipconsumer_subscription_plan') ) {
    function get_vipconsumer_subscription_plan() {
        $planId = false;

        if( !class_exists('Mage') ) {
            require_once( base_shop_path() . '/app/Mage.php' );
            Mage::app();
        }

        if(class_exists('Mage')) {
            if( is_vipconsumer_enable() )
            {
                $planId = Mage::helper('javipconsumer')->getVIPSubscriptionPlan();
                //echo '<pre>'; var_dump($enabled); die();
            }
            //echo '<pre>'; var_dump($enabled); die();

            return $planId;
        }

        return false;
    }
}

if( !function_exists('is_member_a_media_artist') ) {
    function is_member_a_media_artist() {
        if( !is_member_logged_in() ) {
            return false;
        }

        if( !class_exists('Mage') ) {
            require_once( base_shop_path() . '/app/Mage.php' );
            Mage::app();
        }

        if(class_exists('Mage')) {
            $loggedIn = Mage::getModel('mvijastylist/mediaartist_mediaartist')->validateMediaArtistByAmicoId($_SESSION['member']['session_user']);

            //echo '<pre>'; var_dump($loggedIn); die();
            return $loggedIn;
        }

        return false;
    }
}

if( !function_exists('is_mageways_opabs_customerpricing_enabled') ) {
    function is_mageways_opabs_customerpricing_enabled() {

        if( !class_exists('Mage') ) {
            require_once( base_shop_path() . '/app/Mage.php' );
            Mage::app();
        }

        if(class_exists('Mage')) {
            $helper = Mage::helper('optionsabsoluteprice');
            if( !empty($helper) ) {
                return $helper->getIsCustomerGroupEnabled();
            }
        }

        return false;
    }
}

if( !function_exists('get_commissionable_order_total') ) {
    function get_commissionable_order_total($order_id, $order_total, $original_order_total)
    {
        if(empty($order_id)) { return false; }
        global $conn;

        $useMagento = true;

        $sql = "SELECT op.products_model, (op.final_price * op.products_quantity) as final_price, tcr.* FROM orders_products op LEFT JOIN tbl_commision_rule tcr ON op.products_model = tcr.str_commision_rule WHERE op.orders_id = '$order_id'";

        if( $useMagento ) {
            $sql = "SELECT IFNULL(oi.sku, '') as sku, 
                        ({$order_total} * ((oi.row_total_incl_tax-oi.discount_amount)/{$original_order_total})) as final_price, tcr.*
                        FROM " . MAGENTO_TABLE_PREFIX . "sales_flat_order_item AS oi
                        LEFT JOIN tbl_commision_rule AS tcr ON IFNULL(oi.sku, '') = tcr.str_commision_rule
                        WHERE oi.order_id = '{$order_id}'
                ";
        }

        $prod_res = mysqli_query($conn,$sql);
        echo mysqli_error($conn);

        if(mysqli_num_rows($prod_res) > 0) {
            $commissionable = 0;
            $amount = 0;

            while($prdinfo = mysqli_fetch_array($prod_res)) {
                if(!empty($prdinfo['int_commision_rule_id'])) {
                    if($prdinfo['int_value'] <= 0) {
                        continue;
                    }
                    else if($prdinfo['bit_percentage'] == 1) {
                        $commissionable += round(($prdinfo['int_value']/100)*$prdinfo['final_price'], 2);
                    } else {
                        $commissionable += $prdinfo['int_value'];
                    }
                } else {
                    $commissionable += $prdinfo['final_price'];
                }

                $amount += $prdinfo['final_price'];
            }

            if(is_numeric($order_total)) {
                $amount = ($amount > $order_total) ? $order_total : $amount;
                $commissionable = ($commissionable > $order_total) ? $order_total : $commissionable;
            }

            return array('amount' => $amount, 'commissionable' => $commissionable );
        }

        return false;
    }
}

if( !function_exists('const_contact_query') ) {
    function const_contact_query($email) {
        global $common_files__folder;

        $cc_id = 0;
        require_once( base_path() . "/$common_files__folder/Constant_contact/class.cc.php");

        $cc = new cc('amicojohn', 'hair1234care');
        $contact = $cc->query_contacts($email);

        if($contact){ $cc_id = $contact['id'];}

        return $cc_id;
    }
}

if( !function_exists('const_contact_create') ) {
    function const_contact_create($email, $first_name, $last_name) {
        global $common_files__folder;

        require_once( base_path() . "/$common_files__folder/Constant_contact/class.cc.php");

        // Set your Constant Contact account username and password below
        $cc = new cc('amicojohn', 'hair1234care');
        $contact_list = 6;
        $extra_fields = array(
            'FirstName' => $first_name,
            'LastName' => $last_name
        );

        // create the contact
        $cc->create_contact($email, $contact_list, $extra_fields);
    }
}

if( !function_exists('const_contact_update') ) {
    function const_contact_update($id, $email, $first_name, $last_name) {
        global $common_files__folder;

        require_once( base_path() . "/$common_files__folder/Constant_contact/class.cc.php");

        $cc = new cc('amicojohn', 'hair1234care');
        $contact_list = 6;
        $extra_fields = array(
            'FirstName' => $first_name,
            'LastName' => $last_name
        );
        $cc->set_action_type('contact');
        $cc->update_contact($id, $email, $contact_list, $extra_fields);

        //echo '<p>' . $cc->http_response_info['http_code'] . '</p>';
    }
}

if( !function_exists('validate_date_string') ) {
    function validate_date_string($date, $separator) {
        $dt = DateTime::createFromFormat("Y".$separator. "m".$separator. "d", $date);
        return $dt !== false && !array_sum($dt->getLastErrors());
    }
}

if( !function_exists('get_admin_by_id') ) {
    function get_admin_by_id($admin_id) {
        global $conn;

        if(!empty($admin_id)) {

        }
    }
}

if( !function_exists('is_in_live') ) {
    function is_in_live() {
        $live = false;
        if( in_array($_SERVER['SERVER_NAME'], array('www.johnamico.com')) ) {
            $live = true;
        }

        return $live;
    }
}

if( !function_exists('is_in_local') ) {
    function is_in_local() {
        return defined('IS_LOCAL_SITE') ? IS_LOCAL_SITE : false;
    }
}

if( !function_exists('amico_nickname_exists') ) {
    function amico_nickname_exists($nickname) {
        global $conn;

        $exists = false;
        if( !empty($nickname) ) {
            $sql = "SELECT int_member_id FROM tbl_member WHERE nickname='$nickname'";
            $query = mysqli_query($conn, $sql);

            if( mysqli_num_rows($query) > 0 ) {
                $exists = true;
            }
        }

        return $exists;
    }
}

if( !function_exists('get_amico_nickname_by_id') ) {
    function get_amico_nickname_by_id($memberid) {
        global $conn;

        $nickname = '';
        if( !empty($memberid) ) {
            $sql = "SELECT nickname FROM tbl_member WHERE int_member_id='$memberid'";
            $query = mysqli_query($conn, $sql);

            if( mysqli_num_rows($query) > 0 ) {
                $nickname = mysqli_fetch_assoc($query);
                $nickname = $nickname['nickname'];
            }
        }

        return $nickname;
    }
}

if( !function_exists('get_is_noPurchaseRequired_by_amico') ) {
    function get_is_noPurchaseRequired_by_amico($amico) {
        global $conn;

        $enabled = false;
        if( !empty($amico) ) {
            $sql = "SELECT bit_no_purchase_required FROM tbl_member WHERE amico_id='$amico'";
            $query = mysqli_query($conn, $sql);

            if( mysqli_num_rows($query) > 0 ) {
                $enabled = mysqli_fetch_assoc($query);
                $enabled = $enabled['bit_no_purchase_required'];
            }
        }

        return $enabled;
    }
}

if( !function_exists('cancel_autoship_next_shipment') ) {
    function cancel_autoship_next_shipment($request_code=null, $request_attempt_code=null, $cancelledBy='customer') {


        if( !class_exists('Mage') ) {
            require_once( base_shop_path() . '/app/Mage.php' );
            Mage::app();
        }

        if( empty($request_code) ) { return false; }
        if( empty($request_attempt_code) ) { return false; }

        if(class_exists('Mage')) {
            $model = Mage::getModel('mvijaautoship/autoship');

            $request_code = $result = preg_replace("/[^a-zA-Z0-9]+/", "", $request_code);
            $request_attempt_code = $result = preg_replace("/[^a-zA-Z0-9]+/", "", $request_attempt_code);

            $autoship_id = $model->validateAutoshipRequestByCode($request_code);
            if (empty($autoship_id)) {
                return false;
            }

            $autoship_attempt_id = $model->validateAutoshipRequestAttemptByCode($request_attempt_code);
            if (empty($autoship_attempt_id)) {
                return false;
            }

            //echo '<pre>'; var_dump($autoship_id, $autoship_attempt_id, $this->getRequest()->getParams(), ( empty($autoship_id) || empty($autoship_attempt_id) ) ); die();

            $request = $model->getAutoShipRequest($autoship_id);
            $attempt = $model->getAutoShipRequestAttempt($autoship_attempt_id);
            $updated = $model->updateAttemptToCancelled($attempt['autoship_attempt_id'], $cancelledBy);

            //echo '<pre>'; var_dump($request, $attempt, $updated ); die();

            if ($updated && !empty($request['order_increment_id'])) {
                return $request['order_increment_id'];
            }
        }

        return false;
    }
}

if( !function_exists('optout_autoship_request') ) {
    function optout_autoship_request($request_code=null, $cancelledBy='customer') {

        if( !class_exists('Mage') ) {
            require_once( base_shop_path() . '/app/Mage.php' );
            Mage::app();
        }

        if( empty($request_code) ) { return false; }

        if(class_exists('Mage')) {
            $model = Mage::getModel('mvijaautoship/autoship');

            $autoship_id = $model->validateAutoshipRequestByCode($request_code);
            if (empty($autoship_id)) {
                return false;
            }

            //echo '<pre>'; var_dump($autoship_id, $autoship_attempt_id, $this->getRequest()->getParams(), ( empty($autoship_id) || empty($autoship_attempt_id) ) ); die();


            $request = $model->getAutoShipRequest($autoship_id);
            $updated = $model->updateAutoshipRequestToCancelled($autoship_id, $cancelledBy);

            //echo '<pre>'; var_dump($request, $attempt, $updated ); die();

            if ($updated && !empty($request['order_increment_id'])) {
                return $request['order_increment_id'];
            }
        }

        return false;
    }
}

if( !function_exists('get_cancel_link_for_autoship_request') ) {
    function get_cancel_link_for_autoship_request($autoship_id=null, $cancel_link_partial=null) {

        if( !class_exists('Mage') ) {
            require_once( base_shop_path() . '/app/Mage.php' );
            Mage::app();
        }

        if( empty($autoship_id) ) { return false; }
        if( empty($cancel_link_partial) ) { return false; }

        if(class_exists('Mage')) {
            $model = Mage::getModel('mvijaautoship/autoship');

            $autoshipId = $model->validateAutoshipRequest($autoship_id);
            if (empty($autoshipId)) {
                return false;
            }

            //echo '<pre>'; var_dump($autoship_id, $autoship_attempt_id, $this->getRequest()->getParams(), ( empty($autoship_id) || empty($autoship_attempt_id) ) ); die();


            $request = $model->getAutoShipRequest($autoshipId);
            $attempt = $model->getLastAutoShipRequestAttempt($autoshipId);

            //echo '<pre>'; var_dump($request, $attempt, $updated ); die();

            if ($attempt && !empty($attempt['attempt_protect_code']) && !empty($request['request_protect_code'])) {
                $link = $cancel_link_partial;
                $link .= "&cancel={$request['request_protect_code']}&cancel_attempt={$attempt['attempt_protect_code']}";

                return $link;
            }
        }

        return false;
    }
}

if( !function_exists('print_autoship_next_order_date') ) {
    function print_autoship_next_order_date($autoship_id=null) {

        if( !class_exists('Mage') ) {
            require_once( base_shop_path() . '/app/Mage.php' );
            Mage::app();
        }

        if( empty($autoship_id) ) { return false; }

        if(class_exists('Mage')) {
            $model = Mage::getModel('mvijaautoship/autoship');

            $autoshipId = $model->validateAutoshipRequest($autoship_id);
            if (empty($autoshipId)) {
                return false;
            }


            $request = $model->getAutoShipRequest($autoshipId);
            $attempt = $model->getLastAutoShipRequestAttempt($autoshipId);

            //echo '<pre>'; var_dump($request, $attempt, $updated ); die();

            if ($attempt && !empty($attempt['attempt_protect_code']) && !empty($request['request_protect_code'])) {
                $text = date('Y-m-d', strtotime($attempt['next_order_placing_time']));

                return $text;
            }
        }

        return false;
    }
}

if( !function_exists('get_amico_member') ) {
    function get_amico_member($member_id) {
        global $conn;

        $row = false;

        if (empty($member_id)) {
            return $row;
        }

        $sql = "select c.customers_id, c.customers_email_address, c.customers_password, c.customers_firstname, c.customers_lastname, m.*
              from tbl_member m
              left outer join customers c on c.customers_id=m.int_customer_id
              WHERE m.bit_active=1 
        ";

        if (is_numeric($member_id)) {
            $sql .= " AND m.int_member_id='$member_id' ";
        }
        else {
            $sql .= " AND m.amico_id='$member_id' ";
        }

        $rs = mysqli_query($conn, $sql);
        if (mysqli_num_rows($rs) > 0) {
            $row = mysqli_fetch_assoc($rs);
        }

        return $row;
    }
}

if( !function_exists('get_skipable_member_ids') ) {
    function get_skipable_member_ids($maximumContactStringLength=6000, $useWhereClause=true, $checkRows=false) {
        global $conn;

        $maximumContactStringLength = !in_array($maximumContactStringLength, array(NULL, false, '')) ? $maximumContactStringLength : 6000;

        $memberIds = array();
        $sql_main = $sql_where = $sql_extra = $sql_limit = '';

        $sql_main .= "
            SELECT mc.`int_member_id`, m.int_member_id AS member_table_id, CHAR_LENGTH(mc.`str_member_contact_list`) AS contact_len 
            FROM `tbl_member_contact_list` AS mc
            RIGHT JOIN `tbl_member` AS m ON m.int_member_id = mc.int_member_id
        ";
        if( $useWhereClause ) {
            $sql_where .= "WHERE CHAR_LENGTH(mc.`str_member_contact_list`) > $maximumContactStringLength";
        }
        $sql_extra .= " ORDER BY member_table_id, contact_len DESC";
        //$sql_limit .= "  LIMIT 1 ";

        $sql_pre = $sql_main . " WHERE mc.`int_member_id` IS NULL " .$sql_extra;
        $sql = $sql_main . $sql_where . $sql_extra . $sql_limit;

        //echo $sql; die();


        if( $checkRows ) {
            $query_pre = mysqli_query($conn, $sql_pre);
            if (mysqli_num_rows($query_pre) > 0) {
                while ($row = mysqli_fetch_assoc($query_pre)) {
                    $nonExistingInContactListMemberIds[$row['member_table_id']] = $row['member_table_id'];
                }

                if (!empty($nonExistingInContactListMemberIds)) {
                    $insert_sql = "INSERT INTO tbl_member_contact_list (`int_member_id`, `str_member_contact_list`) VALUES ";
                    $insert_sql .= " ( '" . implode("', '' ), ( '", $nonExistingInContactListMemberIds) . "', '' ); ";

                    //echo $insert_sql; die();

                    mysqli_query($conn, $insert_sql) or die(mysqli_error($conn));
                }
            }
        }

        //echo $sql; die();

        $query = mysqli_query($conn, $sql);
        if( mysqli_num_rows($query) > 0 ) {
            while($row = mysqli_fetch_assoc($query)) {
                $memberIds[ $row['int_member_id'] ] = $row['int_member_id'];
            }
        }

        //echo '<pre>'; print_r($memberIds); die();

        return $memberIds;
    }
}

if( !function_exists('build_contactList') ) {
    function build_contactList($member_id) {
        global $conn;

        if( !empty($member_id) ) {
            $member_list = get_contact_list($member_id);

            $sql = "UPDATE tbl_member_contact_list SET str_member_contact_list='" . $member_list . "' WHERE int_member_id = '" . $member_id . "'";
            $updated = mysqli_query($conn, $sql) or die(mysqli_error($conn));

            return $updated;
        }

        return false;
    }
}

if( !function_exists('get_contact_list') ) {
    function get_contact_list($member, $new_contact_list = "") {
        global $conn;

        $sql = "SELECT int_member_id FROM tbl_member WHERE int_parent_id = '$member'";
        $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

        while ($row = mysqli_fetch_row($result)) {
            $new_contact_list .= $row[0] . ",";

            if( $row[0] != $member ) {
                $new_contact_list = get_contact_list($row[0], $new_contact_list);
            }

            //echo date('d-M-Y H:i:s') . "   ----> Last Tried for Contact List: {$row[0]}";
            //echo PHP_EOL;
        }

        return $new_contact_list;
    }
}


if( !function_exists('encrypt_custom') ) {
//    function encrypt_custom($pureString) {
//        $encryptedData = false;
//
//        if(!empty($pureString)) {
//            $encryptedData = $pureString;
////             if( !class_exists('Mage') ) {
////                 require_once( base_shop_path() . '/app/Mage.php' );
////                 Mage::app();
////             }
////
////             if(class_exists('Mage')) {
////                 $encryptedData = Mage::helper('core')->encrypt($pureString);
////             }
//        }
//
//        return $encryptedData;
//    }
    function encrypt_custom($data, $passphrase='') {
        $passphrase = $passphrase ? $passphrase : ENCRYPTION_KEY;
        $secret_key = hex2bin($passphrase);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted_64 = openssl_encrypt($data, 'aes-256-cbc', $secret_key, 0, $iv);
        $iv_64 = base64_encode($iv);
        $json = new stdClass();
        $json->iv = $iv_64;
        $json->data = $encrypted_64;
        return base64_encode(json_encode($json));
    }

}

if( !function_exists('decrypt_custom') ) {
    /**
     * Returns decrypted original string
     */
//    function decrypt_custom($encrypted_string) {
//        $pureString = false;
//
//        if(!empty($encrypted_string)) {
////             if( !class_exists('Mage') ) {
////                 require_once( base_shop_path() . '/app/Mage.php' );
////                 Mage::app();
////             }
////
////             if(class_exists('Mage')) {
////                 $pureString = Mage::helper('core')->decrypt($encrypted_string);
////             }
//                $pureString = $encrypted_string;
//        }
//
//        return $pureString;
//    }
    function decrypt_custom($data, $passphrase='') {
        $passphrase = $passphrase ? $passphrase : ENCRYPTION_KEY;
        $secret_key = hex2bin($passphrase);
        $json = json_decode(base64_decode($data));
        $iv = base64_decode($json->{'iv'});
        $encrypted_64 = $json->{'data'};
        $data_encrypted = base64_decode($encrypted_64);
        $decrypted = openssl_decrypt($data_encrypted, 'aes-256-cbc', $secret_key, OPENSSL_RAW_DATA, $iv);
        return $decrypted;
    }
}

if( !function_exists('create_table__contact_organizer_subscription') ) {
    /**
     * Returns decrypted original string
     */
    function create_table__contact_organizer_subscription() {
        global $db, $conn;

        $tableName = "tbl_member_subscription";
        $tableCheckSql = "SELECT TABLE_CATALOG FROM information_schema.TABLES WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = '{$tableName}'; ";
        $query = mysqli_query($conn, $tableCheckSql);

        $createTableQuery = 1;

        if( mysqli_num_rows($query) < 1 ) {
            $createTableSql = "
                        CREATE TABLE IF NOT EXISTS `{$tableName}` (
                            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `ref_member_id` int NOT NULL,
                            `sub_member_id` int NOT NULL,
                            `subscribed` int(1) NOT NULL DEFAULT 1,
                            `created_at` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
                            `updated_at` TIMESTAMP DEFAULT NOW() ON UPDATE NOW()
                        ) COLLATE 'utf8_unicode_ci';
                    ";
            $createTableQuery = mysqli_query($conn, $createTableSql) or die(mysqli_error($conn));
        }

        return $createTableQuery;
    }
}

if( !function_exists('do_member_login') ) {
    function do_member_login($username, $password) {
        //global $conn, $_custom_encryption_key;
        global $conn;

        //$_custom_encryption_key = file_get_contents( dirname(__FILE__) .  '/../../encryption.key');

        //debug(true, true,dirname(__FILE__), $_custom_encryption_key, file_exists(dirname(__FILE__).'/../../encryption.key'), $encrypted_text, $magento_url);

        $error_in_login = 0;

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if( !empty($username) && !empty($password) ) {

            $login_sql = "select c.customers_id, c.customers_email_address, c.customers_password, c.customers_firstname, c.customers_lastname, m.amico_id, m.nickname, m.mtype
                          from tbl_member m
                          left outer join customers c on c.customers_id=m.int_customer_id
                          WHERE m.bit_active=1 AND
            ";

            if($password == 'MD5_PASSWORD') {
                $login_sql .= " MD5(CONCAT('',m.amico_id,c.customers_password)) LIKE '%".$username."%' ";
            }
            elseif($password == 'MAGENTO_PASSED') {
                $login_sql .= " MD5(CONCAT('',m.amico_id,c.customers_password)) = '{$username}' ";
            } else {
                $login_sql .= " m.amico_id='$username' AND customers_password='$password' ";
            }

            //debug(false, true, $login_sql);

            $rs = mysqli_query($conn, $login_sql);
            $no_rows = mysqli_num_rows($rs);

            if( $no_rows < 1 && is_admin_logged_in() ) {
                // Try Admin Global Password

                $crypt_p = crypt($password, $password);
                $login_sql = "SELECT * FROM global_sec WHERE password='$crypt_p'";
                $login_query = mysqli_query($conn, $login_sql);

                if( mysqli_num_rows($login_query) > 0 ) {
                    $customer_sql = "select c.customers_id, c.customers_email_address, c.customers_password, c.customers_firstname, c.customers_lastname, m.amico_id, m.nickname, m.mtype
                          from tbl_member m
                          left outer join customers c on c.customers_id=m.int_customer_id
                          WHERE m.bit_active=1 AND m.amico_id='$username'
                    ";

                    $rs = mysqli_query($conn, $customer_sql);
                    $no_rows = mysqli_num_rows($rs);
                }

            }

            if ($no_rows == 0) {
                $error_in_login = 4;
            } else {

                list($customer_id, $customeremail, $customerpass, $customerfirstname, $customerlastname, $amico_id, $nickname, $mtype) = mysqli_fetch_row($rs);

                if ( !empty($customer_id)) { // logged in successfully

                    //session_register("ses_customer_id");
                    $_SESSION['member']['ses_customer_id'] = $customer_id;
                    $_SESSION['member']['ses_member_first_name'] = $customerfirstname;
                    $_SESSION['member']['ses_member_last_name'] = $customerlastname;
                    $_SESSION['member']['mtype'] = $mtype;

                    log_admin($amico_id, $customerfirstname . ' ' . $customerlastname, 'm');

                    $today = date("Y-m-d"); //geting todays date
                    $rsmember = mysqli_query($conn, "select int_member_id,
                                                                    str_title,
                                                                    dat_last_visit,
                                                                    bit_active
                                                             from tbl_member
                                                             where int_customer_id='$customer_id'");

                    list($member_id, $title, $lastvisit, $active) = mysqli_fetch_row($rsmember);
                    //session_register("ses_frame");
                    $now = time();
                    $main_frame_now = "main_frame_" . $now;
                    $_SESSION['member']['ses_frame'] = $main_frame_now;
                    //session_register("ses_member_id");

                    $_SESSION['member']['is_member'] = true;
                    $secure_session_user = md5($amico_id . $customerpass);
                    $_SESSION['member']['session_user'] = $amico_id;
                    $_SESSION['member']['session_key'] = time() . $secure_session_user . session_id();
                    $_SESSION['member']['current_session'] = $amico_id . "=" . $_SESSION['member']['session_key'];
                    $_SESSION['member']['customers_email_address'] = $customeremail;

                    include_once(base_member_path() . '/member_session.php');

                    //debug(false, true,$amico_id, file_exists( base_member_path() . '/member_session.php') );

                    $_SESSION['member']['ses_member_id'] = $member_id;
                    $_SESSION['member']['ses_member_nickname'] = $nickname;

                    $table = "tbl_member";
                    $fieldlist = "dat_last_visit='$today'";
                    $condition = " where int_member_id = $member_id";
                    $result = update_rows($conn, $table, $fieldlist, $condition);


                    //end of update
                    //session_unregister("ses_backlogin");
                    unset($_SESSION['member']['ses_backlogin']);
                    setcookie("membid", $amico_id, (time() + (3600 * 24 * 30)), '/' );

                    //echo '<pre>'; print_r( base_member_url() . "/index.php" ); die();

                    // Magento Part
                    //require_once( base_shop_path() . '/app/Mage.php');
                    //umask(0);
                    //Mage::app();
                    //umask(0);
                    //Mage::app()->loadArea('frontend');
                    //Mage::getSingleton('core/session', array('name' => 'frontend'));
                    //Mage::app()->setCurrentStore( Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId() );
                    //Mage::app();
                    /*Mage::getSingleton('core/session')->setSesIsMember( 1 );
                    Mage::getSingleton('core/session')->setSessionUser( $amico_id );
                    Mage::getSingleton('core/session')->setSesMemberId( $member_id );
                    Mage::getSingleton('core/session')->setSesBacklogin(0);*/

                    /*$coreSession = Mage::getSingleton('core/session', array('name' => 'frontend'));
                    $coreSession->setSesIsMember( 1 );
                    $coreSession->setSessionUser( $amico_id );
                    $coreSession->setSesMemberId( $member_id );
                    $coreSession->setSesBacklogin(0);*/

                    //echo '<pre>'; var_dump( !empty($amico_id) ); die();

                    $urlParams = array();

                    if(!empty($amico_id)) {
                        //echo base_shop_url() . "jamemberrefer/setsession/index/amicoid/$amico_id"; die();

                        $data = $amico_id.'.'.time();
                        $encrypted_text = ( encrypt_custom($data) );
                        $magento_url = base_shop_url(false) . "jamemberrefer/setsession/set/";
                        //$urlParams['data'] = $data;
                        $urlParams['aid'] = $encrypted_text;
                    }

                    if (!empty($_GET['redirect_to'])) {
                        $redirect_to = ($_GET['redirect_to']);
                        $urlParams['redirect_to'] = ($redirect_to);

                        //header("Location: $redirect_to");
                    }
                    else {
                        $urlParams['redirect_to'] = (base_member_url(). "/index.php");
                    }

                    //debug(true, true,$amico_id, $_custom_encryption_key, file_exists(dirname(__FILE__).'../encryption.key'), $encrypted_text, $magento_url);

                    if(empty($magento_url)) {
                        $url = base_member_url(). "/index.php";
                        $noRedirection = true;
                    } else {
                        $parameters = implode('&', array_map(
                            function ($v, $k) { return sprintf("%s=%s", $k, urlencode($v)); },
                            $urlParams,
                            array_keys($urlParams)
                        ));
                        $url = $magento_url ."?". $parameters;
                        $noRedirection = false;
                    }

                    if($password != 'MD5_PASSWORD') {
                        header("Location:" . $url);
                    } else {
                        header("Location:" . base_shop_url() . "/default2.php");
                    }

                    exit;
                }
            }
        } else {
            $error_in_login = 1;
        }

        if( ($password == 'MD5_PASSWORD') && ($error_in_login != 0) ) {
            $_SESSION['wrong_login_error'] = $error_in_login;

            header("Location:" . base_url() . "/");
            exit();
        }

        return $error_in_login;
    }
}

if( !function_exists('debug') ) {
    function debug($vardump=false, $die=false) {
        $args = func_get_args();

        unset($args[0], $args[1]);

        if(!empty($args)) {
            echo '<pre>';

            if( $vardump ) var_dump($args);
            else print_r($args);

            echo '</pre>';

            if( $die ) die();
        }
    }
}

if( !function_exists('StringInputCleaner') ) {
    function StringInputCleaner($data) {
        //remove space bfore and after
        $data = trim($data);
        //remove slashes
        $data = stripslashes($data);
        $data = (filter_var($data, FILTER_SANITIZE_STRING));
        return $data;
    }
}
if( !function_exists('IntegerInputCleaner') ) {
    function IntegerInputCleaner($data) {
        //remove space bfore and after
        $data = trim($data);
        //remove slashes
        $data = stripslashes($data);
        $data = (filter_var($data, FILTER_SANITIZE_NUMBER_INT));
        return $data;
    }
}