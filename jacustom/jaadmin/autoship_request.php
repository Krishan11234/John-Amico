<?php
$page_name = 'Manage AutoShip Requests';
$page_title = 'John Amico - ' . $page_name;

ob_start();
require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


function print_customer_name_with_amico($customer_name, $amico_id) {
    $output = '';

    $output .= $customer_name;
    if( !empty($amico_id) ) {
        $output .= " (<strong>$amico_id</strong>)";
    }

    return $output;
}


$member_type_name = 'Member';
$member_type_name_plural = 'Members';
$self_page = 'autoship_request.php';
$page_url = base_admin_url() . '/autoship_request.php?1=1';
$action_page = 'autoship_request.php';
$action_page_url = base_admin_url() . '/autoship_request.php?1=1';
$export_url = base_admin_url() . '/autoship_request.php';

//15449

$autoship_id = filter_var($_REQUEST['autoship_id'], FILTER_SANITIZE_NUMBER_INT);

$ten_days_back = strtotime("-60 days");

if (empty($_POST['daterange']) && !empty($_REQUEST['start_date']) && strpos($_REQUEST['start_date'], '-')) {
    list($_REQUEST['start_date_y'], $_REQUEST['start_date_m'], $_REQUEST['start_date_d']) = explode('-', $_REQUEST['start_date']);
}
if (empty($_POST['daterange']) && !empty($_REQUEST['end_date']) && strpos($_REQUEST['end_date'], '-')) {
    list($_REQUEST['end_date_y'], $_REQUEST['end_date_m'], $_REQUEST['end_date_d']) = explode('-', $_REQUEST['end_date']);
}

$page = ((!empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) ? $_REQUEST['page'] : 1);

$start_date_d = (!empty($_REQUEST['start_date_d']) ? filter_var($_REQUEST['start_date_d'], FILTER_SANITIZE_NUMBER_INT) : date('d', $ten_days_back));
$start_date_m = (!empty($_REQUEST['start_date_m']) ? filter_var($_REQUEST['start_date_m'], FILTER_SANITIZE_NUMBER_INT) : date('m', $ten_days_back));
$start_date_y = (!empty($_REQUEST['start_date_y']) ? filter_var($_REQUEST['start_date_y'], FILTER_SANITIZE_NUMBER_INT) : date('Y', $ten_days_back));
$start_date_m_name = date('F', mktime(0, 0, 0, $start_date_m, 10));
$end_date_d = (!empty($_REQUEST['end_date_d']) ? filter_var($_REQUEST['end_date_d'], FILTER_SANITIZE_NUMBER_INT) : date('d'));
$end_date_m = (!empty($_REQUEST['end_date_m']) ? filter_var($_REQUEST['end_date_m'], FILTER_SANITIZE_NUMBER_INT) : date('m'));
$end_date_y = (!empty($_REQUEST['end_date_y']) ? filter_var($_REQUEST['end_date_y'], FILTER_SANITIZE_NUMBER_INT) : date('Y'));
$end_date_m_name = date('F', mktime(0, 0, 0, $end_date_m, 10));


$start_date_d = str_pad($start_date_d, 2, '0', STR_PAD_LEFT);
$start_date_m = str_pad($start_date_m, 2, '0', STR_PAD_LEFT);
$end_date_d = str_pad($end_date_d, 2, '0', STR_PAD_LEFT);
$end_date_m = str_pad($end_date_m, 2, '0', STR_PAD_LEFT);

$start_date = "$start_date_y-$start_date_m-$start_date_d";
$end_date = "$end_date_y-$end_date_m-$end_date_d";


$view_orders = false;


if( !empty($_REQUEST['autoship_id']) && !empty($_REQUEST['goto']) && ( ($_REQUEST['goto'] == 'update') ) ) {
    $is_edit = ($_REQUEST['goto'] == 'update') ? true : false;
}
if( !empty($_REQUEST['autoship_id']) && !empty($_REQUEST['goto']) && ( ($_REQUEST['goto'] == 'list_orders') ) ) {
    $view_orders = true;
}

