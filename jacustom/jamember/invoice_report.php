<?php
$page_name = 'Invoice Report';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

$member_id = $_SESSION['member']['ses_member_id'];

if (isset($_POST['adminid']) and $_POST['adminid'] > 0) {
    $rsseladmin = mysqli_query($conn, "select * from tbl_admin WHERE int_admin_id = {$_POST['adminid']}");
    $nr = "NO";
    list($adminid, $firstname, $lastname, $admin_username, $admin_pass, $email, $active)
        = mysqli_fetch_row($rsseladmin);
}
else {
    $nr = "YES";
}

$start_time = time()-2592000;

$start_date_d = ( !empty($_REQUEST['start_day']) ? $_REQUEST['start_day'] : date('d', $start_time) );
$start_date_m = ( !empty($_REQUEST['start_month']) ? $_REQUEST['start_month'] : date('m', $start_time) );
$start_date_y = ( !empty($_REQUEST['start_year']) ? $_REQUEST['start_year'] : date('Y', $start_time) );
$end_date_d = ( !empty($_REQUEST['end_day']) ? $_REQUEST['end_day'] : date('d') );
$end_date_m = ( !empty($_REQUEST['end_month']) ? $_REQUEST['end_month'] : date('m') );
$end_date_y = ( !empty($_REQUEST['end_year']) ? $_REQUEST['end_year'] : date('Y') );


$sql = "SELECT amico_id FROM tbl_member WHERE int_member_id = '$member_id'";
$result = mysqli_query($conn,$sql);
$amico_id = mysqli_result($result, 0);

