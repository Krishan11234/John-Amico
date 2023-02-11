<?php

require_once("../common_files/include/global.inc");

$sql = "
SELECT p.products_price_A, p.products_price_B, IF( op.final_price = p.products_price_A, 'Y', 'N' ) price_level_A, IF( op.final_price = p.products_price_B, 'Y', 'N' ) price_level_B, op.* FROM `orders_products` op
INNER JOIN products p ON p.products_id = op.products_id

WHERE op.`orders_id` IN ( SELECT orders_id FROM `orders` WHERE `date_purchased` > '2015-12-31 23:59:59' AND `date_purchased` < '2017-01-01 00:00:00' AND `refering_member` IN ( SELECT amico_id FROM `tbl_member` WHERE `bit_active` = '1' ) ORDER BY `orders_id` )
GROUP BY orders_id
ORDER BY `orders_products_id`
";

$query = mysqli_query($conn, $sql);

$priceLevels = array();

while( $row = mysqli_fetch_object($query) ) {
    if( $row->price_level_A == 'Y' ) {
        $priceLevels['A']['orders'][$row->orders_id]['products_price_A'] = $row->products_price_A;
        $priceLevels['A']['orders'][$row->orders_id]['order_products_price'] = $row->products_price;
    }

    if( $row->price_level_B == 'Y' ) {
        $priceLevels['B']['orders'][$row->orders_id]['products_price_B'] = $row->products_price_B;
        $priceLevels['B']['orders'][$row->orders_id]['order_products_price'] = $row->products_price;
    }

    if( ($row->price_level_A == 'N') && ($row->price_level_B == 'N') ) {
        $priceLevels['N']['orders'][$row->orders_id]['products_price_A'] = $row->products_price_A;
        $priceLevels['N']['orders'][$row->orders_id]['products_price_B'] = $row->products_price_B;
        $priceLevels['N']['orders'][$row->orders_id]['order_products_price'] = $row->products_price;
    }
}

$priceLevels['A']['orders_count'] = count($priceLevels['A']['orders']);
$priceLevels['B']['orders_count'] = count($priceLevels['B']['orders']);
$priceLevels['N']['orders_count'] = count($priceLevels['N']['orders']);


$priceLevels['total_orders'] = $priceLevels['A']['orders_count'] + $priceLevels['B']['orders_count'] + $priceLevels['N']['orders_count'];
$priceLevels['total_orders_with_ab'] = $priceLevels['A']['orders_count'] + $priceLevels['B']['orders_count'];
$priceLevels['total_orders_with_a'] = $priceLevels['A']['orders_count'];
$priceLevels['total_orders_with_b'] = $priceLevels['B']['orders_count'];
$priceLevels['total_orders_without_ab'] = $priceLevels['N']['orders_count'];


echo '<pre>'; print_r($priceLevels); die();