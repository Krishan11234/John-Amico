<?php
$page_name = 'Guest Names';
$page_title = $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$is_popup = true;

$display_header = false;
require_once("templates/header.php");

$number = !empty($_GET['number']) ? filter_var($_GET['number'], FILTER_SANITIZE_NUMBER_INT) : 0;

?>

<script>
    function send_names() {
        guestz = '';
        <?php for($i=1; $i< ($number+1); $i++) : ?>
            if (document.form1.guest<?=$i?>_name.value != '') {
                guestz += document.form1.guest<?=$i?>_name.value + '; ';
            }
        <?php endfor;?>

        window.opener.updateValue('chick_guests1', guestz);
        window.close();
    }
</script>

<div role="main" class="content-body extra_information <?php echo ( $is_popup ? 'no-margin-left' : '' ); ?> ">
    <div class="row ">
        <div class="col-xs-12 centering">
            <form action="" name="form1" method="post" class="form form-validate" onsubmit="return false;">
                <section class="panel">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                    </header>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <?php for($i=1; $i< ($number+1); $i++) : ?>
                                <div class="form-group">
                                    <label class="col-sm-4 form-control-label" for="guest<?=$i?>_name">Guest #<?php echo $i;?> Name</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="guest<?=$i?>_name" name="guest<?=$i?>_name" >
                                    </div>
                                </div>
                                <?php endfor;?>
                            </div>
                        </div>
                    </div>
                    <footer class="panel-footer">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="Submit" name="submit" value="Save Names" class="command  btn btn-default btn-success mr-lg" onClick="send_names();">Save Names</button>
                                <button type="button" class="btn btn-default btn-warning ml-lg" onclick="window.close();">Close Window</button>
                            </div>
                        </div>
                    </footer>
                </section>
            <!--</form>-->
        </div>
    </div>
</div>



<?php
require_once("templates/footer.php");

