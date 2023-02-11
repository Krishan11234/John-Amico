<?php
$page_name = 'Tool Link Management';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


$member_type_name = 'Tool';
$member_type_name_plural = 'Tools';
$self_page = 'tools.php';
$page_url = base_admin_url() . '/tools.php?1=1';
$action_page = 'tools.php';
$action_page_url = base_admin_url() . '/tools.php?1=1';
$export_url = base_admin_url() . '/tools.php';

$mesg = '';

if(!empty($_POST['goto'])) {
    switch($_POST['goto']) {
        case 'update' :
            if( !empty( $_POST['toolid'] ) && is_numeric($_POST['toolid']) && (!empty($_POST['tool_title']) && !empty($_POST['tool_link']) && !empty($_POST['tool_desc']) ) ) {
                $title = filter_var($_POST['tool_title'], FILTER_SANITIZE_STRING);
                $link = filter_var($_POST['tool_link'], FILTER_SANITIZE_URL);
                $desc = filter_var($_POST['tool_desc'], FILTER_SANITIZE_STRING);
                $toolid = filter_var($_POST['toolid'], FILTER_SANITIZE_NUMBER_INT);

                $sql = "UPDATE tool_links SET tool_title='{$title}', tool_link='{$link}', tool_description='{$desc}' WHERE tool_id = '{$toolid}'";
                mysqli_query($conn,$sql);

                $mesg = 'Tool updated successfully';
                $_SESSION['tool_mesg'] = $mesg;

                echo "<script>document.location='$self_page';</script>"; exit;
            }
        break;
        case 'add' :
            if( (!empty($_POST['tool_title']) && !empty($_POST['tool_link']) && !empty($_POST['tool_desc']) ) ) {
                $title = filter_var($_POST['tool_title'], FILTER_SANITIZE_STRING);
                $link = filter_var($_POST['tool_link'], FILTER_SANITIZE_URL);
                $desc = filter_var($_POST['tool_desc'], FILTER_SANITIZE_STRING);

                $sql = "INSERT INTO tool_links SET tool_title='{$title}', tool_link='{$link}', tool_description='{$desc}' ";
                mysqli_query($conn,$sql);

                $mesg = 'Tool added successfully';
                $_SESSION['tool_mesg'] = $mesg;

                echo "<script>document.location='$self_page';</script>"; exit;
            }
        break;
        case 'delete' :
            if( !empty( $_POST['toolid'] ) && is_numeric($_POST['toolid']) ) {
                $toolid = filter_var($_POST['toolid'], FILTER_SANITIZE_NUMBER_INT);

                $sql = "DELETE FROM tool_links WHERE tool_id = '{$toolid}'";
                mysqli_query($conn,$sql);

                $mesg = 'Tool deleted successfully';
                $_SESSION['tool_mesg'] = $mesg;

                echo "<script>document.location='$self_page';</script>"; exit;
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
        <div class="col-md-10 col-xs-12 centering">
            <section class="panel">
                <div class="panel-body">
                    <div class="col-xs-12 centering text-center">
                        <a href="#modal_toolsform" type="button" data-target="#modal_toolsform" class="modal-with-form btn btn-primary btn-success modal_loader">Add New <?php echo $member_type_name; ?></a>

                        <div class="modal_toolsform modal-block modal-block-primary mfp-hide" id="modal_toolsform">
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
                                            <label class="col-md-4">Tool Title</label>
                                            <div class="col-md-8">
                                                <input type="text" name="tool_title" class="form-control pl-lg title_place" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4">Tool Link</label>
                                            <div class="col-md-8">
                                                <input type="text" name="tool_link" class="form-control pl-lg link_place" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4">Tool Description</label>
                                            <div class="col-md-8">
                                                <textarea rows="10" name="tool_desc" class="form-control pl-lg desc_place" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <footer class="panel-footer">
                                        <div class="row">
                                            <div class="col-md-12 text-right">
                                                <input type="hidden" name="toolid" class="id_place" value="">
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

                    <?php if(!empty($_SESSION['tool_mesg'])) : ?>
                        <div class="row">
                            <div class="message_wrapper">
                                <div class="alert alert-info">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <?php echo $_SESSION['tool_mesg']; ?>
                                    <?php unset($_SESSION['tool_mesg']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped text-center">
                                <thead>
                                <th class="" width="200px">Title</th>
                                <th class="" width="">Description</th>
                                <th class="text-center" colspan="2">Commands</th>
                                </thead>
                                <tbody>
                                <?php
                                $sql = "SELECT * FROM tool_links ORDER BY tool_title";
                                $result2 = mysqli_query($conn,$sql);

                                if(mysqli_num_rows($result2) > 0) {
                                    while($res = mysqli_fetch_array($result2)) {
                                        ?>
                                        <tr>
                                            <td class="text-left title" id="title-<?php echo $res['tool_id']?>">
                                                <a href="<?php echo $res['tool_link']; ?>" target="_blank" class="link" id="link-<?php echo $res['tool_id']?>"><?php echo $res['tool_title']?></a>
                                            </td>
                                            <td class="text-left desc" id="desc-<?php echo $res['tool_id']?>"><?php echo $res['tool_description']?></td>
                                            <td>
                                                <button type="button" data-title-class="title" data-link-class="link" data-desc-class="desc" data-toolid="<?php echo $res['tool_id']?>" data-modal-id="modal_toolsform" data-modal-opener-class="modal_loader" class="btn btn-primary edit_button">Edit</button>
                                            </td>
                                            <td>
                                                <form action="<?php echo $self_page; ?>" method="post" class="form form-validate">
                                                    <input type="hidden" name="toolid" value="<?php echo $res['tool_id']?>">
                                                    <input type="hidden" name="goto" value="delete" />
                                                    <button type="Submit" name="delete" value="Delete" class="btn btn-primary btn-danger" onClick="if(confirm('Are you sure you want to delete this tool?')){return true;}else{return false;}">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="2" class="text-center">There are currently no tool links.</td></tr>';
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
                            var titleClass = $(this).attr('data-title-class');
                            var descClass = $(this).attr('data-desc-class');
                            var linkClass = $(this).attr('data-link-class');
                            var modalOpener = $(this).attr('data-modal-opener-class');
                            var modalID = $(this).attr('data-modal-id');
                            var id = $(this).attr('data-toolid');
                            var title = $.trim( $('#' + titleClass + '-' + id).text() );
                            var desc = $.trim( $('#' + descClass + '-' + id).text() );
                            var link = $.trim( $('#' + linkClass + '-' + id).attr('href') );

                            if( jQuery('.' + modalOpener) ) {
                                jQuery('.' + modalOpener).trigger('click');

                                jQuery('#' + modalID).find('.panel-title span').text('Edit');
                                jQuery('#' + modalID).find('.title_place').val(title);
                                jQuery('#' + modalID).find('.desc_place').html(desc);
                                jQuery('#' + modalID).find('.link_place').val(link);
                                jQuery('#' + modalID).find('.id_place').val(id);
                                jQuery('#' + modalID).find('.goto_type').val('update');
                            }
                        });

                        $('.modal_clear_everything').click(function(){
                            var parent = $(this).parents('.modal-block');
                            console.log(parent);
                            //var inputs = $(parent).find('input');

                            $('input', parent).val('');
                            $('textarea', parent).html('');
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

