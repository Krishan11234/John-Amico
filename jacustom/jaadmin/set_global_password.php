<?php
$page_name = 'Set Global Password';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

$logs = array();

$retval = 0;

if ($setP == 1):

    //Admin wishes to reset the global site password.
    //encrypt this password.
    $crypt_p = crypt($_POST['npass2'], $_POST['npass1']);
    if ($p = mysqli_query($conn,"UPDATE global_sec SET password='{$crypt_p}', last_updated=NOW()")):
        $retval = 1;
    endif;
endif;

?>

    <script type="text/javascript" language="JavaScript">
        <!--
        function checkPass() {
            var np1 = document.set_password.elements['npass1'];
            var np2 = document.set_password.elements['npass2'];

            if (np1.value != np2.value) {
                alert('New Passwords Do Not Match');
                np2.focus();
                return false;
            }
            return true;
        }
        -->
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

        <div class="row ">
            <section class="panel">
                <form name="set_password" onSubmit="return checkPass();" class="form-bordered" action="" method="post" enctype="multipart/form-data">
                    <div class="col-xs-12 col-lg-6 col-md-8 centering">
                        <header class="panel-heading">
                            <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                        </header>
                        <?php if(!empty($retval)): ?>
                            <div class="">
                                <div class="alert alert-success">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    Password Has Been Updated Successfully.
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="panel-body pb-lg pt-lg mb-lg mt-lg">
                            <div class="row form-group">
                                <label class="col-md-4 control-label" for="npass1">Password</label>
                                <div class="col-md-8">
                                    <input type="password" class="form-control" id="npass1" name="npass1" required maxlength="50" value="<?php echo ( !empty($is_edit) && !empty($lastname) ? $lastname : '' ); ?>">
                                </div>
                            </div>
                            <div class="row form-group">
                                <label class="col-md-4 control-label" for="npass2">Confirm Password</label>
                                <div class="col-md-8">
                                    <input type="password" class="form-control" id="npass2" name="npass2" required maxlength="50" value="<?php echo ( !empty($is_edit) && !empty($lastname) ? $lastname : '' ); ?>">
                                </div>
                            </div>
                        </div>
                        <footer class="panel-footer text-center">
                            <input type="hidden" name="setP" value="1">
                            <input type="submit" value="Change Password" name="submit" />
                        </footer>
                    </div>
                </form>
                <div class="clearfix"></div>
            </section>
            <?php if( !empty($logs) ) {
                echo implode('<br/>', $logs);
            } ?>
        </div>
    </div>


<?php
require_once("templates/footer.php");
