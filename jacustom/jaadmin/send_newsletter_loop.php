<?php
$page_name = 'Send Newsletters';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$sn = base_url();
 $content = '
<table width="100%" border="0" cellpadding="0" cellspacing="0" bordercolor="5575AC">
  <tr> 
    <td bgcolor="5575AC"><a href="'.$sn.'" target="_blank"><img src="'.$sn.'/images/newsletter_logo.jpg" width="422" height="132" border="0"></a></td>
    <td bgcolor="5575AC" width="10%" valign="top">&nbsp;</td>
    <td bgcolor="5575AC" width="49%" valign="middle"><p><img src="'.$sn.'/images/weekly_tag.jpg"></p>
    </td>
  </tr>
  <tr>
	     <td colspan=3 align=right><b>'.date("m/d/Y").'</b>&nbsp;&nbsp;</td>
  </tr>
</table>  
';

$content .= $_SESSION['sess_email_body'];

 // echo $content;
 
 if(!$l1){
 	$l1 = 0;
 }
 $l2 = 25;
 

 $query_tot = "select c.customers_id,c.customers_email_address
			from tbl_member m 
			inner join customers c on c.customers_id=m.int_customer_id 
			where bit_active=1 and c.customers_email_address like '%@%'
			group by c.customers_email_address 
			order by c.customers_email_address 
			";
$nr_tot = mysqli_num_rows(mysqli_query($conn,$query_tot)) ;
$query = $query_tot . " 
limit $l1, $l2 ";

//echo "nr_tot = $nr_tot<br>".nl2br($query);

$q = mysqli_query($conn,$query);

echo "<font face=arial size=2><br><font color=red><b>Sending the email to these ".mysqli_num_rows($q). " emails [from ".($l1+1)." to ".($l1+mysqli_num_rows($q))." out of $nr_tot]:</b></font>";

$headers  = "MIME-Version: 1.0\n";
$headers .= "Content-type: text/html; charset=iso-8859-1\n";
$headers .= "From: {$_SESSION['sess_sender_name']} <{$_SESSION['sess_sender_email']}>\n";

$subject = $_SESSION['sess_sender_email'];

while ($a = mysqli_fetch_array($q)) {
	echo "<br><b>".$a['customers_email_address']."</b>";
	// Mail it
	$email = $a['customers_email_address'];
	//$content=stripslashes(str_replace("/UserFiles/Image/", "http://www.johnamico.com/UserFiles/Image/", $content));
    $content=str_replace('src="/UserFiles/', 'src="http://www.johnamico.com/UserFiles/', $content);
    $content=str_replace('src="/editor/images/smiley/', 'src="http://www.johnamico.com/editor/images/smiley/', $content);
    //mail($email, $subject, $content, $headers);

}


if($l1 + $l2 < $nr_tot){
	$l1 = $l1 + $l2;
	echo "<script>document.location='send_newsletter_loop.php?newsletter_id=$newsletter_id&l1=$l1'</script>";
}else{
	mysqli_query($conn,"update newsletters_mails set archived='1' where id='$newsletter_id'");
	echo "<br><br><input type=button value=DONE! onclick=\"document.location='newsletter_new.php'\">".mysql_error();
	echo "<script>alert('All the emails have been sent!')</script>";
}