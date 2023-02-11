<?php
$page_name = 'Manage New Stylists';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("functions.php");
require_once("templates/header.php");
require_once("templates/sidebar.php");


//if( is_in_live() ) { exit(''); }

function addcontactlist2($memberid, $addid) {
    global $conn;

    do {
        $sql = "UPDATE tbl_member_contact_list SET str_member_contact_list=CONCAT(str_member_contact_list, '" . $addid . ",') WHERE int_member_id = '$memberid'";
        $result = mysqli_query($conn, $sql);
        echo mysqli_error($conn);

        //if ($memberid==1) break;

        $sql = "SELECT int_parent_id FROM tbl_member WHERE int_member_id = '$memberid'";
        $result = mysqli_query($conn, $sql);
        echo mysqli_error($conn);
        $memberid = mysqli_result($result, 0);


    } while ($memberid != 0 && $memberid != 1);

    return;
}

$member_type_name = 'Member';
$member_type_name_plural = 'Members';
$self_page = 'new_stylists.php';
$page_url = base_admin_url() . '/new_stylists.php?1=1';
$action_page = 'new_stylists.php';
$action_page_url = base_admin_url() . '/new_stylists.php?1=1';
$export_url = base_admin_url() . '/new_stylists.php';

$importToNaxum = false;

if($importToNaxum) {
    require_once('../common_files/include/library/naxum_api.php');
    $naxum = new Naxum_Contact();
}

//15449

