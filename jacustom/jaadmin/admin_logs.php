<?php

$page_title = 'John Amico - Members Logs';

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


if (isset($_POST['adminid']) and $_POST['adminid'] > 0) {
    $rsseladmin = mysqli_query($conn, "select * from tbl_admin WHERE int_admin_id = {$_POST['adminid']}");
    $nr = "NO";
    list($adminid, $firstname, $lastname, $admin_username, $admin_pass, $email, $active)
        = mysqli_fetch_row($rsseladmin);
}
else {
    $nr = "YES";
}


$start_date_d = ( !empty($_REQUEST['start_date_d']) ? $_REQUEST['start_date_d'] : date('d') );
$start_date_m = ( !empty($_REQUEST['start_date_m']) ? $_REQUEST['start_date_m'] : date('m') );
$start_date_y = ( !empty($_REQUEST['start_date_y']) ? $_REQUEST['start_date_y'] : date('Y') );
$end_date_d = ( !empty($_REQUEST['end_date_d']) ? $_REQUEST['end_date_d'] : date('d') );
$end_date_m = ( !empty($_REQUEST['end_date_m']) ? $_REQUEST['end_date_m'] : date('m') );
$end_date_y = ( !empty($_REQUEST['end_date_y']) ? $_REQUEST['end_date_y'] : date('Y') );

$page = ( !empty($_REQUEST['page']) && is_numeric($_REQUEST['page']) ? filter_var($_REQUEST['page'], FILTER_SANITIZE_NUMBER_INT) : 1 );
$limit = 25;

$limit_start = ($page * $limit) - $limit;
$limit_end = ($page * $limit);


$count_sql = "select COUNT(int_admin_id) as count from tbl_admin_logs where 1=1 ";
$count_sql .= " and '$start_date_y-$start_date_m-$start_date_d 00:00:00'<=dt and dt<='$end_date_y-$end_date_m-$end_date_d 23:59:59' ";
$count_query = mysqli_query($conn, $count_sql);

$total_records = mysqli_fetch_object($count_query);
$total_pages = ceil($total_records->count / $limit);
$total_records = $total_records->count;


$sql = "select int_admin_id, str_username, date_format(dt, '%m.%d.%Y %h:%i %p') as dt_nice, dt as dt_hidden from tbl_admin_logs where 1=1 ";
$sql .= " and '$start_date_y-$start_date_m-$start_date_d 00:00:00'<=dt and dt<='$end_date_y-$end_date_m-$end_date_d 23:59:59' ";
$sql .= " order by dt_nice desc LIMIT $limit OFFSET $limit_start";

//echo $sql; die();


$query = mysqli_query($conn, $sql);



$camp_start = "id";
$order_start = "desc";
$l2 = 25;
$fields_alias = array(
    "int_admin_id" => "Amico ID",
    "str_username" => "Name",
    "dt_nice" => "Date"
);
$order_coresp_array = array("dt" => "dt_nice");

$no_results_msg = "<br><br><font class=black1>Sorry, there are no logs for the time interval you selected.";

$script_name = "admin_logs.php?start_date_d=$start_date_d&start_date_m=$start_date_m&start_date_y=$start_date_y&end_date_d=$end_date_d&end_date_m=$end_date_m&end_date_y=$end_date_y&page=$page";
$prev = "admin_logs.php?start_date_d=$start_date_d&start_date_m=$start_date_m&start_date_y=$start_date_y&end_date_d=$end_date_d&end_date_m=$end_date_m&end_date_y=$end_date_y&page=".($page-1);
$next = "admin_logs.php?start_date_d=$start_date_d&start_date_m=$start_date_m&start_date_y=$start_date_y&end_date_d=$end_date_d&end_date_m=$end_date_m&end_date_y=$end_date_y&page=".($page+1);
$head_title = "<a href=\"print_admin_logs.php?start_date_d=$start_date_d&start_date_m=$start_date_m&start_date_y=$start_date_y&end_date_d=$end_date_d&end_date_m=$end_date_m&end_date_y=$end_date_y\" class=black1 target=_blank><b>Print</b></a>";

?>



    <div role="main" class="content-body">
        <header class="page-header">
            <h2>Members Logs</h2>

            <div class="right-wrapper pull-right">
                <ol class="breadcrumbs">
                    <li>
                        <a href="<?php echo base_admin_url(); ?>">
                            <i class="fa fa-home"></i>
                        </a>
                    </li>
                    <li><span>Members Logs</span></li>
                </ol>


                <a class="sidebar-right-toggle"></a>
            </div>
        </header>

        <div class="row">
            <div class="col-xs-12">
                <div class="panel-body">
                    <div class="table-responsive">
                        <div class="col-lg-4 col-md-8 col-sm-10 col-xs-12 centering date_range_wrapper">
                            <form method="POST">
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
                                            <input type="submit" class="mb-xs mt-xs mr-xs btn btn-xs btn-primary" value="Filter">
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </div>

                        <div class="col-sm-10 col-sm-offset-1">
                            <?php
                            if(mysqli_num_rows($query) > 0) {
                            ?>

                                <div class="pagination_wrapper">
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-3 total_page">
                                            <?php echo 'Page: ' . $page . '/' . $total_pages; ?>
                                        </div>
                                        <div class="col-xs-12 col-sm-9 total_records next_prev_pagination">
                                            <?php echo 'Total: ' . $total_records . ' records. Displaying: ' . $limit_start . ' - ' . $limit_end . '.   '; ?>
                                            <?php echo ( ($page > 1) ? '<a href="'. $prev .'">Prev</a>  ' : ''); ?>
                                            <?php echo ( ($total_pages > $page) ? '<a href="'. $next .'">Next</a>' : ''); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <table class="table table-bordered table-striped mb-none">
                                <thead>
                                    <tr>
                                        <th>Amico ID</th>
                                        <th>Name</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                if(mysqli_num_rows($query) > 0) {
                                    while ($res = mysqli_fetch_object($query)) {
                                        echo '<tr> <td>' . $res->int_admin_id . '</td> <td>' . $res->str_username . '</td> <td>' . $res->dt_nice . '</td>  </tr>';
                                    }
                                } else {
                                    echo '<tr> <td align="center" colspan="3">Sorry, there are no logs for the time interval you selected.</td>  </tr>';
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>

<?php
require_once("templates/footer.php");

