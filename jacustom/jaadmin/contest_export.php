<?php
set_time_limit(18000);
require_once("../common_files/include/global.inc");
require_once("session_check.inc");

if( empty($_GET['download']) || ($_GET['download'] != 1) ) {

    $page_title = 'John Amico - Manage Admin emails';

    require_once("templates/header.php");
    require_once("templates/sidebar.php");

} else if( !empty($_GET['download']) && ($_GET['download'] = 1) ) {

    Header("Content-type: application/vnd.ms-excel; name='excel'");
    Header("Content-Disposition: attachment; filename=contest-export.xls");
    Header("Content-Description: Excel output");

    echo "Member ID \tFirst Name \tLast Name \tAddress \tCity \tState \tZip \tPhone \tEC \tLast Year \tCurrent Score \t1 Goal R \t2 Goal R \t3 Goal R \t4 Goal R \t5 Goal R \t6 Goal R \t7 Goal R \t8 Goal R \t9 Goal R \t10 Goal R \t11 Goal R \t12 Goal R \t\n";

    $sql = "SELECT c. * , t.amico_id, t.ec_id, a. * , z.zone_name
            FROM tbl_member t, customers c, address_book a, zones z
            WHERE t.int_customer_id = c.customers_id
            AND c.customers_default_address_id = a.address_book_id
            AND c.customers_id = a.customers_id
            AND z.zone_id = a.entry_zone_id
            AND t.bit_active = 1";
    $res = mysqli_query($conn, $sql) or die(mysql_error());
    while ($row = mysqli_fetch_assoc($res)) {
        $amico_id = $row['amico_id'];

        $sql2 = "SELECT c.customers_firstname, c.customers_lastname FROM customers c, tbl_member t WHERE t.int_customer_id=c.customers_id AND t.amico_id = '" . $row['ec_id'] . "'";
        $res2 = mysqli_query($conn, $sql2) or die(mysql_error());
        $row2 = mysqli_fetch_assoc($res2);
        $ec = $row2['customers_firstname'] . " " . $row2['customers_lastname'];

        echo $row['amico_id'] . " \t";
        echo $row['customers_firstname'] . " \t";
        echo $row['customers_lastname'] . " \t";
        echo $row['entry_street_address'] . " \t";
        echo $row['entry_city'] . " \t";
        echo $row['zone_name'] . " \t";
        echo $row['entry_postcode'] . " \t";
        echo $row['customers_telephone'] . " \t";
        echo $ec . " \t";
        echo "$" . number_format(calculateSales("last", $amico_id), 2) . " \t";
        echo "$" . number_format(calculateSales("", $amico_id), 2) . " \t";

        for ($i = 0; $i <= 4; $i++) {
            $amount = $amount + 1000;
            echo calculateGoal($amount, $num);

        }
        for ($i = 0; $i <= 7; $i++) {
            $amount = $amount + 2500;
            echo calculateGoal($amount, $num);
        }

        echo " \t\n";
        $amount = 0;
    }
}


function calculateSales($year = "", $amico_id = "") {
    global $conn;

    if(date("m") >= 11)
    {
        if($year == "last")
        {
            $last_year = date('Y')-1;
            $second_year = date('Y');
        }
        else
        {
            $last_year = date('Y') ;
            $second_year = date('Y') + 1;
        }
    }
    else
    {
        if($year == "last")
        {
            $last_year = date('Y')-2;
            $second_year = date('Y')-1;
        }
        else
        {
            $last_year = date('Y') - 1;
            $second_year = date('Y');
        }

    }
//	$sql = "SELECT b.* FROM bw_invoices b, tbl_member t WHERE t.amico_id = b.ID AND b.InvoiceDate >='".$last_year."-01-01' AND b.InvoiceDate <= '".$last_year."-12-12' AND t.amico_id = '".$amico_id."'";
    $sql = "SELECT b.* FROM bw_invoices b, tbl_member t WHERE t.amico_id = b.ID AND b.InvoiceDate >='".$last_year."-11-01' AND b.InvoiceDate <= '".$second_year."-10-31' AND t.amico_id = '".$amico_id."'";
    $res = mysqli_query($conn,$sql) or die(mysql_error());

    $total = 0;
    while($row = mysqli_fetch_assoc($res))
    {
        $sql2 = "SELECT Description, ID, (UnitPrice * ShipQty) as total, UnitPrice, ShipQty FROM bw_invoice_line_items WHERE FKEntity = '".$row['SKOEInvoice']."'";
        $res2 = mysqli_query($conn,$sql2) or die(mysql_error());
        $second_total = 0;
        while($row2 = mysqli_fetch_assoc($res2))
        {
            $second_total+=$row2['total'];
            $total+=$row2['total'];
        }
    }

    return round($total,2);
}
function calculateGoal($amount = "1000", $num = 1) {
    global $bg, $amico_id, $conn;

    $row = "";
    $goal = calculateSales("last", $amico_id) + $amount;
    $remaining = $goal - calculateSales("", $amico_id);

    if($remaining < 0)
    {
        $remaining = "won";
    }
    else
    {
        $remaining = "$".number_format($remaining, 2);
    }

    $row.= 	$remaining."\t";

    return $row;
}

?>

    <div role="main" class="content-body">
        <header class="page-header">
            <h2>Export Contest to Excel</h2>

            <div class="right-wrapper pull-right">
                <ol class="breadcrumbs">
                    <li>
                        <a href="<?php echo base_admin_url(); ?>">
                            <i class="fa fa-home"></i>
                        </a>
                    </li>
                    <li><span>Export Contest to Excel</span></li>
                </ol>


                <a class="sidebar-right-toggle"></a>
            </div>
        </header>

        <div class="row admin-control">
            <div class="col-lg-4 col-md-8 col-sm-10 col-xs-12 centering">
                <div class="panel panel-primary">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-10 col-xs-offset-1 text-center">
                                <a href="<?php echo base_admin_url(); ?>/contest_export.php?download=1">
                                    <i class="fa fa-download fa-3" aria-hidden="true"></i>
                                    <h5>Export to Spreadsheet</h5>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?php
require_once("templates/footer.php");
