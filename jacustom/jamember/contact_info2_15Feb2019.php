<?php
//echo '<pre>'; print_r( $_GET ); die();
$page_name = 'Contact Information';
$page_title = $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("../common_files/Constant_contact/class.cc.php");

$is_popup = true;

//$display_header = false;
//require_once("templates/header.php");

$member_id = $_SESSION['member']['ses_member_id'];
$session_user = $_SESSION['member']['session_user'];


$memberid_got = !empty($memberid_got) ? $memberid_got : ( !empty($_GET['memberid']) ? filter_var($_GET['memberid'], FILTER_SANITIZE_NUMBER_INT) : 0 );
$memberid = $memberid_got;

if(empty($memberid)) { exit; }

if(function_exists('create_table__contact_organizer_subscription')) {
    $tableCreated = create_table__contact_organizer_subscription();
}

$a_amico = mysqli_fetch_array(mysqli_query($conn,"select amico_id, mtype from tbl_member where int_member_id='$member_id'"));
$user_amico_id = $a_amico['amico_id'];
$user_mtype    = $a_amico['mtype'];

list($change_ec) = mysqli_fetch_row(mysqli_query($conn,"SELECT c.change_ec FROM customers c INNER JOIN tbl_member tm ON c.customers_id = tm.int_customer_id WHERE tm.int_member_id = '$member_id'"));

if ($_POST['chick_guests1']!='') {$_POST['chick_guests'].='Beauty Show Tickets: '.$_POST['chick_guests1'];};
if ($_POST['chick_guests2']!='') {$_POST['chick_guests'].="\n\n".'Breakfast/Lunch Tickets: '.$_POST['chick_guests2'];};
if ($_POST['chick_guests3']!='') {$_POST['chick_guests'].="\n\n".'Dinner Tickets: '.$_POST['chick_guests3'];};


$customers_id_arr = mysqli_fetch_array(mysqli_query($conn,"select int_customer_id from tbl_member where int_member_id='$memberid'"));
$customers_id = $customers_id_arr['int_customer_id'];

