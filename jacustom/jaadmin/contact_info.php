<?php
$page_name = 'Manage Customers';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");
require_once("functions.php");
require_once("../common_files/Constant_contact/class.cc.php");

$mtype = 'c';


$member_type_name = 'Customer';
$member_type_name_plural = 'Customers';
$self_page = 'contact_info.php';
$page_url = base_admin_url() . '/contact_info.php?1=1';
$action_page = 'act_members.php';
$action_page_url = base_admin_url() . '/contact_info.php?1=1';
$export_url = base_admin_url() . '/contact_info_export.php';


if(isset($_POST['update'])){
    $table = "customers";
    $fieldlist="customers_firstname='{$_POST['firstname']}', customers_lastname='{$_POST['lastname']}', customers_email_address='{$_POST['email']}', customers_telephone='{$_POST['phone']}', customers_fax='{$_POST['fax']}', customers_password='{$_POST['password']}' ";

    $condition=" where customers_id = {$_POST['customerid']}";
    $result=update_rows($conn, $table, $fieldlist, $condition);
    $customer_id= $_POST['customerid'];
    $table = "address_book";
    $fieldlist="entry_company='{$_POST['company']}', entry_firstname='{$_POST['firstname']}', entry_lastname='{$_POST['lastname']}', entry_street_address='{$_POST['streetadd']}', entry_postcode='{$_POST['postcode']}', entry_city='{$_POST['city']}', entry_country_id='{$_POST['country']}', entry_zone_id='{$_POST['zone']}'";
    $condition=" where customers_id = {$_POST['customerid']} and address_book_id=1";
    $result=update_rows($conn, $table, $fieldlist, $condition);
    if($_POST['ismember']==1){
        if($_POST['newmemberid']==""){
            $newmemberid=0;
        }
        else{
            $newmemberid=$_POST['newmemberid'];
        }
        $table = "tbl_member";				// inserting values to setting table
        $in_fieldlist="int_parent_id,int_designation_id,int_customer_id,str_title,dat_last_visit,bit_active";
        $in_values="$newmemberid,1,{$_POST['customerid']},'Mr','$today',1";
        $result=insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values
    }

    $rscustomer1=mysqli_query($conn,"select * from tbl_customer_notes where int_customer_id=".$_POST['customerid']);
    $today = date("Y/m/d");
    if(mysqli_num_rows($rscustomer1)>0){
        $table = "tbl_customer_notes";
        $fieldlist="str_comments='".$_POST['comments']."',str_notes='".$_POST['notes']."',dtt_notes='".$today."'";
        $condition=" where int_customer_id = {$_POST['customerid']}";
        $result=update_rows($conn, $table, $fieldlist, $condition);
    }
    else{
        $table = "tbl_customer_notes";				// inserting values to setting table
        $in_fieldlist="int_customer_id,str_comments,str_notes,dtt_notes,bit_active";
        $in_values="{$_POST['customerid']}, '{$_POST['comments']}', '{$_POST['notes']}','$today',1";
        $result=insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values
    }
    header("Location: contact_info.php");
}


$no_delete_butotn = true;

$limit = 50;
$page = ( ( !empty($_GET['page']) && is_numeric($_GET['page']) ) ? $_GET['page'] : 1 );

$limit_start = ($page * $limit) - $limit;
$limit_end = ($page * $limit);


$conditions = $sortby = array();
$designations = !empty($_REQUEST['designations']) ? ( !is_numeric($_REQUEST['designations']) ? NULL : $_REQUEST['designations']) : NULL;
//$sort = ( !empty($_REQUEST['sort']) ?  $_REQUEST['sort'] : '' );
$sort = ( isset($_REQUEST['sort']) && is_numeric($_REQUEST['sort']) ?  $_REQUEST['sort'] : '' );
$alpabet = ( !empty($_REQUEST['alpabet']) ? filter_var($_REQUEST['alpabet'], FILTER_SANITIZE_STRING) : 'A' ) ;


$sql = "select mem.int_member_id,mem.int_designation_id,cus.customers_id,cus.customers_email_address,cus.customers_firstname,cus.customers_lastname,CONCAT(cus.customers_firstname,',',cus.customers_lastname) AS fullname,mem.bit_active,n.str_comments,n.str_notes,n.dtt_notes
        from customers cus
        inner join tbl_member mem on mem.int_customer_id=cus.customers_id
        left outer join tbl_customer_notes n on n.int_customer_id=cus.customers_id
";

$sortby = "ORDER BY cus.customers_firstname ";

//debug(true, true, $designations, ( in_array($designations, array('', NULL, null, false)) ) );


if( $sort == 0 ) {
    $sortby = "ORDER BY cus.customers_firstname ";
} elseif ($sort == 1) {
    $sortby = "ORDER BY cus.customers_lastname ";
}


