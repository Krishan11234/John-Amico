<?php
/*  Project Name		: amico
   	Program name		: note_status.php
 	Program function	: insert record and listing status
 	Created Date  		: 6 Aug, 2003
 	Last Modified		: 6 Aug, 2003 
	Author				: Shehran
	Developed by	    : Colbridge Web Logics - www.colbridge.com */
//--------------------------------------------------------------------
require_once("session_check.inc");
require_once("../common_files/include/global.inc");

function get_family($amico_id, $depth = 0, $family = array())
{
    global $conn;

	$depth++;

	if($depth > 6)
	{
		return $family;
	}

	$sql = "SELECT tm2.int_member_id, tm2.amico_id FROM tbl_member tm1 INNER JOIN tbl_member tm2 ON tm1.int_member_id=tm2.int_parent_id WHERE tm1.amico_id = '$amico_id'";
	$result = mysqli_query($conn,$sql);
	echo mysql_error();

	while($row = mysqli_fetch_array($result))
	{
		$family[$row['int_member_id']] = array("depth" => $depth, "amico_id" => $row['amico_id']);

		$family = get_family($row['amico_id'], $depth, $family);
	}

	return $family;
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Online Member Commissions</title>
<style>
<!--
.command {font-size: 10pt; height: 22; font-family:arial; filter:progid:DXImageTransform.Microsoft.Gradient
(endColorstr='#ffffff', startColorstr='#CCCCCC', gradientType='1')}

-->
</style>
<script language="javascript">
var valid_id = false;

function validate(f)
{
	if(valid_id)
	{
		return true;
	}
	else
	{
		alert("You must enter a valid Amico ID!!");
		return false;
	}
}
</script>
</head>

<body bgcolor="#ffffff" BOTTOMMARGIN="0" LEFTMARGIN="0" MARGINHEIGHT="0" MARGINWIDTH="0" RIGHTMARGIN="0" TOPMARGIN="0" style="font-family:arial;">
<DIV ALIGN="center">
<table cellpadding="0" cellspacing="0">
	<TBODY>	 
     	<tr>
   			<td align="center" colspan="9"><img name="toplogo" src="../images/logo.gif" border="0">
			</td> 
	 	</tr>
    <tbody>
	<tr>
	<td>&nbsp;</td>
	</tr>
<?if(isset($msg)){?><tr><td><font color="red"><b><?=$msg?></b></font></td></tr><?}?>
    <tr>
		<td align="center" ><b><FONT color=#400080 
          size=4 font face ="Arial">Online Member Commissions</FONT>  </b></td>
    </tr>
	<div align="center">
	<table border="0">
		<tr>
			<td colspan="2" height="5"></td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<table>
				<form name="calc_form" method="GET" action="<?=$_SERVER['PHP_SELF']?>" onSubmit="return validate(this);">
					<tr>
						<td align="right">Select a Month/Year:</td>
						<td>
							<select name="month">
								<option value="01"<?if($_GET['month'] == "01"){?> SELECTED<?}?>>January
								<option value="02"<?if($_GET['month'] == "02"){?> SELECTED<?}?>>February
								<option value="03"<?if($_GET['month'] == "03"){?> SELECTED<?}?>>March
								<option value="04"<?if($_GET['month'] == "04"){?> SELECTED<?}?>>April
								<option value="05"<?if($_GET['month'] == "05"){?> SELECTED<?}?>>May
								<option value="06"<?if($_GET['month'] == "06"){?> SELECTED<?}?>>June
								<option value="07"<?if($_GET['month'] == "07"){?> SELECTED<?}?>>July
								<option value="08"<?if($_GET['month'] == "08"){?> SELECTED<?}?>>August
								<option value="09"<?if($_GET['month'] == "09"){?> SELECTED<?}?>>September
								<option value="10"<?if($_GET['month'] == "10"){?> SELECTED<?}?>>October
								<option value="11"<?if($_GET['month'] == "11"){?> SELECTED<?}?>>November
								<option value="12"<?if($_GET['month'] == "12"){?> SELECTED<?}?>>December
							</select>/<select name="year">
<?
for($i=2004;$i<=date("Y");$i++)
{
?>
								<option value="<?=$i?>"><?=$i?>
<?
}
?>
							</select>
						</td>
					</tr>
<?
$sql = "SELECT c.*, tm.int_member_id, tm.int_customer_id, tm.amico_id FROM tbl_member tm INNER JOIN customers c ON tm.int_customer_id=c.customers_id WHERE tm.amico_id!='' ORDER BY c.customers_lastname";
$result = mysqli_query($conn,$sql);
$count = 0;

$amico_ids = "";
$member_names = "";
while($row = mysqli_fetch_array($result))
{
	$amico_ids .= "\"".strtolower($row['amico_id'])."\",";
	$member_names .= "\"".addslashes($row['customers_firstname'])." ".addslashes($row['customers_lastname'])."\",";
}
$amico_ids = substr($amico_ids, 0, strlen($amico_ids)-1);
$member_names = substr($member_names, 0, strlen($member_names)-1);
?>
<script language="javascript">
function find_member(t, o)
{
	var amico_ids = new Array(<?=$amico_ids?>);
	var member_names = new Array(<?=$member_names?>);
	var str = t.value.toLowerCase();
	var found = false;

	for(count=0;count<amico_ids.length;count++)
	{
		if(str == amico_ids[count])
		{
			found = true;
			valid_id = true;
			o.innerHTML = member_names[count];
			break;
		}
	}

	if(!found)
	{
		valid_id = false;
		o.innerHTML = "Please Enter a Valid Member ID";
	}

	return;
}
</script>
					<tr>
						<td align="right">Enter an Amico ID:</td>
						<td><input type="text" name="amico_id" size="10" value="<?=$_GET['amico_id']?>" onKeyUp="find_member(this, document.getElementById('member'));"> - <b><font id="member" face="arial" size="2">Please Enter a Valid Member ID</font></b></td>
					</tr>
					<tr>
						<td align="center" colspan="2"><input type="submit" name="calculate" value="Calculate Commissions" class="command"></td>
					</tr>
					</form>
				</table>
			</td>
		</tr>
<?php
if(isset($_GET['calculate']))
{
	$start_date = "{$_GET['year']}-{$_GET['month']}-01 00:00:00";
	$end_date = "{$_GET['year']}-{$_GET['month']}-".date("t", mktime(0, 0, 0, $_GET['month'], 1, $_GET['year']))." 23:59:59";

	$sales = 0;
	$commissionable = 0;
	$commission = 0;

	$sql = "SELECT op.* FROM orders_products op 
      INNER JOIN orders o ON op.orders_id=o.orders_id 
      INNER JOIN tbl_member tm ON o.int_member_id=tm.int_member_id 
      WHERE tm.amico_id = '$amico_id' AND (o.date_purchased >= '$start_date' AND o.date_purchased <= '$end_date')";
	$result = mysqli_query($conn,$sql);
	echo mysql_error();

	if(mysqli_num_rows($result) > 0)
	{
		$p_sales = 0;
		$p_commissionable = 0;

		while($product = mysqli_fetch_array($result))
		{
			$sql = "SELECT * FROM tbl_commision_rule WHERE str_commision_rule = '".addslashes($product['products_model'])."' AND bit_active = 1";
			$result = mysqli_query($conn,$sql);
			echo mysql_error();

			if(mysqli_num_rows($result) > 0)
			{
				$rule = mysqli_fetch_array($result);

				if($rule['int_value'] <= 0)
				{
					continue;
				}
				else if($rule['bit_percentage'] == 1)
				{
					$p_commissionable += round(($rule['int_value']/100)*($product['final_price']*$product['products_quantity']), 2);
				}
				else
				{
					$p_commissionable += round($rule['int_value']*$product['products_quantity'], 2);
				}
			}
			else
			{
				$p_commissionable += round($product['final_price']*$product['products_quantity'], 2);
			}

			$p_sales += round($product['final_price']*$product['products_quantity'], 2);
		}

		if($p_commissionable >= 100)
		{
			$sales = $p_sales;
			$commissionable = $p_commissionable;
			$commission = round($commissionable*.05, 2);

			$family = get_family($amico_id);
			
			$member_list = "";

			foreach($family AS $int_member_id => $data)
			{
				$member_list .= $int_member_id.",";
			}

			$member_list = substr($member_list, 0, strlen($member_list)-1);

			$levels = array(.05, .05, .05, .05, .02, .02, .05);

			$sql = "SELECT op.*, o.int_member_id 
              FROM orders_products op 
              INNER JOIN orders o ON op.orders_id=o.orders_id 
              INNER JOIN tbl_member tm ON o.int_member_id=tm.int_member_id 
              WHERE tm.int_member_id IN ($member_list) AND (o.date_purchased >= '$start_date' AND o.date_purchased <= '$end_date')";
			$result = mysqli_query($conn,$sql);
			echo mysql_error();

			if(mysqli_num_rows($result) > 0)
			{
				while($product = mysqli_fetch_array($result))
				{
					$sql = "SELECT * FROM tbl_commision_rule WHERE str_commision_rule = '".addslashes($product['products_model'])."' AND bit_active = 1";
					$result = mysqli_query($conn,$sql);
					echo mysql_error();

					if(mysqli_num_rows($result) > 0)
					{
						$rule = mysqli_fetch_array($result);

						if($rule['int_value'] <= 0)
						{
							continue;
						}
						else if($rule['bit_percentage'] == 1)
						{
							$commissionable += round(($rule['int_value']/100)*($product['final_price']*$product['products_quantity']), 2);
							$commission += round((($rule['int_value']/100)*($product['final_price']*$product['products_quantity']))*$levels[$family[$product['int_member_id']]['depth']], 2);
						}
						else
						{
							$commissionable += round($rule['int_value']*$product['products_quantity'], 2);
							$commission += round(($rule['int_value']*$product['products_quantity'])*$levels[$family[$product['int_member_id']]['depth']], 2);
						}
					}
					else
					{
						$commissionable += round($product['final_price']*$product['products_quantity'], 2);
						$commission += round(($product['final_price']*$product['products_quantity'])*$levels[$family[$product['int_member_id']]['depth']], 2);
					}

					$sales += round($product['final_price']*$product['products_quantity'], 2);
				}
			}

			$sql = "SELECT op.* FROM orders_products op 
              INNER JOIN orders o ON op.orders_id=o.orders_id 
              WHERE o.refering_member = '$amico_id' AND (o.date_purchased >= '$start_date' AND o.date_purchased <= '$end_date')";
			$result = mysqli_query($conn,$sql);
			echo mysql_error();

			if(mysqli_num_rows($result) > 0)
			{
				while($product = mysqli_fetch_array($result))
				{
					$sql = "SELECT * FROM tbl_commision_rule WHERE str_commision_rule = '".addslashes($product['products_model'])."' AND bit_active = 1";
					$result = mysqli_query($conn,$sql);
					echo mysql_error();

					if(mysqli_num_rows($result) > 0)
					{
						$rule = mysqli_fetch_array($result);

						if($rule['int_value'] <= 0)
						{
							continue;
						}
						else if($rule['bit_percentage'] == 1)
						{
							$commissionable += round(($rule['int_value']/100)*($product['final_price']*$product['products_quantity']), 2);
							$commission += round((($rule['int_value']/100)*($product['final_price']*$product['products_quantity']))*.35, 2);
						}
						else
						{
							$commissionable += round($rule['int_value']*$product['products_quantity'], 2);
							$commission += round(($rule['int_value']*$product['products_quantity'])*.35, 2);
						}
					}
					else
					{
						$commissionable += round($product['final_price']*$product['products_quantity'], 2);
						$commission += round(($product['final_price']*$product['products_quantity'])*.35, 2);
					}

					$sales += round($product['final_price']*$product['products_quantity'], 2);
				}
			}

			$levels = array(.35, .0325, .0325, .0325, .013, .013, .0325);

			$sql = "SELECT op.*, o.int_member_id 
              FROM orders_products op 
              INNER JOIN orders o ON op.orders_id=o.orders_id 
              INNER JOIN tbl_member tm ON o.refering_member=tm.amico_id 
              WHERE tm.int_member_id IN ($member_list) AND (o.date_purchased >= '$start_date' AND o.date_purchased <= '$end_date')";
			$result = mysqli_query($conn,$sql);
			echo mysql_error();

			if(mysqli_num_rows($result) > 0)
			{
				while($product = mysqli_fetch_array($result))
				{
					$sql = "SELECT * FROM tbl_commision_rule WHERE str_commision_rule = '".addslashes($product['products_model'])."' AND bit_active = 1";
					$result = mysqli_query($conn,$sql);
					echo mysql_error();

					if(mysqli_num_rows($result) > 0)
					{
						$rule = mysqli_fetch_array($result);

						if($rule['int_value'] <= 0)
						{
							continue;
						}
						else if($rule['bit_percentage'] == 1)
						{
							$commissionable += round(($rule['int_value']/100)*($product['final_price']*$product['products_quantity']), 2);
							$commission += round((($rule['int_value']/100)*($product['final_price']*$product['products_quantity']))*$levels[$family[$product['int_member_id']]['depth']], 2);
						}
						else
						{
							$commissionable += round($rule['int_value']*$product['products_quantity'], 2);
							$commission += round(($rule['int_value']*$product['products_quantity'])*$levels[$family[$product['int_member_id']]['depth']], 2);
						}
					}
					else
					{
						$commissionable += round($product['final_price']*$product['products_quantity'], 2);
						$commission += round(($product['final_price']*$product['products_quantity'])*$levels[$family[$product['int_member_id']]['depth']], 2);
					}

					$sales += round($product['final_price']*$product['products_quantity'], 2);
				}
			}
		}
	}

?>
		<TR>
			<td colspan="2">&nbsp;</td>
		</tr>
		<TR>
			<td colspan="2" align="center">Sales: $<?=number_format($sales, 2)?>&nbsp;&nbsp;&nbsp;&nbsp;Commissionable: $<?=number_format($commissionable, 2)?>&nbsp;&nbsp;&nbsp;&nbsp;Commission Earned: $<?=number_format($commission, 2)?></td>
		</tr>
<?

}
?>
		<TR>
			<td colspan="2">&nbsp;</td>
		</tr>		
		<TR>
			<td colspan="2" align="center"><a href="./index.php">Main Menu</a> </td>
		</tr>	
	</table>
	
	</div>
	</TBODY>	 
</table>
<script language="javascript">
find_member(document.calc_form.amico_id, document.getElementById('member'));
</script>
