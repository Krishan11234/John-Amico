<?php

require_once("../common_files/include/global.inc");
require_once( "session_check.inc");

if( !empty($_SESSION['admin']['is_admin']) ) {
    if (!isset($_SESSION['admin']['current_session']) or !isset($_SESSION['admin']['session_user']) or !isset($_SESSION['admin']['session_key'])) {
        header("location: ".base_admin_url()."/admin_login.php");
        exit;
    }
    if ($_SESSION['admin']['current_session'] != $_SESSION['admin']['session_user'] . "=" . $_SESSION['admin']['session_key']) {
        header("location: ".base_admin_url()."/admin_login.php?auth_msg=1");
        exit;
    }

    if( !empty($_GET['login']) ) {
        file_put_contents( base_shop_path() . '/adminloginsession.sess', md5( time() )  );
        setcookie( md5('adminLoggedInToMage'), 1 );
        header('Location:  ' . base_shop_url() . "mwadminloginout/index?login=1&fc=1");
        exit();
    }
    elseif( !empty($_GET['logout']) ) {
        if( ( !empty($_GET['mage']) && ($_GET['mage'] == 1) ) ) {
            header('Location:  ' . base_shop_url() . "mwadminloginout/index?login=0&fc=1&redirect=" . urlencode(base_admin_url() . "/logout.php"));
            exit;
        } else {
            header("location: ".base_admin_url()."/logout.php");
            exit;
        }
    }

} else {
    //echo '<pre>'; print_r($_SERVER); die();
    header("location: ".base_url()."/");
}