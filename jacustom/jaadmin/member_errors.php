<?php
$page_name = 'Manage Member Comments/Errors';
$page_title = 'John Amico - ' . $page_name;

$is_popup = !empty($_GET['popup']) ? 1 : 0;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

if( !$is_popup ) {
    require_once("templates/header.php");
    //require_once("templates/sidebar.php");
} else {
    $display_header = false;
    require_once("templates/header.php");
}



$member_type_name = 'Comment / Error';
$member_type_name_plural = 'Comments / Errors';
$self_page = 'member_errors.php';
$page_url = base_admin_url() . "/$self_page?";
$action_page = 'member_errors.php';
$action_page_url = base_admin_url() . "/$self_page?1=1";
//$export_url = base_admin_url() . '/members_export.php';


$is_edit = $is_add = false;
$success_message = $error_message = $member_search_result = $conditions = array();

//debug(true, true, $_POST, (!empty($action) && $action=='Add') );

$member_id = $_SESSION['member']['ses_member_id'];
$mlm_id = ( !empty($_GET['mlm_id']) ? filter_var($_GET['mlm_id'], FILTER_SANITIZE_NUMBER_INT) : '' );

$mesg = ( !empty($_GET['msg']) ? $_GET['msg'] : '' );

$redirectto = !empty($_POST['redirectto']) ? $_POST['redirectto'] : $self_page;



//echo '<pre>'; print_r($_POST); die();
if(isset($_POST['memberid']) and $_POST['memberid'] > 0) {
    $res = mysqli_query($conn,"select int_customer_id from tbl_member WHERE int_member_id = ".(int)$_POST['memberid']);
    list($mlm_id)= mysqli_fetch_row($res);
}

//debug(true, true, $mlm_id, $_POST);

if(!empty($mlm_id)) {

    if(!empty($_POST['goto']) && ($_POST['goto'] == 'add') ) {
        $is_add = true;
    }

    if(!empty($_REQUEST['id']) && is_numeric($_REQUEST['id']) ) {
        $is_edit = true;
        $is_add = false;

        $query = "SELECT id, UNIX_TIMESTAMP(date1) as date1, category, type, notes, status, UNIX_TIMESTAMP(date2) as date2 FROM tbl_mlm_errors WHERE id=".(int)$_GET['id'];
        $res = mysqli_query($conn,$query);
        $errorcom_note = mysqli_fetch_assoc($res);

        extract($errorcom_note);
    }

    if (!empty($action) && $action == 'Add') {
        $query = "INSERT INTO tbl_mlm_errors VALUES ( '', $mlm_id, now(), '{$_POST['category']}', '" . addslashes($_POST['type']) . "', '" . addslashes($_POST['notes']) . "', '{$_POST['status']}', " . ($_POST['status'] == 'Resolved' ? 'now()' : "''") . ")";
        mysqli_query($conn, $query);
        if ($_POST['category'] == 'Error') {
            mysqli_query($conn, "UPDATE customers SET errors_no = errors_no + 1 WHERE customers_id = $mlm_id");
        }

        $is_edit = $is_add = false;
    }
    if (!empty($action) && $action == 'Update') {
        $query = "UPDATE tbl_mlm_errors SET notes = '" . addslashes($_POST['notes']) . "', status = '{$_POST['status']}', date2 = " . ($_POST['status'] == 'Resolved' ? 'now()' : "''") . " WHERE id = " . (int) $_POST['id'];
        mysqli_query($conn, $query);

        $is_edit = $is_add = false;
    }


    if (!$is_edit && !$is_add && !empty($member_id)) {

        $limit = 50;
        $page = ((!empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) ? $_REQUEST['page'] : 1);

        $limit_start = ($page * $limit) - $limit;
        $limit_end = ($page * $limit);


        $conditions = $sortby = array();

        //debug(true, false, $status_filter, (!in_array((string)$_REQUEST['params']['status_filter'], array('1','0'), true)), $_POST);

        $sql = "SELECT id, UNIX_TIMESTAMP(date1) as date1, category, type, notes, status as error_com_status, UNIX_TIMESTAMP(date2) as date2
            FROM tbl_mlm_errors
    ";


        $sortby = '';
        $sortby = "ORDER BY date1 DESC";

        $conditions[] = " mlm_id='$mlm_id' ";

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        $sql .= " $sortby ";

        //debug(true, true, $sql);

        $field_details = array(
            'date1' => array(
                'field_type' => 'text_from_callback',
                'name' => 'Date',
                'value' => array(
                    'function' => 'date',
                    'params' => array('m/d/y <\b\r> h:i:s A', 'date1'),
                    'params_field' => array('', 'date1'),
                ),
            ),
            'category' => 'Category',
            'type' => 'Type',
            'notes' => array(
                'field_type' => 'text_from_callback',
                'name' => 'Notes',
                'value' => array(
                    'function' => 'display_errorcom_note_text',
                    'params' => array('notes'),
                    'params_field' => array('notes'),
                ),
            ),
            'error_com_status' => 'Status',
            'date2' => array(
                'field_type' => 'text_from_callback',
                'name' => 'Date Resolved',
                'value' => array(
                    'function' => 'date',
                    'params' => array('m/d/y <\b\r> h:i:s A', 'date2'),
                    'params_field' => array('', 'date2'),
                ),
            ),
            'id' => array(
                'field_type' => 'text_from_callback',
                'name' => 'Action',
                'value' => array(
                    'function' => 'display_errorcom_note_action',
                    'params' => array('id', 'error_com_status', $page_url, $mlm_id),
                    'params_field' => array('id', 'error_com_status'),
                ),
            ),
        );

        $id_field = 'int_annotate_note_id';

        $action_page__id_handler = 'noteid';


        //$query_pag_data = " $condition LIMIT $start, $per_page";
        $data_num_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

        mysqli_store_result($conn);
        $numrows = mysqli_num_rows($data_num_query);

        //echo $sql;

        $sql .= " LIMIT $limit OFFSET $limit_start ";
        //echo $sql;
        $data_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

        $tot1 = $tot2 = 0;
        while ($row = mysqli_fetch_assoc($data_num_query)) {
            if ($row['category'] == 'Error') {
                $tot1++;
            }
            else {
                $tot2++;
            }
        }

    }
}

