<?php

$page_name = isset($page_name) ? $page_name : 'Communicate With Other Members';
$page_title = isset($page_title) ? $page_title : 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

if(!isset($self_only)) {
    $self_only = false;
}

$td = 0;
$main_member_id = $_SESSION['member']['ses_member_id'];


function drawusers($memberid,$count,$listusers) {
    global $conn, $td, $self_only, $main_member_id;

    //debug(true, false, $memberid);

    if(empty($memberid)) return false;

    $rsmember=mysqli_query($conn,"select int_parent_id,int_customer_id,int_designation_id, amico_id from tbl_member where int_member_id=$memberid");
    list($parentid,$customerid,$designation, $amico_id)=mysqli_fetch_row($rsmember);
    if($customerid) {
        $query = "select customers_firstname,customers_lastname,customers_email_address from customers where customers_id=$customerid";
        $rscustomer = mysqli_query($conn, $query);

        list($firstname, $lastname, $memberemail) = mysqli_fetch_row($rscustomer);


        if ($td >= 1) {
            if ($td == 1) {
                echo '<tr valign="baseline" >';
            }
            $checked = ( isset($_POST['id'][$memberid]) ? 'checked' : '' );

            echo '<td width="5%"> <input type="checkbox"  '.$checked.' id="id[' . $memberid . ']" name="id[' . $memberid . ']" value="' . $memberid . '" title="' . $memberemail . '"></td>';
            echo '<td width="20%" ><label class="fullwidth" for="id[' . $memberid . ']"> ' . $firstname . '<br>' . $amico_id . ' </label></td>';
            $td++;
            $count++;
            $listusers[$count] = $memberid;
            if ($td >= 4) {
                echo '</tr>';
                $td = 1;
            }
        }
    }
    if($td <= 0) {//retriving parent
        $rsparent=mysqli_query($conn,"select int_member_id,int_customer_id,int_designation_id from tbl_member where int_member_id=$parentid");
        if(mysqli_num_rows($rsparent)>0){
            list($parentid,$customerid,$designation)=mysqli_fetch_row($rsparent);

            if( $main_member_id != $parentid ) {
                $rscustomer = mysqli_query($conn, "select customers_firstname,customers_lastname from customers where customers_id=$customerid");
                list($firstname, $lastname) = mysqli_fetch_row($rscustomer);

                $checked = (isset($_POST['id'][$parentid]) ? 'checked' : '');

                echo '<tr valign="baseline" bgcolor="#FFFFFF">';
                echo '<td width="5%" > <input type="checkbox"  ' . $checked . ' id="id[' . $parentid . ']" name="id[' . $parentid . ']" value="' . $parentid . '" title="id' . $parentid . '"></td>';
                echo '<td width="20%" ><label class="fullwidth" for="id[' . $parentid . ']">' . $lastname . '. ' . $firstname . '<br>ID ' . $parentid . ' </label></td>';
                $count = 0;
                $listusers[$count] = $memberid;
                $td = 2;
            }
        }
        else{
            $td=1;
        }
    }

    if(!$self_only) {
        $rschilds = mysqli_query($conn, "select int_member_id from tbl_member where int_parent_id=$memberid");
        while (list($childmemberid) = mysqli_fetch_row($rschilds)) {
            //echo $td.': td<br>';
            $listusers = drawusers($childmemberid, $count, $listusers);
        }
    }
    return $listusers;
}


function send_mail($mail_to,$mail_from,$subject, $message) {
    $headers = "From: ".$mail_from."\n";
    //$headers .= "X-Mailer: PHP/" . phpversion()."\n"; // mailer
    $headers .= "Reply-To: ". $mail_from."\n";  // Return path for errors
    $headers .= "Content-Type: text/html; charset=iso-8859-1\n"; // Mime type
    $mail_stat = mail($mail_to, $subject, $message, $headers);
}

function mailusers($selecteduser,$message,$subject,$email_from){
    global $conn;

    if(!empty($selecteduser) && is_array($selecteduser)) {
        $sql = "
            select mem.int_customer_id, cus.customers_email_address
            from tbl_member mem
            INNER JOIN customers cus ON cus.customers_id = mem.int_customer_id
            where mem.int_member_id IN (".implode(',', $selecteduser).")
        ";

        $query = mysqli_query($conn, $sql);

        if( mysqli_num_rows($query) > 0 ) {
            while($cus = mysqli_fetch_object($query)) {
                $to_email = $cus->customers_email_address;

                send_mail($to_email,$email_from,$subject,$message);
            }
        }
    }
}

