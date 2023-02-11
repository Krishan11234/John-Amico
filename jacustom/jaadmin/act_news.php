<?php 
/*  Project Name		: soh
   	Program name		: act_members.php
 	Program function	: action page for members.php
 	Created Date  		: 19 Jun, 2003
 	Last Modified		: 19 Jun, 2003 
	Author				: Shehran Sadique
	Developed by	    : Colbridge Web Logics - www.colbridge.com 
--------------------------------------------------------------------*/
require_once("session_check.inc");
require_once("../common_files/include/global.inc");

/*if(isset($_GET[delete_attachment])){
	unlink("nl_attachments/$_GET[int_news_id].dat");
	
	$sql = "UPDATE tbl_news SET str_attachment = NULL WHERE int_news_id = '{$_GET[int_news_id]}'";
	mysqli_query($conn,$sql);
	echo mysql_error();

	$msg = "Attachment Deleted.";
}
elseif(isset($_POST[add])){*/
if(isset($_POST['add'])){
		$table = "tbl_news";				// inserting values to setting table
		$in_fieldlist="str_date,str_title,str_news,bit_active";
		$in_values="'{$_POST['y1']}-{$_POST['m1']}-{$_POST['d1']}','{$_POST['title']}','{$_POST['news']}',1";
		$result=insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values
		$int_news_id = mysqli_insert_id($conn);

		/*if($attachment)
		{
			move_uploaded_file($_FILES[attachment][tmp_name], "nl_attachments/".$int_news_id.".dat");
			$sql = "UPDATE tbl_news SET str_attachment = '{$_FILES[attachment][name]}' WHERE int_news_id = '$int_news_id'";
			mysqli_query($conn,$sql);
			echo mysql_error();
		}*/
}
	
elseif(isset($_POST['update'])){
	 
	 $table = "tbl_news";
	 $fieldlist="str_date='{$_POST['y1']}-{$_POST['m1']}-{$_POST['d1']}', str_title='{$_POST['title']}', str_news='{$_POST['news']}'";
	 $condition=" where int_news_id = {$_POST['newsid']}";
	 $result=update_rows($conn, $table, $fieldlist, $condition);

	/*if($attachment)
	{
		move_uploaded_file($_FILES[attachment][tmp_name], "nl_attachments/".$_POST[newsletterid].".dat");
		$sql = "UPDATE tbl_news SET str_attachment = '{$_FILES[attachment][name]}' WHERE int_news_id = '{$_POST[newsletterid]}'";
		mysqli_query($conn,$sql);
		echo mysql_error();
	}*/
}
elseif(isset($_POST['activate'])){
	 if($_POST['active']==0){$active=1;}else{$active=0;}
	 $table = "tbl_news";
	 $fieldlist="bit_active=$active";
	 $condition=" where int_news_id = {$_POST['newsid']}";
	 $result=update_rows($conn, $table, $fieldlist, $condition);
}
elseif(isset($_GET['delete'])){
	/*$sql = "SELECT str_attachment FROM tbl_news WHERE int_news_id = '{$_GET[newsletterid]}' AND str_attachment IS NOT NULL";
	$result = mysqli_query($conn,$sql);
	echo mysql_error();

	if(mysqli_num_rows($result) > 0)
	{
		unlink("nl_attachments/$_GET[newsletterid].dat");
	}*/

	$table = "tbl_news";
	echo $note_status_id;
	 $condition=" where int_news_id = {$_GET['newsid']}";
	$result=del_rows($conn, $table, $condition);// function call to delete
}
/*elseif(isset($_GET[send])==1){
	$rsemail=mysqli_query($conn,"select str_admin_email from tbl_admin_email where int_admin_email_id=1");
	list($adminemail)=mysqli_fetch_row($rsemail);

	$rsnewsletter=mysqli_query($conn,"select str_subject,str_newsletter,str_attachment from tbl_news where int_news_id=".$_GET[newsletterid]);
	list($subject,$newsletter,$attachment)=mysqli_fetch_row($rsnewsletter);
	if($_GET['status']==0) 
	{
		$rsmember=mysqli_query($conn,"select c.customers_id,c.customers_email_address from tbl_member m left outer join customers c on c.customers_id=m.int_customer_id order by m.int_customer_id");
		$query="select c.customers_id,c.customers_email_address from tbl_member m left outer join customers c on c.customers_id=m.int_customer_id order by m.int_customer_id";
	}
	elseif($_GET['status']==1) 
	{
		$rsmember=mysqli_query($conn,"select c.customers_id,c.customers_email_address from tbl_member m left outer join customers c on c.customers_id=m.int_customer_id where bit_active=1 order by m.int_customer_id");
		$query="select c.customers_id,c.customers_email_address from tbl_member m left outer join customers c on c.customers_id=m.int_customer_id where bit_active=1 order by m.int_customer_id";
	}
	elseif($_GET['status']==2) 
	{
		$rsmember=mysqli_query($conn,"select c.customers_id,c.customers_email_address from tbl_member m left outer join customers c on c.customers_id=m.int_customer_id where bit_active=0 order by m.int_customer_id");
		$query="select c.customers_id,c.customers_email_address from tbl_member m left outer join customers c on c.customers_id=m.int_customer_id where bit_active=0 order by m.int_customer_id";
	}

//		echo $query;
//		exit;
	require("class.phpmailer.php");

	$mailer = new phpmailer();

	$mailer->From = $adminemail;
	$mailer->FromName = "JOHN AMICO";
	$mailer->WordWrap = 50;      // set word wrap
	$mailer->IsHTML(true);                               // send as HTML
	$mailer->Subject = $subject;
	$mailer->Body = $newsletter;

	if(!empty($attachment))
	{
		$mailer->AddAttachment("nl_attachments/$_GET[newsletterid].dat", $attachment);
	}
	
	while(list($customerid,$email)=mysqli_fetch_row($rsmember)){
		$mailer->ClearAddresses();
//		$mailer->AddAddress("patrick@mediavue.net", '');
		$mailer->AddAddress($email, '');
		
		if($mailer->send())
		{
			$mail .= "1";
		}
	}
if(trim($mail)!=""){
	$msg="News Letter has been send to all the members !";
}
else{
	$msg="News Letter cannot be send !";
}
}*/

header("Location: ".base_admin_url()."/news.php?msg=".$msg);