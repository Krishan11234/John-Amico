<?php
$page_name = 'Video Link Management';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$member_type_name = 'Folder';
$member_type_name_plural = 'Folders';
$self_page = basename(__FILE__);
$page_url = base_admin_url() . "/{$self_page}?1=1";
$action_page = $self_page;
$action_page_url = base_admin_url() . "/{$self_page}?1=1";
$export_url = base_admin_url() . "/{$self_page}";


/*$sql .= "
CREATE TABLE `video_folders` (
  `folder_id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `folder_name` varchar(1000) COLLATE 'utf8_unicode_ci' NOT NULL
) ENGINE='InnoDB' COLLATE 'utf8_unicode_ci';

INSERT INTO `video_folders` (`folder_id`, `folder_name`)
VALUES ('0', 'John Amico');

ALTER TABLE `video_links`
ADD `folder_id` int(11) NOT NULL AFTER `video_id`,
ADD FOREIGN KEY (`folder_id`) REFERENCES `video_folders` (`folder_id`);

ALTER TABLE `video_links`
CHANGE `folder_id` `folder_id` int(11) NOT NULL DEFAULT '1' AFTER `video_id`;
";*/


if(!empty($_GET['delete_folder'])) {
    $folder_id = filter_var($_GET['delete_folder'], FILTER_SANITIZE_NUMBER_INT);

    if( !empty($folder_id) ) {
        $sql = "DELETE FROM video_links WHERE folder_id = '{$folder_id}'";
        mysqli_query($conn, $sql);
        //echo mysql_error();

        $sql = "DELETE FROM video_folders WHERE folder_id = '{$folder_id}'";
        mysqli_query($conn, $sql);
        //echo mysql_error();

        $messages[] = 'Folder is been deleted.';

    } else {
        $error_messages[] = 'Folder cannot be deleted.';
    }
}
if(!empty($_POST['create_folder']) && !empty($_POST['folder_name']) ) {
    $folder_name = filter_var($_POST['folder_name'], FILTER_SANITIZE_STRING);

    if( !empty($folder_name) ) {
        $sql = "INSERT INTO video_folders SET folder_name='{$folder_name}'";
        mysqli_query($conn, $sql);

        $messages[] = "Folder <em>$folder_name</em> is created.";

    } else {
        $error_messages['create_folder'] = 'Please enter a valid folder name';
    }
    //echo mysql_error();
}
//echo '<pre>'; var_dump($_POST) ;die();
if(!empty($_POST['goto'])) {
    switch($_POST['goto']) {
        case 'update' :
            if(
                !empty( $_POST['videoid'] ) && is_numeric($_POST['videoid'])
                && (!empty($_POST['video_title']) && !empty($_POST['video_desc']) )
                && !empty($_POST['folder']) && is_numeric($_POST['folder'])
            ) {
                $title = filter_var($_POST['video_title'], FILTER_SANITIZE_STRING);
                $desc = mysqli_real_escape_string($conn, $_POST['video_desc']);
                $folder_id = mysqli_real_escape_string($conn, $_POST['folder']);

                $videoid = filter_var($_POST['videoid'], FILTER_SANITIZE_NUMBER_INT);

                $sql = "UPDATE video_links SET video_title='{$title}', video_description='{$desc}', folder_id='{$folder_id}' WHERE video_id = '{$videoid}'";
                mysqli_query($conn,$sql);

                $mesg = 'Video updated successfully';
                $_SESSION['video_mesg'] = $mesg;

                echo "<script>document.location='$self_page';</script>"; exit;
            }
            break;
        case 'add' :
            if( (!empty($_POST['video_title']) && !empty($_POST['video_desc']) && !empty($_POST['folder']) && is_numeric($_POST['folder']) ) ) {
                $title = filter_var($_POST['video_title'], FILTER_SANITIZE_STRING);
                $desc = mysqli_real_escape_string($conn, $_POST['video_desc']);
                $folder_id = mysqli_real_escape_string($conn, $_POST['folder']);

                //debug(true, true, $_POST, array($title, $desc, $folder_id));

                $sql = "INSERT INTO video_links  SET video_title='{$title}', video_description='{$desc}', folder_id='{$folder_id}' ";
                mysqli_query($conn,$sql);

                $mesg = 'Video added successfully';
                $_SESSION['video_mesg'] = $mesg;

                echo "<script>document.location='$self_page';</script>"; exit;
            }
            break;
    }
}

