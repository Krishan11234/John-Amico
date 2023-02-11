<?php
$page_name = 'Customers Extra Fields';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

if( !empty($_POST['goto']) && ($_POST['goto'] == 'save')){
	//print_r($_POST['field']);
    // First empty the existing fields
	$query = "update extra_fields set title='' ";
	mysqli_query($conn,$query);

    // Then update the fields with te values
	foreach ($_POST['field'] as $key=>$val){
        $val = filter_var($val, FILTER_SANITIZE_STRING);
        $key = filter_var($key, FILTER_SANITIZE_STRING);

		$query = "update extra_fields set title='$val' where id='$key'";
		mysqli_query($conn,$query);
		//echo "<br>$query<br>err=".mysql_error();
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

    <div class="row">
        <form name="theform" action="extra_fields.php" class="form-horizontal form-bordered" method="post">
            <section class="panel">
                <div class="col-md-6 col-sm-8 col-xs-12 centering">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center">Fields List</h2>
                    </header>
                    <div class="panel-body">
                        <?php
                        for($i=1; $i<=20; $i++){
                            $a = mysqli_fetch_array(mysqli_query($conn,"select * from extra_fields where id='$i'"));
                            ?>
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="inputDefault<?php echo $i; ?>">Field #<?php echo $i?></label>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" id="inputDefault<?php echo $i; ?>" name="field[<?php echo $i; ?>]" maxlength="20" value="<?php echo $a['title']; ?>">
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <footer class="panel-footer">
                        <div class="row">
                            <div class="col-sm-9 centering text-center">
                                <input type ="hidden" name="goto" value="save">
                                <button id="subbut" type="Submit" name="update" value="Update" class="command btn btn-primary">Submit</button>
                                <button id="cancel" type="Reset" name="cancel" value="Cancel" class="command btn btn-default btn-danger">Reset</button>
                            </div>
                        </div>
                    </footer>
                </div>
            </section>
        </form>
    </div>
</div>


<?php
require_once("templates/footer.php");