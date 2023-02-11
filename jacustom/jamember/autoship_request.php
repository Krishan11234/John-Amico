<?php
$page_name = 'Manage AutoShip Requests';
$page_title = 'John Amico - ' . $page_name;

// Header already exists issue. This will keep all the output in Buffer but will not release it.
ob_start();
require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


$member_type_name = 'Member';
$member_type_name_plural = 'Members';
$self_page = 'autoship_request.php';
$page_url = base_member_url() . '/autoship_request.php?1=1';
$action_page = 'autoship_request.php';
$action_page_url = base_member_url() . '/autoship_request.php?1=1';
$export_url = base_member_url() . '/autoship_request.php';

//15449

$main_member_id = $_SESSION['member']['ses_member_id'];

$autoship_id = filter_var($_REQUEST['autoship_id'], FILTER_SANITIZE_NUMBER_INT);

$ten_days_back = strtotime("-20 days");

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



if( !empty($_REQUEST['autoship_id']) && !empty($_REQUEST['goto']) && ( ($_REQUEST['goto'] == 'update') ) ) {
    $is_edit = ($_REQUEST['goto'] == 'update') ? true : false;
}

if( $is_edit && !empty($autoship_id) ) {

    $sql = "SELECT ar.*, CONCAT(ar.interval_period_number, ' ', CONCAT(UCASE(SUBSTRING(ar.interval_period_type, 1, 1)),LCASE(SUBSTRING(ar.interval_period_type, 2))) ) AS request_interval, ar.status AS bit_active, o.increment_id AS orders_id, CONCAT(o.customer_firstname, ' ', o.customer_lastname) AS customers_name, DATE_FORMAT(o.created_at, '%m/%d/%Y %H:%i') as date_purch, o.grand_total AS order_grand_total, o.shipping_description, o.shipping_amount, o.subtotal AS order_total
    FROM " . MAGENTO_TABLE_PREFIX . "mvijaautoship_request AS ar 
    INNER JOIN " . MAGENTO_TABLE_PREFIX . "sales_flat_order AS o ON o.entity_id=ar.mage_order_id
    ";

    $conditions[] = " ar.autoship_id='$autoship_id' ";
    $conditions[] = " ar.status IN (1) ";
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
        $autoshipRequestEnable = $autoshipRequest['status'];

        $prodEnables = $_POST['autoshipProd'];
        $prodQtys = $_POST['autoshipProdQty'];

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
            if( !empty($autoshipProds) ) {
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
if( !empty($_REQUEST['cancel']) && !empty($_REQUEST['cancel_attempt']) ) {

    $request_code = $_REQUEST['cancel'];
    $request_attempt_code = $_REQUEST['cancel_attempt'];

    $cancelled = cancel_autoship_next_shipment($request_code, $request_attempt_code);

    if($cancelled ) {
        $mesg = "The next shipment was cancelled for Order # $cancelled";
        header("Location: {$page_url}&page={$page}");
    } else {
        $errorMessage['cancel'] = 'Could not cancel the request. AutoShip request was invalid.';
    }
}
if( !empty($_REQUEST['optout']) ) {

    $request_code = $_REQUEST['optout'];

    $unsubscribed = optout_autoship_request($request_code);

    if($unsubscribed ) {
        $mesg = "You have successfully unsubscribed Auto Ship request for order # $unsubscribed";
        header("Location: {$page_url}&page={$page}");
    } else {
        $errorMessage['optout'] = 'Could not unsubscribe. AutoShip request was invalid.';
    }
}

if( !$is_edit ) {

    //echo '<pre>'; var_dump($start_date_d, $_REQUEST, $start_date); die();

    $limit = 50;

    $limit_start = ($page * $limit) - $limit;
    $limit_end = ($page * $limit);


    $sql = "SELECT ar.*, CONCAT(ar.interval_period_number, ' ', CONCAT(UCASE(SUBSTRING(ar.interval_period_type, 1, 1)),LCASE(SUBSTRING(ar.interval_period_type, 2))) ) AS request_interval, ar.status AS bit_active, o.increment_id AS orders_id, CONCAT(o.customer_firstname, ' ', o.customer_lastname) AS customers_name, DATE_FORMAT(o.created_at, '%Y-%m-%d') as date_purch, o.grand_total AS order_grand_total, o.subtotal AS order_total
    FROM " . MAGENTO_TABLE_PREFIX . "mvijaautoship_request AS ar 
    INNER JOIN " . MAGENTO_TABLE_PREFIX . "sales_flat_order AS o ON o.entity_id=ar.mage_order_id
    ";

    $sortby = '';
    $sortby = "ORDER BY ar.mage_order_time DESC";

    //$start_date = strtotime("$start_date_d $start_date_m_name, $start_date_y");
    //$end_date = strtotime("$end_date_d $end_date_m_name, $end_date_y");

    $conditions[] = " ar.mage_order_time  >= '$start_date 00:00:00' ";
    $conditions[] = " ar.mage_order_time  <= '$end_date 59:59:59' ";
    $conditions[] = " ar.status IN (1) ";
    $conditions[] = " ar.amico_member_id='$main_member_id' ";

    $field_details = array(
        'orders_id' => 'Order ID',
        'date_purch' => 'Date Purchased',
        'request_interval' => 'AutoShip Every',
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
        'configure' => array(
            'name' => 'Action',
            'multi_fields' => array(
                'configure_products' => array(
                    'link' => "{$action_page_url}&autoship_id=ID_FIELD_VALUE&goto=update&page={$page}",
                    'id_field' => 'autoship_id',
                    'name' => '<i title="Configure Products" class="fa fa-cog fa-2" style="margin-left:5px;" aria-hidden="true"></i>',
                    'button' => false,
                    //'button_extra_class' => 'btn-xs ',
                ),
                'cancel_shipment' => array(
                    'link' => "{$action_page_url}&autoship_id=ID_FIELD_VALUE&goto=update&page={$page}",
                    'link_from_callback' => array(
                        'link_value' => array(
                            'function' => 'get_cancel_link_for_autoship_request',
                            'params_field' => array('autoship_id', 'cancel_link_partial'),
                            'params' => array('autoship_id', 'cancel_link_partial'=>"{$action_page_url}&page={$page}"),
                        ),
                    ),
                    'id_field' => 'autoship_id',
                    'name' => '<i title="Cancel Next Shipment" class="fa fa-stop-circle fa-2" style="color: #d2322d;margin-left:5px;" aria-hidden="true"></i>',
                    'button' => false,
                    //'button_extra_class' => 'btn-danger',
                    'attributes' => array(
                        'onclick' => "return confirm('Are you sure, you want cancel the next shipment? This action cannot be undone.')",
                    ),
                ),
                'unsubscribe_request' => array(
                    'link' => "{$action_page_url}&optout=ID_FIELD_VALUE&page={$page}",
                    'id_field' => 'request_protect_code',
                    'name' => '<i title="Unsubscribe" class="fa fa-ban fa-2" style="color: #d2322d;margin-left:5px;" aria-hidden="true"></i>',
                    'button' => false,
                    //'button_extra_class' => 'btn-danger',
                    'attributes' => array(
                        'onclick' => "return confirm('Are you sure, you want cancel the next shipment? This action cannot be undone.')",
                    ),
                ),
            ),
            'inline' => true,
        ),
    );

    $id_field = 'autoship_id';
    $no_edit_button = false;


    $action_page__id_handler = 'autoship_id';

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    $sql .= " $sortby ";

    //echo $sql;


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

?>

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
                            <?php require_once("../$admin_path/display_members_data.php"); ?>
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
                                    <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                                    <input type="hidden" name="page" value="<?php echo $page; ?>">

                                    <header class="panel-heading">
                                        <h2 class="panel-title text-center">View/Edit AutoShip Request</h2>
                                    </header>
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label" for="autoshipRequest_<?php echo $autoshipRequest['autoship_id']; ?>">Autoship Request Enabled</label>
                                            <div class="col-lg-8 "><?php echo ( $autoshipRequest['status'] == 1 ? 'Yes' : 'No' ); ?></div>
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
                                                    <label class="col-md-4 control-label">Order Placed On</label>
                                                    <div class="col-lg-8 "><div class="form-control-static"><strong><?php echo $autoshipRequest['mage_order']->getCreatedAt(); ?></strong></div></div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if(!empty($autoshipRequest['products']) && is_array($autoshipRequest['products']) ): ?>
                                            <div class="ordered_products_section">
                                                <div class="form-group heading text-center no-border-bottom">
                                                    <p>Ordered Products</p>
                                                    <p>(Checked items will be shipped to next the Order)</p>
                                                </div>
                                                <div class="forms-group ordered_products_info_fields">
                                                    <div class="row">
                                                        <div class="col-md-10 centering">
                                                            <?php foreach($autoshipRequest['products'] as $autoshipProd): ?>
                                                                <div class="autoshipProdItem">
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
                                                <button id="cancel" type="button" name="cancel" value="Cancel" class="command btn btn-default btn-warning" onclick="window.location.href='<?php echo $action_page_url."&page=$page"; ?>'">Cancel</button>
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