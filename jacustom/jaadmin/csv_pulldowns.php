<?php
$page_name = 'Export CSV\'s';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");
require_once("functions.php");
require_once("../common_files/Constant_contact/class.cc.php");

$mtype = 'm';


$member_type_name = 'Member';
$member_type_name_plural = 'Members';
$self_page = 'members.php';
$page_url = base_admin_url() . '/members.php?1=1';
$action_page = 'act_members.php';
$action_page_url = base_admin_url() . '/members.php?1=1';
$export_url = base_admin_url() . '/members_export.php';

?>

    <script language="javascript">
        <!--
        function confirmcustomerexport(Link) {
            if (confirm("Do want to export the customers data into a .csv file")) {
                location.href=Link;
            }
        }
        function confirmorderexport(Link) {
            if (confirm("Do want to export the orders data into a .txt file")) {
                location.href=Link;
            }
        }
        //-->
    </script>

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

        <div class="row admin-control">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 centering">
                <div class="panel panel-primary">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center">Export Contents to CSV</h2>
                    </header>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-12 col-md-4 text-center pb-lg pt-lg">
                                <a href="#" onclick="confirmcustomerexport('<?php echo base_admin_url(); ?>/exportcustomer.php');">
                                    <i class="fa fa-download fa-3" aria-hidden="true"></i>
                                    <h5>Export Customer Informaiton</h5>
                                </a>
                            </div>
                            <div class="col-xs-12 col-md-4 text-center pb-lg pt-lg">
                                <a href="#" onclick="confirmcustomerexport('<?php echo base_admin_url(); ?>/exportorder3.php');">
                                    <i class="fa fa-download fa-3" aria-hidden="true"></i>
                                    <h5>Export Non-Member Order Information</h5>
                                </a>
                            </div>
                            <div class="col-xs-12 col-md-4 text-center pb-lg pt-lg">
                                <a href="#" onclick="confirmcustomerexport('<?php echo base_admin_url(); ?>/exportorder2.php');">
                                    <i class="fa fa-download fa-3" aria-hidden="true"></i>
                                    <h5>Export Order Information</h5>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?php
require_once("templates/footer.php");
