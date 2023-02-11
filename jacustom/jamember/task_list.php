<?php
$page_name = 'Task List';
$page_title = 'John Amico - ' . $page_name;

$is_popup = !empty($_GET['popup']) ? 1 : 0;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

if( !$is_popup ) {
    require_once("templates/header.php");
    require_once("templates/sidebar.php");
} else {
    $display_header = false;
    require_once("templates/header.php");
}



$member_type_name = 'Task';
$member_type_name_plural = 'Tasks';
$self_page = 'task_list.php';
$page_url = base_member_url() . "/$self_page?";
$action_page = 'task_list.php';
$action_page_url = base_member_url() . "/$self_page?1=1";
//$export_url = base_admin_url() . '/members_export.php';


$is_edit = $is_add = false;
$success_message = $error_message = $member_search_result = $conditions = array();

//debug(false, true, $_POST);

$member_id = $_SESSION['member']['ses_member_id'];

$mesg = ( !empty($_GET['msg']) ? $_GET['msg'] : '' );
$memberid = ( !empty($_GET['mem_id']) ? $_GET['mem_id'] : '' );



if(!$is_edit && !$is_add && !empty($member_id)) {

    $limit = 50;
    $page = ((!empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) ? $_REQUEST['page'] : 1);

    $limit_start = ($page * $limit) - $limit;
    $limit_end = ($page * $limit);


    $conditions = $sortby = array();

    //debug(true, false, $status_filter, (!in_array((string)$_REQUEST['params']['status_filter'], array('1','0'), true)), $_POST);

    $sql = "select int_schedule_list_id,dtt_schedule,tme_schedule, CONCAT( DATE_FORMAT(dtt_schedule, '%D %b, %Y'),' ', CONCAT( SUBSTRING(tme_schedule, 1, LENGTH(tme_schedule)-3) , ' ', str_schedule_meridian) ) as datetime,str_schedule_meridian,str_contact,str_reason
            from tbl_schedule_list
    ";

    $sortby = '';
    $sortby = "order by dtt_schedule desc";

    $conditions[] = "int_member_id='$member_id'";

    if( !empty($_POST['filter']) && ( !empty($_POST['datestart']) || !empty($_POST['dateend']) ) ) {

        if( !empty($_POST['datestart']) ) {
            $time = strtotime($_POST['datestart']);
            $starttime = date('Y-m-d', $time);

            if(!empty($time)) {
                $conditions[] = " dtt_schedule >= '$starttime' ";
            }
        }
        if( !empty($_POST['dateend']) ) {
            $time = strtotime($_POST['dateend']);
            $endtime = date('Y-m-d', $time);

            if(!empty($time)) {
                $conditions[] = " dtt_schedule <= '$endtime' ";
            }
        }
    }

    if( !empty($memberid) ) {
        $customer_sql = "SELECT int_customer_id FROM tbl_member WHERE int_member_id='$memberid' LIMIT 1";
        $customer_query = mysqli_query($conn, $customer_sql);
        list($customerid) = mysqli_fetch_array($customer_query);

        $conditions[] = " customers_id = '$customerid' ";
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    $sql .= " $sortby ";


    //debug(true, true, $conditions, $sql );


    $field_details = array(
        'datetime' => 'Scheduled Date',
        'str_contact' => 'Contact Person',
        'str_reason' => 'Regarding',
    );

    $id_field = 'int_schedule_list_id';

    $action_page__id_handler = 'noteid';


    //$query_pag_data = " $condition LIMIT $start, $per_page";
    $data_num_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

    mysqli_store_result($conn);
    $numrows = mysqli_num_rows($data_num_query);

    //echo $sql;

    $sql .= " LIMIT $limit OFFSET $limit_start ";
    //echo $sql;
    $data_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

}

?>

    <div role="main" class="content-body <?php echo ( $is_popup ? 'no-margin-left' : '' ); ?> ">
        <?php if(!$is_popup): ?>
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
        <?php endif; ?>

        <div class="row ">
            <?php if(!$is_edit && !$is_add): ?>
                <section class="panel filter-panel">
                    <div class="col-lg-4 col-md-6 col-xs-12 centering">
                        <form name="show_commissions" class="form form-validate form-bordered" action="" method="post">
                            <?php if( !$is_popup ) : ?>
                                <header class="panel-heading">
                                    <h2 class="panel-title text-center">Filter <?php echo $member_type_name_plural; ?></h2>
                                </header>
                            <?php endif;?>

                            <div class="panel-body ">
                                <div class="row">
                                    <div class="col-xs-12 centering">
                                        <div class="panel-body ">
                                            <?php if( $is_popup ) : ?>
                                                <div class="col-xs-12 buttons">
                                                    <div class="text-center">
                                                        <a href="./calender.php?popup=1&memberid=<?php echo $memberid;?>" class="btn btn-primary">Go to Events Calender</a>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="row form-group form-inline">
                                                    <label class="col-md-4 control-label" for="daterange">Date Range</label>
                                                    <div class="col-md-8">
                                                        <select name="daterange" id="daterange" class="form-control" >
                                                            <option value="" data-start data-end>Select Date Range</option>
                                                            <?php
                                                            $now = time();

                                                            $today = date('Y/m/d', $now);
                                                            $nextday = date('Y/m/d', strtotime('+1 day', $now));

                                                            $this_week_date_string = date('Y', $now) . 'W' . date('W', $now);
                                                            $this_week_start = date('Y/m/d', strtotime($this_week_date_string, $now));
                                                            $this_week_end = date('Y/m/d', strtotime($this_week_date_string . '7', $now));

                                                            $next_week_date_string = date('Y', $now) . 'W' . (date('W', $now)+1);
                                                            $next_week_start = date('Y/m/d', strtotime($next_week_date_string, $now));
                                                            $next_week_end = date('Y/m/d', strtotime($next_week_date_string . '7', $now));

                                                            //$next_week_start = date('Y/m/d', strtotime('-'.$day.' days'));
                                                            //$next_week_end = date('Y/m/d', strtotime('+'.(6-$day).' days'));

                                                            $time_ranges = array(
                                                                'today' => array(
                                                                    'start' => $today,
                                                                    'end' => $today,
                                                                    'name' => 'Today'
                                                                ),
                                                                'tomorrow' => array(
                                                                    'start' => $nextday,
                                                                    'end' => $nextday,
                                                                    'name' => 'Tomorrow'
                                                                ),
                                                                'this_week' => array(
                                                                    'start' => $this_week_start,
                                                                    'end' => $this_week_end,
                                                                    'name' => 'This Week'
                                                                ),
                                                                'next_week' => array(
                                                                    'start' => $next_week_start,
                                                                    'end' => $next_week_end,
                                                                    'name' => 'Next Week'
                                                                ),
                                                                'past' => array(
                                                                    'start' => '',
                                                                    'end' => $today,
                                                                    'name' => 'Past'
                                                                ),
                                                                'future' => array(
                                                                    'start' => $today,
                                                                    'end' => '',
                                                                    'name' => 'Future'
                                                                ),
                                                            );

                                                            foreach($time_ranges as $key=>$time_range) {
                                                                $selected = ( !empty($_POST['daterange']) && ($_POST['daterange'] == $key) ) ? ' selected ' : '';

                                                                echo "<option $selected value='$key' data-start='{$time_range['start']}' data-end='{$time_range['end']}'>{$time_range['name']}</option>";
                                                            }

                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row form-group form-inline">
                                                    <label class="col-md-4 control-label" for="datestart">Start Date</label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" id="datestart" name="datestart" value="<?php echo ( !empty($starttime) ? $starttime : '' ); ?>">
                                                    </div>
                                                </div>
                                                <div class="row form-group form-inline">
                                                    <label class="col-md-4 control-label" for="dateend">End Date</label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" id="dateend" name="dateend" value="<?php echo ( !empty($endtime) ? $endtime : '' ); ?>">
                                                    </div>
                                                </div>
                                                <script>
                                                    jQuery(document).ready(function($){
                                                        $('#daterange').on('change', function(){
                                                            var value = $('option:selected', this);
                                                            var datestart = value.attr('data-start');
                                                            var dateend = value.attr('data-end');

                                                            $('#datestart').val(datestart);
                                                            $('#dateend').val(dateend);
                                                        });
                                                    });
                                                </script>
                                            <?php endif;?>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <?php if( !$is_popup ) : ?>
                                <footer class="panel-footer text-center">
                                    <input type="hidden" name="goto" value="filter">
                                    <input type="submit" value="Filter" name="filter" />
                                </footer>
                            <?php endif;?>
                        </form>
                    </div>
                </section>
                <section class="panel">
                    <div class="<?php echo ( !$is_popup ? 'col-sm-8' : '' );?> col-xs-12 centering">
                        <header class="panel-heading">
                            <h2 class="panel-title text-center">List of <?php echo $member_type_name_plural; ?></h2>
                        </header>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-12  ">
                                    <?php if(!empty($mesg)): ?>
                                        <div class="message  pb-lg pt-lg mb-lg mt-lg">
                                            <div class="alert alert-success">
                                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                                <?php echo $mesg; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php require_once("../$admin_path/display_members_data.php"); ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </section>
            <?php endif; ?>
        </div>
    </div>


<?php
require_once("templates/footer.php");
