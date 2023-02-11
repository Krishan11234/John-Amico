<?php
$page_name = 'Manage Newsletters';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");



$member_type_name = 'Newsletter';
$member_type_name_plural = 'Newsletters';
$self_page = 'newsletter_new.php';
$page_url = base_admin_url() . "/$self_page?1=1";
$action_page = 'newsletter_new.php';
$action_page_url = base_admin_url() . "/$action_page?1=1";
//$export_url = base_admin_url() . '/members_export.php';


$is_edit = $is_add = false;
$success_message = $error_message = array();

//debug(false, true, $_POST);

$mesg = ( !empty($_GET['msg']) ? $_GET['msg'] : '' );
$error = false;

if( !empty($_POST['newsletterid']) && !empty($_POST['goto']) ) {
    if($_POST['goto'] == 'update') {
        $rsnewsletter = mysqli_query($conn,"select id, title, body,status, img, img1, img2, img3, img4, short_descr, date_format(dt, '%m'), date_format(dt, '%d'), date_format(dt, '%Y')  from tbl_newsletter_new WHERE id = {$_POST['newsletterid']}");
        list($newsletterid,$subject,$newsletter,$active, $img, $img1, $img2, $img3, $img4, $short_descr, $month, $day, $year )= mysqli_fetch_row($rsnewsletter);
        $nr = "NO";

        $is_edit = true;
    }
    elseif( $_POST['goto'] == 'update_entry' ) {
        $subject = trim(filter_var($_POST['subject'], FILTER_SANITIZE_STRING));
        $newsletter = trim(mysqli_real_escape_string($conn, $_POST['newsletter']));
        $short_descr = trim(mysqli_real_escape_string($conn, $_POST['short_descr']));

        $mewsletterid = ( !empty($_POST['newsletterid']) ? filter_var($_POST['newsletterid'], FILTER_SANITIZE_NUMBER_INT) : '' );

        $day = ( !empty($_POST['day']) ? filter_var($_POST['day'], FILTER_SANITIZE_NUMBER_INT) : date('d') );
        $month = ( !empty($_POST['month']) ? filter_var($_POST['month'], FILTER_SANITIZE_NUMBER_INT) : date('m') );
        $year = ( !empty($_POST['year']) ? filter_var($_POST['year'], FILTER_SANITIZE_NUMBER_INT) : date('Y') );

        if(!empty($mewsletterid) && !empty($subject) && (!empty($newsletter) || !empty($short_descr)) ) {
            $table = "tbl_newsletter_new";
            $dt = $year . '-' . $month . '-' . $day;
            $fieldlist = "title='{$subject}', body='{$newsletter}', short_descr='{$short_descr}', dt='$dt' ";
            $condition = " where id = {$_POST['newsletterid']}";
            $result = update_rows($conn, $table, $fieldlist, $condition);

            //debug(false, false, $_FILES, $_POST);

            for($i=0; $i < 5; $i++) {
                $key = 'img' .  ( ($i==0) ? '' : $i );

                if ( !empty($_FILES[ $key ]) && !empty($_FILES[ $key ]['name'])) {
                    $id = $mewsletterid;
                    $img_file = $id . '_news_' . $_FILES[ $key ]['name'];

                    //if($i > 0) debug(true, true, $id, $img_file );

                    if (move_uploaded_file($_FILES[ $key ]['tmp_name'], "../images/" . $img_file)) {
                        //echo '<pre>'; print_r($img_file); die();
                        $table = "tbl_newsletter_new";
                        $fieldlist = " `$key`='$img_file'  ";
                        $condition = " where id = '$id'";
                        $result = update_rows($conn, $table, $fieldlist, $condition);
                    }
                }
            }

            $mesg = "News entry updated successfully.";
        }

        $is_edit = $is_add = false;
    }
} elseif ( empty($_POST['newsletterid']) && !empty($_POST['goto']) ) {
    if ($_POST['goto'] == 'add') {
        $is_add = true;
    }
    elseif( $_POST['goto'] == 'add_entry' ) {
        $subject = trim(filter_var($_POST['subject'], FILTER_SANITIZE_STRING));
        $newsletter = trim(mysqli_real_escape_string($conn, $_POST['newsletter']));
        $short_descr = trim(mysqli_real_escape_string($conn, $_POST['short_descr']));

        if(!empty($subject) && (!empty($newsletter) || !empty($short_descr)) ) {
            $table = "tbl_newsletter_new";                // inserting values to setting table
            //$dt = $year . '-' . $month . '-' . $day;
            $dt = date('Y-m-d');
            $in_fieldlist = "title,body,status, short_descr, dt";
            $in_values = "'{$subject}','{$newsletter}',1, '{$short_descr}', '$dt' ";
            $result = insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values

            $id = mysqli_insert_id($conn);

            for($i=0; $i < 5; $i++) {
                $key = 'img' .  ( ($i==0) ? '' : $i );

                if ( !empty($_FILES[ $key ]) and !empty($_FILES[ $key ]['name'])) {

                    $img_file = $id . '_news_' . $_FILES[ $key ]['name'];
                    if (move_uploaded_file($_FILES[ $key ]['tmp_name'], "../images/" . $img_file)) {
                        $table = "tbl_newsletter_new";
                        $fieldlist = " `$key`='$img_file'  ";
                        $condition = " where id = '$id'";
                        $result = update_rows($conn, $table, $fieldlist, $condition);
                    }
                }
            }

            //die();

            $mesg = "Newsletter entry added successfully.";

            $is_edit = $is_add = false;
        } else {
            $mesg = 'You need to enter newsletter subject with description';
            $error = true;
            $is_add = true;
        }


    }
} elseif ( !empty($_POST['newsletterid']) && is_numeric($_POST['newsletterid']) && !empty($_POST['activate']) ) {
    $newsletterid = filter_var($_POST['newsletterid'], FILTER_SANITIZE_NUMBER_INT);
    $make_active = ( ($_POST['active'] == 0) ? 1 : 0 );

    $table = "tbl_newsletter_new";
    $fieldlist = "status=$make_active";
    $condition = " where id = {$newsletterid}";
    $result = update_rows($conn, $table, $fieldlist, $condition);
}

