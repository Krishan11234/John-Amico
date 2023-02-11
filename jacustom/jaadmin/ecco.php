<?php
$page_name = 'ECCO Report';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


$member_type_name = 'Member';
$member_type_name_plural = 'Members';
$self_page = 'ecco.php';
$page_url = base_admin_url() . '/ecco.php?1=1';
$action_page = 'ecco.php';
$action_page_url = base_admin_url() . '/ecco.php?1=1';
$export_url = base_admin_url() . '/ecco.php';

if ($filter_month=="") {$filter_month=date("m");};
if ($filter_year=="") {$filter_year=date("Y");};


$table_headers = array('EC ID', 'Month', 'Total Sales', 'Trend, %', 'Y over Y', 'Members Number', 'Members ordered', 'Members ordered, %', 'New Members', 'Lost Members', 'DVPC', 'Biominoil', 'Contcat Rate, %', 'Email, %');


$main_query = mysqli_query($conn,"select * from tbl_member where mtype='e' ORDER BY amico_id");
?>

<script>var collapse_left_sidebar=true;</script>

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
            <div class="col-xs-12">
                <header class="panel-heading">
                    <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                </header>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-12 filter_wrapper ">
                            <div class="table-responsive">
                                <div class="col-lg-4 col-md-8 col-sm-10 col-xs-12 centering date_range_wrapper">
                                    <form method="POST">
                                        <table class="table table-bordered table-striped mb-none">
                                            <tr>
                                                <td align="center" colspan="2"><strong>Date Range Selection</strong></td>
                                            </tr>
                                            <tr>
                                                <td>Report Month</td>
                                                <td>
                                                    <select name="filter_month">
                                                        <?php
                                                        $months = array('January','February','March','April','May','June','July','August','September','October','November','December');
                                                        $i = 1;
                                                        foreach($months as $month) {
                                                            $selected = ( ($i == $filter_month ) ? 'selected' : '' );
                                                            echo "<option value='$i' $selected>$month</option>";
                                                            $i++;
                                                        }
                                                        ?>
                                                    </select> /
                                                    <select name="filter_year">
                                                        <?php
                                                        $start=2006;
                                                        $end=date("Y");

                                                        while ($start<($end+1)) {
                                                            $selected = ( ($start == $filter_year ) ? 'selected' : '' );
                                                            echo '<option value="'.$start.'" '.$selected.'>'.$start.'</option>';
                                                            $start++;
                                                        };

                                                        ?>

                                                    </select>
                                                </td>

                                            </tr>
                                            <tr>
                                                <td align="center" colspan="2">
                                                    <input type="submit" class="mb-xs mt-xs mr-xs btn btn-xs btn-primary" value="Submit">
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php //require_once('display_members_data.php'); ?>
                    <div class="row">
                        <div class="col-xs-12 data_wrapper ">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-none">
                                    <thead>
                                        <tr><th><?php echo implode('</th><th>', $table_headers); ?></th></tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    while ($fff=mysqli_fetch_array($main_query)) {
                                        $user_amico_id = $fff['amico_id'];

                                        ?>


                                        <?php
                                        $months_till_2006=round((mktime()-mktime(0,0,0,01,01,2006))/(86400*31));

                                        $search_start=mktime(0,0,0,$filter_month,01,$filter_year);

                                        $i=0;
                                        while ($i<4) {
                                            $minus_months=3-$i;

//Total Sales
                                            $sql = "
	SELECT *
	FROM bw_invoices, bw_invoice_line_items, tbl_member
	WHERE tbl_member.ec_id = '".$user_amico_id."' AND bw_invoices.ID=tbl_member.amico_id AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity
	AND bw_invoices.OrderDate>='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$minus_months, 01, $filter_year))."' AND bw_invoices.OrderDate<='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$minus_months, 31, $filter_year))."'";
                                            $query=mysqli_query($conn,$sql) or die (mysql_error());
                                            $value=0;
                                            while ($f=mysqli_fetch_array($query)) {
                                                $value=$value+$f['ShipQty']*$f['UnitPrice'];
                                            };
                                            //echo $sql; echo '<br/>';
                                            $array[$minus_months][1]=$value;
                                            $i++;
                                            //debug(false, false, $sql, $array);
                                        };
                                        echo "<tr>";


//EC ID
                                        echo '<td nowrap>'.$user_amico_id.'</td>';

//Date
                                        echo "<td nowrap>".date("M, Y", mktime (0,0,0,$filter_month, 01, $filter_year))."</td>";


                                        $i=0;

//Total Sales
                                        echo "<td>$".number_format($array[0][1], 2)."</td>";

//Total sales % up or down
                                        if (($array[(1)][1]+$array[(2)][1]+$array[(3)][1])==0) {$value="N/A";} else {
                                            $prev_months_avg = ( ($array[$i+1][1] + $array[$i+2][1] + $array[$i+3][1] ) / 3);
                                            $value=round( ($array[$i][1] - $prev_months_avg ) / ( $prev_months_avg * 100 ) ,2);

                                            if ($value>0) {
                                                $value="+".$value;
                                            }
                                            $value=$value.'%';
                                        }
                                        echo "<td>".$value."</td>";


//Y over Y
                                        $sql = "
	SELECT *
	FROM bw_invoices, bw_invoice_line_items, tbl_member
	WHERE tbl_member.ec_id = '".$user_amico_id."' AND bw_invoices.ID=tbl_member.amico_id AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity
	AND bw_invoices.OrderDate>='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month, 1, $filter_year-1))."' AND bw_invoices.OrderDate<='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month, 31, $filter_year-1))."'";
                                        $query=mysqli_query($conn,$sql) or die (mysql_error());
                                        $value=0;
                                        while ($f=mysqli_fetch_array($query)) {
                                            $value=$value+$f['ShipQty']*$f['UnitPrice'];
                                        };

                                        if ($value==0) {
                                            echo '<td nowrap>0.00%</td>';
                                        } else {
                                            echo "<td nowrap>".number_format((($array[$i][1]-$value)/$value)*100 ,2)."%</td>";
                                        };


//Number of members
                                        $query=mysqli_query($conn,"SELECT * FROM tbl_member_ec, tbl_member WHERE tbl_member.amico_id=tbl_member_ec.amico_id AND tbl_member_ec.ec_id = '".$user_amico_id."' AND event='added' AND timestamp<='".date("Y-m-d H:i:s", mktime (0,0,0,date("m"), 31, date("Y")))."' GROUP BY tbl_member_ec.amico_id") or die (mysql_error());
                                        $query2=mysqli_query($conn,"SELECT * FROM tbl_member_ec, tbl_member WHERE tbl_member.amico_id=tbl_member_ec.amico_id AND tbl_member_ec.ec_id = '".$user_amico_id."' AND event='removed' AND timestamp<='".date("Y-m-d H:i:s", mktime (0,0,0,date("m"), 31, date("Y")))."' GROUP BY tbl_member_ec.amico_id") or die (mysql_error());
                                        $query3=mysqli_query($conn,"SELECT amico_id FROM customers c inner join tbl_member m ON c.customers_id=m.int_customer_id left outer join address_book a on c.customers_id=a.customers_id  WHERE m.ec_id='$user_amico_id' AND m.bit_active='1' GROUP BY m.amico_id");
                                        $delta=mysqli_num_rows($query3)-(mysqli_num_rows($query)-mysqli_num_rows($query2));

                                        $query=mysqli_query($conn,"SELECT * FROM tbl_member_ec, tbl_member WHERE tbl_member.amico_id=tbl_member_ec.amico_id AND tbl_member_ec.ec_id = '".$user_amico_id."' AND event='added' AND timestamp<='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$i, 31, $filter_year))."' GROUP BY tbl_member_ec.amico_id") or die (mysql_error());
                                        $query2=mysqli_query($conn,"SELECT * FROM tbl_member_ec, tbl_member WHERE tbl_member.amico_id=tbl_member_ec.amico_id AND tbl_member_ec.ec_id = '".$user_amico_id."' AND event='removed' AND timestamp<='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$i, 31, $filter_year))."' GROUP BY tbl_member_ec.amico_id") or die (mysql_error());
                                        $value=(mysqli_num_rows($query)-mysqli_num_rows($query2))+$delta;

                                        $array[$i][4]=$value;
                                        echo "<td>".$value."</td>";



//Number of members ordered
                                        $query = "
	SELECT *
	FROM bw_invoices, bw_invoice_line_items, tbl_member
	WHERE tbl_member.ec_id = '".$user_amico_id."' AND bw_invoices.ID=tbl_member.amico_id AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity
	AND bw_invoices.OrderDate>='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$i, 1, $filter_year))."' AND bw_invoices.OrderDate<='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$i, 31, $filter_year))."' GROUP BY tbl_member.amico_id";
                                        $query=mysqli_query($conn,$query) or die (mysql_error());
                                        $value=mysqli_num_rows($query);

                                        $array[$i][5]=$value;
                                        echo "<td>".$value."</td>";



//% of mbrs ordered
                                        if ($array[$i][4]=="0") {$value="N/A";} else {$value=round(($array[$i][5]/$array[$i][4])*100, 2)."%";};
                                        echo "<td>".$value."</td>";



//New Members
                                        $query=mysqli_query($conn,"SELECT * FROM tbl_member_ec WHERE ec_id = '".$user_amico_id."' AND event='added' AND timestamp<='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$i, 31, $filter_year))."' AND timestamp>='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$i, 1, $filter_year))."'") or die (mysql_error());
                                        $value=(mysqli_num_rows($query));
                                        echo "<td>".$value."</td>";


//Lost Members
                                        $query=mysqli_query($conn,"SELECT * FROM tbl_member_ec WHERE ec_id = '".$user_amico_id."' AND event='removed' AND timestamp<='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$i, 31, $filter_year))."' AND timestamp>='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$i, 1, $filter_year))."'") or die (mysql_error());
                                        $value=(mysqli_num_rows($query));
                                        echo "<td>".$value."</td>";



