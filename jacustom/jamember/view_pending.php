<?php
//--FIGURE OUT CONTEST INFO
require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$display_header = false;
require_once("templates/header.php");
?>
<style>

</style>
<body>
<?
$sql = "select amico_id from tbl_member where int_member_id=".(int)$_GET['member_id'];
$ec_id = mysqli_result(mysqli_query($conn,$sql), 0);

$sql ="SELECT * FROM tbl_member WHERE ec_id = '".$ec_id."'";
echo '<div class="mb-lg ">&nbsp;</div>';
echo '<div class="col-xs-12">';
echo "<div class='table-responsive'><table class='table table-bordered table-striped '>";
echo "<tr><th class='text-center'>Member ID</th><th class='text-center'>Name</th><th class='text-center'>Comment</th><th class='text-center'>Category</th><th class='text-center'>Date Entered</th></tr>";
$res = mysqli_query($conn,$sql) or die(mysql_error());
$pending = 0;

while($row = mysqli_fetch_assoc($res)) {
	$res2 = mysqli_query($conn,"select int_customer_id from tbl_member WHERE amico_id = '".$row['amico_id']."'");
	list($mlm_id)= mysqli_fetch_row($res2);
	
	$sql3 = "SELECT id, date1, category, type, notes, status, UNIX_TIMESTAMP(date2) as date2 FROM tbl_mlm_errors WHERE mlm_id = '".$mlm_id."' AND status = 'Pending'";
	$res3 = mysqli_query($conn,$sql3) or die(mysql_error());

	while($row3 = mysqli_fetch_assoc($res3)) {
		$sql4 = "SELECT c.customers_firstname, c.customers_lastname FROM customers c, tbl_member t WHERE t.int_customer_id=c.customers_id AND t.amico_id = '".$row['amico_id']."'";
		$res4 = mysqli_query($conn,$sql4) or die(mysql_error());
		$row4 = mysqli_fetch_assoc($res4);

		$pending++;
		echo "<tr><td align=\"center\">".$row['amico_id']."</td><td align=\"center\">".$row4['customers_firstname']." ".$row4['customers_lastname']."</td><td align=\"center\">".$row3['notes']."</td><td align=\"center\">".$row3['category']."</td><td align=\"center\">".date('m/d/Y g:ia', strtotime($row3['date1']))."</td></tr>";
	}
}

if($pending == 0) {
	echo "<tr><td colspan=\"5\" align=\"center\">There are no pending tasks</td></tr>";
}

echo "</table></div>";
echo "</div>";
echo "<br><center><a href=\"#\" onClick=\"window.close();\" class=\"td\">Close Window</a>&nbsp;|&nbsp;<a href=\"#\" onClick=\"window.print();\">Print Window</a></span></center>";
?>