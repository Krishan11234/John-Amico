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

if (isset($_POST['Update'])) {

    $today = $_POST['recdate'];
    if ($today != "") {
        list($month, $day, $year) = explode('/', $today);
        $today = date("Y/m/d", mktime(0, 0, 0, $month, $day, $year));
    }

    $table = "tbl_salesrecord";
    $fieldlist = "int_member_id={$_POST['member_id']},dtt_record='$today',int_salesrecord={$_POST['salesrecord']},description='{$_POST['description']}',reward='{$_POST['reward']}'";
    //echo $fieldlist;

    $condition = "where int_salesrecord_id=" . $_POST['rec_id'];
    $result = update_rows($conn, $table, $fieldlist, $condition);// function call to update
}

else {
    if ($action == "delete") {
        $table = "tbl_salesrecord";
        $condition = "where int_salesrecord_id='$id'";
        //echo $condition;
        $result = del_rows($conn, $table, $condition);
    }
    else {

        $today = $_POST['recdate'];
        if ($today != "") {
            list($month, $day, $year) = explode('/', $today);
            $today = date("Y/m/d", mktime(0, 0, 0, $month, $day, $year));
        }

        $table = "tbl_salesrecord";
        $in_fieldlist = "int_member_id,dtt_record,int_salesrecord,bit_active,description,reward";
        $values = $_POST['member_id'] . ",'$today','" . $_POST['salesrecord'] . "',1,'{$_POST['description']}','{$_POST['reward']}'";
        $result = insert_fields($conn, $table, $in_fieldlist, $values);// function call to insert
    }
}
header("Location: ".base_admin_url()."/salesrecord.php");