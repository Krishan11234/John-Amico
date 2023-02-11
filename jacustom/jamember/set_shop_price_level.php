<?php
$page_name = 'Set Shop Price Level';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

$logs = array();

$retval = 0;
$member_id = $_SESSION['member']['ses_member_id'];

if( !empty($_POST['btn_setpricelevel']) && !empty($_POST['dd_pricelevel']) && !empty($member_id) ) {

    /*setpricelevel($member_id,$_POST['dd_pricelevel']);
    $table="tbl_member";
    $fieldlist="int_downline_price_level=".$_POST['dd_pricelevel'];
    $condition=" where int_member_id=".$_SESSION['member']['ses_member_id'];
    $result=update_rows($conn, $table, $fieldlist, $condition);

    $retval = 1;*/
}

function setpricelevel($memberid,$level){
    global $conn;

    $rs=mysqli_query($conn,"select * from tbl_member where int_parent_id=".$memberid);

    for($i=0;$i < mysqli_num_rows($rs); $i++){
        $table="tbl_member";
        $fieldlist="int_price_level=".$level;
        $condition=" where int_member_id=".mysqli_result($rs,$i,'int_member_id');
        $result = update_rows($conn, $table, $fieldlist, $condition); // function call to update

        setpricelevel(mysqli_result($rs,$i,'int_member_id'),$level);
    }
}


$query="select int_downline_price_level AS price_level from tbl_member where int_member_id='$member_id'";
$rscheckparent = mysqli_query($conn,$query);
list($price_level) = mysqli_fetch_row($rscheckparent);

$price_level = 1;

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
                <form name="set_price" class="form-bordered" action="" method="post">
                    <div class="col-xs-12 col-lg-6 col-md-8 centering">
                        <header class="panel-heading">
                            <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                        </header>
                        <?php if(!empty($retval)): ?>
                            <div class="">
                                <div class="alert alert-success">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    Price Level Has Been Updated Successfully.
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="panel-body pb-lg pt-lg mb-lg mt-lg">
                            <div class="row form-group">
                                <label class="col-xs-4 control-label" for="dd_pricelevel">Price Level</label>
                                <div class="col-xs-8 form-inline">
                                    <select name="dd_pricelevel" id="dd_pricelevel" class="form-control">
                                        <option value="0" selected="">Set Price Level</option>
                                        <option value="1" <?php echo ( (!empty($price_level) && ($price_level == 1) ) ? 'selected' : '' ); ?> >Level A</option>
                                        <option value="2" <?php echo ( (!empty($price_level) && ($price_level == 2) ) ? 'selected' : '' ); ?> >Level B</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <footer class="panel-footer text-center">
                            <input type="hidden" name="setP" value="1">
                            <input type="submit" value="Change Price Level" name="btn_setpricelevel" />
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
