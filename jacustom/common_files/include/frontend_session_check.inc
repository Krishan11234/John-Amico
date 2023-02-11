<?php 
/*  Project Name		: rainbowsapphire.com
   	Program name		: admin_login.php
 	Program function	: login page - validates user and sets session.
 	Created Date  		: 20 mar 2003
 	Last Modified		: 20 mar 2003
	Author				: Shehran
	Developed by	    : Colbridge Web Logics - www.colbridge.com 
--------------------------------------------------------------------- */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


if(!isset($_SESSION['current_session']) or !isset($_SESSION['session_user']) or !isset($_SESSION['session_key'])){
    if(!isset($_SESSION['member']['current_session']) or !isset($_SESSION['member']['session_user']) or !isset($_SESSION['member']['session_key'])){
        header("location:./index.php");
        exit;
    }
}
if ($_SESSION['current_session'] != $_SESSION['session_user']."=".$_SESSION['session_key']){
    if ($_SESSION['member']['current_session'] != $_SESSION['member']['session_user']."=".$_SESSION['member']['session_key']){
        header("location:./index.php?auth_msg=1");
        exit;
    }
}

