<?php
//--FIGURE OUT CONTEST INFO
require_once("../common_files/include/global.inc");
require_once("session_check.inc");

function calculateSales($year = "") {
    global $conn;

	if(date("m") >= 11)
	{	
		if($year == "last")
		{
			$last_year = date('Y')-1;
			$second_year = date('Y');
		}
		else
		{
			$last_year = date('Y') ;
			$second_year = date('Y') + 1;
		}
	}
	else
	{
		if($year == "last")
		{
			$last_year = date('Y')-2;
			$second_year = date('Y')-1;
		}
		else
		{
			$last_year = date('Y') - 1;
			$second_year = date('Y');
		}
	}

//	$sql = "SELECT b.* FROM bw_invoices b, tbl_member t WHERE t.amico_id = b.ID AND b.InvoiceDate >='".$last_year."-01-01' AND b.InvoiceDate <= '".$last_year."-12-12' AND t.int_member_id = '".$_GET[memberid]."'";
	$sql = "SELECT b.* FROM bw_invoices b, tbl_member t WHERE t.amico_id = b.ID AND b.InvoiceDate >='".$last_year."-11-01' AND b.InvoiceDate <= '".$second_year."-10-31' AND t.int_member_id = '".$_GET[memberid]."'";
	$res = mysqli_query($conn,$sql) or die(mysql_error());

	$total = 0;
	while($row = mysqli_fetch_assoc($res))
	{
		$sql2 = "SELECT Description, ID, (UnitPrice * ShipQty) as total, UnitPrice, ShipQty FROM bw_invoice_line_items WHERE FKEntity = '".$row[SKOEInvoice]."'";
		$res2 = mysqli_query($conn,$sql2) or die(mysql_error());
		$second_total = 0;
		while($row2 = mysqli_fetch_assoc($res2))
		{
			$second_total+=$row2[total];
			$total+=$row2[total];
		}
	}
	
	return round($total,2);
}

function calculateGoal($amount = "1000", $num = 1) {
	global $bg, $conn;
	
	$row = "<tr bgcolor=\"".$bg."\">";
	$goal = calculateSales("last") + $amount;
	$remaining = $goal - calculateSales(); 
	
	if($remaining < 0)
	{
		$remaining = "<font color=\"green\"><b>won</b></font>";
	}
	else
	{
		$remaining = "$".number_format($remaining, 2);
	}

	if($num == "1")
	{
		$num = "Win 15%";
	}
	if($num == "2")
	{
		$num = "Win 30%";
	}
	if($num == "3")
	{
		$num = "Win 50%";
	}
	if($num == "4")
	{
		$num = "Win 75%";
	}
	if($num == "5")
	{
		$num = "Win 100%";
	}
	$row.= 	"<td class=\"copysmallblack\" align=\"center\" style=\"border-right:1px solid #000000\"  nowrap>".$num."</td>";
	$row.= 	"<td class=\"copysmallblack\" align=\"center\" style=\"border-right:1px solid #000000\"  nowrap>$".number_format(calculateSales("last"),2)."</td>";
	$row.= 	"<td class=\"copysmallblack\" align=\"center\" style=\"border-right:1px solid #000000\"  nowrap>$".number_format($goal,2)."</td>";
	$row.= 	"<td class=\"copysmallblack\" align=\"center\" style=\"border-right:1px solid #000000\"  nowrap>$".number_format(calculateSales(),2)."</td>";
	$row.= 	"<td class=\"copysmallblack\" align=\"center\">".$remaining."</td>";

	return $row;
}
?>
<title>John Amico .: Contest Status</title>
<link href="../css/login.css" rel="stylesheet" type="text/css">
<table cellspacing="0" cellpadding="2" style="border:1px solid black">
	<tr bgcolor="#000000">
		<td class="copysmallblack" style="border-bottom:1px solid black;border-right:1px solid white" align="center" nowrap ><b><font color="#FFFFFF">Prize</td>
		<td class="copysmallblack" style="border-bottom:1px solid black;border-right:1px solid white" align="center" nowrap><b><font color="#FFFFFF">Last Year</td>
		<td class="copysmallblack" style="border-bottom:1px solid black;border-right:1px solid white" align="center" nowrap><b><font color="#FFFFFF">Your Cruise Goals</td>
		<td class="copysmallblack" style="border-bottom:1px solid black;border-right:1px solid white" align="center" nowrap><b><font color="#FFFFFF">Score</td>
		<td class="copysmallblack" style="border-bottom:1px solid black" align="center" nowrap><b><font color="#FFFFFF">Goal Remaining</td>
	</tr>
<?php
	echo calculateGoal();
	$amount = 1000;
	$num = 1;
	for($i=0;$i<=3;$i++)
	{
		$num++;
		if($bg){unset($bg);}else{$bg="#EEEEEE";}
		$amount = $amount + 1000;
		echo calculateGoal($amount, $num);
	}

	for($i=0;$i<=7;$i++)
	{
		$num++;
		if($bg){unset($bg);}else{$bg="#EEEEEE";}
		$amount = $amount + 2500;
		echo calculateGoal($amount, $num);
	}
?>
</table><br><Center><a href="#" onClick="window.close();">Close</a><center>