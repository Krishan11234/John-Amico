<?php
$page_name = 'Manage Error Types';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


$member_type_name = 'Error Type';
$member_type_name_plural = 'Error Types';
$self_page = 'mlm_errors.php';
$page_url = base_admin_url() . '/mlm_errors.php?1=1';
$action_page = 'mlm_errors.php';
$action_page_url = base_admin_url() . '/mlm_errors.php?1=1';
$export_url = base_admin_url() . '/mlm_errors.php';

$mesg = '';

if(!empty($_POST['goto'])) {
    switch($_POST['goto']) {
        case 'update' :
            if( !empty( $_POST['ruleid'] ) && is_numeric($_POST['ruleid']) && !empty($_POST['txt_rulename']) ) {
                $text = filter_var($_POST['txt_rulename'], FILTER_SANITIZE_STRING);
                $ruleid = filter_var($_POST['ruleid'], FILTER_SANITIZE_NUMBER_INT);

                $table = "tbl_mlm_errors_types";
                $fieldlist="title='{$text}'";
                $condition=" where eid = {$ruleid}";
                $result=update_rows($conn, $table, $fieldlist, $condition);

                $mesg = 'Error Type updated successfully';
            }
        break;
        case 'add' :
            if( !empty($_POST['txt_rulename']) ) {
                $text = filter_var($_POST['txt_rulename'], FILTER_SANITIZE_STRING);

                $table = "tbl_mlm_errors_types";				// inserting values to setting table
                $in_fieldlist="title";
                $in_values="'{$text}'";
                $result=insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values

                $mesg = 'Error Type added successfully';
            }
        break;
        case 'delete' :
            if( !empty( $_POST['ruleid'] ) && is_numeric($_POST['ruleid']) ) {
                $ruleid = filter_var($_POST['ruleid'], FILTER_SANITIZE_NUMBER_INT);

                $table = "tbl_mlm_errors_types";
                $condition=" where eid = {$ruleid}";
                $result=del_rows($conn, $table, $condition);// function call to delete

                $mesg = 'Error Type deleted successfully';
            }
        break;
    }
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

    <div class="row ">
        <div class="col-md-6 col-xs-12 centering">
            <section class="panel">
                <div class="panel-body">
                    <div class="col-xs-12 centering text-center">
                        <a href="#modal_errorform" type="button" data-target="#modal_errorform" class="modal-with-form btn btn-primary btn-success modal_loader">Add New <?php echo $member_type_name; ?></a>

                        <div class="modal_errorform modal-block modal-block-primary mfp-hide" id="modal_errorform">
                            <form action="<?php echo $self_page; ?>" method="post" class="form form-validate">
                                <section class="panel">
                                    <header class="panel-heading">
                                        <div class="panel-actions">
                                            <a href="#" class="panel-action panel-action-dismiss modal-dismiss modal_clear_everything"></a>
                                        </div>
                                        <h2 class="panel-title text-center"><span>Add</span> <?php echo $member_type_name; ?></h2>
                                    </header>
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label class="col-md-4"><?php echo $member_type_name; ?></label>
                                            <div class="col-md-8">
                                                <input type="text" name="txt_rulename" class="form-control pl-lg text_place" required>
                                            </div>
                                        </div>
                                    </div>
                                    <footer class="panel-footer">
                                        <div class="row">
                                            <div class="col-md-12 text-right">
                                                <input type="hidden" name="ruleid" class="id_place" value="">
                                                <input type="hidden" name="goto" value="add" class="goto_type" />
                                                <button type="Submit" name="submit" value="Upload" class="btn btn-primary btn-success pl-lg pr-lg submit_button">Submit</button>
                                                <button class="btn btn-default btn-warning modal-dismiss modal_clear_everything">Cancel</button>
                                            </div>
                                        </div>
                                    </footer>
                                </section>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
            <section class="panel">
                <header class="panel-heading">
                    <h2 class="panel-title text-center">List of <?php echo $member_type_name_plural; ?></h2>
                </header>
                <div class="panel-body ">

                    <?php if(!empty($mesg)) : ?>
                    <div class="row">
                        <div class="message_wrapper">
                            <div class="alert alert-info">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <?php echo $mesg; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped text-center">
                                <thead>
                                <th class="" width="">Error Type</th>
                                <th class="text-center" colspan="2">Commands</th>
                                </thead>
                                <tbody>
                                <?php
                                $sql = "select * from tbl_mlm_errors_types order by title";
                                $result2 = mysqli_query($conn,$sql);

                                if(mysqli_num_rows($result2) > 0) {
                                    while($file = mysqli_fetch_array($result2)) {
                                        ?>
                                        <tr>
                                            <td class="text-left rule" id="rule-<?php echo $file['eid']?>"><?php echo $file['title']?></td>
                                            <td>
                                                <button type="button" data-text-class="rule" data-ruleid="<?php echo $file['eid']?>" data-modal-id="modal_errorform" data-modal-opener-class="modal_loader" class="btn btn-primary edit_button">Edit</button>
                                            </td>
                                            <td>
                                                <form action="<?php echo $self_page; ?>" method="post" class="form form-validate">
                                                    <input type="hidden" name="ruleid" value="<?php echo $file['eid']?>">
                                                    <input type="hidden" name="goto" value="delete" />
                                                    <button type="Submit" name="delete" value="Delete" class="btn btn-primary btn-danger" onClick="if(confirm('Are you sure you want to delete this type?')){return true;}else{return false;}">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="2" class="text-center">There are currently no rules.</td></tr>';
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <script>
                    jQuery(document).ready(function($){
                        $('.edit_button').click(function(){
                            var textClass = $(this).attr('data-text-class');
                            var modalOpener = $(this).attr('data-modal-opener-class');
                            var modalID = $(this).attr('data-modal-id');
                            var id = $(this).attr('data-ruleid');
                            var text = $('#' + textClass + '-' + id).text();

                            if( jQuery('.' + modalOpener) ) {
                                jQuery('.' + modalOpener).trigger('click');

                                jQuery('#' + modalID).find('.panel-title span').text('Edit');
                                jQuery('#' + modalID).find('.text_place').val(text);
                                jQuery('#' + modalID).find('.id_place').val(id);
                                jQuery('#' + modalID).find('.goto_type').val('update');
                            }
                        });

                        $('.modal_clear_everything').click(function(){
                            var parent = $(this).parents('.modal-block');
                            console.log(parent);
                            //var inputs = $(parent).find('input');

                            $('input, ', parent).val('');
                            $('.goto_type, ', parent).val('add');
                        });
                    });
                </script>
            </section>
        </div>
    </div>
</div>


<?php
require_once("templates/footer.php");

