<?php
$page_name = 'Important Documents';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

if(isset($_POST['download'])) {
    $sql = "SELECT * FROM upload_files WHERE file_id = '{$_POST['file_id']}'";
    $result = mysqli_query($conn,$sql) or die(mysqli_error($conn));
    $file = mysqli_fetch_array($result);

    $file_path = base_admin_path() . "/uploaded_files/{$file['file_id']}.dat";

    if( file_exists($file_path) ) {
        header("Content-Disposition: attachment; filename=".$file['file_name']);
        readfile($file_path);
        exit;
    } else {
        $error_message[] = 'Couldn\'t find the file you are requesting.';
    }
}


require_once("templates/header.php");
require_once("templates/sidebar.php");


$member_type_name = 'Folder';
$member_type_name_plural = 'Folders';
$self_page = 'imp_docs.php';
$page_url = base_member_url() . '/imp_docs.php?1=1';
$main_page_url = base_url() . $_SERVER['REQUEST_URI'];




$sql = "SELECT * FROM upload_folders ORDER BY folder_name";
$result = mysqli_query($conn,$sql);

if(mysqli_num_rows($result) > 0) {
    $folderHas = true;
}

$folder_id = ( (!empty($_GET['folder_id']) && is_numeric($_GET['folder_id'])) ? $_GET['folder_id'] : 0 );

if( !empty($folder_id) ) {
    $sql_check = "SELECT folder_id FROM upload_folders WHERE folder_id='$folder_id' ";
    $result_check = mysqli_query($conn,$sql_check);

    if(mysqli_num_rows($result_check) < 1) {
        $folder_id = 0;
    }
}

//debug(true, true, $folder_id);
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
        <div class="col-xs-12">
            <?php if( !empty($error_message) && is_array($error_message) ) { ?>
                <div class="alert alert-danger">
                    <ul><li><?php echo implode('</li><li>', $error_message); ?></li></ul>
                </div>
            <?php } ?>
            <?php if( !empty($success_message) && is_array($success_message) ) { ?>
                <div class="alert alert-success">
                    <ul><li><?php echo implode('</li><li>', $success_message); ?></li></ul>
                </div>
            <?php } ?>
        </div>
        <?php if(!empty($folderHas)): ?>
        <div class="col-md-4 ">
            <section class="panel">
                <header class="panel-heading">
                    <h2 class="panel-title text-center">List of <?php echo $member_type_name_plural; ?></h2>
                </header>
                <div class="panel-body folders">
                    <ul class="nav nav-main">
                        <?php
                        $i=0;
                        while($folder = mysqli_fetch_array($result)) :
                            $folders[$i] = $folder;

                            if(empty($folder_id) && ($i==0)) { $folder_id = $folders[0]['folder_id']; }

                            //debug(true, true, $folder_id);
                        ?>

                            <li class="<?php echo( ($folder_id == $folder['folder_id']) ? 'nav-active' : '' ); ?>"><a href="<?php echo $page_url.'&folder_id='.$folder['folder_id']; ?>"><?php echo $folder['folder_name']; ?></a></li>

                        <?php $i++; endwhile; ?>
                    </ul>
                </div>
            </section>
        </div>
        <?php endif; ?>

        <div class="col-md-8 col-xs-12 ">
            <section class="panel">
                <header class="panel-heading">
                    <h2 class="panel-title text-center">List of Files</h2>
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
                                $sql = "SELECT * FROM upload_files WHERE file_folder_id = '$folder_id' ORDER BY file_name";
                                $result2 = mysqli_query($conn,$sql);

                                if(mysqli_num_rows($result2) > 0) {
                                    while($file = mysqli_fetch_array($result2)) {
                                        ?>
                                        <tr>
                                            <td class="text-left"><?php echo $file['file_name']?></td>
                                            <td><?php echo filesize_formatted($file['file_size']); ?></td>
                                            <td>
                                                <form action="<?php echo $main_page_url; ?>" method="post" class="form form-validate" >
                                                    <input type="hidden" name="file_id" value="<?php echo $file['file_id']?>">
                                                    <button type="Submit" name="download" value="Download" class="btn btn-primary">Download</button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
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
</div>


<?php
require_once("templates/footer.php");

