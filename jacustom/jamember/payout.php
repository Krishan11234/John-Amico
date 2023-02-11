<?php
$page_name = 'Actual Commission Payout';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


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
                                                <th width="10%">Level</th>
                                                <th width="12%">Number of Members </th>
                                                <th width="16%">Monthly Purchase Per Member</th>
                                                <th width="15%">Team Purchase Of Members</th>
                                                <th width="16%">Volume Commission</th>
                                                <th width="13%">Monthly Income Range</th>
                                                <th width="18%">Cumulative Monthly Income</th>
                                            </tr>
                                            <?php
                                            $members = 1;
                                            $total = 0;

                                            for($i=0; $i<7; $i++) {
                                                if( $i > 0 ) { $members *= 5; }
                                                $multi = $members*225;
                                                $balance = ( ($multi) * 0.05 );
                                                $total += $balance;

                                                $totals[] = $total;

                                                echo "<tr>";
                                                    echo "<td>". ( ($i==0) ? "You" : "Level $i") ."</td>";
                                                    echo "<td>$members</td>";
                                                    echo "<td>x 225</td>";
                                                    echo "<td>$". number_format( $multi) ."</td>";
                                                    echo "<td>x 5%</td>";
                                                    echo "<td>= $". number_format($balance, 2) . "</td>";
                                                    echo "<td>= $$total</td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                            <!--<tr class="loginsmall">
                                                <td height="25" nowrap>You</td>
                                                <td>1</td>
                                                <td>x 225</td>
                                                <td>$225</td>
                                                <td>x 5%</td>
                                                <td>=$11.25</td>
                                                <td>=$11.25</td>
                                            </tr>
                                            <tr class="loginsmall">
                                                <td height="25" nowrap>Level 1</td>
                                                <td>5</td>
                                                <td>x 225</td>
                                                <td>$1,125</td>
                                                <td>x 5%</td>
                                                <td>=$56.25</td>
                                                <td>=$67.50</td>
                                            </tr>
                                            <tr class="loginsmall">
                                                <td height="25" nowrap>Level 2</td>
                                                <td>25</td>
                                                <td>x 225</td>
                                                <td>$5,625</td>
                                                <td>x 5%</td>
                                                <td>=$281.25</td>
                                                <td>=$348.75</td>
                                            </tr>
                                            <tr class="loginsmall">
                                                <td height="25" nowrap>Level 3</td>
                                                <td>125</td>
                                                <td>x 225</td>
                                                <td>$28,125</td>
                                                <td>x 5%</td>
                                                <td>=$1,406.25</td>
                                                <td>=$1,755.00</td>
                                            </tr>
                                            <tr class="loginsmall">
                                                <td height="25" nowrap>Level 4</td>
                                                <td>625</td>
                                                <td>x 225</td>
                                                <td>$140,625</td>
                                                <td>x 5%</td>
                                                <td>=$2,812.50</td>
                                                <td>=$4,567.50</td>
                                            </tr>
                                            <tr class="loginsmall">
                                                <td height="25" nowrap>Level 5</td>
                                                <td>3,125</td>
                                                <td>x 225</td>
                                                <td>$703,125</td>
                                                <td>x 5%</td>
                                                <td>=$14,062.50</td>
                                                <td>=$18,630.30</td>
                                            </tr>
                                            <tr class="loginsmall">
                                                <td height="25" nowrap>Level 6</td>
                                                <td>15,625</td>
                                                <td>x 225</td>
                                                <td>$53,515,625</td>
                                                <td>x 5%</td>
                                                <td>=$175,781.25</td>
                                                <td>=$194,411.25</td>
                                            </tr>-->
                                            <tr class="loginsmall">
                                                <td height="25">&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td colspan="2">
                                                    <div align="center"><strong>Annual Total Income:</strong></div></td>
                                                <td><strong>$<?php echo number_format( array_sum($totals), 2); ?></strong></td>
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

