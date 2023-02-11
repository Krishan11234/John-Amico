<?php


function get_amico_name_with_autoship_vip_check($parent_amico, $member_amico, $member_id, $member_name, $autoshipEnabled=null, $vip_membership_enabled=null)
{
    global $conn;

    //$autoshipEnabled = is_null($autoshipEnabled) ? is_autoship_enable() : $autoshipEnabled;
    $autoshipEnabled = is_null($autoshipEnabled) ? false : $autoshipEnabled;

    if( !empty($member_id) && $autoshipEnabled )
    {
        $sql = "SELECT autoship_id FROM " . MAGENTO_TABLE_PREFIX . "mvijaautoship_request WHERE status=1 AND cancelled_by=0 AND amico_member_id='$member_id' LIMIT 1";
        if( mysqli_num_rows(mysqli_query($conn, $sql)) > 0 )
        {
            $member_name .= "   (<i title=\"Has at least 1 running Auto Ship Order\" style='color: #E91E63;' class=\"fa fa-shopping-cart\" aria-hidden=\"true\"></i>)";
        }

    }

    return $member_name;
}

function get_contact_organizer_field_value($result_array, $field) {
  // var_dump($result_array, $field);
  // die();
    if(empty($result_array) || empty($field) || !isset($result_array[$field]) ) {
        return array(false, false);
    }

    global $conn, $user_mtype, $session_user, $billabletime;

    $amigo_id = $result_array['amico_id'];
    $cv = $result_array[$field];

    //debug(true, true, $result_array);

    if ($field == '_ppp_') {
        /**
         * The calculation has been updated.
         */
        $_1_mo_ago = date("Y-m", time() - (1 * 31 * 86400));
        $_2_mo_ago = date("Y-m", time() - (2 * 31 * 86400));
        $_3_mo_ago = date("Y-m", time() - (3 * 31 * 86400));

        $_4_mo_ago = date("Y-m", time() - (4 * 31 * 86400));
        $_5_mo_ago = date("Y-m", time() - (5 * 31 * 86400));
        $_6_mo_ago = date("Y-m", time() - (6 * 31 * 86400));

        $_7_mo_ago = date("Y-m", time() - (7 * 31 * 86400));
        $_8_mo_ago = date("Y-m", time() - (8 * 31 * 86400));
        $_9_mo_ago = date("Y-m", time() - (9 * 31 * 86400));

        $_10_mo_ago = date("Y-m", time() - (10 * 31 * 86400));
        $_11_mo_ago = date("Y-m", time() - (11 * 31 * 86400));
        $_12_mo_ago = date("Y-m", time() - (12 * 31 * 86400));


        $_13_mo_ago = date("Y-m", time() - (13 * 31 * 86400));
        $_14_mo_ago = date("Y-m", time() - (14 * 31 * 86400));
        $_15_mo_ago = date("Y-m", time() - (15 * 31 * 86400));

        $_16_mo_ago = date("Y-m", time() - (16 * 31 * 86400));
        $_17_mo_ago = date("Y-m", time() - (17 * 31 * 86400));
        $_18_mo_ago = date("Y-m", time() - (18 * 31 * 86400));

        $_19_mo_ago = date("Y-m", time() - (19 * 31 * 86400));
        $_20_mo_ago = date("Y-m", time() - (20 * 31 * 86400));
        $_21_mo_ago = date("Y-m", time() - (21 * 31 * 86400));

        $_22_mo_ago = date("Y-m", time() - (22 * 31 * 86400));
        $_23_mo_ago = date("Y-m", time() - (23 * 31 * 86400));
        $_24_mo_ago = date("Y-m", time() - (24 * 31 * 86400));

        $cv = $amigo_id;

        /* New Code */
        $last_12_months_order_history = array();
        for($i=1; $i< 13; $i++) {
            $a_sum_12to01 = 0;
            $_mo_ago = date("Y-m", time() - ($i * 31 * 86400));
            $_plus_mo_ago = date("Y-m", time() - (($i+1) * 31 * 86400));
            if($i == 1) {
                $query = "
                    SELECT *
                    FROM bw_invoices, bw_invoice_line_items
                    WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity
                    and bw_invoices.OrderDate>='" . $_mo_ago . "-01 00:00:00'";
                $query = db_query($query) or die (mysql_error());
                $a_sum_12to01 = 0;
                while ($f = mysqli_fetch_array($query)) {
                    $a_sum_12to01 = $a_sum_12to01 + $f['ShipQty'] * $f['UnitPrice'];
                };
            } else {
                $query = "
                    SELECT *
                    FROM bw_invoices, bw_invoice_line_items
                    WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity
                    and bw_invoices.OrderDate>='" . $_plus_mo_ago . "-01 00:00:00' and bw_invoices.OrderDate<='" . $_mo_ago . "-01 00:00:00'";
                $query = db_query($query) or die (mysql_error());
                $a_sum_12to01 = 0;
                while ($f = mysqli_fetch_array($query)) {
                    $a_sum_12to01 = $a_sum_12to01 + $f['ShipQty'] * $f['UnitPrice'];
                };
            }
            $last_12_months_order_history[date("F Y", time() - ($i * 31 * 86400))] = $a_sum_12to01;
        }
        // var_dump($amigo_id, count($last_12_months_order_history));
        // die();
        if( count($last_12_months_order_history) > 0 ) {
          //SELECT * FROM tbl_member where amico_id = "W01011899"
            $last_12_months_order_history = json_encode($last_12_months_order_history);
            /**
             * Update "order_history_json" in "tbl_member".
             * Store the data of last 12 months in db.
             */
            db_query("UPDATE `tbl_member` SET `order_history_json`='$last_12_months_order_history' WHERE `amico_id`='$amigo_id'");
        }
        /* New Code */


        $query_12to01 = "
  SELECT *
  FROM bw_invoices, bw_invoice_line_items
  WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity
  and bw_invoices.OrderDate>='" . $_12_mo_ago . "-01 00:00:00' and bw_invoices.OrderDate<='" . $_1_mo_ago . "-01 00:00:00'";
        $query = db_query($query_12to01) or die (mysql_error());
        $a_sum_12to01 = 0;
        while ($f = mysqli_fetch_array($query)) {
            $a_sum_12to01 = $a_sum_12to01 + $f['ShipQty'] * $f['UnitPrice'];
        };

        $query_24to13 = "
  SELECT *
  FROM bw_invoices, bw_invoice_line_items
  WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity
  and bw_invoices.OrderDate<='" . $_13_mo_ago . "-01 00:00:00' and bw_invoices.OrderDate>='" . $_24_mo_ago . "-01 00:00:00'";
        $query = db_query($query_24to13) or die (mysql_error());
        $a_sum_24to13 = 0;
        while ($f = mysqli_fetch_array($query)) {
            $a_sum_24to13 = $a_sum_24to13 + $f['ShipQty'] * $f['UnitPrice'];
        };

        if ($a_sum_24to13 > 0) {
            $cv = (($a_sum_12to01 - $a_sum_24to13) / $a_sum_24to13) * 100;
            $cv = sprintf("%01.2f", $cv);
        } else {
            $cv = "N/A";
        }

        // var_dump($amigo_id, $a_sum_24to13, $a_sum_12to01, $cv);
        // die();

        // $_1_mo_ago = date("Y-m", time() - (1 * 31 * 86400));
        // $_2_mo_ago = date("Y-m", time() - (2 * 31 * 86400));
        // $_3_mo_ago = date("Y-m", time() - (3 * 31 * 86400));
        //
        // $_4_mo_ago = date("Y-m", time() - (4 * 31 * 86400));
        // $_5_mo_ago = date("Y-m", time() - (5 * 31 * 86400));
        // $_6_mo_ago = date("Y-m", time() - (6 * 31 * 86400));
        //
        // //$cv = $_1_mo_ago.'<br>'.$_2_mo_ago.'<br>'.$_3_mo_ago.'<br>'.$_4_mo_ago.'<br>'.$_5_mo_ago.'<br>'.$_6_mo_ago.'<br>';
        //
        // $cv = $amigo_id;
        //
        // $query_321 = "
        //     SELECT *
        //     FROM bw_invoices, bw_invoice_line_items
        //     WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity
        //     and bw_invoices.OrderDate>='" . $_3_mo_ago . "-01 00:00:00' and bw_invoices.OrderDate<='" . $_1_mo_ago . "-01 00:00:00'";
        // $query = db_query($query_321);
        // $a_sum_321 = 0;
        // while ($f = mysqli_fetch_array($query)) {
        //     $a_sum_321 += $f['ShipQty'] * $f['UnitPrice'];
        // };
        //
        //
        // $query_654 = "
        //     SELECT *
        //     FROM bw_invoices, bw_invoice_line_items
        //     WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity
        //     and bw_invoices.OrderDate<='" . $_4_mo_ago . "-01 00:00:00' and bw_invoices.OrderDate>='" . $_6_mo_ago . "-01 00:00:00'";
        // $query = db_query($query_654);
        // $a_sum_654 = 0;
        //
        // while ($f = mysqli_fetch_array($query)) {
        //     $a_sum_654 += $f['ShipQty'] * $f['UnitPrice'];
        // };
        //
        // if ($a_sum_654 > 0) {
        //     $cv = (($a_sum_321 - $a_sum_654) / $a_sum_654) * 100;
        //     $cv = sprintf("%01.2f", $cv);
        // } else {
        //     $cv = "N/A";
        // }
// var_dump("ins", $cv);
// die();
        $updq = db_query("UPDATE `tbl_member` SET `ppp`='$cv' WHERE `amico_id`='$amigo_id'");

        //$cv = $a_sum_321["ss"].'<br> - '.$a_sum_654["ss"].nl2br($query_321);

        return array($cv, true);
    }

    if($field == 'PC' || $field == 'int_customer_id' ) {
        $font_color = "red";


        $query_red = db_query("SELECT customers_telephone,customers_telephone1,customers_telephone2 FROM tbl_member, customers WHERE tbl_member.int_customer_id=customers.customers_id AND tbl_member.amico_id='" . $amigo_id . "'");
        $f = mysqli_fetch_array($query_red);

        $member_phone = '1' . str_replace(array(".", "'", ' ', '(', ')', '-'), "", $f['customers_telephone']);
        $member_phone1 = '1' . str_replace(array(".", "'", ' ', '(', ')', '-'), "", $f['customers_telephone1']);
        $member_phone2 = '1' . str_replace(array(".", "'", ' ', '(', ')', '-'), "", $f['customers_telephone2']);



        $query_yellow = db_query("SELECT bw.InvoiceNo FROM tbl_calls tbc, bw_invoices bw WHERE (tbc.calldestination='" . $member_phone . "' OR tbc.calldestination='" . $member_phone1 . "' OR tbc.calldestination='" . $member_phone2 . "') AND tbc.billabletime>'60' AND tbc.calldate<='" . date("Y-m-t 23:59:00", strtotime('-1 months')) . "' AND tbc.calldate>='" . date("Y-m-1 00:00:00", strtotime('-1 months')) . "' AND bw.ID = '" . $amigo_id . "' ORDER BY tbc.calldate DESC LIMIT 0, 1");

        $query_red = db_query("SELECT id FROM tbl_calls WHERE (calldestination='" . $member_phone . "' OR calldestination='" . $member_phone1 . "' OR calldestination='" . $member_phone2 . "') AND billabletime>'$billabletime' AND calldate<='" . date("Y-m-t 23:59:00", strtotime('-1 months')) . "' AND calldate>='" . date("Y-m-1 00:00:00", strtotime('-1 months')) . "' ORDER BY calldate DESC LIMIT 0, 1");

        $query_blue = db_query("SELECT bw.InvoiceNo FROM tbl_calls tbc, bw_invoices bw WHERE (tbc.calldestination='" . $member_phone . "' OR tbc.calldestination='" . $member_phone1 . "' OR tbc.calldestination='" . $member_phone2 . "') AND tbc.billabletime<='60' AND tbc.calldate<='" . date("Y-m-t 23:59:00", strtotime('-1 months')) . "' AND tbc.calldate>='" . date("Y-m-1 00:00:00", strtotime('-1 months')) . "' AND bw.ID = '" . $amigo_id . "' ORDER BY tbc.calldate DESC LIMIT 0, 1");

        $query_green = db_query("SELECT bw.InvoiceNo FROM bw_invoices bw WHERE ID = '" . $amigo_id . "' AND InvoiceDate >= '" . date('Y-m-d', mktime(0, 0, 0, date("m"), 1, date("Y"))) . "'");

        if (mysqli_num_rows($query_red) > 0) {
            $font_color = "yellow";
        }
        if (mysqli_num_rows($query_yellow) > 0) {
            $font_color = "yellow";
        }
        if (mysqli_num_rows($query_blue) > 0) {
            $font_color = "blue";
        }
        if (mysqli_num_rows($query_green) > 0) {
            $font_color = "#00FF00";
        }


        if ($user_mtype == 'm') {
            $cv = stripslashes("<span style='color:$font_color;'>PC</span>");
        } else {
            $cv = stripslashes("<a style=\"cursor: hand; cursor: pointer;\" onClick=\"window.open(\'./add_comments.php?b=&mlm_id=" . $result_array[$field] . "',\'comments\',\'scrollbars=yes,width=900,height=600,sizable=1\')\"><span style='color:$font_color;'>PC</span></a>");
        }

        return array($cv, true);
    }

    if ($field == "_lod_") {
        $cv = $amigo_id;

        if ($user_mtype == 'm') {
            $query_lod = "SELECT InvoiceDate FROM bw_invoices WHERE ID = '" . $cv . "' ORDER BY InvoiceDate DESC LIMIT 1";
        } else {
            $query_lod = "
    	SELECT date_format(o.OrderDate, '%m/%d/%Y' )
    	FROM bw_invoices o, customers c
    	inner join tbl_member m ON c.customers_id=m.int_customer_id
    	WHERE m.amico_id=o.ID  AND m.ec_id='$session_user' AND m.amico_id='$cv'
    	order by o.OrderDate desc
    	limit 1
    	";
        }

        $a_lod = mysqli_fetch_array(db_query($query_lod));

        if ($a_lod[0]) {
            $lod = $a_lod[0];
        } else {
            $lod = "N/A";
        }
        $cv = $lod;

        if ($cv == "0000/00/00" || $cv == "0000-00-00") {
            $cv = "N/A";
        };

        if ($cv != "N/A") {
            $split = explode("/", $cv);
            $cvupdate = $split[2] . '-' . $split[0] . '-' . $split[1];
        } else {
            $cvupdate = "N/A";
        };

        $updq = db_query("UPDATE `tbl_member` SET `lod`='$cvupdate' WHERE `amico_id`='$amigo_id'");

        return array($cv, true);
    }

    if ($field == "_ytd_") {
        //Year To Date
        //sum of totals of orders created this year

        $cv = $amigo_id;

        $query_current_year = "
            SELECT FKEntity, ShipQty, UnitPrice
            FROM bw_invoices, bw_invoice_line_items
            WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity AND bw_invoices.OrderDate>='" . date("Y") . "-01-01 00:00:00'";
        //echo $query_current_year; die();
        $query = db_query($query_current_year);
        $cv = 0;
        while ($f = mysqli_fetch_array($query)) {
            $cv = $cv + $f['ShipQty'] * $f['UnitPrice'];
        };
        $updq = db_query("UPDATE `tbl_member` SET `ytd`='$cv' WHERE `amico_id`='$amigo_id'");

        return array($cv, true);
    }

    /*if ($field == "_ytd2007_") {
        $cv = get_ytd_by_year($amigo_id, 2007, $cv);
        return array($cv, true);
    }

    if ($field == "_ytd2008_") {
        $cv = get_ytd_by_year($amigo_id, 2008, $cv);
        return array($cv, true);
    }

    if ($field == "_ytd2009_") {
        $cv = get_ytd_by_year($amigo_id, 2009, $cv);
        return array($cv, true);
    }

    if ($field == "_ytd2010_") {
        $cv = get_ytd_by_year($amigo_id, 2010, $cv);
        return array($cv, true);
    }

    if ($field == "_ytd2011_") {
        $cv = get_ytd_by_year($amigo_id, 2011, $cv);
        return array($cv, true);
    }

    if ($field == "_ytd2012_") {
        $cv = get_ytd_by_year($amigo_id, 2012, $cv);
        return array($cv, true);
    }

    if ($field == "_ytd2013_") {
        $cv = get_ytd_by_year($amigo_id, 2013, $cv);
        return array($cv, true);
    }

    if ($field == "_ytd2014_") {
        $cv = get_ytd_by_year($amigo_id, 2014, $cv);
        return array($cv, true);
    }

    if ($field == "_ytd2015_") {
        $cv = get_ytd_by_year($amigo_id, 2015, $cv);
        return array($cv, true);
    }

    if ($field == "_ytd2016_") {
        $cv = get_ytd_by_year($amigo_id, 2016, $cv);
        return array($cv, true);
    }*/

    for( $i=2007; $i< date('Y'); $i++ ) {
        if ($field == "_ytd{$i}_") {
            //$cv = get_ytd_by_year($amigo_id, 2016, $cv);
            return array($cv, true);
        }
    }

    if ($field == "_mtd_") {
        //Month To Date
        //sum of totals of orders created this month

        $cv = $amigo_id;

        $query_current_year = "
            SELECT FKEntity, ShipQty, UnitPrice
            FROM bw_invoices, bw_invoice_line_items
            WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity AND bw_invoices.OrderDate>='" . date("Y") . "-" . date("m") . "-01 00:00:00'";
        $query = db_query($query_current_year);
        $cv = 0;
        while ($f = mysqli_fetch_array($query)) {
            $cv = $cv + $f['ShipQty'] * $f['UnitPrice'];
        };
        $updq = db_query("UPDATE `tbl_member` SET `mtd`='$cv' WHERE `amico_id`='$amigo_id'");

        return array($cv, true);
    }

    if ($field == "_as_c_") {
        $cv = $result_array[$field];
        return array($cv, true);
    }

    if ($field == "_as_") {
        // Average of Sum

        $cv = $amigo_id;

        $_12_mo_ago = date("Y-m", time() - (12 * 31 * 86400));

        $query_for_year = "
            SELECT FKEntity, ShipQty, UnitPrice
            FROM bw_invoices, bw_invoice_line_items
            WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity
            and bw_invoices.OrderDate>='" . $_12_mo_ago . "-01 00:00:00'";
        $query = db_query($query_for_year);
        $a_sum_for_year = 0;
        while ($f = mysqli_fetch_array($query)) {
            $a_sum_for_year = $a_sum_for_year + $f['ShipQty'] * $f['UnitPrice'];
        };

        /* Newly added start */
        $monthz = 0;
        /* Newly added end */
        if ($a_sum_for_year == 0) {
            $cv = 0;
        } else {
            // Old code
            // $monthz = 0;

            for($i=1; $i< 13; $i++) {
                $_mo_ago = date("Y-m", time() - ($i * 31 * 86400));
                $_plus_mo_ago = date("Y-m", time() - (($i+1) * 31 * 86400));

                if($i == 1) {
                    $query = "SELECT FKEntity FROM bw_invoices, bw_invoice_line_items WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity and bw_invoices.OrderDate>='" . $_mo_ago . "-01 00:00:00'";
                    $query = db_query($query);
                    if (mysqli_num_rows($query) > 0) {
                        $monthz = $monthz + 1;
                    }
                } else {
                    $query = "SELECT FKEntity FROM bw_invoices, bw_invoice_line_items WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity and bw_invoices.OrderDate>='" . $_plus_mo_ago . "-01 00:00:00' and bw_invoices.OrderDate<='" . $_mo_ago . "-01 00:00:00'";
                    $query = db_query($query);
                    if (mysqli_num_rows($query) > 0) {
                        $monthz = $monthz + 1;
                    }
                }
            }

            if ($monthz == 0) {
                $monthz = 1;
            }
            $cv = round(($a_sum_for_year / $monthz), 2);
        }

        $updq = db_query("UPDATE `tbl_member` SET `as`='$cv' WHERE `amico_id`='$amigo_id'");
        /* Newly added start */
        db_query("UPDATE `tbl_member` SET `as_c`='$monthz' WHERE `amico_id`='$amigo_id'");
        /* Newly added end */

        return array($cv, true);
    }

    if ($field == 'appointments_date') {
        $dates = array();

        $query = db_query("SELECT customers.customers_id FROM tbl_member, customers WHERE tbl_member.int_customer_id=customers.customers_id AND tbl_member.amico_id='" . $amigo_id . "'");
        $customer = mysqli_fetch_assoc($query);

        if ($customer) {
            $res = db_query("SELECT * FROM tbl_schedule_list WHERE customers_id = " . $customer['customers_id']);

            if (mysqli_num_rows($res)) {
                while ($schedule = mysqli_fetch_assoc($res)) {
                    $dates[] = date('m/d/y', strtotime($schedule['dtt_callback']));
                }
            }
        }

        if ($dates) {
            $cv = implode('<br />', $dates);
        } else $cv = '00/00/00';

        return array($cv, true);
    }


    return array($result_array[$field], false);
}