if(!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$sql .= " $sortby ";


$field_details = array(
    'fullname' => 'First Name,Last Name',
    'customers_email_address' => 'Email Address',
    'actions' => 'Commands',
);

$id_field = 'customers_id';


//$query_pag_data = " $condition LIMIT $start, $per_page";
$data_num_query = mysqli_query($conn,$sql) or die('MySql Error' . mysqli_error($conn));

mysqli_store_result($conn);
$numrows = mysqli_num_rows($data_num_query);

//echo $sql;

$sql .= " LIMIT $limit OFFSET $limit_start ";
//echo $sql;
$data_query = mysqli_query($conn,$sql) or die('MySql Error' . mysqli_error($conn));

?>


<script language="JavaScript" src="<?php echo base_js_url(); ?>/form_check.js"></script>
<script language="JavaScript">
<!--

function confirmCleanUp(Link) {
   if (confirm("Are you sure you want to delete this Customer? \n\n(This action cannot be undone)\nThe childs of this member will be put under the parent of this member")) {
      location.href=Link;
   }
}
//ADDED FOR WAIT
function open_wait_window(msg)
{
 var left = (screen.width-500)/2;
 var top = (screen.height-40)/2;

 waitwin = window.open('wait.php?msg='+msg, null, 'width=500, height=40, left='+left+', top='+top);
 
 return;
}
///END
function changevalues(theform)
{
 if (theform.shipping.value !=""){
 	theform.shiped.value = theform.shipping.value;}
 else{
 	theform.shiped.value ="";	
 }
}
function enableid(theform){

	if(theform.ismember.checked){
		theform.newmemberid.disabled=false;
		theform.newmemberid.focus();
	}
	else{
		theform.newmemberid.value="";
		theform.newmemberid.disabled=true;
	}
	
}
function Validate(theform)
{
	if(isEmpty(theform.firstname.value)){
    	alert("Please enter the First Name");
		theform.firstname.focus();
	    return false;
	} 	
	if(isEmpty(theform.lastname.value)){
    	alert("Please enter the Last Name");
		theform.lastname.focus();
	    return false;
	}
	if(isEmpty(theform.email.value)){
    	alert("Please enter the Email Address");
		theform.email.focus();
	    return false;
	} 
	if(theform.email.value.length != 0 ){
	  var retval = emailCheck(theform.email.value)
	  if (retval == false){
		 theform.email.focus();
  		 return false;
	  }	  	
	 }
	if(isEmpty(theform.streetadd.value)){
    	alert("Please enter the Street Address");
		theform.streetadd.focus();
	    return false;
	}
	if(isEmpty(theform.postcode.value)){
    	alert("Please enter the Postcode");
		theform.postcode.focus();
	    return false;
	}  	
	if(isEmpty(theform.city.value)){
    	alert("Please enter the City");
		theform.city.focus();
	    return false;
	} 
	if(theform.zone.value<=0){
    	alert("Please select a State");
		theform.zone.focus();
	    return false;
	} 
	if(theform.country.value<=0){
    	alert("Please select a Country");
		theform.country.focus();
	    return false;
	} 
	if(isEmpty(theform.phone.value)){
    	alert("Please enter the Phone Number");
		theform.phone.focus();
	    return false;
	}
	return true;
}	//-->

</script>


<?php 	

if(isset($_POST['customerid']) and $_POST['customerid'] > 0){
   $rsselcustomer = mysqli_query($conn,"select c.customers_id, c.customers_email_address, c.customers_telephone, c.customers_fax, c.customers_password,n.str_comments,n.str_notes,n.dtt_notes from customers c left outer join tbl_customer_notes n on c.customers_id=n.int_customer_id WHERE c.customers_id = {$_POST['customerid']}");
   list($customerid,$email,$phone,$fax,$password,$comments,$notes,$notesdate)= mysqli_fetch_row($rsselcustomer);
      
   $rsseladdress1 = mysqli_query($conn,"select entry_company, entry_firstname, entry_lastname, entry_street_address, entry_postcode, entry_city, entry_country_id, entry_zone_id from address_book WHERE customers_id = $customerid and address_book_id=1");
   list($company,$firstname,$lastname,$streetadd,$postcode,$city,$country,$zone)= mysqli_fetch_row($rsseladdress1);
   
   $rsseladdress2 = mysqli_query($conn,"select entry_company, entry_firstname, entry_lastname, entry_street_address, entry_postcode, entry_city, entry_country_id, entry_zone_id from address_book WHERE customers_id = $customerid and address_book_id=2");
   list($sh_company, $sh_firstname,$sh_lastname,$sh_streetadd,$sh_postcode,$sh_city,$sh_country,$sh_zone)= mysqli_fetch_row($rsseladdress2);
   $nr = "NO";
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
            <div class="col-xs-12">
                <div class="panel-body">

                    <div class="row">
                        <div class="col-xs-12 filter_wrapper ">
                            <div class="table-responsive">
                                <div class="col-lg-6 col-md-10 col-sm-12 col-xs-12 centering date_range_wrapper">
                                    <form action="<?php echo $self_page; ?>" method="post">
                                        <table class="table table-bordered table-striped mb-none">
                                            <tr>
                                                <td>Sort By:</td>
                                                <td>
                                                    <div class="">
                                                        <input type="Radio" name="sort" id="sortFirst" value="0" <?php if($sort=='0'){echo 'checked';} ?> ><label for="sortFirst"> First Name </label>
                                                    </div>
                                                    <div class="">
                                                        <input type="Radio" name="sort" id="sortLast" value="1" <?php if($sort=='1'){echo 'checked';} ?> ><label for="sortLast"> Last Name </label>
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
        </div>


    </div>


<?php
require_once("templates/footer.php");