if( $is_edit && !empty($autoship_id) ) {

    $sql = "SELECT ar.*, CONCAT(ar.interval_period_number, ' ', CONCAT(UCASE(SUBSTRING(ar.interval_period_type, 1, 1)),LCASE(SUBSTRING(ar.interval_period_type, 2))) ) AS request_interval, ar.status AS bit_active, o.increment_id AS orders_id, CONCAT(o.customer_firstname, ' ', o.customer_lastname) AS customers_name, DATE_FORMAT(o.created_at, '%m/%d/%Y %H:%i') as date_purch, o.grand_total AS order_grand_total, o.shipping_description, o.shipping_amount, o.subtotal AS order_total, m.amico_id
    FROM " . MAGENTO_TABLE_PREFIX . "mvijaautoship_request AS ar 
    INNER JOIN " . MAGENTO_TABLE_PREFIX . "sales_flat_order AS o ON o.entity_id=ar.mage_order_id
    LEFT JOIN tbl_member AS m ON m.int_member_id=ar.amico_member_id
    ";

    $conditions[] = " ar.autoship_id='$autoship_id' ";
    $conditions[] = " ar.status IN (0,1) ";
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    $data_query = mysqli_query($conn, $sql);

    if( empty($data_query) ) {
        $is_edit = false;
    } else {
        $autoshipRequest = mysqli_fetch_assoc($data_query);

        if( !empty($autoshipRequest['autoship_id']) ) {
            $products_sql = " SELECT arp.*, pv.value AS product_name FROM " . MAGENTO_TABLE_PREFIX . "mvijaautoship_request_products AS arp ";
            $products_sql .= " INNER JOIN " . MAGENTO_TABLE_PREFIX . "catalog_product_entity_varchar pv ON pv.entity_id=arp.mage_product_id";
            $products_sql .= " WHERE pv.attribute_id='71' AND arp.autoship_id='$autoship_id' ";

            //echo $products_sql;

            $prod_data_query = mysqli_query($conn, $products_sql);

            while($prod_row = mysqli_fetch_assoc($prod_data_query)) {
                $autoshipRequest['products'][$prod_row['autoship_product_id']] = $prod_row;
            }

            try {
                // Including Magento
                include_once(base_shop_path() . "/app/Mage.php");
                Mage::reset();
                $app = Mage::app();

                $order = Mage::getModel('sales/order')->load($autoshipRequest['mage_order_id']);

                if( empty($order) ) {
                    $is_edit = false;
                } else {
                    $nextOrderTime = Mage::getModel('mvijaautoship/autoship_cron')->getOrderPlacingTime($autoshipRequest);
                    if( !empty($nextOrderTime) ) {
                        if(is_numeric($nextOrderTime) ) {
                            $autoshipRequest['next_order_placing_time'] = date('Y-m-d H:i', $nextOrderTime);
                        } else {
                            $autoshipRequest['next_order_placing_time'] = $nextOrderTime;
                        }
                    }
                    $nextOrderConfirmingEmailTime = Mage::getModel('mvijaautoship/autoship_cron')->getOrderConfirmingEmailSendingTime($autoshipRequest, $nextOrderTime);
                    if( !empty($nextOrderConfirmingEmailTime) ) {
                        if(is_numeric($nextOrderConfirmingEmailTime) ) {
                            $autoshipRequest['next_order_confirming_time'] = date('Y-m-d H:i', $nextOrderConfirmingEmailTime);
                        } else {
                            $autoshipRequest['next_order_confirming_time'] = $nextOrderConfirmingEmailTime;
                        }
                    }

                    $autoshipRequest['mage_order'] = $order;
                    $autoshipRequest['mage_order_shipping_data'] = $autoshipRequest['mage_order']->getShippingAddress()->getData();
                    $autoshipRequest['mage_order_billing_data'] = $autoshipRequest['mage_order']->getBillingAddress()->getData();

                }

                //echo '<pre>'; var_dump($autoshipRequest); die();
            }
            catch (Exception $e) {
                $mesg = "Something went wrong while updating Order Total";
            }

        }
    }

}

