<?php
$billabletime = 10;

include_once 'query_results_limit_functions.php';

// <desc>General function which displayes mysql queries results </desc>
//<last changed>2/24/2003</last changed>
//$order_coresp_array should contain arrays like array("hidden_field"=>"displayed_field")
//designed to use order by "hidden_field" when "displayed_field" is cliked
//eg : 
//	Select 
//	concat('<a href=new_contact.php?id=',id,'&from=rc>',first_name,' ',last_name,'</a>') as Name,
//	concat(first_name,' ',last_name) as Name_HIDDEN
//	from contact where aid='1'  order by Name_HIDDEN asc limit 0, 200000
// (order by Name can return something else because of the ID value!)
//--> $order_coresp_array=array("Name_HIDDEN"=>"Name");
//print_r($order_coresp_array);
function query_results_limit($query, $camp_start, $order_start, $order_coresp_array, $fields_alias, $script_name, $l2, $head_title, $no_results_msg) {
    global $HTTP_POST_VARS;
    global $HTTP_GET_VARS;
    global $col0;
    global $col1;
    global $col2;
    global $filter;
    global $filter_col;
    global $ec_id;
    global $user_mtype;

    $PHP_SELF = $_SERVER['PHP_SELF'];

    $params = "";
    while (list($var, $value) = @each($HTTP_GET_VARS))
        if ($var != 'b' && $var != 'order' && $var != 'camp' && $var != 'l1' && $var != 'l2' && $var != "filter" && $var != "filter_col") $params .= "$var=" . @urlencode($value) . "&";
    while (list($var, $value) = @each($HTTP_POST_VARS))
        if ($var != 'b' && $var != 'order' && $var != 'camp' && $var != 'l1' && $var != 'l2' && $var != "filter" && $var != "filter_col") $params .= "$var=" . @urlencode($value) . "&";

    $rn = "
";
    preg_replace('/[\n\r]/', " ", "$query");
    global $l1;
    global $camp;
    global $order;
    ?>
    <script language="JavaScript">
        function next() {
            var n = document.myform.l2.value;
            for (i = 1; i <= n; i++)
                document.myform.l1.value++;
            document.myform.submit();
        }

        function previous() {
            document.myform.l1.value = document.myform.l1.value - document.myform.l2.value;
            document.myform.submit();
        }
    </script>
    <?php

    $having_filter = "";
    for ($i = 0; $i < count($filter); $i++)
        if ($filter[$i]) {
            $filter[$i] = stripslashes($filter[$i]);
            $having_filter .= $filter_col[$i] . " " . $filter[$i] . " and ";
        }


    if ($having_filter) {
        $having_filter .= " 1>0 ";
        $hv = array();
        $hv = explode("having", $query);
        $having = $hv[1];
        $having = $having_filter . $having;
        $query = $hv[0] . " having " . $having;
    }
    $result = db_query($query) or die (mysql_error());

    //echo nl2br($query)."<br>".mysql_error();

    if (!mysqli_num_rows($result)) {
        if ($no_results_msg)
            echo $no_results_msg;
        else
            echo "<font size=\"2\" face=\"Arial\" color=\"Red\"><b>No results found.</b></font>";
        exit;
    }
    $query_temp = $query;
    $column = mysqli_fetch_assoc($result);
    $j = 0;
    while (list($col_name, $col_value) = @each($column)) {
        $column_name[$j] = $col_name;


        if (is_numeric($col_value))
            $column_numeric[$j] = 1;
        $query_temp = str_replace("as $col_name", "", $query_temp);
        $j++;
    }

    if (!$script_name)
        $script_name = $_SERVER['PHP_SELF'];
    if (!$l2 or $l2 <= 0)
        $l2 = 20;
    if ($l1 == "") $l1 = 0;

//echo $camp;

    if ($camp == ""):

        if ($camp_start) {


            if (!preg_match("/\,/", $camp_start))
                $camp = $camp_start;
            else {
                $cs = explode(",", $camp_start);
                if ($cs[0]) $col0 = $cs[0];
                if ($cs[1]) $col1 = $cs[1];
                if ($cs[2]) $col2 = $cs[2];
            }

        } else {

            $tmp = 0;
            for ($k = 0; $k < count($column_name); $k++):
                if (preg_match("/HIDDEN/i", $column_name[$k])) {
                    $camp = $column_name[$k + 1];
                    $tmp = 1;
                }
            endfor;
            if (!$tmp)
                $camp = $column_name[0];

        }
    else:
        //unset col0,1,2 when $camp is set
        unset($col0);
        unset($col1);
        unset($col2);
    endif;

    //$l2=3112;
    if ($order == "")

        if ($order_start) $order = $order_start;
        else                $order = "asc";
    if ($col0 and $col1 and $col2)
        $camp = " $col0 , $col1 , $col2 ";
    $limit_query = $query . " order by $camp $order limit $l1, $l2";

    //echo nl2br($limit_query);

    $q = db_query($limit_query);
    $q1 = db_query($limit_query);

    //echo nl2br($limit_query)."<br>err=".mysql_error();

    $q_total = db_query($query);

    $n = mysqli_num_rows($q);
    $n_total = mysqli_num_rows($q_total);


    if ($n_total % $l2 == 0)
        $total_pages = $n_total / $l2;
    else
        $total_pages = floor($n_total / $l2) + 1;


    echo "<form method=post name=myform action=\"$script_name\">\n";
    echo "<input type=hidden name=\"pg\" value=\"\"> \n";
    echo "<input type=hidden name=\"where\" value=\"$where\" > \n";
    echo "<input type=hidden name=\"col0\" value=\"$col0\" > \n";
    echo "<input type=hidden name=\"col1\" value=\"$col1\" > \n";
    echo "<input type=hidden name=\"col2\" value=\"$col2\" > \n";
    echo "<input type=hidden name=\"l1\" value=\"$l1\">\n";
    echo "<input type=hidden name=\"l2\" value=\"$l2\">\n";
    echo "<input type=hidden name=\"order\" value=\"$order\">\n";
    echo "<input type=hidden name=\"camp\" value=\"$camp\">\n";
    echo "<input type=hidden name=\"st\" value=\"$st\">\n";
    echo "<input type=hidden name=\"lastorderdate\" value='" . $_POST['lastorderdate'] . "'>\n";
    echo "<input type=hidden name=\"searchname\" value='" . $_POST['searchname'] . "'>\n";
    echo "<input type=hidden name=\"searchcity\" value='" . $_POST['searchcity'] . "'>\n";
    echo "<input type=hidden name=\"searchstate\" value='" . $_POST['searchstate'] . "'>\n";
    echo "<input type=hidden name=\"searchitemid\" value='" . $_POST['searchitemid'] . "'>\n";


    echo "<br>
            <table border=0 cellpadding=\"2\" cellspacing=\"0\" width=650 class='members_list_table'>
                <thead>
  						<tr>
  							<td class=headlinecontent colspan=" . count($column_name) . " nowrap><font color=\"#000000\">&nbsp;" . $head_title . "</td>
						</tr>
		";

    if ($order == "asc")
        $order = "desc";
    else
        $order = "asc";

    if ($l1 == 0) {
        if ($l2 > $n_total)
            echo "<tr><td align=\"left\" nowrap colspan=3><font class=black1>Page 1/$total_pages</font></td><td align=\"right\" colspan=" . (count($column_name) - 1) . " nowrap><font class=\"black1\">$n_total results, displaying 1 - $n_total</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        else
            echo "<tr><td align=\"left\" nowrap colspan=3><font class=black1>Page 1/$total_pages</font></td><td align=\"right\" colspan=" . (count($column_name) - 1) . " nowrap><font class=\"black1\">$n_total results, displaying 1 - $l2</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        if ($total_pages != 1)
            echo "<a href=\"javascript:next()\" class=black1>Next</a>";

        echo "</td></tr>\n";
    } elseif ((floor($l1 / $l2) + 1) == $total_pages) {
        echo "<tr><td align=\"left\" nowrap colspan=3><font class=black1>Page $total_pages/$total_pages</font></td><td align=\"right\" colspan=" . (count($column_name) - 1) . " nowrap><font class=\"black1\">$n_total results, displaying " . ($l2 * floor($l1 / $l2) + 1) . " - $n_total</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"javascript:previous()\" class=black1>Previous</a></td></tr>\n";
    } else {
        echo "<tr><td align=\"left\" nowrap colspan=3><font class=black1>Page " . (floor($l1 / $l2) + 1) . "/$total_pages</font></td><td align=\"right\" colspan=" . (count($column_name) - 1) . " nowrap><font class=\"black1\">$n_total results, displaying " . ($l2 * floor($l1 / $l2) + 1) . " - " . ($l2 * floor($l1 / $l2) + $l2) . "</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"javascript:previous()\" class=\"black1\">Previous</a> <a href=\"javascript:next()\" class=\"black1\">Next</a></td></tr>\n";
    }

    if (preg_match("/\?/", $script_name))
        $href = $script_name;
    else
        $href = $script_name . "?foo=1";


    echo "<tr class=\"headerfont\" >\n";
    for ($i = 0; $i < count($column_name); $i++):
        $column_name_display[$i] = $column_name[$i];

        $align_column = "left";
        if ($column_name_display[$i] == "Value" or $column_name_display[$i] == "edu" or $column_name_display[$i] == "nr" or $column_name_display[$i] == "NetRevenue")
            $align_column = "center";
        if (!preg_match("/HIDDEN/i", $column_name[$i])) {

            if (is_array($order_coresp_array)):
                /*
                foreach($order_coresp_array as $key[$i]=>$val[$i]){
                    echo "i=$i ".$column_name[$i]." {} ".$key[$i]."=>".$val[$i]."<br>";
                    if ($column_name[$i]==$val[$i]){
                        $column_name[$i]=$key[$i];
                    }
                }
                */
                foreach ($order_coresp_array as $key2 => $val2) {
                    //echo "<br> --- i=$i ".$column_name[$i]." {} ".$key2."=>".$val2."<br>";
                    if ($column_name[$i] == $val2)
                        $column_name[$i] = $key2;
                }


            endif;

            if (is_array($fields_alias)):
                foreach ($fields_alias as $key => $val) {
                    if ($column_name_display[$i] == $key)
                        $column_name_display[$i] = $val;
                }
            endif;

            if ($column_name_display[$i] == 'level') {
                echo "\t\t<td align=\"$align_column\" class=\"header\" style=\"color:#003366;font-size:11px;\" nowrap><b>$column_name_display[$i]</b></td>\n";
            } else {
                $col_name = $column_name_display[$i];

                if ($col_name == 'C') $width = 'width:18px;';
                else if ($col_name == 'appointments_date') $width = 'width:70px;';
                else $width = '';

                if ($col_name == 'appointments_date') $col_name = '';

                echo "\t\t<td align=\"$align_column\" class=\"header\" style=\"color:Black;" . $width . "\" nowrap><a href=\"$href&order=$order&camp=$column_name[$i]&l1=0&l2=$l2&$params" . "c=1&" . "&$get_filter\" class=\"black1\"><b>" . $col_name . "</b></a></td>\n";
            }
        }

        /*if ($column_name_display[$i] == "2007"){
            echo "\t\t<td align=\"$align_column\" class=\"header\" style=\"color:Black;\" nowrap><a href=\"$href&order=$order&camp=$column_name[$i]&l1=0&l2=$l2&$params"."c=1&"."&$get_filter\" class=\"black1\"><b>2008</b></a></td>\n";
        }	*/
    endfor;
    echo "</tr>\n";
    echo "</thead>\n";

    echo "<tbody>\n";

    $count = 0;
    while ($a = mysqli_fetch_assoc($q)):

        $amigo_id = $a['MemberID'];
        $ec_id = $_SESSION['session_user'];

        $level_to_member = get_level($ec_id, $amigo_id);

        if(user_is_affiliate() && $level_to_member > 6) {
            continue;
        }

        if (is_int($count / 2)) $bg = "white";
        else $bg = "#EFEFEF";
        echo "<tr bgcolor=\"$bg\">\n";
        // while (list($cn,$cv)=each($a)){////
        foreach ($a as $cn => $cv) {
            $align = "left";
            if (preg_match('/���/', $cv) or is_numeric($cv)) {
                $tot[$cn] += $cv;
                $align = "left";
            } elseif ($cn == "NoOfRequests" or $cn == "Click_Throughs") $align = "center";
            //else


            $amigo_id = $a['MemberID'];


            if ($cn == '_ppp_') {
                $_1_mo_ago = date("Y-m", time() - (1 * 31 * 86400));
                $_2_mo_ago = date("Y-m", time() - (2 * 31 * 86400));
                $_3_mo_ago = date("Y-m", time() - (3 * 31 * 86400));

                $_4_mo_ago = date("Y-m", time() - (4 * 31 * 86400));
                $_5_mo_ago = date("Y-m", time() - (5 * 31 * 86400));
                $_6_mo_ago = date("Y-m", time() - (6 * 31 * 86400));

                //$cv = $_1_mo_ago.'<br>'.$_2_mo_ago.'<br>'.$_3_mo_ago.'<br>'.$_4_mo_ago.'<br>'.$_5_mo_ago.'<br>'.$_6_mo_ago.'<br>';

                $cv = $amigo_id;

                $query_321 = "
	SELECT *
	FROM bw_invoices, bw_invoice_line_items
	WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity
	and bw_invoices.OrderDate>='" . $_3_mo_ago . "-01 00:00:00' and bw_invoices.OrderDate<='" . $_1_mo_ago . "-01 00:00:00'";
                $query = db_query($query_321) or die (mysql_error());
                $a_sum_321 = 0;
                while ($f = mysqli_fetch_array($query)) {
                    $a_sum_321 = $a_sum_321 + $f['ShipQty'] * $f['UnitPrice'];
                };


                $query_654 = "
	SELECT *
	FROM bw_invoices, bw_invoice_line_items
	WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity
	and bw_invoices.OrderDate<='" . $_4_mo_ago . "-01 00:00:00' and bw_invoices.OrderDate>='" . $_6_mo_ago . "-01 00:00:00'";
                $query = db_query($query_654) or die (mysql_error());
                $a_sum_654 = 0;
                while ($f = mysqli_fetch_array($query)) {
                    $a_sum_654 = $a_sum_654 + $f['ShipQty'] * $f['UnitPrice'];
                };

                if ($a_sum_654 > 0) {
                    $cv = (($a_sum_321 - $a_sum_654) / $a_sum_654) * 100;
                    $cv = sprintf("%01.2f", $cv);
                } else {
                    $cv = "N/A";
                }

                $updq = db_query("UPDATE `tbl_member` SET `ppp`='$cv' WHERE `amico_id`='$amigo_id'") or die (mysql_error());

                //$cv = $a_sum_321["ss"].'<br> - '.$a_sum_654["ss"].nl2br($query_321);
            }


            if ($cn == "PC") {

                $font_color = "red";


                $query_red = db_query("SELECT * FROM tbl_member, customers WHERE tbl_member.int_customer_id=customers.customers_id AND tbl_member.amico_id='" . $amigo_id . "'") or die (mysql_error());
                $f = mysqli_fetch_array($query_red);

                $member_phone = str_replace(" ", "", $f['customers_telephone']);
                $member_phone = str_replace("(", "", $member_phone);
                $member_phone = str_replace(")", "", $member_phone);
                $member_phone = str_replace("-", "", $member_phone);
                $member_phone = str_replace("'", "", $member_phone);
                $member_phone = '1' . str_replace(".", "", $member_phone);

                $member_phone1 = str_replace(" ", "", $f['customers_telephone1']);
                $member_phone1 = str_replace("(", "", $member_phone1);
                $member_phone1 = str_replace(")", "", $member_phone1);
                $member_phone1 = str_replace("-", "", $member_phone1);
                $member_phone1 = str_replace("'", "", $member_phone1);
                $member_phone1 = '1' . str_replace(".", "", $member_phone1);

                $member_phone2 = str_replace(" ", "", $f['customers_telephone2']);
                $member_phone2 = str_replace("(", "", $member_phone2);
                $member_phone2 = str_replace(")", "", $member_phone2);
                $member_phone2 = str_replace("-", "", $member_phone2);
                $member_phone2 = str_replace("'", "", $member_phone2);
                $member_phone2 = '1' . str_replace(".", "", $member_phone2);

//if (is_int($member_phone) && is_int($member_phone1) && is_int($member_phone2)){

                $query_yellow = db_query("SELECT * FROM tbl_calls tbc, bw_invoices bw WHERE (tbc.calldestination='" . $member_phone . "' OR tbc.calldestination='" . $member_phone1 . "' OR tbc.calldestination='" . $member_phone2 . "') AND tbc.billabletime>'60' AND tbc.calldate<='" . date("Y-m-t 23:59:00", strtotime('-1 months')) . "' AND tbc.calldate>='" . date("Y-m-1 00:00:00", strtotime('-1 months')) . "' AND bw.ID = '" . $amigo_id . "' ORDER BY tbc.calldate DESC
LIMIT 0, 1");

                if ($amigo_id == 'W01001761') {
                    echo "SELECT * FROM tbl_calls tbc, bw_invoices bw WHERE (tbc.calldestination='" . $member_phone . "' OR tbc.calldestination='" . $member_phone1 . "' OR tbc.calldestination='" . $member_phone2 . "') AND tbc.billabletime>'60' AND tbc.calldate<='" . date("Y-m-t 23:59:00", strtotime('-1 months')) . "' AND tbc.calldate>='" . date("Y-m-1 00:00:00", strtotime('-1 months')) . "' AND bw.ID = '" . $amigo_id . "' ORDER BY tbc.calldate DESC
LIMIT 0, 1";
                }

//$query_yellow = "SELECT *  FROM bw_invoices, bw_invoice_line_items WHERE bw_invoices.ID='$amigo_id' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity AND bw_invoices.OrderDate>='".date("Y")."-".date("m")."-01 00:00:00'";
//$query_yellow=db_query($query_yellow) or die (mysql_error());


                $query_red = db_query("SELECT * FROM tbl_calls WHERE (calldestination='" . $member_phone . "' OR calldestination='" . $member_phone1 . "' OR calldestination='" . $member_phone2 . "') AND billabletime>'$billabletime' AND calldate<='" . date("Y-m-t 23:59:00", strtotime('-1 months')) . "' AND calldate>='" . date("Y-m-1 00:00:00", strtotime('-1 months')) . "' ORDER BY calldate DESC
LIMIT 0, 1") or die (mysql_error());

//$query_blue = db_query("SELECT * FROM tbl_calls WHERE (calldestination='".$member_phone."' OR calldestination='".$member_phone1."' OR calldestination='".$member_phone2."') AND billabletime>'$billabletime' AND calldate<='".date("Y-m-d H:i:s", mktime (0,0,0,date("m"), 31, date("Y")))."' AND calldate>='".date("Y-m-d H:i:s", mktime (0,0,0,date("m"), 1, date("Y")))."'") or die (mysql_error());

//echo "SELECT * FROM tbl_calls tbc, bw_invoices bw WHERE (tbc.calldestination='".$member_phone."' OR tbc.calldestination='".$member_phone1."' OR tbc.calldestination='".$member_phone2."') AND tbc.billabletime<'59' AND tbc.calldate<='".date("Y-m-d H:i:s", mktime (0,0,0,date("m"), 31, date("Y")))."' AND tbc.calldate>='".date("Y-m-d H:i:s", mktime (0,0,0,date("m"), 1, date("Y")))."' AND bw.ID IS NULL <br /><br />";

//$query_blue=db_query("SELECT * FROM tbl_calls tbc, bw_invoices bw WHERE (tbc.calldestination='".$member_phone."' OR tbc.calldestination='".$member_phone1."' OR tbc.calldestination='".$member_phone2."') AND tbc.billabletime<'61' AND tbc.calldate<='".date("Y-m-d H:i:s", mktime (0,0,0,date("m"), 31, date("Y")))."' AND tbc.calldate>='".date("Y-m-d H:i:s", mktime (0,0,0,date("m"), 1, date("Y")))."' AND bw.ID IS NULL ");

                $query_blue = db_query("SELECT * FROM tbl_calls tbc, bw_invoices bw WHERE (tbc.calldestination='" . $member_phone . "' OR tbc.calldestination='" . $member_phone1 . "' OR tbc.calldestination='" . $member_phone2 . "') AND tbc.billabletime<='60' AND tbc.calldate<='" . date("Y-m-t 23:59:00", strtotime('-1 months')) . "' AND tbc.calldate>='" . date("Y-m-1 00:00:00", strtotime('-1 months')) . "' AND bw.ID = '" . $amigo_id . "' ORDER BY tbc.calldate DESC
LIMIT 0, 1");

                $query_green = db_query("SELECT * FROM bw_invoices bw WHERE ID = '" . $amigo_id . "' AND InvoiceDate >= '" . date('Y-m-d', mktime(0, 0, 0, date("m"), 1, date("Y"))) . "'");

                if (mysqli_num_rows($query_red) > 0) {
                    $font_color = "yellow";
                };
                if (mysqli_num_rows($query_yellow) > 0) {
                    $font_color = "yellow";
                };
//if (mysqli_num_rows($query_yellow)>0)  {$font_color="#00FF00";};
                if (mysqli_num_rows($query_blue) > 0) {
                    $font_color = "blue";
                };
                if (mysqli_num_rows($query_green) > 0) {
                    $font_color = "#00FF00";
                };

//}

                if ($user_mtype == 'm') {
                    $cv = stripslashes("<font color='" . $font_color . "'>PC</font>");
                } else {
                    $cv = stripslashes("<a style=\"cursor: hand; cursor: pointer;\" onClick=\"window.open(\'add_comments.php?b=&mlm_id=" . $f['int_customer_id'] . "',\'comments\',\'scrollbars=yes,width=780,height=400,sizable=1\')\"><font color='" . $font_color . "'>PC</font></a>");
                }

            };


            if ($cn == "Info") {
                //$query_red = db_query("SELECT * FROM tbl_member, customers WHERE tbl_member.int_customer_id=customers.customers_id AND tbl_member.ec_id !=0 AND tbl_member.amico_id='" . $amigo_id . "'") or die (mysql_error());
                $query_red = db_query("SELECT * FROM tbl_member WHERE tbl_member.amico_id='" . $amigo_id . "'") or die (mysql_error());
                $f = mysqli_fetch_array($query_red);

                if ($user_mtype == 'm') {    //contact_info_my.php?
                    
                    $cv = stripslashes("<a style=\"cursor: hand; cursor: pointer;\" onClick=\"MM_openBrWindow(\'extras.php?t=m&mid=" . $f['int_member_id'] . "' ,\'info\',\'scrollbars=1,resizable=1,width=400,height=600\')\"><img src=\"../images/icons/ico_info.gif\" width=\"18\" height=\"15\" border=\"0\"></a>");

                    //echo ( ($cn=='Info') ? var_dump("SELECT * FROM tbl_member, customers WHERE tbl_member.int_customer_id=customers.customers_id AND tbl_member.ec_id !=0 AND tbl_member.amico_id='" . $amigo_id . "'") : '' );
                } else {
                    if ($f['customers_email_address'] == '' || $f['customers_email_address'] == 'poster@johnamico.com' || $f['customers_email_address'] == 'poster@johnamico.net') {
                        $cv = stripslashes("<a style=\"cursor: hand; cursor: pointer;\" onClick=\"MM_openBrWindow(\'extras.php?mid=" . $f['int_member_id'] . "' ,\'info\',\'scrollbars=1,resizable=1,width=1200,height=1050\')\"><img src=\"../images/icons/ico_info_red.gif\" width=\"18\" height=\"15\" border=\"0\"></a>");
                    } else {
                        $cv = stripslashes("<a style=\"cursor: hand; cursor: pointer;\" onClick=\"MM_openBrWindow(\'extras.php?mid=" . $f['int_member_id'] . "' ,\'info\',\'scrollbars=1,resizable=1,width=1200,height=1050\')\"><img src=\"../images/icons/ico_info.gif\" width=\"18\" height=\"15\" border=\"0\"></a>");
                    };
                }
            };

            if ($cn == "level") {
                $ec_id = $_SESSION['session_user'];
                $cv  = $level_to_member;
            }


            if ($cn == "_lod_") {
                $cv = $amigo_id;

                if ($user_mtype == 'm') {
                    $query_lod = "SELECT InvoiceDate FROM bw_invoices WHERE ID = '" . $cv . "' ORDER BY InvoiceDate DESC LIMIT 1";
                } else {
                    $query_lod = "
    	SELECT date_format(o.OrderDate, '%m/%d/%Y' )
    	FROM bw_invoices o, customers c
    	inner join tbl_member m ON c.customers_id=m.int_customer_id
    	WHERE m.amico_id=o.ID  AND m.ec_id='$ec_id' AND m.amico_id='$cv'
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

                $updq = db_query("UPDATE `tbl_member` SET `lod`='$cvupdate' WHERE `amico_id`='$amigo_id'") or die (mysql_error());
            }


//	""=> "Year To Date" );


            if ($cn == "_ytd_") {
//Year To Date
//sum of totals of orders created this year  

                $cv = $amigo_id;

                $query_current_year = "
	SELECT *  
	FROM bw_invoices, bw_invoice_line_items  
	WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity AND bw_invoices.OrderDate>='" . date("Y") . "-01-01 00:00:00'";
                $query = db_query($query_current_year) or die (mysql_error());
                $cv = 0;
                while ($f = mysqli_fetch_array($query)) {
                    $cv = $cv + $f['ShipQty'] * $f['UnitPrice'];
                };
                $updq = db_query("UPDATE `tbl_member` SET `ytd`='$cv' WHERE `amico_id`='$amigo_id'") or die (mysql_error());
            }


            if ($cn == "_ytd2007_") {
                $cv = get_ytd_by_year($amigo_id, 2007, $cv);
            }

            if ($cn == "_ytd2008_") {
                $cv = get_ytd_by_year($amigo_id, 2008, $cv);
            }

            if ($cn == "_ytd2009_") {
                $cv = get_ytd_by_year($amigo_id, 2009, $cv);
            }

            if ($cn == "_ytd2010_") {
                $cv = get_ytd_by_year($amigo_id, 2010, $cv);
            }

            if ($cn == "_ytd2011_") {
                $cv = get_ytd_by_year($amigo_id, 2011, $cv);
            }

            if ($cn == "_ytd2012_") {
                $cv = get_ytd_by_year($amigo_id, 2012, $cv);
            }

            if ($cn == "_ytd2013_") {
                $cv = get_ytd_by_year($amigo_id, 2013, $cv);
            }

            if ($cn == "_ytd2014_") {
                $cv = get_ytd_by_year($amigo_id, 2014, $cv);
            }

            if ($cn == "_mtd_") {
//Month To Date
//sum of totals of orders created this month

                $cv = $amigo_id;

                $query_current_year = "
	SELECT *  
	FROM bw_invoices, bw_invoice_line_items  
	WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity AND bw_invoices.OrderDate>='" . date("Y") . "-" . date("m") . "-01 00:00:00'";
                $query = db_query($query_current_year) or die (mysql_error());
                $cv = 0;
                while ($f = mysqli_fetch_array($query)) {
                    $cv = $cv + $f['ShipQty'] * $f['UnitPrice'];
                };
                $updq = db_query("UPDATE `tbl_member` SET `mtd`='$cv' WHERE `amico_id`='$amigo_id'") or die (mysql_error());
            }


            if ($cn == "_as_") {
                $cv = $amigo_id;

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

                $query_for_year = "
	SELECT *  
	FROM bw_invoices, bw_invoice_line_items  
	WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity 
	and bw_invoices.OrderDate>='" . $_12_mo_ago . "-01 00:00:00'";
                $query = db_query($query_for_year) or die (mysql_error());
                $a_sum_for_year = 0;
                while ($f = mysqli_fetch_array($query)) {
                    $a_sum_for_year = $a_sum_for_year + $f['ShipQty'] * $f['UnitPrice'];
                };

                if ($a_sum_for_year == 0) {
                    $cv = 0;
                } else {
                    $monthz = 0;

                    $query = "SELECT * FROM bw_invoices, bw_invoice_line_items WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity and bw_invoices.OrderDate>='" . $_1_mo_ago . "-01 00:00:00'";
                    $query = db_query($query) or die (mysql_error());
                    if (mysqli_num_rows($query) > 0) {
                        $monthz = $monthz + 1;
                    };

                    $query = "SELECT * FROM bw_invoices, bw_invoice_line_items WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity and bw_invoices.OrderDate>='" . $_2_mo_ago . "-01 00:00:00' and bw_invoices.OrderDate<='" . $_1_mo_ago . "-01 00:00:00'";
                    $query = db_query($query) or die (mysql_error());
                    if (mysqli_num_rows($query) > 0) {
                        $monthz = $monthz + 1;
                    };

                    $query = "SELECT * FROM bw_invoices, bw_invoice_line_items WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity and bw_invoices.OrderDate>='" . $_3_mo_ago . "-01 00:00:00' and bw_invoices.OrderDate<='" . $_2_mo_ago . "-01 00:00:00'";
                    $query = db_query($query) or die (mysql_error());
                    if (mysqli_num_rows($query) > 0) {
                        $monthz = $monthz + 1;
                    };

                    $query = "SELECT * FROM bw_invoices, bw_invoice_line_items WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity and bw_invoices.OrderDate>='" . $_4_mo_ago . "-01 00:00:00' and bw_invoices.OrderDate<='" . $_3_mo_ago . "-01 00:00:00'";
                    $query = db_query($query) or die (mysql_error());
                    if (mysqli_num_rows($query) > 0) {
                        $monthz = $monthz + 1;
                    };

                    $query = "SELECT * FROM bw_invoices, bw_invoice_line_items WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity and bw_invoices.OrderDate>='" . $_5_mo_ago . "-01 00:00:00' and bw_invoices.OrderDate<='" . $_4_mo_ago . "-01 00:00:00'";
                    $query = db_query($query) or die (mysql_error());
                    if (mysqli_num_rows($query) > 0) {
                        $monthz = $monthz + 1;
                    };

                    $query = "SELECT * FROM bw_invoices, bw_invoice_line_items WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity and bw_invoices.OrderDate>='" . $_6_mo_ago . "-01 00:00:00' and bw_invoices.OrderDate<='" . $_5_mo_ago . "-01 00:00:00'";
                    $query = db_query($query) or die (mysql_error());
                    if (mysqli_num_rows($query) > 0) {
                        $monthz = $monthz + 1;
                    };

                    $query = "SELECT * FROM bw_invoices, bw_invoice_line_items WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity and bw_invoices.OrderDate>='" . $_7_mo_ago . "-01 00:00:00' and bw_invoices.OrderDate<='" . $_6_mo_ago . "-01 00:00:00'";
                    $query = db_query($query) or die (mysql_error());
                    if (mysqli_num_rows($query) > 0) {
                        $monthz = $monthz + 1;
                    };

                    $query = "SELECT * FROM bw_invoices, bw_invoice_line_items WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity and bw_invoices.OrderDate>='" . $_8_mo_ago . "-01 00:00:00' and bw_invoices.OrderDate<='" . $_7_mo_ago . "-01 00:00:00'";
                    $query = db_query($query) or die (mysql_error());
                    if (mysqli_num_rows($query) > 0) {
                        $monthz = $monthz + 1;
                    };

                    $query = "SELECT * FROM bw_invoices, bw_invoice_line_items WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity and bw_invoices.OrderDate>='" . $_9_mo_ago . "-01 00:00:00' and bw_invoices.OrderDate<='" . $_8_mo_ago . "-01 00:00:00'";
                    $query = db_query($query) or die (mysql_error());
                    if (mysqli_num_rows($query) > 0) {
                        $monthz = $monthz + 1;
                    };

                    $query = "SELECT * FROM bw_invoices, bw_invoice_line_items WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity and bw_invoices.OrderDate>='" . $_10_mo_ago . "-01 00:00:00' and bw_invoices.OrderDate<='" . $_9_mo_ago . "-01 00:00:00'";
                    $query = db_query($query) or die (mysql_error());
                    if (mysqli_num_rows($query) > 0) {
                        $monthz = $monthz + 1;
                    };

                    $query = "SELECT * FROM bw_invoices, bw_invoice_line_items WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity and bw_invoices.OrderDate>='" . $_11_mo_ago . "-01 00:00:00' and bw_invoices.OrderDate<='" . $_10_mo_ago . "-01 00:00:00'";
                    $query = db_query($query) or die (mysql_error());
                    if (mysqli_num_rows($query) > 0) {
                        $monthz = $monthz + 1;
                    };

                    $query = "SELECT * FROM bw_invoices, bw_invoice_line_items WHERE bw_invoices.ID='$cv' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity and bw_invoices.OrderDate>='" . $_12_mo_ago . "-01 00:00:00' and bw_invoices.OrderDate<='" . $_11_mo_ago . "-01 00:00:00'";
                    $query = db_query($query) or die (mysql_error());
                    if (mysqli_num_rows($query) > 0) {
                        $monthz = $monthz + 1;
                    };

                    if ($monthz == 0) {
                        $monthz = 1;
                    };
                    $cv = round(($a_sum_for_year / $monthz), 2);
                };

                $updq = db_query("UPDATE `tbl_member` SET `as`='$cv' WHERE `amico_id`='$amigo_id'") or die (mysql_error());
            }

            if ($cn == 'appointments_date') {
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
            }

            if (!preg_match("/HIDDEN/i", $cn)) {
                //if ($cn == "_ytd2007_"){
                //echo ( ($cn=='Info') ? var_dump($cv) : '' );
                echo "<td align=\"$align\" nowrap><font class=\"black1\">$cv</font></td>";
                //echo "<td align=\"$align\" nowrap><font class=\"black1\">$cv2008</font></td>";
                //}
                //else echo "<td align=\"$align\" nowrap><font class=\"black1\">$cv</font></td>";
            }
        }////
        $count++;
        echo "</tr>\n";
    endwhile;


    echo "</tbody>\n";
    echo "</table>\n";
    echo "</form>\n";
}
