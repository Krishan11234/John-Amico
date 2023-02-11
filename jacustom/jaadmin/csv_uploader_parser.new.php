<?php
//ini_set('display_errors', 0);
//error_reporting(E_ALL ^ E_NOTICE);

/* $Id$
 *
 * This script will allow the admin
 * to upload their CSV file to the server.
 * it will then parse this file, initiating
 * any of the commission rules that match
 * the specific category.
 *
 */

require_once("session_check.inc");
require_once("../common_files/include/global.inc");
define('CSV_DIR', base_admin_path()."/csv/");

function get_children_info($root, $parent, $info, $depth, $period, $path = "") {
    global $conn;

    $depth++;

    if($depth > 6)
    {
        return $info;
    }

    if(empty($path))
    {
        $path .= $parent;
    }
    else
    {
        $path .= "#".$parent;
    }

    $sql = "SELECT tm1.amico_id FROM tbl_member tm1 INNER JOIN tbl_member tm2 ON tm1.int_parent_id = tm2.int_member_id WHERE tm2.amico_id = '$parent'";
    $result = mysqli_query($conn,$sql);
    echo mysqli_error($conn);

    while($amico_id = mysqli_fetch_row($result))
    {
        if(isset($info[$amico_id[0]]))
        {
            foreach($info[$amico_id[0]]['MP'] as $i_id => $data)
            {
                list($memberpath, $src_member, $level, $commissionable, $amount, $date) = explode("|", $data);
                $memberpath = $path."#".$memberpath;

                if($src_member == $amico_id[0])
                {
                    $info[$root]['MP'][$i_id] = $memberpath."|".$src_member."|".$depth."|".$commissionable."|".$amount."|".$date;
                }
            }
        }

        list($p_month, $p_year) = explode("|", $period);

        $p_ts = mktime(0, 0, 0, $p_month, 1, $p_year);

        $sql_old = "SELECT orders_id, date_purchased FROM orders WHERE refering_member = '$amico_id[0]' AND date_purchased >= '".date("Y", $p_ts)."-".date("m", $p_ts)."-01 00:00:00' AND date_purchased <= '".date("Y", $p_ts)."-".date("m", $p_ts)."-".date("t", $p_ts)." 23:59:59'";

        // Querying From Magento
        $sql_new = "SELECT IF(oa.ja_oldsite_order_id = '' OR 0, o.increment_id, oa.ja_oldsite_order_id) AS orders_id, o.created_at as date_purchased, oa.ja_oldsite_order_id, o.entity_id
                FROM ".MAGENTO_TABLE_PREFIX."sales_flat_order AS o
                INNER JOIN ".MAGENTO_TABLE_PREFIX."amasty_amorderattr_order_attribute AS oa ON oa.order_id = o.entity_id
                WHERE
                    o.created_at >= '".$p_year."-".$p_month."-01 00:00:00'
                    AND o.created_at <= '".$p_year."-".$p_month."-31 23:59:59'
                    AND oa.jareferrer_amicoid='{$amico_id[0]}'
        ";
        //$sql_new .= " AND oa.jareferrer_amico_member_id = 0 ";

        $sql = $sql_old;
        //$sql = $sql_new;

        $ord_res = mysqli_query($conn,$sql);
        echo mysqli_error($conn);


        while ($orders_id = mysqli_fetch_row($ord_res)) {
            $sql_old = "SELECT op.products_model, (op.final_price * op.products_quantity) as final_price, tcr.* FROM orders_products op LEFT JOIN tbl_commision_rule tcr ON op.products_model = tcr.str_commision_rule WHERE op.orders_id = '$orders_id[0]'";

            $sql_new = "SELECT oi.sku, oi.row_total_incl_tax as final_price, tcr.*
                    FROM " . MAGENTO_TABLE_PREFIX . "sales_flat_order_item AS oi
                    LEFT JOIN tbl_commision_rule AS tcr ON oi.sku = tcr.str_commision_rule
                    WHERE
                        oi.order_id = '{$orders_id['entity_id']}'
            ";

            $sql = $sql_old;
            //$sql = $sql_new;

            $prod_res = mysqli_query($conn, $sql);
            echo mysqli_error($conn);

            $commissionable = 0;
            $amount = 0;

            while($prdinfo = mysqli_fetch_array($prod_res))
            {
                if(!empty($prdinfo['int_commision_rule_id']))
                {
                    if($prdinfo['int_value'] <= 0)
                    {
                        continue;
                    }
                    else if($prdinfo['bit_percentage'] == 1)
                    {
                        $commissionable += round(($prdinfo['int_value']/100)*$prdinfo['final_price'], 2);
                    }
                    else
                    {
                        $commissionable += $prdinfo['int_value'];
                    }
                }
                else
                {
                    $commissionable += $prdinfo['final_price'];
                }

                $amount += $prdinfo['final_price'];
            }

            $info[$root]['RMP'][$orders_id[0]] = $path."#".$amico_id[0]."|".$amico_id[0]."|".$depth."|".round($commissionable, 2)."|".$amount."|".$orders_id[1];
        }


        $info = get_children_info($root, $amico_id['0'], $info, $depth, $period, $path);
    }

    return $info;
}

