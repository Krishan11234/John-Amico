<?php
/*  Project Name		: soh
   	Program name		: act_members.php
 	Program function	: action page for members.php
 	Created Date  		: 19 Jun, 2003
 	Last Modified		: 19 Jun, 2003 
	Author				: Shehran Sadique
	Developed by	    : Colbridge Web Logics - www.colbridge.com 
--------------------------------------------------------------------*/
require_once("session_check.inc");
require_once("../common_files/include/global.inc");

$rs = mysqli_query($conn,"select * from tbl_member where int_member_id='".$_POST['ass_memberid']."'");
if((mysqli_num_rows($rs)>0)and(trim($_SESSION['memberid'])!="")){
    $_SESSION['memberid']=$_POST['ass_memberid'];
}
else{
    header("Location: online_order.php?msg=Member ID entered was not correct!");
    exit;
}
if(isset($_POST['add'])){
    $rs = mysqli_query($conn,"select customers_email_address from customers where customers_email_address='".$_POST['email']."'");
    $no_rows = mysqli_num_rows($rs);
    if ($no_rows<=0){//if no user with the same username exists
        //inserting values into customers
        $table = "customers";				// inserting values to setting table
        $in_fieldlist="customers_firstname,customers_lastname,customers_email_address,customers_telephone,customers_fax,customers_password,int_price_level";
        $in_values="'{$_POST['firstname']}','{$_POST['lastname']}','{$_POST['email']}','{$_POST['phone']}','{$_POST['fax']}','{$_POST['pass']}',1";
        $result=insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values
        $customer_id=mysqli_insert_id($conn);//id of the customer inserted
        //end inserting values into customers

        //inserting values into address_book contact billing info by putting '1' in address_book_id
        $table = "address_book";				// inserting values to setting table
        $in_fieldlist="customers_id,address_book_id,entry_company,entry_firstname,entry_lastname,entry_street_address,entry_postcode,entry_city,entry_state,entry_country_id,entry_zone_id";
        $in_values="$customer_id,1,'{$_POST['company']}','{$_POST['firstname']}', '{$_POST['lastname']}', '{$_POST['streetadd']}','{$_POST['postcode']}','{$_POST['city']}','{$_POST['state']}',{$_POST['country']},'{$_POST['zone']}'";
        $result=insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values
        //end inserting values into address_book

        //inserting values into address_book contact billing info by putting '2' in address_book_id
        if($_POST['shiped']==1){
            $table = "address_book";				// inserting values to setting table
            $in_fieldlist="customers_id,address_book_id,entry_company,entry_firstname,entry_lastname,entry_street_address,entry_postcode,entry_city,entry_state,entry_country_id,entry_zone_id";
            $in_values="$customer_id,2,'{$_POST['company']}','{$_POST['firstname']}', '{$_POST['lastname']}', '{$_POST['streetadd']}','{$_POST['postcode']}','{$_POST['city']}','{$_POST['state']}',{$_POST['country']},'{$_POST['zone']}'";
            $result=insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values
        }
        else{
            $table = "address_book";				// inserting values to setting table
            $in_fieldlist="customers_id,address_book_id,entry_company,entry_firstname,entry_lastname,entry_street_address,entry_postcode,entry_city,entry_state,entry_country_id,entry_zone_id";
            $in_values="$customer_id,2,'{$_POST['company']}','{$_POST['s_firstname']}','{$_POST['s_lastname']}', '{$_POST['s_streetadd']}', '{$_POST['s_postcode']}','{$_POST['s_city']}','{$_POST['s_state']}',{$_POST['s_country']},'{$_POST['s_zone']}'";
            $result=insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values
        }
        //end inserting values into address_book
        //sending mail
        //include('./sendmail.php');
        //end of mail
        $msg="Registation sucessfull. Please Login above.";
    }
    else{
        $msg="\"$email\" already exists. Please choose another.";
        header("Location: online_order.php");
    }
}

