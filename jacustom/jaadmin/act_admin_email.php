<?php
/*  Project Name		: amico
   	Program name		: act_admins_email.php
 	Program function	: manage admin email.
 	Created Date 	 	: 16 Jun, 2003
 	Last Modified		: 16 Jun, 2003 
	Author				: Sudheesh
	Developed by	    : Colbridge Web Logics - www.colbridge.com 
--------------------------------------------------------------------*/
require_once("session_check.inc");
require_once("../common_files/include/global.inc");

if(isset($_POST['Update'])){
	$trimmed_email = trim($_POST['AdminEmail']);
	$trimmed_email1 = trim($_POST['AdminEmail1']);
	$trimmed_email2 = trim($_POST['AdminEmail2']);
	$table="tbl_admin_email";
	$fieldlist="str_admin_email='$trimmed_email',str_admin_email1='$trimmed_email1',str_admin_email2='$trimmed_email2'";
	$condition="";
	$result=update_rows($conn, $table, $fieldlist, $condition);// function call to update
}
header("Location: index.php");	
?>