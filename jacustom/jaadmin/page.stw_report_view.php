<head>
    <title>STW Report</title>
    <link rel="stylesheet" href="../common_files/theme_assets/stylesheets/old_stylesheet.css" type="text/css"/>
</head>
<?php
$useMagento = true;

switch($action) {
    case "view":
        ?>
        <!--<head>
            <title>STW Report</title>
            <link rel="stylesheet" href="../css/stylesheet.css" type="text/css"/>
        </head>-->

        <?php
        /*function list_child_invoices($parent_id) {

            global $id, $depth, $commission_total, $conn;
            $sql = "SELECT * FROM stw_data WHERE report_id = '$id' AND parent_id='$parent_id' AND customer_id != 0 GROUP BY member_id ORDER BY last_name";
            $result = mysqli_query($conn, $sql);
            while ($row = mysqli_fetch_array($result)) {
                $des_id = $row['designation_id'];
                for ($cnt = 3 - strlen($des_id); $cnt > 0; $cnt--) {
                    $des_id = "0" . $des_id;
                }

                $sql2 = "SELECT DISTINCT order_id FROM stw_data WHERE member_id = '{$row['member_id']}' AND report_id='$id' ORDER BY order_id";
                $result2 = mysqli_query($conn, $sql2);
                while ($row2 = mysqli_fetch_row($result2)) {
                    $commission = 0;

                    $sql3 = "SELECT * FROM stw_data WHERE order_id = '$row2[0]' AND report_id='$id'";
                    $result3 = mysqli_query($conn, $sql3);
                    while ($row3 = mysqli_fetch_array($result3)) {
                        if ($row3['com_is_percent'] == "Y") {
                            $commission += ($row3['percentage'] / 100) * (($row3['com_amount'] / 100) * $row3['price']);
                        } else {
                            $commission += ($row3['percentage'] / 100) * $row3['com_amount'];
                        }
                    }
                    */?><!--
                    <tr>
                        <td nowrap style="font-size:10px;">
                            <?php /*for ($cnt = 0; $cnt < $depth; $cnt++) {
                                echo "&nbsp;&nbsp;&nbsp;";
                            } */?>
                            <?php /*echo (strtoupper($row['first_name']) . " " . strtoupper($row['last_name']) . "-" . $des_id) */?>
                        </td>
                        <td align="center" style="font-size:10px;"><?php /*echo $depth */?></td>
                        <td align="center" style="font-size:10px;"><?php /*echo $depth */?></td>
                        <td style="font-size:10px;"><?php /*echo $row['designation'] */?></td>
                        <td align="center" style="font-size:10px;"><?php /*echo $row2['0'] */?></td>
                        <td style="font-size:10px;">Commission</td>
                        <td align="center" style="font-size:10px;"><?php /*echo number_format($row['percentage'], 3) */?></td>
                        <td align="right" style="font-size:10px;">$<?/*= number_format(round($commission, 2), 2) */?></td>
                    </tr>

                    --><?php
/*                    $commission_total += $commission;
                }
                $depth++;
                list_child_invoices($row['customer_id']);
                $depth--;
            }

            return;
        }*/

        $sql = "SELECT DATE_FORMAT(report_time, '%b %e, %Y') FROM stw_reports WHERE report_id = '$id'";
        $result = mysqli_query($conn, $sql);
        $report_date = mysqli_result($result, 0);

        $sql = "SELECT stw.member_id, UNIX_TIMESTAMP(stw.invoice_date) as period, td.str_designation, c.*, tm.int_member_id, tm.int_parent_id, tm.int_designation_id
          FROM stw_data stw
          INNER JOIN tbl_member tm ON stw.member_id = tm.amico_id
          INNER JOIN tbl_designation td ON tm.int_designation_id = td.int_designation_id
          INNER JOIN customers c ON tm.int_customer_id = c.customers_id
          WHERE stw.report_id = '$id' GROUP BY stw.member_id ORDER BY stw.member_id";
        $result = mysqli_query($conn, $sql);

        $member_parents = array();
        while ($row = mysqli_fetch_array($result)) {
            $member_parents[$row['int_member_id']] = $row['int_parent_id'];
        }

        mysqli_data_seek($result, 0);

        $first = true;
        while ($row = mysqli_fetch_array($result)) {
            $per_month = date("M", $row['period']);
            $per_year = date("Y", $row['period']);
            $per_start_day = 1;
            $per_end_day = date("t", $row['period']);
            ?>
            <table border="0" cellpadding="0" cellspacing="0" width="100%"
                   <? if ($first){
                       $first = false;
                   }else{
                   ?>style="page-break-before:always;"<?
            } ?>>
                <tr>
                    <td align="center" style="color:black;">
                        <!--<i><b><font size="5">AMICO EDUCATIONAL CONCEPTS INC</font></b><br>-->
                        <img src="../common_files/images/JA-Logo.JPG" border="0" width="318"
                             height="118" align="center"><br>
                        Prepared for:
                        <b><?= (strtoupper($row['customers_firstname']) . " " . strtoupper($row['customers_lastname'])) ?></b><br>
                        Title: <b><?= $row['str_designation'] ?></b><br>
                        For Pay Period:<br>
                        <b><?= ($per_month . " " . $per_start_day . ", " . $per_year . " to " . $per_month . " " . $per_end_day . ", " . $per_year) ?></b><br>
                        Prepared On:<br>
                        <b><?= $report_date ?></b><br>
                        <br>
                        <table border="0" cellpadding="0" cellspacing="0"
                               align="center" style="color:black;" width="200">
                            <tr>
                                <td align="center" style="font-size:10px;">
                                    <hr size="1">
                                    ID Number: <?= $row['member_id'] ?>
                                    <hr size="1">
                                </td>
                            </tr>
                            <?
                            $active_members = 0;
                            foreach ($member_parents AS $m_id => $p_id) {
                                if ($row['int_member_id'] == $p_id) {
                                    $active_members++;
                                }
                            }
                            ?>
                            <tr>
                                <td align="center" style="font-size:10px;">
                                    Active First Level
                                    Members: <?= $active_members ?></td>
                            </tr>
                            <?
                            $des_id = $row['int_designation_id'];
                            for ($cnt = 3 - strlen($des_id); $cnt > 0; $cnt--) {
                                $des_id = "0" . $des_id;
                            }
                            ?>
                            <tr>
                                <td align="center" style="font-size:10px;">
                                    <hr size="1"><?= $des_id ?>
                                    <hr size="1">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table border="0" cellpadding="5" cellspacing="0" align="center"
                   style="color:black;">
                <tr>
                    <td valign="top" nowrap style="font-size:10px;"><b>Name</b>
                    </td>
                    <td valign="top" nowrap align="center"
                        style="font-size:10px;"><b>Level</b></td>
                    <td valign="top" nowrap align="center"
                        style="font-size:10px;"><b>Pay Level</b></td>
                    <td valign="top" nowrap style="font-size:10px;"><b>Title</b>
                    </td>
                    <td valign="top" nowrap align="center"
                        style="font-size:10px;"><b>Invoice Nbr:</b></td>
                    <td valign="top" nowrap style="font-size:10px;"><b>Commission
                            Type</b></td>
                    <td valign="top" nowrap align="center"
                        style="font-size:10px;"><b>% Paid</b></td>
                    <td valign="top" nowrap align="right"
                        style="font-size:10px;"><b>Amount</b></td>
                </tr>
                <tr>
                    <td colspan="8" height="1"
                        style="font-size:10px;padding:0;">
                        <hr size="1">
                    </td>
                </tr>
                <?
                $commission_total = 0;

                $sql2 = "SELECT stw.*, tm.int_designation_id, td.str_designation, c.* FROM stw_data stw INNER JOIN tbl_member tm ON stw.src_member_id = tm.amico_id INNER JOIN tbl_designation td ON tm.int_designation_id = td.int_designation_id INNER JOIN customers c ON tm.int_customer_id = c.customers_id WHERE stw.report_id = '$id' AND stw.member_id = '{$row['member_id']}' AND stw.type = 'MP' ORDER BY stw.level, c.customers_lastname";
                $result2 = mysqli_query($conn, $sql2);
                while ($row2 = mysqli_fetch_array($result2)) {
                    $des_id = $row2['int_designation_id'];
                    for ($cnt = 3 - strlen($des_id); $cnt > 0; $cnt--) {
                        $des_id = "0" . $des_id;
                    }
                    ?>
                    <tr>
                        <td valign="top" nowrap
                            style="font-size:10px;"><?= (strtoupper($row2['customers_firstname']) . " " . strtoupper($row2['customers_lastname']) . "-" . $des_id) ?></td>
                        <td valign="top" align="center"
                            style="font-size:10px;"><?= $row2['level'] ?></td>
                        <td valign="top" align="center"
                            style="font-size:10px;"><?= $row2['level'] ?></td>
                        <td valign="top"
                            style="font-size:10px;"><?= $row2['str_designation'] ?></td>
                        <td valign="top" align="center"
                            style="font-size:10px;"><?= $row2['invoice_id'] ?></td>
                        <td valign="top" style="font-size:10px;">Commission</td>
                        <td valign="top" align="right"
                            style="font-size:10px;"><?= $row2['percentage'] ?></td>
                        <td valign="top" align="right" style="font-size:10px;">
                            $<?= $row2['commissioned'] ?></td>
                    </tr>
                    <?
                    $commission_total += $row2['commissioned'];
                }

                $sql2 = "SELECT stw.*, c.*, o.customers_name FROM stw_data stw INNER JOIN tbl_member tm ON stw.src_member_id = tm.amico_id INNER JOIN orders o ON stw.invoice_id = o.orders_id INNER JOIN customers c ON tm.int_customer_id = c.customers_id WHERE stw.report_id = '$id' AND stw.member_id = '{$row['member_id']}' AND stw.type = 'RMP' ORDER BY stw.level, o.customers_name";


                if ($useMagento) {
                    // Querying From Magento
                    $sql2 = "SELECT stw.*, c.*, CONCAT(o.customer_firstname, ' ', o.customer_lastname) AS customers_name
                            FROM stw_data stw
                            INNER JOIN tbl_member tm ON stw.src_member_id = tm.amico_id
                            INNER JOIN customers c ON tm.int_customer_id = c.customers_id
                            INNER JOIN ".MAGENTO_TABLE_PREFIX."sales_flat_order AS o
                            INNER JOIN ".MAGENTO_TABLE_PREFIX."amasty_amorderattr_order_attribute AS oa ON oa.order_id = o.entity_id
                            WHERE
                                stw.report_id='$id'
                                AND stw.member_id = '{$row['member_id']}'
                                AND stw.type = 'RMP' 
                                AND IF(oa.ja_oldsite_order_id = '' OR 0, o.increment_id, oa.ja_oldsite_order_id) = stw.invoice_id
                                
                            ORDER BY stw.level, customers_name
                         ";
                }

                //echo $sql2; die();

                /*$sql2 = "SELECT stw.*, c.*, o.customers_name, o.entity_id, o.increment_id AS orders_id, o.customer_id, CONCAT(o.customer_firstname, ' ', o.customer_lastname) AS customers_name
                    FROM stw_data stw
                    INNER JOIN tbl_member tm ON stw.src_member_id = tm.amico_id
                    INNER JOIN customers c ON tm.int_customer_id = c.customers_id
                    INNER JOIN orders o ON stw.invoice_id = o.orders_id
                    INNER JOIN ".MAGENTO_TABLE_PREFIX."sales_flat_order AS o
                    INNER JOIN ".MAGENTO_TABLE_PREFIX."amasty_amorderattr_order_attribute AS oa ON oa.order_id = o.entity_id
                    WHERE ( oa.ja_affiliate_member_id = '' OR oa.ja_affiliate_member_id IN (0, NULL, 'N/A', 'n/a', 'N / A') )
                        AND oa.jareferrer_self = 0
                        AND oa.jareferrer_amico_member_id='$member_id'
                ";*/
                $result2 = mysqli_query($conn, $sql2);

                if (mysqli_num_rows($result2) > 0) {
                    ?>
                    <tr>
                        <td colspan="8" height="1" style="font-size:10px;padding:0;">
                            <hr size="1">
                        </td>
                    </tr>
                    <?
                    while ($row2 = mysqli_fetch_array($result2)) {
                        ?>
                        <tr>
                            <td valign="top" nowrap
                                style="font-size:10px;"><?= (strtoupper($row2['customers_name'])) ?></td>
                            <td valign="top" align="center" style="font-size:10px;">N/A</td>
                            <td valign="top" align="center" style="font-size:10px;">N/A</td>
                            <td valign="top" style="font-size:10px;">Non-Member
                                Purchase: <?= (strtoupper($row2['customers_firstname'])) ?> <?= (strtoupper($row2['customers_lastname'])) ?>
                                (<?= $row2['src_member_id'] ?>)
                            </td>
                            <td valign="top" align="center"
                                style="font-size:10px;"><?= $row2['invoice_id'] ?></td>
                            <td valign="top" style="font-size:10px;">Commission</td>
                            <td valign="top" align="right"
                                style="font-size:10px;"><?= $row2['percentage'] ?></td>
                            <td valign="top" align="right" style="font-size:10px;">
                                $<?= $row2['commissioned'] ?></td>
                        </tr>
                        <?
                        $commission_total += $row2['commissioned'];
                    }
                }
                //  list_child_invoices($row[customer_id]);
                ?>
                <tr>
                    <td colspan="8" height="1"
                        style="font-size:10px;padding:0;">
                        <hr size="1">
                    </td>
                </tr>
                <tr>
                    <td colspan="5"></td>
                    <td valign="top" colspan="2" style="font-size:10px;">Center
                        Subtotal:
                    </td>
                    <td valign="top" align="right" style="font-size:10px;">
                        $<?= number_format($commission_total, 2) ?></td>
                </tr>
            </table>

            <?php
        }

        break;

    case "view_alt":
        ?>
        <!--<head>
            <title>STW Report</title>
            <link rel="stylesheet" href="../css/stylesheet.css"
                  type="text/css"/>
        </head>-->

        <?php

        $sql = "SELECT DATE_FORMAT(report_time, '%b %e, %Y') FROM stw_reports WHERE report_id = '$id'";
        $result = mysqli_query($conn, $sql);
        $report_date = mysqli_result($result, 0);

        $sql = "SELECT stw.member_id, UNIX_TIMESTAMP(stw.invoice_date) as period, td.str_designation, c.*, tm.int_member_id, tm.int_parent_id, tm.int_designation_id FROM stw_data stw INNER JOIN tbl_member tm ON stw.member_id = tm.amico_id INNER JOIN tbl_designation td ON tm.int_designation_id = td.int_designation_id INNER JOIN customers c ON tm.int_customer_id = c.customers_id WHERE stw.report_id = '$id' GROUP BY stw.member_id ORDER BY stw.member_id";
        $result = mysqli_query($conn, $sql);

        $member_parents = array();
        while ($row = mysqli_fetch_array($result)) {
            $member_parents[$row['int_member_id']] = $row['int_parent_id'];
        }


        $member_active_members = array();
        foreach ($member_parents as $parent_id) {
            if (!isset($member_active_members[$parent_id])) {
                $member_active_members[$parent_id] = 1;
            }
            else {
                $member_active_members[$parent_id]++;
            }
        }

        mysqli_data_seek($result, 0);

        $first = true;
        while ($row = mysqli_fetch_array($result)) {
            $per_month = date("M", $row['period']);
            $per_year = date("Y", $row['period']);
            $per_start_day = 1;
            $per_end_day = date("t", $row['period']);

            $active_members = (isset($member_active_members[$row['int_member_id']])) ? $member_active_members[$row['int_member_id']] : 0;
            ?>
            <table border="0" cellpadding="0" cellspacing="0" width="100%"
                   <?php if ($first){
                       $first = false;
                   }else{
                   ?>style="page-break-before:always;"<?
            } ?>>
                <tr>
                    <td align="center" style="color:black;">
                        <!--<i><b><font size="5">AMICO EDUCATIONAL CONCEPTS INC</font></b><br>-->
                        <img src="../common_files/images/JA-Logo.JPG" border="0" width="318"
                             height="118" align="center"><br>
                        Prepared for:
                        <b><?= (strtoupper($row['customers_firstname']) . " " . strtoupper($row['customers_lastname'])) ?></b><br>
                        Title:
                        <b><?= get_member_title($row['str_designation'], $active_members); ?></b><br>
                        For Pay Period:<br>
                        <b><?= ($per_month . " " . $per_start_day . ", " . $per_year . " to " . $per_month . " " . $per_end_day . ", " . $per_year) ?></b><br>
                        Prepared On:<br>
                        <b><?= $report_date ?></b><br>
                        <br>
                        <table border="0" cellpadding="0" cellspacing="0"
                               align="center" style="color:black;" width="200">
                            <tr>
                                <td align="center" style="font-size:10px;">
                                    <hr size="1">
                                    ID Number: <?= $row['member_id'] ?>
                                    <hr size="1">
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="font-size:10px;">
                                    Active First Level
                                    Members: <?= $active_members ?></td>
                            </tr>
                            <?
                            $des_id = $row['int_designation_id'];
                            for ($cnt = 3 - strlen($des_id); $cnt > 0; $cnt--) {
                                $des_id = "0" . $des_id;
                            }
                            ?>
                            <tr>
                                <td align="center" style="font-size:10px;">
                                    <hr size="1"><?= $des_id ?>
                                    <hr size="1">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table border="0" cellpadding="5" cellspacing="0" align="center"
                   style="color:black;">
                <tr>
                    <td valign="top" nowrap style="font-size:10px;"><b>Name</b>
                    </td>
                    <td valign="top" nowrap align="center"
                        style="font-size:10px;"><b>Level</b></td>
                    <td valign="top" nowrap align="center"
                        style="font-size:10px;"><b>Pay Level</b></td>
                    <td valign="top" nowrap style="font-size:10px;"><b>Title</b>
                    </td>
                    <td valign="top" nowrap align="center"
                        style="font-size:10px;"><b>Invoice Nbr:</b></td>
                    <td valign="top" nowrap style="font-size:10px;"><b>Commission
                            Type</b></td>
                    <td valign="top" nowrap align="center"
                        style="font-size:10px;"><b>% Paid</b></td>
                    <td valign="top" nowrap align="right"
                        style="font-size:10px;"><b>Amount</b></td>
                </tr>
                <tr>
                    <td colspan="8" height="1"
                        style="font-size:10px;padding:0;">
                        <hr size="1">
                    </td>
                </tr>
                <?php
                $commission_total = 0;

                $sql2 = "SELECT stw.*, tm.int_designation_id, tm.int_member_id, td.str_designation, c.* FROM stw_data stw INNER JOIN tbl_member tm ON stw.src_member_id = tm.amico_id INNER JOIN tbl_designation td ON tm.int_designation_id = td.int_designation_id INNER JOIN customers c ON tm.int_customer_id = c.customers_id WHERE stw.report_id = '$id' AND stw.member_id = '{$row['member_id']}' AND stw.type = 'MP' AND stw.level <= '$active_members' ORDER BY stw.level, c.customers_lastname";
                $result2 = mysqli_query($conn, $sql2);
                while ($row2 = mysqli_fetch_array($result2)) {
                    $des_id = $row2['int_designation_id'];
                    for ($cnt = 3 - strlen($des_id); $cnt > 0; $cnt--) {
                        $des_id = "0" . $des_id;
                    }

                    $am = (isset($member_active_members[$row2['int_member_id']])) ? $member_active_members[$row2['int_member_id']] : 0;
                    ?>
                    <tr>
                        <td valign="top" nowrap
                            style="font-size:10px;"><?= (strtoupper($row2['customers_firstname']) . " " . strtoupper($row2['customers_lastname']) . "-" . $des_id) ?></td>
                        <td valign="top" align="center"
                            style="font-size:10px;"><?= $row2['level'] ?></td>
                        <td valign="top" align="center"
                            style="font-size:10px;"><?= $row2['level'] ?></td>
                        <td valign="top"
                            style="font-size:10px;"><?= get_member_title($row2['str_designation'], $am); ?></td>
                        <td valign="top" align="center"
                            style="font-size:10px;"><?= $row2['invoice_id'] ?></td>
                        <td valign="top" style="font-size:10px;">Commission</td>
                        <td valign="top" align="right"
                            style="font-size:10px;"><?= $row2['percentage'] ?></td>
                        <td valign="top" align="right" style="font-size:10px;">
                            $<?= $row2['commissioned'] ?></td>
                    </tr>
                    <?
                    $commission_total += $row2['commissioned'];
                }

                $sql2 = "SELECT stw.*, c.*, o.customers_name FROM stw_data stw INNER JOIN tbl_member tm ON stw.src_member_id = tm.amico_id INNER JOIN orders o ON stw.invoice_id = o.orders_id INNER JOIN customers c ON tm.int_customer_id = c.customers_id WHERE stw.report_id = '$id' AND stw.member_id = '{$row['member_id']}' AND stw.type = 'RMP' ORDER BY stw.level, o.customers_name";

                if ($useMagento) {
                    // Querying From Magento
                    $sql2 = "SELECT stw.*, c.*, CONCAT(o.customer_firstname, ' ', o.customer_lastname) AS customers_name
                            FROM stw_data stw
                            INNER JOIN tbl_member tm ON stw.src_member_id = tm.amico_id
                            INNER JOIN customers c ON tm.int_customer_id = c.customers_id
                            INNER JOIN ".MAGENTO_TABLE_PREFIX."sales_flat_order AS o
                            INNER JOIN ".MAGENTO_TABLE_PREFIX."amasty_amorderattr_order_attribute AS oa ON oa.order_id = o.entity_id
                            WHERE
                                stw.report_id='$id'
                                AND stw.member_id = '{$row['member_id']}'
                                AND stw.type = 'RMP' 
                                AND IF(oa.ja_oldsite_order_id = '' OR 0, o.increment_id, oa.ja_oldsite_order_id) = stw.invoice_id
                                
                            ORDER BY stw.level, customers_name
                         ";
                }

                $result2 = mysqli_query($conn, $sql2);

                if (mysqli_num_rows($result2) > 0) {
                    ?>
                    <tr>
                        <td colspan="8" height="1" style="font-size:10px;padding:0;">
                            <hr size="1">
                        </td>
                    </tr>
                    <?
                    while ($row2 = mysqli_fetch_array($result2)) {
                        ?>
                        <tr>
                            <td valign="top" nowrap
                                style="font-size:10px;"><?= (strtoupper($row2['customers_name'])) ?></td>
                            <td valign="top" align="center" style="font-size:10px;">N/A</td>
                            <td valign="top" align="center" style="font-size:10px;">N/A</td>
                            <td valign="top" style="font-size:10px;">Non-Member
                                Purchase: <?= (strtoupper($row2['customers_firstname'])) ?> <?= (strtoupper($row2['customers_lastname'])) ?>
                                (<?= $row2['member_id'] ?>)
                            </td>
                            <td valign="top" align="center" style="font-size:10px;">N/A</td>
                            <td valign="top" style="font-size:10px;">Commission</td>
                            <td valign="top" align="right"
                                style="font-size:10px;"><?= $row2['percentage'] ?></td>
                            <td valign="top" align="right" style="font-size:10px;">
                                $<?= $row2['commissioned'] ?></td>
                        </tr>
                        <?
                        $commission_total += $row2['commissioned'];
                    }
                }
                //  list_child_invoices($row[customer_id]);
                ?>
                <tr>
                    <td colspan="8" height="1"
                        style="font-size:10px;padding:0;">
                        <hr size="1">
                    </td>
                </tr>
                <tr>
                    <td colspan="5"></td>
                    <td valign="top" colspan="2" style="font-size:10px;">Center
                        Subtotal:
                    </td>
                    <td valign="top" align="right" style="font-size:10px;">
                        $<?= number_format($commission_total, 2) ?></td>
                </tr>
            </table>

            <?
        }
        ?>
        </body>
        <?
        break;
    default:
        ?>
        <!--<head>
            <title>STW Reports</title>
            <link rel="stylesheet" href="../css/stylesheet.css"
                  type="text/css"/>
        </head>-->

        <body bgcolor="#FFFFFF" bottommargin="0" leftmargin="0" marginheight="0"
              marginwidth="0" rightmargin="0" topmargin="0">
        <table border="0" cellpadding="0" cellspacing="0" align="center">
            <tbody>
            <tr>
                <td align="center" colspan="9">
                    <img name="toplogo" src="../images/logo.gif" border="0">
                </td>
            </tr>
            </tbody>
        </table>
        <br/>
        <br/>
        <center>Click on "View" next to the Report Date of the Report you wish
            to view:
        </center>
        <Br>
        <table cellspacing="0" cellpadding="5" border="0" width="300"
               style="border: 1px solid black" align="center">
            <tr bgcolor="black">
                <td align="center" colspan="2"><font color="white"><B>STW
                            Reports</b></font></td>
            </tr>
            <?php
            $sql = "SELECT *, DATE_FORMAT(report_time, '%b %D, %Y - %l:%i%p') FROM stw_reports ORDER BY report_time DESC";
            $result = mysqli_query($conn, $sql);
            $bgcolor = "#FFFFFF";
            while ($row = mysqli_fetch_row($result)) {
                ?>
                <tr bgcolor="<?= $bgcolor ?>">
                    <td nowrap><?= $row[2] ?></td>
                    <td align="right" nowrap>
                        <a target="_blank"
                           href="stw_reports.php?action=view&id=<?= $row[0] ?>"
                           style="color:blue;">View</a> | <a target="_blank"
                                                             href="stw_reports.php?action=view_alt&id=<?= $row[0] ?>"
                                                             style="color:blue;">View
                            Alt.</a> | <a
                            href="excel_export.php?id=<?= $row[0] ?>"
                            style="color:blue;">Export to Excel</a> | <a
                            href="javascript: if(confirm('Are you sure you want to delete this report?')) location.href='stw_reports.php?action=delete&id=<?= $row[0] ?>'; "
                            style="color:blue;">Delete</a>
                    </td>
                </tr>
                <?
                if ($bgcolor == "#FFFFFF") {
                    $bgcolor = "#EEEEEE";
                }
                else {
                    $bgcolor = "#FFFFFF";
                }
            }
            ?>
        </table>

        <?php
        break;
}

