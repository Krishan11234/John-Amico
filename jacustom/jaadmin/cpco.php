<?php
$page_name = 'CPCO Report';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


$member_type_name = 'Member';
$member_type_name_plural = 'Members';
$self_page = 'cpco.php';
$page_url = base_admin_url() . '/cpco.php?1=1';
$action_page = 'cpco.php';
$action_page_url = base_admin_url() . '/cpco.php?1=1';
$export_url = base_admin_url() . '/cpco.php';

if ($filter_month=="") {$filter_month=date("m");};
if ($filter_year=="") {$filter_year=date("Y");};


$table_headers = array('ID#', 'Population', 'Chapter Membership', 'Active Membership', 'Penetration Percentage', 'Active Chapter Percentage', 'Market Share Percentage');

$query = "
	SELECT *
	FROM tbl_member
	WHERE tbl_member.mtype = 'c'
	AND amico_id LIKE 'c%' OR amico_id LIKE 'C%'
	AND amico_id NOT LIKE 'CON%'
	ORDER BY amico_id
	";

$main_query=mysqli_query($conn,$query) or die (mysql_error());

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
                                    while ($f=mysqli_fetch_array($main_query)) {
                                        echo '<tr>';
                                        //User ID
                                        $user_id=$f['amico_id'];
                                        echo '<td nowrap>'.$f['amico_id'].'</td>';


                                        //Population
                                        $population=$f['growth'];
                                        echo '<td nowrap>'.$f['growth'].'&nbsp;</td>';


                                        //Chapter membership
                                        $res=mysqli_query($conn,"select amico_id from tbl_member where chapter_id='".$f['amico_id']."'");
                                        $cm=mysqli_num_rows($res);
                                        echo '<td nowrap>'.mysqli_num_rows($res).'</td>';


                                        //Active Membership
                                        $res = "
                                            SELECT *
                                            FROM bw_invoices, bw_invoice_line_items, tbl_member
                                            WHERE tbl_member.chapter_id = '".$user_id."' AND bw_invoices.ID=tbl_member.amico_id AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity
                                            AND bw_invoices.OrderDate>='".date("Y-m-d H:i:s", mktime(0,0,0,$filter_month,1,$filter_year)-7776000)."' GROUP BY tbl_member.amico_id";
                                        $res=mysqli_query($conn,$res) or die (mysql_error());
                                        $am=mysqli_num_rows($res);
                                        echo '<td nowrap>'.mysqli_num_rows($res).'</td>';


                                        //Penetration percentage
                                        if (intval($population)==0) {$pp=0;} else {$pp=($cm/$population)*100;};
                                        echo '<td nowrap>'.number_format($pp, 2).'%</td>';


                                        //Active Chapter percentage
                                        if ($cm==0) {$acp=0;} else {$acp=($am/$cm)*100;};
                                        echo '<td nowrap>'.number_format($acp, 2).'%</td>';


                                        //Market Share Percentage
                                        if (intval($population)==0) {$msp=0;} else {$msp=($am/$population)*100;};
                                        echo '<td nowrap>'.number_format($msp, 2).'%</td>';

                                        echo '</tr>';
                                    }
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