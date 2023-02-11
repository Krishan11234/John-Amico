<?php 

require_once("../include/global.inc");

if( !is_session_started() ) {
    session_start();
}

$is_admin_panel = true;

//debug(false, true, $_SESSION);

if(!empty($is_admin_panel)) {
    if (!empty($_SESSION['admin']['is_admin'])) {
        if (!isset($_SESSION['admin']['current_session']) or !isset($_SESSION['admin']['session_user']) or !isset($_SESSION['admin']['session_key'])) {
            header("location:" . base_admin_url() . "/admin_login.php");
            exit;
        }
        if ($_SESSION['admin']['current_session'] != $_SESSION['admin']['session_user'] . "=" . $_SESSION['admin']['session_key']) {
            header("location:" . base_admin_url() . "/admin_login.php?auth_msg=1");
            exit;
        }
    }
    else {
        header("location:" . base_admin_url() . "/admin_login.php");
        exit;
    }
}

if(!empty($is_member_panel)) {
    if (!empty($_SESSION['member']['is_member'])) {
        if (!isset($_SESSION['member']['current_session']) or !isset($_SESSION['member']['session_user']) or !isset($_SESSION['member']['session_key'])) {
            header("location:" . base_member_url() . "/member_login.php");
            exit;
        }
        if ($_SESSION['member']['current_session'] != $_SESSION['member']['session_user'] . "=" . $_SESSION['member']['session_key']) {
            header("location:" . base_member_url() . "/member_login.php?auth_msg=1");
            exit;
        }
    }
    else {
        header("location:" . base_member_url() . "/member_login.php");
        exit;
    }
}