<?
require("../../include/session_check.inc");
require("../../include/global.inc");

$rsemail=mysqli_query($conn,"select str_admin_email from tbl_admin_email where int_admin_email_id=1");
list($adminemail)=mysqli_fetch_row($rsemail);
$rsnewsletter=mysqli_query($conn,"select TargetAudience, str_subject,str_newsletter from tbl_newsletter where int_newsletter_id='".$_POST[newsletter_id]."'");
list($audience, $subject,$newsletter)=mysqli_fetch_row($rsnewsletter);

//echo "adminemail=$adminemail <br><br> subject = $subject <br><br> newsletter = $newsletter <br><br> ";

require_once 'HTML/Progress2.php';
 
$pb = new HTML_Progress2();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title></title>
<style type="text/css">
<!--
<?php echo $pb->getStyle(); ?>
 
body {
    background-color: #E0E0E0;
    color: #000000;
    font-family: Verdana, Arial;
}
// -->
</style>
<script type="text/javascript">
<!--
<?php 
//echo $pb->getScript(); 
if ($total_subscribers <= $start_with){
	mysqli_query($conn,"update customers_emails set sent='1' where id2='$id2' and admin_user='".$_POST['admin_user']."' and newsletter_id='".$_POST[newsletter_id]."' ");
	echo "alert('All messages have been successfully sent!');document.location='../newsletter.php';";
}
?>
 
// wait one second = 1000
//var wait = 0;  
var wait = 1000;  
 
// Pause for N milliseconds to display the progress meter
function pause()
{
    setTimeout("submitForm();", wait);
}
 
// Submit the form with the new value range
function submitForm()
{
    var complete = parseInt(document.forms[0].complete.value);
    if (complete < 100) {   // re-submit the form if the job is not done
        document.forms[0].submit();
    }
}
//-->
</script>
</head>
<body onLoad="pause();">
<?php
/*
$pb->setProgressAttributes(array(
    'position' => 'absolute',
    'left' => 200,
    'top' => 100
));
$pb->setCellAttributes(array(
    'active-color' => '#000084',
    'inactive-color' => '#3A6EA5',
    'width' => 32,
    'height' => 32
));
$pb->setLabelAttributes('pct1', array(
    'width' => 0,
    'left' => 350,
    'top' => 10,
    'font-size' => 16,
    'font-weight' => 'bold'
));
 
// Adds additional text label for process legend
$labelTxtID = 'legend';
$pb->addLabel(HTML_PROGRESS2_LABEL_TEXT, $labelTxtID);
$pb->setLabelAttributes($labelTxtID, array(
    'left' => 0,
    'top' => -16,
    'color' => 'red'
));
*/
$maximum_send = 25;           // max number of emails to send each page load
//$total_subscribers = 150;    // total of subscribers of your newsletter

$total_subscribers = $_POST["total_subscribers"];
//24
//echo "<b>total_subscribers=".$_GET["total_subscribers"]."</b>";exit;
 
// step to advance on each page load
$inc = intval($total_subscribers / $maximum_send * 0.01);
$pb->setIncrement($inc);
 
$post = ($_SERVER['REQUEST_METHOD'] == 'POST');
if ($post) {
    $start_with  = (int)$_POST["start_with"];
    $error_count = (int)$_POST["error_count"];
} else {
    $start_with  = 0;
    $error_count = 0;
}
 
$sent = 0;
if ($total_subscribers >= $start_with)
{
    // retrieve all necessary data in the database
    unset($query);
    unset($a);
	$query="select * from customers_emails where sent='0' and id2='$id2' and admin_user='".$_POST['admin_user']."' and newsletter_id='".$_POST[newsletter_id]."' order by customers_email limit $start_with , ".$maximum_send; 
//echo "query=$query<br>";
	$q = mysqli_query($conn,$query);
	echo "<font face=arial size=2>";
echo "<center>
<font color=red>[<b>".floor(($start_with+$maximum_send)*100/$total_subscribers)."% Sent</b>] </font><br> 
<b>Currently sending to these 25 addresses:</b><br>";
	while($a = mysqli_fetch_array($q)){

//24
///!!!!!!!!!!
//$a["customers_email"] = "sorin@mvisolutions.com";
//////////$a["customers_email"] = "sbogde@yahoo.com";
		echo $a["customers_email"]."<br>";
		
		$mail_to = $a["customers_email"];
		$mail_from = $adminemail;	
		$subject = $subject;	

		$headers = "From: ".$mail_from."\n";
		$headers .= "X-Mailer: PHP/" . phpversion()."\n"; // mailer
		$headers .= "Reply-To: ". $mail_from."\n";  // Return path for errors
		$headers .= "Content-Type: text/html; charset=iso-8859-1\n"; // Mime type

//24
$mail_stat = mail($mail_to, $subject, $newsletter, $headers);        
			
			$status='0';
			if($mail_stat==1) {
				$mail=$mail.','.$mail_stat;
				$status='1';
			}
		mysqli_query($conn,"update customers_emails set status='$status', dt=now() where id2='$id2' and admin_user='".$_POST['admin_user']."' and newsletter_id='".$_POST[newsletter_id]."' and customers_email='$mail_to'");
		echo mysql_error();
	}

//	echo "$total_subscribers >= $start_with</font></center>";
	
	$pb->sleep();          // process simulation
 
    // if new data are available, then ...
    $sent = $maximum_send;
    // else, $error_count++;
}
$start_with += $sent;
 
// set the new progress value
$complete = round($start_with / $total_subscribers * 100);
 /*
$pb->setValue(intval($complete));

$pb->setLabelAttributes($labelTxtID, array(
    'value' => sprintf('Mails sent: %s/%s', $start_with, $total_subscribers))
    );
*/
//echo $pb->getScript(); 
if ($total_subscribers <= $start_with){
	mysqli_query($conn,"update customers_emails set sent='1' where id2='$id2' and admin_user='".$_POST['admin_user']."' and newsletter_id='".$_POST[newsletter_id]."' ");
	echo "<script>alert('All messages have been successfully sent!');document.location='../newsletter.php';</script>";
}

?>
<form name="form" method="post" action="<?php echo basename($_SERVER['PHP_SELF']) ?>">
<input type="hidden" name="start_with" value="<?php echo $start_with; ?>"/>
<input type="hidden" name="error_count" value="<?php echo $error_count; ?>"/>
<input type="hidden" name="complete" value="<?php echo $complete; ?>"/>

<input type="hidden" name="total_subscribers" value="<?php echo $_POST["total_subscribers"]; ?>"/>

<input type=hidden name="id2" value="<?=$_POST["id2"]?>">
<input type=hidden name="admin_user" value="<?=$_POST["admin_user"]?>">
<input type=hidden name="newsletter_id" value="<?=$_POST["newsletter_id"]?>">
</form>
<?php
/*
if ($complete < 100) {
    $pb->hide();
//    $pb->display();
    $pb->moveNext();
} else {
    $pb->hide();
    printf('<p>Mailing Process Ended with %d error(s)</p>', $error_count);
}
*/
?>
</body>
</html> 