if( !empty($customers_id) && is_numeric($customers_id) ) {
    if( $user_mtype == 'e' ) {

        if($_POST['goto'] == 'save__cinfo_info2'){

            //echo '<pre>'; print_r( $_POST ); die();

            if($_POST['cc_id']==0) {
                const_contact_create($_POST['customers_mail_addr'], $_POST['customers_firstname'], $_POST['customers_lastname']);
            }
            else {
                const_contact_update($_POST['cc_id'], $_POST['customers_mail_addr'], $_POST['customers_firstname'], $_POST['customers_lastname']);
            }

            if ($chick_guests1!='') {$chick_guests.='Beauty Show Tickets: '.$chick_guests1;};
            if ($chick_guests2!='') {$chick_guests.="\n\n".'Breakfast/Lunch Tickets: '.$chick_guests2;};
            if ($chick_guests3!='') {$chick_guests.="\n\n".'Dinner Tickets: '.$chick_guests3;};

            //echo "cid = $customers_id";
            $query = "update customers set customers_firstname='".$_POST['customers_firstname']."', customers_lastname='".$_POST['customers_lastname']."', customers_email_address='".$_POST['customers_mail_addr']."', customers_password='$customers_password', customers_telephone='".$_POST['customers_telephone']."', customers_telephone1='".$_POST['customers_telephone1']."', customers_telephone2='".$_POST['customers_telephone2']."', customers_fax='".$_POST['customers_fax']."', cc_type='".$_POST['cc_type']."', cc_number='".$_POST['cc_number']."', cc_expiry_date='".$_POST['cc_expiry_date']."', cc_cvv='".$_POST['cc_cvv']."', tickets='".$_POST['chick_tickets']."', guests='".$_POST['chick_guests']."', tickets_dinner='".$_POST['chick_dinner_tickets']."',mobile_phone='".$_POST['mobile_phone']."', operator_id='".$_POST['operator_id']."'  where customers_id='$customers_id'";
            mysqli_query($conn,$query);
            //echo $query."<br>err = ".mysql_error();
            //exit;

            //echo $query; die();


    //tickets info
            $query = "update customers set chick_tickets='".$_POST['chick_tickets']."', chick_dinner_tickets='".$_POST['chick_dinner_tickets']."', chick_dinner_tickets2='".$_POST['chick_dinner_tickets2']."', chick_guests1='".addslashes($_POST['chick_guests1'])."', chick_guests2='".addslashes($_POST['chick_guests2'])."', chick_guests3='".addslashes($_POST['chick_guests3'])."'  ";
            if(!empty($_POST['password'])) {
                $query .= ", customers_password='{$_POST['password']}'";
            }
            $query.= " where customers_id='$customers_id'";
            mysqli_query($conn,$query);


            $query2 = "update address_book set entry_firstname='{$_POST['customers_firstname']}', entry_lastname='{$_POST['customers_lastname']}', entry_street_address='".$_POST['entry_street_address']."', entry_street_address2='".$_POST['entry_street_address2']."', entry_postcode='".$_POST['entry_postcode']."', entry_city='".$_POST['entry_city']."', entry_state='".$_POST['entry_zone_id']."', entry_zone_id='".$_POST['entry_zone_id']."'  where customers_id='$customers_id' and address_book_id=1";
            mysqli_query($conn,$query2);

            $sh_query = "update address_book set entry_firstname='{$_POST['sh_customers_firstname']}', entry_lastname='{$_POST['sh_customers_lastname']}', entry_street_address='".$_POST['sh_entry_street_address']."', entry_street_address2='".$_POST['sh_entry_street_address2']."', entry_postcode='".$_POST['sh_entry_postcode']."', entry_city='".$_POST['sh_entry_city']."', entry_state='".$_POST['sh_entry_zone_id']."', entry_zone_id='".$_POST['sh_entry_zone_id']."'  where customers_id='$customers_id' and address_book_id=2";
            mysqli_query($conn,$sh_query);

            $ch_query = "update address_book set entry_firstname='{$_POST['ch_customers_firstname']}', entry_lastname='{$_POST['ch_customers_lastname']}', entry_street_address='".$_POST['ch_entry_street_address']."', entry_street_address2='".$_POST['ch_entry_street_address2']."', entry_postcode='".$_POST['ch_entry_postcode']."', entry_city='".$_POST['ch_entry_city']."', entry_state='".$_POST['ch_entry_zone_id']."', entry_zone_id='".$_POST['ch_entry_zone_id']."'  where customers_id='$customers_id' and address_book_id=3";
            mysqli_query($conn,$ch_query);


            $query3 = "UPDATE tbl_member SET nickname='".addslashes($_POST['nickname'])."' ";
            if(is_numeric($_POST['EC'])) {
                $query3 .= ", ec_id = '" . $_POST['EC'] . "'";
            }
            $query3 .= " WHERE int_customer_id = '$customers_id'";
            mysqli_query($conn,$query3);
            //echo $query2."<br>err = ".mysql_error();

            make_customer_file($customers_id);

            $msg = "The information has been saved!";

            if ($_POST['chick_attend'] == 'yes') {
                $msg.="<br />Your request has been sent to Chicago Chicago 2008";
            }

        }

        $rsmember = mysqli_query($conn, "select c.guests, c.tickets, c.tickets_dinner, c.cc_type, c.cc_number, c.cc_expiry_date, c.cc_cvv, c.customers_id,c.customers_firstname,c.customers_lastname,c.customers_email_address,customers_password,customers_telephone,customers_telephone1,customers_telephone2,customers_fax,m.ec_id,m.int_parent_id,
        c.mobile_phone, operator_id, m.amico_id from customers c inner join tbl_member m on c.customers_id=m.int_customer_id where m.int_member_id='$memberid_got'");
        list($chick_guests, $chick_tickets, $chick_dinner_tickets, $cc_type, $cc_number, $cc_expiry_date, $cc_cvv, $customersid, $firstname, $lastname, $email, $pass, $phone, $phone1, $phone2, $fax, $ec_id, $int_parent_id, $mobile_phone, $operator_id, $memberGot_amico_id) = mysqli_fetch_row($rsmember);

        $query = mysqli_query($conn, "SELECT c.customers_firstname, c.customers_lastname, m.amico_id FROM tbl_member m INNER JOIN customers c ON m.int_customer_id=c.customers_id WHERE m.int_member_id = '" . (int) $int_parent_id . "'");
        $parent_member = mysqli_fetch_assoc($query);
        $parent_member_value = '';

        if ($parent_member) {
            $parent_member_value = $parent_member['customers_firstname'] . " " . $parent_member['customers_lastname'] . " (" . $parent_member['amico_id'] . ")";;
        }

        $rsaddress=mysqli_query($conn,"select entry_company,entry_street_address,entry_street_address2,entry_postcode,entry_city,entry_state,entry_country_id,entry_zone_id from address_book where customers_id=$customersid and address_book_id=1");
        list($company,$streetaddress,$streetaddress2,$postcode,$city,$state,$countryid,$zoneid)=mysqli_fetch_row($rsaddress);

        $rscountry = mysqli_query($conn,"select countries_name from countries where countries_id=$countryid");
        list($country) = mysqli_fetch_row($rscountry);
        $rszone = mysqli_query($conn,"select zone_name from zones where zone_id=$zoneid");
        list($zone) = mysqli_fetch_row($rszone);

        $rsaddress2=mysqli_query($conn,"select entry_firstname,  entry_lastname,entry_street_address,entry_street_address2,entry_postcode,entry_city,entry_state,entry_country_id,entry_zone_id from address_book where customers_id=$customersid and address_book_id=2");
        list($sh_firstname,$sh_lastname ,$sh_streetaddress,$sh_streetaddress2,$sh_postcode,$sh_city,$sh_state,$sh_countryid,$sh_zoneid)=mysqli_fetch_row($rsaddress2);

        $rscountry2 = mysqli_query($conn,"select countries_name from countries where countries_id=$sh_countryid");
        list($sh_country) = mysqli_fetch_row($rscountry2);
        $rszone2 = mysqli_query($conn,"select zone_name from zones where zone_id=$sh_zoneid");
        list($sh_zone) = mysqli_fetch_row($rszone2);

        $rsaddress3=mysqli_query($conn,"select entry_firstname,  entry_lastname,entry_street_address,entry_street_address2,entry_postcode,entry_city,entry_state,entry_country_id,entry_zone_id from address_book where customers_id=$customersid and address_book_id=3");
        list($ch_firstname,$ch_lastname ,$ch_streetaddress,$ch_streetaddress2,$ch_postcode,$ch_city,$ch_state,$ch_countryid,$ch_zoneid)=mysqli_fetch_row($rsaddress3);

        $rscountry3 = mysqli_query($conn,"select countries_name from countries where countries_id=$ch_countryid");
        list($ch_country) = mysqli_fetch_row($rscountry3);
        $rszone3 = mysqli_query($conn,"select zone_name from zones where zone_id=$ch_zoneid");
        list($ch_zone) = mysqli_fetch_row($rszone3);

        $cc = new cc('johnamico', 'haircare');
        $contact = $cc->query_contacts($email);
        $cc_id = ( !empty($contact['id']) ) ? $contact['id'] : 0 ;

        //tickets info
        $query=mysqli_query($conn,"select c.*, m.amico_id, m.nickname from customers c inner join tbl_member m on c.customers_id=m.int_customer_id where m.int_member_id='$memberid_got'");
        $f=mysqli_fetch_array($query);

        $chick_tickets = stripslashes($f['chick_tickets']);
        $chick_dinner_tickets = stripslashes($f['chick_dinner_tickets']);
        $chick_dinner_tickets2 = stripslashes($f['chick_dinner_tickets2']);
        $chick_guests1 = stripslashes($f['chick_guests1']);
        $chick_guests2 = stripslashes($f['chick_guests2']);
        $chick_guests3 = stripslashes($f['chick_guests3']);
        $nickname = stripslashes($f['nickname']);
        $amic_id = $f['amico_id'];

        $query=mysqli_query($conn,"SELECT timestamp FROM `tbl_member_ec` WHERE `amico_id`='".$amic_id."' ORDER BY timestamp asc LIMIT 1");
        $f = mysqli_fetch_array($query);
        $join_date = substr($f['timestamp'], 0, 10);

    }
    elseif( $user_mtype == 'c' ) {

        if($_POST['goto'] == 'save__cinfo_info2') {
            if($_POST['cc_id'] == 0) {
                const_contact_create($_POST['customers_mail_addr'], $_POST['customers_firstname'], $_POST['customers_lastname']);
            }
            else {
                const_contact_update($_POST['cc_id'], $_POST['customers_mail_addr'], $_POST['customers_firstname'], $_POST['customers_lastname']);
            }

            $query = "update customers set customers_email_address='".$_POST['customers_mail_addr']."' where customers_id='$customers_id'";
            mysqli_query($conn,$query);
            $msg = "The information has been saved!";
        }

        $rsmember=mysqli_query($conn,"select c.guests, c.tickets, c.tickets_dinner, c.cc_type, c.cc_number, c.cc_expiry_date, c.cc_cvv, c.customers_id,c.customers_firstname,c.customers_lastname,c.customers_email_address,customers_password,customers_telephone,customers_telephone1,customers_telephone2,customers_fax,m.ec_id, m.amico_id from customers c inner join tbl_member m on c.customers_id=m.int_customer_id where m.int_member_id='$memberid_got'");
        list($chick_guests,$chick_tickets,$chick_dinner_tickets,$cc_type,$cc_number,$cc_expiry_date,$cc_cvv,$customersid,$firstname,$lastname,$email,$pass,$phone,$phone1,$phone2,$fax,$ec_id, $memberGot_amico_id)=mysqli_fetch_row($rsmember);

        $rsaddress=mysqli_query($conn,"select entry_company,entry_street_address,entry_street_address2,entry_postcode,entry_city,entry_state,entry_country_id,entry_zone_id from address_book where customers_id=$customersid and address_book_id=1");
        list($company,$streetaddress,$streetaddress2,$postcode,$city,$state,$countryid,$zoneid)=mysqli_fetch_row($rsaddress);
        $rscountry = mysqli_query($conn,"select countries_name from countries where countries_id=$countryid");
        list($country) = mysqli_fetch_row($rscountry);
        $rszone = mysqli_query($conn,"select zone_name from zones where zone_id=$zoneid");
        list($zone) = mysqli_fetch_row($rszone);

        $cc = new cc('johnamico', 'haircare');
        $contact = $cc->query_contacts($email);
        $cc_id = ( !empty($contact['id']) ) ? $contact['id'] : 0 ;
    }


    if($tableCreated) {
        $idExistedSql = "SELECT id, subscribed FROM tbl_member_subscription WHERE ref_member_id={$member_id} AND sub_member_id={$memberid} ";
        $idExistedQuery = mysqli_query($conn, $idExistedSql);

        if(mysqli_num_rows($idExistedQuery) > 0 ) {
            list($subscriptionId, $contact_organizer_email_subscribed) = mysqli_fetch_row($idExistedQuery);
        }
    }

    if($_POST['goto'] == 'save__cinfo_info2' && !empty($member_id) ){
        if( $tableCreated )
        {
            $newsletterSubscribed = in_array($_POST['contact_organizer_email_subscribed'], array(0, 1)) ? (string)$_POST['contact_organizer_email_subscribed'] : NULL;
            if( !empty($subscriptionId) ) {
                $updateSql = "UPDATE tbl_member_subscription SET subscribed={$newsletterSubscribed}  WHERE id={$subscriptionId} ";
            }
            else {
                $updateSql = " INSERT INTO tbl_member_subscription (ref_member_id, sub_member_id, subscribed, created_at) VALUES ({$member_id}, {$memberid}, $newsletterSubscribed, NOW() )";
            }
            mysqli_query($conn, $updateSql);
        }
    }

    if($tableCreated) {
        $idExistedSql = "SELECT id, subscribed FROM tbl_member_subscription WHERE ref_member_id={$member_id} AND sub_member_id={$memberid} ";
        $idExistedQuery = mysqli_query($conn, $idExistedSql);

        if(mysqli_num_rows($idExistedQuery) > 0 ) {
            list($subscriptionId, $contact_organizer_email_subscribed) = mysqli_fetch_row($idExistedQuery);
        }

        if( !isset($contact_organizer_email_subscribed) || ($contact_organizer_email_subscribed == 1) ) {
            $contact_organizer_email_subscribed = 1;
        } else {
            $contact_organizer_email_subscribed = 0;
        }
    }

} else {
    $error_mesg = "Something Went wrong. Please Reload.";
}
?>

<script>
    function pop(url,w,h) {
        var p = window.open(url, "pop", "width="+w+",height="+h+",alwaysLowered=0,alwaysRaised=1,channelmode=0,directories=0,fullscreen=0,hotkeys=1,location=0,menubar=0,resizable=1,scrollbars=1,status=1,titlebar=1,toolbar=0,z-lock=0");
        if( p.opener == null ) p.opener = window;
    }

    function updateValue(id, value) {
        // this gets called from the popup window and updates the field with a new value
        console.log(id, value);
        document.getElementById(id).value = value;
    }
</script>

    <!--<div role="main" class="content-body extra_information <?php /*echo ( $is_popup ? 'no-margin-left' : '' ); */?> ">-->
    <div class="row ">
        <div class="col-xs-12 centering">
            <form name="cinfo_info2" id="cinfo_info2" action="" method="post">
                <section class="panel">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                    </header>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-12 ">

                                <?php if(!empty($msg)): ?>
                                    <div class="message">
                                        <div class="alter alert-success"><?php echo $msg;?></div>
                                    </div>
                                <?php endif;?>

                                <?php if(!empty($error_mesg)): ?>
                                    <div class="message">
                                        <div class="alter alert-danger"><?php echo $error_mesg;?></div>
                                    </div>
                                <?php endif;?>

                                <section class="panel panel-primary">
                                    <header class="panel-heading text-center padding-5-10">
                                        <h2 class="panel-title font-size__16">Personal Information</h2>
                                    </header>
                                    <div class="panel-body">
                                        <input type="hidden" name="memberid" value="<?=$memberid?>" >
                                        <input type="hidden" name="cc_id" value="<?=$cc_id?>" >
                                        <input type="hidden" name="goto" value="save__cinfo_info2">

                                        <div class="form-group">
                                            <label class="col-xs-4 form-control-label" for="">Join Date</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static"><?php echo $join_date;?></p>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-xs-4 form-control-label" for="">Amico ID</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static"><?php echo $memberGot_amico_id;?></p>
                                            </div>
                                        </div>
                                        <?php
                                        $ec_fields = array(
                                            'sponsor' => array('name'=>'Sponsor','value'=>'parent_member_value'),
                                            'nickname' => array(),
                                            'customers_lastname' => array('name'=>'Last Name','value'=>'lastname'),
                                            'customers_firstname' => array('name'=>'First Name','value'=>'firstname'),
                                            'password' => '',
                                            'customers_mail_addr' => array('name'=>'Email Address','value'=>'email'),
                                            'entry_street_address' => array('name'=>'Street Address','value'=>'streetaddress'),
                                            'entry_street_address2' => array('name'=>'Street Address 2','value'=>'streetaddress2'),
                                            'entry_postcode' => array('name'=>'Post Code','value'=>'postcode'),
                                            'entry_city' => array('name'=>'City','value'=>'city'),
                                            'entry_zone_id' => '',
                                            'EC' => '',
                                        );

                                        $chap_fields = $ec_fields;
                                        ?>

                                        <?php

                                        $fields = ( $user_mtype == 'e' ) ? $ec_fields : $chap_fields;

                                        if(!empty($fields)) :
                                            foreach ($fields as $ecf_k => $ecf) :
                                                $value = !empty($ecf['value']) ? $ecf['value'] : $ecf_k;
                                                $title = !empty($ecf['name']) ? $ecf['name'] : ucfirst($ecf_k);
                                                ?>
                                                <?php if($ecf_k == 'password') :?>
                                                    <div class="form-group">
                                                        <label class="col-xs-4 form-control-label" for="<?php echo $ecf_k;?>"><?php echo $title; ?></label>
                                                        <div class="col-xs-8">
                                                            <!--<input <?php /*if($user_mtype == 'c'):*/?>type="text"<?php /*else: */?>type="password"<?php /*endif;*/?> class="form-control" id="<?php /*echo $ecf_k;*/?>" name="<?php /*echo $ecf_k;*/?>" value="<?php /*echo $$value;*/?>">-->
                                                            <div class="row">
                                                                <div class="col-xs-7">
                                                                    <input type="password" class="form-control" id="<?php echo $ecf_k;?>" name="<?php echo $ecf_k;?>" value="<?php echo $pass;?>">
                                                                </div>
                                                                <div class="col-xs-5"><p class="form-control-static"><?php echo $pass;?></p></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php elseif($ecf_k == 'entry_zone_id') : ?>
                                                    <div class="form-group">
                                                        <label class="col-xs-4 form-control-label" for="entry_zone_id">State</label>
                                                        <div class="col-xs-8">
                                                            <select name="entry_zone_id" id="entry_zone_id" class="form-control">
                                                                <?php
                                                                $state=mysqli_query($conn,"SELECT zone_id, zone_name FROM zones WHERE zone_country_id='223'");
                                                                while($row_state=mysqli_fetch_array($state)){?>
                                                                    <option value="<?=$row_state['zone_id'];?>" <?if($row_state['zone_id']==$zoneid){echo "SELECTED";}?>><?=$row_state['zone_name'];?></option>
                                                                <?}?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                <?php elseif($ecf_k == 'EC') : ?>
                                                    <div class="form-group">
                                                        <label class="col-xs-4 form-control-label" for="EC">Current EC</label>
                                                        <div class="col-xs-8">
                                                            <?php if($change_ec == "Y") : ?>
                                                                <select name="EC" id="EC" class="form-control">
                                                                    <?php
                                                                    $sql2 = "SELECT amico_id FROM tbl_member WHERE mtype = 'e' ORDER BY amico_id";
                                                                    $res2 = mysqli_query($conn,$sql2);
                                                                    while($row2 = mysqli_fetch_assoc($res2)) {
                                                                        ?>
                                                                        <option value="<?=$row2['amico_id'];?>" <?if($ec_id == $row2['amico_id']){echo "SELECTED";}?>><?=$row2['amico_id'];?></option>
                                                                    <?php  } ?>
                                                                </select>
                                                            <?php else: ?>
                                                                <p class="form-control-static"><?php echo $ec_id;?></p>
                                                                <!--<input type="hidden" name="EC" value="<?/*=$ec_id;*/?>">-->
                                                            <?php endif;?>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="form-group">
                                                        <label class="col-xs-4 form-control-label" for="<?php echo $ecf_k;?>"><?php echo $title; ?></label>
                                                        <div class="col-xs-8">
                                                            <input type="text" class="form-control" id="<?php echo $ecf_k;?>" name="<?php echo $ecf_k;?>" value="<?php echo $$value;?>">
                                                        </div>
                                                    </div>
                                                <?php endif;?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </section>

                                <section class="panel panel-primary">
                                    <header class="panel-heading text-center padding-5-10">
                                        <h2 class="panel-title font-size__16">Contact Information</h2>
                                    </header>
                                    <div class="panel-body">
                                        <?php
                                        $ec_fields = array(
                                            'customers_telephone' => array('name'=>'Phone Number','value'=>'phone'),
                                            'customers_telephone1' => array('name'=>'Phone Number 1','value'=>'phone1'),
                                            'customers_telephone2' => array('name'=>'Phone Number 2','value'=>'phone2'),
                                            'customers_fax' => array('name'=>'Fax Number','value'=>'fax'),
                                            'mobile_phone' => array('name'=>'Mobile Phone','value'=>'mobile_phone'),
                                            'operator_id' => array(),
                                        );

                                        $chap_fields = $ec_fields;
                                        ?>

                                        <?php

                                        $fields = ( $user_mtype == 'e' ) ? $ec_fields : $chap_fields;

                                        if(!empty($fields)) :
                                            foreach ($fields as $ecf_k => $ecf) :
                                                $value = !empty($ecf['value']) ? $ecf['value'] : $ecf_k;
                                                $title = !empty($ecf['name']) ? $ecf['name'] : ucfirst($ecf_k);
                                                ?>
                                                <?php if($ecf_k == 'operator_id') : ?>
                                                    <div class="form-group">
                                                        <label class="col-xs-4 form-control-label" for="operator_id">Carrier</label>
                                                        <div class="col-xs-8">
                                                            <select name="operator_id" id="operator_id" class="form-operator_id">
                                                                <option value = 0 >None Selected</option>
                                                                <?php
                                                                $rs_operator = mysqli_query($conn,"select id, operator from mobile_operators order by operator");
                                                                while(list($op_id, $operat)= mysqli_fetch_row($rs_operator)){
                                                                    if($op_id == $operator_id)
                                                                        echo '<option value ='.$op_id.' selected>'.$operat.'</option>';
                                                                    else
                                                                        echo '<option value ='.$op_id.' >'.$operat.'</option>';
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="form-group">
                                                        <label class="col-xs-4 form-control-label" for="<?php echo $ecf_k;?>"><?php echo $title; ?></label>
                                                        <div class="col-xs-8">
                                                            <input type="text" class="form-control" id="<?php echo $ecf_k;?>" name="<?php echo $ecf_k;?>" value="<?php echo $$value;?>">
                                                        </div>
                                                    </div>
                                                <?php endif;?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </section>

                                <?php if($user_mtype == 'e') : ?>
                                    <?php foreach( array('sh'=>'Shipping', 'ch'=>'Check') as $addr_id => $addr_name) : ?>
                                        <section class="panel panel-primary">
                                            <header class="panel-heading text-center padding-5-10">
                                                <h2 class="panel-title font-size__16"><?php echo $addr_name;?> Address</h2>
                                            </header>
                                            <div class="panel-body">
                                                <?php
                                                $ec_fields = array(
                                                    'customers_lastname' => array('name'=>'Last Name','value'=>'lastname'),
                                                    'customers_firstname' => array('name'=>'First Name','value'=>'firstname'),
                                                    'entry_street_address' => array('name'=>'Street Address','value'=>'streetaddress'),
                                                    'entry_street_address2' => array('name'=>'Street Address 2','value'=>'streetaddress2'),
                                                    'entry_postcode' => array('name'=>'Post Code','value'=>'postcode'),
                                                    'entry_city' => array('name'=>'City','value'=>'city'),
                                                    'entry_zone_id' => array('value'=>'zoneid'),
                                                );

                                                $chap_fields = array();
                                                ?>

                                                <?php

                                                $fields = ( $user_mtype == 'e' ) ? $ec_fields : $chap_fields;

                                                if(!empty($fields)) :
                                                    foreach ($fields as $ecf_k => $ecf) :
                                                        $value = !empty($ecf['value']) ? $addr_id.'_'.$ecf['value'] : $addr_id.'_'.$ecf_k;
                                                        $title = !empty($ecf['name']) ? $ecf['name'] : ucfirst($ecf_k);
                                                        ?>
                                                        <?php if($ecf_k == 'entry_zone_id') : ?>
                                                            <div class="form-group">
                                                                <label class="col-xs-4 form-control-label" for="<?php echo $addr_id.'_'.$ecf_k; ?>">State</label>
                                                                <div class="col-xs-8">
                                                                    <select name="<?php echo $addr_id.'_'.$ecf_k; ?>" id="<?php echo $addr_id.'_'.$ecf_k; ?>" class="form-control">
                                                                        <?php
                                                                        $state=mysqli_query($conn,"SELECT zone_id, zone_name FROM zones WHERE zone_country_id='223'");
                                                                        while($row_state=mysqli_fetch_array($state)){?>
                                                                            <option value="<?=$row_state['zone_id'];?>" <?if($row_state['zone_id'] == $$value ){echo "SELECTED";}?>><?=$row_state['zone_name'];?></option>
                                                                        <?}?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="form-group">
                                                                <label class="col-xs-4 form-control-label" for="<?php echo $addr_id.'_'.$ecf_k;?>"><?php echo $title; ?></label>
                                                                <div class="col-xs-8">
                                                                    <input type="text" class="form-control" id="<?php echo $addr_id.'_'.$ecf_k;?>" name="<?php echo $addr_id.'_'.$ecf_k;?>" value="<?php echo $$value;?>">
                                                                </div>
                                                            </div>
                                                        <?php endif;?>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </section>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <section class="panel panel-primary">
                                    <header class="panel-heading text-center padding-5-10">
                                        <h2 class="panel-title font-size__16">Credit Card Information</h2>
                                    </header>
                                    <div class="panel-body">
                                        <?php
                                        $ec_fields = array(
                                            'cc_type' => array('name'=>'Card Type',),
                                            'cc_number' => array('name'=>'Card Number'),
                                            'cc_expiry_date' => array('name'=>'Expiry Date'),
                                            'cc_cvv' => array('name'=>'Security Code'),
                                        );

                                        $chap_fields = $ec_fields;
                                        ?>

                                        <?php

                                        $fields = ( $user_mtype == 'e' ) ? $ec_fields : $chap_fields;

                                        if(!empty($fields)) :
                                            foreach ($fields as $ecf_k => $ecf) :
                                                $value = !empty($ecf['value']) ? $ecf['value'] : $ecf_k;
                                                $title = !empty($ecf['name']) ? $ecf['name'] : ucfirst($ecf_k);
                                                ?>
                                                <div class="form-group">
                                                    <label class="col-xs-4 form-control-label" for="<?php echo $ecf_k;?>"><?php echo $title; ?></label>
                                                    <div class="col-xs-8">
                                                        <input type="text" class="form-control" id="<?php echo $ecf_k;?>" name="<?php echo $ecf_k;?>" value="<?php echo $$value;?>">
                                                    </div>
                                                </div>

                                            <?php endforeach; ?>
                                        <?php endif; ?>
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
                                    </div>
                                </section>

                                <!--<section class="panel panel-primary">
                                    <header class="panel-heading text-center padding-5-10">
                                        <h2 class="panel-title font-size__16">Chicago Chicago 2008</h2>
                                    </header>
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label class="col-xs-4 form-control-label" for="chick_attend">Yes, I would like to attend to Chicago Chicago 2008 show</label>
                                            <div class="col-xs-8">
                                                <input type="checkbox" name="chick_attend" id="chick_attend" value="yes" class="">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-xs-4 form-control-label" for="chick_tickets">How many beauty show tickets you need?</label>
                                            <div class="col-xs-8">
                                                <?php /*if($user_mtype == 'e') :*/?>
                                                    <select name="chick_tickets" id="chick_tickets" class="form-control"  onChange="if (this.value!='') {pop('contact_names_tickets.php?number='+this.value, 420, 400);}">
                                                        <option value="">-- Select -- </option>
                                                        <?php /*for($i=1; $i<16; $i++) :
                                                            if ($chick_tickets==$i) {$add='selected';} else { $add=''; }
                                                            echo '<option value="'.$i.'" '.$add.'>'.$i.'</option>';
                                                        endfor; */?>
                                                    </select>
                                                <?php /*else: */?>
                                                    <input type="text" class="form-control" name="chick_tickets" id="chick_tickets" value="<?php /*echo $chick_tickets;*/?>" size="5">
                                                <?php /*endif; */?>
                                            </div>
                                        </div>
                                        <?php /*if($user_mtype == 'e') :*/?>
                                            <div class="form-group">
                                                <label class="col-xs-4 form-control-label" for="chick_dinner_tickets">How many Breakfast/Lunch tickets you need?</label>
                                                <div class="col-xs-8">
                                                    <select name="chick_dinner_tickets" id="chick_dinner_tickets" class="form-control"  onChange="if (this.value!='') {pop('contact_names_dinner_tickets.php?number='+this.value, 420, 400);}">
                                                        <option value="">-- Select -- </option>
                                                        <?php /*for($i=1; $i<16; $i++) :
                                                            if ($chick_dinner_tickets==$i) {$add='selected';} else {$add='';}
                                                            echo '<option value="'.$i.'" '.$add.'>'.$i.'</option>';
                                                        endfor; */?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-xs-4 form-control-label" for="chick_dinner_tickets2">How many dinner tickets you need?</label>
                                                <div class="col-xs-8">
                                                    <?php /*if($user_mtype == 'e') :*/?>
                                                        <select name="chick_dinner_tickets2" id="chick_dinner_tickets2" class="form-control"  onChange="if (this.value!='') {pop('contact_names_dinner_tickets2.php?number='+this.value, 420, 400);}">
                                                            <option value="">-- Select -- </option>
                                                            <?php /*for($i=1; $i<16; $i++) :
                                                                if ($chick_dinner_tickets2==$i) {$add='selected';} else {$add='';}
                                                                echo '<option value="'.$i.'" '.$add.'>'.$i.'</option>';
                                                            endfor; */?>
                                                        </select>
                                                    <?php /*else: */?>
                                                        <input type="text" class="form-control" name="chick_dinner_tickets" id="chick_dinner_tickets" value="<?php /*echo $chick_dinner_tickets;*/?>" size="5">
                                                    <?php /*endif; */?>
                                                </div>
                                            </div>
                                        <?php /*endif; */?>

                                        <?php
/*                                        $ec_fields = array(
                                            'chick_guests1' => array('name'=>'Guests (beauty show tickets)',),
                                            'chick_guests2' => array('name'=>'Guests (Breakfast/Lunch tickets)',),
                                            'chick_guests3' => array('name'=>'Guests (Dinner tickets)',),
                                        );

                                        $chap_fields = array(
                                            'chick_guests' => array('name'=>'Guests',),
                                        );

                                        $fields = ( $user_mtype == 'e' ) ? $ec_fields : $chap_fields;

                                        if(!empty($fields)) :
                                            foreach ($fields as $ecf_k => $ecf) :
                                                $value = !empty($ecf['value']) ? $ecf['value'] : $ecf_k;
                                                $title = !empty($ecf['name']) ? $ecf['name'] : ucfirst($ecf_k);
                                                */?>
                                                <div class="form-group">
                                                    <label class="col-xs-4 form-control-label" for="<?php /*echo $ecf_k;*/?>"><?php /*echo $title; */?></label>
                                                    <div class="col-xs-8">
                                                        <textarea class="form-control" id="<?php /*echo $ecf_k;*/?>" name="<?php /*echo $ecf_k;*/?>" ><?php /*echo $$value;*/?></textarea>
                                                    </div>
                                                </div>
                                            <?php /*endforeach; */?>
                                        <?php /*endif; */?>
                                    </div>
                                </section>-->

                            </div>
                        </div>
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
            <!--<section class="panel">
                <div class="panel-body">
                    <?php
/*                    if ($_POST['chick_attend'] == 'yes') {

                        $q = mysqli_query($conn,"SELECT * FROM `zones` WHERE `zone_id`='".$_POST['entry_zone_id']."'");
                        $f = mysqli_fetch_array($q);
                    */?>
                        <iframe src="_chickago.php?member_id=<?/*=$customers_id*/?>&t=<?/*=$chick_tickets*/?>&td=<?/*=$chick_dinner_tickets*/?>&tbl=<?/*=$chick_dinner_tickets2*/?>&state=<?/*=$f['zone_name']*/?>&guests=<?/*=str_replace("\n", " ", str_replace("\r", " ",$chick_guests))*/?>" width="1" height="1"></iframe>
                    <?/* } */?>
                </div>
            </section>-->
        </div>
    </div>


<?php
//require_once("templates/footer.php");