if( !empty($_REQUEST['stylistid']) && !empty($_REQUEST['goto']) && ( ($_REQUEST['goto'] == 'approve') || ($_REQUEST['goto'] == 'delete') ) ) {
    $stylist_id = filter_var($_REQUEST['stylistid'], FILTER_SANITIZE_NUMBER_INT);

    //debug(false, true, $_POST, $stylist_id);

    if( ($_REQUEST['goto'] == 'approve') ) {

        try {

            $stylist_sql = "SELECT jan.*, rm.amico_id, o.remote_ip, oad.*, z.*
              FROM ".MAGENTO_TABLE_PREFIX."mvijastylist_ja_new_stylists AS jan
              INNER JOIN ".MAGENTO_TABLE_PREFIX."sales_flat_order AS o ON jan.mage_order_id = o.entity_id
              INNER JOIN ".MAGENTO_TABLE_PREFIX."sales_flat_order_address AS oad ON o.entity_id = oad.parent_id
              INNER JOIN zones AS z ON oad.region = z.zone_name
              INNER JOIN tbl_member AS rm ON jan.referrer_member_id = rm.int_member_id

              WHERE jan.stylist_id='$stylist_id'
              ";
            //echo $stylist_sql; die();
            $stylist_query = mysqli_query($conn, $stylist_sql);

            if( mysqli_num_rows($stylist_query) > 0 ) {
                $stylist__1 = mysqli_fetch_assoc($stylist_query);
                $stylist__2 = mysqli_fetch_assoc($stylist_query);

                list($stylist__1['street_1'], $stylist__1['street_2']) = explode(PHP_EOL, $stylist__1['street']);
                list($stylist__2['street_1'], $stylist__2['street_2']) = explode(PHP_EOL, $stylist__2['street']);

                $stylist__1['street_1'] = mysqli_real_escape_string($conn, $stylist__1['street_1']);
                $stylist__1['street_2'] = mysqli_real_escape_string($conn, $stylist__1['street_2']);
                $stylist__2['street_1'] = mysqli_real_escape_string($conn, $stylist__2['street_1']);
                $stylist__2['street_2'] = mysqli_real_escape_string($conn, $stylist__2['street_2']);


                //$password = randomPassword();

                //echo '<pre>'; print_r( array($stylist__1, $stylist__2) ); echo '</pre>'; die();


                //Check if the stylist data was imported to Naxum.
                if($importToNaxum) {
                    if( empty($stylist__1['naxum_contact_id']) ) {

                        $password = $stylist__1['password'];

                        $contactDetails = array(
                            'firstname' => mysqli_real_escape_string($conn, $stylist__1['firstname']),
                            'lastname' => mysqli_real_escape_string($conn, $stylist__1['lastname']),
                            'email' => mysqli_real_escape_string($conn, $stylist__1['email']),
                            'nickname' => mysqli_real_escape_string($conn, $stylist__1['nickname']),
                            'referrer_amico' => mysqli_real_escape_string($conn, $stylist__1['amico_id']),
                            'phone' => mysqli_real_escape_string($conn, $stylist__1['telephone']),
                            'fax' => mysqli_real_escape_string($conn, $stylist__1['fax']),
                            'address' => mysqli_real_escape_string($conn, $stylist__1['street_1']),
                            'address2' => mysqli_real_escape_string($conn, $stylist__1['street_2']),
                            'city' => mysqli_real_escape_string($conn, $stylist__1['city']),
                            'state' => mysqli_real_escape_string($conn, $stylist__1['zone_name']),
                            'zip' => mysqli_real_escape_string($conn, $stylist__1['postcode']),
                            'country' => 'US',
                            'ip' => mysqli_real_escape_string($conn, $stylist__1['remote_ip']),
                        );
                        if( !empty($password) ) {
                            $contactDetails['password'] = $password;
                        }

                        //echo '<pre>'; print_r( $contactDetails ); echo '</pre>'; die();

                        include_once(base_shop_path() . "/app/Mage.php");
                        Mage::reset();
                        $app = Mage::app();

                        $naxumIdOfSponsor = Mage::getModel('mvijastylist/vitashine_vitashine')->getNaxumIDFromAmicoOrVsconId($contactDetails['referrer_amico']);
                        if( !empty($naxumIdOfSponsor) ) {
                            $contactDetails['sponmemberid'] = $naxumIdOfSponsor;
                            $contactDetails['referrer_amico'] = $contactDetails['sponmemberid'];
                        } else {
                            $contactDetails['referrer_amico'] = 3; // Naxum said: send number 3 as that is the master account if no acocunt found
                            //$contactDetails['sponmemberid'] = 3;
                        }

                        $contactDetails['memberid'] = $contactDetails['referrer_amico'];

                        //echo '<pre>'; print_r( $contactDetails ); echo '</pre>'; die();

                        //$naxum->setProcessingMode( $naxum::NAXUM_PROCESSING_MODE_SANDBOX );
                        $naxum_id = $naxum->createContact($contactDetails);

                        //echo '<pre>'; var_dump( $naxum_id ); echo '</pre>'; die();
                        if( !empty($naxum_id) ) {

                            if( (!empty($naxum_id['success']) || ( $naxum_id['field']=='sitename-exists') ) && !empty( $naxum_id['naxum_id'] ) )
                            {

                                $nid = $naxum_id['naxum_id'];

                                $table = MAGENTO_TABLE_PREFIX . "mvijastylist_ja_new_stylists";
                                $fieldlist = "naxum_contact_id='{$nid}'";
                                $condition = " where stylist_id=$stylist_id";
                                $result = update_rows($conn, $table, $fieldlist, $condition);
                            }
                            else {
                                $error_mesg = 'Couldn\'t complete process with "Naxum"';
                            }

                        } else {
                            $error_mesg = 'Couldn\'t complete process with "Naxum"';
                        }
                    } else {
                        $nid = $stylist__1['naxum_contact_id'];
                    }
                }


                $nicknameCheck_sql = "SELECT int_member_id, amico_id FROM tbl_member WHERE nickname='{$stylist__1['nickname']}' ";
                $nicknameCheck_query = mysqli_query($conn, $sql);


                if( (mysqli_num_rows($nicknameCheck_query) < 1) ) {
                    if( !$importToNaxum || ( $importToNaxum && !empty($nid) ) ) {

                        $amico_id = get_next_amico_id('m');
                        $last_visit = date('Y-m-d');

                        //debug(false, true, $stylist__1, $stylist__2);


                        $query_i = "INSERT INTO tbl_member (int_parent_id,int_designation_id,amico_id,str_title,dat_last_visit,bit_active, mtype, ec_id, new_ec_id, growth, contest, reg_date,miles, nickname, bit_custom_comission, expire_custom_comission) VALUES ('{$stylist__1['referrer_member_id']}','1','$amico_id',NULL,'$last_visit','1', 'm', '7', '7', '', '', '" . date("Y-m-d H:i:s") . "','', '{$stylist__1['nickname']}', NULL, NULL)";
                        $result = mysqli_query($conn, $query_i);
                        $member_id = mysqli_insert_id($conn);


                        $table = "customers";                // inserting values to setting table
                        $in_fieldlist = "customers_firstname,customers_lastname,customers_email_address,customers_telephone,mobile_phone,customers_fax,customers_password,ssn,license_number,type, exported, change_ec";
                        $in_values = "'{$stylist__1['firstname']}','{$stylist__1['lastname']}','{$stylist__1['email']}','{$stylist__1['telephone']}','','{$stylist__1['fax']}','{$password}','','','Stylist', 'N', ''";
                        $result = insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values
                        $customer_id = mysqli_insert_id($conn);//id of the customer inserted


                        if (!empty($customer_id)) {
                            //inserting values into address_book contact billing info by putting '1' in address_book_id
                            $table = "address_book";                // inserting values to setting table
                            $in_fieldlist = "customers_id,address_book_id,entry_company,entry_firstname,entry_lastname,entry_street_address,entry_street_address2,entry_postcode,entry_city,entry_state,entry_country_id,entry_zone_id";
                            $in_values = "'$customer_id',1,'','{$stylist__1['firstname']}', '{$stylist__1['lastname']}', '{$stylist__1['street_1']}','{$stylist__1['street_2']}','{$stylist__1['postcode']}','{$stylist__1['city']}','{$stylist__1['region_id']}','223','{$stylist__1['zone_id']}'";
                            $result = insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values


                            //inserting values into address_book contact billing info by putting '2' in address_book_id
                            $table = "address_book";                // inserting values to setting table
                            $in_fieldlist = "customers_id,address_book_id,entry_company,entry_firstname,entry_lastname,entry_street_address,entry_street_address2,entry_postcode,entry_city,entry_state,entry_country_id,entry_zone_id";
                            $in_values = "'$customer_id',2,'','{$stylist__2['firstname']}', '{$stylist__2['lastname']}', '{$stylist__2['street_1']}','{$stylist__2['street_2']}','{$stylist__2['postcode']}','{$stylist__2['city']}','{$stylist__2['region_id']}','223','{$stylist__2['zone_id']}'";
                            $result = insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values


                            //inserting values into address_book contact check info by putting '3' in address_book_id
                            $table = "address_book";                // inserting values to setting table
                            $in_fieldlist = "customers_id,address_book_id,entry_company,entry_firstname,entry_lastname,entry_street_address,entry_street_address2,entry_postcode,entry_city,entry_state,entry_country_id,entry_zone_id";
                            $in_values = "'$customer_id',3,'','{$stylist__1['firstname']}', '{$stylist__1['lastname']}', '{$stylist__1['street_1']}','{$stylist__1['street_2']}','{$stylist__1['postcode']}','{$stylist__1['city']}','{$stylist__1['region_id']}','223','{$stylist__1['zone_id']}'";
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

                            //updating amico id to the stylist table
                            $table = MAGENTO_TABLE_PREFIX . "mvijastylist_ja_new_stylists";
                            $fieldlist = "applied_amico_id='$amico_id', approved='1'";
                            $condition = " where stylist_id=$stylist_id";
                            $result = update_rows($conn, $table, $fieldlist, $condition);


                            //Updating Naxum
                            $contactDetails = array(
                                'email' => $stylist__1['email'],
                                'nickname' => $stylist__1['nickname'],
                                'memberid' => $member_id,
                                'memberid2' => $amico_id,
                            );
                            if($importToNaxum) {
                                $naxum->updateContact($contactDetails);
                            }

                            /* //inserting values into the buliting board table
                             $table = "forum_users";                // inserting values to setting table
                             $in_fieldlist = "username,username_clean,user_password,user_email, user_regdate";
                             $the_date = getdate();
                             $in_values = "$member_id,$member_id,'{$password}','{$stylist__1['email']}','$the_date'";
                             $result = insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values*/
                            //end inserting values into the bulliting board table
                        }

                        //inserting value to the history of EC IDs
                        $ec_query = mysqli_query($conn, "INSERT INTO `tbl_member_ec` (`amico_id`, `ec_id`, `event`, `timestamp`) VALUES ('$amico_id', '7', 'added', '" . date("Y-m-d H:i:s") . "')") or die (mysqli_error($conn));


                        if ($stylist__1['referrer_member_id'] != 0) {
                            addcontactlist2($stylist__1['referrer_member_id'], $member_id);
                            //addcontactlist($refer_member_id,$member_id);
                        }

                        //sending mail
                        $name = $stylist__1['firstname'] . ' ' . $stylist__1['lastname'];

                        $subject = "Welcome to JohnAmico.com!";


                        // NEW MAILING CODE
                        $to = $stylist__1['email'];

                        $message = "Welcome to JohnAmico.com!   <BR>";
                        $message .= $name . '  Your Details Registered Successfully.';

                        $headers = 'MIME-Version: 1.0' . "\r\n";
                        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                        $headers .= 'From: John Amico <johnamico@johnamico.com>' . "\r\n";
                        mail($to, $subject, $message, $headers);

                        $mesg = "Successfully Approved the stylist.";
                    }
                }
                else {
                    //list($existingMemberId, $existingAmicoId) = mysqli_fetch_array($nicknameCheck_query);
                    $error_mesg = "There is an account already created with Nickname '{$stylist__1['nickname']}'.";
                }
            }
        }
        catch (Exception $e) {
            $error_mesg = "Something went wrong while activating this member.";
        }
    }
    elseif( ($_REQUEST['goto'] == 'delete') ) {
        try {

            //$stylistCheck_sql = "SELECT * FROM ".MAGENTO_TABLE_PREFIX."mvijastylist_ja_new_stylists WHERE stylist_id='$stylist_id'";
            //$stylistCheck_query = mysqli_query($conn, $stylistCheck_sql);

            $stylist_sql = "SELECT jan.*, rm.amico_id, o.remote_ip, oad.*, z.*
                FROM ".MAGENTO_TABLE_PREFIX."mvijastylist_ja_new_stylists AS jan
                INNER JOIN ".MAGENTO_TABLE_PREFIX."sales_flat_order AS o ON jan.mage_order_id = o.entity_id
                INNER JOIN ".MAGENTO_TABLE_PREFIX."sales_flat_order_address AS oad ON o.entity_id = oad.parent_id
                INNER JOIN zones AS z ON oad.region = z.zone_name
                INNER JOIN tbl_member AS rm ON jan.referrer_member_id = rm.int_member_id
                
                WHERE jan.stylist_id='$stylist_id'
            ";
            //echo $stylist_sql; die();
            $stylist_query = mysqli_query($conn, $stylist_sql);

            if( mysqli_num_rows($stylist_query) > 0 ) {
                $stylist__1 = mysqli_fetch_assoc($stylist_query);

                //updating amico id to the stylist table
                $table = MAGENTO_TABLE_PREFIX . "mvijastylist_ja_new_stylists";
                $fieldlist = "approved='2'";
                $condition = " where stylist_id=$stylist_id";
                $result = update_rows($conn, $table, $fieldlist, $condition);

                //sending mail
                if(!empty($stylist__1['email']) && filter_var($stylist__1['email'], FILTER_VALIDATE_EMAIL) )
                {
                    $to = $stylist__1['email'];
                    $name = $stylist__1['firstname'] . ' ' . $stylist__1['lastname'];
                    $subject = "Welcome to JohnAmico.com!";

                    //$message = "Welcome to JohnAmico.com!   <BR>";
                    $message = '';
                    $message .= $name . '<br/><br/>  Your Stylist application was declined and your was cancelled.';

                    $headers = 'MIME-Version: 1.0' . "\r\n";
                    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                    $headers .= 'From: John Amico <johnamico@johnamico.com>' . "\r\n";
                    mail($to, $subject, $message, $headers);
                }

                $mesg = "Stylist has been deleted";

            } else {
                $error_mesg = "Stylist Not Found!";
            }

        }
        catch (Exception $e) {
            //debug(false, false, $e);
            $mesg = "Something went wrong while deleting stylist";
        }
    }

}


