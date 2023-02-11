<?php

$page_title = 'Professional Login';

require_once("../common_files/include/global.inc");

if( is_member_logged_in() ) {
    if( !empty($_GET['redirect_to']) ) {
        $redirect_to = urldecode($_GET['redirect_to']);
        //$redirect_to = filter_var($redirect_to, FILTER_SANITIZE_STRING);
        header("Location: $redirect_to");
    } else {
        header("Location:" . base_member_url() . "/index.php");
    }
    exit;
}


$error_in_login = 0;

//echo '<pre>'; print_r($_POST); die();
$username = $password = '';

if (!empty($_POST['member_login'])) {

    $username = filter_var($_POST['membername'], FILTER_SANITIZE_STRING);
    $password = addslashes( trim($_POST['memberpassword']) );

    if ((empty( $username )) and (empty($password))) {
        $error_in_login = 1;
    }  // User ID and str_password not entered
    elseif (empty($username)) {
        $error_in_login = 2;
    }
    elseif (empty($password)) {
        $error_in_login = 3;
    }
    else {
        $error_in_login = do_member_login($username, $password);
    }
}


$display_header = false;
require_once("templates/header.php");

?>

    <div class="admin-control vertical-centering">
        <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-12 centering">
                <form name="frmlogin" action="" method="post">
                    <div class="panel panel-primary">
                        <div class="login-header">
                            <div class="logo-container text-center m-lg pb-lg">
                                <a href="../" class="logo">
                                    <!--<img src="<?php /*echo base_url(); */?>/images/john-amico-logo-black.png" height="40" alt="John Amico Admin" />-->
                                    <img src="<?php echo base_images_url(); ?>/JA-Logo.JPG" height="80" alt="John Amico Admin" />
                                </a>
                                <div class="visible-xs toggle-sidebar-left" data-toggle-class="sidebar-left-opened" data-target="html" data-fire-event="sidebar-left-opened">
                                    <i class="fa fa-bars" aria-label="Toggle sidebar"></i>
                                </div>
                            </div>
                        </div>
                        <div class="panel-heading text-center">
                            <h3 class="no-margin"><?php echo $page_title;?></h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-10 centering">
                                    <div class="pb-lg pt-lg">
                                        <div class="messages">
                                            <?php
                                            if ( !empty($login_error_texts[$error_in_login]) ) {
                                                echo "<div class='alert alert-danger'>$login_error_texts[$error_in_login]</div>";
                                            }
                                            elseif ($error_in_login != 0) {
                                                echo "<div class='alert alert-danger'>Unknown Error!</div>";
                                            }
                                            ?>
                                        </div>

                                        <div class="form-group">
                                            <label for="membername">Member ID</label>
                                            <input type="text" name="membername" class="form-control" id="membername" placeholder="Member ID" value="<?php echo !empty($username) ? $username : ''; ?>"/>
                                        </div>
                                        <div class="form-group">
                                            <label for="memberpassword">Password</label>
                                            <input type="password" name="memberpassword" class="form-control" id="memberpassword" placeholder="Password">
                                        </div>
                                        <div class="form-group"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <footer class="panel-footer">
                            <div class="row">
                                <div class="col-xs-10 centering text-center">
                                    <div class="pull-right">
                                        <button type="submit" name="member_login" value="Login" class="command  btn btn-default btn-primary">Login</button>
                                    </div>
                                    <div class="pull-left">
                                        <a href="<?php echo base_member_url(); ?>/forgotpassword.php">Forgot Password?</a>
                                    </div>
                                </div>
                            </div>
                        </footer>
                    </div>
                </form>
                <script> jQuery(document).ready(function(){ jQuery('#membername').focus(); }); </script>
            </div>
        </div>
    </div>


<?php

require_once("templates/footer.php");