$sql = "SELECT * FROM bw_invoices WHERE ID = '$amico_id' AND (InvoiceDate >= '$start_date_y-$start_date_m-$start_date_d' AND InvoiceDate <= '$end_date_y-$end_date_m-$end_date_d')";
$iresult = mysqli_query($conn,$sql);
$result_count = mysqli_num_rows($iresult);

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

        <div class="row">
            <div class="col-xs-12">
                <div class="panel-body">
                    <div class="table-responsive">
                        <div class="col-lg-4 col-md-8 col-sm-10 col-xs-12 centering date_range_wrapper">
                            <form method="get">
                                <table class="table table-bordered table-striped mb-none">
                                    <tr>
                                        <td align="center" colspan="2"><strong>Date Range Selection</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Start Date:</td>
                                        <td>
                                            <select name="start_month">
                                                <?
                                                for ($i = 1; $i <= 12; $i++) {
                                                    $sel = "";
                                                    if ($i == $start_date_m) {
                                                        $sel = "selected";
                                                    }
                                                    echo "<option value=\"$i\" $sel>$i</option>";
                                                }
                                                ?>
                                            </select> /
                                            <select name="start_day">
                                                <?
                                                for ($i = 1; $i <= 31; $i++) {
                                                    $sel = "";
                                                    if ($i == $start_date_d) {
                                                        $sel = "selected";
                                                    }
                                                    echo "<option value=\"$i\" $sel>$i</option>";
                                                }
                                                ?>
                                            </select> /
                                            <select name="start_year">
                                                <?
                                                for ($i = date("Y") - 2; $i <= date("Y"); $i++) {
                                                    $sel = "";
                                                    if ($i == $start_date_y) {
                                                        $sel = "selected";
                                                    }
                                                    echo "<option value=\"$i\" $sel>$i</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td>End Date:</td>
                                        <td>
                                            <select name="end_month">
                                                <?
                                                for ($i = 1; $i <= 12; $i++) {
                                                    $sel = "";
                                                    if ($i == $end_date_m) {
                                                        $sel = "selected";
                                                    }
                                                    echo "<option value=\"$i\" $sel>$i</option>";
                                                }
                                                ?>
                                            </select> /
                                            <select name="end_day">
                                                <?
                                                for ($i = 1; $i <= 31; $i++) {
                                                    $sel = "";
                                                    if ($i == $end_date_d) {
                                                        $sel = "selected";
                                                    }
                                                    echo "<option value=\"$i\" $sel>$i</option>";
                                                }
                                                ?>
                                            </select> /
                                            <select name="end_year">
                                                <?
                                                for ($i = date("Y") - 2; $i <= date("Y"); $i++) {
                                                    $sel = "";
                                                    if ($i == $end_date_y) {
                                                        $sel = "selected";
                                                    }
                                                    echo "<option value=\"$i\" $sel>$i</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center" colspan="2">
                                            <input type="submit" class="mb-xs mt-xs mr-xs btn btn-xs btn-primary" value="Filter">
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 centering">
                <section class="panel">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                        <?php if( $result_count > 0 ) :?>
                            <div class="text-right">
                                <a href="#" onclick='window.open("print_invoice_report.php?memberid=<?=$member_id?>&start_year=<?=$start_year?>&start_month=<?=$start_month?>&start_day=<?=$start_day?>&end_year=<?=$end_year?>&end_month=<?=$end_month?>&end_day=<?=$end_day?>","Print","scrollbars=yes");'>Printer Friendly</a>
                            </div>
                        <?php endif; ?>
                    </header>
                    <div class="panel-body">
                        <table class="table bg_color__white member_invoices_table" bgcolor="white">
                            <tr>
                                <td width="10%" align="right"><font size="-4"> <?php echo date("M-d-y")?></font></td>
                                <td align="center" class="font-size__20"><b>AMICO EDUCATIONAL CONCEPTS INC</b></td>
                            </tr>
                            <tr>
                                <td width="10%" align="right"><font size="-4"> <?php echo $today?></font></td>
                                <td align="center"><font color="Blue" size="2">ORDER ENTRY INVOICE DETAIL REPORT</font></td>
                            </tr>
                            <tr>
                                <td colspan="2" id="content">
                                    <?php
                                    if($result_count > 0) {
                                        $count = 0;
                                        $count2 = 0;
                                        $inc_value = 100/$result_count;
                                        $progress = 0;
                                        $old_value = 0;

                                        while($irow = mysqli_fetch_array($iresult)) :
                                            list($year,$month,$day) = explode('-',$irow['OrderDate']);
                                            $orddate = date("m|d|Y",mktime(0,0,0,$month,$day,$year));
                                            list($year,$month,$day) = explode('-',$irow['InvoiceDate']);
                                            $invdate = date("m|d|Y",mktime(0,0,0,$month,$day,$year));
                                        ?>

                                            <table border="0" width="100%">
                                                <tr>
                                                    <td><font size="2">Inv.#</font><br><hr color="Black"></td>
                                                    <td><font size="2">Customer ID</font><br><hr color="Black"></td>
                                                    <td><font size="2">Name/Invoice Description</font><br><hr color="Black"></td>
                                                    <td><font size="2">Invoice Information</font><br><hr color="Black"></td>
                                                </tr>

                                                <tr>
                                                    <td valign="top"><font size="2"><?=$irow['InvoiceNo']?></font></td>
                                                    <td valign="top"><font size="2"><?=$amico_id?></font></td>
                                                    <td valign="top"><font size="2"><?=$irow['Name']?></font></td>
                                                    <td>
                                                        <table border="0" width="100%">
                                                            <tr>
                                                                <td align="left"><font size="2">Inv Date:</font></td><td align="left"><font size="2"><?=$invdate?></font></td>
                                                                <td align="right"><font size="2">Rep:</font></td><td align="left"><font size="2"><?=$irow['SalesRepIDNo']?></font></td>
                                                            </tr>
                                                            <tr>
                                                                <td align="left"><font size="2">Ord Date:</font></td><td align="left"><font size="2"><?=$orddate?></font></td>
                                                                <td align="right"><font size="2">Order#:</font></td><td align="left"><font size="2"><?=$irow['OrderNo']?></font></td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>&nbsp;</td>
                                                    <td colspan="3">
                                                        <table border="0" width="100%">
                                                            <tr>
                                                                <td align="center"><font size="2">Item<br><hr size="1" color="Black"></font></td>
                                                                <td align="center"><font size="2">Description<br><hr size="1" color="Black"></font></td>
                                                                <td align="center"><font size="2">SA<br><hr size="1" color="Black"></font></td>
                                                                <td align="center"><font size="2">Qty<br><hr size="1" color="Black"></font></td>
                                                                <td align="center"><font size="2">Price<br><hr size="1" color="Black"></font></td>
                                                                <td align="center"><font size="2">Amount<br><hr size="1" color="Black"></font></td>
                                                            </tr>
                                                            <?php
                                                            $sql = "SELECT ID, Description, ShipQty, UnitPrice FROM bw_invoice_line_items WHERE FKEntity = $irow[SKOEInvoice] ORDER BY ID";
                                                            $liresult = mysqli_query($conn,$sql);

                                                            $totalamt=0;
                                                            while($lirow = mysqli_fetch_array($liresult)) {
                                                                ?>
                                                                <tr>
                                                                    <td><font size="2"><?=$lirow['ID']?></font></td>
                                                                    <td><font size="2"><?=$lirow['Description']?></font></td>
                                                                    <td><font size="2">&nbsp;</font></td>
                                                                    <td align="right"><font size="2"><?=$lirow['ShipQty']?></font></td>
                                                                    <td align="right"><font size="2"><?=number_format($lirow['UnitPrice'],2)?></font></td>
                                                                    <td align="right"><font size="2"><?=number_format($lirow['ShipQty']*$lirow['UnitPrice'],2)?></font></td>
                                                                </tr>
                                                                <?php
                                                                $totalamt=$totalamt+$lirow['ShipQty']*$lirow['UnitPrice'];
                                                            }?>
                                                            <tr>
                                                                <td colspan="5" align="right" valign="bottom"><font size="2">Invoice Total</font></td>
                                                                <td align="right"><font size="2"><hr size="1"><br><?=number_format($totalamt,2)?></font></td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" align="right"><hr color="Black"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" align="right">&nbsp;</td>
                                                </tr>
                                            </table>

                                        <?php
                                        endwhile;
                                    } else {
                                        ?>
                                        <br><center><font size="2" face="Verdana, Arial, Helvetica, sans-serif">No Invoices Found in Selected Date Range</font></center>
                                        <?
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </section>
            </div>
        </div>



    </div>

<?php
require_once("templates/footer.php");

