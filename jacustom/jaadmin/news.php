<?php
$page_name = 'Manage News';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");



$member_type_name = 'News';
$member_type_name_plural = 'News';
$self_page = 'news.php';
$page_url = base_admin_url() . "/$self_page?1=1";
$action_page = 'act_news.php';
$action_page_url = base_admin_url() . "/$self_page?1=1";
//$export_url = base_admin_url() . '/members_export.php';


$is_edit = $is_add = false;
$success_message = $error_message = array();

//debug(false, true, $_POST);

$mesg = ( !empty($_GET['msg']) ? $_GET['msg'] : '' );

if( !empty($_POST['newsid']) && !empty($_POST['goto']) ) {
    if($_POST['goto'] == 'update') {
        $rsnews = mysqli_query($conn, "select int_news_id, str_date, str_title, str_news, bit_active from tbl_news WHERE int_news_id = {$_POST['newsid']}");
        list($newsid, $date, $title, $news, $active) = mysqli_fetch_row($rsnews);
        list($y1, $m1, $d1) = explode('-', $date);
        $nr = "NO";

        $is_edit = true;
    }
    elseif( $_POST['goto'] == 'update_entry' ) {
        $table = "tbl_news";
        $fieldlist="str_date='{$_POST['y1']}-{$_POST['m1']}-{$_POST['d1']}', str_title='{$_POST['title']}', str_news='{$_POST['news']}'";
        $condition=" where int_news_id = {$_POST['newsid']}";
        $result=update_rows($conn, $table, $fieldlist, $condition);

        $mesg = "News entry updated successfully.";

        $is_edit = $is_add = false;
    }
} elseif ( empty($_POST['newsid']) && !empty($_POST['goto']) ) {
    if ($_POST['goto'] == 'add') {
        $is_add = true;
    }
    elseif( $_POST['goto'] == 'add_entry' ) {
        $table = "tbl_news";				// inserting values to setting table
        $in_fieldlist="str_date,str_title,str_news,bit_active";
        $in_values="'{$_POST['y1']}-{$_POST['m1']}-{$_POST['d1']}','{$_POST['title']}','{$_POST['news']}',1";
        $result=insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values
        //$int_news_id = mysqli_insert_id($conn);

        $mesg = "News entry added successfully.";

        $is_edit = $is_add = false;
    }
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

    $sql = "select int_news_id,str_date,str_title,str_news,bit_active from tbl_news ";

    $sortby = '';
    $sortby = "order by str_date desc";

    //debug(true, true, $designations, ( in_array($designations, array('', NULL, null, false)) ) );

    if (is_numeric($status_filter)) {
        $conditions[] = "bit_active='$status_filter'";
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    $sql .= " $sortby ";


    $field_details = array(
        'str_date' => 'Date',
        'str_title' => 'Title',
        'bit_active' => 'Active?',
        'actions' => 'Commands',
    );

    $id_field = 'int_news_id';

    $action_page__id_handler = 'newsid';


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
            if (confirm("Are you sure you want to delete this Member? \n\nThis action cannot be undone!!\nThe children of this member, if any, will be placed under his parent.")) {
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
                                </div>
                            </div>
                        </div>
                        <!-- /Buttons -->
                    <?php else: ?>
                        <!-- Add Form -->
                        <?php //include_once('page.members_addedit_form.php'); ?>
                        <form name="theform" class="form-bordered news_addedit" action="<?php echo $self_page; ?>" method="post">
                            <header class="panel-heading">
                                <h2 class="panel-title text-center"><?php echo ( !empty($is_edit) ? 'Edit' : 'Add New' ); ?> <?php echo $member_type_name; ?></h2>
                            </header>
                            <div class="panel-body">
                                <div class="form-group">
                                    <input type="hidden" name="newsid" value="<?php echo ( ( !empty($is_edit) && !empty($newsid) ) ? $newsid : '' );?>">
                                    <label class="col-md-4 control-label" for="d1">Date</label>
                                    <div class="col-md-8">
                                        <div class="row form-group">
                                            <div class="col-lg-4">
                                                <select name="d1" id="d1" class="form-control" required>
                                                    <option>Day</option>
                                                    <?php
                                                    $days = range(1, 31);
                                                    $todays_day = ( ( !empty($is_edit) && !empty($d1) ) ? $d1 : date('d') );

                                                    foreach($days as $day) {
                                                        echo '<option value="'.$day.'" '.( ($todays_day == $day) ? 'selected' : '' ).' >'.$day.'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="mb-md hidden-lg hidden-xl"></div>
                                            <div class="col-lg-4">
                                                <select name="m1" id="m1" class="form-control" required>
                                                    <option>Month</option>
                                                    <?php
                                                    $months = range(1, 12);
                                                    $todays_mon = ( ( !empty($is_edit) && !empty($m1) ) ? $m1 : date('m') );

                                                    foreach($months as $mon) {
                                                        echo '<option value="'.$mon.'" '.( ($todays_mon == $mon) ? 'selected' : '' ).' >'.$mon.'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="mb-md hidden-lg hidden-xl"></div>
                                            <div class="col-lg-4">
                                                <select name="y1" id="y1" class="form-control" required>
                                                    <option>Year</option>
                                                    <?php
                                                    $current_year = date('Y');
                                                    $todays_year = ( ( !empty($is_edit) && !empty($y1) ) ? $y1 : date('Y') );

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
                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="d1">Title</label>
                                    <div class="col-md-8">
                                        <input required type="text" name="title" value="<?php echo ( ( !empty($is_edit) && !empty($title) ) ? $title : '' );?>" class="form-control" placeholder="Title" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-12 control-label" for="d1">Details</label>
                                    <div class="col-md-12">
                                        <textarea class="form-control" id="news" name="news"><?php echo !empty($news) ? $news : ''; ?></textarea>

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
