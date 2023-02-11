<?php
$page_name = 'Extra Information';
$page_title = $page_name;

//echo '<pre>'; print_r( $_GET ); die();

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$is_popup = true;

$display_header = false;
require_once("templates/header.php");

$memberid_got = ( !empty($_GET['mid']) ? filter_var($_GET['mid'], FILTER_SANITIZE_NUMBER_INT) : 0 );

$currentUser_mtype = $_SESSION['member']['mtype'];
$currentUser_memberId = $_SESSION['member']['ses_member_id'];

//debug(true, true, $memberDetails);
?>


<div role="main" class="content-body <?php echo ( $is_popup ? 'no-margin-left' : '' ); ?> ">
    <div class="row ">
        <div class="col-xs-12 centering">
            <section class="panel">
                <header class="panel-heading">
                    <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                </header>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-12 extras ">
                            <div class="tabs">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#personal" data-toggle="tab" aria-expanded="true">Personal Information</a></li>
                                    <li class=""><a href="#profile" data-toggle="tab" aria-expanded="false">Profile</a></li>
                                    <li class=""><a href="#service_systems" data-toggle="tab" aria-expanded="false">Service Systems</a></li>
                                    <li class=""><a href="#retail_systems" data-toggle="tab" aria-expanded="false">Retail Systems</a></li>
                                    <li class=""><a href="#business_systems" data-toggle="tab" aria-expanded="false">Business Systems</a></li>
                                </ul>
                                <div class="tab-content">
                                    <div id="personal" class="tab-pane active">
                                        <?php

                                        if( !empty($currentUser_mtype) ) {
                                            if ( in_array($currentUser_mtype, array('m', 'a')) ) {
                                                include_once( base_member_path() . '/contact_info_my.php' );
                                                /*<iframe src="contact_info_my.php?memberid=<?=$memberid_got?>" frameborder="0" width="100%" height="650"></iframe>*/
                                            } else {
                                                include_once( base_member_path() . '/contact_info2.php' );
                                                ?>
                                                <!--<iframe src="../contact_organizer/contact_info2.php?memberid=<?/*=$memberid_got*/?>" frameborder="0" width="100%" height="650"></iframe>-->
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                    <div id="profile" class="tab-pane">
                                        <?php include_once( base_member_path() . '/contact_info_extra.php' ); ?>
                                        <!--<iframe src="contact_info_extra.php?memberid=<?/*=$memberid_got*/?>" frameborder="0" width="100%" height="650"></iframe>-->
                                    </div>
                                    <div id="service_systems" class="tab-pane">
                                        <?php include_once( base_member_path() . '/contact_system_services_info_extra.php' ); ?>
                                        <!--<iframe src="contact_system_services_info_extra.php?memberid=<?/*=$memberid_got*/?>" frameborder="0" width="100%" height="650"></iframe>-->
                                    </div>
                                    <div id="retail_systems" class="tab-pane">
                                        <?php include_once( base_member_path() . '/contact_retail_services_info_extra.php' ); ?>
                                        <!--<iframe src="contact_retail_services_info_extra.php?memberid=<?/*=$memberid_got*/?>" frameborder="0" width="100%" height="650"></iframe>-->
                                    </div>
                                    <div id="business_systems" class="tab-pane">
                                        <?php include_once( base_member_path() . '/contact_business_services_info_extra.php' ); ?>
                                        <!--<iframe src="contact_business_services_info_extra.php?memberid=<?/*=$memberid_got*/?>" frameborder="0" width="100%" height="650"></iframe>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>



<?php
require_once("templates/footer.php");
