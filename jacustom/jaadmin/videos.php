<?php
$page_name = 'Video Link Management';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");

header("Location: " . base_admin_url().'/videos_new.php');
exit;

require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


$member_type_name = 'Video';
$member_type_name_plural = 'Videos';
$self_page = 'videos.php';
$page_url = base_admin_url() . '/videos.php?1=1';
$action_page = 'videos.php';
$action_page_url = base_admin_url() . '/videos.php?1=1';
$export_url = base_admin_url() . '/videos.php';

$mesg = '';

if(!empty($_POST['goto'])) {
    switch($_POST['goto']) {
        case 'update' :
            if( !empty( $_POST['videoid'] ) && is_numeric($_POST['videoid']) && (!empty($_POST['video_title']) && !empty($_POST['video_desc']) ) ) {
                $title = filter_var($_POST['video_title'], FILTER_SANITIZE_STRING);
                $desc = mysqli_real_escape_string($conn, $_POST['video_desc']);

                $videoid = filter_var($_POST['videoid'], FILTER_SANITIZE_NUMBER_INT);

                $sql = "UPDATE video_links SET video_title='{$title}', video_description='{$desc}' WHERE video_id = '{$videoid}'";
                mysqli_query($conn,$sql);

                $mesg = 'Video updated successfully';
                $_SESSION['video_mesg'] = $mesg;

                echo "<script>document.location='$self_page';</script>"; exit;
            }
        break;
        case 'add' :
            if( (!empty($_POST['video_title']) && !empty($_POST['video_desc']) ) ) {
                $title = filter_var($_POST['video_title'], FILTER_SANITIZE_STRING);
                $desc = mysqli_real_escape_string($conn, $_POST['video_desc']);

                //debug(true, true, $_POST, array($title, $desc));

                $sql = "INSERT INTO video_links  SET video_title='{$title}', video_description='{$desc}' ";
                mysqli_query($conn,$sql);

                $mesg = 'Video added successfully';
                $_SESSION['video_mesg'] = $mesg;

                echo "<script>document.location='$self_page';</script>"; exit;
            }
        break;
        case 'delete' :
            if( !empty( $_POST['videoid'] ) && is_numeric($_POST['videoid']) ) {
                $videoid = filter_var($_POST['videoid'], FILTER_SANITIZE_NUMBER_INT);

                $sql = "DELETE FROM video_links WHERE video_id = '{$videoid}'";
                mysqli_query($conn,$sql);

                $mesg = 'Video deleted successfully';
                $_SESSION['video_mesg'] = $mesg;

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
                        <a href="#modal_videosform" type="button" data-target="#modal_videosform" class="modal-with-form btn btn-primary btn-success modal_loader">Add New <?php echo $member_type_name; ?></a>

                        <div class="modal_videosform modal-block modal-block-primary mfp-hide" id="modal_videosform">
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
                                            <label class="col-md-4">Video Title</label>
                                            <div class="col-md-8">
                                                <input type="text" name="video_title" class="form-control pl-lg title_place" required>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-4">Video Embed Code</label>
                                            <div class="col-md-8">
                                                <textarea rows="6" name="video_desc" class="form-control pl-lg desc_place" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <footer class="panel-footer">
                                        <div class="row">
                                            <div class="col-md-12 text-right">
                                                <input type="hidden" name="videoid" class="id_place" value="">
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

                    <?php if(!empty($_SESSION['video_mesg'])) : ?>
                    <div class="row">
                        <div class="message_wrapper">
                            <div class="alert alert-info">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <?php echo $_SESSION['video_mesg']; ?>
                                <?php unset($_SESSION['video_mesg']); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped text-center video_list">
                                <thead>
                                <th class="" width="200px">Title</th>
                                <th class="" width="">Description</th>
                                <th class="text-center" colspan="2">Commands</th>
                                </thead>
                                <tbody>
                                <?php
                                $sql = "SELECT * FROM video_links ORDER BY video_title";
                                $result2 = mysqli_query($conn,$sql);

                                if(mysqli_num_rows($result2) > 0) {
                                    while($res = mysqli_fetch_array($result2)) {
                                        //debug(true, false, $res);
                                        ?>
                                        <tr>
                                            <td class="text-left title" id="title-<?php echo $res['video_id']?>"><?php echo $res['video_title']?></td>
                                            <td class="text-center desc" id="desc-<?php echo $res['video_id']?>"><?php echo $res['video_description']?></td>
                                            <td>
                                                <button type="button" data-title-class="title" data-link-class="link" data-desc-class="desc" data-toolid="<?php echo $res['video_id']?>" data-modal-id="modal_videosform" data-modal-opener-class="modal_loader" class="btn btn-primary edit_button">Edit</button>
                                            </td>
                                            <td>
                                                <form action="<?php echo $self_page; ?>" method="post" class="form form-validate">
                                                    <input type="hidden" name="videoid" value="<?php echo $res['video_id']?>">
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
                            var desc = $.trim( $('#' + descClass + '-' + id).html() );
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

