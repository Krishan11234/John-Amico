<?php 

require_once("../common_files/include/global.inc");

if( !is_session_started() ) {
    session_start();
}

$redirect_to = "redirect_to=" . urlencode(currentPageURL());

//debug(false, true, $_SESSION);

if( !empty($_SESSION['admin']['is_admin']) ) {
	if(!isset($_SESSION['admin']['current_session']) or !isset($_SESSION['admin']['session_user']) or !isset($_SESSION['admin']['session_key'])){
		header("location:".base_admin_url()."/admin_login.php?$redirect_to");
		exit;	
	}
	if ($_SESSION['admin']['current_session'] != $_SESSION['admin']['session_user']."=".$_SESSION['admin']['session_key']){
		header("location:".base_admin_url()."/admin_login.php?auth_msg=1&$redirect_to");
		exit;
	}
}
else {
    header("location:".base_admin_url()."/admin_login.php?$redirect_to");
    exit;
}