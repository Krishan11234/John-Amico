<?
require("./include/query_result_limit_empty.php");


$ec_id=$_SESSION['session_user'];

if ($ec_id=='C1') {
$query="SELECT  
m.amico_id as MemberID, 
concat(c.customers_lastname, ', ', c.customers_firstname) as Name,
m.ppp as _ppp_, 
m.lod as _lod_, 
m.as as _as_, 
m.mtd as _mtd_, 
m.ytd as _ytd_ 
FROM customers c inner join tbl_member m ON c.customers_id=m.int_customer_id 
left outer join address_book a on c.customers_id=a.customers_id
WHERE m.bit_active=1 
GROUP BY m.amico_id"; 
} else {
$query="SELECT  
m.amico_id as MemberID, 
concat(c.customers_lastname, ', ', c.customers_firstname) as Name,
m.ppp as _ppp_, 
m.lod as _lod_, 
m.as as _as_, 
m.mtd as _mtd_, 
m.ytd as _ytd_ 
FROM customers c inner join tbl_member m ON c.customers_id=m.int_customer_id 
left outer join address_book a on c.customers_id=a.customers_id
WHERE m.ec_id='".$ec_id."' AND m.bit_active=1 
GROUP BY m.amico_id"; 
};

$fields_alias = array("_lod_"=> "LOD", "_as_"=> "AS", "_mtd_"=> "Month<br>To<br>Date", "_ytd_"=> "Year To Date" );
$fields_alias = array("_ppp_"=> "P", "_lod_"=> "LOD", "_as_"=> "AS", "_mtd_"=> "MTD", "_ytd_"=> "YTD");
$fields_alias = array("_sit_short_"=> "sit_short");
query_results_limit_empty($query, $camp_start, $order_start, $order_coresp_array, $fields_alias, $script_name, $l2, $head_title, $no_results_msg);


?>
