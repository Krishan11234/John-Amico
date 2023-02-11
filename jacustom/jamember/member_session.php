<?php

//debug(true, true,$amico_id, file_exists( '../common_files/include/global.inc') );
if( !function_exists('open_db') ) {
    require_once( dirname(__FILE__) . '/../common_files/include/global.inc');
}

$query= mysqli_query($conn, "select a.*, ctry.countries_name, ctry.countries_iso_code_2, ctry.countries_iso_code_3 from address_book a, countries ctry where a.customers_id = '" . $_SESSION['member']['ses_customer_id'] . "' and address_book_id = '1' and  ctry.countries_id = a.entry_country_id");
list($customerid,$bookid,$genderid,$company,$firstname,$lastname,$streetaddress,$suburb,$postcode,$city,$state,$countryid,$zoneid,$countryname,$cntryiso2,$cntryiso3 )=mysqli_fetch_row($query);




//Id Customers
$_SESSION['member']['is_member'] = true;
$_SESSION['member']['ses_customers_id'] = $customerid;
$_SESSION['member']['customers_type']="REG";
//Billing Information
$_SESSION['member']['customers_name']=$firstname." ".$lastname;
$_SESSION['member']['customers_firstname']=$firstname;
$_SESSION['member']['customers_lastname']=$lastname;
$_SESSION['member']['customers_company']=$company;
$_SESSION['member']['customers_street_address']=$streetaddress;
$_SESSION['member']['customers_telephone']= ( !empty($phone) ? $phone : '');
$_SESSION['member']['customers_fax']=( !empty($fax) ? $fax : '');
$_SESSION['member']['customers_city']=$city;
$_SESSION['member']['customers_postcode']=$postcode;

if($zoneid==0){
$_SESSION['member']['customers_state']=$state;
$zoneid=0;
$_SESSION['member']['customers_zone_name']='';
}else{
$_SESSION['member']['customers_state']='';
$_SESSION['member']['customers_zone_id']=$zoneid;
//$customers_zone_name=;
}
$_SESSION['member']['customers_country_id']=$countryid;
$_SESSION['member']['customers_country_name']=$countryname;
$_SESSION['member']['customers_country_iso_code_2']=$cntryiso2;
$_SESSION['member']['customers_country_iso_code_3']=$cntryiso3;

//Delivery Information
$query= mysqli_query($conn,"select a.*, ctry.countries_name, ctry.countries_iso_code_2, ctry.countries_iso_code_3 from address_book a, countries ctry where a.customers_id = '" . $_SESSION['member']['ses_customer_id'] . "' and address_book_id = '2' and  ctry.countries_id = a.entry_country_id");
list($customerid,$bookid,$genderid,$company,$firstname,$lastname,$streetaddress,$suburb,$postcode,$city,$state,$countryid,$zoneid,$countryname,$cntryiso2,$cntryiso3 )=mysqli_fetch_row($query);

//Billing Information
$_SESSION['member']['delivery_name']=$firstname." ".$lastname;
$_SESSION['member']['delivery_firstname']=$firstname;
$_SESSION['member']['delivery_lastname']=$lastname;
$_SESSION['member']['delivery_company']=$company;
$_SESSION['member']['delivery_street_address']=$streetaddress;
$_SESSION['member']['delivery_telephone']=( !empty($phone) ? $phone : '');
$_SESSION['member']['delivery_fax']=( !empty($fax) ? $fax : '');
$_SESSION['member']['delivery_city']=$city;
$_SESSION['member']['delivery_postcode']=$postcode;

if($zoneid==0){
$_SESSION['member']['delivery_state']=$state;
$zoneid=0;
$_SESSION['member']['delivery_zone_name']='';
}else{
$_SESSION['member']['delivery_state']='';
$_SESSION['member']['delivery_zone_id']=$zoneid;
//$delivery_zone_name=;
}
$_SESSION['member']['delivery_country_id']=$countryid;
$_SESSION['member']['delivery_country_name']=$countryname;
$_SESSION['member']['delivery_country_iso_code_2']=$cntryiso2;
$_SESSION['member']['delivery_country_iso_code_3']=$cntryiso3;
