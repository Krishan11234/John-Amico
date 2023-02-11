<?php
$page_name = 'My Sites';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


//echo '<pre>'; print_r($_SESSION['member']); die();

$referringUrl = base_shop_url() . "{$_SESSION['member']['ses_member_nickname']}";
$stylistUrl = base_shop_url() . "stylist/{$_SESSION['member']['ses_member_nickname']}";

?>

    <div role="main" class="content-body">
        <header class="page-header">
            <h2><?php echo $page_name; ?></h2>

            <div class="right-wrapper pull-right">
                <ol class="breadcrumbs">
                    <li>
                        <a href="<?php echo base_member_url(); ?>">
                            <i class="fa fa-home"></i>
                        </a>
                    </li>
                    <li><span><?php echo $page_name; ?></span></li>
                </ol>


                <a class="sidebar-right-toggle"></a>
            </div>
        </header>

        <div class="row ">
            <div class="col-md-10 col-xs-12 centering">
                <section class="panel">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                    </header>
                    <div class="panel-body ">
                        <div class="row">
                            <section class="panel">
                                <div class="panel-body">
                                    <div class="links_list">
                                        <ul class="">
                                            <li>Your website for retail sales: <a href="<?php echo $referringUrl;  ?>" target="_blank"><?php echo $referringUrl; ?></a></li>
                                            <li>Your website to sponsor a professional stylist: <a href="<?php echo $stylistUrl;  ?>" target="_blank"><?php echo $stylistUrl; ?></a></li>
                                        </ul>
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

