<?php
$page_name = 'Manage Professional Pro';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

require_once("functions.php");
require_once("../common_files/Constant_contact/class.cc.php");

$mtype = 'm';

//debug(true, true, $_POST);

require_once( base_admin_path() . '/act_members.php' );


$member_type_name = 'Member';
$member_type_name_plural = 'Members';
$self_page = 'members.php';
$page_url = base_admin_url() . '/members.php?1=1';
$action_page = 'act_members.php';
$action_page_url = base_admin_url() . "/$self_page?1=1";
$export_url = base_admin_url() . '/members_export.php';

$members_page = true;


$is_edit = $is_add = false;
$success_message = $error_message = $filters = array();

$designations = !empty($_REQUEST['designations']) ? (!is_numeric($_REQUEST['designations']) ? NULL : $_REQUEST['designations']) : NULL;
$amico_id_filter = !empty($_REQUEST['amico_id_filter']) ? filter_var($_REQUEST['amico_id_filter'], FILTER_SANITIZE_STRING) : '';
//$sort = ( !empty($_REQUEST['sort']) ?  $_REQUEST['sort'] : '' );
$sort = (isset($_REQUEST['sort']) && is_numeric($_REQUEST['sort']) ? $_REQUEST['sort'] : '');
$alpabet = (!empty($_REQUEST['alpabet']) ? filter_var($_REQUEST['alpabet'], FILTER_SANITIZE_STRING) : '');
$page = ((!empty($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1);
$name_filter_first = !empty($_REQUEST['name_filter_first']) ? filter_var($_REQUEST['name_filter_first'], FILTER_SANITIZE_STRING) : '';
$name_filter_last = !empty($_REQUEST['name_filter_last']) ? filter_var($_REQUEST['name_filter_last'], FILTER_SANITIZE_STRING) : '';

//debug(true, true, $designations, $sort, $alpabet);

$filters['designations'] = $designations;
$filters['amico_id_filter'] = $amico_id_filter;
$filters['name_filter_first'] = $name_filter_first;
$filters['name_filter_last'] = $name_filter_last;
$filters['sort'] = $sort;
$filters['alpabet'] = $alpabet;
$filters['page'] = $page;


if( !empty($_POST['goto']) && ($_POST['goto'] == 'commission_calculate') ) {

    $calculate_commission_token_file_path = base_admin_temp_path() . "/calculate_commission";
    if(file_exists( $calculate_commission_token_file_path )) {
        unlink( $calculate_commission_token_file_path );
    }

    if( file_put_contents($calculate_commission_token_file_path, $_SESSION['admin']['ses_admin_email']) ) {
        $_SESSION['admin']['calculated_members_commission'] = true;
    }
}


if( (!empty($_POST['memberid']) && !empty($_POST['goto']) && ($_POST['goto'] == 'update') ) || (!empty($_GET['mid']) && validate_memberid($_GET['mid']) ) ) {
    $memberId = !empty( $_POST['memberid'] ) ? $_POST['memberid'] : $_GET['mid'];

    $rsselmember = mysqli_query($conn, "select int_member_id, int_parent_id, int_customer_id, int_designation_id, str_title, dat_last_visit,bit_active, ec_id, bit_no_purchase_required, bit_custom_comission, expire_custom_comission, new_ec_id, amico_id, nickname, DATE_FORMAT(reg_date, '%m/%d/%Y') as reg_data from tbl_member WHERE int_member_id = {$memberId}");
    list($memberid, $refer_member_id, $customerid, $designation, $title, $lastvisit, $active, $ec_id, $bit_no_purchase_required, $bit_custom_comission, $expire_custom_comission, $new_ec_id, $amigo_id, $nickname, $mem_join_data) = mysqli_fetch_row($rsselmember);

    $result = mysqli_query($conn,"SELECT amico_id FROM tbl_member WHERE int_member_id = '$refer_member_id'");
    //echo '<pre>'; var_dump($refer_member_id, $referring_memberid); die();
    $referrer_amico_id = mysqli_result($result, 0);

    $referrer_name_query = mysqli_query($conn,"SELECT c.customers_firstname, c.customers_lastname, tm.int_member_id, tm.int_customer_id, tm.amico_id FROM tbl_member tm INNER JOIN customers c ON tm.int_customer_id=c.customers_id WHERE  tm.mtype='$mtype' and tm.amico_id='$referrer_amico_id' ORDER BY c.customers_lastname");
    list($referrer_first_name, $referrer_last_name) = mysqli_fetch_row($referrer_name_query);

    $timestatps_query = mysqli_query($conn,"SELECT * FROM `tbl_member_ec` WHERE `amico_id`='".$amigo_id."' ORDER BY timestamp asc LIMIT 1");
    $f=mysqli_fetch_array($query);
    $ec_join_date = substr($f['timestamp'], 0, 10);

    $ec_query = mysqli_query($conn,"SELECT c.customers_firstname, c.customers_lastname, tm.int_member_id, tm.int_customer_id, tm.amico_id FROM tbl_member tm INNER JOIN customers c ON tm.int_customer_id=c.customers_id WHERE  tm.mtype='e' and tm.amico_id ='$ec_id' ORDER BY c.customers_lastname");
    list($ec_first_name, $ec_last_name, $ec_member_id) = mysqli_fetch_row($ec_query);

    $new_ec_query = mysqli_query($conn,"SELECT c.customers_firstname, c.customers_lastname, tm.int_member_id, tm.int_customer_id, tm.amico_id FROM tbl_member tm INNER JOIN customers c ON tm.int_customer_id=c.customers_id WHERE  tm.mtype='e' and tm.amico_id ='$new_ec_id' ORDER BY c.customers_lastname");
    list($new_ec_first_name, $new_ec_last_name, $new_ec_member_id) = mysqli_fetch_row($new_ec_query);

    $rsselcustomer = mysqli_query($conn, "select customers_id, customers_email_address, customers_telephone, customers_fax, customers_password, ssn, license_number, type,mobile_phone, operator_id  from customers WHERE customers_id = $customerid");
    list($customerid, $email, $phone, $fax, $password, $ssn, $license_number, $type, $mobile_phone, $operator_id) = mysqli_fetch_row($rsselcustomer);

    $rsseladdress1 = mysqli_query($conn, "select entry_company, entry_firstname, entry_lastname, entry_street_address, entry_street_address2,entry_postcode, entry_city, entry_country_id, entry_zone_id from address_book WHERE customers_id = $customerid and address_book_id=1");
    list($company, $firstname, $lastname, $streetadd, $streetadd_two, $postcode, $city, $country, $zone) = mysqli_fetch_row($rsseladdress1);

    $rsseladdress2 = mysqli_query($conn, "select entry_company, entry_firstname, entry_lastname, entry_street_address,entry_street_address2, entry_postcode, entry_city, entry_country_id, entry_zone_id from address_book WHERE customers_id = $customerid and address_book_id=2");
    list($sh_company, $sh_firstname, $sh_lastname, $sh_streetadd, $sh_streetadd_two, $sh_postcode, $sh_city, $sh_country, $sh_zone) = mysqli_fetch_row($rsseladdress2);

    $rsseladdress3 = mysqli_query($conn, "select entry_street_address2, entry_firstname, entry_lastname, entry_street_address,entry_street_address2, entry_postcode, entry_city, entry_country_id, entry_zone_id from address_book WHERE customers_id = $customerid and address_book_id=3");
    list($check_company, $check_firstname, $check_lastname, $check_streetadd, $check_streetadd_two, $check_postcode, $check_city, $check_country, $check_zone) = mysqli_fetch_row($rsseladdress3);

    $nr = "NO";

    $is_edit = true;

} elseif ( empty($_POST['memberid']) && !empty($_POST['goto']) && ($_POST['goto'] == 'add') ) {
    $is_add = true;
} else {

    $limit = 30;

    $limit_start = ($page * $limit) - $limit;
    $limit_end = ($page * $limit);


    $conditions = $sortby = array();

    $sql = "SELECT ";
    //$sql .= "DISTINCT ec_mem.amico_id, ec_mem.timestamp, ";
    $sql .= "sub.subscribed, mem.int_member_id, mem.int_designation_id,mem.amico_id,cus.customers_id,cus.customers_email_address,cus.customers_firstname,cus.customers_lastname,CONCAT(cus.customers_firstname,',',cus.customers_lastname) as fullname,mem.bit_active, mem.ec_id, mem.new_ec_id
        FROM tbl_member as mem
    ";
    //$sql .= " INNER JOIN tbl_member_ec as ec_mem ON ec_mem.amico_id = mem.amico_id ";
    $sql .= " LEFT JOIN customers as cus ON mem.int_customer_id = cus.customers_id ";
    $sql .= " LEFT JOIN tbl_member_subscription as sub ON mem.int_member_id = sub.sub_member_id ";

    $groupby = " GROUP BY mem.int_member_id ";
    $sortby = '';
    //$sortby = "ORDER BY LEFT(cus.customers_firstname, 1), LEFT(cus.customers_lastname, 1) ASC";
    $sortby = "ORDER BY (CASE WHEN cus.customers_firstname IS NULL then 'a' ELSE 'z' END) DESC, TRIM(cus.customers_firstname)  ASC ";

    //debug(true, true, $designations, ( in_array($designations, array('', NULL, null, false)) ) );
    $conditions[] = "mem.mtype='$mtype'";
    //$conditions['alpha'] = " cus.customers_firstname LIKE('A%') ";

    if (is_numeric($designations)) {
        $conditions['designation'] = "mem.int_designation_id='$designations'";
    }
    if (!empty($alpabet) && ($alpabet !== 'ALL')) {
        if ($sort == 0) {
            $conditions['alpha'] = "cus.customers_firstname LIKE('$alpabet%')";
            $sortby = "ORDER BY TRIM(cus.customers_firstname) ASC";
        }
        elseif ($sort == 1) {
            $conditions['alpha'] = "cus.customers_lastname LIKE('$alpabet%')";
            $sortby = "ORDER BY TRIM(cus.customers_lastname) ASC";
        }
    }
    if( !empty($amico_id_filter) ) {
        //unset( $conditions['designation'], $conditions['alpha'] );
        $conditions[] = "mem.amico_id LIKE '%$amico_id_filter%'";
    }
    if( !empty($name_filter_first) ) {
        $conditions[] = "cus.customers_firstname LIKE '%$name_filter_first%'";
    }
    if( !empty($name_filter_last) ) {
        $conditions[] = "cus.customers_lastname LIKE '%$name_filter_last%'";
    }

    //$sortby .= ", LEFT(mem.amico_id, 1) ASC ";

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    $sql .= " $groupby $sortby ";


    $field_details = array(
        //'fullname' => 'First Name,Last Name',
        'customers_firstname' => 'First Name',
        'customers_lastname' => 'Last Name',
        'customers_email_address' => 'Email Address',
        'amico_id' => 'Amico ID',
        'timestamp' => 'Join Date',
        'ec_id' => 'Ec ID',
        'bit_active' => 'Active?',
        'actions' => 'Commands',
    );

    $id_field = 'int_member_id';


    //echo $sql; die();

    //$query_pag_data = " $condition LIMIT $start, $per_page";
    $data_num_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

    mysqli_store_result($conn);
    $numrows = mysqli_num_rows($data_num_query);

    //echo $sql;

    $sql .= " LIMIT $limit OFFSET $limit_start ";
    //echo $sql;
    $data_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

}

?>
    <script type="text/javascript">
        $(function () {
            $('[name=expire_custom_comission]').datepicker({
                dateFormat: 'yy-mm-dd',
                defaultDate: $('[name=expire_custom_comission]').val()
            });

        });
    </script>
    <script type="text/javascript">
        /*function loadData(page) {

            var alpha = $('#alpha').val();
            var designation = $('#designation').val();
            var sorti = $('input:radio[name=sort]:checked').val();
            //loading_show();
            $.ajax
            ({
                type: "POST",
                url: "load_data.php",
                data: {
                    page: page,
                    alpha: alpha,
                    designation: designation,
                    sorti: sorti
                },
                beforeSend: function () {

                    $('#loading').show();
                },
                success: function (msg) {
                    $('#loading').hide();
                    $("#container").html(msg);
                }
            });
        }
        $(document).ready(function () {
            loadData(1);
            function loading_show() {
                $('#loading').html("<img src='images/loading.gif'/>").fadeIn('fast');
            }

            function loading_hide() {
                $('#loading').fadeOut('fast');
            }


            // For first time page load default results
            //console.log($('#container .pagination li.active'));
            jQuery('.members_data').on('click', '#container .pagination li', function () {
                var page = $(this).attr('p');
                loadData(page);
            });
            $('.members_data').on('click', '#go_btn', function () {
                var page = parseInt($('.goto').val());
                var no_of_pages = parseInt($('.total').attr('a'));
                if (page != 0 && page <= no_of_pages) {
                    loadData(page);
                } else {
                    alert('Enter a PAGE between 1 and ' + no_of_pages);
                    $('.goto').val("").focus();
                    return false;
                }

            });
        });*/

    </script>
    <script language="JavaScript">
        <!--

        function confirmCleanUp(Link) {
            if (confirm("Are you sure you want to delete this Member? \n\nThis action cannot be undone!!\nThe children of this member, if any, will be placed under his parent.")) {
                location.href = Link;
            }
        }

        function changevalues(theform) {
            if (theform.shipping.value != "") {
                theform.shiped.value = theform.shipping.value;
            }
            else {
                theform.shiped.value = "";
            }
        }

        function Validate(theform) {


            if (isEmpty(theform.refer_member_id.value)) {
                theform.refer_member_id.value = 0;
            }
            if (isEmpty(theform.firstname.value)) {
                alert("Please enter the First Name");
                theform.firstname.focus();
                return false;
            }
            if (isEmpty(theform.lastname.value)) {
                alert("Please enter the Last Name");
                theform.lastname.focus();
                return false;
            }
            if (isEmpty(theform.title.value)) {
                alert("Please enter the Title");
                theform.title.focus();
                return false;
            }
            if (isEmpty(theform.email.value)) {
                alert("Please enter the Email Address");
                theform.email.focus();
                return false;
            }
            if (theform.email.value.length != 0) {
                var retval = emailCheck(theform.email.value)
                if (retval == false) {
                    theform.email.focus();
                    return false;
                }
            }
            /*if(isEmpty(theform.streetadd.value)){
             alert("Please enter the Street Address");
             theform.streetadd.focus();
             return false;
             }*/
            if (isEmpty(theform.postcode.value)) {
                alert("Please enter the Postcode");
                theform.postcode.focus();
                return false;
            }
            if (isEmpty(theform.city.value)) {
                alert("Please enter the City");
                theform.city.focus();
                return false;
            }
            if (theform.zone.value <= 0) {
                alert("Please select a State");
                theform.zone.focus();
                return false;
            }
            /*if(theform.country.value<=0){
             alert("Please select a Country");
             theform.country.focus();
             return false;
             } */
            if (isEmpty(theform.phone.value)) {
                alert("Please enter the Phone Number");
                theform.phone.focus();
                return false;
            }
            if (theform.shipping.checked == false) {
                if (isEmpty(theform.sh_firstname.value)) {
                    alert("Please enter the First Name");
                    theform.sh_firstname.focus();
                    return false;
                }
                if (isEmpty(theform.sh_lastname.value)) {
                    alert("Please enter the Last Name");
                    theform.sh_lastname.focus();
                    return false;
                }
                if (isEmpty(theform.sh_streetadd.value)) {
                    alert("Please enter the Street Address");
                    theform.sh_streetadd.focus();
                    return false;
                }
                if (isEmpty(theform.sh_postcode.value)) {
                    alert("Please enter the Postcode");
                    theform.sh_postcode.focus();
                    return false;
                }
                if (isEmpty(theform.sh_city.value)) {
                    alert("Please enter the City");
                    theform.sh_city.focus();
                    return false;
                }
                if (theform.sh_zone.value <= 0) {
                    alert("Please select a State");
                    theform.sh_zone.focus();
                    return false;
                }
                /*if(theform.sh_country.value<=0){
                 alert("Please select a Country");
                 theform.sh_country.focus();
                 return false;
                 } */

            }
            if (!isEmpty(theform.pass.value)) {
                var retval = passwordCheck(theform.pass.value)
                if (retval == false) {
                    theform.pass.focus();
                    return false;
                }
            }
            if (isEmpty(theform.confirmpass.value)) {
                var retval = passwordCheck(theform.pass.value)
                if (retval == false) {
                    theform.pass.focus();
                    return false;
                }
            }
            if (theform.pass.value != theform.confirmpass.value) {
                alert("Both the passwords should be same");
                theform.pass.focus();
                return false;
            }


            return true;
        }   //-->

        function copyBillingToShipping() {

            theform.sh_firstname.value = theform.firstname.value;
            theform.sh_lastname.value = theform.lastname.value;
            theform.sh_streetadd.value = theform.streetadd.value;
            theform.sh_streetadd_two.value = theform.streetadd_two.value;
            theform.sh_postcode.value = theform.postcode.value;
            theform.sh_city.value = theform.city.value;
            theform.sh_zone.selectedIndex = theform.zone.selectedIndex;

        }

        function copyBillingToCheckAddress() {

            theform.check_firstname.value = theform.firstname.value;
            theform.check_lastname.value = theform.lastname.value;
            theform.check_streetadd.value = theform.streetadd.value;
            theform.check_streetadd_two.value = theform.streetadd_two.value;
            theform.check_postcode.value = theform.postcode.value;
            theform.check_city.value = theform.city.value;
            theform.check_zone.selectedIndex = theform.zone.selectedIndex;

        }

    </script>


<?php

if (isset($_POST['memberid']) and $_POST['memberid'] > 0) {


}
else {
    $nr = "YES";
    $password = "";
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

        <?php if( !empty($_SESSION['member_form_success']) || !empty($_SESSION['member_form_errors']) ):?>
            <div class="row ">
                <section class="panel">
                    <div class="col-md-8 col-xs-12 centering">
                        <?php if(!empty($_SESSION['member_form_errors'])): ?>
                            <div class="alert alert-danger">
                                <?php if(is_array($_SESSION['member_form_errors'])) : ?>
                                    <ul><li><?php echo implode('</li><li>', $_SESSION['member_form_errors']);?></li></ul>
                                <?php else : ?>
                                    <p><?php echo $_SESSION['member_form_errors']; ?></p>
                                <?php endif; ?>
                            </div>
                            <?php unset($_SESSION['member_form_errors']); ?>
                        <?php endif;?>
                        <?php if(!empty($_SESSION['member_form_success'])): ?>
                            <div class="alert alert-success">
                                <?php if(is_array($_SESSION['member_form_success'])) : ?>
                                    <ul><li><?php echo implode('</li><li>', $_SESSION['member_form_success']);?></li></ul>
                                <?php else : ?>
                                    <p><?php echo $_SESSION['member_form_success']; ?></p>
                                <?php endif; ?>
                            </div>
                            <?php unset($_SESSION['member_form_success']); ?>
                        <?php endif;?>
                    </div>
                </section>
            </div>
        <?php endif; ?>

        <div class="row ">
            <section class="panel">
                <div class="col-md-8 col-xs-12 centering">
                    <?php if(empty($_POST['memberid']) && !$is_edit && !$is_add ): ?>
                        <!-- Buttons -->
                        <div class="panel-body">
                            <div class="row">
                                <?php
                                if(!empty($_SESSION['admin']['calculated_members_commission'])) {
                                    ?>
                                    <div class="col-xs-12">
                                        <div class="alert alert-success">
                                            Commission and Sales will be calculated at midnight time of the server. You will be notified in your email right after the process gets completed.
                                        </div>
                                    </div>
                                    <?php
                                    unset($_SESSION['admin']['calculated_members_commission']);
                                }
                                ?>
                                <div class="col-xs-12 header_buttons_wrapper text-center centering">
                                    <div class="col-xs- add_button_wrapper centering">
                                        <form action="<?php echo $self_page; ?>" method="post">
                                            <input type="hidden" name="alpabet" value="<?php echo $alpabet; ?>">
                                            <input type="hidden" name="designations" value="<?php echo $designations; ?>">
                                            <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                                            <input type="hidden" name="page" value="<?php echo $page; ?>">

                                            <input type="hidden" name="amico_id_filter" value="<?php echo $amico_id_filter; ?>">
                                            <input type="hidden" name="name_filter_first" value="<?php echo $name_filter_first; ?>">
                                            <input type="hidden" name="name_filter_last" value="<?php echo $name_filter_last; ?>">

                                            <input type="hidden" name="goto" value="add">
                                            <input type="submit" name="add" value="Add New <?php echo $member_type_name; ?>" class="command">
                                        </form>
                                    </div>
                                    <?php
                                    $prevmonth=(date("m")-1);
                                    $rsdate=mysqli_query($conn,"select dtt_calculate from tbl_commision_sales_history group by(dtt_calculate) ORDER BY dtt_calculate DESC LIMIT 1");
                                    list($dates_calculate)=mysqli_fetch_row($rsdate);
                                    $latestdate = $dates_calculate;

                                    /*$rsdate=mysqli_query($conn,"select DISTINCT dtt_calculate from tbl_commision_sales_history group by(dtt_calculate)");

                                    while(list($notusedsales, $dates_calculate)=mysqli_fetch_row($rsdate)){
                                        if($dates_calculate > $latestdate) {
                                            $latestdate = $dates_calculate;
                                        }
                                    }*/

                                    list($latestyear,$latestmonth,$latestday) = explode('-',$latestdate);
                                    $today = date("F/m/d/Y",mktime(0,0,0,($latestmonth-1),$latestday,$latestyear));
                                    list($str_presmonth,$int_presmonth,$presday,$presyear) = explode('/',$today);
                                    if($prevmonth == ($latestmonth-1)){
                                        $buttontitle="Commision and Sales had been calculated upto ".$str_presmonth;
                                    } else {
                                        $buttontitle="Commision and Sales yet to be calculated for ".$str_presmonth;
                                        ?>
                                        <div class="col-xs- export_button_wrapper centering">
                                            <!--<form action="./act_calculate_commision.php" method="post">-->
                                            <form action="<?php echo $self_page;?>" method="post">
                                                <input type="hidden" name="goto" value="commission_calculate" />
                                                <input type="submit" name="commision" value="Calculate Commision" class="command mr-lg" title="<?=$buttontitle?>">
                                            </form>
                                        </div>
                                    <?php } ?>

                                    <div class="col-xs- export_button_wrapper centering">
                                        <input type="button" onclick="document.location.href='members_export.php'" value="Export Active <?php echo $member_type_name_plural; ?>" class="command">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Buttons -->
                    <?php else: ?>
                        <!-- Add Form -->
                        <?php include_once('page.members_addedit_form.php'); ?>
                        <!-- /Add Form -->
                    <?php endif; ?>
                </div>
                <div class="clearfix"></div>
            </section>
            <?php if(empty($_POST['memberid']) and  !$is_edit && !$is_add ): ?>
                <section class="panel">
                    <div class="col-xs-12">
                        <header class="panel-heading">
                            <h2 class="panel-title text-center">List of <?php echo $member_type_name_plural; ?></h2>
                        </header>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-12 filter_wrapper ">
                                    <div class="table-responsive">
                                        <div class="col-lg-6 col-md-10 col-sm-12 col-xs-12 centering date_range_wrapper">
                                            <form action="<?php echo $self_page; ?>" method="post">
                                                <table class="table table-bordered table-striped mb-none">
                                                    <tr>
                                                        <td>Designation:</td>
                                                        <td>
                                                            <select name="designations" id="designation">
                                                                <option value="none">Please Select a Designation</option>
                                                                <option value="0" <?php echo ( empty($designations) ? 'selected' : '' ); ?>>All</option>
                                                                <?php
                                                                $rs_desig = mysqli_query($conn, "select int_designation_id, str_designation from tbl_designation");
                                                                while (list($i_desig, $s_desig) = mysqli_fetch_row($rs_desig)) {
                                                                    if (!empty($designations) && ($i_desig == $designations)) {
                                                                        echo '<option value =' . $i_desig . ' selected>' . $s_desig . '</option>';
                                                                    }
                                                                    else {
                                                                        echo '<option value =' . $i_desig . ' >' . $s_desig . '</option>';
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Name Filter:</td>
                                                        <td>
                                                            <div class="">
                                                                <input type="Radio" name="sort" id="sortFirst" value="0" <?php if($sort=='0'){echo 'checked';} ?> ><label for="sortFirst">First Name Starts with</label>
                                                            </div>
                                                            <div class="">
                                                                <input type="Radio" name="sort" id="sortLast" value="1" <?php if($sort=='1'){echo 'checked';} ?> ><label for="sortLast">Last Name Starts with</label>
                                                            </div>
                                                            <div class="">
                                                                <select name="alpabet" id="alpha">
                                                                    <option value="">ALL</option>
                                                                    <?php
                                                                    $atoz = range('A', 'Z');
                                                                    foreach($atoz as $alpha) {
                                                                        echo '<option value="' . $alpha . '" ' . ((!empty($alpabet) && ($alpabet == $alpha)) ? 'selected' : '') . ' >' . $alpha . '</option>';
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><label for="name_filter_first">Name Filter:</label></td>
                                                        <td>
                                                            <div class="row">
                                                                <div class="col-xs-6">
                                                                    <input type="text" class="form-control" name="name_filter_first" id="name_filter_first" value="<?php echo (!empty($name_filter_first) ? $name_filter_first : ''); ?>" placeholder="First Name"/>
                                                                </div>
                                                                <div class="col-xs-6">
                                                                    <input type="text" class="form-control" name="name_filter_last" id="name_filter_last" value="<?php echo (!empty($name_filter_last) ? $name_filter_last : ''); ?>" placeholder="Last Name"/>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <!--<tr><td colspan="2"><p style="text-align: center">OR</p></td></tr>-->
                                                    <tr>
                                                        <td>Amico ID:</td>
                                                        <td>
                                                            <div class="row col-xs-10">
                                                                <input type="text" class="form-control" name="amico_id_filter" id="amico_id_filter" value="<?php echo (!empty($amico_id_filter) ? $amico_id_filter : ''); ?>" />
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2">
                                                            <div class="row">
                                                                <div class="col-xs-12 text-center">
                                                                    <input type="submit" class="command btn btn-sm col-xs-4 centering" name="go" value="GO!">
                                                                </div>
                                                            </div>
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
            <?php endif; ?>
        </div>
    </div>


<?php
require_once("templates/footer.php");
