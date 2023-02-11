<?php
if(!isset($display_header)) {
    $display_header = true;
}
?>

<!doctype html>
<html class="fixed sidebar-light">
<head>

    <!-- Basic -->
    <meta charset="UTF-8">

    <title><?php echo ( !empty($page_title) ? $page_title : 'John Amico' ); ?></title>
    <meta name="author" content="omar-sharif.net">

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link rel="icon" href="<?php echo base_shop_url(); ?>/media/favicon/default/favicon.ico" type="image/x-icon">

    <!-- Web Fonts  -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="<?php echo base_theme_assets_url(); ?>/vendor/bootstrap/css/bootstrap.css" />

    <link rel="stylesheet" href="<?php echo base_theme_assets_url(); ?>/vendor/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="<?php echo base_theme_assets_url(); ?>/vendor/linecons/linecons.css" />
    <link rel="stylesheet" href="<?php echo base_theme_assets_url(); ?>/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="<?php echo base_theme_assets_url(); ?>/vendor/bootstrap-datepicker/css/bootstrap-datepicker3.css" />

    <!-- Specific Page Vendor CSS -->
    <link rel="stylesheet" href="<?php echo base_theme_assets_url(); ?>/vendor/jquery-ui/jquery-ui.css" />
    <link rel="stylesheet" href="<?php echo base_theme_assets_url(); ?>/vendor/jquery-ui/jquery-ui.theme.css" />
    <link rel="stylesheet" href="<?php echo base_theme_assets_url(); ?>/vendor/bootstrap-multiselect/bootstrap-multiselect.css" />
    <link rel="stylesheet" href="<?php echo base_theme_assets_url(); ?>/vendor/morris.js/morris.css" />
    <link rel="stylesheet" href="<?php echo base_theme_assets_url(); ?>/vendor/bootstrap-fileupload/bootstrap-fileupload.min.css" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="<?php echo base_theme_assets_url(); ?>/stylesheets/theme.css" />

    <!-- Skin CSS -->
    <link rel="stylesheet" href="<?php echo base_theme_assets_url(); ?>/stylesheets/skins/default.css" />

    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="<?php echo base_theme_assets_url(); ?>/stylesheets/theme-custom.css">

    <!-- Theme Custom Print CSS -->
    <link rel="stylesheet" media="print" href="<?php echo base_theme_assets_url(); ?>/stylesheets/theme-custom-print.css">

    <!-- Head Libs -->
    <!--<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>-->
    <script src="<?php echo base_theme_assets_url(); ?>/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo base_theme_assets_url(); ?>/vendor/modernizr/modernizr.js"></script>

    <script language="JavaScript" src="<?php echo base_js_url(); ?>/form_check.js"></script>

</head>
<body>
<section class="body">

    <?php if( $display_header ) :?>

        <?php
        $user_full_name = '';
        if(!empty($_SESSION['admin']['ses_admin_first_name'])) { $user_full_name .= $_SESSION['admin']['ses_admin_first_name'] . ' '; }
        if(!empty($_SESSION['admin']['ses_admin_last_name'])) { $user_full_name .= $_SESSION['admin']['ses_admin_last_name']; }
        ?>

        <!-- start: header -->
        <header class="header">
            <div class="logo-container">
                <a href="../" class="logo">
                    <!--<img src="<?php /*echo base_url(); */?>/images/john-amico-logo-black.png" height="40" alt="John Amico Admin" />-->
                    <img src="<?php echo base_images_url(); ?>/JA-Logo.JPG" height="40" alt="John Amico Admin" />
                </a>
                <div class="visible-xs toggle-sidebar-left" data-toggle-class="sidebar-left-opened" data-target="html" data-fire-event="sidebar-left-opened">
                    <i class="fa fa-bars" aria-label="Toggle sidebar"></i>
                </div>
            </div>

            <?php if( is_admin_logged_in() ) : ?>
                <!-- start: search & user box -->
                <div class="header-right">

                    <span class="separator"></span>

                    <div id="userbox" class="userbox">
                        <a href="#" data-toggle="dropdown">
                            <figure class="profile-picture">
                                <div class="no_profile_picture"><span aria-hidden="true" class="li_user"></span></div>
                                <!--<img src="<?php /*echo base_url(); */?>/theme_assets/images/!logged-user.jpg" alt="<?php /*echo $user_full_name; */?>" class="img-circle" data-lock-picture="<?php /*echo base_url(); */?>/theme_assets/images/!logged-user.jpg" />-->
                            </figure>
                            <div class="profile-info" data-lock-name="John Doe" data-lock-email="johndoe@okler.com">

                                <span class="name"><?php echo $user_full_name; ?></span>
                                <span class="role">administrator</span>
                            </div>

                            <i class="fa custom-caret"></i>
                        </a>

                        <div class="dropdown-menu">
                            <ul class="list-unstyled">
                                <li class="divider"></li>
                                <!--<li>
                                    <a role="menuitem" tabindex="-1" href="pages-user-profile.html"><i class="fa fa-user"></i> My Profile</a>
                                </li>
                                <li>
                                    <a role="menuitem" tabindex="-1" href="#" data-lock-screen="true"><i class="fa fa-lock"></i> Lock Screen</a>
                                </li>-->
                                <li>
                                    <a role="menuitem" tabindex="-1" href="<?php echo base_admin_url(); ?>/loginoutshopauto.php?logout=1<?php echo ( !empty($_COOKIE[md5('adminLoggedInToMage')]) ? '&mage=1' : '' ); ?>"><i class="fa fa-power-off"></i> Logout</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- end: search & user box -->
            <?php endif; ?>

        </header>
        <!-- end: header -->
    <?php endif; ?>

    <div class="inner-wrapper <?php echo (!$display_header ? 'no-padding-top' : ''); ?> ">