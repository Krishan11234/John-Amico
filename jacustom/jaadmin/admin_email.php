<?php

$page_title = 'John Amico - Manage Admin Email';

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

?>


    <script language="JavaScript">
        <!--
        function Validate(theform) {
            if (theform.AdminEmail.value.length == 0) {
                alert("Please enter an email");
                theform.AdminEmail.focus();
                return false;
            }
            if (theform.AdminEmail.value.length != 0) {
                var retval = emailCheck(theform.AdminEmail.value)
                if (retval == false) {
                    theform.AdminEmail.focus();
                    return false;
                }
            }
            if (theform.AdminEmail1.value.length != 0) {
                var retval = emailCheck(theform.AdminEmail1.value)
                if (retval == false) {
                    theform.AdminEmail1.focus();
                    return false;
                }
            }
            if (theform.AdminEmail2.value.length != 0) {
                var retval = emailCheck(theform.AdminEmail2.value)
                if (retval == false) {
                    theform.AdminEmail2.focus();
                    return false;
                }
            }
            return true;
        }
        //-->
    </script>


    <div role="main" class="content-body">
        <header class="page-header">
            <h2>Manage Admin Email ID</h2>

            <div class="right-wrapper pull-right">
                <ol class="breadcrumbs">
                    <li>
                        <a href="<?php echo base_admin_url(); ?>">
                            <i class="fa fa-home"></i>
                        </a>
                    </li>
                    <li><span>Manage Admin Email</span></li>
                </ol>


                <a class="sidebar-right-toggle"></a>
            </div>
        </header>


        <?php
        $rsSelAdminEmails = mysqli_query($conn, "select * from tbl_admin_email");
        list($AdminEmailID, $AdminEmail, $AdminEmail1, $AdminEmail2) = mysqli_fetch_row($rsSelAdminEmails);
        ?>

        <div class="row">
            <div class="col-xs-12">
                <section class="panel">
                    <div class="col-md-6 centering">
                        <form name="send_email" action="act_admin_email.php" method="post" class="form form-validate" onSubmit="return Validate(this);">
                            <header class="panel-heading">
                                <h2 class="panel-title text-center">Site Admin Email</h2>
                            </header>
                            <div class="panel-body">
                                <div class="mb-lg"></div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label" for="AdminEmail">Email</label>
                                    <div class="col-sm-8">
                                        <input type="email" name="AdminEmail" id="AdminEmail" class="form-control" value="<?php echo ( !empty($AdminEmail) ? $AdminEmail: '' ); ?>" >
                                    </div>
                                </div>
                                <div class="mb-lg"></div>
                            </div>
                            <footer class="panel-footer">
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <input type="Submit" class="mr-lg " name="Update" value="Update" >
                                        <input type="button" class="danger" name="cancel" value="Cancel" onclick="location='<?php echo base_admin_url() . '/admin_email.php'; ?>';" >
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>


<?php
require_once("templates/footer.php");
