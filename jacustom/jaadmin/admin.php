<?php

$page_title = 'John Amico - Manage Admins';

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");
?>


<script language="JavaScript">
    function confirmCleanUp(Link) {
       if (confirm("Are you sure you want to delete this Admin User?")) {
          location.href=Link;
       }
    }
    function Validate(theform) {
       if (isEmpty(theform.firstname.value)){
            alert("Please enter your first name.");
            theform.firstname.focus();
            return false;
        }
        if (isEmpty(theform.lastname.value)){
            alert("Please enter your last name.");
            theform.lastname.focus();
            return false;
        }

        var retval = emailCheck(theform.email.value)
        if (retval == false){
            theform.email.focus();
            return false;
        }
         var retval = usernameCheck(theform.admin_username.value)
        if (retval == false){
            theform.admin_username.focus();
            return false;
        }
        var retval = passwordCheck(theform.admin_pass.value)
        if (retval == false){
            theform.admin_pass.focus();
            return false;
        }
        if(theform.confirm_password.value != theform.admin_pass.value ){
            alert("Both passwords must be the same!");
            theform.confirm_password.focus();
            return false;
        }

        return true;
    }
</script>


<?php 	
if(isset($_POST['adminid']) and $_POST['adminid'] > 0){
   $rsseladmin = mysqli_query($conn,"select * from tbl_admin WHERE int_admin_id = '{$_POST['adminid']}'");
   $nr = "NO";
   list($adminid,$firstname,$lastname,$admin_username,$admin_pass,$email,$active)
   	= mysqli_fetch_row($rsseladmin);
   }
else{
   $nr = "YES";
}


if(!isset($_POST['adminid']) and (!isset($_POST['add']))) {
    $rsselalladmin = mysqli_query($conn, "select int_admin_id, str_first_name , str_last_name, str_email, bit_active from tbl_admin ORDER BY str_last_name, str_first_name");

    if( mysqli_num_rows($rsselalladmin) < 1 ) {
        $load_noData_String = true;
    } else {
        $load_noData_String = false;
    }

    $load_addAdmin_form = false;
    $load_editAdmin_form = false;
} else if(!empty($_POST['add'])) {

    $load_addAdmin_form = true;
    $load_editAdmin_form = false;

} else if(!empty($_POST['adminid'])) {
    $load_addAdmin_form = false;
    $load_editAdmin_form = true;
}

if(!empty($_GET['dup'])) {
    $load_addAdmin_form = true;
    $load_editAdmin_form = false;
}

?>