elseif(isset($_POST['update'])){

    $table = "customers";
    $fieldlist="customers_firstname='{$_POST['firstname']}', customers_lastname='{$_POST['lastname']}', customers_email_address='{$_POST['email']}', customers_telephone='{$_POST['phone']}', customers_fax='{$_POST['fax']}', customers_password='{$_POST['password']}' ";
    $condition=" where customers_id = {$_POST['customerid']}";
    $result=update_rows($conn, $table, $fieldlist, $condition);
    $customer_id= $_POST['customerid'];
    $table = "address_book";
    $fieldlist="entry_company='{$_POST['company']}', entry_firstname='{$_POST['firstname']}', entry_lastname='{$_POST['lastname']}', entry_street_address='{$_POST['streetadd']}', entry_postcode='{$_POST['postcode']}', entry_city='{$_POST['city']}', entry_country_id='{$_POST['country']}', entry_zone_id='{$_POST['zone']}'";
    $condition=" where customers_id = {$_POST['customerid']} and address_book_id=1";
    $result=update_rows($conn, $table, $fieldlist, $condition);
    if($shiped==1){
        $table = "address_book";
        $fieldlist="entry_company='{$_POST['company']}', entry_firstname='{$_POST['firstname']}', entry_lastname='{$_POST['lastname']}', entry_street_address='{$_POST['streetadd']}', entry_postcode='{$_POST['postcode']}', entry_city='{$_POST['city']}', entry_country_id='{$_POST['country']}', entry_zone_id='{$_POST['zone']}'";
        $condition=" where customers_id = {$_POST['customerid']} and address_book_id=2";
        $result=update_rows($conn, $table, $fieldlist, $condition);
    }
    else{
        $table = "address_book";
        $fieldlist="entry_company='{$_POST['sh_company']}', entry_firstname='{$_POST['sh_firstname']}', entry_lastname='{$_POST['sh_lastname']}', entry_street_address='{$_POST['sh_streetadd']}', entry_postcode='{$_POST['sh_postcode']}', entry_city='{$_POST['sh_city']}', entry_country_id='{$_POST['sh_country']}', entry_zone_id='{$_POST['sh_zone']}'";
        $condition=" where customers_id = {$_POST['customerid']} and address_book_id=2";
        $result=update_rows($conn, $table, $fieldlist, $condition);
    }
    if($_POST['ismember']==1){
        if($_POST['newmemberid']==""){
            $newmemberid=0;
        }
        else{
            $newmemberid=$_POST['newmemberid'];
        }
        $table = "tbl_member";				// inserting values to setting table
        $in_fieldlist="int_parent_id,int_designation_id,int_customer_id,str_title,dat_last_visit,bit_active";
        $in_values="$newmemberid,1,{$_POST['customerid']},'Mr','$today',1";
        $result=insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values
        $member_id=mysqli_insert_id($conn);
        if($newmemberid!=0){
            addcontactlist($newmemberid,$member_id);
        }
    }

}

function addcontactlist($memberid,$addid){
    global $conn;

    $rscontact=mysqli_query($conn,"select str_member_contact_list from tbl_member_contact_list where int_member_id=$memberid");
    list($contactlist) = mysqli_fetch_row($rscontact);
    $contactlist=$contactlist.$addid.',';

    $sql="update tbl_member_contact_list set str_member_contact_list='".$contactlist."' where int_member_id=$memberid";
    $result =  mysqli_query($conn,$sql);

    $rsparent=mysqli_query($conn,"select int_parent_id from tbl_member where int_member_id=$memberid");
    while(list($parentmemberid) = mysqli_fetch_row($rsparent)){
        if($parentmemberid!=0){
            addcontactlist($parentmemberid,$addid);
        }
    }
}
$_SESSION['customerid']=$customer_id;
$_SESSION['orders']="";
header("Location: online_order.php?step=1");
