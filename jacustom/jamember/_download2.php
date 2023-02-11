<?php
/*Header("Content-type: application/vnd.ms-excel; name='excel'");
Header("Content-Disposition: attachment; filename=JA-exported.xls");*/
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=JA-exported.csv');

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$member_id = $_SESSION['member']['ses_member_id'];
$session_user = $_SESSION['member']['session_user'];

if(!empty($member_id)) {
    $a_amico = mysqli_fetch_array(mysqli_query($conn, "select amico_id from tbl_member where int_member_id='$member_id'"));
    $user_amico_id = $a_amico['amico_id'];

    $query = mysqli_query($conn, "SELECT * FROM tbl_member WHERE  chapter_id='" . $user_amico_id . "' AND is_salon='yes'") or die (mysql_error());
    //is_salon='yes' AND

    $list = array (
        0 => array('Date List Produced','COMPANY NAME','MAILING ADDRESS','CITY','STATE','ZIP CODE','PHONE NUMBER','LAST NAME','FIRST NAME','CONTACT TITLE'),
    );

    $i = 1;

    while ($f = mysqli_fetch_array($query)) {
        $title = $f['str_title'];

        $customer_id = $f['int_customer_id'];
        $res = mysqli_query($conn, "select * from customers where customers_id='$customer_id'");
        $cus = mysqli_fetch_array($res);
        $phone = $cus['customers_telephone'];

        $res = mysqli_query($conn, "select * from address_book where customers_id='$customer_id' AND address_book_id='1'");
        $address = mysqli_fetch_array($res);

        $res2 = mysqli_query($conn, "select * from zones where zone_id='" . $address['entry_state'] . "'");
        $state = mysqli_fetch_array($res2);


        $list[$i] = array(
            date("F d Y"),
            stripslashes($address['entry_company']),
            stripslashes($address['entry_street_address']),
            stripslashes($address['entry_city']),
            stripslashes($state['zone_code']),
            stripslashes($address['entry_postcode']),
            $phone,
            stripslashes($address['entry_firstname']),
            stripslashes($address['entry_lastname']),
            $title,
        );

        $i++;
    }

    // create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');

    foreach ($list as $fields) {
        fputcsv($output, $fields);
    }
}