<?php
$page_name = 'View Per Member Commission';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


$member_type_name = 'Video';
$member_type_name_plural = 'Videos';
$self_page = 'videos.php';
$page_url = base_admin_url() . '/videos.php?1=1';
$action_page = 'videos.php';
$action_page_url = base_admin_url() . '/videos.php?1=1';
$export_url = base_admin_url() . '/videos.php';

$mesg = '';

$is_view = false;

$useMagento = true;

if(!empty($_GET['goto'])) {
    switch($_GET['goto']) {
        case 'view' :
            if( !empty( $_GET['amico_id'] ) && (!empty($_GET['report_id']) && is_numeric($_GET['report_id']) ) ) {
                $report_id = filter_var($_GET['report_id'], FILTER_SANITIZE_STRING);
                $amico_id = mysqli_real_escape_string($conn, $_GET['amico_id']);

                $id = $report_id;

                $is_view = true;
            }
            break;
    }
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
            <div class="col-lg-6 col-md-8 col-xs-12 centering">
                <section class="panel">
                    <form name="show_commissions" class="form form-validate form-bordered" action="" method="get">
                        <header class="panel-heading">
                            <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                        </header>
                        <div class="panel-body ">
                            <div class="row">
                                <div class="col-xs-12 centering">
                                    <div class="panel-body pb-lg pt-lg mb-lg mt-lg">
                                        <div class="row form-group">
                                            <label class="col-md-4 control-label" for="report_id">STW Report</label>
                                            <div class="col-md-8">
                                                <select name="report_id" id="report_id" class="form-control" required>
                                                    <option value="">Select STW Report</option>
                                                    <?php
                                                    $sql = "SELECT report_id, DATE_FORMAT(report_time, '%b %D, %Y - %l:%i%p') AS report_date FROM stw_reports ORDER BY report_time DESC";
                                                    $result = mysqli_query($conn,$sql);

                                                    while($row = mysqli_fetch_array($result)) {
                                                    ?>
                                                        <option value="<?php echo $row['report_id']?>"<?if(($is_view) && (!empty($report_id)) && ($row['report_id'] == $report_id)){?> selected <?php } ?>><?php echo $row['report_date'];?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row form-group">
                                            <label class="col-md-4 control-label" for="amico_id">Enter an Amico ID</label>
                                            <div class="col-md-8 form-inline">
                                                <?php
                                                $sql = "SELECT c.*, tm.int_member_id, tm.int_customer_id, tm.amico_id FROM tbl_member tm INNER JOIN customers c ON tm.int_customer_id=c.customers_id WHERE tm.amico_id!='' ORDER BY c.customers_lastname";
                                                $result = mysqli_query($conn,$sql);
                                                $count = 0;

                                                $amico_ids = "";
                                                $member_names = "";
                                                while($row = mysqli_fetch_array($result)) {
                                                    $amico_ids .= "\"".strtolower($row['amico_id'])."\",";
                                                    $member_names .= "\"".addslashes($row['customers_firstname'])." ".addslashes($row['customers_lastname'])."\",";
                                                }
                                                $amico_ids = substr($amico_ids, 0, strlen($amico_ids)-1);
                                                $member_names = substr($member_names, 0, strlen($member_names)-1);
                                                ?>
                                                <script language="javascript">
                                                    function find_member(t, o) {
                                                        var amico_ids = new Array(<?php echo $amico_ids?>);
                                                        var member_names = new Array(<?php echo $member_names?>);
                                                        var str = t.value.toLowerCase();
                                                        var found = false;

                                                        for(count=0;count<amico_ids.length;count++) {
                                                            if(str == amico_ids[count]) {
                                                                found = true;
                                                                valid_id = true;
                                                                o.innerHTML = member_names[count];
                                                                break;
                                                            }
                                                        }

                                                        if(!found) {
                                                            valid_id = false;
                                                            o.innerHTML = "Please Enter a Valid Member ID";
                                                        }

                                                        return;
                                                    }
                                                </script>

                                                <input type="text" class="form-control" name="amico_id" id="amico_id" maxlength="20" value="<?php echo ( ( !empty($is_view) && !empty($amico_id) ) ? $amico_id : '' ) ; ?>" required oninput="find_member(this, document.getElementById('member'));" onkeyup="find_member(this, document.getElementById('member'));" >
                                                <p class="form-control-static">
                                                    <?php if( !empty($is_edit) ) { ?>
                                                        <?php if( isset($ec_id) ) { ?>
                                                            <?php if($ec_id>0) { ?>
                                                                <strong id="member"><?php if(!empty($ec_first_name)) echo $ec_first_name; ?> <?php if(!empty($ec_last_name)) echo $ec_last_name; ?></strong>
                                                            <?php } elseif($ec_id == 0) { ?>
                                                                <strong id="member">dead accounts</strong>
                                                            <?php } ?>
                                                        <?php  } ?>
                                                    <?php } else { ?>
                                                        <strong id="member">Please Enter a Valid EC ID</strong>
                                                    <?php } ?>
                                                </p>
                                                <script language="javascript">
                                                    find_member(document.show_commissions.amico_id, document.getElementById('member'));
                                                </script>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <footer class="panel-footer text-center">
                            <input type="hidden" name="goto" value="view">
                            <input type="submit" value="Show Commissions" name="show_comms" />
                        </footer>
                    </form>
                </section>
            </div>
            <div class="col-md-10 col-xs-12 centering">
                <?php if($is_view): ?>
                    <div class="row">
                        <div class="commission_details_wrapper">

                            <?php
                            $sql = "SELECT DATE_FORMAT(report_time, '%b %e, %Y') FROM stw_reports WHERE report_id = '$report_id'";
                            $result_date = mysqli_query($conn,$sql);
                            $report_date = mysqli_result($result_date, 0);

                            $sql = "SELECT stw.member_id, UNIX_TIMESTAMP(stw.invoice_date) as period, td.str_designation, c.*, tm.int_member_id, tm.int_parent_id, tm.int_designation_id FROM stw_data stw INNER JOIN tbl_member tm ON stw.member_id = tm.amico_id INNER JOIN tbl_designation td ON tm.int_designation_id = td.int_designation_id INNER JOIN customers c ON tm.int_customer_id = c.customers_id WHERE stw.report_id = '{$report_id}' GROUP BY stw.member_id ORDER BY stw.member_id";
                            $result = mysqli_query($conn,$sql);

                            $member_parents = array();
                            $count = 0;
                            $member_row_num = -1;

                            while($row = mysqli_fetch_array($result)) {
                                $member_parents[$row['int_member_id']] = $row['int_parent_id'];

                                if(strtoupper($row['member_id']) == strtoupper($_GET['amico_id'])) {
                                    $member_row_num = $count;
                                }
                                $count++;

                                //debug(true, true, $member_parents, $member_row_num, $amico_id);
                            }

                            //debug(true, true, $is_view, $count, $member_parents);

                            if($member_row_num < 0) {
                                echo '<div class="table-responsive"><table class="table centering text-center"><tr><td class="strong">The above member did not earn commission for the selected STW data set.</td></tr></table></div>';
                            } else {
                                mysqli_data_seek($result, $member_row_num);
                                $row = mysqli_fetch_array($result);

                                $per_month = date("M", $row['period']);
                                $per_year = date("Y", $row['period']);
                                $per_start_day = 1;
                                $per_end_day = date("t", $row['period']);

                                ?>
                                <div class="member_details_wrapper">
                                    <div class="row">
                                        <div class="col-xs-12 text-center title"><strong>AMICO EDUCATIONAL CONCEPTS INC</strong></div>
                                        <div class="col-xs-12 text-center">Prepared for: <strong><?php echo (strtoupper($row['customers_firstname'])." ".strtoupper($row['customers_lastname'])); ?></strong></div>
                                        <div class="col-xs-12 text-center">Title: <strong><?php echo $row['str_designation']?></strong></div>
                                        <div class="col-xs-12 text-center">For Pay Period: <strong><?php echo ($per_month." ".$per_start_day.", ".$per_year." to ".$per_month." ".$per_end_day.", ".$per_year)?></strong></div>
                                        <div class="col-xs-12 text-center">Prepared On: <strong><?php echo $report_date?></strong></div>
                                    </div>
                                </div>
                                <div class="member_commissions_wrapper">
                                    <div class="member_info">
                                        <div class="table-responsive centering">
                                            <table border="0" cellpadding="0" cellspacing="0" align="center" style="color:black;" width="200">
                                                <tr>
                                                    <td align="center"><hr size="1">ID Number: <?php echo $row['member_id']?><hr size="1"></td>
                                                </tr>
                                                <?
                                                $active_members = 0;

                                                foreach($member_parents AS $m_id => $p_id)
                                                {
                                                    if($row['int_member_id'] == $p_id)
                                                    {
                                                        $active_members++;
                                                    }
                                                }

                                                ?>
                                                <tr>
                                                    <td align="center">Active First Level Members: <?php echo $active_members?></td>
                                                </tr>
                                                <?
                                                $des_id = $row['int_designation_id'];
                                                for($cnt=3-strlen($des_id);$cnt>0;$cnt--)
                                                {
                                                    $des_id = "0" . $des_id;
                                                }
                                                ?>
                                                <tr>
                                                    <td align="center"><hr size="1"><?php echo $des_id?><hr size="1"></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="commissions_list">
                                        <div class="table-responsive centering">
                                            <table class="table table-striped" style="color:black;">
                                                <tr>
                                                    <td valign="top" nowrap><b>Name</b></td>
                                                    <td valign="top" nowrap align="center"><b>Level</b></td>
                                                    <td valign="top" nowrap align="center"><b>Pay Level</b></td>
                                                    <td valign="top" nowrap><b>Title</b></td>
                                                    <td valign="top" nowrap align="center"><b>Invoice Nbr:</b></td>
                                                    <td valign="top" nowrap><b>Commission Type</b></td>
                                                    <td valign="top" nowrap align="center"><b>% Paid</b></td>
                                                    <td valign="top" nowrap align="right"><b>Amount</b></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="8" height="1" style="font-size:10px;padding:0;"><hr size="1"></td>
                                                </tr>
                                                <?
                                                $commission_total = 0;

                                                $sql2 = "SELECT stw.*, tm.int_designation_id, td.str_designation, c.* FROM stw_data stw INNER JOIN tbl_member tm ON stw.src_member_id = tm.amico_id INNER JOIN tbl_designation td ON tm.int_designation_id = td.int_designation_id INNER JOIN customers c ON tm.int_customer_id = c.customers_id WHERE stw.report_id = '{$_GET['report_id']}' AND stw.member_id = '{$row['member_id']}' AND stw.type = 'MP' ORDER BY stw.level, c.customers_lastname";
                                                $result2 = mysqli_query($conn,$sql2);
                                                while($row2 = mysqli_fetch_array($result2))
                                                {
                                                    $des_id = $row2['int_designation_id'];
                                                    for($cnt=3-strlen($des_id);$cnt>0;$cnt--)
                                                    {
                                                        $des_id = "0" . $des_id;
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td valign="top" nowrap><?php echo (strtoupper($row2['customers_firstname'])." ".strtoupper($row2['customers_lastname'])."-".$des_id)?></td>
                                                        <td valign="top" align="center"><?php echo $row2['level']?></td>
                                                        <td valign="top" align="center"><?php echo $row2['level']?></td>
                                                        <td valign="top"><?php echo $row2['str_designation']?></td>
                                                        <td valign="top" align="center"><?php echo $row2['invoice_id']?></td>
                                                        <td valign="top">Commission</td>
                                                        <td valign="top" align="right"><?php echo $row2['percentage']?></td>
                                                        <td valign="top" align="right">$<?php echo $row2['commissioned']?></td>
                                                    </tr>
                                                    <?
                                                    $commission_total += $row2['commissioned'];
                                                }

                                                $sql2 = "SELECT stw.*, c.*, o.customers_name 
                                                  FROM stw_data stw 
                                                  INNER JOIN tbl_member tm ON stw.src_member_id = tm.amico_id 
                                                  INNER JOIN orders o ON stw.invoice_id = o.orders_id 
                                                  INNER JOIN customers c ON tm.int_customer_id = c.customers_id 
                                                  WHERE stw.report_id = '{$id}' AND stw.member_id = '{$row['member_id']}' AND stw.type = 'RMP' 
                                                  ORDER BY stw.level, o.customers_name";

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

                                                $result2 = mysqli_query($conn,$sql2);

                                                if(mysqli_num_rows($result2) > 0)
                                                {
                                                    ?>
                                                    <tr>
                                                        <td colspan="8" height="1" style="font-size:10px;padding:0;"><hr size="1"></td>
                                                    </tr>
                                                    <?
                                                    while($row2 = mysqli_fetch_array($result2))
                                                    {
                                                        ?>
                                                        <tr>
                                                            <td valign="top" nowrap><?php echo (strtoupper($row2['customers_name']))?></td>
                                                            <td valign="top" align="center">N/A</td>
                                                            <td valign="top" align="center">N/A</td>
                                                            <td valign="top">Non-Member Purchase: <?php echo (strtoupper($row2['customers_firstname']))?> <?php echo (strtoupper($row2['customers_lastname']))?> (<?php echo $row2['member_id']?>)</td>
                                                            <td valign="top" align="center"><?php echo  $row2['invoice_id'] ?></td>
                                                            <td valign="top">Commission</td>
                                                            <td valign="top" align="right"><?php echo $row2['percentage']?></td>
                                                            <td valign="top" align="right">$<?php echo $row2['commissioned']?></td>
                                                        </tr>
                                                        <?
                                                        $commission_total += $row2['commissioned'];
                                                    }
                                                }
                                                ?>
                                                <tr>
                                                    <td colspan="8" height="1" style="font-size:10px;padding:0;"><hr size="1"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="5"></td>
                                                    <td valign="top" colspan="2">Center Subtotal:</td>
                                                    <td valign="top" align="right">$<?php echo number_format($commission_total, 2)?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="8" height="1" style="font-size:10px;padding:0;"><hr size="1"></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>

                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


<?php
require_once("templates/footer.php");

