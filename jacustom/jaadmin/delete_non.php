<?php
//--------------------------------------------------------------------------||
// This script allows the user to delete orders on all non-member purchases ||
//--------------------------------------------------------------------------||
require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$order_id = filter_var($_POST['orderid'], FILTER_SANITIZE_NUMBER_INT);

$sql="DELETE FROM orders WHERE orders_id='".$order_id."'";
$res=mysqli_query($conn,$sql) or die(mysql_error());

$sql2="DELETE FROM orders_total WHERE orders_id='".$order_id."'";
$res2=mysqli_query($conn,$sql2) or die(mysql_error());

$sql3="DELETE FROM orders_products WHERE orders_id='".$order_id."'";
$res3=mysqli_query($conn,$sql3) or die(mysql_error());

header("location:".base_admin_url()."/script4.php?msg=Order+was+deleted");
exit;
