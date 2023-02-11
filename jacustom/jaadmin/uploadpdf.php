<?php
$page_name = 'Manage Member Docs';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$member_type_name = 'Folder';
$member_type_name_plural = 'Folders';
$self_page = 'uploadpdf.php';
$page_url = base_admin_url() . '/uploadpdf.php?1=1';
$action_page = 'act_members.php';
$action_page_url = base_admin_url() . '/uploadpdf.php?1=1';
$export_url = base_admin_url() . '/uploadpdf.php';

$up_dir="./uploaded_files/";

function update_uploadpdf_file_size($file_id, $actual_file_size) {
    global $conn;

    if( !empty($file_id) && !empty($actual_file_size) ) {
        $sql = "UPDATE upload_folders SET file_size='{$actual_file_size}' WHERE file_id = '{$file_id}'";
        mysqli_query($conn, $sql);
    }

}


if(!empty($_GET['delete_folder'])) {
    $folder_id = filter_var($_GET['delete_folder'], FILTER_SANITIZE_NUMBER_INT);

    if( !empty($folder_id) ) {
        $sql = "SELECT * FROM upload_files WHERE file_folder_id = '{$_GET['delete_folder']}'";
        $result = mysqli_query($conn, $sql);
        //echo mysql_error();

        if (mysqli_num_rows($result) > 0) {
            while ($file = mysqli_fetch_array($result)) {
                if (file_exists("uploaded_files/{$file['file_id']}.dat")) {
                    unlink("uploaded_files/{$file['file_id']}.dat");
                }
            }
        }

        $sql = "DELETE FROM upload_files WHERE file_folder_id = '{$_GET['delete_folder']}'";
        mysqli_query($conn, $sql);
        //echo mysql_error();

        $sql = "DELETE FROM upload_folders WHERE folder_id = '{$_GET['delete_folder']}'";
        mysqli_query($conn, $sql);
        //echo mysql_error();

        $messages[] = 'Folder is been deleted.';

    } else {
        $error_messages[] = 'Folder cannot be deleted.';
    }
}
if(!empty($_POST['download']) && !empty($_POST['file_id']) ) {
    $file_id = filter_var($_POST['file_id'], FILTER_SANITIZE_NUMBER_INT);

    if( !empty( $file_id ) ) {
        $sql = "SELECT * FROM upload_files WHERE file_id = '{$file_id}'";
        $result = mysqli_query($conn, $sql);
        //echo mysqli_error($conn);
        $file = mysqli_fetch_array($result);

        //echo '<pre>'; print_r( array($sql, $file, $_POST) ); die();

        if( !empty($file['file_id']) ) {
            $file_path = "uploaded_files/{$file['file_id']}.dat";

            if( file_exists($file_path) ) {
                header("Content-Disposition: attachment; filename=" . $file['file_name']);
                readfile($file_path);

                exit;
            }
        }
    }

    $error_messages[] = 'Wrong file requested. Please try again';
}
if(!empty($_POST['create_folder']) && !empty($_POST['folder_name']) ) {
    $folder_name = filter_var($_POST['folder_name'], FILTER_SANITIZE_STRING);

    if( !empty($folder_name) ) {
        $sql = "INSERT INTO upload_folders SET folder_name='{$_POST['folder_name']}'";
        mysqli_query($conn, $sql);

        $messages[] = "Folder <em>$folder_name</em> is created.";

    } else {
        $error_messages['create_folder'] = 'Please enter a valid folder name';
    }
    //echo mysql_error();
}
if(isset($_POST['upload'])) {

    // Check file size
    $maxFileSize = 20 * 1024 * 1024; // 20 MB
    if ($_FILES['userfile']["size"] > $maxFileSize)
    {
        $error_messages['upload'] = 'Your file size is too large. Max file size limit is: ' . ($maxFileSize / (1024 * 1024)) . ' MB';
    }
    else {
        $randomhash = rand();

        //$uploadfile =$up_dir.mysqli_insert_id($conn).".dat";
        $uploadfile =$up_dir.$randomhash.".dat";
        $uploaded = move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile);

        //	chmod($uploadfile, 755);

        if( $uploaded ) {
            $sql = "INSERT INTO upload_files SET file_folder_id='{$_POST['folder']}', file_name='{$_FILES['userfile']['name']}', file_size='".filesize($_FILES['userfile']['tmp_name'])."'";
            $query = mysqli_query($conn,$sql);
            //echo mysqli_error($conn);

            $uploadfilename =$up_dir.mysqli_insert_id($conn).".dat";

            rename($uploadfile,$uploadfilename);
        } else {
            $error_messages['create_folder'] = 'Could not process the file';
        }
    }
}

