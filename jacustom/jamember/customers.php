<?php

$page_name = 'Customer Management';
$page_name = isset($page_name) ? $page_name : 'Communicate With Other Members';
$page_title = isset($page_title) ? $page_title : 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$td = 0;
$member_id = $_SESSION['member']['ses_member_id'];
$customersAvailable = false;


if (isset($_POST['export'])) {
    $FILE=fopen("./tmp","w");
    fwrite($FILE,"\"first_name\",\"last_name\",\"street_address\",\"city\",\"state\",\"zip\",\"email\",\"phone\",\"dob\"\n");
    $sql = mysqli_query($conn,"SELECT * FROM member_customers WHERE member_id='$member_id'");
    while ($row = mysqli_fetch_object($sql)) {
        fwrite($FILE,"\"".str_replace('\"','\'',$row->first_name)."\",\"".str_replace('\"','\'',$row->last_name)."\",\"".str_replace('\"','\'',$row->street_address)."\",\"".str_replace('\"','\'',$row->city)."\",\"".str_replace('\"','\'',$row->state)."\",\"".str_replace('\"','\'',$row->zip)."\",\"".str_replace('\"','\'',$row->email)."\",\"".str_replace('\"','\'',$row->phone)."\",\"".str_replace('\"','\'',$row->dob)."\"\n");
    }
    fclose($FILE);
    header('Content-Type: application/octet-stream');
    header('Content-Length: '.filesize('./tmp'));
    header('Content-Disposition: attachment; filename="customers.csv"');
    readfile('./tmp');
    unlink('./tmp');
}


require_once("templates/header.php");
require_once("templates/sidebar.php");


//debug(true, true, $_POST);
if (isset($Action)) {
    if ($Action == "Email") {
        $Message = $Action;
        //_POST["subject"] This should be the subject
        //_POST["BodyContents"] And, this should be the body
        //Customers
        if (is_array($Customers) && count($Customers) > 0) {
            //First, get each of the customer's email addresses
            $ListOfCustomers = "";$comma = "";
            foreach ($Customers as $id=>$g) {
                $ListOfCustomers .= $comma.$id;
                $comma = ", ";
            }
            $CustomersEmailAddress = mysqli_query($conn,"SELECT email FROM member_customers WHERE members_customer_id IN ($ListOfCustomers)");
            $ListOfEmails = "";$comma = "";
            while ($ACustomer = mysqli_fetch_object($CustomersEmailAddress)) {
                $ListOfEmails .= $comma.$ACustomer->email;
                $comma = ", ";
            }

            //Now, send the email:
            if (mail($customers_email_address,stripslashes($subject),stripslashes($BodyContents),"From: ".$customers_email_address."\r\n"."MIME-Version: 1.0\r\nContent-type: text/html; charset=iso-8859-1\r\n"."BCC:".$ListOfEmails)) {
                $Message = "Your email was successfully sent out.";
            }
            else {
                $Message = "The system encountered an error in sending your email.";
            }
            //Then, clear the email variables
            $subject = "";
            $BodyContents = "";
        }
        else {
            $Message = "Please select at least one customer to receive your email";
        }
    }
    elseif ($Action == "Delete") {
        foreach ($Customers as $id=>$g) {
            mysqli_query($conn,"DELETE FROM member_customers WHERE members_customer_id=$id");
        }
    }
}

if(!empty($member_id)) {
    $MembersCustomers = mysqli_query($conn,"SELECT * FROM member_customers WHERE member_id='$member_id'");
    if ($MembersCustomers && mysqli_num_rows($MembersCustomers) >0 ) {
        $customersAvailable = true;
    }
}


