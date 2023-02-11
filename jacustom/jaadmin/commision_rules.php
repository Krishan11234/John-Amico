<?php
$page_name = 'Commission Rules';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

$mtype = 'c';


$member_type_name = 'Commission Rule';
$member_type_name_plural = 'Commission Rules';
$self_page = 'commision_rules.php';
$page_url = base_admin_url() . '/commision_rules.php?1=1';
$action_page = 'act_commision_rules.php';
$action_page_url = base_admin_url() . '/commision_rules.php?1=1';
//$export_url = base_admin_url() . '/contact_info_export.php';
$action_page__id_handler = 'ruleid';


$is_edit = $is_add = false;
$success_message = $error_message = array();

if( !empty($_POST['ruleid']) && !empty($_POST['goto']) && ($_POST['goto'] == 'update') ) {
    $rscommision1 = mysqli_query($conn,"select * from tbl_commision_rule WHERE int_commision_rule_id= {$_POST['ruleid']}");
    if(mysqli_num_rows($rscommision1)) {
        list($rule_id, $commision_rule, $bit_commisionable, $bit_percentage, $value, $bit_active) = mysqli_fetch_row($rscommision1);
        $is_edit = true;
    }
} elseif ( empty($_POST['ruleid']) && !empty($_POST['goto']) && ($_POST['goto'] == 'add') ) {
    $is_add = true;
}

if( !empty($_POST['action']) && ($_POST['action'] == "updateDesignations") && (!empty($_POST['id']) && is_array($_POST['id'])) ) {
    foreach($_POST['id'] as $key => $value) {
        $sql = "UPDATE tbl_designation SET designation_percentage = '{$_POST['percentage'][$key]}' WHERE int_designation_id = '$value'";
        mysqli_query($conn,$sql);
    }

    $success_message['designation'] = "Designations Information Updated Successfully!";
}

$no_delete_butotn = false;

$conditions = $sortby = array();
$sort = ( isset($_REQUEST['sort']) && is_numeric($_REQUEST['sort']) ?  filter_var($_REQUEST['sort'], FILTER_SANITIZE_NUMBER_INT) : '' );
$alpabet = ( !empty($_REQUEST['alpabet']) ? filter_var($_REQUEST['alpabet'], FILTER_SANITIZE_STRING) : 'A' ) ;

$limit = 30;
$page = ( ( !empty($_REQUEST['page']) && is_numeric($_REQUEST['page']) ) ? $_REQUEST['page'] : 1 );

$limit_start = ($page * $limit) - $limit;
$limit_end = ($page * $limit);


$sql = "select * from tbl_commision_rule ";

$sortby = "order by str_commision_rule";
$conditions[] = "bit_commisionable=1";

if( !empty($alpabet) && ($alpabet !== 'ALL') ) {
    if ($sort == 1) {
        $conditions[] = "str_commision_rule LIKE('$alpabet%')";
        $sortby = "ORDER BY str_commision_rule ASC";
    }
}

