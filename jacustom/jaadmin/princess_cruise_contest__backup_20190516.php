<?php
$page_name = 'Princess Cruise Incentive Contest';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("../common_files/include/putnaIncentiveFunction.php");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


$self_page = basename(__FILE__);
$page_url = base_admin_url() . "/{$self_page}?1=1";
$action_page = $self_page;
$export_page = base_admin_url() . "/princess_cruise_contest_export.php";
$action_page_url = base_admin_url() . "/{$self_page}?1=1";


$reportGenerator = new IncentiveReportGenerator($conn, 'princess_cruise', 5);
$reportGenerator->setStartTime('2018-01-01 00:00:00')->setEndTime('2018-08-31 11:59:59');
$thisTableReady = $reportGenerator->getPutnaTableReady();

if($thisTableReady) {
    $limit = 25;
    $page = ((!empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) ? $_REQUEST['page'] : 1);

    $limit_start = ($page * $limit) - $limit;
    $limit_end = ($page * $limit);


    $sql = " SELECT m.int_member_id, m.amico_id, ir.total_sale, ir.incentive_percentage, CONCAT(c.customers_firstname, ' ', c.customers_lastname) AS full_name, ( ir.total_sale*(ir.incentive_percentage/100)) AS total_incentive";
    $sql .= " FROM `{$reportGenerator->getIncentiveReportTableName()}` ir ";
    $sql .= " INNER JOIN tbl_member m ON ir.member_id = m.int_member_id";
    $sql .= " INNER JOIN customers c ON m.int_customer_id = c.customers_id";
    if($reportGenerator->getIncentiveTypeColumnReady() && !empty($reportGenerator->getIncentiveType()) ) {
        $sql .= " WHERE ir.incentive_type='{$reportGenerator->getIncentiveType()}' ";
    }

    $sortby = $groupBy = '';
    $sortby = "ORDER BY ir.total_sale DESC";

    $field_details = array(
        'full_name' => 'Member Name',
        'amico_id' => 'Amico ID',
        'total_sale' => array(
            'name' => 'Total Sale',
            'field_type' => 'text_from_callback',
            'value' => array(
                'function' => 'number_format',
                'params' => array('total_sale', 2),
                'params_field' => array('total_sale', ''),
                'prefix' => '$'
            ),
        ),
        'incentive_percentage' => 'Incentive Percentage (%)',
        'total_incentive' => array(
            'field_type' => 'text_from_callback',
            'name' => 'Incentive Will Receive',
            'value' => array(
                'function' => 'number_format',
                'params' => array('total_incentive', 2),
                'params_field' => array('total_incentive', ''),
                'prefix' => '$'
            ),
        ),
    );

    $no_edit_button = true;


    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    $sql .= " $groupBy $sortby ";

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
        <section class="panel">
            <div class="col-xs-12 header_buttons_wrapper text-center centering">
                <div class="panel-body">
                    <a href="<?php echo $export_page; ?>" class="btn btn-primary ">Export Report</a>
                </div>
            </div>
        </section>
        <section class="panel">
            <div class="col-xs-12">
                <header class="panel-heading">
                    <h2 class="panel-title text-center"><?php echo $page_name; ?> (<?php echo $reportGenerator->getStartTimeInWords(); ?> - <?php echo $reportGenerator->getEndTimeInWords(); ?>)</h2>
                </header>
                <div class="panel-body">
                    <?php require_once('display_members_data.php'); ?>
                </div>
            </div>
            <div class="clearfix"></div>
        </section>
    </div>
</div>


<?php
require_once("templates/footer.php");