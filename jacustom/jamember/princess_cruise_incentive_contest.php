<?php
//$page_name = 'Princess Cruise Incentive Contest';
$page_name = 'Cancun Riu Palace Incentive Contest';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("../common_files/include/putnaIncentiveFunction.php");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

$reportGenerator = new IncentiveReportGenerator($conn, 'princess_cruise', 5);
//$reportGenerator->setStartTime('2018-01-01 00:00:00')->setEndTime('2018-08-31 11:59:59');
//$reportGenerator->setStartTime('2019-05-11 00:00:00')->setEndTime('2019-08-31 11:59:59');
$reportGenerator->setStartTime('2019-05-11 00:00:00')->setEndTime('2019-12-31 11:59:59');
$thisTableReady = $reportGenerator->getPutnaTableReady();

$reportGenerator_contest2 = new IncentiveReportGenerator($conn, 'princess_cruise_till_dec31', 5);
//$reportGenerator_contest2->setStartTime('2018-01-01 00:00:00')->setEndTime('2018-12-31 11:59:59');
$reportGenerator_contest2->setStartTime('2019-05-11 00:00:00')->setEndTime('2019-12-31 11:59:59');

$memberId = $_SESSION['member']['ses_member_id'];
$topMemberLimit = 5;

if($thisTableReady) {
    $memberIncentive = $reportGenerator_contest2->getIncentiveReport( $memberId );
    $topIncentives = $reportGenerator->getTopIncentiveReports($topMemberLimit);
    //echo '<pre>'; print_r($topIncentives); die();
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

        <div class="row punta_cana_contest_page">
            <div class="col-md-12 col-xs-12 centering">
                <section class="panel">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                    </header>
                    <div class="panel-body">
                        <?php if(!$thisTableReady):?>
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="body_title text-center">
                                        The system is not ready yet to display any report.
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <div class="col-xs-12 col-md-5 contest-1 contest">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="body_title text-center">
                                                <h3>Contest - 1</h3>
                                                <h4><strong><?php echo $reportGenerator_contest2->getStartTimeInWords("M jS"); ?></strong> &mdash; <strong><?php echo $reportGenerator_contest2->getEndTimeInWords("M jS, Y"); ?></strong></h4>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 single_line_wrapper"><div class="col-xs-8 centering"><div class="single_line"></div></div></div>
                                    </div>
                                    <div class="incentive_details">
                                        <div class="col-xs-12">
                                            <div class="body_title text-center">
                                                <p>This calculation is based on online consumer sales made during the time frame. </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="incentive_details">
                                        <div class="row">
                                            <div class="col-xs-12 total_sales">
                                                <span class="title">Your total Online Consumer Sales</span><span class="separator">:</span>
                                                <span class="number"><?php echo ( !empty($memberIncentive['total_sale']) ? "$".number_format($memberIncentive['total_sale'], 2) : 'N/A' ); ?></span>
                                            </div>
                                            <div class="col-xs-12 total_sales_incentive">
                                                <span class="title">Your total Cancun Cruise Prize Dollars</span><span class="separator">:</span>
                                                <span class="number"><?php echo ( !empty($memberIncentive['total_sale']) ? "$".number_format($memberIncentive['incentive'], 2) : 'N/A' ); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-7 contest-2 contest">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="body_title text-center">
                                                <h3>Contest - 2</h3>
                                                <h4><strong><?php echo $reportGenerator->getStartTimeInWords("M jS"); ?></strong> &mdash; <strong><?php echo $reportGenerator->getEndTimeInWords("M jS, Y"); ?></strong></h4>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 single_line_wrapper"><div class="col-xs-8 centering"><div class="single_line"></div></div></div>
                                        <div class="col-xs-12">
                                            <div class="body_title text-center incentive_details" style="margin-top: 10px;">
                                                <p>This incentive is calculated based on online consumer sales made during the time frame. Top Professional with the highest amount of online consumer orders wins the Trip FREE!</p>
                                            </div>
                                        </div>
                                        <!--<div class="col-xs-12 single_line_wrapper"><div class="col-xs-6 centering"><div class="single_line"></div></div></div>-->
                                        <?php if( !empty($topIncentives) && in_array($memberId, array_keys($topIncentives)) ) : ?>
                                            <!--<div class="col-xs-12"><p></p></div>-->
                                            <div class="col-xs-12 text-center"><p class="winning_text">Till now you are one of the Top <?php echo $topMemberLimit; ?> Members</p></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="incentive_details" style="margin-top: 10px;">
                                        <div class="row">
                                            <div class="col-xs-12 col-md-7 centering">
                                                <?php if(!empty($topIncentives)) { ?>
                                                    <p>Current Standings:</p>
                                                    <ol class="standing_list">
                                                        <?php foreach($topIncentives as $topIncentive) { ?>
                                                            <li>
                                                                <span class="title"><?php echo "{$topIncentive['firstname']} {$topIncentive['lastname']}"; ?></span>
                                                                <span class="separator">:</span>
                                                                <span class="number"><?php echo ( !empty($topIncentive['total_sale']) ? "$".number_format($topIncentive['total_sale'], 2) : 'N/A' ); ?></span>
                                                            </li>
                                                        <?php } ?>
                                                    </ol>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </div>


<?php
require_once("templates/footer.php");

