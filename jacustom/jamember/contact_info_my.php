<?php

$page_name = 'Extra Information';
$page_title = $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$is_popup = true;

//$display_header = false;
//require_once("templates/header.php");

$memberid_got = !empty($memberid_got) ? $memberid_got : ( !empty($_GET['memberid']) ? filter_var($_GET['memberid'], FILTER_SANITIZE_NUMBER_INT) : 0 );
$currentUser_memberId = $_SESSION['member']['ses_member_id'];


if(function_exists('create_table__contact_organizer_subscription')) {
    $tableCreated = create_table__contact_organizer_subscription();
}


if(!empty($memberid_got) ) {

    //echo '<pre>'; var_dump($_POST); die();

    if($tableCreated) {
        $idExistedSql = "SELECT id, subscribed FROM tbl_member_subscription WHERE ref_member_id={$currentUser_memberId} AND sub_member_id={$memberid_got} ";
        $idExistedQuery = mysqli_query($conn, $idExistedSql);

        if(mysqli_num_rows($idExistedQuery) > 0 ) {
            list($subscriptionId, $contact_organizer_email_subscribed) = mysqli_fetch_row($idExistedQuery);
        }
    }

    if($_POST['goto'] == 'save__cinfo_info_my' && !empty($currentUser_memberId) ){
        if( $tableCreated )
        {
            $newsletterSubscribed = in_array($_POST['contact_organizer_email_subscribed'], array(0, 1)) ? (string)$_POST['contact_organizer_email_subscribed'] : NULL;
            if( !empty($subscriptionId) ) {
                $updateSql = "UPDATE tbl_member_subscription SET subscribed={$newsletterSubscribed}  WHERE id={$subscriptionId} ";
            }
            else {
                $updateSql = " INSERT INTO tbl_member_subscription (ref_member_id, sub_member_id, subscribed, created_at) VALUES ({$currentUser_memberId}, {$memberid_got}, $newsletterSubscribed, NOW() )";
            }
            mysqli_query($conn, $updateSql);

            $msg = "The information has been saved!";
        }
    }

    if($tableCreated) {
        $idExistedSql = "SELECT id, subscribed FROM tbl_member_subscription WHERE ref_member_id={$currentUser_memberId} AND sub_member_id={$memberid_got} ";
        $idExistedQuery = mysqli_query($conn, $idExistedSql);

        if(mysqli_num_rows($idExistedQuery) > 0 ) {
            list($subscriptionId, $contact_organizer_email_subscribed) = mysqli_fetch_row($idExistedQuery);
        }
    }

    $rsselcustomer = mysqli_query($conn,"select c.customers_id, c.customers_email_address, c.customers_telephone, c.customers_fax, c.customers_password, c.mobile_phone, c.operator_id from customers c inner join tbl_member t  on c.customers_id=t.int_customer_id WHERE t.int_member_id = '$memberid_got'");
    list($customerid,$email,$phone,$fax,$password,$mobile_phone,$operator_id)= mysqli_fetch_row($rsselcustomer);

    $rsseladdress1 = mysqli_query($conn,"select entry_company, entry_firstname, entry_lastname, entry_street_address,entry_street_address2, entry_postcode, entry_city, entry_country_id, entry_zone_id from address_book WHERE customers_id = $customerid and address_book_id=1");
    list($company,$firstname,$lastname,$streetadd, $addres, $postcode,$city,$country,$zone)= mysqli_fetch_row($rsseladdress1);


    $rs_operator = mysqli_query($conn,"select  operator from mobile_operators where id=$operator_id");
    list($operator)=mysqli_fetch_row($rs_operator);
    $rs_country = mysqli_query($conn,"select countries_name from countries  where countries_id=$country");
    list($country)=mysqli_fetch_row($rs_country);

    $rs_state = mysqli_query($conn,"select zone_name from zones  where zone_id=$zone ");
    list($stat)=mysqli_fetch_row($rs_state);

    if($tableCreated) {
        if( !isset($contact_organizer_email_subscribed) || ($contact_organizer_email_subscribed == 1) ) {
            $contact_organizer_email_subscribed = 1;
        } else {
            $contact_organizer_email_subscribed = 0;
        }
    }
    //echo '<pre>'; var_dump($contact_organizer_email_subscribed); die();
}
?>

