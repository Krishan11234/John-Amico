<?php
require_once("session_check.inc");
require_once("../common_files/include/global.inc");
require_once("functions.php");
require_once '../common_files/Constant_contact/class.cc.php';

//debug(false, true, $_POST);

/*function const_contact_create($email, $first_name, $last_name) {


    // Set your Constant Contact account username and password below
    $cc = new cc('amicojohn', 'hair1234care');
    $contact_list = 6;
    $extra_fields = array(
        'FirstName' => $first_name,
        'LastName' => $last_name
    );

    // create the contact
    $cc->create_contact($email, $contact_list, $extra_fields);


}


function const_contact_update($id, $email, $first_name, $last_name) {


    $cc = new cc('amicojohn', 'hair1234care');
    $contact_list = 6;
    $extra_fields = array(
        'FirstName' => $first_name,
        'LastName' => $last_name
    );
    $cc->set_action_type('contact');
    $cc->update_contact($id, $email, $contact_list, $extra_fields);

//	echo '<p>' . $cc->http_response_info['http_code'] . '</p>';


}*/

if (!$mtype) {
    $mtype = 'm';
}


if (!$ec_member_id) {
    $ec_member_id = '0';
};

if (!$new_ec_member_id) {
    $new_ec_member_id = '0';
};


//reverse order
if (trim($stuff2_new) != "") {
    $id_1 = $ec_member_id;
    $id_2 = $new_ec_member_id;

    $ec_member_id = $id_2;
    $new_ec_member_id = $id_1;
}
else {
    $new_ec_member_id = "";
}

$mobileAppSubscriptionEnabled = true;
if($mobileAppSubscriptionEnabled) {
    require_once '../common_files/include/library/appnotch_api.php';
    if(class_exists('Appnotch_User')) {
        $mobileAppSubscriptionEnabled = true;
    } else {
        $mobileAppSubscriptionEnabled = false;
    }
}


add_NoPurchaseRequired_column();

if($mobileAppSubscriptionEnabled) {
    add_JAMobileAppEnable_column();
}

function add_NoPurchaseRequired_column() {
    global $conn;

    $columnCheckSql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".DB."' AND TABLE_NAME = 'tbl_member' AND COLUMN_NAME = 'bit_no_purchase_required'";
    $query = mysqli_query($conn, $columnCheckSql);

    if( mysqli_num_rows($query) < 1 ) {
        $alterSql = " ALTER TABLE `tbl_member` ADD `bit_no_purchase_required` int(1) NOT NULL DEFAULT '0' AFTER `nickname`; ";
        $query = mysqli_query($conn, $alterSql);

        if( $query ) {
            return true;
        }
    } else {
        return true;
    }
    return false;
}
function add_JAMobileAppEnable_column() {
    global $conn;

    $columnCheckSql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".DB."' AND TABLE_NAME = 'tbl_member' AND COLUMN_NAME = 'bit_ja_mobileapp_active'";
    $query = mysqli_query($conn, $columnCheckSql);

    if( mysqli_num_rows($query) < 1 ) {
        $alterSql = " ALTER TABLE `tbl_member` ADD `bit_ja_mobileapp_active` int(11) NOT NULL DEFAULT '0' AFTER `bit_no_purchase_required`; ";
        $query = mysqli_query($conn, $alterSql);
    } else {
        return true;
    }

    return false;
}


function rebuild_contact_lists($parent_id, $old_parent) {
    global $conn;


    $skippedIds = get_skipable_member_ids();

    if( !in_array($parent_id, $skippedIds) ) {
        $member_list = get_contact_list($parent_id);

        $sql = "UPDATE tbl_member_contact_list SET str_member_contact_list='" . $member_list . "' WHERE int_member_id = '" . $parent_id . "'";
        mysqli_query($conn, $sql);
    }

    if( !in_array($old_parent, $skippedIds) ) {
        $old_parent_member_list = get_contact_list($old_parent);

        $sql = "UPDATE tbl_member_contact_list SET str_member_contact_list='" . $old_parent_member_list . "' WHERE int_member_id = '" . $old_parent . "'";
        mysqli_query($conn, $sql);
    }
}

function rebuild_contact_lists_for_skipped_id() {
    global $conn;


    $skippedIds = get_skipable_member_ids();

    if( !empty($skippedIds) ) {
        foreach($skippedIds as $member_id) {
            $member_list = get_contact_list($member_id);

            $sql = "UPDATE tbl_member_contact_list SET str_member_contact_list='" . $member_list . "' WHERE int_member_id = '" . $member_id . "'";
            mysqli_query($conn, $sql);
        }
    }
}

function addcontactlist($memberid, $addid) {
    global $conn;

    $rscontact = mysqli_query($conn, "select str_member_contact_list from tbl_member_contact_list where int_member_id=$memberid");
    list($contactlist) = mysqli_fetch_row($rscontact);
    $contactlist = $contactlist . $addid . ',';
    $sql = "update tbl_member_contact_list set str_member_contact_list='" . $contactlist . "' where int_member_id=$memberid";
    $result = mysql_db_query(DB, $sql, CONN);

    $rsparent = mysqli_query($conn, "select int_parent_id from tbl_member where int_member_id=$memberid");
    while (list($parentmemberid) = mysqli_fetch_row($rsparent)) {
        if ($parentmemberid != 0) {
            addcontactlist($parentmemberid, $addid);
        }
    }
}

