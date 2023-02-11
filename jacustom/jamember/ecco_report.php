<?php
set_time_limit(60);

$page_name = 'ECCO Report';
$page_title = "John Amico - $page_name";

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


$member_id = $_SESSION['member']['ses_member_id'];
$session_user = $_SESSION['member']['session_user'];

if(!empty($member_id)) {
    $billabletime = 10;

    $a_amico = mysqli_fetch_array(mysqli_query($conn, "select * from tbl_member where int_member_id='" . $_SESSION['ses_member_id'] . "'"));
    $user_amico_id = $a_amico['amico_id'];
    $user_customer_id = $a_amico['int_members_id'];
    $user_type = $a_amico['mtype'];
}

//echo $user_amico_id;
?>


    <script language="JavaScript">
        collapse_left_sidebar_func(true, true);
        function MM_findObj(n, d) { //v4.01
            var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
                d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
            if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
            for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
            if(!x && d.getElementById) x=d.getElementById(n); return x;
        }

        function MM_showHideLayers() { //v6.0
            var i,p,v,obj,args=MM_showHideLayers.arguments;
            for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) { v=args[i+2];
                if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; }
                obj.visibility=v; }
        }

        function HideStatus()	{
            if (document.readyState=="complete")  {
                MM_showHideLayers('layer_loader','','hide');
            }
        }

        document.onreadystatechange = HideStatus;
    </script>
    <div role="main" class="content-body <?php echo ( $is_popup ? 'no-margin-left' : '' ); ?> ">
        <?php if(!$is_popup): ?>
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
        <?php endif; ?>

        <div class="row ">
            <div class="col-xs-12 centering">
                <section class="panel">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                    </header>
                    <div class="panel-body">
                        <div id="layer_loader" style="POSITION:absolute ; TOP: 200; LEFT: 50%;">
                            <table width="200" bgcolor="#FFFFFF" align="center" style="border:1px solid black">
                                <tr>
                                    <td align="center"><img src="../images/loading.gif"><br><h1>Loading Data...<br>Please wait.</h1></td>
                                </tr>
                            </table>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th class="text-center">Month</th>
                                    <th class="text-center">Total Sales</th>
                                    <th class="text-center">Trend, %</th>
                                    <th class="text-center">Y over Y</th>
                                    <th class="text-center">Members Number</th>
                                    <th class="text-center">Members Ordered</th>
                                    <th class="text-center">Members ordered, %</th>
                                    <th class="text-center">New members</th>
                                    <th class="text-center">Lost Members</th>
                                    <th class="text-center">DVPC</th>
                                    <th class="text-center">Biominoil</th>
                                    <th class="text-center">Contact Rate, %</th>
                                    <th class="text-center">Email, %</th>
                                </tr>
                                <?php
                                //$user_amico_id =0;
                                if( !empty($user_amico_id) ) {
                                    $months_till_2006 = round((time() - mktime(0, 0, 0, 01, 01, 2006)) / (86400 * 31));

                                    $i = 0;
                                    while ($i < ($months_till_2006 + 2 + 10)) {
                                        //Total Sales
                                        $query = "
                                    SELECT bw_invoice_line_items.ShipQty, bw_invoice_line_items.UnitPrice FROM bw_invoices, bw_invoice_line_items, tbl_member
                                    WHERE tbl_member.ec_id = '$user_amico_id' AND bw_invoices.ID=tbl_member.amico_id AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity
                                    AND bw_invoices.OrderDate>='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - $i, 1, date("Y"))) . "' AND bw_invoices.OrderDate<='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - $i, 31, date("Y"))) . "'";
                                        $query = mysqli_query($conn, $query);
                                        $value = 0;
                                        while ($f = mysqli_fetch_array($query)) {
                                            $value += $f['ShipQty'] * $f['UnitPrice'];
                                        }
                                        $array[$i][1] = $value;
                                        $i++;
                                    }


                                    $i = 0;
                                    while ($i < $months_till_2006) {
                                        if (($array[($i + 1)][1] + $array[($i + 2)][1] + $array[($i + 3)][1]) == 0) {
                                            $total_sales_percent = "N/A";
                                        }
                                        else {
                                            $total_sales_percent = round((($array[$i][1] - (($array[($i + 1)][1] + $array[($i + 2)][1] + $array[($i + 3)][1]) / 3)) / (($array[($i + 1)][1] + $array[($i + 2)][1] + $array[($i + 3)][1]) / 3) * 100), 2);
                                            if ($total_sales_percent > 0) {
                                                $total_sales_percent = "+" . $total_sales_percent;
                                            };
                                            $total_sales_percent .= '%';
                                        }

                                        ?>
                                        <tr>
                                            <td class=""><?php echo date("M, Y", (mktime(0, 0, 0, date("m") - $i, date("d"), date("Y")))); //Date ?></td>
                                            <td class="">$<?php echo number_format($array[$i][1], 2); //Total Sales ?></td>
                                            <td class=""><?php echo $total_sales_percent; //Total sales % up or down ?></td>

                                            <?php
                                            //Y over Y
                                            $query = " SELECT bw_invoice_line_items.ShipQty, bw_invoice_line_items.UnitPrice FROM bw_invoices, bw_invoice_line_items, tbl_member
                                        WHERE tbl_member.ec_id = '$user_amico_id' AND bw_invoices.ID=tbl_member.amico_id AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity AND bw_invoices.OrderDate>='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 1, date("Y") - 1)) . "' AND bw_invoices.OrderDate<='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 31, date("Y") - 1)) . "'";

                                            $query = mysqli_query($conn, $query);
                                            $value = 0;
                                            while ($f = mysqli_fetch_array($query)) {
                                                $value += $f['ShipQty'] * $f['UnitPrice'];
                                            }
                                            ?>
                                            <td class=""><?php echo ($array[($i + 12)][1] != 0) ? number_format((($array[$i][1] - $array[($i + 12)][1]) / $array[($i + 12)][1]) * 100, 2) : 0; ?></td>

                                            <?php
                                            //Number of members
                                            $query = mysqli_query($conn, "SELECT tbl_member_ec.ec_id FROM tbl_member_ec, tbl_member WHERE tbl_member.amico_id=tbl_member_ec.amico_id AND tbl_member_ec.ec_id = '$user_amico_id' AND event='added' AND timestamp<='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 31, date("Y"))) . "' GROUP BY tbl_member_ec.amico_id");
                                            $query2 = mysqli_query($conn, "SELECT tbl_member_ec.ec_id FROM tbl_member_ec, tbl_member WHERE tbl_member.amico_id=tbl_member_ec.amico_id AND tbl_member_ec.ec_id = '$user_amico_id' AND event='removed' AND timestamp<='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 31, date("Y"))) . "' GROUP BY tbl_member_ec.amico_id");
                                            $query3 = mysqli_query($conn, "SELECT amico_id FROM customers c inner join tbl_member m ON c.customers_id=m.int_customer_id left outer join address_book a on c.customers_id=a.customers_id  WHERE m.ec_id='$user_amico_id' AND m.bit_active='1' GROUP BY m.amico_id");

                                            $delta = mysqli_num_rows($query3) - ( mysqli_num_rows($query) - mysqli_num_rows($query2) );

                                            $query = mysqli_query($conn, "SELECT tbl_member_ec.ec_id FROM tbl_member_ec, tbl_member WHERE tbl_member.amico_id=tbl_member_ec.amico_id AND tbl_member_ec.ec_id = '$user_amico_id' AND event='added' AND timestamp<='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - $i, 31, date("Y"))) . "' GROUP BY tbl_member_ec.amico_id");
                                            $query2 = mysqli_query($conn, "SELECT tbl_member_ec.ec_id FROM tbl_member_ec, tbl_member WHERE tbl_member.amico_id=tbl_member_ec.amico_id AND tbl_member_ec.ec_id = '$user_amico_id' AND event='removed' AND timestamp<='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - $i, 31, date("Y"))) . "' GROUP BY tbl_member_ec.amico_id");
                                            $value = (mysqli_num_rows($query) - mysqli_num_rows($query2)) + $delta;

                                            $array[$i][4] = $value;
                                            $total_members = $value;
                                            ?>
                                            <td class=""><?php echo $value ?></td>

                                            <?php
                                            //Number of members ordered
                                            $query = "SELECT bw_invoice_line_items.FKEntity FROM bw_invoices, bw_invoice_line_items, tbl_member
                                        WHERE tbl_member.ec_id = '$user_amico_id' AND bw_invoices.ID=tbl_member.amico_id AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity
                                        AND bw_invoices.OrderDate>='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - $i, 1, date("Y"))) . "' AND bw_invoices.OrderDate<='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - $i, 31, date("Y"))) . "' GROUP BY tbl_member.amico_id";

                                            $query = mysqli_query($conn, $query);
                                            $value = mysqli_num_rows($query);

                                            $array[$i][5] = $value;
                                            ?>
                                            <td class=""><?php echo $value ?></td>


                                            <?php
                                            //% of Members ordered
                                            if ($array[$i][4] == "0") {
                                                $value = "N/A";
                                            } else {
                                                $value = round(($array[$i][5] / $array[$i][4]) * 100, 2) . "%";
                                            }
                                            ?>
                                            <td class=""><?php echo $value ?></td>


                                            <?php
                                            //New Members
                                            $query = mysqli_query($conn, "SELECT tbl_member_ec.ec_id FROM tbl_member_ec WHERE ec_id = '$user_amico_id' AND event='added'
                                           AND timestamp<='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - $i, 31, date("Y"))) . "'
                                           AND timestamp>='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - $i, 1, date("Y"))) . "'");
                                            $value = (mysqli_num_rows($query));
                                            ?>
                                            <td class=""><?php echo $value ?></td>


                                            <?php
                                            //Lost Members
                                            $query = mysqli_query($conn, "SELECT tbl_member_ec.ec_id FROM tbl_member_ec WHERE ec_id = '$user_amico_id' AND event='removed'
                                           AND timestamp<='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - $i, 31, date("Y"))) . "'
                                           AND timestamp>='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - $i, 1, date("Y"))) . "'") or die (mysql_error());
                                            $value = (mysqli_num_rows($query));
                                            ?>
                                            <td class=""><?php echo $value ?></td>


                                            <?php
                                            //DVPC
                                            if ($array[$i][5] == 0) {
                                                $value = "N/A";
                                            } else {
                                                $value = round(($array[$i][1] / $array[$i][5]), 2);
                                                $value = "$" . number_format($value, 2);
                                            }
                                            ?>
                                            <td class=""><?php echo $value ?></td>


                                            <?php
                                            //Biominoil
                                            $query = " SELECT bw_invoice_line_items.FKEntity FROM bw_invoices, bw_invoice_line_items, tbl_member
                                        WHERE tbl_member.ec_id = '$user_amico_id' AND bw_invoices.ID=tbl_member.amico_id AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity
                                        AND (bw_invoice_line_items.ID='BIOMIN' OR bw_invoice_line_items.ID='BIOMIN3')
                                        AND bw_invoices.OrderDate>='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - $i, 1, date("Y"))) . "'
                                        AND bw_invoices.OrderDate<='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - $i, 31, date("Y"))) . "' GROUP BY tbl_member.amico_id";
                                            $query = mysqli_query($conn, $query);
                                            $value = mysqli_num_rows($query);

                                            $array[$i][10] = $value;
                                            ?>
                                            <td class=""><?php echo $value ?></td>


                                            <?php
                                            //Contact rate
                                            if ($user_type == 'm') {
                                                $rslist = mysqli_query($conn, "select str_member_contact_list from tbl_member_contact_list where int_member_id='$member_id'");
                                                list($contactlist) = mysqli_fetch_row($rslist);
                                                $contactlist = substr($contactlist, 0, (strlen($contactlist) - 1));
                                                $query_total = mysqli_query($conn, "SELECT int_member_id FROM tbl_member WHERE int_member_id in($contactlist)");
                                            }
                                            elseif ($user_type == 'e' || $user_type == 'c') {
                                                $query_total = mysqli_query($conn, "SELECT ec_id FROM tbl_member WHERE ec_id='$user_amico_id'");
                                            }

                                            $query_red = mysqli_query($conn, "SELECT customers.customers_telephone, customers.customers_telephone1, customers.customers_telephone2 FROM tbl_member, customers WHERE tbl_member.int_customer_id=customers.customers_id
                                               AND tbl_member.ec_id='$user_amico_id' GROUP BY tbl_member.int_member_id");

                                            $count = 0;
                                            while ($f = mysqli_fetch_array($query_red)) {

                                                $member_phone = '1' . str_replace(array(" ", ')', '(', '-', '.'), "", $f['customers_telephone']);
                                                $member_phone1 = '1' . str_replace(array(" ", ')', '(', '-', '.'), "", $f['customers_telephone1']);
                                                $member_phone2 = '1' . str_replace(array(" ", ')', '(', '-', '.'), "", $f['customers_telephone2']);

                                                $member_phone = addslashes($member_phone);
                                                $member_phone1 = addslashes($member_phone1);
                                                $member_phone2 = addslashes($member_phone2);

                                                $sql_test = "SELECT tbl_calls.id FROM tbl_calls WHERE (calldestination='$member_phone' OR calldestination='$member_phone1' OR calldestination='$member_phone2')
                                            AND billabletime>'$billabletime'
                                            AND calldate<='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - $i, 31, date("Y"))) . "'
                                            AND calldate>='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - $i, 1, date("Y"))) . "'";

                                                $query_test = mysqli_query($conn, $sql_test);
                                                if (mysqli_num_rows($query_test) > 0) {
                                                    $count++;
                                                }
                                            }

                                            $query_yellow = mysqli_query($conn, "SELECT bw_invoice_line_items.FKEntity FROM bw_invoices, bw_invoice_line_items, tbl_member
                                                WHERE tbl_member.ec_id='$user_amico_id' AND bw_invoices.ID=tbl_member.amico_id AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity
                                                AND bw_invoices.OrderDate>='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - $i, 1, date("Y"))) . "'
                                                AND bw_invoices.OrderDate<='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - $i, 31, date("Y"))) . "'
                                                GROUP BY tbl_member.amico_id") or die (mysql_error());

                                            if (mysqli_num_rows($query_total) == 0 || $count == 0) {
                                                $contact_rate = "N/A";
                                            } else {
                                                $contact_rate = number_format((($count + mysqli_num_rows($query_yellow)) / mysqli_num_rows($query_total)) * 100, 2) . '%';
                                            }
                                            ?>
                                            <td class=""><?php echo $contact_rate ?></td>


                                            <?php
                                            //Email Persentage

                                            $query = mysqli_query($conn, "SELECT tbl_member_ec.ec_id FROM tbl_member_ec, tbl_member, customers
                                            WHERE customers.customers_email_address!=''
                                            AND customers.customers_id=tbl_member.int_customer_id
                                            AND tbl_member.amico_id=tbl_member_ec.amico_id
                                            AND tbl_member_ec.ec_id = '$user_amico_id'
                                            AND tbl_member_ec.event='added'
                                            AND tbl_member_ec.timestamp<='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - $i, 31, date("Y"))) . "'
                                            GROUP BY tbl_member.amico_id");

                                            $query2 = mysqli_query($conn, "SELECT tbl_member_ec.ec_id FROM tbl_member_ec, tbl_member, customers
                                            WHERE customers.customers_email_address!=''
                                            AND customers.customers_id=tbl_member.int_customer_id
                                            AND tbl_member.amico_id=tbl_member_ec.amico_id
                                            AND tbl_member_ec.ec_id = '$user_amico_id'
                                            AND tbl_member_ec.event='removed'
                                            AND tbl_member_ec.timestamp<='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - $i, 31, date("Y"))) . "'
                                            GROUP BY tbl_member.amico_id");

                                            $value = (mysqli_num_rows($query) + mysqli_num_rows($query2));

                                            $query = mysqli_query($conn, "SELECT tbl_member_ec.ec_id FROM tbl_member_ec, tbl_member, customers
                                           WHERE customers.customers_id=tbl_member.int_customer_id
                                           AND tbl_member.amico_id=tbl_member_ec.amico_id
                                           AND tbl_member_ec.ec_id = '$user_amico_id'
                                           AND tbl_member_ec.event='added'
                                           AND tbl_member_ec.timestamp<='" . date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - $i, 31, date("Y"))) . "'
                                           GROUP BY tbl_member.amico_id");
                                            $value2 = (mysqli_num_rows($query));

                                            $value = number_format((($value / $value2) * 100), 2);
                                            ?>
                                            <td class=""><?php echo $value ?>%</td>

                                        </tr>

                                        <?php
                                        $i++;
                                    }

                                } else {
                                    echo '<tr><td class="text-center" colspan="100%">No Data Found!</td>';
                                }
                                ?>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

<?php
require_once("templates/footer.php");