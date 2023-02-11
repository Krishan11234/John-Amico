<?php
$page_name = 'Commission Structure';
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

        <div class="row commission_structure_page">
            <div class="col-md-11 col-xs-12 centering">
                <section class="panel">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center">JOHN AMICO <?php echo $page_name; ?></h2>
                    </header>
                    <div class="panel-body ">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="body_title text-center">
                                    The John Amico commission structure allows you to earn income from your referrals of both professional stylists and consumers.
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 professional_commission commission_section">
                                <h3 class="title text-center">Professional Stylist Commission Program</h3>
                                <div class="next_level text-center">
                                    <img src="<?php echo base_member_url(); ?>/images/professional_commission_structure.png" class="img-responsive">
                                </div>
                                <div class="description text-justify">
                                    <p>&nbsp;</p>
                                    <p><strong>Here's how the professional stylist commission program works:</strong></p>
                                    <!--<p>&nbsp;</p>-->
                                    <p>In order to receive commissions from a particular month you must be considered an active member in John Amico. &nbsp;To be considered an active member in John Amico requires a purchase of $100 in commissionable products. &nbsp;Refer to the home office regarding commissionable product questions.</p>
                                    <p></p>
                                    <p>You develop a family tree or genealogy as follows:</p>
                                    <p><span class="underline">Level One Professional Members&ndash; 5%</span> &ndash; When you sponsor a professional stylist (with the purchase of a John Amico discovery kit), you will receive 5% of your member&rsquo;s commissionable product purchase. &nbsp;There is no limit to the number of professional stylists you can personally sponsor.</p>

                                    <p><span class="underline">Level Two Professional Members &ndash; 5%</span> - When your personally sponsored members sponsor a new John Amico member, you&rsquo;ll receive 5% of those members&rsquo; product purchases. &nbsp;There is no limit to the number of professional stylists on your second level.</p>

                                    <p><span class="underline">Level Three Professional Members - 5%</span> - When your level two members sponsor a new John Amico member, you&rsquo;ll receive 5% of those members&rsquo; product purchases. &nbsp;There is no limit to the number of professional stylists on your third level.</p>

                                    <p><span class="underline">Level Four Professional Members - 2%</span> - When your level three members sponsor a new John Amico member, you&rsquo;ll receive 2% of those members&rsquo; product purchases. &nbsp;There is no limit to the number of professional stylists on your fourth level.</p>

                                    <p><span class="underline">Level Five Professional Members &ndash; 2%</span> - When your level four members sponsor a new John Amico member, you&rsquo;ll receive 2% of those members&rsquo; product purchases. &nbsp;There is no limit to the number of professional stylists on your fifth level.</p>

                                    <p><span class="underline">Level Six Professional Members &ndash; 5%</span> - When your level five members sponsor a new John Amico member, you&rsquo;ll receive 5% of those members&rsquo; product purchases. &nbsp;There is no limit to the number of professional stylists on your sixth level.</p>
                                </div>
                            </div>
                            <div class="col-sm-6 consumer_commission commission_section">
                                <h3 class="title text-center">Consumer Commission Program</h3>
                                <div class="next_level text-center">
                                    <img src="<?php echo base_member_url(); ?>/images/consumer_commission_structure.png" class="img-responsive">
                                </div>
                                <div class="description text-justify">
                                    <p>&nbsp;</p>
                                    <p><strong>Here's how the consumer commission program works:</strong></p>
                                    <!--<p>&nbsp;</p>-->
                                    <p>When you refer consumers to purchase John Amico products from the website identified by your unique ID or link you will receive 35% of the product sale.</p>
                                    <p>When any of your professional members in your genealogy have consumers place orders you will receive a percentage of the 35% as follows:</p>
                                    <ul>
                                        <li>Level 1 = 5% </li>
                                        <li>Level 2 = 5% </li>
                                        <li>Level 3 = 5% </li>
                                        <li>Level 4 = 2% </li>
                                        <li>Level 5 = 2% </li>
                                        <li>Level 6 = 5%</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>


<?php
require_once("templates/footer.php");