if ( !empty($_GET['newsletterid']) && is_numeric($_GET['newsletterid']) && !empty($_GET['delete']) ) {
    $newsletterid = filter_var($_GET['newsletterid'], FILTER_SANITIZE_NUMBER_INT);

    $table = "tbl_newsletter_new";
    $condition = " where id = {$newsletterid}";
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

    $sql = "select id, title, body, status,short_descr, date_format(dt, '%m/%d/%Y') as dtn  from tbl_newsletter_new ";

    $sortby = '';
    $sortby = "order by dt desc, title";

    //debug(true, true, $designations, ( in_array($designations, array('', NULL, null, false)) ) );

    if (is_numeric($status_filter)) {
        $conditions[] = "status='$status_filter'";
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    $sql .= " $sortby ";


    $field_details = array(
        'title' => 'Title',
        'dtn' => 'Date',
        'status' => 'Active?',
        'actions' => 'Commands',
    );

    $id_field = 'id';

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

?>

    <script language="JavaScript">
        <!--

        function confirmCleanUp(Link) {
            if (confirm("Are you sure you want to delete this entry? \n\nThis action cannot be undone!!\n")) {
                location.href = Link;
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
                <div class="col-md-8 col-xs-12 centering">
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
                                    <div class="col-xs- add_button_wrapper centering">
                                        <button class="btn btn-primary btn-success" onclick="window.location='<?php echo base_admin_url() . '/send_newsletter.php'; ?>'">Send Newsletter</button>
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
                                    <label class="col-md-4 control-label" for="subject">Subject</label>
                                    <div class="col-md-8">
                                        <input required type="text" name="subject" id="subject" value="<?php echo ( ( !empty($subject) ) ? $subject : '' );?>" class="form-control" placeholder="Subject" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="month">Date</label>
                                    <div class="col-md-8">
                                        <div class="row form-group">
                                            <div class="col-lg-4">
                                                <select name="month" id="month" class="form-control" required>
                                                    <option>Month</option>
                                                    <?php
                                                    $months = range(1, 12);
                                                    $todays_mon = ( ( !empty($month) ) ? $month : date('m') );

                                                    foreach($months as $mon) {
                                                        echo '<option value="'.$mon.'" '.( ($todays_mon == $mon) ? 'selected' : '' ).' >'.$mon.'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="mb-md hidden-lg hidden-xl"></div>
                                            <div class="col-lg-4">
                                                <select name="day" id="day" class="form-control" required>
                                                    <option>Day</option>
                                                    <?php
                                                    $days = range(1, 31);
                                                    $todays_day = ( ( !empty($day) ) ? $day : date('d') );

                                                    foreach($days as $day) {
                                                        echo '<option value="'.$day.'" '.( ($todays_day == $day) ? 'selected' : '' ).' >'.$day.'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="mb-md hidden-lg hidden-xl"></div>
                                            <div class="col-lg-4">
                                                <select name="year" id="year" class="form-control" required>
                                                    <option>Year</option>
                                                    <?php
                                                    $current_year = date('Y');
                                                    $todays_year = ( ( !empty($year) ) ? $year : date('Y') );

                                                    $years = range(2006, ($current_year+5));

                                                    foreach($years as $year) {
                                                        echo '<option value="'.$year.'" '.( ($todays_year == $year) ? 'selected' : '' ).' >'.$year.'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php for($i=0; $i < 5; $i++) : ?>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Image #<?php echo $i+1; ?></label>
                                        <div class="col-md-8">
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <div class="fileupload fileupload-new" data-provides="fileupload">
                                                        <div class="input-append">
                                                            <div class="uneditable-input">
                                                                <i class="fa fa-file fileupload-exists"></i>
                                                                <span class="fileupload-preview"></span>
                                                            </div>
                                                        <span class="btn btn-default btn-file">
                                                            <span class="fileupload-exists">Change</span>
                                                            <span class="fileupload-new">Select file</span>
                                                            <input type="file" name="img<?php echo ( ($i==0) ? '' : $i ); ?>">
                                                        </span>
                                                            <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">Remove</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                if( !empty($edit) ):
                                                    $image = 'img' .  ( ($i==0) ? '' : $i );

                                                    if(!empty($$image)) :
                                                ?>
                                                        <div class="col-xs-12">
                                                            <p class="form-control-static">
                                                                <a href="../images/<?php echo $$image?>" target="_blank" ><?php echo $$image?></a>
                                                            </p>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endfor; ?>

                                <div class="form-group">
                                    <label class="col-md-12 control-label" for="short_descr">Short Description</label>
                                    <div class="col-md-12">
                                        <textarea class="form-control" id="short_descr" name="short_descr"><?php echo !empty($short_descr) ? $short_descr : ''; ?></textarea>

                                        <script src="//cdn.ckeditor.com/4.5.11/full-all/ckeditor.js"></script>
                                        <!--<script> CKEDITOR.replace( 'news', { skin: 'kama' } ); </script>-->
                                        <script>//<![CDATA[
                                            CKEDITOR.replace('news', { "filebrowserBrowseUrl": "/jaadmin/../ckeditor/ckfinder/ckfinder.html", "filebrowserImageBrowseUrl": "/jaadmin/../ckeditor/ckfinder/ckfinder.html?type=Images", "filebrowserFlashBrowseUrl": "/jaadmin/../ckeditor/ckfinder/ckfinder.html?type=Flash", "filebrowserUploadUrl": "/jaadmin/../ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files", "filebrowserImageUploadUrl": "/jaadmin/../ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images", "filebrowserFlashUploadUrl": "/jaadmin/../ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash", skin: 'kama' });
                                            //]]>
                                        </script>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-12 control-label" for="newsletter">Long Description</label>
                                    <div class="col-md-12">
                                        <textarea class="form-control" id="newsletter" name="newsletter"><?php echo !empty($newsletter) ? $newsletter : ''; ?></textarea>

                                        <script src="//cdn.ckeditor.com/4.5.11/full-all/ckeditor.js"></script>
                                        <!--<script> CKEDITOR.replace( 'news', { skin: 'kama' } ); </script>-->
                                        <script>//<![CDATA[
                                            CKEDITOR.replace('news', { "filebrowserBrowseUrl": "/jaadmin/../ckeditor/ckfinder/ckfinder.html", "filebrowserImageBrowseUrl": "/jaadmin/../ckeditor/ckfinder/ckfinder.html?type=Images", "filebrowserFlashBrowseUrl": "/jaadmin/../ckeditor/ckfinder/ckfinder.html?type=Flash", "filebrowserUploadUrl": "/jaadmin/../ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files", "filebrowserImageUploadUrl": "/jaadmin/../ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images", "filebrowserFlashUploadUrl": "/jaadmin/../ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash", skin: 'kama' });
                                            //]]>
                                        </script>
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