if( !empty($_REQUEST['autoship_id']) && !empty($_REQUEST['goto']) && ( ($_REQUEST['goto'] == 'update') || ($_REQUEST['goto'] == 'delete') ) ) {

    $is_edit = ( $_REQUEST['goto'] == 'update' ) ? true : false;
    //debug(false, true, $_POST, $autoship_id, $order_total);

    $error = false;

    if( ($_REQUEST['goto'] == 'update') && !empty($_POST['update'])  ) {

        //debug(false, true, $_POST, $autoship_id, $autoshipRequest);
        $prodEnables = $_POST['autoshipProd'];
        $prodQtys = $_POST['autoshipProdQty'];


        $autoshipRequestEnable = ( !empty($_POST['autoshipRequestEnable']) && ($_POST['autoshipRequestEnable'] == $autoship_id)) ? 1 : 0;
        $autoshipRequest['status'] = $autoshipRequestEnable;

        if( !empty($prodEnables) ) {
            //echo '<pre>'; var_dump( $prodEnables, $prodQtys, $request ); die();

            if( !empty($prodEnables) && !empty($autoshipRequest['products'])) {
                $prodIds = array_keys($autoshipRequest['products']);
                foreach($prodEnables as $autoProdId =>$autoProd) {
                    if( in_array($autoProdId, $prodIds) ) {
                        $autoshipProds[$autoProdId]['enabled'] = 1;
                        $autoshipRequest['products'][$autoProdId]['status'] = 1;
                        $enabledProds[] = $autoProdId;
                    } else {
                        $autoshipRequest['products'][$autoProdId]['status'] = 0;
                        $autoshipProds[$autoProdId]['enabled'] = 0;
                    }
                }
            }

        } else {
            if( $autoshipRequestEnable ) {
                $error = true;
                $errorMessage['autoshipProd'] = "There are no products enabled for this request. To place Autoship Order, at least one product should be enabled.";
            }
        }

        if( !empty($prodQtys) ) {
            if( !empty($prodQtys) && !empty($autoshipRequest['products'])) {
                $prodIds = array_keys($autoshipRequest['products']);
                foreach($prodQtys as $autoProdId =>$prodQty) {
                    if( in_array($autoProdId, $prodIds) ) {
                        //$prodsQty[$autoProdId] = $prodQty;
                        $autoshipProds[$autoProdId]['qty'] = $prodQty;
                        $autoshipRequest['products'][$autoProdId]['qty'] = $prodQty;

                        if( $prodQty < 1 ) {
                            $errorMessage['autoshipQty'] = "Sorry! You cannot set product Quantity to 0 (zero).";
                            $error = true;

                            break;
                        }
                    }
                }
            }
        }


        // This is for displaying the Posted Data
        if(!empty($autoshipRequest['products'])) {
            foreach($autoshipRequest['products'] as $asrProdId => $asrProd) {
                if( in_array($asrProdId, array_keys($_POST['autoshipProd'])) ) {
                    $autoshipRequest['products'][$asrProdId]['status'] = 1;
                    $disableProds[$autoship_id][$asrProdId] = 1;
                } else {
                    $autoshipRequest['products'][$asrProdId]['status'] = 0;
                    $disableProds[$autoship_id][$asrProdId] = 0;
                }
            }
        }

        //echo '<pre>'; var_dump( $autoshipRequest['products'] ); die();

        if(!$error) {

            $autoship_sql = " UPDATE " . MAGENTO_TABLE_PREFIX . "mvijaautoship_request SET status='$autoshipRequestEnable' WHERE autoship_id='$autoship_id'  ";
            $res3 = mysqli_query($conn, $autoship_sql) or die(mysqli_error($conn));

            if( !empty($res3) && !empty($autoshipProds) ) {
                foreach($autoshipProds as $prodId=>$prod)
                {
                    if( isset($prod['enabled']) && !empty($prod['qty']) ) {
                        $prodUpdateSql[] = "UPDATE " . MAGENTO_TABLE_PREFIX . "mvijaautoship_request_products SET status='{$prod['enabled']}', qty='{$prod['qty']}' WHERE autoship_id='$autoship_id' AND autoship_product_id='$prodId'; ";
                    }
                }
                if( !empty($prodUpdateSql) ) {
                    $prodUpdateSql = implode(PHP_EOL, $prodUpdateSql);
                    $res3 = mysqli_query($conn, $prodUpdateSql);

                    //debug(true, true, $res3, $disableProds, $prodUpdateSql);
                    if( $res3 ) {
                        $mesg = "AutoShip Request is updated.";
                    }
                }

            }
        }
    }
    elseif( ($_REQUEST['goto'] == 'delete') && !empty($_REQUEST['delete']) ) {

        $autoship_sql = " UPDATE " . MAGENTO_TABLE_PREFIX . "mvijaautoship_request SET status='2', cancelled_by='Admin' WHERE autoship_id='$autoship_id'  ";
        $res3 = mysqli_query($conn, $autoship_sql);

        $mesg = "Autoship Request has been deleted";
        $is_edit = false;
    }
}
if( !empty($_REQUEST['autoship_id']) && !empty($_REQUEST['activate']) && isset($_REQUEST['active']) ) {
    $status = (int)$_REQUEST['active'];
    $status = ($status==1) ? 0 : 1;
    $autoship_sql = " UPDATE " . MAGENTO_TABLE_PREFIX . "mvijaautoship_request SET status='$status' WHERE autoship_id='$autoship_id'  ";
    $res3 = mysqli_query($conn, $autoship_sql) or die(mysqli_error($conn));

    if($res3 ) {
        $statusChangeRow = $autoship_id;
        $mesg = "AutoShip Request is updated.";
    }
}
if( !empty($_REQUEST['cancel']) && !empty($_REQUEST['cancel_attempt']) ) {

    $request_code = $_REQUEST['cancel'];
    $request_attempt_code = $_REQUEST['cancel_attempt'];

    $cancelled = cancel_autoship_next_shipment($request_code, $request_attempt_code, 'admin');

    if($cancelled ) {
        $mesg = "The next shipment was cancelled for Order # $cancelled";
        //header("Location: {$page_url}&page={$page}");
        //die();
    } else {
        $errorMessage['cancel'] = 'Could not cancel the request. AutoShip request was invalid.';
    }
}

