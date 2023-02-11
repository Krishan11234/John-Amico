<?
Header("Content-type: application/vnd.ms-excel; name='excel'");
Header("Content-Disposition: attachment; filename=report_export.xls");

require_once("session_check.inc");
require_once("../common_files/include/global.inc");
?>

<body topmargin="0" leftmargin="0">

<?
$sql ="SELECT * FROM tbl_member ORDER BY ec_id";
echo "<table border=\"1\" width=\"100%\" cellpadding=\"2\" cellspacing=\"0\">";
echo "<tr><td align=\"center\"><b>Member ID</td><td align=\"center\"><b>Name</td><td align=\"center\"><b>Comment</td><td align=\"center\"><b>Category</td><td align=\"center\"><b>Status</td><td align=\"center\"><b>Date Entered</td><td align=\"center\"><b>Type</td><td>EC Id</td></tr>";
$res = mysqli_query($conn,$sql) or die(mysql_error());
$pending = 0;

$add="";
if ($status!="") {$add.=" AND status = '".$status."'";};
if ($type!="") {$add.=" AND category = '".$type."'";};
if ($start_date!="") {$add.=" AND date1 >= '".$start_date."'";};
if ($end_date!="") {$add.=" AND date1 <= '".$end_date."'";};

while($row = mysqli_fetch_assoc($res))
{
	$res2 = mysqli_query($conn,"select int_customer_id, ec_id from tbl_member WHERE amico_id = '".$row['amico_id']."'");
	$row2 = mysqli_fetch_assoc($res2);

	$mlm_id = $row2['int_customer_id'];
	$ec_id = $row2['ec_id'];

	$sql3 = "SELECT id, date1, category, type, notes, status, UNIX_TIMESTAMP(date2) as date2 FROM tbl_mlm_errors WHERE mlm_id = '".$mlm_id."' ".$add;
	$res3 = mysqli_query($conn,$sql3) or die(mysql_error());

	while($row3 = mysqli_fetch_assoc($res3))
	{
		$sql4 = "SELECT c.customers_firstname, c.customers_lastname FROM customers c, tbl_member t WHERE t.int_customer_id=c.customers_id AND t.amico_id = '".$row['amico_id']."'";
		$res4 = mysqli_query($conn,$sql4) or die(mysql_error());
		$row4 = mysqli_fetch_assoc($res4);

		$pending++;
		echo "<tr><td align=\"center\">".$row['amico_id']."</td><td align=\"center\">".$row4['customers_firstname']." ".$row4['customers_lastname']."</td><td align=\"center\">".$row3['notes']."</td><td align=\"center\">".$row3['category']."</td><td align=\"center\">".$row3['status']."</td><td align=\"center\">".date('m/d/Y g:ia', strtotime($row3['date1']))."</td><td align=\"center\">".$row3['type']."</td><td>".$ec_id."</tr>";
	}
}

?>
