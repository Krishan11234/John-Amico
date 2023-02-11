<?php
$page_name = 'Scorecard';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


function calculateSales($year = "") {
    global $conn;

    $member_id = $_SESSION['member']['ses_member_id'];

    if(date("m") >= 11) {
        if($year == "last") {
            $last_year = date('Y')-1;
            $second_year = date('Y');
        } else {
            $last_year = date('Y') ;
            $second_year = date('Y') + 1;
        }
    } else {
        if($year == "last") {
            $last_year = date('Y')-2;
            $second_year = date('Y')-1;
        } else {
            $last_year = date('Y') - 1;
            $second_year = date('Y');
        }
    }


//	$sql = "SELECT b.* FROM bw_invoices b, tbl_member t WHERE t.amico_id = b.ID AND b.InvoiceDate >='".$last_year."-01-01' AND b.InvoiceDate <= '".$last_year."-12-12' AND t.int_member_id = '".$_GET[memberid]."'";
    $sql = "SELECT b.* FROM bw_invoices b, tbl_member t WHERE t.amico_id = b.ID AND b.InvoiceDate >='".$last_year."-11-01' AND b.InvoiceDate <= '".$second_year."-10-31' AND t.int_member_id = '$member_id'";
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
    global $bg;

    $row = "<tr bgcolor=\"".$bg."\">";
    $goal = calculateSales("last") + $amount;
    $remaining = $goal - calculateSales();

    if($remaining < 0) {
        $remaining = "<font color=\"green\"><b>won</b></font>";
    } else {
        $remaining = "$".number_format($remaining, 2);
    }

    if($num == "1") { $num = "Win 15%"; }
    if($num == "2") { $num = "Win 30%"; }
    if($num == "3") { $num = "Win 50%"; }
    if($num == "4") { $num = "Win 75%"; }
    if($num == "5") { $num = "Win 100%"; }

    $row.= 	"<td class=\"copysmallblack\" align=\"center\" >".$num."</td>";
    $row.= 	"<td class=\"copysmallblack\" align=\"center\" >$".number_format(calculateSales("last"),2)."</td>";
    $row.= 	"<td class=\"copysmallblack\" align=\"center\" >$".number_format($goal,2)."</td>";
    $row.= 	"<td class=\"copysmallblack\" align=\"center\" >$".number_format(calculateSales(),2)."</td>";
    $row.= 	"<td class=\"copysmallblack\" align=\"center\">".$remaining."</td>";

    return $row;
}

?>

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
            <div class="col-md-12 col-xs-12 centering">
                <section class="panel">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center">JOHN AMICO <?php echo $page_name; ?></h2>
                    </header>
                    <div class="panel-body ">
                        <div class="row">
                            <section class="panel">
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped text-center">
                                            <tr valign="baseline" class="loginsmall">
                                                <th class="text-center">Prize</th>
                                                <th class="text-center">Last Year</th>
                                                <th class="text-center">Your Cruise Goals</th>
                                                <th class="text-center">Score</th>
                                                <th class="text-center">Goal Remaining</th>
                                            </tr>
                                            <?php
                                            echo calculateGoal();
                                            $amount = 1000;
                                            $num = 1;
                                            for($i=0;$i<=3;$i++) {
                                                $num++;
                                                if($bg){unset($bg);}else{$bg="#FFFFFF";}
                                                $amount = $amount + 1000;
                                                echo calculateGoal($amount, $num);
                                            }

                                            for($i=0;$i<=7;$i++) {
                                                $num++;
                                                if($bg){unset($bg);}else{$bg="#FFFFFF";}
                                                $amount = $amount + 2500;
                                                echo calculateGoal($amount, $num);
                                            }
                                            ?>
                                        </table>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>


<?php
require_once("templates/footer.php");