$ten_days_back = strtotime("-20 days");

if( empty($_POST['daterange']) && !empty($_REQUEST['start_date']) && strpos($_REQUEST['start_date'], '-') ) {
    list($_REQUEST['start_date_y'], $_REQUEST['start_date_m'], $_REQUEST['start_date_d']) = explode('-', $_REQUEST['start_date']);
}
if( empty($_POST['daterange']) && !empty($_REQUEST['end_date']) && strpos($_REQUEST['end_date'], '-') ) {
    list($_REQUEST['end_date_y'], $_REQUEST['end_date_y'], $_REQUEST['end_date_y']) = explode('-', $_REQUEST['end_date']);
}

$start_date_d = ( !empty($_REQUEST['start_date_d']) ? filter_var($_REQUEST['start_date_d'], FILTER_SANITIZE_NUMBER_INT) : date('d', $ten_days_back) );
$start_date_m = ( !empty($_REQUEST['start_date_m']) ? filter_var($_REQUEST['start_date_m'], FILTER_SANITIZE_NUMBER_INT) : date('m', $ten_days_back) );
$start_date_y = ( !empty($_REQUEST['start_date_y']) ? filter_var($_REQUEST['start_date_y'], FILTER_SANITIZE_NUMBER_INT) : date('Y', $ten_days_back) );
$start_date_m_name = date('F', mktime(0, 0, 0, $start_date_m, 10));
$end_date_d = ( !empty($_REQUEST['end_date_d']) ? filter_var($_REQUEST['end_date_d'], FILTER_SANITIZE_NUMBER_INT) : date('d') );
$end_date_m = ( !empty($_REQUEST['end_date_m']) ? filter_var($_REQUEST['end_date_m'], FILTER_SANITIZE_NUMBER_INT) : date('m') );
$end_date_y = ( !empty($_REQUEST['end_date_y']) ? filter_var($_REQUEST['end_date_y'], FILTER_SANITIZE_NUMBER_INT) : date('Y') );
$end_date_m_name = date('F', mktime(0, 0, 0, $end_date_m, 10));

