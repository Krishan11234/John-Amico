<?php
$page_name = 'Monthly Earnings Summary';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

//$useMagento = true;

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
            <div class="col-md-6 col-xs-12 centering">
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
                                            <tr class="">
                                                <th class="copysmallblack text-center" align="center">Month</th>
                                                <th class="copysmallblack text-center" align="center">Amount Earned</th>
                                            </tr>
                                            <?php
                                            $memberid = $_SESSION['member']['ses_member_id'];
                                            $today=date("y-m-d");
                                            $startyear=0;
                                            $endmonth=(date("m"));
                                            $startmonth=$endmonth-7;
                                            $startmonth=$startmonth+1;
                                            $totalcommision = 0;
                                            if($startmonth<=0 ) {
                                                $startmonth=$startmonth+12;
                                                $startyear=-1;
                                            }

                                            while($startmonth != $endmonth){
                                                $today = date("F/m/d/y",mktime(0,0,0,$startmonth,02,(date("Y")+$startyear)));

                                                list($str_presmonth,$int_presmonth,$presday,$presyear) = explode('/',$today);

                                                $sql = "SELECT report_id FROM `stw_data` WHERE invoice_date >= '" . (date("Y") + $startyear) . "-" . $startmonth . "-01' AND invoice_date <= '" . (date("Y") + $startyear) . "-" . $startmonth . "-31' GROUP BY report_id ORDER BY report_id DESC LIMIT 0,1";

                                                //echo $sql;
                                                $res = mysqli_query($conn,$sql);

                                                if(mysqli_num_rows($res) > 0) {
                                                    $id = mysqli_result($res, 0);

                                                    $sql = "SELECT tm.int_member_id, tm.int_parent_id FROM stw_data stw INNER JOIN tbl_member tm ON stw.member_id = tm.amico_id WHERE stw.report_id = '$id' GROUP BY stw.member_id ORDER BY stw.member_id";
                                                    $result = mysqli_query($conn,$sql);

                                                    $member_parents = array();
                                                    while($row = mysqli_fetch_array($result)) {
                                                        $member_parents[$row['int_member_id']] = $row['int_parent_id'];
                                                    }

                                                    $active_members = 0;

                                                    foreach($member_parents AS $m_id => $p_id) {
                                                        if($memberid == $p_id) {
                                                            $active_members++;
                                                        }
                                                    }

                                                    $sql = "SELECT SUM(sd.amount), SUM(sd.commissioned) 
                                                      FROM stw_data sd 
                                                      INNER JOIN tbl_member tm ON sd.src_member_id = tm.amico_id 
                                                      -- INNER JOIN orders o ON sd.invoice_id = o.orders_id 
                                                      INNER JOIN customers c ON tm.int_customer_id = c.customers_id 
                                                      WHERE sd.report_id = '$id' AND sd.member_id = '{$_SESSION['member']['session_user']}' AND sd.type = 'RMP'
                                                    ";
                                                    $res2 = mysqli_query($conn,$sql) or die(mysqli_error($conn));
                                                    list($rmp_amount, $rmp_commissioned) = mysqli_fetch_row($res2);

                                                    $sql = "SELECT SUM(sd.amount), SUM(sd.commissioned) FROM stw_data sd INNER JOIN tbl_member tm ON sd.member_id=tm.amico_id LEFT JOIN customers c ON tm.int_customer_id=c.customers_id LEFT JOIN address_book ab ON tm.int_customer_id=ab.customers_id AND ab.address_book_id='3' LEFT JOIN zones z ON ab.entry_zone_id=z.zone_id WHERE sd.report_id = '$id' AND sd.member_id = '{$_SESSION['member']['session_user']}' AND sd.level <= '$active_members' AND sd.type = 'MP' GROUP BY sd.member_id ORDER BY sd.member_id";

                                                    $res2 = mysqli_query($conn,$sql) or die(mysqli_error($conn));
                                                    list($fla_amount, $fla_commissioned) = mysqli_fetch_row($res2);

                                                    $fla_commissioned_number = round($fla_commissioned + $rmp_commissioned, 2);
                                                    $fla_commissioned = number_format($fla_commissioned_number, 2);
                                                    $totalcommision += $fla_commissioned_number;

                                                    ?>
                                                    <tr>
                                                        <td width="23%" bgcolor="#FFFFFF" class="copysmallblack"><div align="center"><strong><?=$int_presmonth?>-<?=$presyear?>:</strong></div></td>
                                                        <td width="77%" bgcolor="#FFFFFF" class="copysmallblack"><div align="right"><strong>
                                                                    $ <?=$fla_commissioned?></strong></div></td>
                                                    </tr>
                                                <?php } else { ?>
                                                    <tr>
                                                        <td width="23%" bgcolor="#FFFFFF" class="copysmallblack"><div align="center"><strong><?=$int_presmonth?>-<?=$presyear?>:</strong></div></td>
                                                        <td width="22%" bgcolor="#eeeeee" class="loginsmall"><div align="right">Not Calculated</div></td>
                                                    </tr>
                                                <?php
                                                }

                                                $startmonth=$startmonth+1;
                                                if($startmonth>12){
                                                    $startmonth=1;
                                                    $startyear=0;
                                                }
                                            }
                                            ?>

                                            <tr>
                                                <td bgcolor="#FFFFFF" class="copysmallblack"><div align="center"><strong><font color="#003366" style="font-size:14px">TOTAL</font></strong></div></td>
                                                <td bgcolor="#FFFFFF" class="copysmallblack" align="right"><strong><font color="#003366" style="font-size:14px">$ <?=number_format($totalcommision, 2)?></font></strong></td>
                                            </tr>
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