if( !$is_edit ) {

    //echo '<pre>'; var_dump($view_orders, $start_date_d, $_REQUEST, $start_date); die();

    $limit = 50;

    $limit_start = ($page * $limit) - $limit;
    $limit_end = ($page * $limit);


    if( !$view_orders ) {

        $sql = "SELECT ar.*, CONCAT(ar.interval_period_number, ' ', CONCAT(UCASE(SUBSTRING(ar.interval_period_type, 1, 1)),LCASE(SUBSTRING(ar.interval_period_type, 2))) ) AS request_interval, ar.status AS bit_active, o.increment_id AS orders_id, CONCAT(o.customer_firstname, ' ', o.customer_lastname) AS customers_name, DATE_FORMAT(o.created_at, '%Y-%m-%d') as date_purch, o.grand_total AS order_grand_total, o.subtotal AS order_total, m.amico_id, IF( (ISNULL(ar.amico_member_id)  OR (ar.amico_member_id<1) ), 'No', 'Yes') as is_amico_member_order
    FROM " . MAGENTO_TABLE_PREFIX . "mvijaautoship_request AS ar 
    INNER JOIN " . MAGENTO_TABLE_PREFIX . "sales_flat_order AS o ON o.entity_id=ar.mage_order_id
    LEFT JOIN tbl_member AS m ON m.int_member_id=ar.amico_member_id
    ";

        $sortby = '';
        $sortby = "ORDER BY ar.mage_order_time DESC";

        //$start_date = strtotime("$start_date_d $start_date_m_name, $start_date_y");
        //$end_date = strtotime("$end_date_d $end_date_m_name, $end_date_y");

        //$conditions[] = " ar.mage_order_time  >= '$start_date 00:00:00' ";
        //$conditions[] = " ar.mage_order_time  <= '$end_date 59:59:59' ";
        $conditions[] = " ar.status IN (0,1) ";

        $field_details = array(
            'orders_id' => 'Order ID',
            'customers_name' => array(
                'field_type' => 'text_from_callback',
                'name' => 'Customer Name',
                'value' => array(
                    'function' => 'print_customer_name_with_amico',
                    'params' => array('customers_name', 'amico_id'),
                    'params_field' => array('customers_name', 'amico_id'),
                ),
            ),
            'request_interval' => 'AutoShip Every',
            'date_purch' => 'Date Purchased',
            'next_order' => array(
                'name' => 'Next Order On',
                'field_type' => 'text_from_callback',
                'value' => array(
                    'function' => 'print_autoship_next_order_date',
                    'params' => array('autoship_id'),
                    'params_field' => array('autoship_id'),
                ),
            ),
            'order_grand_total' => 'Order total ($)',
            'is_amico_member_order' => 'Member Order',
            'status' => 'Is Active?',
            'configure' => array(
                'name' => 'Action',
                'multi_fields' => array(
                    'cancel_shipment' => array(
                        'link' => "{$action_page_url}&autoship_id=ID_FIELD_VALUE&goto=update&page={$page}",
                        'link_from_callback' => array(
                            'link_value' => array(
                                'function' => 'get_cancel_link_for_autoship_request',
                                'params_field' => array(
                                    'autoship_id',
                                    'cancel_link_partial'
                                ),
                                'params' => array(
                                    'autoship_id',
                                    'cancel_link_partial' => "{$action_page_url}&page={$page}"
                                ),
                            ),
                        ),
                        'id_field' => 'autoship_id',
                        'name' => 'Cancel Next Shipment',
                        'button' => true,
                        'button_extra_class' => ' btn-warning',
                        'attributes' => array(
                            'onclick' => "return confirm('Are you sure, you want cancel the next shipment? This action cannot be undone.')",
                        ),
                    ),
                    'list_orders' => array(
                        'link' => "{$action_page_url}&autoship_id=ID_FIELD_VALUE&goto=list_orders&page={$page}",
                        /*'link_from_callback' => array(
                            'link_value' => array(
                                'function' => 'get_cancel_link_for_autoship_request',
                                'params_field' => array(
                                    'autoship_id',
                                    'cancel_link_partial'
                                ),
                                'params' => array(
                                    'autoship_id',
                                    'cancel_link_partial' => "{$action_page_url}&page={$page}"
                                ),
                            ),
                        ),*/
                        'id_field' => 'autoship_id',
                        'name' => 'View Orders',
                        'button' => true,
                        'button_extra_class' => ' btn-success',
                    ),
                ),
                'inline' => true,
            ),
            'actions' => 'Commands',
        );

        $id_field = 'autoship_id';
        $no_edit_button = false;


        $action_page__id_handler = 'autoship_id';

    }
    else {

        $page_name = "View Autoship Orders for Request ID: <strong>$autoship_id</strong>";


        /*$sql = "SELECT ar.*, CONCAT(ar.interval_period_number, ' ', CONCAT(UCASE(SUBSTRING(ar.interval_period_type, 1, 1)),LCASE(SUBSTRING(ar.interval_period_type, 2))) ) AS request_interval, ar.status AS bit_active, o.increment_id AS orders_id, CONCAT(o.customer_firstname, ' ', o.customer_lastname) AS customers_name, DATE_FORMAT(o.created_at, '%Y-%m-%d') as date_purch, o.grand_total AS order_grand_total, o.subtotal AS order_total, rpo.*
    FROM " . MAGENTO_TABLE_PREFIX . "mvijaautoship_request AS ar 
    INNER JOIN " . MAGENTO_TABLE_PREFIX . "mvijaautoship_request_processed_orders AS rpo ON rpo.autoship_id=ar.autoship_id
    INNER JOIN " . MAGENTO_TABLE_PREFIX . "sales_flat_order AS o ON o.entity_id=rpo.mage_order_id
    ";*/

        $sql = "
        SELECT arpo.*, o.increment_id AS orders_id, CONCAT(o.customer_firstname, ' ', o.customer_lastname) AS customers_name, 
        DATE_FORMAT(o.created_at, '%Y-%m-%d') as date_purch, o.grand_total AS order_grand_total, o.subtotal AS order_total
        
        FROM  (
            SELECT ar.*, CONCAT(ar.interval_period_number, ' ', CONCAT(UCASE(SUBSTRING(ar.interval_period_type, 1, 1)),LCASE(SUBSTRING(ar.interval_period_type, 2))) ) AS request_interval, ar.status AS bit_active
            FROM stws_mvijaautoship_request AS ar
            LEFT JOIN stws_mvijaautoship_request_processed_orders AS rpo ON rpo.autoship_id=ar.autoship_id
            
            WHERE ar.autoship_id = '{$autoship_id}'
            ORDER BY rpo.created DESC
        
        ) AS arpo
        
        INNER JOIN stws_sales_flat_order AS o ON o.entity_id=arpo.mage_order_id 
        ";


        $sortby = '';
        //$sortby = "ORDER BY rpo.created DESC";

        //$start_date = strtotime("$start_date_d $start_date_m_name, $start_date_y");
        //$end_date = strtotime("$end_date_d $end_date_m_name, $end_date_y");

        //$conditions[] = " rpo.created  >= '$start_date 00:00:00' ";
        //$conditions[] = " rpo.created  <= '$end_date 59:59:59' ";
        //$conditions[] = " ar.status IN (0,1) ";
        //$conditions[] = " ar.autoship_id = '$autoship_id' ";

        $field_details = array(
            'orders_id' => 'Order ID',
            'customers_name' => array(
                'field_type' => 'text_from_callback',
                'name' => 'Customer Name',
                'value' => array(
                    'function' => 'print_customer_name_with_amico',
                    'params' => array('customers_name', 'amico_id'),
                    'params_field' => array('customers_name', 'amico_id'),
                ),
            ),
            'date_purch' => 'Date Purchased',
            'order_grand_total' => 'Order total ($)',
            /*'configure' => array(
                'name' => 'Action',
                'multi_fields' => array(
                    'view_order' => array(
                        'link' => "{$action_page_url}&order_id=ID_FIELD_VALUE&goto=view_order&page={$page}",
                        'id_field' => 'orders_id',
                        'name' => 'View Order',
                        'button' => true,
                        'button_extra_class' => ' btn-success',
                    ),
                ),
                'inline' => true,
            ),*/
        );

        $id_field = 'order_id';
        $no_edit_button = false;


        $action_page__id_handler = 'order_id';
    }




    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    $sql .= " $sortby ";

    //echo $sql; die();


    //$query_pag_data = " $condition LIMIT $start, $per_page";
    $data_num_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

    mysqli_store_result($conn);
    $numrows = mysqli_num_rows($data_num_query);

    //echo $sql;

    $sql .= " LIMIT $limit OFFSET $limit_start ";
    //echo $sql;
    $data_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

    //$display_data_from_data_page = false;
}