$start_date = "$start_date_y-$start_date_m-$start_date_d";
$end_date = "$end_date_y-$end_date_m-$end_date_d";

//echo '<pre>'; var_dump($start_date_d, $_REQUEST, $start_date); die();

$limit = 30;
$page = ((!empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) ? $_REQUEST['page'] : 1);

$limit_start = ($page * $limit) - $limit;
$limit_end = ($page * $limit);


$sql = "SELECT jan.stylist_id, IF(jan.email='', o.customer_email, jan.email) AS email, jan.nickname, jan.product_sku, IF(jan.naxum_contact_id=0, 'N/A', jan.naxum_contact_id) AS naxum_contact_id, IF( ISNULL(jan.applied_amico_id), 'N/A', jan.applied_amico_id) AS applied_amico_id, jan.approved, IF(jan.approved=1, CONCAT('Yes', ' - ', jan.applied_amico_id), 'No') AS approved_txt, o.entity_id, o.increment_id AS orders_id, CONCAT(o.customer_firstname, ' ', o.customer_lastname) AS customers_name, DATE_FORMAT(jan.created, '%m/%d/%Y %H:%i') as date_purch, o.grand_total AS order_total, m.amico_id AS refering_member
FROM ".MAGENTO_TABLE_PREFIX."mvijastylist_ja_new_stylists AS jan
INNER JOIN ".MAGENTO_TABLE_PREFIX."sales_flat_order AS o ON jan.mage_order_id = o.entity_id
INNER JOIN ".MAGENTO_TABLE_PREFIX."amasty_amorderattr_order_attribute AS oa ON oa.order_id = o.entity_id
INNER JOIN tbl_member AS m ON jan.referrer_member_id = m.int_member_id
";