function addcontactlist2($memberid, $addid) {
    global $conn;

    do {
        $sql = "UPDATE tbl_member_contact_list SET str_member_contact_list=CONCAT(str_member_contact_list, '" . $addid . ",') WHERE int_member_id = '$memberid'";
        $result = mysqli_query($conn, $sql);
        echo mysql_error();

        //if ($memberid==1) break;

        $sql = "SELECT int_parent_id FROM tbl_member WHERE int_member_id = '$memberid'";
        $result = mysqli_query($conn, $sql);
        echo mysql_error();
        $memberid = mysqli_result($result, 0);


    } while ($memberid != 0 && $memberid != 1);

    return;
}

function createcontactlist($memberid) {
    global $conn;

    if( empty($memberid) ) {
        return false;
    }

    $check_sql = "SELECT str_member_contact_list FROM tbl_member_contact_list WHERE int_member_id = '$memberid' ";
    $check_result = mysqli_query($conn, $check_sql);

    if( mysqli_num_rows($check_result) < 1 ) {
        $sql = "INSERT INTO tbl_member_contact_list (`str_member_contact_list`, `int_member_id`) VALUES ('', $memberid) ";
        $result = mysqli_query($conn, $sql);
        echo mysql_error();

        if ($result) {
            return mysqli_insert_id($conn);
        }
    }

    return;
}

function rebuild_affected_contact_lists($old_parent, $new_parent, $member) {
    global $conn;

    $old_parent_ancestry = array();
    $new_parent_ancestry = array();

    $old_parent_ptr = $old_parent;
    $new_parent_ptr = $new_parent;

    do {
        $old_parent_ancestry[] = $old_parent_ptr;

        $sql = "SELECT int_parent_id FROM tbl_member WHERE int_member_id = '$old_parent_ptr'";
        $result = mysqli_query($conn, $sql);
        echo mysql_error();
        $old_parent_ptr = mysqli_result($result, 0);
    } while ($old_parent_ptr != 0);

    do {
        $new_parent_ancestry[] = $new_parent_ptr;

        $sql = "SELECT int_parent_id FROM tbl_member WHERE int_member_id = '$new_parent_ptr'";
        $result = mysqli_query($conn, $sql);
        echo mysql_error();
        $new_parent_ptr = mysqli_result($result, 0);
    } while ($new_parent_ptr != 0);

    foreach ($old_parent_ancestry as $old_ancestor) {
        if (in_array($old_ancestor, $new_parent_ancestry)) {
            $common_ancestor = $old_ancestor;
            break;
        }
    }

    $old_parent_ptr = $old_parent;
    $new_parent_ptr = $new_parent;

    //echo "AAA"; exit;

    if ($common_ancestor != $old_parent) {
        do {
            //echo "<br><br>OLD".
            $sql = "UPDATE tbl_member_contact_list SET str_member_contact_list='" . get_contact_list($old_parent_ptr) . "' WHERE int_member_id = '$old_parent_ptr'";
            mysqli_query($conn, $sql);
            echo mysql_error();

            $sql = "SELECT int_parent_id FROM tbl_member WHERE int_member_id = '$old_parent_ptr'";
            $result = mysqli_query($conn, $sql);
            echo mysql_error();
            $old_parent_ptr = @mysqli_result($result, 0);
        } while ($old_parent_ptr != $common_ancestor);
    }

    if ($common_ancestor != $new_parent) {
        $sql = "SELECT str_member_contact_list FROM tbl_member_contact_list WHERE int_member_id = '$member'";
        $result = mysqli_query($conn, $sql);
        echo mysql_error();
        $member_list = $member . "," . mysqli_result($result, 0);

        do {
            //echo "<br><br>NEW".
            $sql = "UPDATE tbl_member_contact_list SET str_member_contact_list=CONCAT(str_member_contact_list, '" . $member_list . "') WHERE int_member_id = '$new_parent_ptr'";
            mysqli_query($conn, $sql);
            echo mysql_error();

            $sql = "SELECT int_parent_id FROM tbl_member WHERE int_member_id = '$new_parent_ptr'";
            $result = mysqli_query($conn, $sql);
            echo mysql_error();
            $new_parent_ptr = @mysqli_result($result, 0);
        } while ($new_parent_ptr != $common_ancestor);
    }

    return;
}