function csv_explode($void)
{
    $holder = "";
    $final = array();

    for($count = 0; $count < strlen($void); $count++)
    {
        if($void[$count] != "," && $count < strlen($void))
        {
            if($void[$count] == "\"")
            {
                $count++;
                while($void[$count] != "\"")
                {
                    $holder = $holder . $void[$count];
                    $count++;
                }
            }
            else
            {
                $holder = $holder . $void[$count];
            }
        }
        else
        {
            $final[] = $holder;
            $holder = "";
        }
    }
    $final[] = $holder;
    $holder = "";

    return $final;
}


function get_member_designation_id($active_members)
{
    $output = 1;

    if($active_members >= 12) $output = 5;
    else if($active_members >= 9) $output = 4;
    else if($active_members >= 6) $output = 3;
    else if($active_members >= 3) $output = 2;
    else if($active_members >= 1) $output = 1;

    return $output;
}


function parse_csv_file($file) {
    global $conn;

    //echo '<pre>'; var_dump(CSV_DIR.$file, file_exists(CSV_DIR.$file) ); echo '</pre>'; die();

    if(file_exists(CSV_DIR.$file) && $csv = fopen(CSV_DIR.$file, "r"))
    {
        $sql = "INSERT INTO stw_reports (report_time) VALUES (Now())";
        mysqli_query($conn,$sql);
        echo mysqli_error($conn);

        $r_id = mysqli_insert_id($conn);

        if( empty($r_id) ) {
            echo "Couldn't create STW report to database.<br/>";
            die();
        }

        fgets($csv, 1024);

        $period = "";
        $rmps = array();

        $_SESSION['STW_Importing']['Ref_Mem_IDs'] = array();

        while(!feof($csv))
        {

            list($m_id, $i_id, $i_date, $item, $price) = explode(',',fgets($csv, 1024));

            /*if( !empty($m_id) ) {
                if( in_array($m_id, $_SESSION['STW_Importing']['Ref_Mem_IDs']) ) {
                    //continue;
                }
            }*/

            list($month, $day, $year) = explode("/", $i_date);
            $month = str_pad($month, 2, '0', STR_PAD_LEFT);
            $period = "$month|$year";

            //echo '<pre>'; print_r(array( $period, $month, $year, $i_date )); echo '</pre>'; die();

            if($price <= 0)
            {
                continue;
            }

            $commissionable = $price;

            $sql = "SELECT * FROM tbl_commision_rule WHERE str_commision_rule = '".addslashes($item)."' AND bit_active = 1";
            $result = mysqli_query($conn,$sql);
            echo mysqli_error($conn);

            if(mysqli_num_rows($result) > 0)
            {
                $rule = mysqli_fetch_array($result);

                if($rule['int_value'] <= 0)
                {
                    continue;
                }
                else if($rule['bit_percentage'] == 1)
                {
                    $commissionable = round(($rule['int_value']/100)*$price, 2);
                }
                else
                {
                    $commissionable = $rule['int_value'];
                }
            }

            if(isset($info[$m_id]['MP'][$i_id]))
            {
                list($member_path, $src_member, $level, $commissionable_tot, $amount_tot, $date) = explode("|", $info[$m_id]['MP'][$i_id]);

                $commissionable_tot += $commissionable;
                $amount_tot += $price;

                $info[$m_id]['MP'][$i_id] = $m_id."|".$m_id."|0|".rtrim($commissionable_tot)."|".rtrim($amount_tot)."|".rtrim($date);
            }
            else
            {
                $info[$m_id]['MP'][$i_id] = $m_id."|".$m_id."|0|".rtrim($commissionable)."|".rtrim($price)."|".rtrim($i_date);
            }

            ksort($info);

            list($p_month, $p_year) = explode("|", $period);
            $p_year = (int)$p_year;

            $p_ts = mktime(0, 0, 0, $p_month, 1, $p_year);

            //$sql_old = "SELECT orders_id, date_purchased FROM orders WHERE refering_member = '$m_id' AND date_purchased >= '".date("Y", $p_ts)."-".date("m", $p_ts)."-01 00:00:00' AND date_purchased <= '".date("Y", $p_ts)."-".date("m", $p_ts)."-".date("t", $p_ts)." 23:59:59'";

            $sql_old = "SELECT orders_id, date_purchased FROM orders WHERE refering_member = '$m_id' AND date_purchased >= '".$p_year."-".$p_month."-01 00:00:00' AND date_purchased <= '".$p_year."-".$p_month."-31 23:59:59'";

            // Querying From Magento
            $sql_new = "SELECT IF(oa.ja_oldsite_order_id = '' OR 0, o.increment_id, oa.ja_oldsite_order_id) AS orders_id, o.created_at as date_purchased, oa.ja_oldsite_order_id, o.entity_id
                    FROM ".MAGENTO_TABLE_PREFIX."sales_flat_order AS o
                    INNER JOIN ".MAGENTO_TABLE_PREFIX."amasty_amorderattr_order_attribute AS oa ON oa.order_id = o.entity_id
                    WHERE
                        o.created_at >= '".$p_year."-".$p_month."-01 00:00:00'
                        AND o.created_at <= '".$p_year."-".$p_month."-31 23:59:59'
                        AND oa.jareferrer_amicoid='$m_id'
            ";
            //$sql_new .= " AND oa.jareferrer_amico_member_id = 0 ";

            $sql = $order_sql = $sql_old;
            //$sql = $order_sql = $sql_new;

            //echo $sql; echo PHP_EOL.PHP_EOL;//die();
            //echo '<pre>'; print_r(array( $sql_old, $p_ts, date('t', $p_ts) )); echo '</pre>'; die();

            $ord_res = mysqli_query($conn,$sql);
            echo mysqli_error($conn);

            if( mysqli_num_rows($ord_res) > 0 ) {
                while ($orders_id = mysqli_fetch_array($ord_res)) {
                    //$sql_old = "SELECT op.products_model, (op.final_price * op.products_quantity) as final_price, tcr.* FROM orders_products op LEFT JOIN tbl_commision_rule tcr ON op.products_model = tcr.str_commision_rule WHERE op.orders_id = '$orders_id[0]'";

                    //$sql_old = "SELECT op.products_model, (op.final_price * op.products_quantity) as final_price, tcr.* FROM orders_products op LEFT JOIN tbl_commision_rule tcr ON op.products_model = tcr.str_commision_rule WHERE op.orders_id = '{$orders_id['ja_oldsite_order_id']}'";
                    $sql_old = "SELECT op.products_model, (op.final_price * op.products_quantity) as final_price, tcr.* FROM orders_products op LEFT JOIN tbl_commision_rule tcr ON op.products_model = tcr.str_commision_rule WHERE op.orders_id = '{$orders_id[0]}'";

                    $sql_new = "SELECT oi.sku, oi.row_total_incl_tax as final_price, tcr.*
                            FROM ".MAGENTO_TABLE_PREFIX."sales_flat_order_item AS oi
                            LEFT JOIN tbl_commision_rule AS tcr ON oi.sku = tcr.str_commision_rule
                            WHERE
                                oi.order_id = '{$orders_id['entity_id']}'
                    ";

                    $sql = $sql_old;
                    //$sql = $sql_new;

                    //echo $sql; echo PHP_EOL.PHP_EOL;//die();
                    //echo $sql_old; echo PHP_EOL.PHP_EOL;//die();

                    //die();

                    $prod_res = mysqli_query($conn, $sql);
                    echo mysqli_error($conn);

                    $commissionable = 0;
                    $amount = 0;

                    if( mysqli_num_rows($prod_res) > 0 ) {
                        while ($prdinfo = mysqli_fetch_array($prod_res)) {
                            if (!empty($prdinfo['int_commision_rule_id'])) {
                                if ($prdinfo['int_value'] <= 0) {
                                    continue;
                                }
                                else {
                                    if ($prdinfo['bit_percentage'] == 1) {
                                        $commissionable += round(($prdinfo['int_value'] / 100) * $prdinfo['final_price'], 2);
                                    }
                                    else {
                                        $commissionable += $prdinfo['int_value'];
                                    }
                                }
                            }
                            else {
                                $commissionable += $prdinfo['final_price'];
                            }

                            $amount += $prdinfo['final_price'];
                        }
                    }

                    //$info[$m_id]['RMP'][$orders_id[0]] = $m_id . "|" . $m_id . "|0|" . round($commissionable, 2) . "|" . $amount . "|" . $orders_id[1];
                    $info[$m_id]['RMP'][$orders_id[0]] = $m_id . "|" . $m_id . "|0|" . round($commissionable, 2) . "|" . $amount . "|" . $orders_id[1];
                    $rmps[$m_id] = $info[$m_id]['RMP'];

                    //echo '<pre>'; print_r( array($sql, $order_sql)); echo '</pre>'; die(); }
                }
            }

            $_SESSION['STW_Importing']['Ref_Mem_IDs'][] = $m_id;

            //echo '<pre>'; print_r( $info ); echo '</pre>'; die();
        }

        //echo '<pre>'; print_r($info); echo '</pre>'; die();

        //die();

        fclose($csv);

        //echo '<pre>'; print_r($info); echo '</pre>'; die();
        echo '<pre>'; print_r($rmps); echo '</pre>'; //die();


        foreach ($info as $m_id => $invoices)
        {
            $info = get_children_info($m_id, $m_id, $info, 0, $period);

            ksort($info[$m_id]['MP']);

            if(isset($info[$m_id]['RMP']))
            {
                ksort($info[$m_id]['RMP']);
                $rmps[$m_id] = $info[$m_id]['RMP'];
            }
        }

        echo '<pre>'; print_r($rmps); echo '</pre>'; //die();
        ?>
        <table border="1" cellpadding="2" cellspacing="0">
            <th>Member ID</th>
            <th>Sales</th>
            <th>Amount Commissionable</th>
            <th>Commission Earned</th>
            <?php

            //echo '<pre>'; print_r( $info ); die();

            foreach ($info as $m_id => $invoices)
            {
                $sales = 0;
                $commissionable = 0;
                $commission_earned = 0;
                //echo $m_id;


                foreach ($invoices['MP'] as $i_id => $data)
                {
                    list($member_path, $src_member, $level, $i_commissionable, $i_amount, $date) = explode("|", $data);

                    if($level == 0)
                    {
                        $commissionable += $i_commissionable;
                    }
                }

                if($commissionable < 100)
                {
                    continue;
                }

                $commissionable = 0;

                foreach ($invoices['MP'] as $i_id => $data)
                {
                    list($member_path, $src_member, $level, $i_commissionable, $i_amount, $date) = explode("|", $data);

                    // Get custom comission info
                    $sql = "SELECT bit_custom_comission, expire_custom_comission FROM tbl_member WHERE amico_id='$src_member'";
                    $query = mysqli_query($conn,$sql);

                    list($bit_custom_comission, $expire_custom_comission) = mysqli_fetch_row($query);
                    // Set comission, if custom comission not expire set 20% for current user invoice
                    $cur_date = (int)strtotime($date);
                    if($expire_custom_comission)
                        $comission_expire = (int)strtotime($expire_custom_comission);
                    else
                        $comission_expire = 0;

                    if($level == 0 || $level == 1 || $level == 2 || $level == 3 || $level == 6)
                    {
                        if(((int)$bit_custom_comission == 1) && ($cur_date < $comission_expire)){
                            if ((int)$level == 1) {
                                $i_percentage = 20;
                            } elseif ((int)$level == 0) {
                                $i_percentage = 5;
                            } else {
                                $i_percentage = 0;
                            }

                        } else {
                            $i_percentage = 5;
                        }

                    } else if($level == 4 || $level == 5) {
                        if (((int)$bit_custom_comission == 1) && ($cur_date < $comission_expire)) {
                            $i_percentage = 0;
                        } else {
                            $i_percentage = 2;
                        }
                    }

                    $i_commission_earned = round(($i_percentage/100)*$i_commissionable, 2);

                    list($month, $day, $year) = explode('/', $date);
                    $sql_date = $year.'-'.$month.'-'.$day;

                    $sql = "INSERT INTO stw_data (type, report_id, member_id, src_member_id, invoice_id, invoice_date, level, member_path, amount, commissionable, commissioned, percentage) VALUES ('MP', '$r_id', '$m_id', '$src_member', '$i_id', '$sql_date', '$level', '$member_path', '$i_amount', '$i_commissionable', '$i_commission_earned', '$i_percentage')";
                    mysqli_query($conn,$sql);
                    echo mysqli_error($conn);

                    $sales += $i_amount;
                    $commissionable += $i_commissionable;
                    $commission_earned += $i_commission_earned;
                }

                if(isset($invoices['RMP']))
                {
                    foreach ($invoices['RMP'] as $i_id => $data)
                    {
                        list($member_path, $src_member, $level, $i_commissionable, $i_amount, $date) = explode("|", $data);

                        if($i_commissionable <= 0)
                        {
                            continue;
                        }

                        if($level == 0)
                        {
                            $i_percentage = 35;
                        }
                        else if($level == 1 || $level == 2 || $level == 3 || $level == 6)
                        {
                            $i_percentage = 3.25;
                        }
                        else if($level == 4 || $level == 5)
                        {
                            $i_percentage = 1.30;
                        }

                        $i_commission_earned = round(($i_percentage/100)*$i_commissionable, 2);

                        $sql = "INSERT INTO stw_data (type, report_id, member_id, src_member_id, invoice_id, invoice_date, level, member_path, amount, commissionable, commissioned, percentage) VALUES ('RMP', '$r_id', '$m_id', '$src_member', '$i_id', '$date', '$level', '$member_path', '$i_amount', '$i_commissionable', '$i_commission_earned', '$i_percentage')";

                        //if($i_id == '13749') { echo '<pre>'; print_r( array($sql, $invoices)); echo '</pre>'; die(); }

                        mysqli_query($conn,$sql);
                        echo mysqli_error($conn);

                        $sales += $i_amount;
                        $commissionable += $i_commissionable;
                        $commission_earned += $i_commission_earned;
                    }
                }

                ?>
                <tr>
                    <td><?=$m_id?></td>
                    <td align="right">$<?=number_format(round($sales, 2), 2)?></td>
                    <td align="right">$<?=number_format(round($commissionable, 2), 2)?></td>
                    <td align="right">$<?=number_format(round($commission_earned, 2), 2)?></td>
                </tr>
                <?php
                $sql = "SELECT int_member_id FROM tbl_member WHERE amico_id = '$m_id'";
                $result = mysqli_query($conn,$sql);
                echo mysqli_error($conn);
                $int_member_id = mysqli_result($result, 0);

                $sql = "INSERT INTO tbl_commision_sales_history (int_member_id, int_commision, int_sales, dtt_calculate, int_month, int_year) VALUES ('$int_member_id', '".round($commission_earned, 2)."', '".round($sales, 2)."', NOW(), '".date("m", $p_ts)."', '".date("Y", $p_ts)."')";
                mysqli_query($conn,$sql);
                echo mysqli_error($conn);
            }
            //die();
            ?>
        </table><br>
        <center><a href="excel_export.php?id=<?=$r_id?>"><font color="#0000CC">Export To Excel</font></a></center><br>
        <?php
    }

    $sql = "SELECT tm.int_member_id, tm.int_parent_id
    FROM stw_data stw
    INNER JOIN tbl_member tm ON stw.member_id = tm.amico_id
    WHERE stw.report_id = '$r_id'
    GROUP BY stw.member_id
    ORDER BY stw.member_id";
    $result = mysqli_query($conn,$sql);

    $member_parents = array();
    while($row = mysqli_fetch_assoc($result))
    {
        $member_parents[$row['int_member_id']] = $row['int_parent_id'];
    }

    $member_active_members = array();
    foreach($member_parents as $parent_id)
    {
        if(!isset($member_active_members[$parent_id])) $member_active_members[$parent_id] = 1;
        else $member_active_members[$parent_id]++;
    }

    foreach($member_active_members as $int_member_id => $active_members)
    {
        $int_designation_id = get_member_designation_id($active_members);

        $sql = "UPDATE tbl_member SET int_designation_id='$int_designation_id' WHERE int_member_id = '$int_member_id'";
        mysqli_query($conn,$sql);
        echo mysqli_error($conn);
    }


    $sql = "SELECT amico_id, int_designation_id FROM tbl_member GROUP BY int_member_id ORDER BY int_member_id";
    $result = mysqli_query($conn,$sql);
    echo mysqli_error($conn);

    while($row = mysqli_fetch_row($result))
    {
        $sql = "INSERT INTO past_designations SET report_id = '$r_id', member_id='$row[0]', int_designation_id='$row[1]'";
        mysqli_query($conn,$sql);
    }

    return;
}