//DVPC
                                        if ($array[$i][5]==0) {$value="N/A";} else {
                                            $value=round(($array[$i][1]/$array[$i][5]), 2);
                                            $value="$".number_format($value,2);
                                        };
                                        echo "<td>".$value."</td>";


//Biominoil
                                        $query = "
	SELECT *
	FROM bw_invoices, bw_invoice_line_items, tbl_member
	WHERE tbl_member.ec_id = '".$user_amico_id."' AND bw_invoices.ID=tbl_member.amico_id AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity
	AND (bw_invoice_line_items.ID='BIOMIN' OR bw_invoice_line_items.ID='BIOMIN3') AND bw_invoices.OrderDate>='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$i, 1, $filter_year))."' AND bw_invoices.OrderDate<='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$i, 31, $filter_year))."' GROUP BY tbl_member.amico_id";
                                        $query=mysqli_query($conn,$query) or die (mysql_error());
                                        $value=mysqli_num_rows($query);

                                        $array[$i][10]=$value;
                                        echo "<td>".$value."</td>";


//Contact rate

                                        $query_total=mysqli_query($conn,"SELECT ec_id FROM tbl_member WHERE ec_id='".$user_amico_id."'") or die (mysql_error());

                                        $query_red=mysqli_query($conn,"SELECT customers_telephone, customers_telephone1, customers_telephone2 FROM tbl_member, customers WHERE tbl_member.int_customer_id=customers.customers_id AND tbl_member.ec_id='".$user_amico_id."' GROUP BY tbl_member.int_member_id") or die (mysql_error());
                                        $count=0;
                                        while ($f=mysqli_fetch_array($query_red)) {

                                            $member_phone=str_replace(" ", "", $f['customers_telephone']);
                                            $member_phone=str_replace("(", "", $member_phone);
                                            $member_phone=str_replace(")", "", $member_phone);
                                            $member_phone=str_replace("-", "", $member_phone);
                                            $member_phone='1'.str_replace(".", "", $member_phone);

                                            $member_phone1=str_replace(" ", "", $f['customers_telephone1']);
                                            $member_phone1=str_replace("(", "", $member_phone1);
                                            $member_phone1=str_replace(")", "", $member_phone1);
                                            $member_phone1=str_replace("-", "", $member_phone1);
                                            $member_phone1='1'.str_replace(".", "", $member_phone1);

                                            $member_phone2=str_replace(" ", "", $f['customers_telephone2']);
                                            $member_phone2=str_replace("(", "", $member_phone2);
                                            $member_phone2=str_replace(")", "", $member_phone2);
                                            $member_phone2=str_replace("-", "", $member_phone2);
                                            $member_phone2='1'.str_replace(".", "", $member_phone2);


                                            $member_phone=addslashes($member_phone);
                                            $member_phone1=addslashes($member_phone1);
                                            $member_phone2=addslashes($member_phone2);

//echo "SELECT id FROM tbl_calls WHERE (calldestination='".$member_phone."' OR calldestination='".$member_phone1."' OR calldestination='".$member_phone2."') AND billabletime>'$billabletime' AND calldate<='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$i, 31, $filter_year))."' AND calldate>='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$i, 1, $filter_year))."'";
//die ('stop!');

                                            $query_test=mysqli_query($conn,"SELECT id FROM tbl_calls WHERE (calldestination='".$member_phone."' OR calldestination='".$member_phone1."' OR calldestination='".$member_phone2."') AND billabletime>'$billabletime' AND calldate<='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$i, 31, $filter_year))."' AND calldate>='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$i, 1, $filter_year))."'") or die (mysql_error());
                                            if (mysqli_num_rows($query_test)>0) {
                                                $count++;
                                            };
                                        };

                                        $query_yellow = mysqli_query($conn,"SELECT InvoiceNo  FROM bw_invoices, bw_invoice_line_items, tbl_member WHERE tbl_member.ec_id='".$user_amico_id."' AND bw_invoices.ID=tbl_member.amico_id AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity AND bw_invoices.OrderDate>='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$i, 1, $filter_year))."' AND bw_invoices.OrderDate<='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$i, 31, $filter_year))."' GROUP BY tbl_member.amico_id") or die (mysql_error());

                                        if (mysqli_num_rows($query_total)==0 || $count==0) {
                                            $contact_rate="N/A";} else {
                                            $contact_rate=number_format((($count+mysqli_num_rows($query_yellow))/mysqli_num_rows($query_total))*100, 2).'%';
                                        };
                                        echo "<td>".$contact_rate."</td>";


//Email Persentage
                                        $query=mysqli_query($conn,"SELECT * FROM tbl_member_ec, tbl_member, customers WHERE customers.customers_email_address!='' AND customers.customers_id=tbl_member.int_customer_id AND tbl_member.amico_id=tbl_member_ec.amico_id AND tbl_member_ec.ec_id = '".$user_amico_id."' AND tbl_member_ec.event='added' AND tbl_member_ec.timestamp<='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$i, 31, $filter_year))."'") or die (mysql_error());
                                        $query2=mysqli_query($conn,"SELECT * FROM tbl_member_ec WHERE ec_id = '".$user_amico_id."' AND event='removed' AND timestamp<='".date("Y-m-d H:i:s", mktime (0,0,0,$filter_month-$i, 31, $filter_year))."'") or die (mysql_error());
                                        $value=(mysqli_num_rows($query)-mysqli_num_rows($query2));
                                        if ($array[$i][4]==0) {$value=0;} else {
                                            $value=number_format((($value/$array[$i][4])*100), 2);
                                        };
                                        echo "<td>".$value."%</td>";


                                        echo "<tr>";

                                        ?> <?};
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
        </section>
    </div>
</div>


<?php
require_once("templates/footer.php");