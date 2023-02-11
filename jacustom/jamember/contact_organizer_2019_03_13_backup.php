<?php
$page_name = 'Contact Organizer';
$page_title = 'John Amico - ' . $page_name;

$billabletime = 10;

$useMagento = true;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("contact_organizer_functions.php");
require_once( base_library_path().'/mailer/class.phpmailer.php');
require_once("../$common_files__folder/include/functions.php");

require_once("templates/header.php");
require_once("templates/sidebar.php");



$member_type_name = 'Contact';
$member_type_name_plural = 'Contacts';
$self_page = 'contact_organizer.php';
$page_url = base_member_url() . "/$self_page?";
$action_page = 'contact_organizer.php';
$action_page_url = base_member_url() . "/$self_page?1=1";
//$export_url = base_admin_url() . '/members_export.php';


define('SMS_MESSAGE_BOX_PLACEHOLDER', 'Maximum message length is 100 characters.');

$is_edit = $is_add = false;
$success_message = $error_message = $member_search_result = $conditions = array();

//debug(false, true, $_POST);

$member_id = $_SESSION['member']['ses_member_id'];
$session_user = $_SESSION['member']['session_user'];

$mesg = ( !empty($_GET['msg']) ? $_GET['msg'] : '' );


$autoship_enabled = is_autoship_enable();


$a_amico = mysqli_fetch_array(mysqli_query($conn,"select m.*, c.customers_email_address from tbl_member m INNER JOIN customers c ON c.customers_id = m.int_customer_id  where int_member_id='$member_id'"));
$user_amico_id = $a_amico['amico_id'];
$user_mtype = $a_amico['mtype'];
$user_email = $a_amico['customers_email_address'];

//debug(true, true, $user_amico_id, $user_mtype, $member_id, $a_amico);


