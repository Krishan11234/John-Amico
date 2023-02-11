<?php
require_once("../common_files/include/global.inc");
require_once("session_check.inc");


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../css/login.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="#EEEEEE" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="100%" height="100%" border="0" cellpadding="5" cellspacing="0">
  <tr>
    <td width="100%" valign="top"> 
      <div align="center">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr> 
    <td valign="top">
	 
	 <table border="0" align="center" cellpadding="5" cellspacing="0">
	 <form method="get">
	 <input type="hidden" name="memberid" value="<?=$memberid?>">
	  <tr bgcolor="#999999">
	   <td align="center" colspan="2"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Date Range Selection</strong></font></td>
	  </tr>
	  <tr bgcolor="#FFFFFF">
	   <td align="right"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Start Date:</font></td>
	   <td><select name="start_month">
<?php

$start_time = time()-2592000;

if(!$start_year)
{
	$start_year = date("Y", $start_time);
}
if(!$start_day)
{
	$start_day = date("d", $start_time);
}
if(!$start_month)
{
	$start_month = date("m", $start_time);
}
if(!$end_year)
{
	$end_year = date("Y");
}
if(!$end_day)
{
	$end_day = date("d");
}
if(!$end_month)
{
	$end_month = date("m");
}

for($count=1;$count<=12;$count++)
{
	if($count < 10)
	{
		$val = "0".$count;
	}
	else
	{
		$val = $count;
	}
?>
         <option value="<?=$val?>"<?if($start_month==$count){?> SELECTED<?}?>><?=$val?>
<?
}
?>
	     </select>/<select name="start_day">
<?
for($count=1;$count<=31;$count++)
{
	if($count < 10)
	{
		$val = "0".$count;
	}
	else
	{
		$val = $count;
	}
?>
         <option value="<?=$val?>"<?if($start_day==$count){?> SELECTED<?}?>><?=$val?>
<?
}
?>
	     </select>/<select name="start_year">
<?
for($val=2000;$val <= date("Y");$val++)
{
?>
         <option value="<?=$val?>"<?if($start_year==$val){?> SELECTED<?}?>><?=$val?>
<?
}
?>
	     </select></td>
	  </tr>
	  <tr bgcolor="#FFFFFF">
	   <td align="right"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">End Date:</font></td>
	   <td><select name="end_month">
<?
for($count=1;$count<=12;$count++)
{
	if($count < 10)
	{
		$val = "0".$count;
	}
	else
	{
		$val = $count;
	}
?>
         <option value="<?=$val?>"<?if($end_month==$count){?> SELECTED<?}?>><?=$val?>
<?
}
?>
	     </select>/<select name="end_day">
<?
for($count=1;$count<=31;$count++)
{
	if($count < 10)
	{
		$val = "0".$count;
	}
	else
	{
		$val = $count;
	}
?>
         <option value="<?=$val?>"<?if($end_day==$count){?> SELECTED<?}?>><?=$val?>
<?
}
?>
	     </select>/<select name="end_year">
<?
for($val=2000;$val <= date("Y");$val++)
{
?>
         <option value="<?=$val?>"<?if($end_year==$val){?> SELECTED<?}?>><?=$val?>
<?
}
?>
	     </select></td>
	  </tr>
	  <tr bgcolor="#FFFFFF">
	   <td align="center" colspan="2"><input type="submit" value="Adjust Date Range"></td>
	  </tr>
	  </form>
	 </table>
	 <br>
<?
$result = mysqli_query($conn,"SELECT amico_id FROM tbl_member WHERE int_member_id = '{$_GET['memberid']}'");
$amico_id = mysqli_result($result, 0);

$sql = "SELECT * FROM bw_invoices WHERE ID = '$amico_id' AND (InvoiceDate >= '$start_year-$start_month-$start_day' AND InvoiceDate <= '$end_year-$end_month-$end_day')";
$iresult = mysqli_query($conn,$sql);
$result_count = mysqli_num_rows($iresult);
?>
	 <table width="90%" border="0" align="center" cellpadding="5" cellspacing="0">
        <tr> 
          <td bgcolor="#999999">
		  	<table width="100%">
				<tr>
					<td align="left">
						<font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>JOHN AMICO Invoice Report</strong></font>
					</td>
					<td align="right">
						<a href="#" onclick='javascript:window.open("print_invoice_report_new.php?memberid=<?=$memberid?>&start_year=<?=$start_year?>&start_month=<?=$start_month?>&start_day=<?=$start_day?>&end_year=<?=$end_year?>&end_month=<?=$end_month?>&end_day=<?=$end_day?>","Print","scrollbars=yes");'>Printer Friendly</a>
					</td>
				</tr>
			</table>
		  
		  
        </tr>
        </tr>
		<tr> 
          <td bgcolor="white">
			<table border="0" width="100%">
				<TR>
					<td width="10%" align="right"><font size="-4"> <?=date("M-d-y")?></font></td>
					<td align="center"><b>AMICO EDUCATIONAL CONCEPTS INC</b></td>
				</tr>
				<TR>
					<td width="10%" align="right"><font size="-4"> <?=$today?></font></td>
					<td align="center"><font color="Blue" size="2">ORDER ENTRY INVOICE DETAIL REPORT</font></td>
				</tr>
				<TR>
					<td colspan="2" id="content">
