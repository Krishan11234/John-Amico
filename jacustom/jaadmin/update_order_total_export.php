<?php
require_once("../common_files/include/global.inc");

$start_date = filter_var($_GET['sd'], FILTER_SANITIZE_STRING);
$end_date = filter_var($_GET['ed'], FILTER_SANITIZE_STRING);


Header("Content-type: application/vnd.ms-excel; name='excel'");
Header("Content-Disposition: attachment; filename=NonMemberOrderTotals__for_{$start_date}--{$end_date}.xls");
Header("Content-Description: Excel output");

echo "Order ID"."\t"."Customer Name"."\t"."Date Purchased"."\t"."Referring Member"."\t"."Order Total";
echo "\n";

if(!empty($start_date) && !empty($end_date)) {
    $sql = "SELECT o.entity_id, o.increment_id AS orders_id, o.protect_code, o.customer_id, CONCAT(o.customer_firstname, ' ', o.customer_lastname) AS customers_name, 
        DATE_FORMAT(o.created_at, '%m/%d/%Y') as date_purch, DATE_FORMAT(o.created_at, '%m/%d/%Y') as date_purch, FORMAT(o.grand_total, 2) AS order_grand_total, 
        FORMAT((o.grand_total-(o.shipping_amount+o.tax_amount)), 2) AS order_cleared_grand_total, FORMAT(o.subtotal, 2) AS order_total, FORMAT(o.subtotal, 2) AS order_subtotal, 
        oa.jareferrer_amicoid AS refering_member, IF( (oa.jareferrer_self=0), 'No', 'Yes' ) AS is_member_self, IF(oa.ja_oldsite_order_id=0, 'N/A', 
        oa.ja_oldsite_order_id) AS oldsite_order_id, CONCAT(c.customers_firstname, ' ', c.customers_lastname) AS refering_member_name
        
        FROM ".MAGENTO_TABLE_PREFIX."sales_flat_order AS o
        INNER JOIN ".MAGENTO_TABLE_PREFIX."amasty_amorderattr_order_attribute AS oa ON oa.order_id = o.entity_id
        INNER JOIN tbl_member m ON m.amico_id=oa.jareferrer_amicoid
        INNER JOIN customers c ON m.int_customer_id=c.customers_id
    ";
    $sql .= " WHERE ";
    $sql .= " ( oa.ja_affiliate_member_id = '' OR oa.ja_affiliate_member_id IN (0, NULL, 'N/A', 'n/a', 'N / A') ) ";
    $sql .= " AND oa.jareferrer_self = 0 ";
    $sql .= " AND oa.jareferrer_amicoid NOT IN ( '0', '') ";
    $sql .= " AND o.created_at  >= '$start_date 00:00:00' ";
    $sql .= " AND o.created_at  <= '$end_date 23:59:59' ";
    $sql .= " ORDER BY o.created_at DESC ";

    //echo '<pre>'; print_r($sql); die();

    $result = mysqli_query($conn,$sql);


    while($row = mysqli_fetch_array($result))
    {
        //echo $row['orders_id']."\t".$row['customers_name']."\t".$row['date_purch']."\t"."{$row['refering_member']} ({$row['refering_member_name']})"."\t"."$".$row['order_grand_total'];
        echo $row['orders_id']."\t".$row['customers_name']."\t".$row['date_purch']."\t"."{$row['refering_member']} ({$row['refering_member_name']})"."\t"."$".$row['order_cleared_grand_total'];
        echo "\n";
    }
} else {
    echo "Date Range not found! ";
}