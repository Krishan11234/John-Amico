<?php
$page_name = 'Update EC ID';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

$logs = array();

if ($act=="1") {

//$query=mysqli_query($conn,"DELETE FROM `tbl_calls`") or die (mysql_error());

    $handle = fopen ($_FILES['ffile']['tmp_name'], "r");
    $error = array();
    $i=0; $e = 0;
    while ($buffer = fgets($handle)) {
        $split=explode(",", $buffer);

        $amico_id = $split[0];
        $ec_id = $split[1];
        $ec_id = trim($ec_id, chr(10));
        $ec_id = trim($ec_id, chr(13));
        $sql = "UPDATE tbl_member  SET ec_id='".$ec_id."' WHERE amico_id='".$amico_id."'";
        mysqli_query($conn,$sql);

        if(mysqli_affected_rows($conn)==0){
            $e++;
            $error[] = "amico_id=".$amico_id." Ec ID=".$ec_id;

        }else {$i++;   }



    }

    fclose ($handle);
    if($e!==0){
        $err = "$e entries has been not found or not updated </br>";
        foreach($error as $text){
            $err .= $text."</br>";
        }
    }
    $msg="Update has been done!<br/> ".$i." entries has been updated.</br>".$err;
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
            <section class="panel">
                <form name="file_upload" class="form-bordered" action="" method="post" enctype="multipart/form-data">
                    <div class="col-xs-12 col-lg-10 col-md-10 centering">
                        <header class="panel-heading">
                            <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                        </header>
                        <?php if(!empty($msg)): ?>
                            <div class="panel-body pb-lg pt-lg mb-lg mt-lg">
                                <div class="alert alert-success">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <?php echo $msg; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="panel-body pb-lg pt-lg mb-lg mt-lg">
                            <div class="form-group">
                                <label class="col-md-3 control-label">Database File</label>
                                <div class="col-md-6">
                                    <div class="fileupload fileupload-new" data-provides="fileupload">
                                        <div class="input-append">
                                            <div class="uneditable-input">
                                                <i class="fa fa-file fileupload-exists"></i>
                                                <span class="fileupload-preview"></span>
                                            </div>
                                        <span class="btn btn-default btn-file">
                                            <span class="fileupload-exists">Change</span>
                                            <span class="fileupload-new">Select file</span>
                                            <input type="file" name="ffile">
                                        </span>
                                            <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">Remove</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <footer class="panel-footer text-center">
                            <input type="hidden" name="act" value="1">
                            <input type="submit" value="Submit" />
                        </footer>
                    </div>
                </form>
                <div class="clearfix"></div>
            </section>
            <?php if( !empty($logs) ) {
                echo implode('<br/>', $logs);
            } ?>
        </div>
    </div>


<?php
require_once("templates/footer.php");