function send_mail_thru_gmail($from_email, $from_name, $to_email, $to_name, $reply_to_email, $reply_to_name, $subject, $body, $smtpsecure=null) {
    $mail = new PHPMailer();
    $mail->IsSMTP();

    //GMAIL config
    $mail->SMTPAuth = true;              // enable SMTP authentication
    if(strlen($smtpsecure)) {
        $mail->SMTPSecure = $smtpsecure; // sets the prefix to the server
    }
    $mail->Host = "smtp.gmail.com";      // sets GMAIL as the SMTP server
    $mail->Port = 465;                   // set the SMTP port for the GMAIL server
    $mail->Username = "johnamico33345";  // GMAIL username
    $mail->Password = "johnamico1!";     // GMAIL password
    //End Gmail

    $mail->From = $from_email;           // Set to members email address who is sending message out
    $mail->FromName = $from_name;        // Set to members name who is sending message out
    $mail->Subject = $subject;           // use subject field from form
    $mail->MsgHTML($body);

    $mail->AddReplyTo($reply_to_email, $reply_to_name); // Set same members info here.
    $mail->AddAddress($to_email, $to_name);             // phone number and name of recipient
    $mail->IsHTML(true);                                // send as HTML

    return $mail->Send();
}

/**
 * @param $from_email
 * @param $un
 * @return string
 */