$sortby = '';
$sortby = "ORDER BY jan.created DESC";

//$start_date = strtotime("$start_date_d $start_date_m_name, $start_date_y");
//$end_date = strtotime("$end_date_d $end_date_m_name, $end_date_y");

//$conditions[] = " o.refering_member != '' ";
//$conditions[] = " o.refering_member != 'None' ";
//$conditions[] = "  oa.jareferrer_amicoid NOT IN ( '0', '') ";
//$conditions[] = " jan.created  >= '$start_date' ";
//$conditions[] = " jan.created  <= '$end_date' ";
$conditions[] = " (jan.created BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59') ";
$conditions[] = " jan.approved IN (0, 1)";

$field_details = array(
    'customers_name' => 'Stylist Name',
    'email' => 'Email',
    'orders_id' => 'Order ID',
    'product_sku' => 'Product SKU',
    'date_purch' => 'Date Purchased',
    'refering_member' => 'Referring Member',
    //'naxum_contact_id' => 'Naxum ID',
    'nickname' => 'Nickname',
    'approved_txt' => 'Approved?',
    'actions' => 'Commands',
    'actions_approve' => array(
        'action' => 'approve',
        'button_type' => 'success',
        'display_condition' => array(
            'field' => 'approved',
            'value' => '1',
            'result' => false, //No Display, true=>Display
        ),
    ),
    'actions_delete' => array(
        'action' => 'delete',
        'button_type' => 'danger',
        'display_condition' => array(
            'field' => 'approved',
            'value' => '1',
            'result' => false, //No Display, true=>Display
        ),
    ),
);

