<?php
$page_name = 'Contact Information';
$page_title = $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$is_popup = true;

//$display_header = false;
//require_once("templates/header.php");

$member_id = $_SESSION['member']['ses_member_id'];
$session_user = $_SESSION['member']['session_user'];


$memberid_got = !empty($memberid_got) ? $memberid_got : ( !empty($_GET['memberid']) ? filter_var($_GET['memberid'], FILTER_SANITIZE_NUMBER_INT) : 0 );
$memberid = $memberid_got;

if(empty($memberid)) {
    exit;
}

$a_amico = mysqli_fetch_array(mysqli_query($conn,"select amico_id, mtype from tbl_member where int_member_id='$member_id'"));
$user_amico_id = $a_amico['amico_id'];
$user_mtype    = $a_amico['mtype'];


$rsmember=mysqli_query($conn,"select c.customers_id,c.customers_firstname,c.customers_lastname,c.customers_email_address,customers_password,customers_telephone,customers_fax from customers c inner join tbl_member m on c.customers_id=m.int_customer_id where m.int_member_id='$memberid'");
list($customersid,$firstname,$lastname,$email,$pass,$phone,$fax)=mysqli_fetch_row($rsmember);
$customers_id = $customersid;

//echo "$user_amico_id {} $user_mtype {}".$_SESSION['ses_member_id'];

if($_POST['goto'] == 'save__cinfo_extra') {
    $query = "delete from  extra_fields_customers where customers_id='$customers_id'";
    mysqli_query($conn,$query);

    foreach ($_POST['field'] as $key => $val) {
        $query = "insert into extra_fields_customers set field_id='$key', customers_id='$customers_id', val='$val' ";
        mysqli_query($conn,$query);
    }

    $msg = "The information has been saved!";
}

?>

<!--<div role="main" class="content-body extra_information <?php /*echo ( $is_popup ? 'no-margin-left' : '' ); */?> ">-->
    <div class="row ">
        <div class="col-xs-12 centering">
            <section class="panel">
                <header class="panel-heading">
                    <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                </header>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-12 profile ">

                            <?php if(!empty($msg)): ?>
                                <div class="message">
                                    <div class="alter alert-success"><?php echo $msg;?></div>
                                </div>
                            <?php endif;?>

                            <form name="cinfo_extra" id="cinfo_extra" action="" method="post">
                                <section class="panel panel-primary">
                                    <header class="panel-heading text-center padding-5-10">
                                        <h2 class="panel-title font-size__16">Extra Information - <?=$firstname?> <?=$lastname?></h2>
                                    </header>
                                    <div class="panel-body">
                                        <input type="hidden" name="memberid" value="<?=$memberid?>" >
                                        <input type="hidden" name="goto" value="save__cinfo_extra">

                                        <?php
                                        $query = "select id, title from extra_fields where title is not null and title!='' order by id ";
                                        $q = mysqli_query($conn,$query);

                                        while ($row = mysqli_fetch_array($q)) :
                                            $aaa = mysqli_fetch_array(mysqli_query($conn,"select val from extra_fields_customers where field_id='".$row['id']."' and customers_id='$customers_id'"));
                                        ?>
                                            <div class="form-group">
                                                <label class="col-xs-4 form-control-label" for=""><?=$row['title']?></label>
                                                <div class="col-xs-8">
                                                    <input type="text" class="form-control" name="field[<?=$row['id']?>]" value="<?=$aaa['val']?>">
                                                </div>
                                            </div>

                                        <?php endwhile; ?>
                                    </div>
                                    <footer class="panel-footer">
                                        <div class="row">
                                            <div class="col-md-12 text-center">
                                                <button type="Submit" name="submit" value="send_emails" class="command  btn btn-default btn-success mr-lg">Submit</button>
                                                <button type="reset" class="btn btn-default btn-warning ml-lg">Reset</button>
                                            </div>
                                        </div>
                                    </footer>
                                </section>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>


<?php
//require_once("templates/footer.php");