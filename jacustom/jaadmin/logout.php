<?php 

//require_once("session_check.inc");
require_once("../common_files/include/common_functions.php");
session_unregister("ses_admin_id");
$_SESSION['admin']['is_admin'] = false;

if( ( !empty($_GET['mage']) && ($_GET['mage'] == 1) ) ) {
    setcookie(md5('adminLoggedInToMage'), 0, time() - 2500);
}

header("Location:./admin_login.php");