function display_errorcom_note_text($note) {
    $output = '';

    if(!empty($note)) {
        $output = "<div class='' style='width: 250px; white-space: normal;'>".nl2br($note)."</div>";
        //$output = nl2br($note);
    }

    return $output;
}

function display_errorcom_note_action($note_id, $status, $page_uri, $mlm_id) {
    $output = '';

    if(!empty($note_id) && ($status != 'Resolved') ) {
        $output = "<a href='$page_uri&mlm_id=$mlm_id&id=$note_id' class='btn btn-primary'>Edit</a>";
    }

    return $output;
}


?>

    <div role="main" class="content-body <?php echo ( $is_popup ? 'no-margin-left' : '' ); ?> ">
        <?php if(!$is_popup): ?>
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
        <?php endif; ?>

        <div class="row ">
            <div class="col-xs-12 centering">
                <section class="panel">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                    </header>
                    <div class="panel-body">
                        <?php if( ($is_edit || $is_add) && !empty($mlm_id) ): ?>
                            <form name="theform" class="form-bordered note_edit" action="<?php echo $self_page; ?>?mlm_id=<?=$mlm_id?>" method="post">
                                <header class="panel-heading">
                                    <h2 class="panel-title text-center"><?php echo ( !empty($is_add) ? 'Add New' : 'Edit' ); ?> <?php echo $member_type_name; ?></h2>
                                </header>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Date</label>
                                        <div class="col-md-8"><p class="form-control-static"><?php echo ( !empty($date1) ? date('m/d/y h:i:s A',$date1) : date('m/d/y h:i:s A') ); ?></p></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Category</label>
                                        <div class="col-md-8">
                                            <?php if( !empty($category) ) : ?>
                                                <p class="form-control-static"><?php echo $category; ?></p>
                                            <?php else: ?>
                                                <select class="form-control" name="category">
                                                    <option value="Error"> Error</option>
                                                    <option value="Comment" selected=""> Comment</option>
                                                </select>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Type</label>
                                        <div class="col-md-8">
                                            <?php if( !empty($type) ) : ?>
                                                <p class="form-control-static"><?php echo $type; ?></p>
                                            <?php else: ?>
                                                <select class="form-control" name="type">
                                                    <?php
                                                    $query = "SELECT title FROM tbl_mlm_errors_types ORDER BY title";
                                                    $res = mysqli_query($conn,$query);
                                                    while($row = mysqli_fetch_assoc($res)){
                                                        ?>
                                                        <option value="<?=$row['title']?>"><?=$row['title']?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Notes</label>
                                        <div class="col-md-8"><textarea class="form-control" name="notes" cols="30" rows="5"><?php echo ( !empty($notes) ? $notes : '' ); ?></textarea></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Status</label>
                                        <div class="col-md-8">
                                            <select class="form-control" name="status" onchange="setDate1(this.value);">
                                                <option value="Pending" <?=(!empty($status) && $status=="Pending"?'selected':'')?>> Pending</option>
                                                <option value="Resolved" <?=(!empty($status) && $status=="Resolved"?'selected':'')?>> Resolved</option>
                                            </select>
                                            <script language="JavaScript">
                                                function setDate1(status) {
                                                    var input = window.document.getElementById("dat2");
                                                    if(status=='Resolved'){
                                                        setDate(input);
                                                    }else{
                                                        input.value = '';
                                                    }
                                                }
                                                function setDate(input) {
                                                    input.value = '<?=date('m/d/y h:i:s A');?>';
                                                }
                                            </script>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Date</label>
                                        <div class="col-md-8"><p class="form-control-static">
                                                <input type="text" name="date2" size="18" maxlength="256" id="dat2" readonly>
                                        </p></div>
                                    </div>
                                </div>
                                <footer class="panel-footer">
                                    <div class="row">
                                        <div class="col-sm-9 centering text-center">
                                            <input type="hidden" name="id" value="<?=$_GET['id']?>">
                                            <input type="hidden" name="memberid" value="<?=$_GET['memberid']?>">
                                            <input type="submit" name="action" value="<?php echo ( !empty($is_add) ? 'Add' : 'Update' ) ;?>" class="command" title="">
                                            <input type="hidden" name="page" value="<?php echo $page; ?>">
                                            <?php echo ( !empty($is_add) ? '<input type="hidden" name="goto" value="add_entry">' : '<input type ="hidden" name="goto" value="update_entry">' ); ?>
                                            <button id="cancel" type="button" name="cancel" value="Cancel" class="command btn btn-default btn-warning"  onClick="location.href='<?php echo $page_url."&mlm_id=$mlm_id&page=$page"; ?>';">Cancel</button>
                                        </div>
                                    </div>
                                </footer>
                            </form>
                        <?php endif; ?>
                        <?php if(!$is_edit && !$is_add && !empty($mlm_id) ): ?>
                            <div class="col-xs-12 centering">
                                <div class="row">
                                    <div class="mt-lg"></div>
                                    <div class="col-xs-12 text-center font-size__20 mb-lg">
                                        <?php
                                        list($customers_first_name, $customers_last_name) = mysqli_fetch_row(mysqli_query($conn,"SELECT customers_firstname, customers_lastname FROM customers WHERE customers_id = '$mlm_id'"));
                                        echo $customers_first_name . " " . $customers_last_name . "<br><br>";
                                        ?>
                                    </div>

                                    <div class="col-xs-12 header_buttons_wrapper text-center">
                                        <div class="col-xs- add_button_wrapper centering">
                                            <form action="<?php echo $self_page; ?>?mlm_id=<?=$mlm_id?>" method="post">
                                                <input type="hidden" name="page" value="<?php echo $page; ?>">
                                                <input type="hidden" name="goto" value="add">
                                                <input type="hidden" name="memberid" value="<?=$_POST['memberid']?>">
                                                <input type="submit" name="action" value="Add Error or Comment" class="command" title="">
                                                <button type="button" onclick="window.location.href='<?php echo $redirectto;?>';" class="btn btn-primary btn-warning ml-lg">Go Back to Member's Page</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="mt-lg"></div>

                                    <div class="col-xs-12 text-left mb-lg">
                                        <?php
                                        echo "Total Errors: $tot1<br>Total Comments: $tot2";
                                        ?>
                                    </div>
                                    <div class="col-xs-12 filter_wrapper ">
                                        <?php if(!empty($mesg)): ?>
                                            <div class="message  pb-lg pt-lg mb-lg mt-lg">
                                                <div class="alert alert-success">
                                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                                    <?php echo $mesg; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php require_once("../$admin_path/display_members_data.php"); ?>
                            </div>
                            <div class="clearfix"></div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>


<?php
require_once("templates/footer.php");
