<?php 

require_once("../common_files/include/global.inc");

if( !is_session_started() ) {
    session_start();
}

$redirect_to = "redirect_to=" . urlencode(currentPageURL());

//debug(false, true, $_SESSION);
if( !empty($_SESSION['member']['is_member']) ) {

    if( empty($_COOKIE['membid']) ) {
        header("location:".base_member_url()."/member_login.php?$redirect_to");
        exit;
    }

    if(!isset($_SESSION['member']['current_session']) or !isset($_SESSION['member']['session_user']) or !isset($_SESSION['member']['session_key'])){
        header("location:".base_member_url()."/member_login.php?$redirect_to");
        exit;
    }
    if ($_SESSION['member']['current_session'] != $_SESSION['member']['session_user']."=".$_SESSION['member']['session_key']){
        header("location:".base_member_url()."/member_login.php?auth_msg=1&$redirect_to");
        exit;
    }

    $_SESSION['ses_member_id'] = $_SESSION['member']['ses_member_id'];
}
else {
    header("location:".base_member_url()."/member_login.php?$redirect_to");
	exit;	
}