?>
    <script>
        Message = "<?if (isset($Message)) { echo $Message;}?>";
        if (Message.length > 0) {
            alert (Message);
        }
        function CheckAll(that) {
            /*for (Customer in ShownCustomers) {
                document.getElementById("Customers["+ShownCustomers['Customer']+"]").checked = !(document.getElementById("Customers["+ShownCustomers['Customer']+"]").checked);
            }*/
            if ($(that).prop('checked')) {
                jQuery('.customer_check').prop('checked', true);
            } else {
                jQuery('.customer_check').prop('checked', false);
            }
        }
        function EditCustomer(which) {
            window.open("<?php echo base_member_url(); ?>/edit_customer.php?which="+which,"_blank","width=500,height=430,toolbars=no,scrollbars=yes");
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
                            <div class="form-group <?php echo ( !empty($error_messages['id']) ? 'has-error' : '' ); ?> ">
                                <label class="col-md-12 control-label">Customers</label>
                                <div class="col-md-12 userlist">
                                    <?php if ($customersAvailable) { ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <tr>
                                                    <th class="text-center">
                                                        <input type="checkbox" onClick="CheckAll(this);">
                                                    </th>
                                                    <th class="text-center">Edit</th>
                                                    <th class="text-center">Name</th>
                                                    <th class="text-center">Phone</th>
                                                    <th class="text-center">Email</th>
                                                </tr>
                                                <?php
                                                $Id_List = "";$comma = "";
                                                while ($ACustomer = mysqli_fetch_object($MembersCustomers)) :
                                                    $Id_List .= $comma.'"'.$ACustomer->members_customer_id.'"';
                                                    $comma = ", ";
                                                ?>
                                                    <tr>
                                                        <td class="text-center"><input class="customer_check" type="checkbox" name="Customers[<?=$ACustomer->members_customer_id?>]" id="Customers[<?=$ACustomer->members_customer_id?>]"></td>
                                                        <td class="text-center"><a href="#" onClick="EditCustomer(<?=$ACustomer->members_customer_id?>);return false;"><i class="fa fa-pencil"></i></a></td>
                                                        <td class="text-center"><?=$ACustomer->first_name." ".$ACustomer->last_name?></td>
                                                        <td class="text-center"><?=$ACustomer->phone?></td>
                                                        <td class="text-center"><a href="mailto:<?=$ACustomer->email?>"><?=$ACustomer->email?></a></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </table>
                                            <script> ShownCustomers = Array(<?=$Id_List?>); </script>
                                        </div>
                                    <?php } ?>
                                    <div class="buttons">
                                        <?php if ($customersAvailable) : ?>
                                            <input type="submit" class="danger" value="Delete Selected"  onClick="document.getElementById('CustomerAction').value='Delete'" style="margin: 5px 25px;">
                                            <input type="submit" name="export" value="Export All to Excel" style="margin: 5px 25px;">
                                        <?php endif; ?>
                                        <input type="button" value="Add New"  onClick="EditCustomer('');return false;" style="margin: 5px 25px;">
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group <?php echo ( !empty($error_messages['subject']) ? 'has-error' : '' ); ?> ">
                                <label class="col-md-4 control-label" for="subject">Email Subject</label>
                                <div class="col-md-8">
                                    <input type="text" name="subject" id="subject" class="form-control" value="<?php echo ( !empty($subject) ? $subject: '' ); ?>" >
                                </div>
                            </div>
                            <div class="form-group <?php echo ( !empty($error_messages['message']) ? 'has-error' : '' ); ?> ">
                                <label class="col-md-4 control-label" for="message">Email Message</label>
                                <div class="col-md-8">
                                    <textarea name="message" id="message" class="form-control" cols="33" rows="5"><?php echo ( !empty($message) ? $message: '' ); ?></textarea>
                                </div>
                            </div>
                        </div>
                        <footer class="panel-footer">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <input type="hidden" name="Action" id="CustomerAction" value="">
                                    <button type="Submit" name="submit" onClick="document.getElementById('CustomerAction').value='Email'"  value="send_emails" class="command  btn btn-default btn-success btn-lg mr-lg">Submit</button>
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
