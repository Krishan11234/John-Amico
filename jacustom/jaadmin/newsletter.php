<?php
$page_name = 'Manage Newsletters';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


define('SMS_MESSAGE_BOX_PLACEHOLDER', 'Maximum message length is 100 characters.');

$member_type_name = 'Newsletter';
$member_type_name_plural = 'Newsletters';
$self_page = 'newsletter.php';
$page_url = base_admin_url() . "/$self_page?1=1";
$action_page = 'act_newsletter.php';
$action_page_url = base_admin_url() . "/$action_page?1=1";
//$export_url = base_admin_url() . '/members_export.php';


$is_edit = $is_add = false;
$success_message = $error_message = array();

//debug(false, true, $_POST);

$mesg = ( !empty($_GET['msg']) ? $_GET['msg'] : '' );
$error = false;

if( !empty($_POST['newsletterid']) && !empty($_POST['goto']) ) {
    if($_POST['goto'] == 'update') {
        $rsnewsletter = mysqli_query($conn, "select TargetAudience, int_newsletter_id, str_subject, str_newsletter,bit_active, int_days, sms, sms_msg from tbl_newsletter WHERE int_newsletter_id = {$_POST['newsletterid']}");
        list($audience, $newsletterid, $subject, $newsletter, $active, $in_days, $sms, $sms_msg) = mysqli_fetch_row($rsnewsletter);
        $nr = "NO";

        $is_edit = true;
    }
    elseif( $_POST['goto'] == 'update_entry' ) {

        $audience = trim(filter_var($_POST['Audience'], FILTER_SANITIZE_STRING));
        $subject = trim(filter_var($_POST['subject'], FILTER_SANITIZE_STRING));
        $newsletter = trim(mysqli_real_escape_string($conn, $_POST['newsletter']));
        $sms_msg= trim(mysqli_real_escape_string($conn, $_POST['sms_msg']));

        $sms = !empty($_POST['sms']) ? 1 : 0;
        $in_days = ( !empty($_POST['in_days']) ? filter_var($_POST['in_days'], FILTER_SANITIZE_NUMBER_INT) : '' );
        $mewsletterid = ( !empty($_POST['newsletterid']) ? filter_var($_POST['newsletterid'], FILTER_SANITIZE_NUMBER_INT) : '' );

        if(!empty($mewsletterid)) {
            $table = "tbl_newsletter";
            $fieldlist = "str_subject='{$subject}', str_newsletter='{$newsletter}', TargetAudience='{$audience}', int_days='{$in_days}', sms='{$sms}', sms_msg='{$sms_msg}'";
            $condition = " where int_newsletter_id = {$mewsletterid}";
            $result = update_rows($conn, $table, $fieldlist, $condition);

            $mesg = "News entry updated successfully.";
        }

        $is_edit = $is_add = false;
    }
}
elseif ( empty($_POST['newsletterid']) && !empty($_POST['goto']) ) {
    if ($_POST['goto'] == 'add') {
        $is_add = true;
    }
    elseif( $_POST['goto'] == 'add_entry' ) {
        $audience = trim(filter_var($_POST['Audience'], FILTER_SANITIZE_STRING));
        $subject = trim(filter_var($_POST['subject'], FILTER_SANITIZE_STRING));
        $newsletter = trim(mysqli_real_escape_string($conn, $_POST['newsletter']));
        $sms_msg= trim(mysqli_real_escape_string($conn, $_POST['sms_msg']));

        $sms = !empty($_POST['sms']) ? 1 : 0;
        $in_days = ( !empty($_POST['in_days']) ? filter_var($_POST['in_days'], FILTER_SANITIZE_NUMBER_INT) : '' );
        $mewsletterid = ( !empty($_POST['newsletterid']) ? filter_var($_POST['newsletterid'], FILTER_SANITIZE_NUMBER_INT) : '' );

        if(!empty($subject) && (!empty($newsletter) || !empty($sms_msg)) ) {
            $table = "tbl_newsletter";                // inserting values to setting table
            $in_fieldlist="str_subject,str_newsletter,bit_active, TargetAudience, int_days";
            $in_values = "'{$subject}','{$newsletter}',1, '{$audience}', '$in_days' ";
            $result = insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values

            $mesg = "Newsletter entry added successfully.";

            $is_edit = $is_add = false;
        } else {
            $mesg = 'You need to enter newsletter subject with description';
            $error = true;
            $is_add = true;
        }


    }
}
elseif ( !empty($_POST['newsletterid']) && is_numeric($_POST['newsletterid']) && !empty($_POST['activate']) ) {
    $newsletterid = filter_var($_POST['newsletterid'], FILTER_SANITIZE_NUMBER_INT);
    $make_active = ( ($_POST['active'] == 0) ? 1 : 0 );

    $table = "tbl_newsletter";
    $fieldlist = "bit_active=$make_active";
    $condition = " where int_newsletter_id = {$newsletterid}";
    $result = update_rows($conn, $table, $fieldlist, $condition);
}