if(!empty($_POST['delete']) && !empty($_POST['file_id']) ) {
    $file_id = filter_var($_POST['file_id'], FILTER_SANITIZE_NUMBER_INT);

    if( !empty( $file_id ) ) {
        $sql = "SELECT * FROM upload_files WHERE file_id = '{$_POST['file_id']}'";
        $result = mysqli_query($conn, $sql);
        //echo mysql_error();
        $file = mysqli_fetch_array($result);

        if (file_exists("uploaded_files/{$file['file_id']}.dat")) {
            unlink("uploaded_files/{$file['file_id']}.dat");
        }

        $sql = "DELETE FROM upload_files WHERE file_id = '{$_POST['file_id']}'";
        mysqli_query($conn, $sql);
        //echo mysql_error();

        $messages[] = 'File is been deleted.';

    } else {
        $error_messages[] = 'Wrong file requested to delete. Please try again';
    }
}


require_once("templates/header.php");
require_once("templates/sidebar.php");

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
            <div class="col-md-8 col-xs-12 centering">
                <?php if(!empty($messages) || !empty($error_messages) ) : ?>
                    <section class="panel">
                        <div class="col-md-8 col-xs-12 centering">
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
                            <a href="#modal_folderuploadform" type="button" data-target="#modal_editDesignations" class="modal-with-form btn btn-primary btn-success">Create Folder / Upload Files</a>

                            <div class="modal_folderuploadform modal-block modal-block-primary mfp-hide" id="modal_folderuploadform">

                                <section class="panel">
                                    <header class="panel-heading">
                                        <div class="panel-actions">
                                            <a href="#" class="panel-action panel-action-dismiss modal-dismiss"></a>
                                        </div>
                                        <h2 class="panel-title">Create Folder / Upload Files</h2>
                                    </header>
                                    <div class="panel-body">
                                        <form action="uploadpdf.php" method="post" class="form form-validate" enctype="multipart/form-data">
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
                                        $sql = "SELECT * FROM upload_folders ORDER BY folder_name";
                                        $result = mysqli_query($conn,$sql);

                                        if(mysqli_num_rows($result) > 0) {
                                            ?>
                                            <section class="panel">
                                                <form action="uploadpdf.php" method="post" class="form form-validate" enctype="multipart/form-data">
                                                    <header class="panel-heading">
                                                        <h2 class="panel-title">Upload Files</h2>
                                                    </header>
                                                    <div class="panel-body">
                                                        <div class="form-group">
                                                            <label class="col-md-3 control-label">In Folder</label>
                                                            <div class="col-md-9">
                                                                <select name="folder" class="form-control">
                                                                    <option>Seleft Folder</option>
                                                                    <?php while($folder = mysqli_fetch_array($result)) { ?>
                                                                        <option value="<?php echo $folder['folder_id']; ?>"><?php echo $folder['folder_name']; ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-3 control-label">File</label>
                                                            <div class="col-md-9">
                                                                <div class="fileupload fileupload-new" data-provides="fileupload">
                                                                    <div class="input-append">
                                                                        <div class="uneditable-input">
                                                                            <i class="fa fa-file fileupload-exists"></i>
                                                                            <span class="fileupload-preview"></span>
                                                                        </div>
                                                                    <span class="btn btn-default btn-file">
                                                                        <span class="fileupload-exists">Change</span>
                                                                        <span class="fileupload-new">Select file</span>
                                                                        <input type="file" name="userfile" required>
                                                                        <input type="hidden" name="MAX_FILE_SIZE" value="1000000">
                                                                    </span>
                                                                        <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">Remove</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <footer class="panel-footer">
                                                        <div class="row">
                                                            <div class="col-md-12 text-center">
                                                                <button type="Submit" name="upload" value="Upload" class="btn btn-primary btn-success pl-lg pr-lg">Upload File</button>
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
            <div class="col-md-8 col-xs-12 centering">
                <section class="panel">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center">List of <?php echo $member_type_name_plural; ?></h2>
                    </header>
                    <div class="panel-body folders">
                        <?php
                        $sql = "SELECT * FROM upload_folders ORDER BY folder_name";
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
                                                        <button class="btn btn-primary btn-danger" onClick="if(confirm('Deleting this folder will also delete any files contained in it, are you sure you want to delete it?')){ window.location='<?php echo base_admin_url(); ?>/?delete_folder=<?php echo $folder['folder_id']; ?>'; return true;}else{return false;}" >Delete Folder</button>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                                <div class="clearfix"></div>
                                            </header>
                                            <div class="panel-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped text-center">
                                                        <thead>
                                                        <th class="text-center" width="340px">File Name</th>
                                                        <th class="text-center">File Size</th>
                                                        <th class="text-center" colspan="2">Commands</th>
                                                        </thead>
                                                        <tbody>
                                                        <?php
                                                        $sql = "SELECT * FROM upload_files WHERE file_folder_id = '{$folder['folder_id']}' ORDER BY file_name";
                                                        $result2 = mysqli_query($conn,$sql);

                                                        if(mysqli_num_rows($result2) > 0) {
                                                            $file_has = false;

                                                            while($file = mysqli_fetch_array($result2)) {
                                                                $uploaded_filepath = $up_dir . "{$file['file_id']}.dat";
                                                                if( !file_exists($uploaded_filepath) ) {
                                                                    continue;
                                                                } else {
                                                                    $actualFileSize = filesize($uploaded_filepath);
                                                                    if( $actualFileSize < 1 ) {
                                                                        continue;
                                                                    }
                                                                    if( $actualFileSize != $file['file_size']  ){
                                                                        update_uploadpdf_file_size($file['file_id'], $actualFileSize);
                                                                        $file['file_size'] = $actualFileSize;
                                                                    }

                                                                    $file_has = true;
                                                                }
                                                                ?>

                                                                <tr>
                                                                    <td><?php echo $file['file_name']?></td>
                                                                    <td><?php echo filesize_formatted($file['file_size']); ?></td>
                                                                    <td>
                                                                        <form action="uploadpdf.php" method="post" enctype="multipart/form-data">
                                                                            <input type="hidden" name="file_id" value="<?php echo $file['file_id']?>">
                                                                            <button type="Submit" name="download" value="Download" class="btn btn-primary">Download</button>
                                                                        </form>
                                                                    </td>
                                                                    <td>
                                                                        <form action="uploadpdf.php" method="post" enctype="multipart/form-data">
                                                                            <input type="hidden" name="file_id" value="<?php echo $file['file_id']?>">
                                                                            <button type="Submit" name="delete" value="Delete" class="btn btn-primary btn-danger" onClick="if(confirm('Are you sure you want to delete this file?')){return true;}else{return false;}">Delete</button>
                                                                        </form>
                                                                    </td>
                                                                </tr>
                                                                <?php
                                                            }
                                                        } else {
                                                            echo '<tr><td colspan="3" class="text-center">No Files found in this folder</td></tr>';
                                                            $noFileMessageDisplayed = true;
                                                        }

                                                        if( !$file_has && !$noFileMessageDisplayed ) {
                                                            echo '<tr><td colspan="3" class="text-center">No Files found in this folder</td></tr>';
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
                </section>
            </div>
        </div>
    </div>


<?php
require_once("templates/footer.php");

