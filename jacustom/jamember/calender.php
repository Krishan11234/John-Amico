<?php
$page_name = 'Calendar of Events';
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



$member_type_name = 'Event';
$member_type_name_plural = 'Events';
$self_page = 'calender.php';
$page_url = base_member_url() . "/$self_page?";
$action_page = 'calender.php';
$action_page_url = base_member_url() . "/$self_page?1=1";
//$export_url = base_admin_url() . '/members_export.php';


$is_edit = $is_add = false;
$success_message = $error_message = $member_search_result = $conditions = array();

$member_id = $_SESSION['member']['ses_member_id'];
$get_member_id = ( !empty($_GET['memberid']) ? filter_var($_GET['memberid'], FILTER_SANITIZE_NUMBER_INT) : '' );

$mesg = ( !empty($_GET['msg']) ? $_GET['msg'] : '' );

$day = filter_var($_GET['wday'], FILTER_SANITIZE_NUMBER_INT);
$month = filter_var($_GET['wmonth'], FILTER_SANITIZE_NUMBER_INT);
$year = filter_var($_GET['wyear'], FILTER_SANITIZE_NUMBER_INT);

$day = !empty($day) ? $day : date('d');
$month = !empty($month) ? $month : date('m');
$year = !empty($year) ? $year : date('Y');


//debug(false, true, $_POST);


$weekdays = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

//echo '<pre>'; print_r($_POST); die();
if(isset($_POST['submit'])) {
    //echo '<pre>'; print_r($_POST); die();
    $customers_id = (isset($_POST['customers_id'])) ? (int)$_POST['customers_id'] : '';

    if($customers_id) {
        if( !empty($_POST['customers_telephone']) ) { $update_set[] = " customers_telephone = '{$_POST['customers_telephone']}' "; }
        if( !empty($_POST['customers_telephone1']) ) { $update_set[] = " customers_telephone1 = '{$_POST['customers_telephone1']}' "; }
        if( !empty($_POST['customers_telephone2']) ) { $update_set[] = " customers_telephone2 = '{$_POST['customers_telephone2']}' "; }
        if( !empty($_POST['mobile_phone']) ) { $update_set[] = " mobile_phone = '{$_POST['mobile_phone']}' "; }

        if(!empty($update_set)) {
            $update_string = implode(' , ', $update_set);
            update_rows($conn, 'customers', $update_string, "WHERE customers_id = '$customers_id' ");
        }
    }


    list($smonth,$sday,$syear) = explode('-',$_POST['sdate']);
    $sdate=date("Y/m/d",mktime(0,0,0,$smonth,$sday,$syear));

    $ctime=$_POST['chour'].':'.$_POST['cmin'];
    $cdate=date("Y/m/d",mktime(0,0,0,$_POST['cmonth'],$_POST['cday'],$_POST['cyear']));
    $table = " tbl_schedule_list";

    $in_fieldlist = "int_member_id,dtt_schedule,tme_schedule,str_schedule_meridian,str_contact,str_reason,dtt_callback,tme_callback,str_callback_meridian,bit_active,customers_id";
    $in_values = "{$_SESSION['ses_member_id']},'$sdate','{$_POST['stime']}','{$_POST['smeridian']}','{$_POST['contact']}','{$_POST['reason']}','$cdate','$ctime','{$_POST['cmeridian']}',1,".$customers_id;

    //debug(true, true, $in_fieldlist, $in_values);

    $result = insert_fields($conn, $table, $in_fieldlist, $in_values);

    ?>
    <script language="JavaScript">
        window.opener.location.reload(true);
        window.close();
    </script>
    <?
}
if(isset($_POST['update'])){
    $cdate=date("Y/m/d",mktime(0,0,0,$_POST['cmonth'],$_POST['cday'],$_POST['cyear']));
    $ctime=$_POST['chour'].':'.$_POST['cmin'];
    $scdate=date("Y/m/d",mktime(0,0,0,$_POST['scmonth'],$_POST['scday'],$_POST['scyear']));
    list($sctime,$scmeridian) = explode(',',$_POST['schtime']);
    $table = "tbl_schedule_list";
    $fieldlist="dtt_schedule='$scdate',tme_schedule='$sctime',str_schedule_meridian='$scmeridian',str_contact='{$_POST['contact']}',str_reason='{$_POST['reason']}',dtt_callback='$cdate',tme_callback='$ctime',str_callback_meridian='{$_POST['cmeridian']}'";
    $condition=" where int_schedule_list_id=".$_POST['scheduleid'];
    $result=update_rows($conn, $table, $fieldlist, $condition);
    ?>
    <script language="JavaScript">
        window.opener.location.reload(true);
        window.close();
    </script>
    <?
}

