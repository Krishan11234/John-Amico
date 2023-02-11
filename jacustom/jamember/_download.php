<?php 
/*Header("Content-type: application/vnd.ms-excel; name='excel'");
Header("Content-Disposition: attachment; filename=JA-exported.xls");*/
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=JA-exported.csv');


require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$member_id = $_SESSION['member']['ses_member_id'];
$session_user = $_SESSION['member']['session_user'];

// var_dump($_SESSION['member']); exit;

if(!empty($member_id)) {

    $a_amico = mysqli_fetch_array(mysqli_query($conn, "select amico_id from tbl_member where int_member_id='$member_id'"));
    $user_amico_id = $a_amico['amico_id'];

    if($_SESSION['member']['mtype'] == 'c'){
        $query = mysqli_query($conn, "SELECT * FROM tbl_member WHERE chapter_id='" . $user_amico_id . "'");
    }elseif ($_SESSION['member']['mtype'] == 'e') {
        $query = mysqli_query($conn, "SELECT * FROM tbl_member WHERE ec_id='" . $user_amico_id . "'");
    }else{
        $rslist = mysqli_query($conn, "select str_member_contact_list from tbl_member_contact_list where int_member_id='$member_id'");
        list($contactlist) = mysqli_fetch_row($rslist);
        $contactlist = implode(',', array_filter(explode(',', $contactlist)) );
        $query = mysqli_query($conn, "SELECT * FROM tbl_member WHERE int_member_id IN ($contactlist)");
    }


    // if($_SESSION['member']['mtype'] == 'm'){
        // $query = mysqli_query($conn, "SELECT int_member_id FROM tbl_member WHERE int_member_id in($contactlist)");
    // }

    //is_salon='yes' AND

    $list = array (
        0 => array('crew_member_id','int_sales','int_commission','check_string','mdt','ytd','2018','2019','first_name','last_name','customers_email_address','phone','address1','address2','city','state','zip','active_members','current_title','highest_title','date_achieved','current_year_highest_title','first_time_title'),
    );

    $i = 1;
    while ($f = mysqli_fetch_array($query)) {

        $customer_id = $f['int_customer_id'];
        $res = mysqli_query($conn, "select * from customers where customers_id='$customer_id'");
        $cus = mysqli_fetch_array($res);

        $res = mysqli_query($conn, "select * from address_book where customers_id='$customer_id' AND address_book_id='1'");
        $address = mysqli_fetch_array($res);

        $zip = $address['entry_postcode'];
        $res = mysqli_query($conn, "select * from zones where zone_id='" . $address['entry_state'] . "'");
        $state = mysqli_fetch_array($res);

        $list[$i] = array(
            stripslashes($f['amico_id']),
            '',
            '',
            '',
            $f['mtd'],
            $f['ytd'],
            $f['ytd2018'],
            $f['ytd2019'],
            stripslashes($cus['customers_firstname']),
            stripslashes($cus['customers_lastname']),
            stripslashes($cus['customers_email_address']),
            stripslashes($cus['customers_telephone']),
            stripslashes($address['entry_street_address']),
            stripslashes($address['entry_street_address2']),
            stripslashes($address['entry_city']),
            stripslashes($state['zone_name']),
            stripslashes($zip),
            '',
            stripslashes($cus['str_title']),
            '',
            '',
            '',
            '',
        );

        $i++;
    }


    // create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');

    foreach ($list as $fields) {
        fputcsv($output, $fields);
    }

}