if ( !empty($_GET['newsletterid']) && is_numeric($_GET['newsletterid']) && !empty($_GET['delete']) ) {
    $newsletterid = filter_var($_GET['newsletterid'], FILTER_SANITIZE_NUMBER_INT);

    $table = "tbl_newsletter";
    $condition = " where int_newsletter_id = {$newsletterid}";
    $result = del_rows($conn, $table, $condition);// function call to delete

    $mesg = "Newsletter entry removed successfully.";
}

if(!$is_edit && !$is_add) {

    $limit = 50;
    $page = ((!empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) ? $_REQUEST['page'] : 1);

    $limit_start = ($page * $limit) - $limit;
    $limit_end = ($page * $limit);


    $conditions = $sortby = array();

    $_REQUEST['params']['status_filter'] = ( isset($_REQUEST['status_filter']) ? $_REQUEST['status_filter'] : $_REQUEST['params']['status_filter'] );
    $status_filter = isset($_REQUEST['params']['status_filter']) ? (!in_array((string)$_REQUEST['params']['status_filter'], array('1','0'), true) ? NULL : $_REQUEST['params']['status_filter']) : NULL;


    //debug(true, false, $status_filter, (!in_array((string)$_REQUEST['params']['status_filter'], array('1','0'), true)), $_POST);

    $sql = "select TargetAudience, int_newsletter_id, int_newsletter_id as send, int_newsletter_id as send_test,str_subject,str_newsletter,bit_active, int_days from tbl_newsletter ";

    $sortby = '';
    $sortby = "order by int_newsletter_id desc";

    //debug(true, true, $designations, ( in_array($designations, array('', NULL, null, false)) ) );

    if (is_numeric($status_filter)) {
        $conditions[] = "bit_active='$status_filter'";
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    $sql .= " $sortby ";


    $field_details = array(
        'TargetAudience' => 'Audience',
        'str_subject' => 'Subject',
        'in_days' => 'In days',
        'bit_active' => 'Active?',
        'actions' => 'Commands',
        'send' => array(
            'link' => '#',
            'id_field' => 'int_newsletter_id',
            'button' => true,
            'name' => 'Send',
            'attributes' => array(
                'onclick' => "confirmSend('act_newsletter.php?newsletterid=ID_FIELD_VALUE&send=1&status=$status_filter'); return false;",
            ),
        ),
        'send_test' => array(
            'link' => '#',
            'id_field' => 'int_newsletter_id',
            'button' => true,
            'name' => 'Send in Test Mode',
            'attributes' => array(
                'onclick' => "confirmSend('act_newsletter.php?newsletterid=ID_FIELD_VALUE&send=1&test_mode=1&status=$status_filter'); return false;",
            ),
            'check_function' => array(
                'function' => 'check_if_stw_cruise',
                'params' => array('TargetAudience'),
                'params_field' => array('TargetAudience'),
            ),
        ),
    );

    $id_field = 'int_newsletter_id';

    $action_page__id_handler = 'newsletterid';


    //$query_pag_data = " $condition LIMIT $start, $per_page";
    $data_num_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

    mysqli_store_result($conn);
    $numrows = mysqli_num_rows($data_num_query);

    //echo $sql;

    $sql .= " LIMIT $limit OFFSET $limit_start ";
    //echo $sql;
    $data_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

}

function check_if_stw_cruise($audience) {
    return ( in_array($audience, array('Cruise', 'STW')) ) ? false : true;
}

?>

    <script language="JavaScript">
        <!--
        jQuery(document).ready(function () {
            $("#sms").click(function (event) {
                if (!$("#sms").is(":checked")) {
                    $(".sms_message_field_wrapper").addClass('hide').removeClass('show');
                    $(".message_field_wrapper").addClass('show').removeClass('hide');
                }
                else {
                    $(".sms_message_field_wrapper").addClass('show').removeClass('hide');
                    $(".message_field_wrapper").addClass('hide').removeClass('show');
                }
            });
        });

        function confirmCleanUp(Link) {
            if (confirm("Are you sure you want to delete this entry? \n\nThis action cannot be undone!!\n")) {
                window.location.href = Link;
            }
        }
        function confirmSend(Link) {
            if (confirm("Are you sure you want to send this newsletter to all the members?")) {
                window.location.href = Link;
            }
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
                <div class="col-md-10 col-xs-12 centering">
                    <?php if(!$is_edit && !$is_add): ?>
                        <!-- Buttons -->
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-12 header_buttons_wrapper text-center centering">
                                    <div class="col-xs- add_button_wrapper centering">
                                        <form action="<?php echo $self_page; ?>" method="post">
                                            <input type="hidden" name="params[status_filter]" value="<?php $status_filter; ?>">
                                            <input type="hidden" name="params[page]" value="<?php echo $page; ?>">
                                            <input type="hidden" name="page" value="<?php echo $page; ?>">
                                            <input type="hidden" name="goto" value="add">
                                            <input type="submit" name="add" value="Add New <?php echo $member_type_name; ?>" class="command">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Buttons -->
                    <?php else: ?>
                        <!-- Add Form -->
                        <?php if(!empty($mesg)): ?>
                            <div class="message  pb-lg pt-lg mb-lg mt-lg">
                                <div class="alert alert-<?php echo ( !empty($error) ? 'danger' : 'info' ); ?>">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <?php echo $mesg; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php //include_once('page.members_addedit_form.php'); ?>
                        <form name="theform" class="form-bordered news_addedit" action="<?php echo $self_page; ?>" method="post" enctype="multipart/form-data">
                            <header class="panel-heading">
                                <h2 class="panel-title text-center"><?php echo ( !empty($is_edit) ? 'Edit' : 'Add New' ); ?> <?php echo $member_type_name; ?></h2>
                            </header>
                            <div class="panel-body">
                                <div class="form-group">
                                    <input type="hidden" name="newsletterid" value="<?php echo ( ( !empty($newsletterid) ) ? $newsletterid : '' );?>">
                                    <label class="col-md-4 control-label" for="Audience">Audience</label>
                                    <div class="col-md-8">
                                        <select name="Audience" id="Audience" class="form-control" required>
                                            <?php
                                            $audiences = array(
                                                'ECs' => 'ECs',
                                                'Chapters' => 'Chapters',
                                                'Members' => 'Members',
                                                'Customers' => 'Customers',
                                                'STW' => 'STW Inquiry',
                                                'Cruise' => 'Cruise Inquiry',
                                                'All' => 'All',
                                            );

                                            foreach($audiences as $audi_key=>$audi) {
                                                $selected = ( !empty($audience) && ($audience == $audi_key) ? 'selected' : '' );
                                                $selected = ( !empty($audience) && ($audience == 'Both') && ($audi == 'All') ? 'selected' : '' );
                                                echo "<option value='$audi_key' $selected>$audi</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="subject">Subject</label>
                                    <div class="col-md-8">
                                        <input required type="text" name="subject" id="subject" value="<?php echo ( ( !empty($subject) ) ? $subject : '' );?>" class="form-control" placeholder="Subject" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="in_days">In Days</label>
                                    <div class="col-md-8">
                                        <input type="text" maxlength="3" name="in_days" id="subject" value="<?php echo ( ( !empty($in_days) ) ? $in_days : '' );?>" class="form-control width-auto" placeholder="In Days" />
                                    </div>
                                </div>
                                <div class="form-group sms_field_wrapper">
                                    <label class="col-md-4 control-label" for="sms">Send as sms</label>
                                    <div class="col-md-8">
                                        <input type="checkbox" name="sms" id="sms" value="1" <?php echo ( !empty($sms) ? 'checked="checked"' : '' ); ?>>
                                    </div>
                                </div>
                                <div class="form-group message_field_wrapper <?php echo ( !empty($sms) ? 'hide' : '' );?> <?php echo !empty($error_message['message']) ? 'has-error' : '';?>">
                                    <label class="col-md-4 control-label" for="message">Message</label>
                                    <div class="col-md-8">
                                        <textarea class="form-control" id="newsletter" name="newsletter"><?php echo !empty($newsletter) ? $newsletter : ''; ?></textarea>

                                        <script src="//cdn.ckeditor.com/4.5.11/full-all/ckeditor.js"></script>
                                        <!--<script> CKEDITOR.replace( 'news', { skin: 'kama' } ); </script>-->
                                        <script>//<![CDATA[
                                            CKEDITOR.replace('news', { "filebrowserBrowseUrl": "/jaadmin/../ckeditor/ckfinder/ckfinder.html", "filebrowserImageBrowseUrl": "/jaadmin/../ckeditor/ckfinder/ckfinder.html?type=Images", "filebrowserFlashBrowseUrl": "/jaadmin/../ckeditor/ckfinder/ckfinder.html?type=Flash", "filebrowserUploadUrl": "/jaadmin/../ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files", "filebrowserImageUploadUrl": "/jaadmin/../ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images", "filebrowserFlashUploadUrl": "/jaadmin/../ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash", skin: 'kama' });
                                            //]]>
                                        </script>
                                    </div>
                                </div>
                                <div class="form-group sms_message_field_wrapper <?php echo ( !empty($sms) ? '' : 'hide' );?> <?php echo !empty($error_message['sms_msg']) ? 'has-error' : '';?>">
                                    <label class="col-md-4 control-label" for="sms_msg">SMS Message</label>
                                    <div class="col-md-8">
                                        <textarea class="form-control" required id="sms_msg" rows="5" cols="80" maxlength="100" name="sms_msg"><?php echo !empty($sms_msg) ? $sms_msg : SMS_MESSAGE_BOX_PLACEHOLDER; ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <footer class="panel-footer">
                                <div class="row">
                                    <div class="col-sm-9 centering text-center">
                                        <input type="hidden" name="params[status_filter]" value="<?php $status_filter; ?>">
                                        <input type="hidden" name="params[page]" value="<?php echo $page; ?>">
                                        <input type="hidden" name="page" value="<?php echo $page; ?>">
                                        <?php echo ( !empty($is_add) ? '<input type="hidden" name="goto" value="add_entry">' : '<input type ="hidden" name="goto" value="update_entry">' ); ?>
                                        <button type="Submit" name="<?php echo ( !empty($is_add) ? 'add' : 'update'); ?>" value="" class="command  btn btn-default btn-success"><?php echo ( !empty($is_add) ? 'Add' : 'Update'); ?></button>
                                        <button id="cancel" type="button" name="cancel" value="Cancel" class="command btn btn-default btn-warning"  onClick="location.href='<?php echo $page_url.'&page='.$page.'&status_filter='.$status_filter; ?>';">Cancel</button>
                                    </div>
                                </div>
                            </footer>
                        </form>
                        <!-- /Add Form -->
                    <?php endif; ?>
                </div>
                <div class="clearfix"></div>
            </section>
            <?php if(!$is_edit && !$is_add): ?>
                <section class="panel">
                    <div class="col-xs-12">
                        <header class="panel-heading">
                            <h2 class="panel-title text-center">List of <?php echo $member_type_name_plural; ?></h2>
                        </header>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-12 filter_wrapper ">
                                    <?php if(!empty($mesg)): ?>
                                        <div class="message  pb-lg pt-lg mb-lg mt-lg">
                                            <div class="alert alert-success">
                                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                                <?php echo $mesg; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="table-responsive">
                                        <div class="col-lg-6 col-md-10 col-sm-12 col-xs-12 centering date_range_wrapper">
                                            <form action="<?php echo $self_page; ?>" method="post">
                                                <table class="table table-bordered table-striped mb-none">
                                                    <tr>
                                                        <td>Status Filter:</td>
                                                        <td>
                                                            <select name="params[status_filter]" id="params[status_filter]">
                                                                <option value="none" <?php echo ( (is_null($status_filter) || !isset($status_filter)) ? 'selected' : '' ); ?>>All</option>
                                                                <option value="1" <?php echo ( (isset($status_filter) && ($status_filter == 1)) ? 'selected' : '' ); ?>>Active</option>
                                                                <option value="0" <?php echo ( (isset($status_filter) && ($status_filter == 0)) ? 'selected' : '' ); ?>>Deactive</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2">
                                                            <div class="row">
                                                                <div class="col-xs-12 text-center">
                                                                    <input type="submit" class="command btn btn-sm col-xs-4 centering" name="go" value="GO!">
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php require_once('display_members_data.php'); ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </section>
            <?php endif; ?>
        </div>
    </div>


<?php
require_once("templates/footer.php");
