<?php
$page_name = 'Update Totals For Non-Member Purchases';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


$member_type_name = 'Member';
$member_type_name_plural = 'Members';
$self_page = 'update_order_total.php';
$page_url = base_admin_url() . '/update_order_total.php?1=1';
$action_page = 'update_order_total.php';
$action_page_url = base_admin_url() . '/update_order_total.php?1=1';
$export_url = base_admin_url() . '/update_order_total.php';


if( !empty($_REQUEST['orderid']) && !empty($_REQUEST['goto']) && ( ($_REQUEST['goto'] == 'update') || ($_REQUEST['goto'] == 'delete') ) ) {
    $order_id = filter_var($_REQUEST['orderid'], FILTER_SANITIZE_NUMBER_INT);

    //debug(false, true, $_POST, $order_id, $order_total);

    if( $_REQUEST['submit'] == 'Update' ) { $_REQUEST['goto'] = 'update'; }

    if( ($_REQUEST['goto'] == 'update') && !empty($_POST['total'])  ) {

        $order_referring = filter_var($_POST['referring_id'], FILTER_SANITIZE_STRING);
        $order_total = filter_var((float)$_POST['total'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION|FILTER_FLAG_ALLOW_SCIENTIFIC);

        if( is_get_orders_from_magento() ) {
            try {
                // Including Magento
                include_once ( base_shop_path() . "/app/Mage.php");
                Mage::reset();
                $app = Mage::app();

                $order = Mage::getModel('sales/order')->loadByIncrementId( $order_id );

                //debug(true, true, $order);

                //$baseTotal = $order->getBaseGrandTotal();
                $grandTotal = $order->getGrandTotal();
                $subtotal = $order->getSubtotal();
                $shipping = $order->getShippingAmount();
                $discount = abs($order->getDiscountAmount());
                $tax = $order->getTaxAmount();

                $order_cleared_total = $grandTotal-($shipping+$tax);

                //debug(true, true, array($grandTotal, $subtotal, $shipping, $discount, $tax, ($grandTotal-($shipping+$tax)), $order->getData() ));

                // If the total is changed, ww will update the change to Magento
                if( $order_cleared_total != $order_total ) {
                    //$differ = $grandTotal - $subtotal;

                    $changed_orderGrandTotal = $order_total + $shipping + $tax;

                    //debug(false, true, $changed_orderGrandTotal, $grandTotal, $differ);

                    //$order->setGrandTotal($changed_orderGrandTotal);
                    //$order->setSubtotal($order_total);

                    if(!empty($_POST['total_field']) ) {
                        if( in_array($_POST['total_field'], array('order_subtotal')) ) {
                            $order->setSubtotal($order_total);
                            //$order->setGrandTotal($changed_orderGrandTotal);
                        }
                        elseif(in_array($_POST['total_field'], array('order_total', 'order_grand_total', 'order_cleared_grand_total')) ) {
                            $order->setGrandTotal($changed_orderGrandTotal);
                        }
                    }
                    //$order->setSubtotal($order_total);
                    $order->save();

                    $mesg[] = "Order total has been updated";


                    if(function_exists('get_commissionable_order_total')) {
                        $amC = get_commissionable_order_total($order_id, $order_total);
                        $invoice_rows = array();

                        //echo '<pre>'; var_dump( $amC ); die();

                        if(!empty($amC) && !empty($amC['commissionable'])) {
                            $STW_update_sql = "UPDATE stw_data SET `commissionable`='{$amC['commissionable']}', `amount`='{$amC['amount']}'  WHERE invoice_id='{$order_id}'; ";
                            mysqli_query($conn, $STW_update_sql);

                            $STW_select_sql = "SELECT * FROM stw_data WHERE invoice_id='{$order_id}'; ";
                            //echo '<pre>'; var_dump( $STW_select_sql ); die();
                            $STW_query = mysqli_query($conn, $STW_select_sql);

                            while($STW_row = mysqli_fetch_assoc($STW_query)) {
                                $commissioned = ( ($STW_row['percentage']/100) * $amC['commissionable'] ) ;

                                $invoice_rows["{$STW_row['type']}_{$STW_row['report_id']}_{$STW_row['member_id']}_{$STW_row['invoice_id']}"] = " UPDATE  stw_data SET `commissioned`='{$commissioned}' WHERE report_id='{$STW_row['report_id']}' AND  `member_id`='{$STW_row['member_id']}' AND `invoice_id`='{$STW_row['invoice_id']}' AND `type`='{$STW_row['type']}' ; ";

                                mysqli_query($conn, $invoice_rows["{$STW_row['type']}_{$STW_row['report_id']}_{$STW_row['member_id']}_{$STW_row['invoice_id']}"] );
                            }
                        }
                    }


                }

                //debug(true, true, function_exists('validate_amico_member'), $order_referring);

                if( empty($order_referring) || !validate_amico_member($order_referring) ) {
                    $errorMesg[] = "Please enter valid Amico ID";
                }

                //debug(true, true, Mage::getSingleton('core/resource')->getTableName('amorderattr/order_attribute') );

                if( empty($errorMesg) ) {
                    $sql = "UPDATE `".Mage::getSingleton('core/resource')->getTableName('amorderattr/order_attribute')."` ao 
                        INNER JOIN  `".Mage::getSingleton('core/resource')->getTableName('sales/order')."` o ON o.entity_id=ao.order_id
                        SET jareferrer_amicoid='$order_referring'
                        WHERE o.increment_id='$order_id' 
                    ";
                    //echo $sql ; die();
                    Mage::getSingleton('core/resource')->getConnection('core_write')->query($sql);

                    $mesg[] = "Order referring Member has been updated";
                }

                /*$invoices_total = array();

                $invoiceCollection = $order->getInvoiceCollection();
                if( !empty($invoiceCollection) ) {
                    foreach ($invoiceCollection as $invoice) {
                        //var_dump($invoice);
                        $invoiceId = $invoice->getId();
                        //$invoiceIncrementId = $invoice->getIncrementId();

                        $invoices_total[$invoiceId] = $invoice->getGrandTotal();
                        $invoices_instance[$invoiceId] = $invoice;
                    }

                    if( !empty($invoices_total) ) {
                        $invoice_total_value = array_sum($invoices_total);

                        if( $invoice_total_value != $order_total ) {
                            end($invoices_instance);
                            $invoiceThatWillBeChanged = $invoices_instance[ key($invoices_instance) ];

                            reset($invoices_instance);

                            $rest_ofTheInvoices_total = array_sum( array_pop($invoices_instance) );

                            if( !empty( $invoiceThatWillBeChanged->getId() ) ) {
                                $lastInvoice_total = $order_total - $rest_ofTheInvoices_total;
                                if( $lastInvoice_total > -1 ) {
                                    $invoiceThatWillBeChanged->setGrandTotal( $lastInvoice_total );
                                    $invoiceThatWillBeChanged->save();
                                }
                            }
                        }
                    }
                }*/

            }
            catch (Exception $e) {
                $errorMesg[] = $e->getMessage();
                $errorMesg[] = "Something went wrong while updating Order Total";
            }
        }
        else {
            $sql3 = "UPDATE orders_total SET value='" . $order_total . "' WHERE orders_id='" . $order_id . "' AND title='Total:'";
            $res3 = mysqli_query($conn, $sql3) or die(mysql_error());
            $mesg[] = "Order total has been updated";
        }
    }
    elseif( ($_REQUEST['goto'] == 'delete') ) {
        if( is_get_orders_from_magento() ) {
            try {
                // Including Magento
                include_once ( base_shop_path() . "/app/Mage.php");
                Mage::reset();
                $app = Mage::app();

                $order = Mage::getModel('sales/order')->loadByIncrementId( $order_id );

                /*
                 * http://inchoo.net/magento/programming-magento/how-to-delete-magento-product-from-frontend-template-code-from-view-files/
                 */
                Mage::register('isSecureArea', true);
                $order->delete();
                Mage::unregister('isSecureArea');

                $mesg[] = "Order has been deleted";

            }
            catch (Exception $e) {
                debug(false, true, $e);
                $errorMesg[] = "Something went wrong while deleting Order";
            }
        }
        else {
            $sql = "DELETE FROM orders WHERE orders_id='" . $order_id . "'";
            $res = mysqli_query($conn, $sql) or die(mysql_error());

            $sql2 = "DELETE FROM orders_total WHERE orders_id='" . $order_id . "'";
            $res2 = mysqli_query($conn, $sql2) or die(mysql_error());

            $sql3 = "DELETE FROM orders_products WHERE orders_id='" . $order_id . "'";
            $res3 = mysqli_query($conn, $sql3) or die(mysql_error());

            $mesg[] = "Order has been deleted";
        }
    }


}


$ten_days_back = strtotime("-20 days");

if( empty($_POST['daterange']) && !empty($_REQUEST['start_date']) && strpos($_REQUEST['start_date'], '-') ) {
    list($_REQUEST['start_date_y'], $_REQUEST['start_date_m'], $_REQUEST['start_date_d']) = explode('-', $_REQUEST['start_date']);
}
if( empty($_POST['daterange']) && !empty($_REQUEST['end_date']) && strpos($_REQUEST['end_date'], '-') ) {
    list($_REQUEST['end_date_y'], $_REQUEST['end_date_m'], $_REQUEST['end_date_d']) = explode('-', $_REQUEST['end_date']);
}

$start_date_d = ( !empty($_REQUEST['start_date_d']) ? filter_var($_REQUEST['start_date_d'], FILTER_SANITIZE_NUMBER_INT) : date('d', $ten_days_back) );
$start_date_m = ( !empty($_REQUEST['start_date_m']) ? filter_var($_REQUEST['start_date_m'], FILTER_SANITIZE_NUMBER_INT) : date('m', $ten_days_back) );
$start_date_y = ( !empty($_REQUEST['start_date_y']) ? filter_var($_REQUEST['start_date_y'], FILTER_SANITIZE_NUMBER_INT) : date('Y', $ten_days_back) );
$start_date_m_name = date('F', mktime(0, 0, 0, $start_date_m, 10));
$end_date_d = ( !empty($_REQUEST['end_date_d']) ? filter_var($_REQUEST['end_date_d'], FILTER_SANITIZE_NUMBER_INT) : date('d') );
$end_date_m = ( !empty($_REQUEST['end_date_m']) ? filter_var($_REQUEST['end_date_m'], FILTER_SANITIZE_NUMBER_INT) : date('m') );
$end_date_y = ( !empty($_REQUEST['end_date_y']) ? filter_var($_REQUEST['end_date_y'], FILTER_SANITIZE_NUMBER_INT) : date('Y') );
$end_date_m_name = date('F', mktime(0, 0, 0, $end_date_m, 10));

$start_date = "$start_date_y-$start_date_m-$start_date_d";
$end_date = "$end_date_y-$end_date_m-$end_date_d";

//echo '<pre>'; var_dump($start_date_d, $_REQUEST, $start_date); die();

$limit = 100;
$page = ((!empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) ? $_REQUEST['page'] : 1);

$limit_start = ($page * $limit) - $limit;
$limit_end = ($page * $limit);


$action_page__id_handler = 'orderid';



if( is_get_orders_from_magento() ) {
    $sql = "SELECT o.entity_id, o.increment_id AS orders_id, o.protect_code, o.customer_id, CONCAT(o.customer_firstname, ' ', o.customer_lastname) AS customers_name, 
    DATE_FORMAT(o.created_at, '%m/%d/%Y') as date_purch, FORMAT(o.grand_total, 2) AS order_grand_total, FORMAT((o.grand_total-(o.shipping_amount+o.tax_amount)), 2) AS order_cleared_grand_total,
    FORMAT(o.subtotal, 2) AS order_total, FORMAT(o.subtotal, 2) AS order_subtotal, oa.jareferrer_amicoid AS refering_member, IF( (oa.jareferrer_self=0), 'No', 'Yes' ) AS is_member_self, 
    IF(oa.ja_oldsite_order_id=0, 'N/A', oa.ja_oldsite_order_id) AS oldsite_order_id, CONCAT(c.customers_firstname, ' ', c.customers_lastname) AS refering_member_name
    
    FROM ".MAGENTO_TABLE_PREFIX."sales_flat_order AS o
    INNER JOIN ".MAGENTO_TABLE_PREFIX."amasty_amorderattr_order_attribute AS oa ON oa.order_id = o.entity_id
    INNER JOIN tbl_member m ON m.amico_id=oa.jareferrer_amicoid
    INNER JOIN customers c ON m.int_customer_id=c.customers_id
    ";

    $sortby = '';
    $sortby = "ORDER BY o.created_at DESC";

    //$start_date = strtotime("$start_date_d $start_date_m_name, $start_date_y");
    //$end_date = strtotime("$end_date_d $end_date_m_name, $end_date_y");

    //$conditions[] = " o.refering_member != '' ";
    //$conditions[] = " o.refering_member != 'None' ";
    $conditions[] = "  ( oa.ja_affiliate_member_id = '' OR oa.ja_affiliate_member_id IN (0, NULL, 'N/A', 'n/a', 'N / A') ) ";
    $conditions[] = "  oa.jareferrer_self = 0 ";
    $conditions[] = "  oa.jareferrer_amicoid NOT IN ( '0', '') ";
    $conditions[] = " o.created_at  >= '$start_date 00:00:00' ";
    $conditions[] = " o.created_at  <= '$end_date 23:59:59' ";

    $field_details = array(
        'orders_id' => 'Order ID',
        'customers_name' => 'Customer Name',
        'date_purch' => 'Date Purchased',
        'refering_member' => array(
            'type' => 'html',
            'name' => 'Referring Member',
            'prefix' => '<form method="post" action="'.$action_page_url.'">',
            'html' => '
            <div class="row form-group">
                <div class="col-lg-5">
                    <input type="text" name="referring_id" class="form-control" value="ORDER_REFERRING_FIELD_VALUE" size="20">
                </div>
                <div class="col-lg-6">
                    <span>ORDER_REFERRING_NAME_VALUE</span>
                </div>
                <div class="mb-md hidden-lg hidden-xl"></div>
            </div>
            ',
            'id_field' => array(
                'ID_FIELD_VALUE' => 'orders_id',
                'ORDER_REFERRING_FIELD_VALUE' => 'refering_member',
                'ORDER_REFERRING_NAME_VALUE' => 'refering_member_name'
            ),
        ),
        //'order_total' => array(
        //'order_subtotal' => array(
        //'order_grand_total' => array(
        'order_cleared_grand_total' => array(
            'type' => 'html',
            'name' => 'Order Total',
            //'prefix' => '<form method="post" action="'.$action_page_url.'">',
            'html' => '

            <input type="hidden" name="order_id" value="ID_FIELD_VALUE">
            <input type="hidden" name="goto" value="update">
            <input type="hidden" name="start_date" value="'.$start_date.'">
            <input type="hidden" name="end_date" value="'.$end_date.'">
            <input type="hidden" name="sort" value="'.$sort.'">
            <input type="hidden" name="page" value="'.$page.'">
            <div class="row form-group">
                <div class="col-lg-7">
                    <input type="text" name="total" class="form-control" value="ORDER_TOTAL_FIELD_VALUE" size="10">
                </div>
                <div class="mb-md hidden-lg hidden-xl"></div>
                <div class="col-lg-4">
                    <input type="hidden" name="total_field" value="order_cleared_grand_total" >
                    <button type="submit" name="submit" value="Update" class="command btn btn-primary btn-warning">Update</button>
                </div>
            </div>
            ',
            'suffix' => '</form>',
            'id_field' => array(
                'ID_FIELD_VALUE' => 'orders_id',
                //'ORDER_TOTAL_FIELD_VALUE' => 'order_total',
                //'ORDER_TOTAL_FIELD_VALUE' => 'order_subtotal'
                //'ORDER_TOTAL_FIELD_VALUE' => 'order_grand_total'
                'ORDER_TOTAL_FIELD_VALUE' => 'order_cleared_grand_total'
            ),
        ),
        'actions' => 'Commands',
    );

    $magento_order_update_page = true;

    $id_field = 'orders_id';
    $no_edit_button = true;

} else {
    $sql = "SELECT o.orders_id, o.customers_name, DATE_FORMAT(o.date_purchased, '%m/%d/%Y') as date_purch, o.refering_member, ot.value AS order_total
        FROM orders AS o
        INNER JOIN orders_total ot ON ot.orders_id = o.orders_id
";

    $sortby = '';
    $sortby = "ORDER BY o.date_purchased DESC";

//debug(true, true, $designations, ( in_array($designations, array('', NULL, null, false)) ) );
    $conditions[] = " o.refering_member != '' ";
    $conditions[] = " o.refering_member != 'None' ";
    $conditions[] = " o.date_purchased  >= '$start_date' ";
    $conditions[] = " o.date_purchased  <= '$end_date' ";
    $conditions[] = " ot.title='Total:' ";


    $field_details = array(
        'orders_id' => 'Order ID',
        'customers_name' => 'Customer Name',
        'date_purch' => 'Date Purchased',
        'refering_member' => 'Referring Member',
        'order_total' => array(
            'type' => 'html',
            'name' => 'Order Total',
            'prefix' => '<form method="post" action="'.$action_page_url.'">',
            'html' => '

                <input type="hidden" name="order_id" value="ID_FIELD_VALUE">
                <input type="hidden" name="goto" value="update">
                <input type="hidden" name="start_date" value="'.$start_date.'">
                <input type="hidden" name="end_date" value="'.$end_date.'">
                <input type="hidden" name="sort" value="'.$sort.'">
                <input type="hidden" name="page" value="'.$page.'">
                <div class="row form-group">
                    <div class="col-lg-7">
                        <input type="text" name="total" class="form-control" value="ORDER_TOTAL_FIELD_VALUE" size="10">
                    </div>
                    <div class="mb-md hidden-lg hidden-xl"></div>
                    <div class="col-lg-4">
                        <button type="submit" name="submit" value="Update" class="command btn btn-primary btn-warning">Update</button>
                    </div>
                </div>
            ',
            'suffix' => '</form>',
            'id_field' => array(
                'ID_FIELD_VALUE' => 'orders_id',
                'ORDER_TOTAL_FIELD_VALUE' => 'order_total'
            )
            ,
        ),
        'actions' => 'Commands',
    );

    $id_field = 'orders_id';
}


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

?>

<style>
    .action_panel { margin-bottom: 50px; }
</style>
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
        <section class="panel">
            <div class="col-xs-12">
                <header class="panel-heading">
                    <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                </header>
                <div class="panel-body">
                    <?php if(!empty($mesg) || !empty($errorMesg)): ?>
                        <div class="row">
                            <div class="col-xs-12 ">
                                <?php if( !empty($mesg) ) { ?>
                                    <div class="alert alert-success">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        <?php if(is_array($mesg)) : ?>
                                            <ul>
                                            <?php foreach($mesg as $sm): ?>
                                                <li><?php echo $sm; ?></li>
                                            <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <?php echo $mesg; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php } ?>
                                <?php if(!empty($errorMesg)) { ?>
                                    <div class="alert alert-danger">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        <?php if(is_array($errorMesg)): ?>
                                            <ul>
                                            <?php foreach($errorMesg as $sm): ?>
                                                <li><?php echo $sm; ?></li>
                                            <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <?php echo $errorMesg; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="row action_panel">
                        <div class="col-lg-8 col-sm-8 col-xs-12 filter_wrapper ">
                            <div class="row">
                                <div class="col-lg-6 col-xs-12 centering date_range_wrapper">
                                    <form method="POST" action="">
                                        <input type="hidden" name="daterange" value="1" />
                                        <div class="table-responsive">
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
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-4 col-xs-12">
                            <div class="row">
                                <div class="col-xs-12 centering ">
                                    <div class="row">
                                        <div class="col-xs- export_button_wrapper centering">
                                            <button class="command btn btn-success" onclick="document.location.href='update_order_total_export.php?sd=<?php echo $start_date; ?>&ed=<?php echo $end_date; ?>'"
                                                >Export Order Totals</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <?php require_once('display_members_data.php'); ?>
                </div>
            </div>
            <div class="clearfix"></div>
        </section>
    </div>
</div>


<?php
require_once("templates/footer.php");