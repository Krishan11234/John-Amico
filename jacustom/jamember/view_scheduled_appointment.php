<?php
$page_name = 'View Scheduled Appointment';
$page_title = $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$is_popup = true;

$display_header = false;
require_once("templates/header.php");


$self_page = "view_scheduled_appointment.php";
$self_page_url = base_member_url() . "/$self_page";


$schedule_id = !empty($_GET['scheduleid']) ? filter_var($_GET['scheduleid'], FILTER_SANITIZE_NUMBER_INT) : 0;
$reschedule = !empty($_GET['reshedule']) ? 1 : 0;


if(!empty($schedule_id)) {
    $q = "SELECT sl.dtt_schedule,sl.tme_schedule,sl.str_schedule_meridian,sl.dtt_callback,sl.tme_callback,sl.str_callback_meridian,sl.str_contact,sl.str_reason,
          c.customers_telephone, c.customers_telephone1, c.customers_telephone2, c.mobile_phone
            FROM tbl_schedule_list sl
            LEFT JOIN customers c ON c.customers_id = sl.customers_id
            WHERE int_schedule_list_id='$schedule_id' ";

    $rsschedule = mysqli_query($conn, $q);

    list($schdate, $schtime, $schmeridian, $scdate, $sctime, $scmeridian, $contact, $reason, $customers_telephone, $customers_telephone1, $customers_telephone2, $mobile_phone) = mysqli_fetch_row($rsschedule);

    list($syear, $smonth, $sday) = explode('-', $scdate);
    $scdate = date("m|d|Y", mktime(0, 0, 0, $smonth, $sday, $syear));

    list($shour, $smin, $ssec) = explode(':', $sctime);
    $sctime = $shour . ':' . $smin;

    list($scyear, $scmonth, $scday) = explode('-', $schdate);
    list($schour, $scmin, $scsec) = explode(':', $schtime);
    $schtime = $schour . ':' . $scmin . ',' . $schmeridian;
}
	
?>

<script language="JavaScript">
    <!--
    function validate(theform){
        if(isEmpty(theform.contact.value)){
            alert("Please enter the Contact Name");
            theform.contact.focus();
            return false;
        }
        if(isEmpty(theform.reason.value)){
            alert("Please enter the Subject");
            theform.reason.focus();
            return false;
        }
        if((isEmpty(theform.cmonth.value)) || (theform.cmonth.value>12)){
            alert("Please enter a Month");
            theform.cmonth.focus();
            return false;
        }
        if((isEmpty(theform.cday.value)) || (theform.cday.value>31)){
            alert("Please enter a Day");
            theform.cday.focus();
            return false;
        }
        if(isEmpty(theform.cyear.value)){
            alert("Please enter a Year");
            theform.cyear.focus();
            return false;
        }
        if((isEmpty(theform.chour.value)) || (theform.chour.value>12)){
            alert("Please enter a Hour");
            theform.chour.focus();
            return false;
        }
        if((isEmpty(theform.cmin.value)) || (theform.cmin.value>60)){
            alert("Please enter Minutes");
            theform.cmin.focus();
            return false;
        }
        if((isEmpty(theform.scmonth.value)) || (theform.cmonth.value>12)){
            alert("Please enter a Month");
            theform.scmonth.focus();
            return false;
        }
        if((isEmpty(theform.scday.value)) || (theform.cday.value>31)){
            alert("Please enter a Day");
            theform.scday.focus();
            return false;
        }
        if(isEmpty(theform.scyear.value)){
            alert("Please enter a Year");
            theform.scyear.focus();
            return false;
        }
        //window.close();

        theform.submit();

        return false;
    }
    -->
</script>

