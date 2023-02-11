<?php
$page_name = 'Media Artist Program';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

//echo '<pre>'; print_r($_SESSION['member']); die();
$amicoMember = get_amico_member($_SESSION['member']['ses_member_id']);

//echo '<pre>'; var_dump($_SESSION['member'], $amicoMember); die();

?>

    <div role="main" class="content-body">
        <header class="page-header">
            <h2><?php echo $page_name; ?></h2>

            <div class="right-wrapper pull-right">
                <!--<ol class="breadcrumbs">
                <li>
                    <a href="<?php /*echo base_admin_url(); */?>">
                        <i class="fa fa-home"></i>
                    </a>
                </li>
                <li><span>Administrator Control Panel</span></li>
            </ol>-->


                <a class="sidebar-right-toggle" ></a>
            </div>
        </header>

        <!-- start: page -->
        <div class="row mlm_manage_wrapper panels_wrapper">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10 mlm_manage centering">
                <div class="panel panel-primary">
                    <div class="panel-heading"><?php echo $page_name; ?></div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6 col-lg-6 col-xs-12 text-center item">
                                <a class="link" href="<?php echo base_shop_url(); ?>mediaartist/" target="_blank">
                                    <div class="">
                                        <i class="fa fa-cart-plus fa-3 icon" aria-hidden="true"></i>
                                        <h5 class="name"><?php echo ( is_member_a_media_artist() ? 'Re-' : '' ); ?>Buy Media Artist Program</h5>
                                    </div>
                                </a>
                            </div>
                            <?php /*if( !empty($_SESSION['member']['ses_member_nickname']) && is_member_a_media_artist() ): */?>
                            <?php if( !empty($_SESSION['member']['ses_member_nickname']) ): ?>
                                <div class="col-md-6 col-lg-6 col-xs-12 text-center item">
                                    <a class="naxumLoginLink" href="#">
                                        <div class="">
                                            <i class="fa fa-paint-brush fa-3 icon" aria-hidden="true"></i>
                                            <h5 class="name">Login to Media Artist Program</h5>
                                        </div>
                                    </a>
                                    <div class="hide">
                                        <form action="https://office.myjohnamico.com/index.cgi" method="post" name="0.1_formlogin" id="naxumLogin" target="_blank">
                                            <input type="hidden" name="sitename" value="<?php echo $amicoMember['nickname']; ?>">
                                            <!-- <input type="hidden" name="pswd" value="<?php //echo md5($amicoMember['nickname'] . NAXUM_GLOBAL_PASSWORD); ?>"> -->
                                            <input type="hidden" name="password" value="<?php echo $amicoMember['customers_password']; ?>" />
                                            <input type="hidden" name="logoutredirect" value="<?php echo base_member_url(); ?>">
                                            <input type="submit" name="formlogin" value="Login to Marketing System">
                                        </form>
                                    </div>
                                    <script>
                                        jQuery(document).ready(function($){
                                            $('.naxumLoginLink').on('click', function(){
                                                $('#naxumLogin').submit();
                                                return false;
                                            });
                                        });
                                    </script>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?php
require_once("templates/footer.php");