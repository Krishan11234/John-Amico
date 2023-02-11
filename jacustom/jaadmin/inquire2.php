<?php
$page_name = 'Salon Cruises Inquiry List';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");



$member_type_name = 'Inquiry';
$member_type_name_plural = 'Inquiries';
$self_page = 'inquire2.php';
$page_url = base_admin_url() . "/$self_page?1=1";
$action_page = 'inquire2.php';
$action_page_url = base_admin_url() . "/$self_page?1=1";
$export_url = base_admin_url() . '/export-inquire2.php';


$is_edit = $is_add = false;
$success_message = $error_message = array();

//debug(false, true, $_POST);

$mesg = ( !empty($_GET['msg']) ? $_GET['msg'] : '' );


$limit = 50;
$page = ((!empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) ? $_REQUEST['page'] : 1);

$limit_start = ($page * $limit) - $limit;
$limit_end = ($page * $limit);


if ($_GET['delete'] && ($_GET['delete']==1) && !empty($_GET['inquiryid']) ) {
    $id = filter_var($_GET['inquiryid'], FILTER_SANITIZE_NUMBER_INT);

    $delsql="DELETE FROM salon_inquire WHERE id = '$id' LIMIT 1";
    $result=mysqli_query($conn,$delsql) or die(mysql_error());
    $delsub=0;

    header("Location: ".pagination_url($page, $self_page));
}


$conditions = $sortby = array();

$sql = "SELECT *, DATE_FORMAT(date_mailed, '%m/%d/%Y') as date_mailed, DATE_FORMAT(date_followup, '%m/%d/%Y') as date_followup FROM salon_inquire ";

$sortby = '';
$sortby = "ORDER BY id DESC";

//debug(true, true, $designations, ( in_array($designations, array('', NULL, null, false)) ) );

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$sql .= " $sortby ";

$table_headers = array(
    'id'=>'ID', 'date'=>'Date', 'User_name'=>'Name', 'business'=> 'Business Name', 'address'=> 'Address', 'city'=>'City', 'province'=>'State', 'postal_code'=>'Zip Code', 'dayime_phone'=>'Daytime Phone', 'other_phone'=>'Other Phone', 'fax_number'=>'Fax',

    'User_email'=>array(
        'link' => 'mailto:ID_FIELD_VALUE',
        'id_field' => 'User_email',
        'name' => 'Email',
        'text_to_display' => 'ID_FIELD_VALUE',
    ),


    'company'=>'Type of Company','position'=>'Company Position','services'=>'Company Services','have_cruise'=>'Have you Cruised Before','Who_may_be_joining_you'=>'Who is Joining You','working_at_location'=>'Number Working at Your Locations','date_mailed'=>'Date Mailed','date_followup'=>'Date Followed Up','call_completed'=>'Call Completed','notes'=>'Notes','source'=>'Source',

    'edit'=>array(
        'link' => '#',
        'id_field' => 'id',
        'name' => 'Edit',
        'button' => true,
        'attributes' => array(
            'onclick' => "window.open('edit_inquire.php?id=ID_FIELD_VALUE','ei', 'height=600, width=400, scrollbars=yes, sizable=no, location=no, status=no'); return false;",
        ),
    ),

    'actions'=>'Delete'
);

$field_details = $table_headers;

$id_field = 'id';
$action_page__id_handler = 'inquiryid';
$no_edit_button = true;


//$query_pag_data = " $condition LIMIT $start, $per_page";
$data_num_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

mysqli_store_result($conn);
$numrows = mysqli_num_rows($data_num_query);

//echo $sql;

$sql .= " LIMIT $limit OFFSET $limit_start ";
//echo $sql;
$data_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));



?>

    <script language="JavaScript">
        <!--

        function confirmCleanUp(Link) {
            if (confirm("Are you sure you want to delete this Inquiry? \n\nThis action cannot be undone!!\n")) {
                location.href = Link;
            }
        }

        -->
    </script>
    <script>var collapse_left_sidebar=true;</script>

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
                                        <a href="#" class="btn btn-primary" onClick="window.open('add_inquire.php?id=<?=$row['id'];?>','ei', 'height=600, width=400, scrollbars=yes, sizable=no, location=no, status=no')">Add New <?php echo $member_type_name; ?></a>
                                    </div>
                                    <div class="col-xs- export_button_wrapper centering">
                                        <input type="button" onclick="document.location.href='<?php echo $export_url; ?>'" value="Export Active <?php echo $member_type_name_plural; ?>" class="command">
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