<div role="main" class="content-body extra_information <?php echo ( $is_popup ? 'no-margin-left' : '' ); ?> ">
    <div class="row ">
        <div class="col-xs-12 centering">
        <?php if(!$reschedule) : ?>
            <section class="panel">
                <header class="panel-heading">
                    <h2 class="panel-title text-center">Scheduled Appointment</h2>
                </header>
                <div class="panel-body">
                    <?php
                    $fields_text = array(
                        'contact' => array('name'=>'Contact'),
                        'reason' => array('name'=>'Regarding'),
                        'scdate' => array('name'=>'Date'),
                        'sctime' => array('name'=>'Time', ),
                        'customers_telephone' => array('name'=>'Phone Number', ),
                        'customers_telephone1' => array('name'=>'Phone Number 1', ),
                        'customers_telephone2' => array('name'=>'Phone Number 2', ),
                        'mobile_phone' => array('name'=>'Mobile Phone', ),
                    );

                    foreach( $fields_text as $fk => $fv ) :
                        $value = !empty($fv['value']) ? $fv['value'] : $fk;
                        $value_ = $$value;

                        if( $fk == 'sctime' ) {
                            $value_ = "$value_ $schmeridian";
                        }
                    ?>
                    <div class="row">
                        <div class="col-xs-12 ">
                            <div class="row">
                                <div class="col-xs-5"><strong><?php echo $fv['name'] ?></strong></div>
                                <div class="col-xs-1">:</div>
                                <div class="col-xs-6"><?php echo $value_ ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach;?>
                </div>
                <footer class="panel-footer">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <div class="col-sm-4">
                                <a href="<?php echo $self_page_url;?>?scheduleid=<?php echo $schedule_id?>&reshedule=1" class="btn btn-default btn-success">Reschedule</a>
                            </div>
                            <div class="pb-lg visible-xs"></div>
                            <div class="col-sm-4">
                                <form  name="theform" action="./calender.php?delete=1" method="post" target="<?=$_SESSION['member']['ses_frame']?>">
                                    <input type="Hidden" name="scheduleid" value="<?php echo $schedule_id?>">
                                    <button type="button" class="btn btn-default btn-danger " onclick="if(confirm('Do you really want to delete this schedule?')){ theform.submit(); return false;}" >Delete Schedule</button>
                                </form>
                            </div>
                            <div class="pb-lg visible-xs"></div>
                            <div class="col-sm-4">
                                <a href="#" class="btn btn-default btn-warning " onclick="window.close()">Cancel</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </section>
        <?php else: ?>
            <section class="panel">
                <form  name="theform" action="./calender.php" method="post">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center">Change Scheduled Appointment</h2>
                    </header>
                    <div class="panel-body">
                        <div class="form-group">
                            <label class="col-sm-4 form-control-label" for="contact">Contact</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="contact" name="contact" value="<?=$contact?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 form-control-label" for="reason">Regarding</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="reason" name="reason" value="<?=$reason?>">
                            </div>
                        </div>
                        <div class="form-group"></div>
                        <div class="form-group">
                            <h4 class="col-xs-12 form-control-static">Call Back Time:</h4>
                        </div>
                        <div class="form-group ">
                            <label class="col-sm-4 form-control-label" for="cmonth">Date</label>
                            <div class="col-sm-8 ">
                                <div class="row">
                                    <div class="col-xs-4"><input type="text" class="form-control" id="cmonth" name="cmonth" size="2" value="<?=$smonth?>" placeholder="MM"></div>
                                    <div class="col-xs-4"><input type="text" class="form-control" id="cday" name="cday" size="2" value="<?=$sday?>" placeholder="DD"></div>
                                    <div class="col-xs-4"><input type="text" class="form-control" id="cyear" name="cyear" size="4" value="<?=$syear?>" placeholder="YYYY"></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 form-control-label" for="chour">Time</label>
                            <div class="col-sm-8 ">
                                <div class="row">
                                    <div class="col-xs-7">
                                        <div class="row">
                                            <div class="col-xs-5"><input type="text" class="form-control" id="chour" name="chour" value="<?=$schour?>" placeholder="HH"></div>
                                            <div class="pull-left">:</div>
                                            <div class="col-xs-5"><input type="text" class="form-control" id="cmin" name="cmin" value="<?=$scmin?>" placeholder="MM"></div>
                                        </div>
                                    </div>
                                    <div class="col-xs-5">
                                        <?php $checked_am = ( trim($schmeridian)=='AM' ? 'checked' : '' ); ?>
                                        <?php $checked_pm = ( trim($schmeridian)=='PM' ? 'checked' : '' ); ?>
                                        <div class="row">
                                            <div class="col-xs-4">
                                                <div class="row">
                                                    <label class="col-xs-4 form-control-label" for="cmeridian[AM]">AM</label>
                                                    <div class="col-xs-8"><input type="radio" id="cmeridian[AM]" name="cmeridian" value="AM" <?php echo $checked_am?>></div>
                                                </div>
                                            </div>
                                            <div class="col-xs-4">
                                                <div class="row">
                                                    <label class="col-xs-4 form-control-label" for="cmeridian[PM]">PM</label>
                                                    <div class="col-xs-8"><input type="radio" id="cmeridian[PM]" name="cmeridian" value="PM" <?php echo $checked_pm?>></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group"></div>
                        <div class="form-group">
                            <h4 class="col-xs-12 form-control-static">Reschedule Time:</h4>
                        </div>
                        <div class="form-group ">
                            <label class="col-sm-4 form-control-label" for="cmonth">Date</label>
                            <div class="col-sm-8 ">
                                <div class="row">
                                    <div class="col-xs-4"><input type="text" class="form-control" id="cmonth" name="cmonth" size="2" value="<?=$smonth?>" placeholder="MM"></div>
                                    <div class="col-xs-4"><input type="text" class="form-control" id="cday" name="cday" size="2" value="<?=$sday?>" placeholder="DD"></div>
                                    <div class="col-xs-4"><input type="text" class="form-control" id="cyear" name="cyear" size="4" value="<?=$syear?>" placeholder="YYYY"></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 form-control-label" for="schtime">Time</label>
                            <div class="col-sm-8 ">
                                <div class="row">
                                    <select name="schtime" id="schtime" class="form-control">
                                        <?php
                                        $start=strtotime('07:00');
                                        $end=strtotime('19:00');

                                        for ($i=$start; $i<=$end; $i = $i + 30*60) {
                                            $hour_min_sec = date('h:i:s',$i);
                                            $hour_min = date('h:i',$i);
                                            $hour_format = date('A',$i);
                                            $time = "$hour_min $hour_format";

                                            $selected = ( "$hour_min,$hour_format" == "$schour:$scmin,$schmeridian" ) ? 'selected' : '';

                                            echo "<option value='$hour_min,$hour_format' $selected>$time</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <footer class="panel-footer">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <div class="col-xs-6">
                                    <input type="hidden" name="scheduleid" value="<?php echo $schedule_id?>">
                                    <button class="btn btn-primary btn-success" type="submit" name="update">Submit</button>
                                </div>
                                <div class="col-xs-6">
                                    <button class="btn btn-primary btn-warning" type="reset">Reset</button>
                                </div>
                            </div>
                        </div>
                    </footer>
                </form>
            </section>
        <?php endif;?>
        </div>
    </div>

<?php
require_once("templates/footer.php");

