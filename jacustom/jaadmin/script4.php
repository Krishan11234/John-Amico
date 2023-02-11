<?php
//--------------------------------------------------------------------------||
// This script allows the user to update orders on all non-member purchases ||
//--------------------------------------------------------------------------||
require_once("session_check.inc");
require_once("../common_files/include/global.inc");

if($submit){
	$sql3="UPDATE orders_total SET value='".$total."' WHERE orders_id='".$order_id."' AND title='Total:'";
	$res3=mysqli_query($conn,$sql3) or die(mysql_error());
	echo "<center><font color=red face=arial size=2>Order total has been updated</font></center>";
}
if($msg){
	echo "<center><font color=red face=arial size=2>".$msg."</font></center>";
}
?>
<link href="../css/calendarstyle.css" rel="stylesheet" type="text/css">
<style>
td {
	font-size: 12px;
	font-family: arial, sans-serif;
	font-weight: bold;
}
.command {
	font-size: 10pt; 
	height: 22; 
	font-family:arial; 
	filter:progid:DXImageTransform.Microsoft.Gradient
	(endColorstr='#ffffff', startColorstr='#CCCCCC', gradientType='1')
}
</style>
<body bgcolor="#ffffff" BOTTOMMARGIN="0" LEFTMARGIN="0" MARGINHEIGHT="0" 
	MARGINWIDTH="0" RIGHTMARGIN="0" TOPMARGIN="0">
<DIV ALIGN="center">
<table border="0" cellpadding="0" cellspacing="0">
  <TBODY>	
    <tr>
   		<td align="center" colspan="9"><img name="toplogo" 
			src="../images/logo.gif" border="0">
		</td> 
	</tr>
	<tr align="center">
		<td colspan="2"><a href="./index.php">Main Menu</a></td>
	</tr>
  </tbody>
</table>
</DIV>
<center>
<table cellpadding="5" cellspacing="0" style="border:1px solid black" align="center"><caption><font face="arial" size="3"><b>Update Totals For Non-Member Purchases</b></font></caption>
	<tr>
		<td colspan="6" align="center"><form method="post" name="form1" action="<?=$_SERVER['PHP_SELF'];?>">
			<font style="font-size: 12px;font-family: arial, sans-serif;font-weight: bold;">From: 
			<select name=m1>
				<?
				if (!$m1)
					$m1=date("m");
				for ($i=1;$i<=12;$i++):
					if ($m1==$i)
						echo "<option value=\"$i\" selected>$i\n";
					else
						echo "<option value=\"$i\">$i\n";
				endfor;
				?>
			</select> / 
			<select name=d1>
				<?
				if (!$d1)
					$d1=date("d");
				for ($i=1;$i<=31;$i++):
					if ($i==$d1)
						echo "<option value=\"$i\" selected>$i\n";
					else
						echo "<option value=\"$i\">$i\n";
				endfor;
				?>
			</select> /
			<select name=y1>
				<?
				if (!$y1)
					$y1=date("Y");
				for ($i=date("Y")-5;$i<=date("Y")+5;$i++):
					if ($i==$y1)
						echo "<option value=\"$i\" selected>$i\n";
					else
						echo "<option value=\"$i\">$i\n";
				endfor;
				?>
			</select>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			To:
			<select name=m2>
				<?
				if (!$m2)
					$m2=date("m");
				for ($i=1;$i<=12;$i++):
					if ($i==$m2)
						echo "<option value=\"$i\" selected>$i\n";
					else
						echo "<option value=\"$i\">$i\n";
				endfor;
				?>
			</select> / 
			<select name=d2>
				<?
				if (!$d2)
					$d2=date("d");
				for ($i=1;$i<=31;$i++):
					if ($d2==$i)
						echo "<option value=\"$i\" selected>$i\n";
					else
						echo "<option value=\"$i\">$i\n";
				endfor;
				?>
			</select> /
			<select name=y2>
				<?
				if (!$y2)
					$y2=date("Y");
				for ($i=date("Y")-5;$i<=date("Y")+5;$i++):
					if ($i==$y2)
						echo "<option value=\"$i\" selected>$i\n";
					else
						echo "<option value=\"$i\">$i\n";
				endfor;
				?><input type="submit" name="date_adjust" value="Adjust Dates" class="command">
		</td>
	</tr>
	<tr bgcolor="#000000">
		<td><font color="white"><b>Order Id</td>
		<td><font color="white"><b>Customers Name</td>
		<td><font color="white"><b>Referring Member ID</td>
		<td><font color="white"><b>Date Ordered</td>
		<td><font color="white"><b>Total</td>
		<td><font color="white"><b>Update</td>
		<td><font color="white"><b>Delete</td>
	</tr>
<?
$sql="SELECT orders_id, customers_name, DATE_FORMAT(date_purchased, '%m/%d/%Y') as date_purch, refering_member FROM orders WHERE  refering_member != '' AND refering_member != 'None'";
if($date_adjust){
	$sql.=" AND date_purchased  >= '".$y1."-".$m1."-".$d1."' AND date_purchased <= '".$y2."-".$m2."-".$d2."'";
}else{
	$sql.=" AND date_purchased >= '2008-01-01'";
}
$sql.=" ORDER BY date_purchased DESC";
$res=mysqli_query($conn,$sql) or die(mysql_error());

while($row=mysqli_fetch_row($res)){
	$sql2="SELECT value FROM orders_total WHERE orders_id='".$row[0]."' AND title='Total:'";
	$res2=mysqli_query($conn,$sql2) or die(mysql_error());
	while($row2=mysqli_fetch_row($res2)){
		if($bg){unset($bg);}else{$bg="#EEEEEE";}?>
		<form method="post" action="<?=$_SERVER['PHP_SELF'];?>">
		<input type="hidden" name="order_id" value="<?=$row[0];?>">
		<tr bgcolor="<?=$bg;?>">
			<td><?=$row[0];?></td>
			<td><?=$row[1];?></td>
			<td><?=$row[3];?></td>
			<td><?=$row[2];?></td>
			<td><input type="text" name="total" value="<?=$row2[0];?>" size="10"></td>
			<td><input type="submit" name="submit" value="Update" class="command"></td>
			<td><input type="button" name="delete" value="Delete" onClick="window.open('delete_non.php?id=<?=$row[0];?>','_self');return confirm('Are you sure you want to delete this record');" class="command"></td>
		</tr>
		</form>
<?
	}
}
?>