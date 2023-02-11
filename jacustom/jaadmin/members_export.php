<?php
require_once("../common_files/include/global.inc");

Header("Content-type: application/vnd.ms-excel; name='excel'");
Header("Content-Disposition: attachment; filename=ActiveMembers.xls");
Header("Content-Description: Excel output");


$sql = "SELECT t.int_member_id  
FROM customers c 
inner join tbl_member t on c.customers_id = t.int_customer_id 
where `bit_active` = '1' and t.mtype='m'
ORDER  BY t.amico_id  
";

$result = mysqli_query($conn,$sql);


echo "Amico ID"."\t"."Referring Members ID"."\t"."EC ID"."\t"."First Name"."\t"."Last Name"."\t"."Title"."\t"."Email Address"."\t"."Street Address One"."\t"."Street Address Two"."\t"."Post Code"."\t"."City"."\t"."State"."\t"."Phone Number"."\t"."Fax Number"."\t"."Social Security Number";
echo "\t"."Shipping First Name"."\t"."Shipping Last Name"."\t"."Shipping Company"."\t"."Shipping Street Address"."\t"."Shipping Post Code"."\t"."Shipping City"."\t"."Shipping State";
echo "\t"."Password";
echo "\n";

while($row = mysqli_fetch_array($result))
{
    $rsselmember = mysqli_query($conn,"select amico_id,int_member_id, int_parent_id, int_customer_id, int_designation_id, str_title, dat_last_visit,bit_active,ec_id from tbl_member WHERE int_member_id = ".$row["int_member_id"]);
    list($am_id,$memberid,$refer_member_id,$customerid,$designation,$title,$lastvisit,$active, $ec_id)= mysqli_fetch_row($rsselmember);
	
    $referrer_id = 0;
   	if($refer_member_id){
        $rsselref = mysqli_query($conn,"select amico_id from tbl_member WHERE int_member_id = '$refer_member_id'");
        list($referrer_id)= mysqli_fetch_row($rsselref);
   	}
   
   $rsselcustomer = mysqli_query($conn,"select customers_id, customers_firstname, customers_lastname, customers_email_address, customers_telephone, customers_fax, customers_password, ssn from customers WHERE customers_id = '$customerid'");
   list($customerid,$firstname,$lastname,$email,$phone,$fax,$password,$ssn)= mysqli_fetch_row($rsselcustomer);

   $rsseladdress1 = mysqli_query($conn,"select entry_company, entry_street_address,entry_street_address2, entry_postcode, entry_city, entry_country_id, entry_zone_id from address_book WHERE customers_id = '$customerid' and address_book_id=1");
   list($company,$streetadd,$streetaddTwo,$postcode,$city,$country,$zone)= mysqli_fetch_row($rsseladdress1);

    $rs_zone = mysqli_query($conn,"select z.zone_id, z.zone_name from zones z, countries c  where z.zone_country_id=c.countries_id and c.countries_iso_code_3 ='USA' order by zone_name");
    while(list($i_zoneid, $s_zone)= mysqli_fetch_row($rs_zone)){
        if($i_zoneid==$zone)
            $state=$s_zone;
    }

    $rsseladdress2 = mysqli_query($conn,"select entry_company, entry_firstname, entry_lastname, entry_street_address, entry_postcode, entry_city, entry_country_id, entry_zone_id from address_book WHERE customers_id = '$customerid' and address_book_id=2");
    list($sh_company, $sh_firstname,$sh_lastname,$sh_streetadd,$sh_postcode,$sh_city,$sh_country,$sh_zone)= mysqli_fetch_row($rsseladdress2);

    $rs_zone = mysqli_query($conn,"select z.zone_id, z.zone_name from zones z, countries c  where z.zone_country_id=c.countries_id and c.countries_iso_code_3 ='USA' order by zone_name");
    while(list($i_zoneid, $s_zone)= mysqli_fetch_row($rs_zone)){
        if($i_zoneid==$sh_zone)
            $sh_state=$s_zone;
    }

    //  echo $am_id."\t".$referrer_id."\t".$ec_id."\t".$firstname."\t".$lastname."\t".$title."\t".$email."\t".$company."\t".$streetadd."\t".$postcode."\t".$city."\t".$state."\t".$phone."\t".$fax."\t".$ssn."\t";

    echo $am_id."\t".$referrer_id."\t".$ec_id."\t".$firstname."\t".$lastname."\t".$title."\t".$email."\t".$streetadd."\t".$streetaddTwo."\t".$postcode."\t".$city."\t".$state."\t".$phone."\t".$fax."\t".$ssn."\t";
    echo $sh_firstname."\t".$sh_lastname."\t".$sh_company."\t".$sh_streetadd."\t".$sh_postcode."\t".$sh_city."\t".$sh_state."\t";
    echo $password."\t";

    echo "\n";
}