if( $view_orders && !$is_edit ) {

}

?>

<?php if(!$view_orders && !empty($numrows)): ?> <script>var collapse_left_sidebar=true;</script> <?php endif; ?>

<div role="main" class="content-body">
    <header class="page-header">
        <h2><?php echo $page_name; ?></h2>

        <div class="right-wrapper pull-right">
            <ol class="breadcrumbs">
                <li>
                    <a href="<?php echo base_admin_url(); ?>">
                        <i class="fa fa-home"></i>
                    </a>
                </li>
                <li><span><?php echo $page_name; ?></span></li>
            </ol>


            <a class="sidebar-right-toggle"></a>
        </div>
    </header>

    <div class="row ">
        <?php if(!empty($mesg) || !empty($errorMessage)): ?>

            <div class="row">
                <?php if(!empty($mesg)): ?>
                    <div class="col-sm-10 centering">
                        <div class="alert alert-success">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <?php echo $mesg; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if(!empty($errorMessage)): ?>
                    <div class="col-sm-10 centering">
                        <div class="alert alert-danger">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <?php if(is_array($errorMessage)) {
                                echo "<ul style='list-style: none'><li>".implode('</li><li>', $errorMessage) . "</li></ul>";
                            } else {
                                echo $errorMessage;
                            }
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        <?php endif; ?>
        <?php if(!$is_edit && is_autoship_enable() ) : ?>
        <section class="panel">
            <div class="col-xs-12">
                <header class="panel-heading">
                    <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                </header>
                <div class="panel-body">
                    <?php if($display_date_range) :?>
                    <div class="row">
                        <div class="col-xs-12 filter_wrapper ">
                            <div class="table-responsive">
                                <div class="col-lg-4 col-md-8 col-sm-10 col-xs-12 centering date_range_wrapper">
                                    <form method="POST" action="">
                                        <input type="hidden" name="daterange" value="1" />
                                        <table class="table table-bordered table-striped mb-none">
                                            <tr>
                                                <td align="center" colspan="2"><strong>Date Range Selection</strong></td>
                                            </tr>
                                            <tr>
                                                <td>Start Date:</td>
                                                <td>
                                                    <select name="start_date_m">
                                                        <?
                                                        for ($i = 1; $i <= 12; $i++) {
                                                            $sel = "";
                                                            if ($i == $start_date_m) {
                                                                $sel = "selected";
                                                            }
                                                            echo "<option value=\"$i\" $sel>$i</option>";
                                                        }
                                                        ?>
                                                    </select> /
                                                    <select name="start_date_d">
                                                        <?
                                                        for ($i = 1; $i <= 31; $i++) {
                                                            $sel = "";
                                                            if ($i == $start_date_d) {
                                                                $sel = "selected";
                                                            }
                                                            echo "<option value=\"$i\" $sel>$i</option>";
                                                        }
                                                        ?>
                                                    </select> /
                                                    <select name="start_date_y">
                                                        <?
                                                        for ($i = date("Y") - 2; $i <= date("Y"); $i++) {
                                                            $sel = "";
                                                            if ($i == $start_date_y) {
                                                                $sel = "selected";
                                                            }
                                                            echo "<option value=\"$i\" $sel>$i</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </td>

                                            </tr>
                                            <tr>
                                                <td>End Date:</td>
                                                <td>
                                                    <select name="end_date_m">
                                                        <?
                                                        for ($i = 1; $i <= 12; $i++) {
                                                            $sel = "";
                                                            if ($i == $end_date_m) {
                                                                $sel = "selected";
                                                            }
                                                            echo "<option value=\"$i\" $sel>$i</option>";
                                                        }
                                                        ?>
                                                    </select> /
                                                    <select name="end_date_d">
                                                        <?
                                                        for ($i = 1; $i <= 31; $i++) {
                                                            $sel = "";
                                                            if ($i == $end_date_d) {
                                                                $sel = "selected";
                                                            }
                                                            echo "<option value=\"$i\" $sel>$i</option>";
                                                        }
                                                        ?>
                                                    </select> /
                                                    <select name="end_date_y">
                                                        <?
                                                        for ($i = date("Y") - 2; $i <= date("Y"); $i++) {
                                                            $sel = "";
                                                            if ($i == $end_date_y) {
                                                                $sel = "selected";
                                                            }
                                                            echo "<option value=\"$i\" $sel>$i</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center" colspan="2">
                                                    <input type="submit" class="mb-xs mt-xs mr-xs btn btn-xs btn-primary" value="Submit">
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php require_once('display_members_data.php'); ?>
                </div>
            </div>
            <div class="clearfix"></div>
        </section>
        <?php endif; ?>
        <?php if($is_edit && is_autoship_enable()) :?>
            <?php if(!empty($autoshipRequest)) :?>
                <div class="row">
                    <div class="col-xs-12 col-md-10 col-lg-8 centering">
                        <div class="add_edit_member_type_wrapper" >
                            <form name="theform" class="form-bordered autoship_request_edit" action="" method="post">
                                <input type="hidden" name="autoship_id" value="<?php echo ( !empty($autoship_id) ? $autoship_id : '' ); ?>">
                                <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
                                <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
                                <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                                <input type="hidden" name="page" value="<?php echo $page; ?>">

                                <header class="panel-heading">
                                    <h2 class="panel-title text-center">View/Edit AutoShip Request</h2>
                                </header>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label class="col-md-4 control-label" for="autoshipRequest_<?php echo $autoshipRequest['autoship_id']; ?>">Autoship Request Enabled</label>
                                        <div class="col-lg-8 ">
                                            <input type="checkbox" class="" id="autoshipRequest_<?php echo $autoshipRequest['autoship_id']; ?>" name="autoshipRequestEnable" value="<?php echo $autoshipRequest['autoship_id']; ?>" <?php echo ( $autoshipRequest['status'] == 1 ? 'checked' : '' ); ?> />
                                        </div>
                                    </div>
                                    <div class="order_information_fields_section">
                                        <div class="form-group heading text-center no-border-bottom"><p>AutoShip Request Information</p></div>
                                        <div class="forms-group contact_billing_fields">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">AutoShip Interval</label>
                                                <div class="col-lg-8 "><div class="form-control-static"><strong><?php echo $autoshipRequest['request_interval']; ?></strong></div></div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Order Confirmation Email will be sent on</label>
                                                <div class="col-lg-8 "><div class="form-control-static"><strong><?php echo $autoshipRequest['next_order_confirming_time']; ?></strong></div></div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Next Order Placing Time</label>
                                                <div class="col-lg-8 "><div class="form-control-static"><strong><?php echo $autoshipRequest['next_order_placing_time']; ?></strong></div></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="order_information_fields_section">
                                        <div class="form-group heading text-center no-border-bottom"><p>Order Information</p></div>
                                        <div class="forms-group contact_billing_fields">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Order ID # </label>
                                                <div class="col-lg-8 "><div class="form-control-static"><strong><?php echo $autoshipRequest['orders_id']; ?></strong></div></div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Order Total</label>
                                                <div class="col-lg-8 "><div class="form-control-static"><strong>$<?php echo number_format($autoshipRequest['order_grand_total'], 2); ?></strong></div></div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Order Shipping Cost</label>
                                                <div class="col-lg-8 "><div class="form-control-static"><strong>$<?php echo number_format($autoshipRequest['shipping_amount'], 2); ?></strong></div></div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Order Placed On</label>
                                                <div class="col-lg-8 "><div class="form-control-static"><strong><?php echo $autoshipRequest['mage_order']->getCreatedAt(); ?></strong></div></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="customer_information_fields_section">
                                        <div class="form-group heading text-center no-border-bottom"><p>Customer Information</p></div>
                                        <div class="forms-group contact_billing_fields">
                                            <?php
                                            foreach(array('billing', 'shipping') as $addressType) {
                                                $addressDetails = $autoshipRequest["mage_order_{$addressType}_data"];
                                                ?>

                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo ucfirst($addressType); ?> Information</label>
                                                    <div class="col-lg-8 ">
                                                        <div class="form-control-static">
                                                            <div class="cusAddressInfo_name"><strong>Name:</strong> <?php echo $addressDetails['firstname'] . " " . $addressDetails['lastname']; ?></div>
                                                            <div class="cusAddressInfo_email"><strong>Email:</strong> <?php echo $addressDetails['email']; ?></div>
                                                            <div class="cusAddressInfo_address"><strong>Address:</strong>
                                                                <?php echo $addressDetails['street']; ?><br/>
                                                                <?php echo $addressDetails['city'] . ", " . $addressDetails['region'] . " " . $addressDetails['postcode'] . ", ", $addressDetails['country_id']; ?><br/>
                                                            </div>
                                                            <div class="cusAddressInfo_phone"><strong>Tel:</strong> <?php echo $addressDetails['telephone']; ?></div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <?php if(!empty($autoshipRequest['products']) && is_array($autoshipRequest['products']) ): ?>
                                        <div class="ordered_products_section">
                                            <div class="form-group heading text-center no-border-bottom">
                                                <p>Ordered Products</p>
                                            </div>
                                            <div class="forms-group ordered_products_info_fields">
                                                <div class="row">
                                                    <div class="col-md-10 centering">
                                                        <?php foreach($autoshipRequest['products'] as $autoshipProd): ?>
                                                        <div class="form-group">
                                                            <div class="col-xs-2">
                                                                <input type="checkbox" class="" id="autoshipProd_<?php echo $autoshipProd['autoship_product_id']; ?>" name="autoshipProd[<?php echo $autoshipProd['autoship_product_id']; ?>]" value="1" <?php echo ( $autoshipProd['status'] == 1 ? 'checked' : '' ); ?> />
                                                                <input type="hidden" name="autoshipProdHidden[<?php echo $autoshipProd['autoship_product_id']; ?>]" value="1" />
                                                            </div>
                                                            <div class="col-xs-10">
                                                                <label class="control-label" for="autoshipProd_<?php echo $autoshipProd['autoship_product_id']; ?>"><?php echo $autoshipProd['product_name']; ?></label>
                                                                <div>
                                                                    <div class="autoshipProdQty">
                                                                        <div class="form-group">
                                                                            <label for="autoProdQty_<?php echo $autoshipProd['autoship_product_id']; ?>" class="col-xs-3 control-label no-padding"><strong>Order-able Qty:</strong></label>
                                                                            <div class="col-xs-3 no-padding">
                                                                                <input type="number" id="autoProdQty_<?php echo $autoshipProd['autoship_product_id']; ?>" name="autoshipProdQty[<?php echo $autoshipProd['autoship_product_id']; ?>]" class="form-control" min="1" value="<?php echo $autoshipProd['qty']; ?>" />
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="autoshipProdSku"><strong>Product SKU:</strong> <?php echo $autoshipProd['mage_product_sku']; ?></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <footer class="panel-footer">
                                    <div class="row">
                                        <div class="col-sm-9 centering text-center">
                                            <input type="hidden" name="goto" value="update">
                                            <button type="Submit" name="update" value="Update" class="command  btn btn-default btn-success">Update</button>
                                            <button id="cancel" type="button" name="cancel" value="Cancel" class="command btn btn-default btn-warning" onclick="window.location.href='<?php echo $action_page_url."&start_date=$start_date&end_date=$end_date&page=$page"; ?>'">Cancel</button>
                                        </div>
                                    </div>
                                </footer>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>


<?php
require_once("templates/footer.php");