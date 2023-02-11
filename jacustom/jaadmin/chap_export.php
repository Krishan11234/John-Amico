<?php

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

Header("Content-type: application/vnd.ms-excel; name='excel'");
Header("Content-Disposition: attachment; filename=Chapters.xls");
Header("Content-Description: Excel output");

$use_magento = true;

$mtype='c';
$sql = "select m.growth, m.contest, m.int_member_id, m.int_designation_id,m.amico_id,c.customers_id,c.customers_email_address,c.customers_firstname,
        c.customers_lastname,m.bit_active 
        from tbl_member m 
        left outer join customers c on m.int_customer_id=c.customers_id  
        WHERE m.mtype='$mtype' AND is_deleted=0
        order by c.customers_firstname
";

$result = mysqli_query($conn,$sql);

echo "Category"."\t"."Information"."\t"."Growth"."\t"."Comments/Errors"."\t"."Last Name, First Name"."\t"."Member ID"."\t"."Last Order Date"."\t"."Average sale"."\t"."Month To Date"."\t"."Year To Date Sales"."\t"."Percentage over last three months"."\t"."Contest"."\t"."Email"."\t"."Orders";
echo "\n";

while($row = mysqli_fetch_array($result))
{

    if(!$use_magento) {
        //Last Order Date
        $ec_id = $row['amico_id'];

        $query_lod = " 
        SELECT o.date_purchased  
        FROM orders o 
        inner JOIN customers c on c.customers_id=o.customers_id   
        inner join tbl_member m ON c.customers_id=m.int_customer_id  
        WHERE m.ec_id='$ec_id' AND m.bit_active=1	
        order by o.orders_id desc 
        limit 1
	";
        $a_lod = mysqli_fetch_array(mysqli_query($conn,$query_lod));

        if($a_lod[0]){
            $lod = $a_lod[0];
        }else {
            $lod = "N/A";
        }

        //Month To Date
        //sum of totals of orders created this month
        $query_current_month = "
        SELECT sum(ot.value) as ss  
        FROM orders o 
        inner join orders_total ot on ot.orders_id=o.orders_id and class='ot_total'  
        inner JOIN customers c on c.customers_id=o.customers_id   
        inner join tbl_member m ON c.customers_id=m.int_customer_id  
        WHERE m.ec_id='$ec_id' AND m.bit_active=1 and o.date_purchased>='".date("Y")."-".date("m")."-01 00:00:00'
        group by o.orders_id 
        order by o.orders_id desc   
 	";
        $a_sum_current_month =mysqli_fetch_array(mysqli_query($conn,$query_current_month));

        //Year To Date
        //sum of totals of orders created this year
        $query_current_year = "
        SELECT sum(ot.value) as ss  
        FROM orders o 
        inner join orders_total ot on ot.orders_id=o.orders_id and class='ot_total'  
        inner JOIN customers c on c.customers_id=o.customers_id   
        inner join tbl_member m ON c.customers_id=m.int_customer_id  
        WHERE m.ec_id='$ec_id' AND m.bit_active=1 and o.date_purchased>='".date("Y")."-01-01 00:00:00'
        group by o.orders_id 
        order by o.orders_id desc   
        ";
        $a_sum_current_year =mysqli_fetch_array(mysqli_query($conn,$query_current_year));

        $query_avg_sale  = "
        SELECT count(o.orders_id) as ss  
        FROM orders o 
        inner JOIN customers c on c.customers_id=o.customers_id   
        inner join tbl_member m ON c.customers_id=m.int_customer_id  
        WHERE m.ec_id='$ec_id' AND m.bit_active=1 
        group by o.orders_id 
        order by o.orders_id desc   
        ";
        $a_sum_avg_sale =mysqli_fetch_array(mysqli_query($conn,$query_avg_sale));

        $query_avg_sale2  = "
        SELECT DISTINCT (date_format( o.date_purchased, '%m/%Y' ) )  as ss  
        FROM orders o 
        inner join orders_total ot on ot.orders_id=o.orders_id and class='ot_total'  
        inner JOIN customers c on c.customers_id=o.customers_id   
        inner join tbl_member m ON c.customers_id=m.int_customer_id  
        WHERE m.ec_id='$ec_id' AND m.bit_active=1 
        group by o.orders_id 
        order by o.orders_id desc   
        ";

        $nr2 = mysqli_num_rows(mysqli_query($conn,$query_avg_sale2));
        if($nr2){
            $avg = $a_sum_avg_sale[0]/$nr2;
        } else {
            $avg = 0;
        }
    }
    else {

        //Last Order Date
        $ec_id = $row['amico_id'];

        $query_lod = " 
            SELECT o.created_at
            FROM `stws_amasty_amorderattr_order_attribute` oa
            INNER JOIN stws_sales_flat_order o ON oa.`order_id`=o.entity_id
            WHERE oa.`jareferrer_amicoid` = '{$ec_id}' AND oa.`jareferrer_self` = '1'
            ORDER BY oa.`order_id` DESC
            LIMIT 1
        ";
        $a_lod = mysqli_fetch_array(mysqli_query($conn,$query_lod));

        if($a_lod[0]){
            $lod = $a_lod[0];
        }else {
            $lod = "N/A";
        }


        //Month To Date
        //sum of totals of orders created this month
        $query_current_month = "
            SELECT sum(o.grand_total) as ss  
            FROM `stws_amasty_amorderattr_order_attribute` oa
            INNER JOIN stws_sales_flat_order o ON oa.`order_id`=o.entity_id  
            WHERE oa.`jareferrer_amicoid` = '{$ec_id}' AND oa.`jareferrer_self` = '1' and o.created_at>='".date("Y")."-".date("m")."-01 00:00:00'
            group by o.orders_id 
            order by o.orders_id desc   
        ";
        $a_sum_current_month =mysqli_fetch_array(mysqli_query($conn,$query_current_month));


        //Year To Date
        //sum of totals of orders created this year
        $query_current_year = "
            SELECT sum(o.grand_total) as ss  
            FROM `stws_amasty_amorderattr_order_attribute` oa
            INNER JOIN stws_sales_flat_order o ON oa.`order_id`=o.entity_id  
            WHERE oa.`jareferrer_amicoid` = '{$ec_id}' AND oa.`jareferrer_self` = '1' and o.date_purchased>='".date("Y")."-01-01 00:00:00'
            group by o.orders_id 
            order by o.orders_id desc   
        ";
        $a_sum_current_year =mysqli_fetch_array(mysqli_query($conn,$query_current_year));



        $query_avg_sale  = "
            SELECT count(o.entity_id) as ss  
            FROM `stws_amasty_amorderattr_order_attribute` oa
            INNER JOIN stws_sales_flat_order o ON oa.`order_id`=o.entity_id  
            WHERE oa.`jareferrer_amicoid` = '{$ec_id}' AND oa.`jareferrer_self` = '1' 
            group by o.orders_id 
            order by o.orders_id desc   
        ";
        $a_sum_avg_sale =mysqli_fetch_array(mysqli_query($conn,$query_avg_sale));



        $query_avg_sale2  = "
            SELECT DISTINCT (date_format( o.created_at, '%m/%Y' ) )  as ss  
            FROM `stws_amasty_amorderattr_order_attribute` oa
            INNER JOIN stws_sales_flat_order o ON oa.`order_id`=o.entity_id    
            WHERE oa.`jareferrer_amicoid` = '{$ec_id}' AND oa.`jareferrer_self` = '1' 
            group by o.orders_id 
            order by o.orders_id desc   
        ";

        $nr2 = mysqli_num_rows(mysqli_query($conn,$query_avg_sale2));
        if($nr2){
            $avg = $a_sum_avg_sale[0]/$nr2;
        } else {
            $avg = 0;
        }

    }
	
	echo "As shown on screen"."\t"."I"."\t" . $row['growth'] . "\t"."Notes/Errors"."\t".$row['customers_firstname'].", ".$row['customers_lastname']."\t".$row['amico_id']."\t".$lod."\t".$avg ."\t".$a_sum_current_month["ss"]."\t".$a_sum_current_year["ss"]."\t"."P"."\t". $row['contest'] ."\t"."Envelope"."\t"."Orders";
	echo "\n";

}