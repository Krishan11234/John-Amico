<?php
require_once("../common_files/include/global.inc");
require_once("session_check.inc");

//FOR PRODUCTION SERVER
//$_POST[newsletter] = str_replace('src=\"/UserFiles/', 'src=\"http://www.johnamico.com/UserFiles/', $_POST[newsletter]); /*added by ksa 2/6/2008 */

//For DEV SERVER
//$_POST[newsletter] = str_replace('src=\"/UserFiles/', 'src=\"http://dev.johnamico.com/UserFiles/', $_POST[newsletter]); /*added by ksa 2/6/2008 */


//$_POST[newsletter] = str_replace('src=\"/editor/images/smiley/', 'src=\"http://www.johnamico.com/editor/images/smiley/', $_POST[newsletter]); /*added by ksa 2/9/2008 */


if(isset($_POST['add'])){

		$table = "tbl_newsletter";				// inserting values to setting table
		$in_fieldlist="str_subject,str_newsletter,bit_active, TargetAudience, int_days";
		$in_values="'{$_POST['subject']}','{$_POST['newsletter']}',1, '{$_POST['Audience']}', '{$_POST['in_days']}'";
		$result=insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values
}
elseif(isset($_POST['update'])) {
          if(isset($_POST['sms'])){ $_POST['sms']=1;  }
            else { $_POST['sms']=0;}	 

	 $table = "tbl_newsletter";
	 $fieldlist="str_subject='{$_POST['subject']}', str_newsletter='{$_POST['newsletter']}', TargetAudience='{$_POST['Audience']}', int_days='{$_POST['in_days']}', sms='{$_POST['sms']}', sms_msg='{$_POST['sms_msg']}'";
	 $condition=" where int_newsletter_id = {$_POST['newsletterid']}";
	 $result=update_rows($conn, $table, $fieldlist, $condition);
       
}
elseif(isset($_POST['activate'])){
	 if($_POST['active']==0){$active=1;}else{$active=0;}
	 $table = "tbl_newsletter";
	 $fieldlist="bit_active=$active";
	 $condition=" where int_newsletter_id = {$_POST['newsletterid']}";
	 $result=update_rows($conn, $table, $fieldlist, $condition);
}
elseif(isset($_GET['delete'])){
	$table = "tbl_newsletter";
	echo $note_status_id;
	 $condition=" where int_newsletter_id = {$_GET['newsletterid']}";
	$result=del_rows($conn, $table, $condition);// function call to delete
}
elseif(isset($_GET['send'])==1){
	$rsemail=mysqli_query($conn,"select str_admin_email from tbl_admin_email where int_admin_email_id=1");
	list($adminemail)=mysqli_fetch_row($rsemail);

	$rsnewsletter=mysqli_query($conn,"select TargetAudience, str_subject,str_newsletter,sms,sms_msg from tbl_newsletter where int_newsletter_id=".$_GET['newsletterid']);
	list($audience, $subject,$newsletter,$sms,$sms_msg)=mysqli_fetch_row($rsnewsletter);
        if($sms==1){
         $subject = '';
}
	if ($audience == "Customers" || $audience == "All") {
		$rscustomer = mysqli_query($conn,"SELECT email FROM member_customers");
	}

	if ($audience == "STW" || $audience == "All") {
		$query_rsinquiry = "SELECT email FROM stw_inquire GROUP BY email ";
		$rsinquiry = mysqli_query($conn,$query_rsinquiry);
	}

	if ($audience == "Cruise" || $audience == "All") {
		$query_rsinquiry2 = "SELECT User_email FROM salon_inquire GROUP BY User_email ";
		$rsinquiry2 = mysqli_query($conn,$query_rsinquiry2);
	}

//24 ---> limit !
$tbl_member    = "tbl_member";
$tbl_customers = "customers"; 
$tbl_customers_emails = "customers_emails";
if($test_mode == 1){
	$tbl_member    = "tbl_member_test";
	$tbl_customers = "customers_test"; 
	$tbl_customers_emails = "customers_emails_test";
}
	if($_GET['status']==0) {
		if ($audience == "Members" || $audience == "All") {
			if($sms==0){
				$query = "select c.customers_id,c.customers_email_address,c.mobile_phone,c.operator_id 
				from $tbl_member m 
				inner join $tbl_customers c on c.customers_id=m.int_customer_id 
				group by c.customers_email_address 
				order by m.int_customer_id";
				$rsmember=mysqli_query($conn,$query);
			}
			if($sms==1){
				$query = "select c.customers_id,c.customers_email_address,c.mobile_phone,c.operator_id 
				from $tbl_member m 
				inner join $tbl_customers c on c.customers_id=m.int_customer_id 
				where c.mobile_phone!='' and c.operator_id!=0";			
				$rsmember=mysqli_query($conn,$query);
			}
		}
		
	}elseif($_GET['status']==1)  {
		if ($audience == "Members" || $audience == "All") {
			if($sms==0) {
				$query = "select c.customers_id,c.customers_email_address, c.mobile_phone,c.operator_id 
				from $tbl_member m 
				inner join $tbl_customers c on c.customers_id=m.int_customer_id 
				where bit_active=1 
				group by c.customers_email_address 
				order by m.int_customer_id ";
				$rsmember=mysqli_query($conn,$query);
			}
			if($sms==1){
				$query = "select c.customers_id,c.customers_email_address, c.mobile_phone,c.operator_id 
				from $tbl_member m 
				inner join $tbl_customers c on c.customers_id=m.int_customer_id 
				where bit_active=1 and c.mobile_phone!='' and c.operator_id!=0 ";
				$rsmember=mysqli_query($conn,$query);
				}
		}
	}elseif($_GET['status']==2) {
		if ($audience == "Members" || $audience == "All") {
			if($sms==0) {
				$query = "select c.customers_id,c.customers_email_address, c.mobile_phone,c.operator_id 
				from $tbl_member m 
				inner join $tbl_customers c on c.customers_id=m.int_customer_id 
				where bit_active=0 
				group by c.customers_email_address 
				order by m.int_customer_id  ";
				$rsmember=mysqli_query($conn,$query);
				}
			if($sms==1) {
				$query = "select c.customers_id,c.customers_email_address, c.mobile_phone,c.operator_id 
				from $tbl_member m 
				inner join $tbl_customers c on c.customers_id=m.int_customer_id 
				where bit_active=0 and c.mobile_phone!='' and c.operator_id!=0";
				$rsmember=mysqli_query($conn,$query);
				}
		}
	}
	
$ID2 = mktime();


	if ($audience == "Members" || $audience == "All") {
		if($sms==0){
		echo "<br><br><br><center><b>Please wait ...</b></center>";
	
	
		while(list($customerid,$email)=mysqli_fetch_row($rsmember)){
			$status='0';
			$subject = ereg_replace("'","",$subject);
			$query_history="insert into $tbl_customers_emails set sent='0', id2='$ID2', admin_user='".$_SESSION['session_user']."', `status`='$status', newsletter_id='".$_GET['newsletterid']."', customers_id='$customerid', customers_email='$email', subject='$subject', body='', `query`='$query' ";
//24 
			mysqli_query($conn,$query_history);
			echo mysql_error()."<br>";

		}
//EXIT;
		
	$query_h = "select * from $tbl_customers_emails where sent='0' and id2='$ID2' and admin_user='".$_SESSION['session_user']."' and newsletter_id='".$_GET['newsletterid']."'  ";
	$q_h 	 = mysqli_query($conn,$query_h);
	$n_h 	 = mysqli_num_rows($q_h);
	?>
		<form method="post" name="mm" action="mailer/index.php">
			<input type=hidden name="total_subscribers" value="<?=$n_h?>">
			<input type=hidden name="test_mode" value="<?=$test_mode?>">
			<input type=hidden name="id2" value="<?=$ID2?>">
			<input type=hidden name="admin_user" value="<?=$_SESSION['session_user']?>">
			<input type=hidden name="newsletter_id" value="<?=$_GET['newsletterid']?>">
		</form>
	<script>
		document.mm.submit();
	</script>
	<?
	exit;
	}
		if($sms==1){
			while(list($customerid,$email,$mobile_phone,$operator_id)=mysqli_fetch_row($rsmember)){
			 
			$mob_op="select operator_address from mobile_operators where id=$operator_id";
			$resmob = mysqli_query($conn,$mob_op);
			list($operator_address) = mysqli_fetch_row($resmob);
			$mobile_phone = preg_replace('/[^\d]*/','',$mobile_phone);
			$mail_to = $mobile_phone.$operator_address;
			$letter = $sms_msg;
			$mail_from = $adminemail;	
			$subject = $subject;
			$headers = "From: ".$mail_from."\n";
			$headers .= "X-Mailer: PHP/" . phpversion()."\n"; // mailer
			$headers .= "Reply-To: ". $mail_from."\n";  // Return path for errors
			$headers .= "Content-Type: text/html; charset=iso-8859-1\n"; // Mime type
	
			$mail_stat = mail($mail_to, $subject, $letter, $headers);

/*var_dump($mail_stat);
var_dump($email);
var_dump($letter);
var_dump($mail_to); */

			if($mail_stat==1) {
				$mail=$mail.','.$mail_stat;
			}
		
        }	
  }
}
$query_rsinquiry = "SELECT email FROM stw_inquire GROUP BY email";
$query_rsinquiry2 = "SELECT User_email FROM salon_inquire GROUP BY User_email";



	if ($audience == "Customers" || $audience == "All") {
		if($sms==0){
		while(list($email)=mysqli_fetch_row($rscustomer)){
			$mail_to = $email;
			$mail_from = $adminemail;	
			$subject = $subject;	
		
			$headers = "From: ".$mail_from."\n";
			$headers .= "X-Mailer: PHP/" . phpversion()."\n"; // mailer
			$headers .= "Reply-To: ". $mail_from."\n";  // Return path for errors
			$headers .= "Content-Type: text/html; charset=iso-8859-1\n"; // Mime type
	
			$mail_stat = mail($mail_to, $subject, $newsletter, $headers);
			
			if($mail_stat==1) {
				$mail=$mail.','.$mail_stat;
			}
		}
	}
}	
	
	if ($audience == "STW" || $audience == "All") {
		if($sms==0){
		while(list($email)=mysqli_fetch_row($rsinquiry)){
			$mail_to = $email;
			$mail_from = $adminemail;	
			$subject = $subject;
			$headers = "From: ".$mail_from."\n";
			$headers .= "X-Mailer: PHP/" . phpversion()."\n"; // mailer
			$headers .= "Reply-To: ". $mail_from."\n";  // Return path for errors
			$headers .= "Content-Type: text/html; charset=iso-8859-1\n"; // Mime type
	
			$mail_stat = mail($mail_to, $subject, $newsletter, $headers);
			
			if($mail_stat==1) {
				$mail=$mail.','.$mail_stat;
			}
		}
	}	
}


//chapter newsletter
	if ($audience == "Chapters" || $audience == "All") {
		if($sms==0){
		$rsquery3 = "select c.customers_id,c.customers_email_address, c.mobile_phone,c.operator_id 
		from tbl_member m 
		inner join customers c on c.customers_id=m.int_customer_id 
		where bit_active=1 and m.mtype='c' and (m.amico_id like 'C%' or m.amico_id like 'c%') 
		group by c.customers_email_address 
		order by m.int_customer_id  ";
		$rsinquiry3=mysqli_query($conn,$rsquery3) or die (mysql_error());}
		if($sms==1){
		$rsquery3 = "select c.customers_id,c.customers_email_address, c.mobile_phone,c.operator_id 
		from tbl_member m 
		inner join customers c on c.customers_id=m.int_customer_id 
		where bit_active=1 and m.mtype='c' and (m.amico_id like 'C%' or m.amico_id like 'c%') and c.mobile_phone!='' and c.operator_id!=0 
		group by c.customers_email_address 
		order by m.int_customer_id  ";
		$rsinquiry3=mysqli_query($conn,$rsquery3) or die (mysql_error());}
		
		while(list($customerid,$email,$mobile_phone,$operator_id)=mysqli_fetch_row($rsinquiry3)){
			  if($sms==1){
			$mob_op="select operator_address from mobile_operators where id=$operator_id";
			$resmob = mysqli_query($conn,$mob_op);
			list($operator_address) = mysqli_fetch_row($resmob);
			$mobile_phone = preg_replace('/[^\d]*/','',$mobile_phone);
			$mail_to = $mobile_phone.$operator_address;
			$letter = $sms_msg;
		}
		   if($sms==0) { $mail_to = $email;	
			  $letter = $newsletter; 	
			}
			$mail_from = $adminemail;	
			$subject = $subject;
			$headers = "From: ".$mail_from."\n";
			$headers .= "X-Mailer: PHP/" . phpversion()."\n"; // mailer
			$headers .= "Reply-To: ". $mail_from."\n";  // Return path for errors
			$headers .= "Content-Type: text/html; charset=iso-8859-1\n"; // Mime type
	
			$mail_stat = mail($mail_to, $subject, $letter, $headers);

/*var_dump($mail_stat);
var_dump($email);
var_dump($letter);
var_dump($mail_to);*/

			
			if($mail_stat==1) {
				$mail=$mail.','.$mail_stat;
			}
		}
		
}
//chapter newsletter



//ECs newsletter
	if ($audience == "ECs" || $audience == "All") {
		if($sms==0){
		$rsquery3 = "select c.customers_id,c.customers_email_address, c.mobile_phone,c.operator_id  
		from tbl_member m 
		inner join customers c on c.customers_id=m.int_customer_id 
		where bit_active=1 and m.mtype='e' 
		group by c.customers_email_address 
		order by m.int_customer_id  ";
		$rsinquiry3=mysqli_query($conn,$rsquery3) or die (mysql_error());}
		if($sms==1){
		$rsquery3 = "select c.customers_id,c.customers_email_address, c.mobile_phone,c.operator_id  
		from tbl_member m 
		inner join customers c on c.customers_id=m.int_customer_id 
		where bit_active=1 and m.mtype='e'and c.mobile_phone!='' and c.operator_id!=0 
		group by c.customers_email_address 
		order by m.int_customer_id  ";
		$rsinquiry3=mysqli_query($conn,$rsquery3) or die (mysql_error());}
		
		while(list($customerid,$email,$mobile_phone,$operator_id)=mysqli_fetch_row($rsinquiry3)){
                     if($sms==1){
			$mob_op="select operator_address from mobile_operators where id=$operator_id";
			$resmob = mysqli_query($conn,$mob_op);
			list($operator_address) = mysqli_fetch_row($resmob);
			$mobile_phone = preg_replace('/[^\d]*/','',$mobile_phone);
			$mail_to = $mobile_phone.$operator_address;
			$letter = $sms_msg;
		}
		   if($sms==0) { $mail_to = $email;	
			  $letter = $newsletter;	
			}
			$mail_from = $adminemail;	
			$subject = $subject;
			$headers = "From: ".$mail_from."\n";
			$headers .= "X-Mailer: PHP/" . phpversion()."\n"; // mailer
			$headers .= "Reply-To: ". $mail_from."\n";  // Return path for errors
			$headers .= "Content-Type: text/html; charset=iso-8859-1\n"; // Mime type
	
			$mail_stat = mail($mail_to, $subject, $letter, $headers);

/*var_dump($mail_stat);
var_dump($email);
var_dump($letter);
var_dump($mail_to);*/

			if($mail_stat==1) {
				$mail=$mail.','.$mail_stat;
			}
		}
	}

//ECs newsletter


if ($audience == "Cruise" || $audience == "All") {
	
		while(list($email)=mysqli_fetch_row($rsinquiry2)){
			$mail_to = $email;
			$mail_from = $adminemail;	
			$subject = $subject;
			$headers = "From: ".$mail_from."\n";
			$headers .= "X-Mailer: PHP/" . phpversion()."\n"; // mailer
			$headers .= "Reply-To: ". $mail_from."\n";  // Return path for errors
			$headers .= "Content-Type: text/html; charset=iso-8859-1\n"; // Mime type
	
			$mail_stat = mail($mail_to, $subject, $newsletter, $headers);
			
			if($mail_stat==1) {
				$mail=$mail.','.$mail_stat;
			}
		}
	    }
	


if(trim($mail)!=""){
	$msg="Newsletter has been sent!";
}
else{
	$msg="Cannot send newsletter!";
}
}
        
header("Location: newsletter.php?msg=".$msg);
?>
