<?php
$page_name = 'Business Systems Extra Fields';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

if( !empty($_POST['goto']) && ($_POST['goto'] == 'save')){

    //$query = "update extra_service_systems set title='' ";
    $query = "TRUNCATE TABLE extra_business_systems";
    mysqli_query($conn,$query);
    $query = "ALTER TABLE extra_business_systems AUTO_INCREMENT = '1'";
    mysqli_query($conn,$query);

    $i=1;
    foreach ($_POST['field']['title'] as $key => $val){


        //foreach ($val as $k => $v){
        //$query = "update extra_service_systems set $key='$v' where id='$k'";
        if ($_POST['field']['title'][$i] != ''){
            $query = "insert into extra_business_systems set title='".$_POST['field']['title'][$i]."', item_id='".$_POST['field']['item_id'][$i]."', category='".$_POST['field']['category'][$i]."'";
            mysqli_query($conn,$query);
        }
        //echo "<br>$query<br>err=".mysql_error();
        //}
        $i++;
    }
}
?>


    <script>
        function addRow(frm) {
            var rowNum = $('.system_field').size();
            //alert(rowNum);

            rowNum ++;

            var row = '<div class="row system_field" id="system_field_'+rowNum+'"><div class="col-sm-4"><div class="form-group"><label class="control-label">Category</label><input type="Text" class="form-control" name="field[category]['+rowNum+']" maxlength="20" value="" /></div></div><div class="col-sm-4"><div class="form-group"><label class="control-label">Field #'+rowNum+'</label><input type="Text" class="form-control" name="field[title]['+rowNum+']" maxlength="20" value="" /></div></div><div class="col-sm-4"><div class="form-group"><label class="control-label">Item ID</label><input type="Text" class="form-control" name="field[item_id]['+rowNum+']" maxlength="20" value="" /></div></div><div class="remove_btn_wrapper"><span class="remove_btn" onclick="removeRow('+rowNum+');" style="color:red;"><i class="fa fa-minus-circle fa-2" aria-hidden="true"></i></span></div></div>';

            $('.system_fields').append(row);
        }

        function removeRow(rnum) {
            $('#system_field_'+rnum).remove();
        }

    </script>

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
            <form name="theform" action="" class="form-horizontal form-bordered" method="post">
                <section class="panel">
                    <div class="col-md-10 col-sm-12 col-xs-12 centering">
                        <header class="panel-heading">
                            <h2 class="panel-title text-center">Fields List</h2>
                        </header>
                        <div class="panel-body system_fields">
                            <?php
                            $q = mysqli_query($conn,"select * from extra_business_systems order by category ASC");

                            $i = 1;
                            while ($a = mysqli_fetch_array($q)){
                                ?>
                                <div class="row system_field" id="system_field_<?php echo $a['id'];?>">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="control-label">Category</label>
                                            <input type="Text" class="form-control" name="field[category][<?php echo $a['id'];?>]" maxlength="20" value="<?php echo $a['category'];?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="control-label">Field #<?php echo $a['id'];?></label>
                                            <input type="Text" class="form-control" name="field[title][<?php echo $a['id'];?>]" maxlength="20" value="<?php echo $a['title'];?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="control-label">Item ID</label>
                                            <input type="Text" class="form-control" name="field[item_id][<?php echo $a['id'];?>]" maxlength="20" value="<?php echo $a['item_id'];?>" />
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <footer class="panel-footer">
                            <div class="row">
                                <div class="col-sm-9 centering text-center">
                                    <input type ="hidden" name="goto" value="save">
                                    <button id="subbut" type="Submit" name="update" value="Update" class="command btn btn-primary">Submit</button>
                                    <!--<button id="cancel" type="Reset" name="cancel" value="Cancel" class="command btn btn-default btn-danger">Reset</button>-->
                                    <button onclick="addRow(this.form);" type="button" value="Add row" class="command btn btn-success" >Add Row</button>
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
