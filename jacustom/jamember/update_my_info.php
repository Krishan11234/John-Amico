<?php
$page_name = 'Update My Profile';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


$member_id = $_SESSION['member']['ses_member_id'];
$customers_id = $_SESSION['member']['ses_customer_id'];

$sql="SELECT c.customers_firstname, c.customers_lastname, c.customers_email_address, c.customers_telephone, c.mobile_phone, c.operator_id, c.ssn, c.license_number, c.type, c.customers_password, tm.amico_id, tm.nickname FROM customers c, tbl_member tm WHERE c.customers_id=tm.int_customer_id AND tm.int_member_id='$member_id'";
$res=mysqli_query($conn,$sql) or die(mysqli_error($conn));
//$row=mysqli_fetch_array($res);
list($firstname, $lastname, $email, $phone, $mobile, $operator_id, $ssn, $license_number, $type, $password, $amico_id, $nickname) = mysqli_fetch_row($res);

$cc_id = const_contact_query($email);

//debug(false, true, $_SESSION['member'], $member_id, $customers_id, $firstname, $lastname, $email, $phone, $mobile, $operator_id, $ssn, $license_number, $type, $password, $amico_id, $nickname);

$addresses = array(
    'billing' => array(
        'name' => 'Billing Address',
    ),
    'shipping' => array(
        'name' => 'Shipping Address',
        'jsMethod' => 'copyBillingToShipping',
    ),
    'check' => array(
        'name' => 'Check Address',
        'jsMethod' => 'copyBillingToCheck',
    ),
);

//debug(true, true, $_POST['email'], filter_var($_POST['email'], FILTER_VALIDATE_EMAIL), $_POST);