//$magento_order_update_page = true;

$id_field = 'stylist_id';
$no_edit_button = true;
$no_delete_butotn = true;


$action_page__id_handler = 'stylistid';

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$sql .= " $sortby ";

//echo $sql; die();



//$query_pag_data = " $condition LIMIT $start, $per_page";
$data_num_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

mysqli_store_result($conn);
$numrows = mysqli_num_rows($data_num_query);

//echo $sql;

$sql .= " LIMIT $limit OFFSET $limit_start ";
//echo $sql;
$data_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

//$display_data_from_data_page = false;

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
                <div class="col-xs-12">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                    </header>
                    <div class="panel-body">
                        <?php if(!empty($mesg) || !empty($error_mesg) ): ?>
                            <div class="row">
                                <div class="col-xs-12 ">
                                    <?php if( !empty($mesg) ) : ?>
                                        <div class="alert alert-success">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                            <?php echo $mesg; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if( !empty($error_mesg) ) : ?>
                                        <div class="alert alert-danger">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                            <?php echo $error_mesg; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="row">
                            <div class="col-xs-12 filter_wrapper ">
                                <div class="table-responsive">
                                    <div class="col-lg-4 col-md-8 col-sm-10 col-xs-12 centering date_range_wrapper">
                                        <form method="POST" action="">
                                            <input type="hidden" name="daterange" value="1" />
                                            <table class="table table-bordered table-striped mb-none">
                                                <tr>
                                                    <td align="center" colspan="2"><strong>Date Range Selection</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Start Date:</td>
                                                    <td>
                                                        <select name="start_date_m">
                                                            <?
                                                            for ($i = 1; $i <= 12; $i++) {
                                                                $sel = "";
                                                                if ($i == $start_date_m) {
                                                                    $sel = "selected";
                                                                }
                                                                echo "<option value=\"$i\" $sel>$i</option>";
                                                            }
                                                            ?>
                                                        </select> /
                                                        <select name="start_date_d">
                                                            <?
                                                            for ($i = 1; $i <= 31; $i++) {
                                                                $sel = "";
                                                                if ($i == $start_date_d) {
                                                                    $sel = "selected";
                                                                }
                                                                echo "<option value=\"$i\" $sel>$i</option>";
                                                            }
                                                            ?>
                                                        </select> /
                                                        <select name="start_date_y">
                                                            <?
                                                            for ($i = date("Y") - 2; $i <= date("Y"); $i++) {
                                                                $sel = "";
                                                                if ($i == $start_date_y) {
                                                                    $sel = "selected";
                                                                }
                                                                echo "<option value=\"$i\" $sel>$i</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>

                                                </tr>
                                                <tr>
                                                    <td>End Date:</td>
                                                    <td>
                                                        <select name="end_date_m">
                                                            <?
                                                            for ($i = 1; $i <= 12; $i++) {
                                                                $sel = "";
                                                                if ($i == $end_date_m) {
                                                                    $sel = "selected";
                                                                }
                                                                echo "<option value=\"$i\" $sel>$i</option>";
                                                            }
                                                            ?>
                                                        </select> /
                                                        <select name="end_date_d">
                                                            <?
                                                            for ($i = 1; $i <= 31; $i++) {
                                                                $sel = "";
                                                                if ($i == $end_date_d) {
                                                                    $sel = "selected";
                                                                }
                                                                echo "<option value=\"$i\" $sel>$i</option>";
                                                            }
                                                            ?>
                                                        </select> /
                                                        <select name="end_date_y">
                                                            <?
                                                            for ($i = date("Y") - 2; $i <= date("Y"); $i++) {
                                                                $sel = "";
                                                                if ($i == $end_date_y) {
                                                                    $sel = "selected";
                                                                }
                                                                echo "<option value=\"$i\" $sel>$i</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="center" colspan="2">
                                                        <input type="submit" class="mb-xs mt-xs mr-xs btn btn-xs btn-primary" value="Submit">
                                                    </td>
                                                </tr>
                                            </table>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php require_once('display_members_data.php'); ?>
                    </div>
                </div>
                <div class="clearfix"></div>
            </section>
        </div>
    </div>


<?php
require_once("templates/footer.php");