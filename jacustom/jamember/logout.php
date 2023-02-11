<?php 

//require_once("session_check.inc");
require_once("../common_files/include/common_functions.php");
session_unregister("ses_member_id");
$_SESSION['member']['is_member'] = false;
$_SESSION['member'] = array();

setcookie("membid", '', (time() - (3600 * 24 * 30)));

if( !empty($_GET['redirect_to']) ) {
    $redirect = urldecode($_GET['redirect_to']);
} else {
    $redirect = base_member_url(). "/member_login.php";
}

$magento_url = base_shop_url() . "jamemberrefer/setsession/unset". "?redirect_to=".urlencode($redirect);

//header("Location:./member_login.php");
header("Location: $magento_url");
