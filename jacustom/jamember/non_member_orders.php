<?php
$page_name = 'Web Orders';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$is_popup = true;

require_once("templates/header.php");
//require_once("templates/sidebar.php");


//if( is_in_live() ) { exit(''); }


function get_autoship_text($member_id, $autoshipYear, $autoshipEnabled=null) {
    $autoshipEnabled = is_null($autoshipEnabled) ? is_autoship_enable() : $autoshipEnabled;

    if (!empty($member_id) && $autoshipEnabled && $autoshipYear) {
        $text = "Yes (On Every {$autoshipYear})";
    }
    else {
        $text = '--';
    }
    return $text;
}
function get_vipconsumer_text($member_id, $vipConsumer, $vipEnabled=null) {
    $vipEnabled = is_null($vipEnabled) ? is_vipconsumer_enable() : $vipEnabled;

    if (!empty($member_id) && $vipEnabled && $vipConsumer) {
        $text = "Yes";
    }
    else {
        $text = '--';
    }
    return $text;
}


$member_type_name = 'Orders';
$member_type_name_plural = 'Members';
$self_page = basename(__FILE__);
$page_url = base_member_url() . "/{$self_page}?1=1&" . http_build_query($_GET);
$action_page = $self_page;
$action_page_url = base_member_url() . "/{$self_page}?1=1&" . http_build_query($_GET);
$export_url = base_member_url() . "/{$self_page}";


$useMagento = true;


$ten_days_back = strtotime("-20 days");

