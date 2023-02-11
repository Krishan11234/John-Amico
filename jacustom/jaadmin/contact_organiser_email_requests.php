<?php
$page_name = 'Contact Organiser Email Requests';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


function mail_queue_status_text($status=0) {
    if(!empty($status) ) {
        if( in_array($status, array(1)) ) {
            return "<div class='alert alert-info' style='margin-bottom: 0px;'><strong>Running</strong></div>";
        }
        if( in_array($status, array(2, 3)) ) {
            return "<div class='alert alert-success' style='margin-bottom: 0px;'><strong>Completed</strong></div>";
        }
    }
    return "<div class='alert alert-warning' style='margin-bottom: 0px;'><strong>Pending</strong></div>";
}
function full_name_text($name, $amicoId) {
    $text = $name;
    if(!empty($amicoId)) {
        $text .= " (<strong>{$amicoId}</strong>)";
    }
    return $text;
}

$member_type_name = 'Member';
$member_type_name_plural = 'Members';
$self_page = basename(__FILE__);
$self_page_url = base_admin_url(). "/{$self_page}";
$page_url = base_admin_url() . "/{$self_page}?1=1";
$action_page = $self_page;
$action_page_url = base_admin_url() . "/{$action_page}?1=1";


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

$limit = 25;
$page = ((!empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) ? $_REQUEST['page'] : 1);

$limit_start = ($page * $limit) - $limit;
$limit_end = ($page * $limit);


$action_page__id_handler = 'orderid';



$sql = "SELECT COUNT(mlc.chunk_id) AS total_mail_queue, mlce.*, CONCAT(c.customers_firstname, ' ', c.customers_lastname) AS full_name, mlce.status AS mail_queue_status, mlce.subject AS email_subject
FROM mail_log_queue_complete_email AS mlce
INNER JOIN mail_log_chunk AS mlc ON mlce.queue_mail_id = mlc.queue_mail_id
INNER JOIN tbl_member AS tm ON mlce.sender_id = tm.amico_id
INNER JOIN customers AS c ON c.customers_id = tm.int_customer_id
";

$sortby = $groupBy = '';
$groupBy = "GROUP BY mlc.queue_mail_id ";
$sortby = "ORDER BY mlce.created DESC";

//$start_date = strtotime("$start_date_d $start_date_m_name, $start_date_y");
//$end_date = strtotime("$end_date_d $end_date_m_name, $end_date_y");

//$conditions[] = " o.refering_member != '' ";
//$conditions[] = " o.refering_member != 'None' ";
$conditions[] = " mlce.created  >= '$start_date 00:00:00' ";
$conditions[] = " mlce.created  <= '$end_date 23:59:59' ";

$field_details = array(
    'full_name' => array(
        'field_type' => 'text_from_callback',
        'name' => 'Sender',
        'value' => array(
            'function' => 'full_name_text',
            'params' => array('full_name', 'sender_id'),
            'params_field' => array('full_name', 'sender_id'),
        ),
    ),
    'sender_email' => 'Email',
    'email_subject' => 'Subject',
    'created' => 'Created',
    'total_mail_queue' => 'Total Emails',
    'mail_queue_status' => array(
        'field_type' => 'text_from_callback',
        'name' => 'Status',
        'value' => array(
            'function' => 'mail_queue_status_text',
            'params' => array('mail_queue_status'),
            'params_field' => array('mail_queue_status'),
        ),
    ),
);

$magento_order_update_page = true;

$id_field = 'orders_id';
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
                    <div class="row">
                        <div class="col-xs-12 filter_wrapper ">
                            <div class="table-responsive">
                                <div class="col-lg-4 col-md-8 col-sm-10 col-xs-12 centering date_range_wrapper">
                                    <form method="GET" action="">
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
                    <?php require_once('display_members_data.php'); ?>
                </div>
            </div>
            <div class="clearfix"></div>
        </section>
    </div>
</div>


<?php
require_once("templates/footer.php");