$page_name = 'STW Report Upload';
$page_title = 'John Amico - ' . $page_name;

require_once("templates/header.php");
require_once("templates/sidebar.php");

$member_type_name = 'STW Report Upload';
//$member_type_name_plural = 'STW Reports';
$self_page = 'stw_reports.php';
$page_url = base_admin_url() . '/stw_reports.php?1=1';


?>
    <script type="text/javascript">
        <!--
        function fileReg() {
            var Doc = document.file_upload.elements['csv'];

            //console.log(Doc);

            if (! /(STW)?\.(csv)$/.test(Doc.value)) {
                alert('The file must be called STW.csv in order for the upload to work');
                return false;
            }
            else {
                return true;
            }
        }
        //-->
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
            <section class="panel">
                <form name="file_upload" class="form-bordered" onSubmit="return fileReg();" action="" method="post" enctype="multipart/form-data">
                    <div class="col-xs-12 col-lg-10 col-md-10 centering">
                        <header class="panel-heading">
                            <h2 class="panel-title text-center">Upload CSV file</h2>
                        </header>
                        <div class="panel-body pb-lg pt-lg mb-lg mt-lg">
                            <div class="form-group">
                                <label class="col-md-3 control-label">File Upload</label>
                                <div class="col-md-6">
                                    <div class="fileupload fileupload-new" data-provides="fileupload"><input type="hidden" value="1" name="Upload">
                                        <div class="input-append">
                                            <div class="uneditable-input">
                                                <i class="fa fa-file fileupload-exists"></i>
                                                <span class="fileupload-preview"></span>
                                            </div>
                                        <span class="btn btn-default btn-file">
                                            <span class="fileupload-exists">Change</span>
                                            <span class="fileupload-new">Select file</span>
                                            <input type="file" name="csv">
                                        </span>
                                            <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">Remove</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <footer class="panel-footer text-center">
                            <input type="submit" value="Upload" />
                        </footer>
                    </div>
                </form>
                <div class="clearfix"></div>
            </section>
            <section class="panel">
                <div class="col-xs-12">
                    <?php
                    //parse_csv_file("STW.csv");

                    /* If a file has been selected for upload... */

                    //echo '<pre>'; var_dump($_POST, $_FILES); echo ''; die();

                    if ( ($Upload == 1) && !empty($_FILES['csv']) ):
                        $file_info = pathinfo( $_FILES['csv']['name'] );

                        if( !empty($file_info) ) {
                            $fileName = $file_info['filename'] . "___" . date('d-M-Y__G-i') . "." . $file_info['extension'];
                            $csv_file = CSV_DIR . $fileName;
                            $csv_name = $_FILES['csv']['name'];
                        }

                        echo "<center><pre>";
                        if (move_uploaded_file($_FILES['csv']['tmp_name'], $csv_file)):
                            echo "Received ".$csv_name." successfully.<br />";
                            parse_csv_file($fileName);
                        else:
                            echo "Problem with file upload.<br />";
                            echo "<br>".$csv_file."<br>".$csv_name."<br>";
                            print_r($_FILES);
                            exit;
                        endif;
                        echo "</pre></center>";

                        unset($Upload);
                    endif;
                    ?>
                </div>
            </section>
        </div>
    </div>


<?php
require_once("templates/footer.php");