if(!empty($_POST['delete']) && !empty($_POST['videoid']) ) {
    $video_id = filter_var($_POST['videoid'], FILTER_SANITIZE_NUMBER_INT);

    if( !empty( $video_id ) ) {
        $sql = "DELETE FROM video_links WHERE video_id = '{$video_id}'";
        mysqli_query($conn, $sql);
        //echo mysql_error();

        $messages[] = 'Video is been deleted.';

    } else {
        $error_messages[] = 'Wrong Video requested to delete. Please try again';
    }
}


require_once("templates/header.php");
require_once("templates/sidebar.php");

?>

    <style>
        .video_list iframe {
            width: 400px !important;
            height: 250px !important;
            position: relative !important;
        }
    </style>
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
                <?php if(!empty($messages) || !empty($error_messages) ) : ?>
                    <section class="panel">
                        <div class="col-md-10 col-xs-12 centering">
                            <div class="row">
                                <div class="message_wrapper">
                                    <div class="alert alert-success <?php echo ( !empty($error_messages) ? 'alert-danger' : ''); ?>">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        <ul>
                                            <?php if( !empty($error_messages) ) : ?>
                                                <li><?php echo implode('</li><li>', $error_messages); ?></li>
                                            <?php elseif( !empty($messages) ) : ?>
                                                <li><?php echo implode('</li><li>', $messages); ?></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>
                <section class="panel">
                    <div class="panel-body">
                        <div class="col-xs-12 centering text-center">
                            <?php
                            if( !empty($error_messages['create_folder']) ) {
                                echo "<script>jQuery(document).ready(function($){ $('.modal-with-form').click();) });</script>";
                            }
                            ?>
                            <a href="#modal_folderuploadform" type="button" data-target="#modal_editDesignations" class="modal-with-form btn btn-primary btn-success">Create Folder / Add Video</a>

                            <div id="modal_videosform" class="modal-block modal-block-primary mfp-hide">
                                <form action="<?php echo $self_page; ?>" method="post" class="form form-validate">
                                    <section class="panel">
                                        <header class="panel-heading">
                                            <div class="panel-actions">
                                                <a href="#" class="panel-action panel-action-dismiss modal-dismiss modal_clear_everything"></a>
                                            </div>
                                            <h2 class="panel-title text-center"><span>Edit Video</span></h2>
                                        </header>
                                        <div class="panel-body">
                                            <?php
                                            $sql = "SELECT * FROM video_folders ORDER BY folder_name";
                                            $result = mysqli_query($conn,$sql);

                                            if(mysqli_num_rows($result) > 0) {
                                                ?>
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">In Folder</label>
                                                    <div class="col-md-8">
                                                        <select name="folder" class="form-control folderId" required="required">
                                                            <option>Select Folder</option>
                                                            <?php while($folder = mysqli_fetch_array($result)) { ?>
                                                                <option value="<?php echo $folder['folder_id']; ?>"><?php echo $folder['folder_name']; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
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
                                            <?php } else { ?>
                                                <div class="message_wrapper">
                                                    <div class="alert <?php echo ( !empty($error_messages) ? 'alert-danger' : ''); ?>">
                                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                                        <ul>
                                                            <liL>Please create "Video Folder" to add/edit video</liL>
                                                        </ul>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <footer class="panel-footer">
                                            <div class="row">
                                                <div class="col-md-12 text-right">
                                                    <input type="hidden" name="videoid" class="id_place" value="">
                                                    <input type="hidden" name="goto" value="update" class="goto_type" />
                                                    <button type="Submit" name="submit" value="Upload" class="btn btn-primary btn-success pl-lg pr-lg submit_button">Submit</button>
                                                    <button class="btn btn-default btn-warning modal-dismiss modal_clear_everything">Cancel</button>
                                                </div>
                                            </div>
                                        </footer>
                                    </section>
                                </form>
                            </div>

                            <div class="modal_folderuploadform modal-block modal-block-primary mfp-hide" id="modal_folderuploadform">

                                <section class="panel">
                                    <header class="panel-heading">
                                        <div class="panel-actions">
                                            <a href="#" class="panel-action panel-action-dismiss modal-dismiss"></a>
                                        </div>
                                        <h2 class="panel-title">Create Folder / Add Video</h2>
                                    </header>
                                    <div class="panel-body">
                                        <form action="<?php echo $action_page; ?>" method="post" class="form form-validate">
                                            <?php if( !empty($error_messages['create_folder']) ) : ?>
                                                <section class="panel">
                                                    <div class="col-md-8 col-xs-12 centering">
                                                        <div class="row">
                                                            <div class="message_wrapper">
                                                                <div class="alert <?php echo ( !empty($error_messages) ? 'alert-danger' : ''); ?>">
                                                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                                                    <ul>
                                                                        <?php if( !empty($error_messages) ) : ?>
                                                                            <li><?php echo implode('</li><li>', $error_messages); ?></li>
                                                                        <?php endif; ?>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </section>
                                            <?php endif; ?>
                                            <section class="panel">
                                                <header class="panel-heading">
                                                    <h2 class="panel-title">Create Folder</h2>
                                                </header>
                                                <div class="panel-body">
                                                    <div class="form-group <?php echo ( !empty($error_messages['create_folder']) ? 'has-error' : '' ); ?> ">
                                                        <label class="col-md-4" for="folder_name">Folder Name</label>
                                                        <div class="col-md-8">
                                                            <input type="text" name="folder_name" id="folder_name" class="form-control pl-lg" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <footer class="panel-footer">
                                                    <div class="row">
                                                        <div class="col-md-12 text-center">
                                                            <button type="Submit" name="create_folder" value="Create" class="btn btn-primary btn-success pl-lg pr-lg">Create</button>
                                                        </div>
                                                    </div>
                                                </footer>
                                            </section>
                                        </form>

                                        <?php
                                        $sql = "SELECT * FROM video_folders ORDER BY folder_name";
                                        $result = mysqli_query($conn,$sql);

                                        if(mysqli_num_rows($result) > 0) {
                                            ?>
                                            <section class="panel">
                                                <form action="<?php echo $action_page; ?>" method="post" class="form form-validate">
                                                    <header class="panel-heading">
                                                        <h2 class="panel-title">Add Video</h2>
                                                    </header>
                                                    <div class="panel-body">
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">In Folder</label>
                                                            <div class="col-md-8">
                                                                <select name="folder" class="form-control" required="required">
                                                                    <option>Select Folder</option>
                                                                    <?php while($folder = mysqli_fetch_array($result)) { ?>
                                                                        <option value="<?php echo $folder['folder_id']; ?>"><?php echo $folder['folder_name']; ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
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
                                                            <div class="col-md-12 text-center">
                                                                <input type="hidden" name="goto" value="add" class="goto_type" />
                                                                <button type="Submit" name="upload" value="Upload" class="btn btn-primary btn-success pl-lg pr-lg">Add Video</button>
                                                            </div>
                                                        </div>
                                                    </footer>
                                                </form>
                                            </section>
                                        <?php } ?>
                                    </div>
                                    <footer class="panel-footer">
                                        <div class="row">
                                            <div class="col-md-12 text-right">
                                                <button class="btn btn-default btn-warning modal-dismiss">Cancel</button>
                                            </div>
                                        </div>
                                    </footer>
                                </section>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-md-10 col-xs-12 centering">
                <section class="panel">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center">List of <?php echo $member_type_name_plural; ?></h2>
                    </header>
                    <div class="panel-body folders">
                        <?php
                        $sql = "SELECT * FROM video_folders ORDER BY folder_name";
                        $result = mysqli_query($conn,$sql);

                        if(mysqli_num_rows($result) > 0) {
                            while($folder = mysqli_fetch_array($result)) {
                                ?>
                                <div class="row folder">
                                    <div class="col-xs-12 centering folder_inner">
                                        <section class="panel">
                                            <header class="panel-heading">
                                                <div class="row">
                                                    <div class="col-md-8 pull-left"><h2 class="panel-title"><u>Folder:</u>  <?php echo $folder['folder_name']; ?></h2></div>
                                                    <div class="col-md-4 pull-right text-right">
                                                        <button class="btn btn-primary btn-danger" onClick="if(confirm('Deleting this folder will also delete any videos contained in it. Are you sure you want to delete it?')){ window.location='<?php echo $action_page_url; ?>&delete_folder=<?php echo $folder['folder_id']; ?>'; return true;}else{return false;}" >Delete Folder</button>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                                <div class="clearfix"></div>
                                            </header>
                                            <div class="panel-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped text-center video_list">
                                                        <thead>
                                                        <th class="text-center" width="340px">Title</th>
                                                        <th class="text-center">Preview</th>
                                                        <th class="text-center" colspan="2">Commands</th>
                                                        </thead>
                                                        <tbody>
                                                        <?php
                                                        $sql = "SELECT * FROM video_links WHERE folder_id = '{$folder['folder_id']}' ORDER BY video_title";
                                                        $result2 = mysqli_query($conn,$sql);

                                                        if(mysqli_num_rows($result2) > 0) {
                                                            $file_has = false;

                                                            while($res = mysqli_fetch_array($result2)) {

                                                                ?>

                                                                <tr>
                                                                    <td class="text-left title" id="title-<?php echo $res['video_id']?>"><?php echo $res['video_title']?></td>
                                                                    <td class="text-center desc" id="desc-<?php echo $res['video_id']?>"><?php echo $res['video_description']?></td>
                                                                    <td>
                                                                        <!--<button type="button" data-title-class="title" data-link-class="link" data-desc-class="desc" data-toolid="<?php /*echo $res['video_id']*/?>" data-modal-id="modal_videosform" data-modal-opener-class="modal_loader" class="modal-with-form btn btn-primary edit_button">Edit</button>-->

                                                                        <a type="button" href="#modal_videosform" data-target="#modal_videosform" data-title-class="title" data-link-class="link" data-desc-class="desc" data-toolid="<?php echo $res['video_id']?>" data-modal-id="modal_videosform" data-modal-opener-class="modal_loader" data-video-folder-id="<?php echo $res['folder_id']?>" class="modal-with-form btn btn-primary edit_button">Edit</a>
                                                                    </td>
                                                                    <td>
                                                                        <form action="<?php echo $self_page; ?>" method="post" class="form form-validate">
                                                                            <input type="hidden" name="videoid" value="<?php echo $res['video_id']?>">
                                                                            <button type="Submit" name="delete" value="Delete" class="btn btn-primary btn-danger" onClick="if(confirm('Are you sure you want to delete this tool?')){return true;}else{return false;}">Delete</button>
                                                                        </form>
                                                                    </td>
                                                                </tr>
                                                                <?php
                                                            }
                                                        } else {
                                                            echo '<tr><td colspan="3" class="text-center">No video found in this folder</td></tr>';
                                                            $noFileMessageDisplayed = true;
                                                        }
                                                        ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </section>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>

                    </div>
                    <script>
                        jQuery(document).ready(function($){
                            $('.edit_button').click(function(){
                                var titleClass = $(this).attr('data-title-class');
                                var descClass = $(this).attr('data-desc-class');
                                var linkClass = $(this).attr('data-link-class');
                                var folderId = $(this).attr('data-video-folder-id');
                                var modalID = $(this).attr('data-modal-id');
                                var id = $(this).attr('data-toolid');
                                var title = $.trim( $('#' + titleClass + '-' + id).text() );
                                var desc = $.trim( $('#' + descClass + '-' + id).html() );
                                var link = $.trim( $('#' + linkClass + '-' + id).attr('href') );

                                //if( jQuery(modalOpener) ) {
                                    //jQuery('.' + modalOpener).trigger('click');

                                    jQuery('#' + modalID).find('.panel-title span').text('Edit');
                                    jQuery('#' + modalID).find('.title_place').val(title);
                                    jQuery('#' + modalID).find('.desc_place').html(desc);
                                    jQuery('#' + modalID).find('.link_place').val(link);
                                    jQuery('#' + modalID).find('.id_place').val(id);
                                    jQuery('#' + modalID).find('.folderId').val(folderId);
                                //}

                                return false;
                            });

                            $('.modal_clear_everything').click(function(){
                                var parent = $(this).parents('.modal-block');
                                //console.log(parent);
                                //var inputs = $(parent).find('input');

                                $('input', parent).val('');
                                $('textarea', parent).html('');
                                $('.folderId, ', parent).val('');
                            });
                        });
                    </script>
                </section>
            </div>
        </div>
    </div>


<?php
require_once("templates/footer.php");