if( !empty($_POST['goto']) && ($_POST['goto'] == 'update') && !empty($_POST['update']) ) {

    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $cc_id = filter_var($_POST['cc_id'], FILTER_SANITIZE_NUMBER_INT);
    $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);
    $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_STRING);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $mobile = filter_var($_POST['mobile_phone'], FILTER_SANITIZE_STRING);
    $operator_id = filter_var($_POST['operator'], FILTER_SANITIZE_NUMBER_INT);
    $ssn = filter_var($_POST['ssn'], FILTER_SANITIZE_STRING);
    $license_number = filter_var($_POST['license_number'], FILTER_SANITIZE_STRING);
    $type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
    $nicknamePosted  = filter_var( $_POST['nickname'], FILTER_SANITIZE_STRING);
    $password = htmlspecialchars($_POST['customers_password']);
    $confirm_password = htmlspecialchars($_POST['confirmpass']);

    if( empty($email) ) {
        $error_messages['email'] = 'Please a valid Email Address';
        $email = $_POST['email'];
    }

    if( $password != $confirm_password ) {
        $error_messages['customers_password'] = 'Password mismatched!';
    }

    // Updating Nickname
    if( ($nicknamePosted != $nickname) && amico_nickname_exists($nicknamePosted) ) {
        $error_messages['nickname'] = 'Nickname already exists. Please choose a different one.';
        $nicknameExists =true;
    }

    if( empty($error_messages) ) {

        $sql2="UPDATE customers c inner join tbl_member tm on c.customers_id=tm.int_customer_id  SET c.customers_firstname='$firstname', c.customers_lastname='$lastname', c.customers_email_address='$email', c.customers_telephone='$phone', c.mobile_phone='$mobile', c.operator_id='$operator_id', c.ssn='$ssn', c.license_number='$license_number', c.type='$type' ";

        if(!empty($password)) {
            $sql2 .= ", c.customers_password='$password'";
        }
        $sql2 .= " WHERE tm.int_member_id='$customers_id' ";

        //debug(false, true, $sql2);

        $res2=mysqli_query($conn,$sql2) or die (mysqli_error($conn));

        if( empty($nicknameExists) ) {
            $sql2 = "UPDATE tbl_member SET nickname='" . $nicknamePosted . "' WHERE int_customer_id='" . $customers_id . "'";
            $res2 = mysqli_query($conn, $sql2);
        }

        // Addresses
        $i=1;
        foreach($addresses as $akey => $address) {
            $street1 = filter_var( $_POST['entry_street_address_' . $i], FILTER_SANITIZE_STRING);
            $street2 = filter_var( $_POST['entry_street_address2_' . $i], FILTER_SANITIZE_STRING);
            $post = filter_var( $_POST['entry_postcode_' . $i], FILTER_SANITIZE_STRING);
            $city = filter_var( $_POST['entry_city_' . $i], FILTER_SANITIZE_STRING);
            $state = filter_var( $_POST['entry_zone_id_' . $i], FILTER_SANITIZE_NUMBER_INT);


            mysqli_query($conn,"
                UPDATE address_book a inner join tbl_member t on a.customers_id=t.int_customer_id
                SET a.entry_street_address='$street1', a.entry_street_address2='$street2', a.entry_city='$city', a.entry_zone_id='$state', a.entry_postcode='$post'

                WHERE t.int_member_id='$member_id' AND address_book_id='$i'
            ") or die (mysqli_error($conn));

            $i++;
        }

        make_customer_file($customers_id);

        if($cc_id == 0){
            const_contact_create($email, $firstname, $lastname);
        } else{
            const_contact_update($cc_id, $email, $firstname, $lastname);
        }

        $messages[] = "Your Information Has Been Saved";
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
            <?php if(!empty($messages) || !empty($error_messages) ) : ?>
                <section class="panel">
                    <div class="col-md-8 col-xs-12 centering">
                        <div class="row">
                            <div class="message_wrapper">
                                <div class="alert alert-success <?php echo ( !empty($error_messages) ? 'alert-danger' : ''); ?>">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <ul>
                                        <?php if( !empty($error_messages) ) : ?>
                                            <li><?php echo implode('</li><li>', $error_messages); ?></li>
                                        <?php elseif( !empty($messages) ) : ?>
                                            <li><?php echo implode('</li><li>', $messages); ?></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
            <section class="panel">
                <div class="col-md-8 col-xs-12 centering">
                    <div class="edit_profile_wrapper">
                        <form name="theform" class="form-bordered member_addedit add_edit_member_type_wrapper form form-validate" action="" method="post">
                            <input type="hidden" name="cc_id" value="<?php echo ( !empty($cc_id) ? $cc_id : 0 ); ?>">

                            <header class="panel-heading">
                                <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                            </header>
                            <div class="panel-body">
                                <div class="contact_billing_fields_section">
                                    <div class="form-group heading text-center no-border-bottom"><p></p></div>
                                    <div class="forms-group contact_billing_fields">
                                        <div class="form-group no-border-bottom">
                                            <label class="col-md-4 control-label">Member ID</label>
                                            <div class="col-lg-8 ">
                                                <p class="form-control-static remove_fcs_padding"><strong><?php echo $amico_id; ?></strong></p>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4 control-label" for="title">Name</label>
                                            <div class="col-md-8">
                                                <div class="row form-group">
                                                    <div class="col-lg-6">
                                                        <input type="text" class="form-control" id="firstname" name="firstname" maxlength="20" placeholder="First Name" value="<?php echo ( !empty($firstname) ? $firstname : '' ); ?>">
                                                    </div>
                                                    <div class="mb-md hidden-lg hidden-xl"></div>
                                                    <div class="col-lg-6">
                                                        <input type="text" class="form-control" id="lastname" name="lastname" maxlength="20" placeholder="Last Name" value="<?php echo ( !empty($lastname) ? $lastname : '' ); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group <?php echo ( (!empty($error_messages) && in_array('email', $error_messages)) ? 'has-error' : '' );?> ">
                                            <label class="col-md-4 control-label" for="email">Email Address</label>
                                            <div class="col-md-8">
                                                <input type="text" class="form-control" name="email" id="email" maxlength="30" value="<?php echo ( !empty($email) ? $email : '' ); ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4 control-label" for="phone">Phone Number</label>
                                            <div class="col-md-8 form-inline">
                                                <input type="text" class="form-control" name="phone" id="phone" maxlength="40" value="<?php echo ( !empty($phone) ? $phone : '' ); ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4 control-label" for="mobile_phone">Mobile Number</label>
                                            <div class="col-md-8 form-inline">
                                                <input type="text" class="form-control" name="mobile_phone" id="mobile_phone" maxlength="20" value="<?php echo ( !empty($mobile_phone) ? $mobile_phone : '' ); ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4 control-label" for="operator">Carrier</label>
                                            <div class="col-md-8 form-inline">
                                                <select name="operator" class="form-control">
                                                    <option value = 0 >Select Operator</option>
                                                    <?php
                                                    $rs_operator = mysqli_query($conn,"select id, operator from mobile_operators order by operator");
                                                    while(list($op_id, $operat)= mysqli_fetch_row($rs_operator)){

                                                        if( !empty($operator_id) && ($op_id==$operator_id) )
                                                            echo '<option value ='.$op_id.' selected>'.$operat.'</option>';
                                                        else
                                                            echo '<option value ='.$op_id.' >'.$operat.'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4 control-label" for="ssn">Social Security Number</label>
                                            <div class="col-md-8 form-inline">
                                                <input type="text" class="form-control" name="ssn" id="ssn" maxlength="11" value="<?php echo ( !empty($ssn) ? $ssn : '' ); ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4 control-label" for="license_number">License Number</label>
                                            <div class="col-md-8 form-inline">
                                                <input type="text" class="form-control" name="license_number" id="license_number" maxlength="20" value="<?php echo ( !empty($license_number) ? $license_number : '' ); ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4 control-label" for="type">Type</label>
                                            <div class="col-md-8 form-inline">
                                                <?php $type = ( (!empty($type) ) ? $type : '' );?>
                                                <select name="type" id="type" class="form-control" >
                                                    <option value="0">Please Select</option>
                                                    <option value="Booth Rental"<?if($type == "Booth Rental"){?> SELECTED<?}?>>Booth Rental</option>
                                                    <option value="Consultant"<?if($type == "Consultant"){?> SELECTED<?}?>>Consultant</option>
                                                    <option value="Salon Owner"<?if($type == "Salon Owner"){?> SELECTED<?}?>>Salon Owner</option>
                                                    <option value="School Owner"<?if($type == "School Owner"){?> SELECTED<?}?>>School Owner</option>
                                                    <option value="Stylist"<?if($type == "Stylist"){?> SELECTED<?}?>>Stylist</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4 control-label" for="nickname">Site Name</label>
                                            <div class="col-md-8 form-inline">
                                                <input type="text" class="form-control" name="nickname" id="nickname" maxlength="20" value="<?php echo ( !empty($nicknamePosted) ? $nicknamePosted : (!empty($nickname) ? $nickname : '' ) ); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                $i=1;

                                foreach($addresses as $akey => $address) {
                                    $street1_orig = 'entry_street_address';
                                    $street2_orig = 'entry_street_address2';
                                    $post_orig = 'entry_postcode';
                                    $city_orig = 'entry_city';
                                    $state_orig = 'entry_zone_id';

                                    $street1 = $street1_orig . '_' . $i;
                                    $street2 = $street2_orig . '_' . $i;
                                    $post = $post_orig . '_' . $i;
                                    $city = $city_orig . '_' . $i;
                                    $state = $state_orig . '_' . $i;

                                    $query="SELECT * FROM address_book WHERE customers_id='$customers_id' AND address_book_id='$i'";
                                    $result=mysqli_query($conn,$query) or die(mysqli_error($conn));
                                    $rows=mysqli_fetch_array($result);

                                    //debug(false, true, $rows);

                                    ?>

                                    <div class="<?php echo $akey; ?>_fields_section">
                                        <div class="form-group heading text-center no-border-bottom">
                                            <p><?php echo $address['name']; ?></p>
                                            <?php if( $i > 1 ): ?>
                                                <!--<p>
                                                    (<input type="Checkbox" id="" onclick="<?php /*echo $address['jsMethod']; */?>(this);"><label for="copy_address_sh">same as contact Billing Info</label>)
                                                </p>-->
                                            <?php endif; ?>
                                        </div>
                                        <div class="forms-group <?php echo $akey; ?>_fields">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label" for="entry_street_address_<?php echo $i; ?>">Address</label>
                                                <div class="col-md-8">
                                                    <div class="row form-group">
                                                        <div class="col-lg-6">
                                                            <input type="text" class="form-control" id="<?php echo $street1; ?>" name="<?php echo $street1; ?>" maxlength="30" placeholder="Street Address 1" value="<?php echo ( !empty($rows[$street1_orig]) ? $rows[$street1_orig] : '' ); ?>">
                                                        </div>
                                                        <div class="mb-md hidden-lg hidden-xl"></div>
                                                        <div class="col-lg-6">
                                                            <input type="text" class="form-control" id="<?php echo $street2; ?>" name="<?php echo $street2; ?>" maxlength="40" placeholder="Street Address 2" value="<?php echo ( !empty($rows[$street2_orig]) ? $rows[$street2_orig] : '' ); ?>">
                                                        </div>
                                                        <div class="mb-md hidden-lg hidden-xl"></div>
                                                        <div class="col-lg-4">
                                                            <input type="text" class="form-control" id="<?php echo $post; ?>" name="<?php echo $post; ?>" maxlength="20" placeholder="Post Code" value="<?php echo ( !empty($rows[$post_orig]) ? $rows[$post_orig] : '' ); ?>">
                                                        </div>
                                                        <div class="mb-md hidden-lg hidden-xl"></div>
                                                        <div class="col-lg-4">
                                                            <input type="text" class="form-control" id="<?php echo $city; ?>" name="<?php echo $city; ?>" maxlength="20" placeholder="City" value="<?php echo ( !empty($rows[$city_orig]) ? $rows[$city_orig] : '' ); ?>">
                                                        </div>
                                                        <div class="mb-md hidden-lg hidden-xl"></div>
                                                        <div class="col-lg-4">
                                                            <select name="<?php echo $state; ?>" class="form-control">
                                                                <option value="0" >Select State</option>
                                                                <?php
                                                                $rs_zone = mysqli_query($conn,"select z.zone_id, z.zone_name from zones z, countries c  where z.zone_country_id=c.countries_id and c.countries_iso_code_3 ='USA' order by zone_name");
                                                                while(list($i_zoneid, $s_zone)= mysqli_fetch_row($rs_zone)){
                                                                    if( !empty($rows[$state_orig]) && ($i_zoneid == $rows[$state_orig]) )
                                                                        echo '<option value ='.$i_zoneid.' selected>'.$s_zone.'</option>';
                                                                    else
                                                                        echo '<option value ='.$i_zoneid.' >'.$s_zone.'</option>';
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                    $i++;
                                }
                                ?>

                                <div class="check_address_fields_section">
                                    <div class="form-group heading text-center no-border-bottom"><p>Password Information</p></div>
                                    <div class="forms-group shipping_info_fields ">
                                        <div class="form-group form-inline ">
                                            <label class="col-md-4 control-label" for="customers_password">Password</label>
                                            <div class="col-md-8">
                                                <input type="password" class="form-control" name="customers_password" id="customers_password" maxlength="20" value="">
                                            </div>
                                        </div>
                                        <div class="form-group form-inline ">
                                            <label class="col-md-4 control-label" for="confirmpass">Confirm Password</label>
                                            <div class="col-md-8">
                                                <input type="password" class="form-control" name="confirmpass" id="confirmpass" maxlength="20" value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <footer class="panel-footer">
                                <div class="row">
                                    <div class="col-sm-9 centering text-center">
                                        <input type ="hidden" name="mem" value="<?php echo $member_id; ?>">
                                        <input type ="hidden" name="goto" value="update">
                                        <button type="Submit" name="update" value="Update Profile" class="command  btn btn-default btn-success">Update</button>
                                        <button id="reset" type="reset" name="reset" value="Reset" class="command btn btn-default btn-warning">Reset</button>
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </div>
                <div class="clearfix"></div>
            </section>
        </div>
    </div>



<?php
require_once("templates/footer.php");