if ( !empty($_POST['goto']) && in_array($_POST['goto'], array('add', 'update')) && ( !empty($_POST['addUser']) || !empty($_POST['updateUser']) ) ) {

    $is_add = ( $_POST['goto'] == 'add' ) ? true : false;
    $is_edit = ( $_POST['goto'] == 'update' ) ? true : false;

    //echo '<pre>'; var_dump($is_add); die();

    if( $is_edit ) {
        $edit_memberid = IntegerInputCleaner( $_POST['memberid']);
        $edit_customerid = IntegerInputCleaner( $_POST['customerid']);

        if( empty($edit_customerid) || validate_customerid($edit_customerid) ) {
            $error_messages['customerid'] = "No customer id found. Please refresh your page.";
        }

        if( !empty($edit_memberid) ) {
            $sql = "SELECT int_parent_id, ec_id, amico_id FROM tbl_member WHERE int_member_id = '{$edit_memberid}'";
            $result = mysqli_query($conn, $sql);
            if( mysqli_num_rows($result) < 1 ) {
                $error_messages['memberid'] = "No member id found. Please refresh your page.";
            } else {
                $f_ec = mysqli_fetch_array($result);
                $old_parent = $f_ec['int_parent_id'];
            }

        } else {
            $error_messages['memberid'] = "No member id found. Please refresh your page.";
        }
    }

    if( in_array($mtype, array('m', 'a') ) )
    {
        $referring_memberid = StringInputCleaner($_POST['stuff']);
        $refer_member_id = validate_amico_member($referring_memberid);
        //echo '<pre>'; var_dump($refer_member_id, $referring_memberid, validate_amico_member($referring_memberid)); die();
        if (empty($referring_memberid) || empty($referring_memberid) ) {
            $error_messages['stuff'] = "Please enter a Valid Referring Member ID";
        }

        $ec_id = IntegerInputCleaner($_POST['stuff2']);
        $ec_id = empty($ec_id) ? 0 : validate_ec_id($ec_id);
        $ec_member_id = $ec_id;
        if ( ($ec_member_id === false) ) {
            $error_messages['stuff2'] = "Please enter a Valid EC ID";
        }

        $new_ec_id = IntegerInputCleaner($_POST['stuff2_new']);
        $new_ec_id = empty($new_ec_id) ? 0 : validate_ec_id($new_ec_id);
        $new_ec_member_id = $new_ec_id;
        if ( ($new_ec_member_id === false) ) {
            $error_messages['stuff2_new'] = "Please enter a Valid New EC ID";
        }

        if( !empty($new_ec_member_id) ) {
            $ec_member_id = $new_ec_member_id;
            $new_ec_member_id = $new_ec_id;
        }

        $nickname = StringInputCleaner($_POST['nickname']);
        $change_ec = '';

        if (!ctype_alnum($nickname)) {
            $error_messages['nickname'] = "Nickname can only contain Alpha-Numeric characters.";
        }
        $edit_nickname = get_amico_nickname_by_id($edit_memberid);
        if (($edit_nickname != $nickname)) {
            if (amico_nickname_exists($nickname)) {
                $error_messages['nickname'] = 'Nickname already exists. Please choose a different one.';
            }
        }

        if( $is_add ) {
            $amico_id = get_next_amico_id($mtype);
        }

    }
    elseif( $mtype == 'e' ) {
        $amico_id_xyz = !empty($_POST['amico_id_xyz']) ? StringInputCleaner($_POST['amico_id_xyz']) : 0;

        if( $is_add ) {
            if (empty($amico_id_xyz)) {
                $amico_id = get_next_amico_id();
            }
            else {
                $amico_id = $amico_id_xyz;

                if( !validate_new_amico_id_is_unique($amico_id) ) {
                    $error_messages['amico_id_xyz'] = "This Amico ID is already in use. Please choose a different one.";
                }
            }
        }

        $change_ec = (!empty($_POST['change_ec'])) ? 'Y' : 'N';
        $refer_member_id = 0;
    }
    elseif( $mtype == 'c' ) {
        if( $is_add ) {
            if (empty($amico_id_xyz)) {
                $amico_id = get_next_amico_id();
            }
            else {
                $amico_id = $amico_id_xyz;

                if (!validate_new_amico_id_is_unique($amico_id)) {
                    $error_messages['amico_id_xyz'] = "This Amico ID is already in use. Please choose a different one.";
                }
            }
        }
        $refer_member_id = 0;

        $growth = StringInputCleaner($_POST['growth']);
        $contest = StringInputCleaner($_POST['contest']);
        $miles = StringInputCleaner($_POST['miles']);

        $change_ec = '';

    }

    //echo '<pre>'; var_dump( $change_ec, $_POST ); die();


    $title = StringInputCleaner($_POST['title']);
    if( empty($title) ) {
        $error_messages['title'] = "Please your name Title";
    }
    $firstname = StringInputCleaner($_POST['firstname']);
    if( empty($firstname) ) {
        $error_messages['firstname'] = "Please enter your first name";
    }
    $lastname = StringInputCleaner($_POST['lastname']);
    if( empty($lastname) ) {
        $error_messages['lastname'] = "Please enter your last name";
    }
    if( !StringInputCleaner($_POST['email']) ) {
        $error_messages['email'] = "Please enter a valid email address";
    } else {
        $email = strtolower(trim($_POST['email']));
    }


    $zone = StringInputCleaner($_POST['zone']);
    if( empty($zone) ) {
        $error_messages['zone'] = "Billing State field cannot be empty";
    }
    $sh_zone = StringInputCleaner($_POST['sh_zone']);
    if( empty($sh_zone) ) {
        $error_messages['sh_zone'] = "Shipping State field cannot be empty";
    }
    $check_zone= StringInputCleaner($_POST['check_zone']);
    if( ($mtype != 'c') &&  empty($check_zone) ) {
        $error_messages['check_zone'] = "Check State field cannot be empty";
    }

    $postcode = StringInputCleaner($_POST['postcode']);
    if( empty($postcode) ) {
        $error_messages['postcode'] = "Zip code field cannot be empty";
    } else {
        /*if( !validate_zip_code($postcode) ) {
            $error_messages['postcode'] = "Please enter a valid zip code";
        }*/
    }

    $noPurchaseRequired_enabled = !empty($_POST['bit_no_purchase_required']) ? 1 : 0;
    $bit_ja_mobileapp_active = !empty($_POST['bit_ja_mobileapp_active']) ? 1 : 0;

    $commission_enabled = !empty($_POST['bit_custom_comission']) ? 1 : 0;
    if ($commission_enabled) {
        $commission_expire = StringInputCleaner($_POST['expire_custom_comission']);

        $exp_date = strtotime($commission_expire);

        if (($exp_date <= time())) {
            $error_messages['expire_custom_comission'] = "Please enter a future date";
        } else {
            $exp_date = date( 'Y-m-d', $exp_date );
        }

        //echo '<pre>'; var_dump( $_POST['expire_custom_comission'], $commission_expire, $exp_date ); die();
    }

    if( !empty($_POST['pass']) && !empty($_POST['confirmpass']) ) {
        $password = $_POST['pass'];
        $password2 = $_POST['confirmpass'];

        if ($password != $password2) {
            $error_messages['pass'] = "Your password did not match";
        }
    } else {
        if( $is_add ) {
            $error_messages['pass'] = "Please enter password for this member";
        }
    }

    //W01012817
    //echo '<pre>'; print_r(array($_POST, $error_messages)); echo '</pre>'; die();


    if( !empty($error_messages) ) {
        $_SESSION['member_form_errors'] = $error_messages;
        //redirectToMainPage($mtype);
    }

    if( empty($error_messages) ) {

        //$referring_memberid = filter_var($_POST['refer_member_id'], FILTER_SANITIZE_NUMBER_INT);

        $phone = StringInputCleaner($_POST['phone']);
        $phone1 = StringInputCleaner($_POST['phone1']);
        $phone2 = StringInputCleaner($_POST['phone2']);
        $mobile_phone = StringInputCleaner($_POST['mobile_phone']);
        $fax = StringInputCleaner($_POST['fax']);
        $ssn = StringInputCleaner($_POST['ssn']);
        $license_number = StringInputCleaner($_POST['license_number']);
        $type = StringInputCleaner($_POST['type']);
        $operator = StringInputCleaner($_POST['operator']);

        $company = '';

        $streetadd = StringInputCleaner($_POST['streetadd']);
        $streetadd_two = StringInputCleaner($_POST['streetadd_two']);
        $city = StringInputCleaner($_POST['city']);
        $state = StringInputCleaner($_POST['state']);

        $country = '223'; // US

        $sh_firstname = StringInputCleaner($_POST['sh_firstname']);
        $sh_lastname = StringInputCleaner($_POST['sh_lastname']);
        $sh_streetadd = StringInputCleaner($_POST['sh_streetadd']);
        $sh_streetadd_two = StringInputCleaner($_POST['sh_streetadd_two']);
        $sh_postcode = StringInputCleaner($_POST['sh_postcode']);
        $sh_city = StringInputCleaner($_POST['sh_city']);
        $sh_state = StringInputCleaner($_POST['sh_state']);
        $sh_country = '223'; // US

        $check_firstname = StringInputCleaner($_POST['check_firstname']);
        $check_lastname = StringInputCleaner($_POST['check_lastname']);
        $check_streetadd = StringInputCleaner($_POST['check_streetadd']);
        $check_streetadd_two = StringInputCleaner($_POST['check_streetadd_two']);
        $check_postcode = StringInputCleaner($_POST['check_postcode']);
        $check_city = StringInputCleaner($_POST['check_city']);
        $check_state = StringInputCleaner($_POST['check_state']);
        $check_country = '223'; // US
    }

    if( empty($error_messages) ) {

        $miles = !empty($miles) ? $miles : 0;
        $change_ec = in_array(strtoupper($change_ec), array('Y', 'N')) ? $change_ec : 'N';
        $exported = in_array(strtoupper($exported), array('Y', 'N')) ? $exported : 'N';

        $today = date("Y/m/d");

        if( $is_edit ) {


            if( !empty($edit_memberid) ) {

                //echo '<pre>'; var_dump($refer_member_id, $old_parent); die();

                if (!empty($f_ec['ec_id'])) {
                    if ($f_ec['ec_id'] != $stuff2) {
                        $ec_query = mysqli_query($conn, "INSERT INTO `tbl_member_ec` (`amico_id`, `ec_id`, `event`, `timestamp`) VALUES ('" . $f_ec['amico_id'] . "', '" . $f_ec['ec_id'] . "', 'removed', '" . date("Y-m-d H:i:s", mktime()) . "')") or die (mysqli_error($conn));
                        $ec_query = mysqli_query($conn, "INSERT INTO `tbl_member_ec` (`amico_id`, `ec_id`, `event`, `timestamp`) VALUES ('" . $f_ec['amico_id'] . "', '$ec_member_id', 'added', '" . date("Y-m-d H:i:s", mktime()) . "')") or die (mysqli_error($conn));
                    }
                }

                $table = "tbl_member";
                $fieldlist = "ec_id='$ec_member_id',new_ec_id='$new_ec_member_id',contest='$contest',growth='$growth',str_title='$title',int_parent_id='$refer_member_id',miles='$miles',nickname = '$nickname',bit_custom_comission=$commission_enabled,expire_custom_comission='$exp_date'";
                $fieldlist .= ",bit_no_purchase_required=$noPurchaseRequired_enabled";
                $condition = " where int_member_id ='$edit_memberid'";
                $result = update_rows($conn, $table, $fieldlist, $condition);


                if (!empty($edit_customerid)) {
                    if ($mtype == 'e') {
                        $table = "customers";
                        $fieldlist = "customers_telephone1='{$phone1}', customers_telephone2='{$phone2}'";
                        $condition = " where customers_id = '{$edit_customerid}'";
                        $result = update_rows($conn, $table, $fieldlist, $condition);
                    }

                    $table = "customers";
                    $fieldlist = "customers_firstname='$firstname', customers_lastname='$lastname', customers_email_address='$email', customers_telephone='$phone', customers_fax='$fax', ssn='$ssn', license_number='$license_number', type='$type', exported='N', change_ec='$change_ec', mobile_phone='$mobile_phone', operator_id='$operator'";
                    if( !empty($password) ) {
                        $fieldlist .= ", customers_password='$password' ";
                    }
                    $condition = " where customers_id = '$edit_customerid'";
                    $result = update_rows($conn, $table, $fieldlist, $condition);


                    $table = "address_book";
                    $fieldlist = "entry_company='$company', entry_firstname='$firstname', entry_lastname='$lastname', entry_street_address='$streetadd',entry_street_address2='$streetadd_two', entry_postcode='$postcode', entry_city='$city', entry_country_id='$country', entry_zone_id='$zone'";
                    $condition = " where customers_id = '$edit_customerid' and address_book_id=1";
                    $result = update_rows($conn, $table, $fieldlist, $condition);

                    if ($shiped == 1) {
                        $table = "address_book";
                        $fieldlist = "entry_company='$company', entry_firstname='$firstname', entry_lastname='$lastname', entry_street_address='$streetadd',entry_street_address2='$streetadd_two', entry_postcode='$postcode', entry_city='$city', entry_country_id='$country', entry_zone_id='$zone'";
                        $condition = " where customers_id = '$edit_customerid' and address_book_id=2";
                        $result = update_rows($conn, $table, $fieldlist, $condition);
                    }
                    else {
                        $table = "address_book";
                        $fieldlist = "entry_company='$company', entry_firstname='$sh_firstname', entry_lastname='$sh_lastname', entry_street_address='$sh_streetadd', entry_street_address2='$sh_streetadd_two',entry_postcode='$sh_postcode', entry_city='$sh_city', entry_country_id='$sh_country', entry_zone_id='$sh_zone'";
                        $condition = " where customers_id = '$edit_customerid' and address_book_id=2";
                        $result = update_rows($conn, $table, $fieldlist, $condition);
                    }

                    $table = "address_book";
                    $fieldlist = "entry_company='$company', entry_firstname='$check_firstname', entry_lastname='$check_lastname', entry_street_address='$check_streetadd', entry_street_address2='$check_streetadd_two',entry_postcode='$check_postcode', entry_city='$check_city', entry_country_id='$check_country', entry_zone_id='$check_zone'";
                    $condition = " where customers_id = '$edit_customerid' and address_book_id=3";
                    $result = update_rows($conn, $table, $fieldlist, $condition);
                }

                //if ( isset($_POST['chapt']) && !empty($old_parent)) {
                if ( !empty($refer_member_id) && !empty($old_parent) && ($refer_member_id != $old_parent) ) {
                    rebuild_contact_lists($refer_member_id, $old_parent);
                }

                $success_message = "Successfully Updated Information.";

            }
        }

        if( $is_add ) {
            /**
             * To solve regarding the error message "Enter a Valid EC ID"
             * when trying to assign a professional under a newly created "EC".
             */
            if (!$ec_member_id) {
                if(!empty($_POST['amico_id_xyz']) && !empty($mtype) && $mtype=="e") {
                    $ec_member_id__only_for_adding_ec = IntegerInputCleaner($_POST['amico_id_xyz']);
                    if(!empty($ec_member_id__only_for_adding_ec)) {
                        $ec_member_id = $ec_member_id__only_for_adding_ec;
                        unset($ec_member_id__only_for_adding_ec);
                    }
                }
            }
            /* End of the new code */

            $query_i = "INSERT INTO tbl_member (int_parent_id,int_designation_id,amico_id,str_title,dat_last_visit,bit_active, mtype, ec_id, new_ec_id, growth, contest, reg_date,miles, nickname, bit_no_purchase_required, bit_custom_comission, expire_custom_comission) VALUES ('$refer_member_id','1','$amico_id','{$title}','$today','1', '$mtype', '$ec_member_id', '$new_ec_member_id', '$growth', '$contest', '" . date("Y-m-d H:i:s") . "','$miles', '{$nickname}', $noPurchaseRequired_enabled,$commission_enabled, '$exp_date')";
            $result = mysqli_query($conn, $query_i);
            $member_id = mysqli_insert_id($conn);


            $rs = mysqli_query($conn, "select customers_email_address from customers where customers_email_address='" . $email . "'");
            $no_rows = mysqli_num_rows($rs);


            $table = "customers";                // inserting values to setting table
            $in_fieldlist = "customers_firstname,customers_lastname,customers_email_address,customers_telephone,mobile_phone,customers_fax,customers_password,ssn,license_number,type, exported, change_ec";
            $in_values = "'{$firstname}','{$lastname}','{$email}','{$phone}','{$mobile_phone}','{$fax}','{$password}','{$ssn}','{$license_number}','{$type}', 'N', '{$change_ec}'";
            $result = insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values
            $customer_id = mysqli_insert_id($conn);//id of the customer inserted

            if ($mtype == 'e') {
                if (!empty($customer_id)) {
                    $table = "customers";
                    $fieldlist = "customers_telephone1='{$phone1}', customers_telephone2='{$phone2}'";
                    $condition = " where customers_id = " . $customer_id;
                    $result = update_rows($conn, $table, $fieldlist, $condition);
                }
            }

            if (!empty($customer_id)) {
                //inserting values into address_book contact billing info by putting '1' in address_book_id
                $table = "address_book";                // inserting values to setting table
                $in_fieldlist = "customers_id,address_book_id,entry_company,entry_firstname,entry_lastname,entry_street_address,entry_street_address2,entry_postcode,entry_city,entry_state,entry_country_id,entry_zone_id";
                $in_values = "'$customer_id',1,'{$company}','{$firstname}', '{$lastname}', '{$streetadd}','{$streetadd_two}','{$postcode}','{$city}','{$state}','{$country}','{$zone}'";
                $result = insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values


                //inserting values into address_book contact billing info by putting '2' in address_book_id
                if ($_POST['shiped'] == 1) {
                    $table = "address_book";                // inserting values to setting table
                    $in_fieldlist = "customers_id,address_book_id,entry_company,entry_firstname,entry_lastname,entry_street_address,entry_street_address2,entry_postcode,entry_city,entry_state,entry_country_id,entry_zone_id";
                    $in_values = "'$customer_id',2,'{$company}','{$firstname}', '{$lastname}', '{$streetadd}','{$streetadd_two}','{$postcode}','{$city}','{$state}','{$country}','{$zone}'";
                    $result = insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values
                }
                else {
                    $table = "address_book";                // inserting values to setting table
                    $in_fieldlist = "customers_id,address_book_id,entry_company,entry_firstname,entry_lastname,entry_street_address,entry_street_address2,entry_postcode,entry_city,entry_state,entry_country_id,entry_zone_id";
                    $in_values = "'$customer_id',2,'{$company}','{$sh_firstname}','{$sh_lastname}', '{$sh_streetadd}','{$sh_streetadd_two}', '{$sh_postcode}','{$sh_city}','{$sh_state}','{$sh_country}','{$sh_zone}'";
                    $result = insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values
                }


                //inserting values into address_book contact check info by putting '3' in address_book_id
                $table = "address_book";                // inserting values to setting table
                $in_fieldlist = "customers_id,address_book_id,entry_company,entry_firstname,entry_lastname,entry_street_address,entry_street_address2,entry_postcode,entry_city,entry_state,entry_country_id,entry_zone_id";
                $in_values = "'$customer_id',3,'{$company}','{$check_firstname}','{$check_lastname}', '{$check_streetadd}','{$check_streetadd_two}', '{$check_postcode}','{$check_city}','{$check_state}','{$check_country}','{$check_zone}'";
                $result = insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values
            }

            if (!empty($member_id)) {
                //inserting values into tbl_member_contact_list
                $table = "tbl_member_contact_list";                // inserting values to setting table
                $in_fieldlist = "int_member_id,str_member_contact_list,bit_active";
                $in_values = "'$member_id','',1";
                $result = insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values

                //updating latest tbl_member id with the latest customers id
                $table = "tbl_member";
                $fieldlist = "int_customer_id='$customer_id'";
                $condition = " where int_member_id = $member_id";
                $result = update_rows($conn, $table, $fieldlist, $condition);

                //inserting values into the buliting board table
                $table = "forum_users";                // inserting values to setting table
                $in_fieldlist = "username,username_clean,user_password,user_email, user_regdate";
                $the_date = getdate();
                $in_values = "$member_id,$member_id,'password','$email','$the_date'";
                $result = insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values
                //end inserting values into the bulliting board table

                // Create a row to the ContactList table for future use
                createcontactlist($member_id);
            }

            //inserting value to the history of EC IDs
            $ec_query = mysqli_query($conn, "INSERT INTO `tbl_member_ec` (`amico_id`, `ec_id`, `event`, `timestamp`) VALUES ('$amico_id', '$ec_member_id', 'added', '" . date("Y-m-d H:i:s") . "')") or die (mysqli_error($conn));


            if ($refer_member_id != 0) {
                addcontactlist2($refer_member_id, $member_id);
                //addcontactlist($refer_member_id,$member_id);
            }

            //sending mail
            $name = $firstname . ' ' . $lastname;

            $subject = "Welcome to JohnAmico.com!";


            // NEW MAILING CODE

            $to = $email;

            $message = "Welcome to JohnAmico.com!   <BR>";
            $message .= $name . '  Your Details Registered Successfully';

            $headers = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'From: Sender <johnamico@johnamico.com>' . "\r\n";
            mail($to, $subject, $message, $headers);

            $success_message = "Successfully Inserted Information.";
        }

        if( $is_edit ) {
            $customer_id = $edit_customerid;
        }

        if( !empty($success_message) ) {
            $_SESSION['member_form_success'][] = $success_message;
            //redirectToMainPage($mtype);
        }

        make_customer_file($customer_id);

    }

    if( empty($error_messages) ) {
        if (!empty($email) && !empty($firstname)) {
            $cc_id = IntegerInputCleaner($_POST['cc_id']);
            $cc_id = empty($cc_id) ? 0 : $cc_id;

            if (empty($cc_id)) {
                const_contact_create($email, $firstname, $lastname);
            }
            else {
                const_contact_update($cc_id, $email, $firstname, $lastname);
            }
        }

        if ($mtype == 'c') {
            if (trim($memberid) != '') {
                $query = mysqli_query($conn, "SELECT int_customer_id FROM tbl_member WHERE int_member_id='$memberid'") or die (mysql_error());
                $f = mysqli_fetch_array($query);
                $cid = $f['int_customer_id'];
            }

            if (trim($amico_id_xyz) != '') {
                $query = mysqli_query($conn, "SELECT int_customer_id FROM tbl_member WHERE amico_id='$amico_id_xyz'") or die (mysql_error());
                $f = mysqli_fetch_array($query);
                $cid = $f['int_customer_id'];
            }

            if( !empty($cid) ) {
                members_to_chapter($cid);
            }

        }
    }

    if(empty($error_messages) && $mobileAppSubscriptionEnabled) {

        $appnotchClass = new Appnotch_User(DB, $conn);

        $query = mysqli_query($conn, "SELECT bit_ja_mobileapp_active, ja_mobileapp_user_id FROM tbl_member WHERE int_member_id='$memberid'") or die (mysqli_error($conn));
        $f = mysqli_fetch_array($query);
        $mobileAppUserActive = $f['bit_ja_mobileapp_active'];
        $mobileAppUserId = $f['ja_mobileapp_user_id'];


        $contactDetails = array(
            'ref_user_type' => 'professional_member',
            'ref_user_id' => $memberid,
            "email" => $email,
            "password" => $password,
            "name" => "{$firstname} {$lastname}",
            "phoneNumber" => (!empty($phone) ? $phone : '' ),
            "url" => base_url() . "/" . ( ctype_alnum($nickname) ? $nickname : '' ),
            "firstName" => $firstname,
            "lastName" => $lastname,
            "tag" => $nickname,
        );

        // Enable Mobile App now and Register it.
        if( empty($mobileAppUserActive) && !empty($bit_ja_mobileapp_active) ) {
            if( empty($mobileAppUserId) ) {
                // Appnotch API Call to create and enable
                $assignedId = $appnotchClass->createTenantMemberAndCreateTenantAndAssign($contactDetails);
                if($assignedId) {
                    $table = "tbl_member";
                    $fieldlist = "bit_ja_mobileapp_active=1, ja_mobileapp_user_id='{$assignedId}'";
                    $condition = " where int_member_id ='" . $memberid . "'";
                    $result = update_rows($conn, $table, $fieldlist, $condition);

                    $_SESSION['member_form_success'][] = "The JA Mobile App is enabled for this member";
                }
                else {
                    $_SESSION['member_form_errors'][] = "Couldn't enable The JA Mobile App ";
                }
            } else {

                $table = "tbl_member";
                $fieldlist = "bit_ja_mobileapp_active=1";
                $condition = " where int_member_id ='" . $memberid . "'";
                $result = update_rows($conn, $table, $fieldlist, $condition);

                /*$tenant = $appnotchClass->checkAppnotchUserExistsInDb($contactDetails['email'], 'email', true);
                if(!empty($tenant['tenant_id'])) {
                    $enabled = $appnotchClass->enableTenant($tenant['tenant_id']);
                    if($enabled) {
                        $table = "tbl_member";
                        $fieldlist = "bit_ja_mobileapp_active=1";
                        $condition = " where int_member_id ='" . $memberid . "'";
                        $result = update_rows($conn, $table, $fieldlist, $condition);

                        $_SESSION['member_form_success'][] = "The JA Mobile App is enabled for this member";
                    } else {
                        $_SESSION['member_form_errors'][] = "Couldn't enable The JA Mobile App ";
                    }
                } else {
                    $_SESSION['member_form_errors'][] = "Couldn't find The JA Mobile App ID for this member";
                }*/
            }
        }
        // Disable Mobile App now and Register it.
        elseif( !empty($mobileAppUserActive) && empty($bit_ja_mobileapp_active) ) {
            if( !empty($mobileAppUserId) ) {

                $table = "tbl_member";
                $fieldlist = "bit_ja_mobileapp_active=0";
                $condition = " where int_member_id ='" . $memberid . "'";
                $result = update_rows($conn, $table, $fieldlist, $condition);

                // Appnotch API Call to disable
                /*$tenant = $appnotchClass->checkAppnotchUserExistsInDb($contactDetails['email'], 'email', true);
                if(!empty($tenant['tenant_id'])) {
                    $enabled = $appnotchClass->disableTenant($tenant['tenant_id']);
                    //echo '<pre>'; var_dump($enabled, $tenant); die();
                    if($enabled) {


                        $_SESSION['member_form_success'][] = "The JA Mobile App is disabled for this member";
                    } else {
                        $_SESSION['member_form_errors'][] = "Couldn't disable The JA Mobile App ";
                    }
                } else {
                    $_SESSION['member_form_errors'][] = "Couldn't find The JA Mobile App ID for this member";
                }*/
            }
        }
        // Enabled Mobile App but no User ID from API. Register it.
        elseif( !empty($mobileAppUserActive) && !empty($bit_ja_mobileapp_active) ) {
            if( empty($mobileAppUserId) ) {
                // Appnotch API Call to create and enable
                $assignedId = $appnotchClass->createTenantMemberAndCreateTenantAndAssign($contactDetails);
                if($assignedId) {
                    $table = "tbl_member";
                    $fieldlist = "ja_mobileapp_user_id='{$assignedId}'";
                    $condition = " where int_member_id ='" . $memberid . "'";
                    $result = update_rows($conn, $table, $fieldlist, $condition);

                    $_SESSION['member_form_success'][] = "The JA Mobile App is enabled for this member";
                }
                else {
                    $_SESSION['member_form_errors'][] = "Couldn't enable The JA Mobile App ";
                }
            }
        }
    }


}
elseif (isset($_GET['delete'])) {
    $memberid = IntegerInputCleaner($_GET['memberid']);

    $rsparent = mysqli_query($conn, "select mtype, int_parent_id from tbl_member where int_member_id='$memberid'");
    list($mtype, $parentid) = mysqli_fetch_row($rsparent);

    $rschild = mysqli_query($conn, "select int_member_id from tbl_member where int_parent_id='$memberid'");
    while (list($childid) = mysqli_fetch_row($rschild)) {
        $table = "tbl_member";
        $fieldlist = "int_parent_id=" . $parentid;
        $condition = " where int_member_id = $childid";
        $result = update_rows($conn, $table, $fieldlist, $condition);
    }

    $table = "tbl_member";
    $condition = " where int_member_id = {$_GET['memberid']}";
    $result = del_rows($conn, $table, $condition);// function call to delete

    if($result) {
        /*if($mobileAppSubscriptionEnabled) {
            $query = mysqli_query($conn, "SELECT t.bit_ja_mobileapp_active, t.ja_mobileapp_user_id, au.tenantmember_id, au.tenant_id, au.tenantmember_email FROM tbl_member t INNER JOIN ".Appnotch_User::APPNOTCH_TABLE_NAME." au  ON au.ref_user_id=t.int_member_id WHERE t.int_member_id='$memberid' AND au.ref_user_type='profesisonal_member' ") or die (mysql_error());
            $f = mysqli_fetch_array($query);
            $mobileAppUserActive = $f['bit_ja_mobileapp_active'];
            $mobileAppUserId = $f['ja_mobileapp_user_id'];

            if( !empty($mobileAppUserId) && !empty($f['tenant_id']) && $f['tenantmember_id'] ) {
                // Delete Appnotch API Call
                $appnotchClass = new Appnotch_User(DB, $conn);

                $contactDetails = array(
                    'ref_user_type' => 'professional_member',
                    'ref_user_id' => $memberid,
                    "email" => $f['tenantmember_email'],
                    'tenant_id' => $f['tenant_id'],
                    'tenantmember_id' => $f['tenantmember_id'],
                );

                $appnotchClass = new Appnotch_User(DB, $conn);
                $appnotchClass->deleteTenantMemberAndDeleteTenant($contactDetails);
            }
        }*/
    }

    redirectToMainPage($mtype, $msg);
}
elseif (isset($_POST['activate'])) {
    if ($_POST['active'] == 1) {
        $_POST['active'] = 0;
    }
    else {
        $_POST['active'] = 1;
    }

    $table = "tbl_member";
    $fieldlist = "bit_active={$_POST['active']}";
    $condition = " where int_member_id={$_POST['memberid']}";
    $result = update_rows($conn, $table, $fieldlist, $condition); // function call to update

    redirectToMainPage($mtype, $msg);

}
else {
    //redirectToMainPage($mtype, $msg);
}