if( !empty($_POST['goto']) && ($_POST['goto'] == 'send_email') && !empty($_POST['send_email']) ) {

    //$message = !empty($_POST['message']) ? filter_var($_POST['message'], FILTER_SANITIZE_STRING) : '';
    $message = !empty($_POST['message']) ? $_POST['message'] : '';
    $sms_message = !empty($_POST['sms_message']) ? filter_var($_POST['sms_message'], FILTER_SANITIZE_STRING) : '';
    $email_to = !empty($_POST['email_to']) ? filter_var($_POST['email_to'], FILTER_SANITIZE_STRING) : '';
    $subject = !empty($_POST['subject']) ? filter_var($_POST['subject'], FILTER_SANITIZE_STRING) : '';
    $from = !empty($_POST['from']) ? filter_var($_POST['from'], FILTER_SANITIZE_STRING) : '';
    $isLink = !empty($_POST['islink']) ? true : false;
    $is_sms = !empty($_POST['sms']) ? true : false;
    $linktext = !empty($_POST['linktext']) ? filter_var($_POST['linktext'], FILTER_SANITIZE_STRING) : '';

    if( empty($from) && !empty($user_email) ) {
        $from = $user_email;
    }

    if($is_sms) {
        $message = $sms_message;

        if(trim($message) == SMS_MESSAGE_BOX_PLACEHOLDER) {
            $message = '';
        }
    }

    if( empty($message) ) {
        if (!$is_sms) {
            $error_message['message'] = 'You need to enter your message.';
        }
        else {
            $error_message['sms_message'] = 'You need to enter your message.';
        }
    }
    if( empty($subject) ) {
        if (!$is_sms) {
            $error_message['subject'] = 'You need to enter email subject.';
        }
    }
    if( empty($from) ) {
        if (!$is_sms) {
            $error_message['from'] = 'You need to enter "From Email Address"';
        }
    }

    //debug(true, true, $_POST, $error_message);

    if( empty($error_message) ) {

        $mail_queue = new MailQueue();

        if (in_array($email_to, array( 'downline', 'contact', 'other', 'both', 'filtered' ))) {

            $unsubscribedMembersSql = "SELECT sub_member_id FROM tbl_member_subscription WHERE ref_member_id={$member_id} AND subscribed=0";
            $unsubscribedMembersQuery = mysqli_query($conn, $unsubscribedMembersSql);
            while($unsubscribedMember = mysqli_fetch_assoc($unsubscribedMembersQuery) ) {
                $unsubscribedMembers[] = $unsubscribedMember['sub_member_id'];
            }
            //debug(false, true, $unsubscribedMembers);

            switch ($email_to) {
                case 'downline':
                    if($user_mtype == 'c') {
                        $mail_query = "select c.customers_firstname,c.customers_lastname,c.customers_email_address, m.int_member_id, m.amico_id
                            from customers c
                            inner join tbl_member m on c.customers_id=m.int_customer_id
                            where m.chapter_id='{$user_amico_id}'";
                        if ($is_sms) {
                            $mail_query = "select c.customers_firstname,c.customers_lastname,c.customers_email_address, c.mobile_phone, c.operator_id, m.int_member_id, m.amico_id, mo.operator_address
                              from customers c inner join tbl_member m on c.customers_id=m.int_customer_id
                              RIGHT JOIN mobile_operators mo ON mo.id=c.operator_id
                              where m.chapter_id='{$user_amico_id}' and c.mobile_phone!='' and c.operator_id!=0 ";
                        }
                    }
                    else {
                        $rslist = mysqli_query($conn, "select str_member_contact_list from tbl_member_contact_list where int_member_id={$member_id}");
                        list($contactlist) = mysqli_fetch_row($rslist);

                        $contactlist = substr($contactlist, 0, (strlen($contactlist) - 1));

                        $mail_query = "select c.customers_firstname,c.customers_lastname,c.customers_email_address, m.int_member_id, m.amico_id
                            from customers c
                            inner join tbl_member m on c.customers_id=m.int_customer_id
                            where m.int_member_id in(" . $contactlist . ")";
                        if ($is_sms) {
                            $mail_query = "select c.customers_firstname,c.customers_lastname,c.customers_email_address, c.mobile_phone, c.operator_id, m.int_member_id, m.amico_id, mo.operator_address
                              from customers c inner join tbl_member m on c.customers_id=m.int_customer_id
                              RIGHT JOIN mobile_operators mo ON mo.id=c.operator_id
                              where m.int_member_id in(" . $contactlist . ") and c.mobile_phone!='' and c.operator_id!=0 ";
                        }
                    }

                    $results = mysqli_query($conn, $mail_query);
                    while ($rows = mysqli_fetch_array($results)) {
                        if(in_array($rows['int_member_id'], $unsubscribedMembers)) { continue; }

                        if ($is_sms) {
                            $operator_address = $rows['operator_address'];
                            $mobile_phone = preg_replace('/[^\d]*/', '', $rows['mobile_phone']);
                            $this_to = $mobile_phone . $operator_address;
                            if ($rows['operator_id'] == '51') {
                                $customer_name = $rows['customers_firstname'] . " " . $rows['customers_lastname'];
                                send_mail_thru_gmail($from, $from, $this_to, "", $from, $customer_name, $subject, $letter);
                            }
                        }
                        else {
                            $this_to = $rows['customers_firstname'] . " " . $rows['customers_lastname'];
                            $this_to .= " <" . $rows['customers_email_address'] . ">";
                        }

                        $head = get_mail_header($from, $un);
                        $zag = get_mail_html_body($message, $un);

                        if ($rows['operator_id'] !== '51') {
                            $mail_queue->add_to_queue($this_to, $subject, $zag, $head, $session_user, $rows['int_member_id'], $rows['amico_id'], $from);
                        }
                    }

                    $mail_queue->save_queue();

                    if( is_in_live() ) {
                        mail("john.amicojr@johnamico.com", $subject, $zag, $head);
                        mail("john.amicojr@gmail.com", $subject, $zag, $head);
                        //mail("omar@mvisolutions.com", $subject, $zag, $head);
                    }

                    break;

                case 'other' :
                    if ($user_mtype == "e") {
                        if ($is_sms) {
                            if($useMagento) {
                                $mail_query = "SELECT DISTINCT o.customer_firstname AS customers_firstname, o.customer_lastname AS customers_lastname, o.customer_email AS customers_email_address, oa.telephone AS mobile_phone, NULL AS operator_id, t.int_member_id, t.amico_id, NULL AS operator_address, NULL AS operator_address
                                        FROM stws_sales_flat_order o
                                        INNER JOIN stws_sales_flat_order_address as oa ON o.billing_address_id=oa.entity_id
                                        INNER JOIN stws_amasty_amorderattr_order_attribute as oat ON o.entity_id=oat.order_id
                                        INNER JOIN tbl_member t ON oat.jareferrer_amicoid=t.amico_id
                                        WHERE
                                        oat.jareferrer_self=1
                                        AND o.customer_email !=''
                                        AND oa.telephone!=''
                                        AND t.ec_id ='{$user_amico_id}' ";
                            } else {
                                $mail_query = "SELECT c.customers_firstname, c.customers_lastname, c.customers_email_address, c.mobile_phone, c.operator_id, t.int_member_id, t.amico_id,mo.operator_address, mo.operator_address
                                        FROM customers c, tbl_member t, orders o, mobile_operators mo
                                        WHERE mo.id=c.operator_id AND  o.int_member_id = t.int_member_id AND c.mobile_phone!='' AND c.operator_id!=0 AND t.int_customer_id = c.customers_id AND t.ec_id = '" . $user_amico_id . "'";
                            }
                        }
                        else {
                            if( $useMagento ) {
                                $mail_query = "SELECT DISTINCT o.customer_firstname AS customers_firstname, o.customer_lastname AS customers_lastname, o.customer_email AS customers_email_address, t.int_member_id, t.amico_id
                                        FROM stws_sales_flat_order o
                                        INNER JOIN stws_sales_flat_order_address as oa ON o.billing_address_id=oa.entity_id
                                        INNER JOIN stws_amasty_amorderattr_order_attribute as oat ON o.entity_id=oat.order_id
                                        INNER JOIN tbl_member t ON oat.jareferrer_amicoid=t.amico_id
                                        WHERE
                                        oat.jareferrer_self=1
                                        AND o.customer_email !=''
                                        AND t.ec_id ='{$user_amico_id}' ";
                            } else {
                                $mail_query = "SELECT c.customers_firstname, c.customers_lastname, c.customers_email_address, t.int_member_id, t.amico_id
                                    FROM customers c, tbl_member t, orders o
                                    WHERE o.int_member_id = t.int_member_id AND t.int_customer_id = c.customers_id AND t.ec_id = '" . $user_amico_id . "'";
                            }
                        }
                    }
                    if ($user_mtype == "c") {
                        if ($is_sms) {
                            if( $useMagento ) {
                                $mail_query = "SELECT o.customer_firstname AS customers_firstname, o.customer_lastname AS customers_lastname, o.customer_email AS customers_email_address, oa.telephone AS mobile_phone, NULL AS operator_id, t.int_member_id, t.amico_id, NULL AS operator_address, NULL AS operator_address
                                        FROM stws_sales_flat_order o
                                        INNER JOIN stws_sales_flat_order_address as oa ON o.billing_address_id=oa.entity_id
                                        INNER JOIN stws_amasty_amorderattr_order_attribute as oat ON o.entity_id=oat.order_id
                                        INNER JOIN tbl_member t ON oat.jareferrer_amicoid=t.amico_id
                                        WHERE
                                         oat.jareferrer_self=1
                                        AND o.customer_email !=''
                                        AND oa.telephone!=''
                                        AND t.chapter_id = '{$user_amico_id}'
                                        GROUP BY t.int_member_id";
                            } else {
                                $mail_query = "SELECT c.customers_firstname, c.customers_lastname, c.customers_email_address,c.mobile_phone, c.operator_id, t.int_member_id, t.amico_id,mo.operator_address
                                    FROM customers c, tbl_member t, orders o, mobile_operators mo
                                    WHERE mo.id=c.operator_id AND o.int_member_id = t.int_member_id AND t.int_customer_id = c.customers_id  AND c.mobile_phone!='' AND c.operator_id!=0 AND t.chapter_id = '" . $user_amico_id . "' GROUP BY t.int_member_id";
                            }
                        }
                        else {
                            if( $useMagento ) {
                                $mail_query = "SELECT o.customer_firstname AS customers_firstname, o.customer_lastname AS customers_lastname, o.customer_email AS customers_email_address, t.int_member_id, t.amico_id
                                        FROM stws_sales_flat_order o
                                        INNER JOIN stws_sales_flat_order_address as oa ON o.billing_address_id=oa.entity_id
                                        INNER JOIN stws_amasty_amorderattr_order_attribute as oat ON o.entity_id=oat.order_id
                                        INNER JOIN tbl_member t ON oat.jareferrer_amicoid=t.amico_id
                                        WHERE
                                         oat.jareferrer_self=1
                                        AND o.customer_email !=''
                                        AND t.chapter_id = '{$user_amico_id}'
                                        GROUP BY t.int_member_id";
                            } else {
                                $mail_query = "SELECT c.customers_firstname, c.customers_lastname, c.customers_email_address, t.int_member_id, t.amico_id
                                        FROM customers c, tbl_member t, orders o
                                        WHERE o.int_member_id = t.int_member_id AND t.int_customer_id = c.customers_id  AND t.chapter_id = '" . $user_amico_id . "' GROUP BY t.int_member_id";
                            }
                        }
                    }
                    if ($user_mtype == "m") {
                        if ($is_sms) {
                            $mail_query = "SELECT  DISTINCT SUBSTRING_INDEX( o.customers_name,  '\ ', 1  )  AS customers_firstname, SUBSTRING_INDEX( o.customers_name,  '\ ',  - 1  )  AS customers_lastname, o.customers_email_address, c.mobile_phone, c.operator_id, t.int_member_id, t.amico_id,mo.operator_address
                                     FROM orders o
                                     INNER  JOIN tbl_member t ON o.int_member_id = t.int_member_id
                                     INNER  JOIN customers c ON t.int_customer_id = c.customers_id
                                     RIGHT JOIN mobile_operators mo ON mo.id=c.operator_id
                                     WHERE o.customers_id =  '0' AND c.mobile_phone!='' AND c.operator_id!=0 AND o.int_member_id='{$member_id}'";
                        }
                        else {
                            $mail_query = "SELECT DISTINCT SUBSTRING_INDEX(o.customers_name, '\ ', 1) AS customers_firstname, SUBSTRING_INDEX(o.customers_name, '\ ', -1) AS customers_lastname,
                                    o.customers_email_address, t.int_member_id, t.amico_id
                                    FROM orders o
                                    INNER  JOIN tbl_member t ON o.int_member_id = t.int_member_id
                                    WHERE o.customers_id='0'
                                    AND o.int_member_id='{$member_id}'";
                        }
                    }

                    $results = mysqli_query($conn, $mail_query);
                    while ($rows = mysqli_fetch_assoc($results)) {
                        if(in_array($rows['int_member_id'], $unsubscribedMembers)) { continue; }

                        if ($is_sms) {
                            /*$mob_op = "select operator_address from mobile_operators where id=" . $rows['operator_id'];
                            $resmob = mysqli_query($conn,$mob_op);
                            list($operator_address) = mysqli_fetch_row($resmob);*/
                            $operator_address = $rows['operator_address'];
                            $mobile_phone = preg_replace('/[^\d]*/', '', $rows['mobile_phone']);
                            $this_to = $mobile_phone . $operator_address;
                            if ($rows['operator_id'] == '51') {

                                $customer_name = $rows['customers_firstname'] . " " . $rows['customers_lastname'];
                                send_mail_thru_gmail($from, $from, $this_to, "", $from, $customer_name, $subject, $letter);
                            }
                        }
                        else {
                            $this_to = $rows['customers_email_address'] . "";
                        }

                        $head = get_mail_header($from, $un);
                        $zag = "------------" . $un . "\nContent-Type:text/html;\n";
                        //$zag      .= "Content-Transfer-Encoding: 8bit\n\n$message\n\n";
                        if ($user_mtype == "e" || $user_mtype == "m") {
                            if ($isLink) {
                                $zag .= get_link_text($linktext, $rows['amico_id'], $rows['customers_password']);
                            }
                        }
                        $zag .= "\n\n$message\n\n";
                        if ($rows['operator_id'] !== '51') {
                            //mail($this_to, $subject, $zag, $head);
                            $mail_queue->add_to_queue($this_to, $subject, $zag, $head, $session_user, $rows['int_member_id'], $rows['amico_id'], $from);
                        }
                    }

                    $mail_queue->save_queue();

                    if( is_in_live() ) {
                        mail("john.amicojr@johnamico.com", $subject, $zag, $head);
                        mail("john.amicojr@gmail.com", $subject, $zag, $head);
                        //mail("omar@mvisolutions.com", $subject, $zag, $head);
                    }

                    break;

                case 'contact' :
                    if ($is_sms) {
                        $mail_query = "SELECT c.customers_firstname, c.customers_lastname, c.customers_email_address, t.int_member_id, t.amico_id, c.customers_password, c.mobile_phone, c.operator_id, mo.operator_address FROM customers c, tbl_member t, mobile_operators mo WHERE mo.id=c.operator_id AND t.int_customer_id = c.customers_id AND c.mobile_phone!='' AND c.operator_id!=0 AND t.ec_id = '" . $user_amico_id . "'";
                    }
                    else {
                        $mail_query = "SELECT c.customers_firstname, c.customers_lastname, c.customers_email_address, t.int_member_id, t.amico_id, c.customers_password FROM customers c, tbl_member t  WHERE t.int_customer_id = c.customers_id AND t.ec_id = '" . $user_amico_id . "'";
                    }

                    $results = mysqli_query($conn, $mail_query);
                    while ($rows = mysqli_fetch_assoc($results)) {
                        if(in_array($rows['int_member_id'], $unsubscribedMembers)) { continue; }

                        if ($is_sms) {
                            /*$mob_op = "select operator_address from mobile_operators where id=" . $rows['operator_id'];
                            $resmob = mysqli_query($conn,$mob_op);
                            list($operator_address) = mysqli_fetch_row($resmob);*/
                            $operator_address = $rows['operator_address'];
                            $mobile_phone = preg_replace('/[^\d]*/', '', $rows['mobile_phone']);
                            $this_to = $mobile_phone . $operator_address;
                            if ($rows['operator_id'] == '51') {

                                $customer_name = $rows['customers_firstname'] . " " . $rows['customers_lastname'];
                                send_mail_thru_gmail($from, $from, $this_to, "", $from, $customer_name, $subject, $letter);
                            }
                        }
                        else {
                            $this_to = $rows['customers_email_address'] . "";
                        }
                        $head = get_mail_header($from, $un);
                        $zag = "------------" . $un . "\nContent-Type:text/html;\n";

                        if ($user_mtype == "e" || $user_mtype == "m") {

                            if ($isLink) {
                                $zag .= get_link_text($linktext, $rows['amico_id'], $rows['customers_password']);
                            }
                        }

                        $zag .= "\n\n$message\n\n";
                        if ($rows['operator_id'] !== '51') {
                            //mail($this_to, $subject, $zag, $head);
                            $mail_queue->add_to_queue($this_to, $subject, $zag, $head, $session_user, $rows['int_member_id'], $rows['amico_id'], $from);
                        }

                    }

                    $mail_queue->save_queue();

                    if( is_in_live() ) {
                        mail("john.amicojr@johnamico.com", $subject, $zag, $head);
                        mail("john.amicojr@gmail.com", $subject, $zag, $head);
                        //mail("omar@mvisolutions.com", $subject, $zag, $head);
                    }


                    break;

                case 'both' :
                    // Down line
                    /*if ($is_sms) {
                        $mail_query = "SELECT c.customers_firstname, c.customers_lastname,c.customers_email_address, c.mobile_phone, c.operator_id, t.int_customer_id, t.int_member_id, t.amico_id, mo.operator_address
                            FROM customers c
                            LEFT JOIN tbl_member t ON t.int_customer_id=c.customers_id
                            RIGHT JOIN mobile_operators mo ON mo.id=c.operator_id
                            WHERE t.int_parent_id='$member_id'AND  c.mobile_phone!='' AND c.operator_id!=0";
                    }
                    else {
                        $mail_query = "SELECT c.customers_firstname, c.customers_lastname,c.customers_email_address, t.int_customer_id, t.int_member_id, t.amico_id
                            FROM customers c
                            LEFT JOIN tbl_member t ON t.int_customer_id=c.customers_id
                            WHERE t.int_parent_id='$member_id'";
                    }*/

                    $rslist = mysqli_query($conn, "select str_member_contact_list from tbl_member_contact_list where int_member_id={$member_id}");
                    list($contactlist) = mysqli_fetch_row($rslist);

                    $contactlist = substr($contactlist, 0, (strlen($contactlist) - 1));

                    $mail_query = "select c.customers_firstname,c.customers_lastname,c.customers_email_address, m.int_member_id, m.amico_id from customers c inner join tbl_member m on c.customers_id=m.int_customer_id where m.int_member_id in(" . $contactlist . ")";
                    if ($is_sms) {
                        $mail_query = "select c.customers_firstname,c.customers_lastname,c.customers_email_address, c.mobile_phone, c.operator_id, m.int_member_id, m.amico_id, mo.operator_address
                              from customers c inner join tbl_member m on c.customers_id=m.int_customer_id
                              RIGHT JOIN mobile_operators mo ON mo.id=c.operator_id
                              where m.int_member_id in(" . $contactlist . ") and c.mobile_phone!='' and c.operator_id!=0 ";
                    }

                    $results = mysqli_query($conn, $mail_query);
                    while ($rows = mysqli_fetch_row($results)) {
                        if(in_array($rows['int_member_id'], $unsubscribedMembers)) { continue; }

                        if ($is_sms) {
                            $operator_address = $rows['operator_address'];
                            $mobile_phone = preg_replace('/[^\d]*/', '', $rows['mobile_phone']);
                            $this_to = $mobile_phone . $operator_address;
                            if ($rows['operator_id'] == '51') {
                                $customer_name = $rows['customers_firstname'] . " " . $rows['customers_lastname'];
                                send_mail_thru_gmail($from, $from, $this_to, "", $from, $customer_name, $subject, $letter);
                            }
                        }
                        else {
                            $this_to = $rows['customers_firstname'] . " " . $rows['customers_lastname'];
                            $this_to .= " <" . $rows['customers_email_address'] . ">";
                        }
                        $head = get_mail_header($from, $un);
                        $zag = get_mail_html_body($message, $un);

                        if ($rows['operator_id'] !== '51') {
                            //mail($this_to, $subject, $zag, $head);
                            $mail_queue->add_to_queue($this_to, $subject, $zag, $head, $session_user, $rows['int_member_id'], $rows['amico_id'], $from);
                        }
                    }
                    //$mail_queue->save_queue();


                    // Other
                    if ($user_mtype == "e") {
                        if ($is_sms) {
                            if($useMagento) {
                                $mail_query = "SELECT DISTINCT o.customer_firstname AS customers_firstname, o.customer_lastname AS customers_lastname, o.customer_email AS customers_email_address, oa.telephone AS mobile_phone, NULL AS operator_id, t.int_member_id, t.amico_id, NULL AS operator_address, NULL AS operator_address
                                        FROM stws_sales_flat_order o
                                        INNER JOIN stws_sales_flat_order_address as oa ON o.billing_address_id=oa.entity_id
                                        INNER JOIN stws_amasty_amorderattr_order_attribute as oat ON o.entity_id=oat.order_id
                                        INNER JOIN tbl_member t ON oat.jareferrer_amicoid=t.amico_id
                                        WHERE
                                        oat.jareferrer_self=1
                                        AND o.customer_email !=''
                                        AND oa.telephone!=''
                                        AND t.ec_id ='{$user_amico_id}' ";
                            } else {
                                $mail_query = "SELECT c.customers_firstname, c.customers_lastname, c.customers_email_address, c.mobile_phone, c.operator_id, t.int_member_id, t.amico_id,mo.operator_address, mo.operator_address
                                        FROM customers c, tbl_member t, orders o, mobile_operators mo
                                        WHERE mo.id=c.operator_id AND  o.int_member_id = t.int_member_id AND c.mobile_phone!='' AND c.operator_id!=0 AND t.int_customer_id = c.customers_id AND t.ec_id = '" . $user_amico_id . "'";
                            }
                        }
                        else {
                            if( $useMagento ) {
                                $mail_query = "SELECT DISTINCT o.customer_firstname AS customers_firstname, o.customer_lastname AS customers_lastname, o.customer_email AS customers_email_address, t.int_member_id, t.amico_id
                                        FROM stws_sales_flat_order o
                                        INNER JOIN stws_sales_flat_order_address as oa ON o.billing_address_id=oa.entity_id
                                        INNER JOIN stws_amasty_amorderattr_order_attribute as oat ON o.entity_id=oat.order_id
                                        INNER JOIN tbl_member t ON oat.jareferrer_amicoid=t.amico_id
                                        WHERE
                                        oat.jareferrer_self=1
                                        AND o.customer_email !=''
                                        AND t.ec_id ='{$user_amico_id}' ";
                            } else {
                                $mail_query = "SELECT c.customers_firstname, c.customers_lastname, c.customers_email_address, t.int_member_id, t.amico_id
                                    FROM customers c, tbl_member t, orders o
                                    WHERE o.int_member_id = t.int_member_id AND t.int_customer_id = c.customers_id AND t.ec_id = '" . $user_amico_id . "'";
                            }
                        }
                    }
                    if ($user_mtype == "c") {
                        if ($is_sms) {
                            if( $useMagento ) {
                                $mail_query = "SELECT o.customer_firstname AS customers_firstname, o.customer_lastname AS customers_lastname, o.customer_email AS customers_email_address, oa.telephone AS mobile_phone, NULL AS operator_id, t.int_member_id, t.amico_id, NULL AS operator_address, NULL AS operator_address
                                        FROM stws_sales_flat_order o
                                        INNER JOIN stws_sales_flat_order_address as oa ON o.billing_address_id=oa.entity_id
                                        INNER JOIN stws_amasty_amorderattr_order_attribute as oat ON o.entity_id=oat.order_id
                                        INNER JOIN tbl_member t ON oat.jareferrer_amicoid=t.amico_id
                                        WHERE
                                         oat.jareferrer_self=1
                                        AND o.customer_email !=''
                                        AND oa.telephone!=''
                                        AND t.chapter_id = '{$user_amico_id}'
                                        GROUP BY t.int_member_id";
                            } else {
                                $mail_query = "SELECT c.customers_firstname, c.customers_lastname, c.customers_email_address,c.mobile_phone, c.operator_id, t.int_member_id, t.amico_id,mo.operator_address
                                    FROM customers c, tbl_member t, orders o, mobile_operators mo
                                    WHERE mo.id=c.operator_id AND o.int_member_id = t.int_member_id AND t.int_customer_id = c.customers_id  AND c.mobile_phone!='' AND c.operator_id!=0 AND t.chapter_id = '" . $user_amico_id . "' GROUP BY t.int_member_id";
                            }
                        }
                        else {
                            if( $useMagento ) {
                                $mail_query = "SELECT o.customer_firstname AS customers_firstname, o.customer_lastname AS customers_lastname, o.customer_email AS customers_email_address, t.int_member_id, t.amico_id
                                        FROM stws_sales_flat_order o
                                        INNER JOIN stws_sales_flat_order_address as oa ON o.billing_address_id=oa.entity_id
                                        INNER JOIN stws_amasty_amorderattr_order_attribute as oat ON o.entity_id=oat.order_id
                                        INNER JOIN tbl_member t ON oat.jareferrer_amicoid=t.amico_id
                                        WHERE
                                         oat.jareferrer_self=1
                                        AND o.customer_email !=''
                                        AND t.chapter_id = '{$user_amico_id}'
                                        GROUP BY t.int_member_id";
                            } else {
                                $mail_query = "SELECT c.customers_firstname, c.customers_lastname, c.customers_email_address, t.int_member_id, t.amico_id
                                        FROM customers c, tbl_member t, orders o
                                        WHERE o.int_member_id = t.int_member_id AND t.int_customer_id = c.customers_id  AND t.chapter_id = '" . $user_amico_id . "' GROUP BY t.int_member_id";
                            }
                        }
                    }
                    if ($user_mtype == "m") {
                        if ($is_sms) {
                            $mail_query = "SELECT  DISTINCT SUBSTRING_INDEX( o.customers_name,  '\ ', 1  )  AS customers_firstname, SUBSTRING_INDEX( o.customers_name,  '\ ',  - 1  )  AS customers_lastname, o.customers_email_address, c.mobile_phone, c.operator_id, t.int_member_id, t.amico_id,mo.operator_address
                                     FROM orders o
                                     INNER  JOIN tbl_member t ON o.int_member_id = t.int_member_id
                                     INNER  JOIN customers c ON t.int_customer_id = c.customers_id
                                     RIGHT JOIN mobile_operators mo ON mo.id=c.operator_id
                                     WHERE o.customers_id =  '0' AND c.mobile_phone!='' AND c.operator_id!=0 AND o.int_member_id='{$member_id}'";
                        }
                        else {
                            $mail_query = "SELECT DISTINCT SUBSTRING_INDEX(o.customers_name, '\ ', 1) AS customers_firstname, SUBSTRING_INDEX(o.customers_name, '\ ', -1) AS customers_lastname,
                                    o.customers_email_address, t.int_member_id, t.amico_id
                                    FROM orders o
                                    INNER  JOIN tbl_member t ON o.int_member_id = t.int_member_id
                                    WHERE o.customers_id='0'
                                    AND o.int_member_id='{$member_id}'";
                        }
                    }

                    $results = mysqli_query($conn, $mail_query);
                    while ($rows = mysqli_fetch_row($results)) {
                        if(in_array($rows['int_member_id'], $unsubscribedMembers)) { continue; }

                        if ($is_sms) {
                            $operator_address = $rows['operator_address'];
                            $mobile_phone = preg_replace('/[^\d]*/', '', $rows['mobile_phone']);
                            $this_to = $mobile_phone . $operator_address;
                            if ($rows['operator_id'] == '51') {
                                $customer_name = $rows['customers_firstname'] . " " . $rows['customers_lastname'];
                                send_mail_thru_gmail($from, $from, $this_to, "", $from, $customer_name, $subject, $letter);

                                $mail = new PHPMailer();
                                $mail->IsSMTP();
                            }
                        }
                        else {
                            $this_to = $rows['customers_firstname'] . " " . $rows['customers_lastname'];
                            $this_to .= " <" . $rows['customers_email_address'] . ">";
                        }
                        $head = get_mail_header($from, $un);
                        $zag = get_mail_html_body($message, $un);

                        if ($rows['operator_id'] !== '51') {
                            //$ml = mail($this_to, $subject, $zag, $head);
                            $mail_queue->add_to_queue($this_to, $subject, $zag, $head, $session_user, $rows['int_member_id'], $rows['amico_id'], $from);
                        }
                    }
                    $mail_queue->save_queue();

                    if( is_in_live() ) {
                        mail("john.amicojr@johnamico.com", $subject, $zag, $head);
                        mail("john.amicojr@gmail.com", $subject, $zag, $head);
                        //mail("omar@mvisolutions.com", $subject, $zag, $head);
                    }

                    break;

                case 'filtered' :
                    if (!empty($_SESSION['contact_organizer']['sql'])) {
                        $mail_query = mysqli_query($conn, $_SESSION['contact_organizer']['sql']);

                        while ($rows = mysqli_fetch_array($mail_query)) {
                            if(in_array($rows['int_member_id'], $unsubscribedMembers)) { continue; }

                            if ($is_sms) {
                                $mob_op = "select operator_address from mobile_operators where id=" . $rows['operator_id'];
                                $resmob = mysqli_query($conn, $mob_op);
                                list($operator_address) = mysqli_fetch_row($resmob);
                                $mobile_phone = preg_replace('/[^\d]*/', '', $rows['mobile_phone']);
                                $this_to = $mobile_phone . $operator_address;
                                if ($rows['operator_id'] == '51') {
                                    $customer_name = $rows['customers_firstname'] . " " . $rows['customers_lastname'];
                                    send_mail_thru_gmail($from, $from, $this_to, "", $from, $customer_name, $subject, $letter, "tls");
                                }
                            }
                            else {
                                $this_to = $rows['customers_firstname'] . " " . $rows['customers_lastname'];
                                $this_to .= " <" . $rows['customers_email_address'] . ">";
                                //$this_to .= " <ownrr2@mailinator.com>";
                            }
                            $head = get_mail_header($from, $un);
                            $zag = "------------" . $un . "\nContent-Type:text/html;\n";

                            if ($user_mtype == "e" || $user_mtype == "m") {
                                if ($_POST['islink']) {
                                    $zag .= get_link_text($linktext, $rows['amico_id'], $rows['customers_password']);
                                }
                            }

                            $zag .= "\n\n$message\n\n";


                            if ($rows['operator_id'] != '51') {
                                //(mail($this_to, $subject, $zag, $head));
                                $mail_queue->add_to_queue($this_to, $subject, $zag, $head, $session_user, $rows['int_member_id'], $rows['amico_id'], $from);

                            }
                        }
                        $mail_queue->save_queue();

                        if( is_in_live() ) {
                            mail("john.amicojr@johnamico.com", $subject, $zag, $head);
                            mail("john.amicojr@gmail.com", $subject, $zag, $head);
                            //mail("omar@mvisolutions.com", $subject, $zag, $head);
                        }
                    }
                    break;
            }

            $email_sending_success = true;
        }
    } else {

    }
}