if( empty($_POST['daterange']) && !empty($_REQUEST['start_date']) && strpos($_REQUEST['start_date'], '-') ) {
    list($_REQUEST['start_date_y'], $_REQUEST['start_date_m'], $_REQUEST['start_date_d']) = explode('-', $_REQUEST['start_date']);
}
if( empty($_POST['daterange']) && !empty($_REQUEST['end_date']) && strpos($_REQUEST['end_date'], '-') ) {
    list($_REQUEST['end_date_y'], $_REQUEST['end_date_y'], $_REQUEST['end_date_y']) = explode('-', $_REQUEST['end_date']);
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

$limit = 30;
$page = ((!empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) ? $_REQUEST['page'] : 1);

$limit_start = ($page * $limit) - $limit;
$limit_end = ($page * $limit);



if( !empty($_GET['memberid']) ) {
    $memberIdAmico = filter_var($_GET['memberid'], FILTER_SANITIZE_STRING);

    //debug(true, true, $_GET, $memberIdAmico);

    try {

        $autoshipEnabled = is_autoship_enable();
        $vipEnabled = is_vipconsumer_enable();
        $vipPlanId = get_vipconsumer_subscription_plan();

        if($useMagento) {
            $query = "SELECT o.entity_id, o.increment_id AS orders_id, o.customer_id, CONCAT(o.customer_firstname, ' ', o.customer_lastname) AS customers_name, DATE_FORMAT(o.created_at, '%m/%d/%Y') as date_purchased, o.grand_total AS order_total, o.customer_id";
            if ($autoshipEnabled) {
                $query .= ", CONCAT(ar.interval_period_number, ' ', ar.interval_period_type) AS autoship_interval_time";
            }
            if ($vipEnabled) {
                //$query .= ", rs.customer_id AS is_vip_consumer";
                $query .= " , IF(rst.plan_id='{$vipPlanId}',rs.customer_id,null) AS is_vip_consumer ";
            }
            $query .= "
                                    FROM " . MAGENTO_TABLE_PREFIX . "sales_flat_order AS o
                                    INNER JOIN " . MAGENTO_TABLE_PREFIX . "amasty_amorderattr_order_attribute AS oa ON oa.order_id = o.entity_id ";
            if ($autoshipEnabled) {
                $query .= " LEFT JOIN " . MAGENTO_TABLE_PREFIX . "mvijaautoship_request AS ar ON ar.mage_order_id = o.entity_id ";
            }
            if ($vipEnabled) {
                $query .= " LEFT JOIN " . MAGENTO_TABLE_PREFIX . "recurringandrentalpayments_subscription AS rs ON rs.customer_id = o.customer_id ";
                $query .= " LEFT JOIN " . MAGENTO_TABLE_PREFIX . "recurringandrentalpayments_terms AS rst ON rst.terms_id = rs.term_type ";
            }
            /*if ($vipEnabled) {
                $query .= " GROUP BY o.entity_id ";
            }*/
            //$query .= " ORDER BY o.created_at DESC ";
        } else {
            $query = "SELECT orders.orders_id, orders.customers_name, orders.date_purchased, orders_products.orders_products_id, orders_products.products_id, orders_total.value
                                  FROM orders
                                  LEFT JOIN orders_products ON orders.orders_id=orders_products.orders_id
                                  LEFT JOIN orders_total ON orders.orders_id=orders_total.orders_id AND orders_total.class='ot_subtotal'
                                  WHERE orders.customers_id='0' AND orders.int_member_id='$member_id'
                            ";
        }

        $sql .= $query;

        $sortby = '';
        if ($vipEnabled && $useMagento) {
            $sortby .= " GROUP BY o.entity_id ";
        }
        $sortby .= " ORDER BY o.created_at DESC ";

//$start_date = strtotime("$start_date_d $start_date_m_name, $start_date_y");
//$end_date = strtotime("$end_date_d $end_date_m_name, $end_date_y");

//$conditions[] = " o.refering_member != '' ";
//$conditions[] = " o.refering_member != 'None' ";
//$conditions[] = "  oa.jareferrer_amicoid NOT IN ( '0', '') ";
//$conditions[] = " jan.created  >= '$start_date' ";
//$conditions[] = " jan.created  <= '$end_date' ";

        if($useMagento) {
            $conditions[] = " ( oa.ja_affiliate_member_id = '' OR oa.ja_affiliate_member_id IN (0, NULL, 'N/A', 'n/a', 'N / A') ) ";
            $conditions[] = " (o.created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59') ";
            $conditions[] = " oa.jareferrer_self = 0";
            $conditions[] = " oa.jareferrer_amicoid='$memberIdAmico' ";
        }

        $field_details = array(
            'customers_name' => 'Customer Name',
            'order_total' => array(
                'field_type' => 'text_from_callback',
                'name' => 'Order amount',
                'value' => array(
                    'function' => 'number_format',
                    'params' => array('order_total', 2),
                    'params_field' => array('order_total', ''),
                ),
                'prefix' => '$',
            ),
            'date_purchased' => 'Date Purchased',
            'orders_id' => array(
                'link' => base_shop_member_order_view_url() . 'ID_FIELD_VALUE',
                'id_field' => 'orders_id',
                'name' => 'View Order',
                'button' => false,
                'attributes' => array(
                    'target' => '_blank',
                ),
                'text_to_display' => 'Order: ID_FIELD_VALUE',
            ),
            'autoship_interval_time' => array(
                'field_type' => 'text_from_callback',
                'name'=>'Is Autoship Enabled Order',
                'value' => array(
                    'function' => 'get_autoship_text',
                    'params' => array($memberIdAmico, 'autoship_interval_time', $autoshipEnabled),
                    'params_field' => array('', 'autoship_interval_time', ''),
                    'check_function' => 'empty',
                    'check_function_params' => array($autoshipEnabled),
                    'check_function_params_field' => array(),
                ),
            ),
            'is_vip_consumer' => array(
                'field_type' => 'text_from_callback',
                'name' => 'VIP Consumer',
                'value' => array(
                    'function' => 'get_vipconsumer_text',
                    'params' => array($memberIdAmico, 'is_vip_consumer', $vipEnabled),
                    'params_field' => array('', 'is_vip_consumer', ''),
                    'check_function' => 'empty',
                    'check_function_params' => array($vipEnabled),
                    'check_function_params_field' => array(),
                ),
            ),
        );



        //$magento_order_update_page = true;

        $id_field = 'stylist_id';
        $no_edit_button = true;
        $no_delete_butotn = true;


        $action_page__id_handler = 'stylistid';

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
    catch (Exception $e) {
        $mesg = "Something went wrong while activating this member.";
    }

}

?>


    <div class="row-fluid non_member_orders_page">
        <section class="panel">
            <div class="col-xs-12">
                <header class="panel-heading">
                    <h2 class="panel-title text-center"><?php echo $page_name; ?> <?php echo (!empty($memberIdAmico) ? "Referred By: {$memberIdAmico}" : ''); ?></h2>
                </header>
                <div class="panel-body">
                    <?php if(!empty($mesg) || !empty($error_mesg) ): ?>
                        <div class="row">
                            <div class="col-xs-12 ">
                                <?php if( !empty($mesg) ) : ?>
                                    <div class="alert alert-success">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        <?php echo $mesg; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if( !empty($error_mesg) ) : ?>
                                    <div class="alert alert-danger">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        <?php echo $error_mesg; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-xs-12 filter_wrapper ">
                            <div class="table-responsive">
                                <div class="col-lg-4 col-md-8 col-sm-10 col-xs-12 centering date_range_wrapper">
                                    <form method="GET" action="<?php echo $action_page_url; ?>">
                                        <input type="hidden" name="memberid" value="<?php echo $memberIdAmico; ?>" />
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
                                                        for ($i = date("Y") - 10; $i <= date("Y"); $i++) {
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
                    <?php require_once( base_admin_path() . '/display_members_data.php'); ?>
                </div>
            </div>
            <div class="clearfix"></div>
        </section>
    </div>


<?php
//require_once("templates/footer.php");