if(isset($_GET['delete'])){
    $table = "tbl_schedule_list";
    $condition=" where int_schedule_list_id=".$_POST['scheduleid'];
    $result=del_rows($conn, $table, $condition);// function call to delete
    ?>
    <script language="JavaScript">
        window.opener.location.reload(true);
        window.close();
    </script>
    <?
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
            <div class="col-xs-12 centering">
                <section class="panel">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                    </header>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-12 months ">
                                <div class="display_months_js">
                                    <div class='calendar datepicker datepicker-primary col-sm-4'></div>
                                    <div class='calendar datepicker datepicker-primary col-sm-4'></div>
                                    <div class='calendar datepicker datepicker-primary col-sm-4'></div>
                                    <div class="clearfix"></div>
                                </div>
                                <script type="text/javascript">
                                    (function($) {
                                        $(document).ready(function(){
                                            function dateSelectCallback(e){
                                                var currentDate = e.date;
                                                var locationString = '<?php echo "$self_page?".($is_popup? 'popup=1&' : '').(!empty($get_member_id) ? "&memberid=$get_member_id&" : '')."wday="?>'+currentDate.getDate()+'&wmonth='+(currentDate.getMonth() + 1)+'&wyear='+currentDate.getFullYear();

                                                window.location = locationString;

                                                console.log(currentDate, locationString);
                                            }
                                            multipleDatePicker('.calendar', dateSelectCallback, <?php echo $year; ?>, <?php echo $month; ?>, <?php echo $day; ?> );
                                        });
                                    })(jQuery);
                                </script>
                            </div>
                            <div class="p-lg">&nbsp;</div>
                            <div class="col-xs-12 buttons">
                                <div class="text-center">
                                    <a href="./calender.php" class="btn btn-primary">Go to Current Month</a>
                                </div>
                            </div>
                            <div class="p-lg">&nbsp;</div>
                            <div class="col-xs-12 week_wtih_time">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <?php
                                        $start=strtotime('06:30');
                                        $end=strtotime('19:00');

                                        $current_time = mktime(0,0,0,$month, $day, $year);
                                        $current_day_weekday_name = date('l', $current_time);
                                        $position_of_name_key = array_search($current_day_weekday_name, $weekdays);
                                        $left_days = ($position_of_name_key+1) - 1;
                                        $rest_days = 7-( $left_days + 1 );
                                        $rest_days = ( $rest_days < 0 ) ? 0 : $rest_days;

                                        //debug(true, false, $current_time, $current_day_weekday_name, $position_of_name_key, $left_days, $rest_days);

                                        $j= 0;

                                        for ($i=$start; $i<=$end; $i = $i + 30*60) {
                                            $hour_min_sec = date('h:i:s',$i);
                                            $hour_min = date('h:i',$i);
                                            $hour_format = date('A',$i);
                                            $time = "$hour_min $hour_format";

                                            echo '<tr>';
                                            echo ( ( $j == 0 ) ? '<th></th>' : "<td class='time'>$time</td>") ;

                                            for( $k=0; $k < count($weekdays); $k++ ) {

                                                if( $k < $position_of_name_key ) {
                                                    $agoDay = $position_of_name_key - $k;
                                                    $newTime = strtotime( "-$agoDay days", $current_time);
                                                }
                                                elseif( $k > $position_of_name_key ) {
                                                    $nextDay = $k - $position_of_name_key;
                                                    $newTime = strtotime( "+$nextDay days", $current_time);
                                                }
                                                else {
                                                    $newTime = $current_time;
                                                }

                                                //debug( true, false, $newTime, date('l  d/m/Y', $newTime) );

                                                $cell_class = ( ($k == $position_of_name_key) ? 'current_day' : '' );

                                                if( $j == 0 ) {
                                                    echo "<th class='$cell_class'>{$weekdays[$k]}<br/>".date('jS M, Y', $newTime)."</th>";
                                                } else {

                                                    echo "<td class='$cell_class'>";
                                                    echo "<a href='#' onclick=\"window.open('".base_member_url()."/new_scheduled_appointment.php?time=$hour_min,$hour_format&date=".date('m-d-Y', $newTime)."','appointment','width=780,height=680'); return false;\">
                                                                <i class='fa fa-plus fa-1' aria-hidden='true'></i>
                                                            </a>
                                                    ";
                                                    $task_sql = "select int_schedule_list_id from tbl_schedule_list sl ";

                                                    if(!empty($is_popup) && !empty($get_member_id)) {
                                                        $task_sql .= "INNER JOIN tbl_member m ON m.int_customer_id = sl.customers_id";
                                                    }

                                                    $task_sql .= " where sl.dtt_schedule='".date('Y-m-d', $newTime)."' and sl.tme_schedule='$hour_min_sec' and sl.str_schedule_meridian='$hour_format' and sl.int_member_id='$member_id' ";

                                                    if(!empty($is_popup) && !empty($get_member_id)) {
                                                        $task_sql .= " AND m.int_member_id='$get_member_id' ";
                                                    }

                                                    //debug(false, true, $task_sql);

                                                    $rsschedule=mysqli_query($conn,$task_sql);

                                                    while(list($scheduleid)=mysqli_fetch_row($rsschedule)) {
                                                        echo'<a href="javascript:;" onClick="window.open(\''.base_member_url().'/view_scheduled_appointment.php?scheduleid='.$scheduleid.'\',\'viewscheduledappointment\',\'width=410,height=465\')"><img src="../images/icons/ico_zettel.gif" width="15" height="13" border="0"></a>';
                                                    }

                                                    echo "</td>";
                                                }
                                            }

                                            echo '</tr>';

                                            $j++;
                                        }

                                        //die();
                                        ?>


                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>


<?php
require_once("templates/footer.php");