//header("location: mlm_pulldown.php?msg=Memember+was+added");

function redirectToMainPage($mtype='m', $mesg='') {
    $mesg = (!empty($mesg) ? $mesg : '');

    if ($mtype == 'm') {
        header("Location: members.php?mesg=$mesg&sort={$_REQUEST['sort']}&alpabet={$_REQUEST['alpabet']}&designations={$_REQUEST['designations']}&page={$_REQUEST['page']}");
    }
    elseif ($mtype == 'a') {
        header("Location: ambassadors.php?mesg=$mesg&sort={$_REQUEST['sort']}&alpabet={$_REQUEST['alpabet']}&designations={$_REQUEST['designations']}&page={$_REQUEST['page']}");
    }
    elseif ($mtype == 'e') {
        header("Location: ecs.php?mesg=$mesg&sort={$_REQUEST['sort']}&alpabet={$_REQUEST['alpabet']}&designations={$_REQUEST['designations']}&page={$_REQUEST['page']}");
    }
    elseif ($mtype == 'c') {
        header("Location: chap.php?mesg=$mesg&sort={$_REQUEST['sort']}&alpabet={$_REQUEST['alpabet']}&designations={$_REQUEST['designations']}&page={$_REQUEST['page']}");
    }
    else {
        header("Location: members.php?goto=add&mesg=$mesg&sort={$_REQUEST['sort']}&alpabet={$_REQUEST['alpabet']}&designations={$_REQUEST['designations']}&page={$_REQUEST['page']}");
    }
}