<?php if(!empty($memberid_got) ) : ?>

    <!--<div role="main" class="content-body extra_information <?php /*echo ( $is_popup ? 'no-margin-left' : '' ); */?> ">-->
        <div class="row ">
            <div class="col-xs-12 centering">

                <?php if(!empty($msg)): ?>
                    <div class="message">
                        <div class="alert alert-success"><?php echo $msg;?></div>
                    </div>
                <?php endif;?>

                <form action="" name="cinfo_info_my" method="post">
                    <section class="panel">
                        <header class="panel-heading">
                            <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                        </header>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-12 personal ">
                                    <section class="panel panel-primary">
                                        <input type="hidden" name="memberid" value="<?=$memberid_got?>" >
                                        <input type="hidden" name="goto" value="save__cinfo_info_my">

                                        <header class="panel-heading text-center padding-5-10">
                                            <h2 class="panel-title font-size__16">Personal Information</h2>
                                        </header>
                                        <div class="panel-body">
                                            <div class="form-group">
                                                <div class="col-xs-4"><p class="form-control-static strong">Last Name</p></div>
                                                <div class="col-xs-8"><p class="form-control-static"><?php echo $lastname; ?></p></div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-xs-4"><p class="form-control-static strong">First Name</p></div>
                                                <div class="col-xs-8"><p class="form-control-static"><?php echo $firstname; ?></p></div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-xs-4"><p class="form-control-static strong">Password</p></div>
                                                <div class="col-xs-8"><p class="form-control-static">*****</p></div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-xs-4"><p class="form-control-static strong">Email Address</p></div>
                                                <div class="col-xs-8"><p class="form-control-static"><?php echo $email; ?></p></div>
                                            </div>
                                        </div>
                                    </section>
                                    <section class="panel panel-primary">
                                        <header class="panel-heading text-center padding-5-10">
                                            <h2 class="panel-title font-size__16">Company Information</h2>
                                        </header>
                                        <div class="panel-body">
                                            <div class="form-group">
                                                <div class="col-xs-4"><p class="form-control-static strong">Company</p></div>
                                                <div class="col-xs-8"><p class="form-control-static"><?php echo $company; ?></p></div>
                                            </div>
                                        </div>
                                    </section>
                                    <section class="panel panel-primary">
                                        <header class="panel-heading text-center padding-5-10">
                                            <h2 class="panel-title font-size__16">Address</h2>
                                        </header>
                                        <div class="panel-body">
                                            <div class="form-group">
                                                <div class="col-xs-4"><p class="form-control-static strong">Street Address</p></div>
                                                <div class="col-xs-8"><p class="form-control-static"><?php echo $streetadd; ?></p></div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-xs-4"><p class="form-control-static strong">Post Code</p></div>
                                                <div class="col-xs-8"><p class="form-control-static"><?php echo $postcode; ?></p></div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-xs-4"><p class="form-control-static strong">City</p></div>
                                                <div class="col-xs-8"><p class="form-control-static"><?php echo $city; ?></p></div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-xs-4"><p class="form-control-static strong">State</p></div>
                                                <div class="col-xs-8"><p class="form-control-static"><?php echo $stat; ?></p></div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-xs-4"><p class="form-control-static strong">Country</p></div>
                                                <div class="col-xs-8"><p class="form-control-static"><?php echo $country; ?></p></div>
                                            </div>
                                        </div>
                                    </section>
                                    <section class="panel panel-primary">
                                        <header class="panel-heading text-center padding-5-10">
                                            <h2 class="panel-title font-size__16">Contact</h2>
                                        </header>
                                        <div class="panel-body">
                                            <div class="form-group">
                                                <div class="col-xs-4"><p class="form-control-static strong">Phone Number</p></div>
                                                <div class="col-xs-8"><p class="form-control-static"><?php echo $phone; ?></p></div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-xs-4"><p class="form-control-static strong">Fax Number</p></div>
                                                <div class="col-xs-8"><p class="form-control-static"><?php echo $fax; ?></p></div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-xs-4"><p class="form-control-static strong">Mobile Phone</p></div>
                                                <div class="col-xs-8"><p class="form-control-static"><?php echo $mobile_phone; ?></p></div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-xs-4"><p class="form-control-static strong">Carrier</p></div>
                                                <div class="col-xs-8"><p class="form-control-static"><?php echo $operator; ?></p></div>
                                            </div>
                                        </div>
                                    </section>
                                    <section class="panel panel-primary">
                                        <header class="panel-heading text-center padding-5-10">
                                            <h2 class="panel-title font-size__16">Options</h2>
                                        </header>
                                        <div class="panel-body">
                                            <div class="form-group">
                                                <div class="col-xs-4"><label for="contact_organizer_email_subscribed" class="form-control-static strong">Newsletter Subscribed?</label></div>
                                                <div class="col-xs-8">
                                                    <input type="radio" class="" id="contact_organizer_email_subscribed_yes" name="contact_organizer_email_subscribed" value="1" <?php echo ( ($contact_organizer_email_subscribed == 1) ? 'checked="checked"' : ''); ?>>
                                                    <label for="contact_organizer_email_subscribed_yes">Yes</label>
                                                    <input type="radio" class="" id="contact_organizer_email_subscribed_no" name="contact_organizer_email_subscribed" value="0" <?php echo ( ($contact_organizer_email_subscribed==0) ? 'checked="checked"' : ''); ?>>
                                                    <label for="contact_organizer_email_subscribed_no">No</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-xs-4"><p class="form-control-static strong">Discount Group</p></div>
                                                <div class="col-xs-8"><p class="form-control-static"></p></div>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </div>
                        </div>
                        <footer class="panel-footer">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <button type="Submit" name="submit" value="save" class="command  btn btn-default btn-success mr-lg">Submit</button>
                                    <button type="reset" class="btn btn-default btn-warning ml-lg">Reset</button>
                                </div>
                            </div>
                        </footer>
                    </section>
                </form>
            </div>
        </div>


    <?php
    //require_once("templates/footer.php");

endif;?>