function get_mail_header($from_email, $un) {
    //$head = "From: " . $from_email . "\n";
    $head = "From: " . $from_email . " <info@johnamico.com>\n"; // Server only sends if the "FROM" address has a running server's domain in it
    $head .= "X-Mailer: John Amico Web Mailer\n";
    $head .= "Reply-To: " . $from_email . "\n";
    $head .= "Mime-Version: 1.0\n";
    $head .= "Content-Type:multipart/mixed;";
    $head .= "boundary=\"----------" . $un . "\"\n\n";

    return $head;
}

/**
 * @param $message
 * @param $un
 * @return string
 */
function get_mail_html_body($message, $un) {
    $zag = "------------" . $un . "\nContent-Type:text/html;\n";
    $zag .= "Content-Transfer-Encoding: 8bit\n\n$message\n\n";

    return $zag;
}

function get_link_text($linktext, $amico_id, $customers_password) {

    $htmllink = base_url() . "/dologin.php?id=" . md5($amico_id . $customers_password);
    if (strlen(trim($linktext))) {
        $htmllink = '<a href="' . $htmllink . '">' . $linktext . '</a>';
    }

    return $htmllink;
}

function attribute_value_maker($type, $attr_value, $condition_value=array()) {
    $valueText = '';

    if( !empty($attr_value) ) {
        if(is_array($attr_value)) {
            foreach($attr_value as $avk => $av) {
                if($avk === 'CONDITION__HAS_EMAIL_VALUE') {
                    if(!empty($condition_value['CONDITION__HAS_EMAIL_VALUE'])) {
                        $valueText .= $av . ' ';
                    }
                } else {
                    $valueText .= $av . ' ';
                }
            }
        } else {
            $valueText .= $attr_value;
        }
    }

    return $valueText;
}