if(!$is_edit && !$is_add && !empty($member_id)) {

    $limit = 50;
    $page = ((!empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) ? $_REQUEST['page'] : 1);

    $limit_start = ($page * $limit) - $limit;
    $limit_end = ($page * $limit);

    $name_types = array(1=>'customers_firstname', 2=>'customers_lastname');

    $search_itemid = !empty($_REQUEST['searchitemid']) ? filter_var($_REQUEST['searchitemid'], FILTER_SANITIZE_STRING) : '';
    $search_lastorderdate = !empty($_REQUEST['lastorderdate']) ? filter_var($_REQUEST['lastorderdate'], FILTER_SANITIZE_STRING) : '';
    $nametype = (!empty($_REQUEST['nametype']) && !empty($name_types[ $_REQUEST['nametype'] ]) ) ? $_REQUEST['nametype'] : '';
    $search_name = !empty($_REQUEST['searchname']) ? filter_var($_REQUEST['searchname'], FILTER_SANITIZE_STRING) : '';
    $search_city = !empty($_REQUEST['searchcity']) ? filter_var($_REQUEST['searchcity'], FILTER_SANITIZE_STRING) : '';
    $search_state = !empty($_REQUEST['searchstate']) ? filter_var($_REQUEST['searchstate'], FILTER_SANITIZE_STRING) : '';
    $name_filter_first = !empty($_REQUEST['name_filter_first']) ? filter_var($_REQUEST['name_filter_first'], FILTER_SANITIZE_STRING) : '';
    $name_filter_last = !empty($_REQUEST['name_filter_last']) ? filter_var($_REQUEST['name_filter_last'], FILTER_SANITIZE_STRING) : '';

    $tableColumnsListSQL = "SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='".DB."' AND `COLUMN_NAME` LIKE 'ytd20%' AND `TABLE_NAME`='tbl_member'";
    $tableColumnsListQuery = mysqli_query($conn, $tableColumnsListSQL);
    while ($row = mysqli_fetch_assoc($tableColumnsListQuery)) {
        $tableColumnsList[] = $row['COLUMN_NAME'];
        $tableColumnsList__onlyYears[] = (int)str_replace('ytd', '', $row['COLUMN_NAME']);
    }

    //echo '<pre>'; print_r($tableColumnsList__onlyYears); die();


    //$ytdColumns = range( date('Y')-10, date('Y')-1 );
    $ytdColumns = array();
    $count = 0;
    for($i=(date('Y')-1); $i>(date('Y')-10); $i--) {
        if( $count == 4 ) {
            break;
        }
        if( in_array($i, $tableColumnsList__onlyYears) ) {
            $ytdColumns[] = $i;
            $count++;
        }
    }
    sort($ytdColumns);

    $sortingColumns = array(
        'info' => 'int_member_id',
        'memberid' => 'amico_id',
        'name' => 'customer_fullname',
        'email' => 'customers_email_address',
        'a' => 'ec_calendar_id',
        'c' => 'contest_member_id',
        'pc' => 'int_customer_id',
        'lod' => '_lod_',
        'p' => '_ppp_',
        'as' => '_as_',
        'as_c' => '_as_c_',
        'mtd' => '_mtd_',
        'ytd' => '_ytd_',
    );


    $search_lastorderdate_time = strtotime($search_lastorderdate);

    $conditions = $sortby = array();

    //debug(true, true, $ytdColumns, $tableColumnsList, $tableColumnsList__onlyYears);
    //debug(true, false, $status_filter, (!in_array((string)$_REQUEST['params']['status_filter'], array('1','0'), true)), $_POST);

    $sql = "select m.int_member_id,
            m.int_member_id as contest_member_id,
            m.int_member_id as invoice_report_id,
            m.amico_id , m.amico_id as `level`,
            m.amico_id as quick_order_id,
            m.amico_id as nonmember_orders_id,
            m.int_designation_id, m.int_customer_id,
            m.int_member_id AS ec_calendar_id,
            '' AS appointments_date,
            m.int_customer_id AS PC,
            c.customers_firstname,c.customers_lastname,concat(c.customers_lastname, ', ', c.customers_firstname) as customer_fullname,
            c.customers_email_address,
            m.ppp as _ppp_,
            m.lod as _lod_,
            m.as as _as_,
            m.as_c as _as_c_,
            m.mtd as _mtd_,
            m.ytd as _ytd_,
            m.order_history_json as _ohj_,
    ";

    for($i=0; $i<count($ytdColumns); $i++) {
        $columnName = "ytd{$ytdColumns[$i]}";

        if( in_array($columnName, $tableColumnsList) ) {
            $sortingColumns[$ytdColumns[$i]] = "_{$columnName}_";

            $ytds[] = " m.$columnName as _{$columnName}_ ";
        }
    }

    if( !empty($ytds) ) {
        $sql .= implode(', '.PHP_EOL, $ytds);
    }

    if ( !empty($search_itemid) ) {
        $sql .= " FROM bw_invoices o, bw_invoice_line_items l, customers c ";

        $conditions[] = "m.amico_id=o.ID AND o.SKOEInvoice=l.FKEntity";
        $conditions[] = "l.ID='$search_itemid'";
    } else {
        $sql .= " FROM customers c ";
    }

    if ( !empty($search_lastorderdate_time) ) {
        $sql .= ", bw_invoices o ";

        $conditions[] = "m.amico_id=o.ID";
        $conditions[] = "m.lod='$search_lastorderdate'";
    }

    if ($user_mtype == 'e') {
        $sql .= " inner join tbl_member m ON c.customers_id=m.int_customer_id
        left outer join address_book a on c.customers_id=a.customers_id
        ";

        $conditions[] = " m.ec_id='$user_amico_id' ";
        $conditions[] = "m.bit_active=1";
    }
    elseif ($user_mtype == 'c') {
        $sql .= " inner join tbl_member m ON c.customers_id=m.int_customer_id
        left outer join address_book a on c.customers_id=a.customers_id
        ";

        $conditions[] = "m.chapter_id='$user_amico_id'";
        $conditions[] = "m.bit_active=1";
    }
    else {
        $sql .= "
            inner join tbl_member m ON c.customers_id=m.int_customer_id
            left outer join address_book a on c.customers_id=a.customers_id
        ";

        //$conditions[] = "m.int_member_id='$member_id'";
        $conditions[] = "m.bit_active=1";

        $rslist = mysqli_query($conn, "select str_member_contact_list from tbl_member_contact_list where int_member_id='$member_id'");
        list($contactlist) = mysqli_fetch_row($rslist);
        $contactlist = implode(',', array_filter(explode(',', $contactlist)) );
        //$contactlist = substr($contactlist, 0, (strlen($contactlist) - 1));

        if( !empty($contactlist) ) {
            $conditions[] = "m.int_member_id IN ($contactlist)";
        } else {
            $conditions[] = "m.int_member_id IN ()";
        }
    }



    /*if (!empty($nametype) && !empty($search_name) ) {
        if($nametype == 1) {
            $conditions[] = "c.customers_firstname like ('$search_name%') ";
        }
        elseif($nametype == 2) {
            $conditions[] = "c.customers_lastname like ('$search_name%') ";
        }

    }*/

    if (!empty($name_filter_first) ) {
        $conditions[] = "c.customers_firstname like ('$name_filter_first%') ";
    }
    if (!empty($name_filter_last) ) {
        $conditions[] = "c.customers_lastname like ('$name_filter_last%') ";
    }
    if ( !empty($search_city) ) $conditions[] = "a.entry_city like('$search_city%') ";
    if ( !empty($search_state) ) $conditions[] = "a.entry_zone_id ='$search_state' ";



    $sortby = !empty($_REQUEST['sby']) ? filter_var($_REQUEST['sby'], FILTER_SANITIZE_STRING) : end($sortingColumns);
    $sortorder = !empty($_REQUEST['sorder']) ? filter_var( strtolower($_REQUEST['sorder']), FILTER_SANITIZE_STRING) : 'desc';

    $nextSortOrder = ( $sortorder == 'desc' ) ? 'asc' : 'desc';

    if( in_array($sortby,  array_keys($sortingColumns) ) && in_array( strtolower($sortorder), array('asc', 'desc') ) ) {
        $sortBySql = " ORDER BY {$sortingColumns[$sortby]} ".strtoupper($sortorder)." ";
    } else {
        $sortBySql = " ORDER BY ".end($sortingColumns)." DESC ";
    }

    $sortbySqlAdd = '';
    $group_by = "GROUP BY m.amico_id";
    $sortbySqlAdd .= $group_by . $sortBySql;



    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    $sql .= " $sortbySqlAdd ";

    if( empty($contactlist) ) {
        //$sql = '';
    }


    $_SESSION['contact_organizer']['sql'] = $sql;
    $_SESSION['contact_organizer']['conditions'] = $conditions;
    $_SESSION['contact_organizer']['post'] = $_POST;

    //debug(false, false, $_POST, $conditions, $sql );


    $field_details = array(
        'int_member_id' => array(
            'link' => '#',
            'id_field' => 'int_member_id',
            'name' => 'Info',
            'button' => false,
            'attributes' => array(
                'onclick' => "window.open('extras.php?".( ($user_mtype == 'm') ? 't=m&' : '' )."mid=ID_FIELD_VALUE','info', 'scrollbars=1,resizable=1,width=".( ($user_mtype == 'm') ? '850' : '1200' ).",height=".( ($user_mtype == 'm') ? '910' : '1050' )."'); return false;",
                //'class' => array('CONDITION__HAS_PROVIDED_VALUE' => 'red-text'),
                'class' => array('member_info_button'),
            ),
            'text_to_display' => '<i class="fa fa-info-circle fa-2" aria-hidden="true"></i>'
        ),
        'amico_id' => 'MemberID',
        'level' => array(
            'field_type' => 'text_from_callback',
            'name' => 'level',
            'value' => array(
                'function' => 'get_level',
                'params' => array($session_user, 'amico_id'),
                'params_field' => array('', 'amico_id'),
                'check_function' => 'user_is_affiliate',
                'check_function_params' => array($session_user),
                'check_function_params_field' => array(),
            ),
        ),
        //'customer_fullname' => 'Name',
        'customer_fullname' => array(
            'field_type' => 'text_from_callback',
            'name' => 'Name',
            'value' => array(
                'function' => 'get_amico_name_with_autoship_vip_check',
                'params' => array($session_user, 'amico_id', 'int_member_id', 'customer_fullname', $autoship_enabled),
                'params_field' => array('', 'amico_id', 'int_member_id', 'customer_fullname', ''),
            ),
        ),
        // 'contest_member_id' => array(
        //     'link' => '#',
        //     'id_field' => 'contest_member_id',
        //     'name' => 'C',
        //     'button' => false,
        //     'attributes' => array(
        //         //'onclick' => "window.open('contest.php?memberid=ID_FIELD_VALUE','contest', 'crollbars=no,width=450,height=300'); return false;",
        //     ),
        // ),
        '_as_c_'=> 'AS_C',
    );

    if( in_array($user_mtype, array('e', 'c')) ) {
        unset($field_details['level']);
        $field_details += array(
            'ec_calendar_id' => array(
                'link' => '#',
                'id_field' => 'ec_calendar_id',
                'name' => 'A',
                'button' => false,
                'attributes' => array(
                    //'onclick' => "window.open('calender.php?popup=1&memberid=ID_FIELD_VALUE','calendar', 'scrollbars=yes,width=995,height=600'); return false;",
                    'onclick' => "window.open('task_list.php?popup=1&mem_id=ID_FIELD_VALUE','tasklist', 'scrollbars=yes,width=950,height=600'); return false;",
                ),
            ),
            'appointments_date' => '',
        );
    }

    $field_details += array (
        'int_customer_id' => array(
            'link' => '#',
            'id_field' => 'int_customer_id',
            'name' => 'PC',
            'button' => false,
            'attributes' => array(
                'onclick' => "window.open('add_comments.php?b=&mlm_id=ID_FIELD_VALUE','comments', 'scrollbars=1,width=600,height=850, sizable=yes'); return false;",
            ),
        ),
        '_ppp_' => 'P',
        '_lod_' => 'LOD',
        '_as_'  => 'AS',
        '_mtd_' => 'MTD',
        '_ytd_' => 'YTD',
    );

    $field_details_ytds = array();
    for($i=0; $i<count($ytdColumns); $i++) {
        $columnName = "ytd{$ytdColumns[$i]}";

        if( in_array($columnName, $tableColumnsList) ) {
            $field_details_ytds["_{$columnName}_"] = $ytdColumns[$i];
        }
    }

    $field_details += $field_details_ytds;

    $field_details += array(
        'customers_email_address'=>array(
            'link' => 'mailto:ID_FIELD_VALUE',
            'id_field' => 'customers_email_address',
            'name' => 'Email',
            'text_to_display' => '<i class="fa fa-envelope fa-2" aria-hidden="true"></i>',
        ),
        'invoice_report_id'=>array(
            'link' => '#',
            'id_field' => 'invoice_report_id',
            'name' => 'Orders',
            'button' => false,
            'attributes' => array(
                'onclick' => "window.open('invoice_report_new.php?memberid=ID_FIELD_VALUE','orders', 'scrollbars=yes,width=800,height=700'); return false;",
            ),
            'text_to_display' => '<i class="fa fa-bar-chart fa-2" aria-hidden="true"></i>',
        ),
        /*'quick_order_id' => array(
            'link' => '#',
            'id_field' => 'quick_order_id',
            'name' => 'Quick Order',
            'button' => false,
            'attributes' => array(
                'onclick' => "window.open('".base_shop_url()."quick-order?memberid=ID_FIELD_VALUE','quickorder', 'scrollbars=yes,width=950,height=600'); return false;",
            ),
            'text_to_display' => '<i class="fa fa-shopping-cart fa-2" aria-hidden="true"></i>',
        ),*/
        'nonmember_orders_id' => array(
            'link' => '#',
            'id_field' => 'nonmember_orders_id',
            'name' => 'Web Orders',
            'button' => false,
            'attributes' => array(
                'onclick' => "window.open('non_member_orders.php?memberid=ID_FIELD_VALUE','nonmemberorders', 'scrollbars=yes,width=1250,height=700'); return false;",
            ),
            'text_to_display' => '<i class="fa fa-bars fa-2" aria-hidden="true"></i>',
        ),

    );

    $id_field = 'int_schedule_list_id';

    $action_page__id_handler = 'noteid';


    if( !empty($sql) ) {
        //$query_pag_data = " $condition LIMIT $start, $per_page";
        $data_num_query = mysqli_query($conn, $sql);

        //mysqli_store_result($conn);
        $numrows = mysqli_num_rows($data_num_query);

        //echo $sql;

        $sql .= " LIMIT $limit OFFSET $limit_start ";
        //echo $sql;
        $data_query = mysqli_query($conn, $sql);
    } else {
        $data_num_query = $data_query = null;
        $numrows = 0;
    }

}