function list_child_invoices($parent_id) {
    global $id, $depth, $commission_total, $conn;

    $sql = "SELECT * FROM stw_data WHERE report_id = '$id' AND parent_id='$parent_id' AND customer_id != 0 GROUP BY member_id ORDER BY last_name";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_array($result)) {
        $des_id = $row['designation_id'];
        for ($cnt = 3 - strlen($des_id); $cnt > 0; $cnt--) {
            $des_id = "0" . $des_id;
        }

        $sql2 = "SELECT DISTINCT order_id FROM stw_data WHERE member_id = '{$row['member_id']}' AND report_id='$id' ORDER BY order_id";
        $result2 = mysqli_query($conn, $sql2);
        while ($row2 = mysqli_fetch_row($result2)) {
            $commission = 0;

            $sql3 = "SELECT * FROM stw_data WHERE order_id = '$row2[0]' AND report_id='$id'";
            $result3 = mysqli_query($conn, $sql3);
            while ($row3 = mysqli_fetch_array($result3)) {
                if ($row3['com_is_percent'] == "Y") {
                    $commission += ($row3['percentage'] / 100) * (($row3['com_amount'] / 100) * $row3['price']);
                }
                else {
                    $commission += ($row3['percentage'] / 100) * $row3['com_amount'];
                }
            }
            ?>
            <tr>
                <td nowrap
                    style="font-size:10px;"><? for ($cnt = 0; $cnt < $depth; $cnt++) {
                        echo "&nbsp;&nbsp;&nbsp;";
                    } ?> <?= (strtoupper($row['first_name']) . " " . strtoupper($row['last_name']) . "-" . $des_id) ?></td>
                <td align="center" style="font-size:10px;"><?= $depth ?></td>
                <td align="center" style="font-size:10px;"><?= $depth ?></td>
                <td style="font-size:10px;"><?= $row['designation'] ?></td>
                <td align="center" style="font-size:10px;"><?= $row2['0'] ?></td>
                <td style="font-size:10px;">Commission</td>
                <td align="center"
                    style="font-size:10px;"><?= number_format($row['percentage'], 3) ?></td>
                <td align="right" style="font-size:10px;">
                    $<?= number_format(round($commission, 2), 2) ?></td>
            </tr>
            <?
            $commission_total += $commission;
        }
        $depth++;
        list_child_invoices($row['customer_id']);
        $depth--;
    }

    return;
}