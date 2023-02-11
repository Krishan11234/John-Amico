<?php
/*  Project Name		: Amico	
   	Program name		: act_admin
 	Program function	: Manage admin actions
 	Created Date  		: 4 jul, 2003
 	Last Modified		: 4 jul, 2003 
	Author				: Shehran
	Developed by	    : Colbridge Web Logics - www.colbridge.com */
//--------------------------------------------------------------------
require_once("session_check.inc");
require_once("../common_files/include/global.inc");

if(isset($_POST['add'])){
	// checking for duplicate username.

	$rssel_username = mysqli_query($conn,"select str_username from tbl_admin WHERE str_username =
	 '{$_POST['admin_username']}'");
	list($tmp_username)= mysqli_fetch_row($rssel_username);

	if($tmp_username == ""){
		$table = "tbl_admin";	// inserting values to Admins table
		$in_fieldlist="str_first_name,str_last_name,str_email,str_username
			,str_password,bit_active";
		$in_values="'{$_POST['firstname']}','{$_POST['lastname']}','{$_POST['email']}','{$_POST['admin_username']}','{$_POST['admin_pass']}',1";
		$result=insert_fields($conn, $table, $in_fieldlist, $in_values);
		// function calls for inserting values
	}
	else{
		header("Location: admin.php?dup=1&adminid=0&firstname=$firstname&lastname=$lastname&email=$email");
		exit;
	}
}
elseif(isset($_POST['activate'])){
    if($_POST['active'] == 1){$_POST['active'] = 0;}else{$_POST['active'] = 1;}
	
	$table="tbl_admin";
	$fieldlist="bit_active={$_POST['active']}";
	$condition=" where int_admin_id={$_POST['adminid']}";
	$result=update_rows($conn, $table, $fieldlist, $condition); // function call to update
}
elseif(isset($_POST['update'])){
	// checking for duplicate username.
	$rssel_username = mysqli_query($conn,"select int_admin_id from tbl_admin WHERE str_username =
		 '{$_POST['admin_username']}'");
	list($tmp_adminid)= mysqli_fetch_row($rssel_username);
   	if(($tmp_adminid == "") or ($tmp_adminid == $adminid)){
		$table="tbl_admin";
		$fieldlist="str_first_name='{$_POST['firstname']}',str_last_name='{$_POST['lastname']}',str_email='{$_POST['email']}'
			,str_username='{$_POST['admin_username']}',str_password='{$_POST['admin_pass']}'";
		$condition=" where int_admin_id = {$_POST['adminid']}";
		$result=update_rows($conn, $table, $fieldlist, $condition);// function call to update
	}
	else{
		header("Location: admin.php?dup=1&adminid=$adminid&firstname=$firstname
			&lastname=$lastname&email=$email");	
		exit;
	}	
}
elseif(isset($_GET['delete'])){
	$table="tbl_admin";
	$condition=" where int_admin_id={$_GET['adminid']}";
	$result=del_rows($conn, $table, $condition);// function call to delete
}
header("Location: admin.php");	
?>