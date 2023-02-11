<?php

$page_title = 'Administrator Login';

require_once("../common_files/include/global.inc");


//echo '<pre>'; var_dump( (!empty($_GET['redirect_to']) && empty($_SESSION['admin_redirect_after_login'])), $_GET['redirect_to'], $_SESSION['admin_redirect_after_login'] ); die();

if( !empty($_GET['redirect_to']) && empty($_SESSION['admin_redirect_after_login']) ) {
    $redirect_to = urldecode($_GET['redirect_to']);
    $_SESSION['admin_redirect_after_login'] = $redirect_to;
}

//echo '<pre>'; var_dump( $_SESSION, $_SESSION['admin_redirect_after_login'] ); die();

if( is_admin_logged_in() ) {
    if( !empty($_SESSION['admin_redirect_after_login']) ) {
        $redirect_to = $_SESSION['admin_redirect_after_login'];
        //$redirect_to = filter_var($redirect_to, FILTER_SANITIZE_STRING);

        $_SESSION['admin_redirect_after_login'] = '';

        header("Location: $redirect_to");
    } else {
        header("Location:" . base_admin_url() . "/index.php");
    }
    exit;
}

$error_in_login = 0;

if (isset($_POST['login'])) {

    //echo '<pre>'; print_r($_POST); die();

    if ((empty($_POST['tmpusername'])) and (empty($_POST['tmppassword']))) {
        $error_in_login = 1;
    }  // User ID and str_password not entered
    elseif (empty($_POST['tmpusername'])) {
        $error_in_login = 2;
    }
    elseif (empty($_POST['tmppassword'])) {
        $error_in_login = 3;
    }
    else {
        //session_start();

        //echo '<pre>'; print_r($_POST); die();

        // select the pre-set admin email id to use in case of error code 5
        $rs_sel_admin_email = mysqli_query($conn, "select str_admin_email from tbl_admin_email");
        list($str_admin_email) = mysqli_fetch_row($rs_sel_admin_email);


        $rs_admin_login = mysqli_query($conn, "select int_admin_id,str_username,str_email,str_password,bit_active,str_first_name,str_last_name from tbl_admin where str_password = '" . $_POST['tmppassword'] . "' and str_username = '" . $_POST['tmpusername'] . "'");
        list($int_admin_id, $str_username, $str_email, $str_password, $bit_active, $first_name, $last_name) = mysqli_fetch_row($rs_admin_login);

        if ((!$int_admin_id == "") and ($bit_active == 1)) { // logged in successfully


            $secure_session_user = md5($_POST['tmpusername'] . $_POST['tmppassword']);
            $_SESSION['admin']['session_user'] = $_POST['tmpusername'];
            $_SESSION['admin']['session_key'] = time() . $secure_session_user . session_id();
            $_SESSION['admin']['current_session'] = $_POST['tmpusername'] . "=" . $_SESSION['admin']['session_key'];
            $_SESSION['admin']['is_admin'] = true;

            //declare project specific session  varibales here

            $_SESSION['admin']['ses_admin_id'] = $int_admin_id;
            $_SESSION['admin']['ses_admin_first_name'] = $first_name;
            $_SESSION['admin']['ses_admin_last_name'] = $last_name;
            $_SESSION['admin']['ses_admin_email'] = $str_email;
            //log_admin($int_admin_id, $_POST[tmpusername]);

            //echo '<pre>'; print_r($_GET['redirect_to']); die();
            //echo '<pre>'; var_dump( $_SESSION, $_SESSION['admin_redirect_after_login'] ); die();

            if( !empty($_SESSION['admin_redirect_after_login']) ) {
                $redirect_to = $_SESSION['admin_redirect_after_login'];
                //$redirect_to = filter_var($redirect_to, FILTER_SANITIZE_STRING);

                $_SESSION['admin_redirect_after_login'] = '';

                header("Location: $redirect_to");
            } else {
                header("Location:" . base_admin_url() . "/index.php");
            }
            exit;
        }
        elseif ((!$int_admin_id == "") and ($bit_active == 0)) {
            $error_in_login = 5; // account exist but inactive or disabled
        }
        else {
            $error_in_login = 4;// incorect login information
        }
    }
}

$display_header = false;
require_once("templates/header.php");

?>

    <!-- start: page -->
    <div class="admin-control vertical-centering">
        <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-12 centering">
                <form name="frmlogin" action="admin_login.php" method="post">
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
                                            if ($error_in_login == 1) {
                                                echo "<b>Please enter a valid User ID and Password.</b>";
                                            }
                                            elseif ($error_in_login == 2) {
                                                echo "<b>Please enter a valid User ID.</b>";
                                            }
                                            elseif ($error_in_login == 3) {
                                                echo "<b>Please enter a valid Password.</b>";
                                            }
                                            elseif ($error_in_login == 4) {
                                                echo "<b>Your login information is not correct<br>
                                                    Please Re-Enter a valid User Name and Password.</b>";
                                            }
                                            elseif ($error_in_login == 5) {
                                                echo "<b>Account inactive or disabled.<br>Please <a href='mailto:e'>
                                                    contact site admin</b></a>";
                                            }
                                            elseif ($error_in_login != 0) {
                                                echo "Unknown Error!";
                                            }
                                            ?>
                                        </div>

                                        <div class="form-group">
                                            <label for="tmpusername">Username</label>
                                            <input type="text" name="tmpusername" class="form-control" id="tmpusername" placeholder="Username"/>
                                        </div>
                                        <div class="form-group">
                                            <label for="tmppassword">Password</label>
                                            <input type="password" name="tmppassword" class="form-control" id="tmppassword" placeholder="Password">
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
                                        <button type="Submit" name="login" value="Login" class="command  btn btn-default btn-primary">Login</button>
                                    </div>
                                    <div class="pull-left">
                                        <a href="<?php echo base_admin_url(); ?>/forgotpassword.php">Forgot Password?</a>
                                    </div>
                                </div>
                            </div>
                        </footer>
                    </div>
                    <script> jQuery(document).ready(function(){ jQuery('#tmpusername').focus(); }); </script>
                </form>
            </div>
        </div>
    </div>


<?php

require_once("templates/footer.php");