<?
if($result_count > 0)
{
	$date = getdate (time());
	$today = $date['hours'].':'.$date['minutes']; 
?>
<br><center><font size="3"><b>LOADING INVOICES...</b></font></center>
						<table align="center" cellspacing="0" cellpadding="3">
							<tr>
								<td align="right"><font size="2">PERCENTAGE COMPLETE: </font></td>
								<td width="200"><table width="100%" cellspacing="0" cellpadding="0" border="0" style="border: 1px solid black"><tr><td><table cellspacing="0" cellpadding="0" border="0" bgcolor="green"><tr><td id="meter" width="0" height="12"></td></tr></table></td></tr></table></td>
								<td><font id='percent'>0%</font></td>
							</tr>
						</table>						
<?
}
else
{
?>
<br><center><font size="2" face="Verdana, Arial, Helvetica, sans-serif">No Invoices Found in Selected Date Range</font></center>
<?
}
?>
					</td>
				</tr>
			</table>
		</font></td></tr>	
      </table> 
   </td>
	</tr>
</table>
<?
if($result_count > 0)
{
?>
<div id="invoices" style="position:absolute; left:-999999px; top:-999999px; visibility: visible; z-index:50;">
</div>
<?
	$count = 0;
	$count2 = 0;
	$inc_value = 100/$result_count;
	$progress = 0;
	$old_value = 0;
	
	while($irow = mysqli_fetch_array($iresult))
	{
		list($year,$month,$day) = explode('-',$irow['OrderDate']);
		$orddate = date("m|d|Y",mktime(0,0,0,$month,$day,$year));
		list($year,$month,$day) = explode('-',$irow['InvoiceDate']);
    	$invdate = date("m|d|Y",mktime(0,0,0,$month,$day,$year));
?>
<div id="data_<?=$count?>" style="position:absolute; left:-999999px; top:-999999px; visibility: visible; z-index:100;">
						<table border="0" width="100%">
							<tr>
								<td><font size="2">Inv.#</font><br><hr color="Black"></td>
								<td><font size="2">Customer ID</font><br><hr color="Black"></td>
								<td><font size="2">Name/Invoice Description</font><br><hr color="Black"></td>
								<td><font size="2">Invoice Information</font><br><hr color="Black"></td>
							</tr>
							
							<tr>
								<td valign="top"><font size="2"><?=$irow['InvoiceNo']?></font></td>
								<td valign="top"><font size="2"><?=$amico_id?></font></td>
								<td valign="top"><font size="2"><?=$irow['Name']?></font></td>
								<td>
									<table border="0" width="100%">
										<tr>
											<td align="left"><font size="2">Inv Date:</font></td><td align="left"><font size="2"><?=$invdate?></font></td>
											<td align="right"><font size="2">Rep:</font></td><td align="left"><font size="2"><?=$irow['SalesRepIDNo']?></font></td>
										</tr>
										<tr>
											<td align="left"><font size="2">Ord Date:</font></td><td align="left"><font size="2"><?=$orddate?></font></td>
											<td align="right"><font size="2">Order#:</font></td><td align="left"><font size="2"><?=$irow['OrderNo']?></font></td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td colspan="3">
									<table border="0" width="100%">
										<tr>
											<td align="center"><font size="2">Item<br><hr size="1" color="Black"></font></td>
											<td align="center"><font size="2">Description<br><hr size="1" color="Black"></font></td>
											<td align="center"><font size="2">SA<br><hr size="1" color="Black"></font></td>
											<td align="center"><font size="2">Qty<br><hr size="1" color="Black"></font></td>
											<td align="center"><font size="2">Price<br><hr size="1" color="Black"></font></td>
											<td align="center"><font size="2">Amount<br><hr size="1" color="Black"></font></td>
										</tr>
<?
		$sql = "SELECT ID, Description, ShipQty, UnitPrice FROM bw_invoice_line_items WHERE FKEntity = $irow[SKOEInvoice] ORDER BY ID";
		$liresult = mysqli_query($conn,$sql);

		$totalamt=0;
		while($lirow = mysqli_fetch_array($liresult))
		{
?>
										<tr>
											<td><font size="2"><?=$lirow['ID']?></font></td>
											<td><font size="2"><?=$lirow['Description']?></font></td>
											<td><font size="2">&nbsp;</font></td>
											<td align="right"><font size="2"><?=$lirow['ShipQty']?></font></td>
											<td align="right"><font size="2"><?=number_format($lirow['UnitPrice'],2)?></font></td>
											<td align="right"><font size="2"><?=number_format($lirow['ShipQty']*$lirow['UnitPrice'],2)?></font></td>
										</tr>
<?
			$totalamt=$totalamt+$lirow['ShipQty']*$lirow['UnitPrice'];
		}?>
										<tr>
											<td colspan="5" align="right" valign="bottom"><font size="2">Invoice Total</font></td>
											<td align="right"><font size="2"><hr size="1"><br><?=number_format($totalamt,2)?></font></td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td colspan="4" align="right"><hr color="Black"></td>
							</tr>
							<tr>
								<td colspan="4" align="right">&nbsp;</td>
							</tr>
						</table>				
</div>
<script language="javascript">document.getElementById("invoices").innerHTML += document.getElementById("data_<?=$count?>").innerHTML;</script>
<?
		$count++;
		
		$progress += $inc_value;
		$ceiled_progress = ceil($progress);
		$ceiled_progress = $ceiled_progress > 100 ? "100" : $ceiled_progress;
		if($ceiled_progress != $old_value)
		{
			echo "<script language=\"javascript\">document.getElementById('percent').innerHTML = '".$ceiled_progress."%';document.getElementById('meter').width=".($ceiled_progress*2).";</script>\n";
		}
		flush();
	}
?>
<script language="javascript">document.getElementById("content").innerHTML = document.getElementById("invoices").innerHTML;</script>
<?
}
?>
</body>
</html>