<div role="main" class="content-body">
    <header class="page-header">
        <h2>Manage Administrators</h2>

        <div class="right-wrapper pull-right">
            <ol class="breadcrumbs">
                <li>
                    <a href="<?php echo base_admin_url(); ?>">
                        <i class="fa fa-home"></i>
                    </a>
                </li>
                <li><span>Manage Admins</span></li>
            </ol>


            <a class="sidebar-right-toggle" ></a>
        </div>
    </header>

    <div class="row">
        <div class="col-xs-12">
            <?php if( ($load_editAdmin_form || $load_addAdmin_form) && ( ($_SESSION['admin']['ses_admin_id'] == 1) || ($_SESSION['admin']['ses_admin_id'] == $adminid) ) ) : ?>
                <section class="panel">
                    <div class="col-md-6 centering">
                        <form name="send_email" action="act_admin.php" method="post" class="form form-validate" onSubmit="return Validate(this);">
                            <header class="panel-heading">
                                <h2 class="panel-title text-center"><?php echo ( ($nr == "NO") ? 'Edit ' : 'Add ') . 'Admin User'; ?></h2>
                            </header>
                            <div class="panel-body">
                                <div class="">
                                    <input type="hidden" name="adminid" value="<?php echo $adminid?>">
                                    <?php if( isset($_GET['dup']) ) : ?>
                                        <div class="message">
                                            <div class="alert alert-danger">
                                                Requested UserName is not available, please re-enter.
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label" for="firstname">First Name</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="firstname" id="firstname" class="form-control" value="<?php echo ( !empty($firstname) ? $firstname: '' ); ?>" >
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label" for="lastname">Last Name</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="lastname" id="lastname" class="form-control" value="<?php echo ( !empty($lastname) ? $lastname: '' ); ?>" >
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label" for="email">Email</label>
                                        <div class="col-sm-8">
                                            <input type="email" name="email" id="email" class="form-control" value="<?php echo ( !empty($email) ? $email: '' ); ?>" >
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label" for="admin_username">User Name</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="admin_username" id="admin_username" class="form-control" value="<?php echo ( !empty($admin_username) ? $admin_username: '' ); ?>" >
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label" for="admin_pass">Password</label>
                                        <div class="col-sm-8">
                                            <input type="password" name="admin_pass" id="admin_pass" class="form-control" value="<?php echo ( !empty($admin_pass) ? $admin_pass: '' ); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label" for="confirm_password">Confirm Password</label>
                                        <div class="col-sm-8">
                                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" value="<?php echo ( !empty($admin_pass) ? $admin_pass: '' ); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <footer class="panel-footer">
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <?php
                                        if($nr == "NO"){ ?>
                                            <input type="Submit" class="mr-lg " name="update" value="Update" >
                                        <?php }else{ ?>
                                            <input type="Submit" class="mr-lg " name="add" value="   Add   ">
                                        <?php } ?>
                                        <input type="button" class="danger" name="cancel" value="Cancel" onclick="location='<?php echo base_admin_url() . '/admin.php'; ?>';" >
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </section>
            <?php endif; ?>
            <?php if( !$load_editAdmin_form && !$load_addAdmin_form ) : ?>
                <section class="panel">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <div class="col-lg-4 col-md-8 col-sm-10 col-xs-12 centering text-center">
                                <?php if(($_SESSION['admin']['ses_admin_id'] == 1)){ ?>
                                    <form action="admin.php" method="post">
                                        <table class="table mb-none">
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="adminid" value="0">
                                                    <input type="submit" name="add" value="Add New Admin User" style="" >
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                <?php } else{
                                    echo ('&nbsp;');
                                }?>
                            </div>
                            <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12 centering date_range_wrapper">

                                <!-- Displaying Admins -->
                                <table class="table table-bordered table-striped mb-none">
                                    <thead>
                                    <tr>
                                        <th>Name (Last, First)</th>
                                        <th>Email</th>
                                        <th>Is Active?</th>
                                        <th>Commands</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if($load_noData_String) {
                                        echo '<tr><td colspan="4">Sorry, No data found!</td></tr>';
                                    } else {
                                        if(!empty($rsselalladmin)) {
                                            while (list($adminid, $firstname, $lastname, $email, $active) = mysqli_fetch_row($rsselalladmin)) {
                                                echo "<tr>";
                                                echo "<td>$lastname $firstname</td> <td>$email</td>";
                                                ?>
                                                <td>
                                                    <form action='act_admin.php' method='post'>
                                                        <input type="hidden" name="adminid" value="<?php echo $adminid; ?>">
                                                        <input type="hidden" name="active" value="<?php echo $active; ?>">

                                                        <?php
                                                        if(($_SESSION['admin']['ses_admin_id'] == 1)){ // if super user logged in
                                                            if($adminid != $_SESSION['admin']['ses_admin_id']){
                                                                // if the current record is super users record
                                                                if($active == 1){
                                                                    echo '<input type="submit" name="activate" value="   Active   ">';
                                                                }
                                                                else{
                                                                    echo '<input type="submit" class="danger" name="activate" value=" Deactive ">';
                                                                }
                                                            }
                                                            else{
                                                                echo('<font color="blue">Super User</font>');
                                                            }
                                                        }	// if not super user logged in
                                                        else{
                                                            if($active == 0){
                                                                echo('<font color="white">DeActive</font>');
                                                            }
                                                            else{
                                                                echo('<font color="white">Active</font>');
                                                            }
                                                        }
                                                        ?>

                                                    </form>
                                                </td>
                                                <td>
                                                    <form action='admin.php' method='post'>
                                                        <input type="hidden" name="adminid" value="<?php echo $adminid; ?>">

                                                        <?php
                                                        /* No one can edit super users record but super user.
                                                           At lest one user should be retained in the data base
                                                           so that he can log in to the system, if every one is
                                                           deleted then setup has to be run again, which deletes
                                                           all existing record!! here the first record, id = 1, which is
                                                           the initial admin (super user) is retained */
                                                        if(($_SESSION['admin']['ses_admin_id'] == 1) or ($adminid == $_SESSION['admin']['ses_admin_id'])){
                                                            // if super user or logged in users record
                                                            echo '<input type="submit" name="edit" value=" Edit ">&nbsp;';

                                                            if($_SESSION['admin']['ses_admin_id'] == 1){
                                                                if($adminid != 1){
                                                                    echo('<input type="button" name="delete" class="danger" value="Delete" onClick="return	confirmCleanUp(\'act_admin.php?adminid='. $adminid . '&delete=1\')">');
                                                                }
                                                            }
                                                        } elseif($adminid == 1){
                                                            echo('Super User');
                                                        } else {
                                                            echo'&nbsp;';
                                                        }
                                                        ?>
                                                    </form>
                                                </td>
                                                <?php

                                            }

                                            mysqli_free_result($rsselalladmin);
                                        }
                                    }
                                    ?>

                                    </tbody>
                                </table>
                                <!-- /Displaying Admins -->

                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

        </div>
    </div>
</div>


<?php
require_once("templates/footer.php");