?>
    <script>
        collapse_left_sidebar_func(true, true);
        jQuery(document).ready(function () {
            $("#sms").click(function (event) {
                if (!$("#sms").is(":checked")) {
                    $(".sms_message_field_wrapper").addClass('hide').removeClass('show');
                    $(".message_field_wrapper").addClass('show').removeClass('hide');
                }
                else {
                    $(".sms_message_field_wrapper").addClass('show').removeClass('hide');
                    $(".message_field_wrapper").addClass('hide').removeClass('show');
                }
            });

            $('table.members_list_table tr').click(function () {
                var tr = $(this).closest('tr');

                $('table.members_list_table tr').removeClass('active');
                tr.addClass('active');
            });

            $('table.members_list_table tr').hover(function () {
                var tr = $(this).closest('tr');

                $('table.members_list_table tr').removeClass('hover');
                tr.addClass('hover');
            });
        });
    </script>

    <div role="main" class="content-body">
        <header class="page-header">
            <h2><?php echo $page_name; ?></h2>

            <div class="right-wrapper pull-right">
                <ol class="breadcrumbs">
                    <li>
                        <a href="<?php echo base_admin_url(); ?>">
                            <i class="fa fa-home"></i>
                        </a>
                    </li>
                    <li><span><?php echo $page_name; ?></span></li>
                </ol>


                <a class="sidebar-right-toggle"></a>
            </div>
        </header>

        <div class="row ">
            <?php if(!$is_edit && !$is_add): ?>
                <section class="panel ">
                    <div class="col-lg-4 col-md-4 col-xs-12 filter_wrapper">
                        <form name="show_commissions" class="form form-validate form-bordered" action="" method="get">
                            <header class="panel-heading">
                                <h2 class="panel-title text-center">Filter <?php echo $member_type_name_plural; ?></h2>
                            </header>
                            <div class="panel-body ">
                                <div class="row">
                                    <div class="col-xs-12 centering">
                                        <div class="panel-body ">
                                            <!--<div class="row form-group ">
                                                <label class="col-md-4 control-label" for="searchname">Search Name</label>
                                                <div class="col-md-8">
                                                    <div class="row">
                                                        <div class="col-xs-12">
                                                            <input type="text"  name="searchname" class="form-control" id="searchname" value="<?php /*echo ( !empty($search_name) ? $search_name : '' ); */?>" />
                                                        </div>
                                                        <div class="col-md-12 form-inline">
                                                            <div class="row">
                                                                <div class="radio col-xs-6">
                                                                    <input type="radio" id="searchorder_first" name="nametype" value="1" <?php /*echo ( ( ( !empty($nametype) && ($nametype == 1) ) || empty($nametype) ) ? 'checked' : '' ); */?> >
                                                                    <label for="searchorder_first">First Name</label>
                                                                </div>
                                                                <div class="radio col-xs-6">
                                                                    <input type="radio" id="searchorder_last" name="nametype" value="2" <?php /*echo ( !empty($nametype) && ($nametype == 2) ? 'checked' : '' ); */?> >
                                                                    <label for="searchorder_last">Last Name</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>-->
                                            <div class="row form-group ">
                                                <label class="col-md-4 control-label" for="name_filter_first">Name Filter:</label>
                                                <div class="col-md-8">
                                                    <div class="row">
                                                        <div class="col-xs-6">
                                                            <input type="text" class="form-control" name="name_filter_first" id="name_filter_first" value="<?php echo (!empty($name_filter_first) ? $name_filter_first : ''); ?>" placeholder="First Name"/>
                                                        </div>
                                                        <div class="col-xs-6">
                                                            <input type="text" class="form-control" name="name_filter_last" id="name_filter_last" value="<?php echo (!empty($name_filter_last) ? $name_filter_last : ''); ?>" placeholder="Last Name"/>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row form-group ">
                                                <label class="col-md-4 control-label" for="searchcity">Search City</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" id="searchcity" name="searchcity" value="<?php echo ( !empty($search_city) ? $search_city : '' ); ?>">
                                                </div>
                                            </div>
                                            <div class="row form-group ">
                                                <label class="col-md-4 control-label" for="searchstate">Search State</label>
                                                <div class="col-md-8">
                                                    <select name="searchstate" id="searchstate" class="form-control">
                                                        <option value="">-- All states --</option>
                                                        <?php
                                                        $rssearch = mysqli_query($conn,"select zone_id,zone_name, zone_country_id from zones WHERE zone_country_id='223'");
                                                        while (list($zoneid, $zonename) = mysqli_fetch_row($rssearch)) {
                                                            ?>
                                                            <option value="<?= $zoneid ?>" <? if ( !empty($search_state) && $search_state == $zoneid) echo " selected"; ?>><?= $zonename ?></option>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row form-group ">
                                                <label class="col-md-4 control-label" for="lastorderdate">Last Order Date</label>
                                                <div class="col-md-8">
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                        <input data-plugin-datepicker type="text" class="form-control" id="lastorderdate" name="lastorderdate" value="<?php echo ( !empty($search_lastorderdate) ? $search_lastorderdate : '' ); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row form-group ">
                                                <label class="col-md-4 control-label" for="searchitemid">Search Item ID</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" id="searchitemid" name="searchitemid" value="<?php echo ( !empty($search_itemid) ? $search_itemid : '' ); ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <footer class="panel-footer text-center">
                                <input type="hidden" name="goto" value="filter">
                                <input type="submit" value="Filter" name="filter" />
                                <button type="button" class="clear btn btn-warning btn-primary" onclick="window.location = window.location.href;">Clear</button>
                            </footer>
                        </form>
                    </div>
                    <div class="m-lg visible-sm visible-xs">&nbsp;</div>
                    <div class="col-lg-8 col-md-8 col-xs-12 email_wrapper">
                        <form name="show_commissions" class="form form-validate form-bordered" action="" method="post">
                            <header class="panel-heading">
                                <h2 class="panel-title text-center">Send Email to <?php echo $member_type_name_plural; ?></h2>
                            </header>
                            <div class="panel-body ">
                                <div class="row">
                                    <div class="col-xs-12 centering">
                                        <div class="panel-body ">
                                            <?php if ( !empty($email_sending_success) ) : ?>
                                                <div class="row">
                                                    <div class="message alert alert-success">
                                                        Success: Email requests are added to the queue. Queued emails will be start sending in 15 minutes. You will receive a notification when all requests are processed. This may take a while.
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ( !empty($error_message) ) : ?>
                                                <div class="row">
                                                    <div class="message alert alert-danger">
                                                        <ul><li><?php echo implode('</li><li>', $error_message);?></li></ul>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <div class="row form-group email_to_field_wrapper">
                                                <label class="col-md-4 control-label" for="email_to">Send Email To</label>
                                                <div class="col-md-8">
                                                    <select name="email_to" id="email_to" class="form-control">
                                                        <? if ( in_array($user_mtype , array('m', 'c')) ) { ?>
                                                            <option value="downline" <?php echo ( !empty($email_to) && ($email_to == 'downline') ? 'selected' : '' ); ?> >My Downline</option>
                                                        <? } ?>
                                                        <? if ($user_mtype == 'e') { ?>
                                                            <option value="contact" <?php echo ( !empty($email_to) && ($email_to == 'contact') ? 'selected' : '' ); ?>>Contact List</option>
                                                        <? } ?>
                                                        <option value="other" <?php echo ( !empty($email_to) && ($email_to == 'other') ? 'selected' : '' ); ?>>Customers</option>
                                                        <? if ($user_mtype == 'm') { ?>
                                                            <option value="both" <?php echo ( !empty($email_to) && ($email_to == 'both') ? 'selected' : '' ); ?>>Both</option>
                                                        <? } ?>
                                                        <option value="filtered" <?php echo ( !empty($email_to) && ($email_to == 'filtered') ? 'selected' : '' ); ?>>Filtered Users</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row form-group subject_field_wrapper <?php echo !empty($error_message['subject']) ? 'has-error' : '';?>">
                                                <label class="col-md-4 control-label" for="subject">Subject</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" id="subject" name="subject" value="<?php echo ( !empty($subject) ? $subject : '' ); ?>">
                                                </div>
                                            </div>
                                            <div class="row form-group sms_field_wrapper">
                                                <label class="col-md-4 control-label" for="sms">Send as sms</label>
                                                <div class="col-md-8">
                                                    <input type="checkbox" name="sms" id="sms" value="1" <?php echo ( !empty($is_sms) ? 'checked="checked"' : '' ); ?>>
                                                </div>
                                            </div>
                                            <div class="row form-group from_field_wrapper">
                                                <label class="col-md-4 control-label" for="from">From</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" id="from" name="from" value="<?php echo ( !empty($from) ? $from : ( !empty($user_email) ? strtolower($user_email) : '' ) ); ?>">
                                                </div>
                                            </div>
                                            <div class="row form-group message_field_wrapper <?php echo !empty($error_message['message']) ? 'has-error' : '';?>">
                                                <label class="col-md-4 control-label" for="message">Message</label>
                                                <div class="col-md-8">
                                                    <textarea class="form-control" id="message" name="message"><?php echo !empty($message) ? $message : ''; ?></textarea>

                                                    <script src="//cdn.ckeditor.com/4.5.11/full-all/ckeditor.js"></script>
                                                    <!--<script> CKEDITOR.replace( 'message', { skin: 'kama', height: '150px' } ); </script>-->
                                                    <!--<script> CKEDITOR.replace( 'news', { skin: 'kama' } ); </script>-->
                                                    <script>//<![CDATA[
                                                        CKEDITOR.replace('message', { "filebrowserBrowseUrl": "/jaadmin/../ckeditor/ckfinder/ckfinder.html", "filebrowserImageBrowseUrl": "/jaadmin/../ckeditor/ckfinder/ckfinder.html?type=Images", "filebrowserFlashBrowseUrl": "/jaadmin/../ckeditor/ckfinder/ckfinder.html?type=Flash", "filebrowserUploadUrl": "/jaadmin/../ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files", "filebrowserImageUploadUrl": "/jaadmin/../ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images", "filebrowserFlashUploadUrl": "/jaadmin/../ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash", skin: 'kama', height: '150px' });
                                                        //]]>
                                                    </script>

                                                </div>
                                            </div>
                                            <div class="row form-group sms_message_field_wrapper hide <?php echo !empty($error_message['sms_msg']) ? 'has-error' : '';?>">
                                                <label class="col-md-4 control-label" for="sms_message">SMS Message</label>
                                                <div class="col-md-8">
                                                    <textarea class="form-control" id="sms_message" rows="5" cols="80" maxlength="100" name="sms_message" placeholder="<?php echo SMS_MESSAGE_BOX_PLACEHOLDER;?>"><?php echo !empty($sms_message) ? $sms_message : ''; ?></textarea>
                                                </div>
                                            </div>
                                            <?php if ($user_mtype == 'e' || $user_mtype == 'm') { ?>
                                                <div class="row form-group islink_field_wrapper">
                                                    <label class="col-md-4 control-label" for="islink">Include Link?</label>
                                                    <div class="col-md-8">
                                                        <input type="checkbox" name="islink" id="islink" value="1" <?php echo ( !empty($isLink) ? 'checked="checked"' : '' ); ?>>
                                                        <label for="islink">Yes</label>
                                                    </div>
                                                </div>
                                                <div class="row form-group linktext_field_wrapper">
                                                    <label class="col-md-4 control-label" for="linktext">Link Text</label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" id="linktext" name="linktext" value="<?php echo ( !empty($linktext) ? $linktext : '' ); ?>">
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <footer class="panel-footer text-center">
                                <input type="hidden" name="goto" value="send_email">
                                <input type="submit" value="Send Email" name="send_email" />
                            </footer>
                        </form>
                    </div>
                </section>
                <div class="clearfix"></div>
                <div class="m-lg ">&nbsp;</div>

                <section class="panel contact_list_panel">
                    <div class="col-xs-12 centering ">
                        <header class="panel-heading">
                            <h2 class="panel-title text-center">List of <?php echo $member_type_name_plural; ?></h2>
                        </header>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-12 links ">
                                    <ul>
                                        <?php if(in_array($user_mtype, array('m', 'e'))) { ?>
                                            <li><a href="<?php echo base_member_url()?>/ecco_report.php" target="_blank">ECCO Report</a></li>
                                            <li><a href="<?php echo base_member_url()?>/sit_report.php" target="_blank">SIT Report</a></li>
                                            <li><a href="#" onclick="window.open('<?php echo base_member_url()?>/view_pending.php?member_id=<?php echo $member_id?>', 'pen', 'height=400, width=600, scrollbars=yes, status=no, location=no'); return false;" target="_blank">View Pending Tasks</a></li>
                                            <li><a href="<?php echo base_shop_url()?>/" target="_blank">View Shopping Cart</a></li>
                                        <?php } ?>
                                        <?php if($user_mtype == 'c') { ?>
                                            <li><a href="<?php echo base_member_url()?>/cpco_report.php" target="_blank">CPCO Report</a></li>
                                            <li><a href="<?php echo base_member_url()?>/_download.php">Download Members List</a></li>
                                            <li><a href="<?php echo base_member_url()?>/mail_labels.php?mode=members" target="_blank">Mail Labels Members List</a></li>
                                            <li><a href="<?php echo base_member_url()?>/_download2.php">Download Population List</a></li>
                                            <li><a href="<?php echo base_member_url()?>/mail_labels.php?mode=salons" target="_blank">Mail Labels Population List</a></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <?php if(in_array($user_mtype, array('m', 'e'))) { ?>
                                    <div class="col-xs-12 contact_rate text-center">
                                        <?php
                                        $contact_rate = "N/A";

                                        if ($user_mtype == 'm') {
                                            $query_total_sql = "SELECT int_member_id FROM tbl_member WHERE int_member_id in($contactlist)";
                                            $query_total = mysqli_query($conn, $query_total_sql);

                                            $query_red_sql = "SELECT customers.customers_telephone, customers.customers_telephone1, customers.customers_telephone2 FROM tbl_member, customers  WHERE tbl_member.int_customer_id=customers.customers_id AND tbl_member.int_member_id in($contactlist) GROUP BY tbl_member.int_member_id";
                                            $query_red = mysqli_query($conn, $query_red_sql);
                                        }
                                        else {
                                            $query_total_sql = "SELECT int_member_id FROM tbl_member WHERE ec_id='$user_amico_id'";
                                            $query_total = mysqli_query($conn, $query_total_sql);

                                            $query_red_sql = "SELECT customers.customers_telephone, customers.customers_telephone1, customers.customers_telephone2 FROM tbl_member, customers WHERE tbl_member.int_customer_id=customers.customers_id AND tbl_member.ec_id='$user_amico_id' GROUP BY tbl_member.int_member_id";
                                            $query_red = mysqli_query($conn, $query_red_sql);
                                        }

                                        $count = 0;

                                        while ($f = mysqli_fetch_array($query_red)) {

                                            if( true ) {

                                                $member_phone = '1' . str_replace(array(" ", ')', '(', '-', '.'), "", $f['customers_telephone']);
                                                $member_phone1 = '1' . str_replace(array(" ", ')', '(', '-', '.'), "", $f['customers_telephone1']);
                                                $member_phone2 = '1' . str_replace(array(" ", ')', '(', '-', '.'), "", $f['customers_telephone2']);

                                                $member_phone = addslashes($member_phone);
                                                $member_phone1 = addslashes($member_phone1);
                                                $member_phone2 = addslashes($member_phone2);

                                                $sql_test = "SELECT id FROM tbl_calls WHERE (calldestination='$member_phone' OR calldestination='$member_phone1' OR calldestination='$member_phone2') AND billabletime>'$billabletime' AND calldate<='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 31, date("Y"))) . "' AND calldate>='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 1, date("Y"))) . "'";

                                            } else {
                                                $member_phone = str_replace(array(" ", ')', '(', '-', '.'), "", $f['customers_telephone']);
                                                $member_phone1 = str_replace(array(" ", ')', '(', '-', '.'), "", $f['customers_telephone1']);
                                                $member_phone2 = str_replace(array(" ", ')', '(', '-', '.'), "", $f['customers_telephone2']);

                                                $member_phone = addslashes($member_phone);
                                                $member_phone1 = addslashes($member_phone1);
                                                $member_phone2 = addslashes($member_phone2);

                                                if( !empty($member_phone) ) { $calldestinationArr[] = "calldestination='1$member_phone'"; }
                                                if( !empty($member_phone1) ) { $calldestinationArr[] = "calldestination='1$member_phone1'"; }
                                                if( !empty($member_phone2) ) { $calldestinationArr[] = "calldestination='1$member_phone2'"; }

                                                if( !empty($calldestinationArr) ) {
                                                    $calldestination = "(". implode(' OR ', $calldestinationArr) . ") AND ";
                                                }

                                                $sql_test = "SELECT id FROM tbl_calls WHERE $calldestination billabletime>'$billabletime' AND calldate<='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 31, date("Y"))) . "' AND calldate>='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 1, date("Y"))) . "'";
                                            }

                                            $query_test = mysqli_query($conn, $sql_test);
                                            if (mysqli_num_rows($query_test) > 0) {
                                                $count++;
                                            }
                                        }

                                        if ($user_mtype == 'm')
                                        {
                                            $query_yellow_sql = "SELECT tbl_member.amico_id  FROM bw_invoices, bw_invoice_line_items, tbl_member WHERE tbl_member.int_member_id in($contactlist) AND bw_invoices.ID=tbl_member.amico_id AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity AND bw_invoices.OrderDate>='" . date("Y") . "-" . date("m") . "-01 00:00:00' GROUP BY tbl_member.amico_id";

                                            $query_yellow = mysqli_query($conn, $query_yellow_sql);
                                        }
                                        else {
                                            $query_yellow_sql = "SELECT tbl_member.amico_id  FROM bw_invoices, bw_invoice_line_items, tbl_member WHERE tbl_member.ec_id='$user_amico_id' AND bw_invoices.ID=tbl_member.amico_id AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity AND bw_invoices.OrderDate>='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 1, date("Y"))) . "' AND bw_invoices.OrderDate<='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 31, date("Y"))) . "' GROUP BY tbl_member.amico_id";

                                            $query_yellow = mysqli_query($conn,$query_yellow_sql);
                                        }

                                        if (mysqli_num_rows($query_total) > 0) {
                                            $contact_rate = '%' . round((($count + mysqli_num_rows($query_yellow)) / mysqli_num_rows($query_total)) * 100, 2);
                                        }

                                        ?>
                                        Contact rate: <strong style="color: green;"><?php echo $contact_rate; ?></strong>
                                    </div>
                                <?php } ?>
                                <div class="clearfix"></div>
                                <div class="m-lg ">&nbsp;</div>
                            </div>
                            <?php $display_data_from_data_page = false; require_once("../$admin_path/display_members_data.php"); ?>

                            <div class="row">
                                <?php if(!$no_data) : ?>
                                    <div class="col-xs-12 data_pagination_wrapper">
                                        <div class="col-sm-12 col-md-12 col-lg-4 pagination_info_wrapper">
                                            <div class="dataTables_info" id="datatable-default_info" role="status" aria-live="polite">Showing <?php echo $limit_start+1;?> to <?php echo ( ($limit_end > $total_records ) ? $total_records : $limit_end );?> of total <?php echo $total_records;?> entries</div>
                                        </div>
                                        <div class="col-sm-12 col-md-12 col-lg-8 pagination_wrapper">
                                            <div class="dataTables_paginate paging_bs_normal" id="datatable-ajax_paginate">
                                                <ul class="pagination">
                                                    <?php
                                                    if ($cur_page > 1) {
                                                        $pre = $cur_page - 1;

                                                        ?>
                                                        <li data-p="1" class="first"><a href="<?php echo pagination_url(); ?>"><span class="fa fa-step-backward"></span></a></li>
                                                        <li data-p="<?php echo $pre; ?>" class="prev" ><a href="<?php echo pagination_url($pre); ?>"><span class="fa fa-chevron-left"></span></a></li>
                                                    <?php } else { ?>
                                                        <li class="prev disabled" ><a href="#"><span class="fa fa-chevron-left"></span></a></li>
                                                    <?php }?>

                                                    <?php
                                                    for ($i = $start_loop; $i <= $end_loop; $i++) {

                                                        if ($cur_page == $i) {
                                                            echo '<li class="active" p="' . $i . '" ><a href="'.pagination_url($i).'">' . $i . '</a></li>';
                                                        } else {
                                                            //$msg .= "<li p='$i' class='active'>{$i}</li>";
                                                            echo '<li class="" p="' . $i . '" ><a href="'.pagination_url($i).'">' . $i . '</a></li>';
                                                        }
                                                    }
                                                    ?>

                                                    <?php
                                                    if ($cur_page < $no_of_paginations) {
                                                        $nex = $cur_page + 1;

                                                        ?>
                                                        <li data-p="<?php echo $nex; ?>" class="next"><a href="<?php echo pagination_url($nex); ?>"><span class="fa fa-chevron-right"></span></a></li>
                                                        <li data-p="<?php echo $no_of_paginations; ?>" class="last"><a href="<?php echo pagination_url($no_of_paginations); ?>"><span class="fa fa-step-forward"></span></a></li>
                                                    <?php } else { ?>
                                                        <li class="next disabled"><a href="#"><span class="fa fa-chevron-right"></span></a></li>
                                                    <?php }?>

                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 data_wrapper">
                                        <div class="panel-body">
                                            <div class="table-responsive">
                                                <?php if(!empty($data_query) && !empty($fields)) : ?>
                                                    <div class="modal" id="exampleModal" tabindex="-1" role="dialog">
                                                        <div class="modal-dialog" role="document" style="width:450px">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h3 class="modal-title" style="display:inline-block;">Last 12 Months History</h3>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                      <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>Modal body text goes here.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <table class="table table-bordered table-striped mb-none members_list_table">
                                                        <thead>
                                                        <tr>
                                                            <?php foreach($field_details as $field_key => $field_name) {
                                                                if( is_array($field_name) ) {
                                                                    $fieldName = $field_name['name'];
                                                                } else {
                                                                    $fieldName = $field_name;
                                                                }
                                                                // var_dump($field_key, $field_name);
                                                                // die();
                                                                $fieldNameSmall = strtolower($fieldName);

                                                                $sortingColumnsKeys = array_keys($sortingColumns);

                                                                $fieldNameUrl = "{$action_page_url}&sby=$fieldNameSmall&sorder=$nextSortOrder";

                                                                if(!empty($search_itemid)) { $fieldNameUrl .= "&searchitemid=$search_itemid"; }
                                                                if(!empty($search_lastorderdate)) { $fieldNameUrl .= "&lastorderdate=$search_lastorderdate"; }
                                                                if(!empty($nametype)) { $fieldNameUrl .= "&nametype=$nametype"; }
                                                                if(!empty($search_name)) { $fieldNameUrl .= "&searchname=$search_name"; }
                                                                if(!empty($search_city)) { $fieldNameUrl .= "&searchcity=$search_city"; }
                                                                if(!empty($search_state)) { $fieldNameUrl .= "&searchstate=$search_state"; }
                                                                if(!empty($name_filter_first)) { $fieldNameUrl .= "&name_filter_first=$name_filter_first"; }
                                                                if(!empty($name_filter_last)) { $fieldNameUrl .= "&name_filter_last=$name_filter_last"; }


                                                                /**
                                                                 * 13 February, 2019
                                                                 * The following has been updated due to clients request.
                                                                 * Only the label has been updated.
                                                                 * The commented lines are the old lines.
                                                                 */
                                                                /**
                                                                 * // Old code
                                                                 * $fieldNameUrlTag = ( in_array($fieldNameSmall, $sortingColumnsKeys) ? "<a href='$fieldNameUrl'>$fieldName</a>" : $fieldName );
                                                                 */
                                                                $th_title = $fieldName;
                                                                $contact_organizer_css_classes = "";
                                                                switch($fieldNameSmall) {
                                                                    case "as":
                                                                        $th_title = "AM";
                                                                        break;
                                                                    case "as_c":
                                                                        $th_title = "C";
                                                                        $contact_organizer_css_classes = "as_c_class";
                                                                        break;
                                                                    default:
                                                                        break;
                                                                }
                                                                $fieldNameUrlTag = ( in_array($fieldNameSmall, $sortingColumnsKeys) ? "<a href='$fieldNameUrl' class='$contact_organizer_css_classes'>$th_title</a>" : $fieldName );

                                                                echo "<th>" . $fieldNameUrlTag . '</th>';
                                                            }?>
                                                        </tr>
                                                        </thead>
                                                        <?php $count = 0; ?>
                                                        <?php while($res = mysqli_fetch_assoc($data_query)): ?>
                                                            <?php

                                                            $amigo_id = $res['amico_id'];
                                                            $res['MemberID'] = $amigo_id;

                                                            $ec_id = $session_user;

                                                            $level_to_member = get_level($ec_id, $amigo_id);

                                                            if(user_is_affiliate($session_user) && $level_to_member > 6) {
                                                                continue;
                                                            }

                                                            if (is_int($count / 2)) $bg = "white";
                                                            else $bg = "#EFEFEF";
                                                            echo "<tr bgcolor=\"$bg\">\n";


                                                            foreach($fields as $field) {
                                                                list($res[$field], $completed_this_field) = get_contact_organizer_field_value($res, $field);


                                                                //$completed_this_field = false;

                                                                if($field == 'level') {
                                                                    $res[$field] = $level_to_member;
                                                                    $completed_this_field = true;
                                                                }

                                                                if($field == '_as_c_') {
                                                                    $rkn_additional_css = "";
                                                                    $rkn_modal_trigger_content = "";
                                                                    if($user_mtype=="e") {
                                                                        $rkn_additional_css = "custom_AS_C_link";
                                                                        $rkn_modal_trigger_content = "data-toggle='modal' data-target='#exampleModal'";
                                                                    }
                                                                    $rkn_data_member_id = $res['MemberID'];
                                                                    // var_dump($rkn_additional_css, $rkn_data_member_id);
                                                                    // die();
                                                                }
                                                                // var_dump($field, $field_details["_as_c_"]);
                                                                // die();


                                                                if( is_array($field_details[$field]) && !$completed_this_field ) {
                                                                    ?>
                                                                    <td>
                                                                        <?php if( !empty($field_details[$field]['name']) ) { ?>
                                                                            <?php if( !empty($field_details[$field]['link']) ) { ?>
                                                                                <?php /*debug(true, false, $field_details[$field]['attributes']); */?>
                                                                                <a
                                                                                    <?php
                                                                                    $aClass = array();
                                                                                    $aClass[] = ( !empty($field_details[$field]['button']) ? 'btn btn-primary' : '' );
                                                                                    $aClass[] = ( !empty($field_details[$field]['button_extra_class']) ? $field_details[$field]['button_extra_class'] : '' );
                                                                                    $aClass[] = ( !empty($field_details[$field]['attributes']['class']) ? attribute_value_maker('class', $field_details[$field]['attributes']['class'], $field_details[$field]['attributes']['class']['class_values'] ) : '');
                                                                                    $aClass[] = ( !empty($res['customers_email_address']) ? 'member_has_email' : '' );
                                                                                    ?>

                                                                                    href="<?php echo ( !empty($field_details[$field]['link']) ?
                                                                                        ( (!empty($field_details[$field]['id_field']) && isset($res[ $field_details[$field]['id_field'] ]) ) ? str_replace('ID_FIELD_VALUE', $res[ $field_details[$field]['id_field'] ],  $field_details[$field]['link']) :  $field_details[$field]['link'] )
                                                                                        : '#'); ?>"
                                                                                    class="<?php echo implode(' ', $aClass); ?>"

                                                                                    <?php echo ( !empty($field_details[$field]['newtab']) ? 'target="_blank"' : '' ); ?>

                                                                                    <?php
                                                                                    $attributes = '';
                                                                                    if( !empty($field_details[$field]['attributes']) && is_array($field_details[$field]['attributes']) ) {
                                                                                        foreach($field_details[$field]['attributes'] as $attr_key => $attr_val) {
                                                                                            $attributes .= " $attr_key=\"$attr_val\" ";
                                                                                        }
                                                                                    }
                                                                                    $attributes = str_replace('ID_FIELD_VALUE', $res[ $field_details[$field]['id_field'] ],  $attributes);
                                                                                    //debug(true, false, $attributes);
                                                                                    echo $attributes;
                                                                                    ?>

                                                                                >
                                                                                    <?php

                                                                                    if( !empty($field_details[$field]['text_to_display']) ) {
                                                                                        if(!empty($field_details[$field]['id_field']) && isset($res[ $field_details[$field]['id_field'] ]) ) {
                                                                                            $linkContent = str_replace('ID_FIELD_VALUE', $res[ $field_details[$field]['id_field'] ],  $field_details[$field]['text_to_display']);
                                                                                        } else {
                                                                                            $linkContent = $field_details[$field]['text_to_display'];
                                                                                        }

                                                                                        if(!empty($field_details[$field]['text_field']) && isset($res[ $field_details[$field]['text_field'] ]) ) {
                                                                                            $linkContent = str_replace('TEXT_FIELD_VALUE', $res[ $field_details[$field]['text_field'] ],  $linkContent);
                                                                                        }
                                                                                    } else {
                                                                                        $linkContent = $field_details[$field]['name'];
                                                                                    }

                                                                                    echo $linkContent;

                                                                                    ?>
                                                                                </a>
                                                                            <?php } else {

                                                                              //var_dump($field, $res[$field], $completed_this_field);
                                                                              // var_dump($field, $field_details[$field]);
                                                                              // die();

                                                                                if(!empty($field_details[$field]['field_type'])) {
                                                                                    switch ($field_details[$field]['field_type']) {
                                                                                        case 'text_from_callback':
                                                                                            $text = '';

                                                                                            if( !empty($field_details[$field]['value']) && is_array($field_details[$field]['value']) ) {
                                                                                                $value = $field_details[$field]['value'];
                                                                                                $forward = true;

                                                                                                if(!empty( $value['check_function'] ) ) {
                                                                                                    $params = ( !empty($value['check_function_params']) && is_array($value['check_function_params']) ) ? $value['check_function_params'] : array();
                                                                                                    if(!empty($params) ) {
                                                                                                        foreach($params as $k=>$param) {
                                                                                                            if( !empty($value['check_function_params_field'][$k]) ) {
                                                                                                                $param_field = $value['check_function_params_field'][$k];

                                                                                                                $params[$k] = str_replace($param_field, $res[ $param_field ], $param);
                                                                                                            }
                                                                                                        }
                                                                                                    }

                                                                                                    $forward = call_user_func_array($value['function'], $params);
                                                                                                }

                                                                                                if($forward) {
                                                                                                    if (!empty($value['function'])) {
                                                                                                        $params = (!empty($value['params']) && is_array($value['params'])) ? $value['params'] : array();
                                                                                                        if (!empty($params)) {
                                                                                                            foreach ($params as $k => $param) {
                                                                                                                if (!empty($value['params_field'][$k])) {
                                                                                                                    $param_field = $value['params_field'][$k];

                                                                                                                    $params[$k] = str_replace($param_field, $res[$param_field], $param);
                                                                                                                }
                                                                                                            }
                                                                                                        }

                                                                                                        $text .= call_user_func_array($value['function'], $params);
                                                                                                    }
                                                                                                } else {

                                                                                                }
                                                                                            }
                                                                                            echo $text;
                                                                                            break;
                                                                                    }
                                                                                }
                                                                            } ?>
                                                                        <?php } ?>
                                                                    </td>
                                                                    <?php
                                                                }
                                                                else {
                                                                    /**
                                                                     * Must modify this code
                                                                     */
                                                                    if($field == '_as_c_') {
                                                                        $order_json_data = $user_mtype =='e' ? $res[_ohj_] : '{}';
                                                                        echo "<td class='$rkn_additional_css' data-member_id='$rkn_data_member_id' data-order_history_json='$order_json_data' $rkn_modal_trigger_content>".$res[$field]."</td>";
                                                                    } else {
                                                                        echo "<td>".$res[$field]."</td>";
                                                                    }

                                                                }
                                                            }
                                                            $count++;
                                                            echo "</tr>\n";
                                                            ?>
                                                        <?php endwhile; ?>
                                                    </table>
                                                    <script>
                                                        jQuery('.member_info_button').each(function(){
                                                            if( ! jQuery(this).hasClass('member_has_email') ) {
                                                                jQuery(this).addClass('red-color a_important_red-color');
                                                            }
                                                        });
                                                    </script>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="col-xs-12">
                                        <span>No Data Found!</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                    <div class="clearfix"></div>
                </section>
                <div class="clearfix"></div>
                <div class="m-lg ">&nbsp;</div>

                <section class="panel non_member_orders">
                    <div class="col-xs-12 centering ">
                        <header class="panel-heading">
                            <h2 class="panel-title text-center">Non Member Purchases</h2>
                        </header>

                        <?php
                        $autoshipEnabled = is_autoship_enable();
                        $vipEnabled = is_vipconsumer_enable();
                        $vipPlanId = get_vipconsumer_subscription_plan();

                        //$autoshipEnabled = false;
                        //$vipEnabled = false;
                        ?>

                        <?php
                        if($useMagento) {
                            $query = "SELECT o.entity_id, o.increment_id AS orders_id, o.customer_id, CONCAT(o.customer_firstname, ' ', o.customer_lastname) AS customers_name, DATE_FORMAT(o.created_at, '%m/%d/%Y') as date_purchased, o.grand_total AS `value`, o.customer_id";
                            if ($autoshipEnabled) {
                                $query .= ", CONCAT(ar.interval_period_number, ' ', ar.interval_period_type) AS autoship_interval_time";
                            }
                            if ($vipEnabled) {
                                //$query .= ", rs.customer_id AS is_vip_consumer";
                                $query .= " , IF(rst.plan_id='{$vipPlanId}',rs.customer_id,null) AS is_vip_consumer ";
                            }
                            $query .= "
                                    FROM " . MAGENTO_TABLE_PREFIX . "sales_flat_order AS o
                                    INNER JOIN " . MAGENTO_TABLE_PREFIX . "amasty_amorderattr_order_attribute AS oa ON oa.order_id = o.entity_id ";
                            if ($autoshipEnabled) {
                                $query .= " LEFT JOIN " . MAGENTO_TABLE_PREFIX . "mvijaautoship_request AS ar ON ar.mage_order_id = o.entity_id ";
                            }
                            if ($vipEnabled) {
                                $query .= " LEFT JOIN " . MAGENTO_TABLE_PREFIX . "recurringandrentalpayments_subscription AS rs ON rs.customer_id = o.customer_id ";
                                $query .= " LEFT JOIN " . MAGENTO_TABLE_PREFIX . "recurringandrentalpayments_terms AS rst ON rst.terms_id = rs.term_type ";
                            }
                            $query .= "
                                    WHERE ( oa.ja_affiliate_member_id = '' OR oa.ja_affiliate_member_id IN (0, NULL, 'N/A', 'n/a', 'N / A') )
                                        AND oa.jareferrer_self = 0
                                        AND oa.jareferrer_amicoid='$session_user'
                                        AND oa.later_applied_member_id IN (0, NULL, '')
                                        -- AND oa.jareferrer_amico_member_id='$member_id'
                            ";
                            if ($vipEnabled) {
                                $query .= " GROUP BY o.entity_id ";
                            }
                            $query .= " ORDER BY o.created_at DESC ";
                        } else {
                            $query = "SELECT orders.orders_id, orders.customers_name, orders.date_purchased, orders_products.orders_products_id, orders_products.products_id, orders_total.value
                                  FROM orders
                                  LEFT JOIN orders_products ON orders.orders_id=orders_products.orders_id
                                  LEFT JOIN orders_total ON orders.orders_id=orders_total.orders_id AND orders_total.class='ot_subtotal'
                                  WHERE orders.customers_id='0' AND orders.int_member_id='$member_id'
                            ";
                        }

                        //debug(false, true, $query);

                        if ($non_m = mysqli_query($conn,$query)):
                            ?>
                            <div class="panel-body">
                                <div class="col-xs-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <tr class="">
                                                <th class="text-center">Name</th>
                                                <th class="text-center">Order Amount</th>
                                                <th class="text-center">Date</th>
                                                <th class="text-center">View Order</th>
                                                <?php if($autoshipEnabled): ?><th class="text-center">Is Autoship Enabled Order</th><?php endif; ?>
                                                <?php if($vipEnabled): ?><th class="text-center">VIP Consumer</th><?php endif; ?>
                                            </tr>
                                            <?php if (mysqli_num_rows($non_m) > 0): ?>
                                                <?php while ($nm = mysqli_fetch_object($non_m)) : ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $nm->customers_name; ?></td>
                                                        <td class="text-center"><?php echo '$'.number_format($nm->value,2); ?></td>
                                                        <td class="text-center"><?php echo $nm->date_purchased; ?></td>
                                                        <td class="text-center">
                                                            <a target="_blank" href="<?php echo base_shop_member_order_view_url();?><?php echo $nm->orders_id ?>">OrderID: <?php echo $nm->orders_id ?></a>
                                                        </td>
                                                        <?php if($autoshipEnabled): ?><td class="text-center"><?php echo ( !empty($nm->autoship_interval_time) ? "Yes (On Every {$nm->autoship_interval_time})" : " -- " ); ?></td><?php endif; ?>
                                                        <?php if($vipEnabled): ?><td class="text-center"><?php echo ( !empty($nm->is_vip_consumer) ? "Yes" : " -- " ); ?></td><?php endif; ?>
                                                    </tr>
                                                <?php endwhile; mysqli_free_result($non_m); ?>
                                            <?php else : ?>
                                                <tr><td colspan="4">No Data Found!</td></tr>
                                            <?php endif; ?>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($user_mtype=="e") { ?>
    <script>
        jQuery(document).on("click", ".custom_AS_C_link", function() {
          console.log("in");
            var data_str = jQuery(this).data("order_history_json");
            var json_length = Object.keys(data_str).length;
            var html = "";
            if( json_length > 0 ) {
                html += "<table class='table'>";
                jQuery.each(data_str, function(index, key) {
                    html += "<tr>";
                    html += "<td>";
                    html += "<strong>"+index+"</strong>";
                    html += "</td>";
                    html += "<td style='text-align:right;'>";
                    html += "<input type='text' value='"+key+"'>";
                    html += "</td>";
                    html += "</tr>";
                });
                html += "</table>";
            } else {
                html += "<p>Nothing Found</p>";
            }
            jQuery(".modal-body").html(html);
            jQuery('#exampleModal').trigger('focus');
        });
    </script>
    <?php } ?>

<?php
require_once("templates/footer.php");