if(isset($_POST['submit']) ){
    $subject = filter_var( trim($_POST['subject']), FILTER_SANITIZE_STRING);
    $message = filter_var( trim($_POST['message']), FILTER_SANITIZE_STRING);
    if( empty($subject) ) {
        $error_messages['subject'] = 'Please enter your email message.';
    }
    if( empty($message) ) {
        $error_messages['message'] = 'Please enter your email message.';
    }
    if (!empty($_POST['id']) && is_array($_POST['id'])) {
        $selectedusers = $_POST['id'];

        foreach ($selectedusers as $id) {
            if (is_numeric($id)) {
                $selecteduser[] = $id;
            }
        }
        //debug(false, true, $selectedusers, $selecteduser);
        if (empty($selecteduser)) {
            $error_messages['id'] = 'Please select at least one user';
        }
    } else {
        $error_messages['id'] = 'Please select at least one user';
    }

    if(empty($error_messages)) {
        if (!empty($selecteduser)) {
            //debug(false, true, $selecteduser);

            $rsmember = mysqli_query($conn, "select int_customer_id from tbl_member where int_member_id={$main_member_id}");
            list($customerid) = mysqli_fetch_row($rsmember);
            $rscustomer = mysqli_query($conn, "select customers_email_address from customers where customers_id=$customerid");
            list($email_from) = mysqli_fetch_row($rscustomer);
            mailusers($selecteduser, $message, $subject, $email_from);

            $messages[] = 'Email messages has been sent';
        }
    }
    /*$listusers=Array(0);
    $str="";
    $listusers=explode(",",$_POST['listuser1']);//list of all users
    for($i=0;$i<(count($listusers));$i++){
        $id='id'.$listusers[$i];
        if(!empty($$id)) {
            if ($$id > 0) {
                $str = $str . $$id . ',';
            }
        }
    }
    $selecteduser=explode(",",$str);
    $rsmember=mysqli_query($conn,"select int_customer_id from tbl_member where int_member_id={$_SESSION['member']['ses_member_id']}");
    list($customerid)=mysqli_fetch_row($rsmember);
    $rscustomer=mysqli_query($conn,"select customers_email_address from customers where customers_id=$customerid");
    list($email_from)=mysqli_fetch_row($rscustomer);
    mailusers($selecteduser,$_POST['message'],$_POST['subject'],$email_from);*/
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

        <div class="row genealogy_communicate">
            <div class="col-md-10 col-xs-12 centering">
                <form name="send_email" action="<?php echo $self_page; ?>" method="post" class="form form-validate">
                    <section class="panel">
                        <header class="panel-heading">
                            <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                        </header>
                        <div class="panel-body">
                            <?php if(!empty($error_messages)) : ?>
                                <div class="row">
                                    <div class="message_wrapper">
                                        <div class="alert alert-danger">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                            <ul>
                                                <li><?php echo implode('</li><li>', $error_messages); ?></li>
                                            </ul>
                                            <?php //unset($error_messages['send_email_modal']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if(!empty($messages)) : ?>
                                <div class="row">
                                    <div class="message_wrapper">
                                        <div class="alert alert-success">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                            <ul>
                                                <li><?php echo implode('</li><li>', $messages); ?></li>
                                            </ul>
                                            <?php //unset($error_messages['send_email_modal']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="form-group <?php echo ( !empty($error_messages['subject']) ? 'has-error' : '' ); ?> ">
                                <label class="col-md-4 control-label" for="subject">Email Subject</label>
                                <div class="col-md-8">
                                    <input type="text" name="subject" id="subject" class="form-control" value="<?php echo ( !empty($subject) ? $subject: '' ); ?>" required>
                                </div>
                            </div>
                            <div class="form-group <?php echo ( !empty($error_messages['message']) ? 'has-error' : '' ); ?> ">
                                <label class="col-md-4 control-label" for="message">Email Message</label>
                                <div class="col-md-8">
                                    <textarea name="message" id="message" class="form-control" cols="33" rows="5"><?php echo ( !empty($message) ? $message: '' ); ?></textarea>
                                </div>
                            </div>
                            <div class="form-group <?php echo ( !empty($error_messages['id']) ? 'has-error' : '' ); ?> ">
                                <label class="col-md-12 control-label">Select Users</label>
                                <div class="col-md-12 userlist">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <?php
                                            /*$listuser=drawusers($_SESSION['member']['ses_member_id'],0,Array());
                                            $str="";
                                            for($i=0;$i<=count($listuser);$i++){
                                                if( !empty($listuser[$i]) ) {
                                                    if (trim($listuser[$i]) != "") {
                                                        $str = $str . $listuser[$i] . ',';
                                                    }
                                                }
                                            }*/
                                            drawusers($main_member_id,0,Array());
                                            ?>
                                        </table>
                                        <?php //echo '<input type="hidden" name="listuser1" value="'.$str.'">'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <footer class="panel-footer">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <button type="Submit" name="submit" value="send_emails" class="command  btn btn-default btn-success btn-lg mr-lg">Submit</button>
                                    <button type="reset" class="btn btn-default btn-warning btn-lg ml-lg">Reset</button>
                                </div>
                            </div>
                        </footer>
                    </section>
                </form>
            </div>
        </div>
    </div>


<?php
require_once("templates/footer.php");

