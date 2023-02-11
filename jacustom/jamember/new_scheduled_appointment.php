<?php
$page_name = 'New Scheduled Appointment';
$page_title = $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$is_popup = true;

$display_header = false;
require_once("templates/header.php");

list($hour,$min) = explode(':',$_GET['time']);
list($min,$meridian) = explode(',',$min);
list($month,$day,$year) = explode('-',$_GET['date']);

$stime=$hour.':'.$min;
$sdate=date("Y/m/d",mktime(0,0,0,$month,$day,$year));

$contact_name = '';
$customers_telephone = '';
$customers_telephone1 = '';
$customers_telephone2 = '';
$mobile_phone = '';
$customers_id = '';

$member_id = (isset($_GET['memberid'])) ? (int)$_GET['memberid'] : FALSE;

$member_id_uri = ($member_id) ? "&memberid=".$member_id : '';

if($member_id) {
    $q = "SELECT c.* FROM customers c inner join tbl_member m ON c.customers_id=m.int_customer_id WHERE m.int_member_id = ".$member_id;

    $res = mysqli_query($conn,$q);
    if(mysqli_num_rows($res)>0) {
        $member = mysqli_fetch_array($res);

        $contact_name = $member['customers_firstname']." ".$member['customers_lastname'];
        $customers_telephone = $member['customers_telephone'];
        $customers_telephone1 = $member['customers_telephone1'];
        $customers_telephone2 = $member['customers_telephone2'];
        $mobile_phone = $member['mobile_phone'];
        $customers_id = $member['customers_id'];
    }
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
        theform.submit();
        //window.close();
	}
-->
</script>


<div role="main" class="content-body extra_information <?php echo ( $is_popup ? 'no-margin-left' : '' ); ?> ">
    <div class="row ">
        <div class="col-xs-12 centering">
            <form class="form form-validate form-bordered" name="theform"
                  action="./calender.php?wday=<?php echo $day ?>&wmonth=<?php echo $month ?>&wyear=<?php echo $year ?><?php echo $member_id_uri ?>&samepage=1" method="post" onsubmit="return validate(this)" target="<?php echo $_SESSION['ses_frame'] ?>">
                <section class="panel">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                    </header>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="row">
                                    <input type="hidden" name="memberid" value="<?=$member_id?>" >
                                    <input type="hidden" name="goto" value="save">

                                    <div class="form-group">
                                        <label class="col-sm-4 form-control-label" for="contact">Contact</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="contact" name="contact" value="<?=$contact_name?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 form-control-label" for="reason">Regarding</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="reason" name="reason" value="<?=$reason?>">
                                        </div>
                                    </div>
                                    <div class="form-group ">
                                        <label class="col-sm-4 form-control-label" for="cmonth">Date</label>
                                        <div class="col-sm-8 ">
                                            <div class="row">
                                                <div class="col-xs-4"><input type="text" class="form-control" id="cmonth" size="2" name="cmonth" value="<?=$month?>" placeholder="MM"></div>
                                                <div class="col-xs-4"><input type="text" class="form-control" id="cday" size="2" name="cday" value="<?=$day?>" placeholder="DD"></div>
                                                <div class="col-xs-4"><input type="text" class="form-control" id="cyear" size="4" name="cyear" value="<?=$year?>" placeholder="YYYY"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 form-control-label" for="chour">Time</label>
                                        <div class="col-sm-8 ">
                                            <div class="row">
                                                <div class="col-xs-6">
                                                    <div class="row">
                                                        <div class="col-xs-5"><input type="text" class="form-control" id="chour" name="chour" value="<?=$hour?>" placeholder="Hour"></div>
                                                        <div class="pull-left">:</div>
                                                        <div class="col-xs-5"><input type="text" class="form-control" id="cmin" name="cmin" value="<?=$min?>" placeholder="Minute"></div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-6">
                                                    <?php $checked_am = ( trim($meridian)=='AM' ? 'checked' : '' ); ?>
                                                    <?php $checked_pm = ( trim($meridian)=='PM' ? 'checked' : '' ); ?>
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
                                    <div class="form-group">
                                        <label class="col-sm-4 form-control-label" for="customers_telephone">Phone Number</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="customers_telephone" name="customers_telephone" value="<?=$customers_telephone?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 form-control-label" for="customers_telephone1">Phone Number 1</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="customers_telephone1" name="customers_telephone1" value="<?=$customers_telephone1?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 form-control-label" for="customers_telephone2">Phone Number 2</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="customers_telephone2" name="customers_telephone2" value="<?=$customers_telephone2?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 form-control-label" for="mobile_phone">Mobile Number</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="mobile_phone" name="mobile_phone" value="<?=$mobile_phone?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <footer class="panel-footer">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <input type="hidden"  name="stime" value=<?=$stime?>>
                                <input type="hidden"  name="customers_id" value="<?=$customers_id; ?>">
                                <input type="hidden" name="sdate" value=<?=$_GET['date']?>>
                                <input type="hidden" name="smeridian" value=<?=$meridian?>>

                                <button type="submit" name="submit" value="send_emails" class="command  btn btn-default btn-success mr-lg">Submit</button>
                                <button type="button" class="btn btn-default btn-warning ml-lg" onclick="window.close()">Close</button>
                            </div>
                        </div>
                    </footer>
                </section>
            </form>
        </div>
    </div>


<?php
require_once("templates/footer.php");