if(!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$sql .= " $sortby ";

$field_details = array(
    'str_commision_rule' => 'Rule',
    'bit_active' => 'Active?',
    'actions' => 'Commands',
);

$id_field = 'int_commision_rule_id';


//$query_pag_data = " $condition LIMIT $start, $per_page";

$data_num_query = mysqli_query($conn,$sql) or die('MySql Error' . mysqli_error($conn));

mysqli_store_result($conn);
$numrows = mysqli_num_rows($data_num_query);

//$sql .= " LIMIT $limit OFFSET $limit_start ";
//$numrows = 1;

//echo $sql;

$displayPagination = false;


//echo $sql;
$data_query = mysqli_query($conn,$sql) or die('MySql Error' . mysqli_error($conn));

?>

<script language="JavaScript">
<!--

function confirmCleanUp(Link) {
   if (confirm("Are you sure you want to delete this Rule ?")) {
      location.href=Link;
   }
}
function confirmSend(Link) {
   if (confirm("Are you sure you want to send this newsletter to all the members?")) {
      location.href=Link;
   }
}
function Validate(theform) {
	if(isEmpty(theform.txt_rulename.value)){
    	alert("Please enter the Rule Name");
		theform.txt_rulename.focus();
	    return false;
	} 	
	if(!isFloat(theform.txt_value.value)){
    	alert("Please enter the Value");
		theform.txt_value.focus();
	    return false;
	} 	

	return true;
}
function validateDesignation(f) {

    var proceed = true;
    msg = "The form cannot be processed for the following reason(s):\n\n";

    var inputs = Array();

    inputs = document.getElementsByTagName("input");
    console.log(inputs);
    for(count=0;count<inputs.length;count++) {
        if(inputs[count].type == "text" && isNaN(inputs[count].value))
        {
            msg += "All percentages must be numeric values\n";
            proceed = false;
            break;
        }
    }

    if(proceed)
    {
        return true;
    }
    else
    {
        alert(msg);
        return false;
    }
}
-->
</script>

<?php 	
if( !empty($_GET['page']) && is_numeric($_GET['page']) ){

       $nr = "NO";
   }
else{
   $nr = "YES";
}
?>

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

        <div class="row">
            <?php if( !empty($success_message) || !empty($error_messages) ): ?>
                <div class="col-xs-12 messages_wrapper">
                    <?php
                    if(!empty($success_message)) { echo '<ul class="alert alert-success"><li>'.implode('</li><li>', $success_message).'</li></ul>'; unset($success_message); }
                    if(!empty($error_messages)) { echo '<ul class="alert alert-danger"><li>'.implode('</li><li>', $error_messages).'</li></ul>'; unset($error_messages); }
                    ?>
                </div>
            <?php endif; ?>
            <?php if( !empty($is_edit) || !empty($is_add) ) { ?>
                <form name="theform" action="act_commision_rules.php" method="post" onsubmit="return Validate(this);" >
                    <section class="panel">
                        <div class="col-lg-8 col-md-10 col-sm-12 col-xs-12 centering add_record_wrapper">
                            <header class="panel-heading">
                                <h2 class="panel-title text-center"><?php echo ( !empty($is_edit) ? 'Edit' : 'Add New' ); ?> <?php echo $member_type_name; ?></h2>
                            </header>
                            <div class="panel-body">
                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="rulename">Rule Name</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="txt_rulename" id="rulename" maxlength="20" value="<?php echo ( !empty($is_edit) && !empty($commision_rule) ? $commision_rule : '' ); ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="value">Value</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="txt_value" id="value" maxlength="5" value="<?php echo ( !empty($is_edit) && isset($value) ? $value : '' ); ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="percentage">Percentage</label>
                                    <div class="col-md-8">
                                        <input type="checkbox" name="chk_percent" class="form-control" id="percentage" <?php echo ( !empty($is_edit) && !empty($bit_active) ? 'checked="checked"' : '' ); ?> title="If this is not checked the value will be treated as $ amount">
                                    </div>
                                </div>
                            </div>
                            <footer class="panel-footer">
                                <div class="row">
                                    <div class="col-sm-9 centering text-center">
                                        <input type="hidden" name="page" value="<?php echo $page; ?>">
                                        <?php echo ( (!empty($is_edit) && !empty($rule_id)) ? '<input type="hidden" name="ruleid" value="'.$rule_id.'">' : ''); ?>
                                        <?php echo ( !empty($is_add) ? '<input type="hidden" name="goto" value="add">' : '<input type ="hidden" name="goto" value="update">' ); ?>
                                        <button type="Submit" name="<?php echo ( !empty($is_add) ? 'add' : 'update'); ?>" value="" class="command  btn btn-default btn-success"><?php echo ( !empty($is_add) ? 'Add' : 'Update'); ?></button>
                                        <button id="cancel" type="button" name="cancel" value="Cancel" class="command btn btn-default btn-warning"  onClick="location.href='<?php echo $page_url.'&page='.$page; ?>';">Cancel</button>
                                    </div>
                                </div>
                            </footer>
                        </div>
                        <div class="clearfix"></div>
                    </section>
                </form>
            <?php } ?>

            <?php if( empty($is_edit) && empty($is_add) ) { ?>
                <section class="panel">
                    <div class="col-xs-12 filter_wrapper ">
                        <div class="table-responsive">
                            <div class="col-lg-6 col-md-10 col-sm-12 col-xs-12 centering name_filter">
                                <form action="<?php echo $self_page; ?>" method="post">
                                    <table class="table table-bordered table-striped mb-none">
                                        <tr>
                                            <td>Name Filter:</td>
                                            <td>
                                                <div class="filter inline_block">
                                                    <input type="Radio" name="sort" id="sortFirst" value="1" <?php if($sort=='1'){echo 'checked';} ?> ><label for="sortFirst">Rule Name Starts with</label>
                                                </div>
                                                <div class="filter inline_block">
                                                    <select name="alpabet" id="alpha">
                                                        <option value="ALL"<?php echo (($alpabet == 'ALL') ? 'selected' : '');?>>ALL</option>
                                                        <?php
                                                        $atoz = range('A', 'Z');
                                                        foreach($atoz as $alpha) {
                                                            echo '<option value="'.$alpha.'" '. ( ( !empty($alpabet) && ($alpabet == $alpha) ) ? 'selected' : '') .' >'.$alpha.'</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
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
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </section>

                <section class="panel">
                    <div class="col-xs-12">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-12 header_buttons_wrapper text-center centering">
                                    <div class="col-xs- add_button_wrapper centering">
                                        <form action="<?php echo $self_page; ?>" method="post">
                                            <input type="hidden" name="page" value="<?php echo $page; ?>">
                                            <input type="hidden" name="goto" value="add" />
                                            <button type="submit" name="add" value="" class="command btn btn-primary btn-success">Add New <?php echo $member_type_name; ?></button>
                                        </form>
                                    </div>
                                    <div class="col-xs- export_button_wrapper centering has-form">
                                        <a type="button" href="#modal_editDesignations" data-target="#modal_editDesignations" class="command modal-with-form btn btn-primary">Edit Designation Commission Percentages</a>
                                        <div id="modal_editDesignations" class="modal-block modal-block-primary mfp-hide">
                                            <form method="post" class="form form-validate" novalidate>
                                                <!--onsubmit="return validateDesignation(this);"-->
                                                <section class="panel">
                                                    <header class="panel-heading">
                                                        <div class="panel-actions">
                                                            <a href="#" class="panel-action panel-action-dismiss modal-dismiss"></a>
                                                        </div>
                                                        <h2 class="panel-title">Edit Designation Commission Percentages</h2>
                                                    </header>
                                                    <div class="panel-body">
                                                        <?php
                                                        $sql = "SELECT * FROM tbl_designation ORDER BY int_designation_id DESC";
                                                        $result = mysqli_query($conn,$sql);
                                                        $i = 0;
                                                        while($row = mysqli_fetch_array($result)) {
                                                        ?>
                                                            <div class="form-group mt-lg">
                                                                <label class="col-sm-6 control-label" for="percentage[<?php echo $i; ?>]"><?php echo $row['str_designation']; ?></label>
                                                                <div class="col-sm-6 form-inline">
                                                                    <input type="hidden" name="id[<?php echo $i; ?>]" value="<?php echo $row['int_designation_id'];?>">
                                                                    <input class="form-control mr-sm" size="3" maxlength="3" type="text" id="percentage[<?php echo $i; ?>]" name="percentage[<?php echo $i; ?>]" required aria-required="true" value="<?php echo $row['designation_percentage'];?>" />%
                                                                </div>
                                                            </div>
                                                        <?php $i++; } ?>
                                                    </div>
                                                    <footer class="panel-footer">
                                                        <div class="row">
                                                            <div class="col-md-12 text-center">
                                                                <input type="hidden" name="action" value="updateDesignations">
                                                                <button type="submit" class="submit btn btn-primary mr-lg">Submit</button>
                                                                <button class="btn btn-default btn-warning modal-dismiss">Cancel</button>
                                                            </div>
                                                        </div>
                                                    </footer>
                                                </section>
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
            <?php } ?>
        </div>
    </div>


<?php
require_once("templates/footer.php");

unset($_POST);
