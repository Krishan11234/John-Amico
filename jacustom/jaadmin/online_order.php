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

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['continue'])){
	$query="select * from tbl_member where int_customer_id='".$customersid."'";
	//echo $query;
	$rsmember=mysqli_query($conn,$query) or die(mysql_error());
	if(mysqli_result($rsmember,0,int_price_level)==1){
		$rsproduct=mysqli_query($conn,"select products_price_A from products where products_id=".$_POST['productid']);}
	else{
		$rsproduct=mysqli_query($conn,"select products_price_B from products where products_id=".$_POST['productid']);}


list($price)=mysqli_fetch_row($rsproduct);

if (trim($_SESSION['orders'])==""){
	$_SESSION['orders']=$_SESSION['orders'].$_POST['productid'];
	$_SESSION['orders']=$_SESSION['orders'].','.$_POST['qty'];
	$_SESSION['orders']=$_SESSION['orders'].','.$price;
}
else{
	$_SESSION['orders']=$_SESSION['orders'].",".$_POST['productid'];
	$_SESSION['orders']=$_SESSION['orders'].','.$_POST['qty'];
	$_SESSION['orders']=$_SESSION['orders'].','.$price;
}
//echo $_SESSION[orders];
header("Location: checkout.php");
}

if (isset($_POST['ok'])){
$_SESSION['customerid']=$_GET['customerid'];
$_SESSION['orders']="";
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Status</title>
<style>
<!--
.command {font-size: 10pt; height: 22; font-family:arial; filter:progid:DXImageTransform.Microsoft.Gradient
(endColorstr='#ffffff', startColorstr='#CCCCCC', gradientType='1')}

-->
</style>

<script language="javascript">
<!--
function Validate(theform) {
	if(theform.productid.value==0){
	alert("Please select an Item !");
	theform.productid.focus();
	return false;
	}
	if(theform.qty.value<=0){
	alert("Please enter a Quatity !");
	theform.qty.focus();
	return false;
	}

return true
}


function perform_edit(Link) {
	location.href=Link;
}

function confirmCleanUp(Link) {
   if(confirm("Are you sure you want to delete this Status ?")) {      	
		location.href=Link;
   }
}

function isEmpty(s){   
	return ((s == null) || (s.length == 0) || (s.substr(0,1) == " "))
}

function check_empty_value(frm){
     if(isEmpty(frm.note_status_type.value)){
    	alert("Please enter the Status ?");
		frm.note_status_type.focus();
	    return false;
	} 
}		
function checkmember(frm){
	 if(isEmpty(frm.memberid.value) && (frm.customerid.value!=0)){
    	alert("Please enter a Member ID ?");
		frm.memberid.focus();
	    return false;
	}
	return true;
}
//-->	 
</script>	
</head>

<body bgcolor="#ffffff" BOTTOMMARGIN="0" LEFTMARGIN="0" MARGINHEIGHT="0" MARGINWIDTH="0" RIGHTMARGIN="0" TOPMARGIN="0">
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
    <tr>
		<td align="center" ><b><FONT color=#400080 
          size=4 font face ="Arial">Manage Customer / Quick Order</FONT>  </b></td>
    </tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td align="center"><font color="Red"><b><?=$_GET['msg']?></b></font></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>

	</tr>
	
	
	<td>
				<form action="online_order.php" method="post" name="theform" >
				<center><FONT face="Arial" size="2" >State</font>
					<select name="zone">
						<option value = 0 >None Selected</option>
						<?php
                        $zone = ( !empty($zone) ? $zone : '' );
						$rs_zone = mysqli_query($conn,"select z.zone_id, z.zone_name from zones z, countries c  where z.zone_country_id=c.countries_id and c.countries_iso_code_3 ='USA' order by zone_name");
						while(list($i_zoneid, $s_zone)= mysqli_fetch_row($rs_zone)){
							if($i_zoneid==$zone)
								echo '<option value ='.$i_zoneid.' selected>'.$s_zone.'</option>'; 	
							else
								echo '<option value ='.$i_zoneid.' >'.$s_zone.'</option>'; 	
						}
					
						
						
					//sort ($customersname, "select c.customers_id, c.customers_firstname, c.customers_lastname, m.amico_id from customers c, tbl_member m zones z countries u WHERE c.customers_id=m.int_customer_id and z.zone_country_id=c.countries_id and u.countries_iso_code_3 ='USA' order by c.customers_lastname");
				
						
						?>
						
						
						<?php
				/*		
	if (isset ($_REQUEST["zone"])){ 	
"select c.customers_id, c.customers_firstname, c.customers_lastname, m.amico_id from customers c, tbl_member m, zones z, countries u WHERE c.customers_id = m.int_customer_id"; //and z.zone_country_id = u.countries_id and u.countries_iso_code_3 ='USA' order by c.customers_lastname";
				}
								
else {
"select c.customers_id, c.customers_firstname, c.customers_lastname, m.amico_id from customers c, tbl_member m WHERE c.customers_id=m.int_customer_id order by c.customers_lastname");
}					
			*/	
	?>
						
						
						
					</select> <input type="Submit" name="Sort" value="Sort"> </form>
				</center></td>
	
	
	
	
	
	
	

	<div align="center">
	<form action="customers.php" method="post" name="theform" onsubmit="return checkmember(this);">
	<table border="0">
		<?php
		if(!isset($_REQUEST['step'])){
		?>
		<form action="customers.php" method="post" name="theform" onsubmit="return checkmember(this);">
		<tr>
			<td >Choose Customer&nbsp;</td>
			<?php
			$rscustomer=mysqli_query($conn,"select c.customers_id, c.customers_firstname, c.customers_lastname, m.amico_id from customers c, tbl_member m WHERE c.customers_id=m.int_customer_id order by c.customers_lastname");
			?>
			<td>
				<select name="customers_id">
					
					<option value="0">Please Select</option>
					<?php
					while(list($customerid,$customername, $customerlastname, $amico_id)=mysqli_fetch_row($rscustomer)){?>
						<option value="<?=$customerid;?>"><?=$customername;?> <?=$customerlastname;?> - <?=$amico_id;?></option>
					<?}?>
				
				</select>
			
			</td>		
		</tr>
		<tr>
			<td colspan="2" align="center">
				<!--<input type="Text" name="memberid" size="10"> -->
				<input type="Submit" name="continue" value="Continue" class="command">
			</td>		
		</tr>
		<tr>
		<td>&nbsp;</td>
		</tr>
		
		<tr>
			<td align="left"><a href="./customers.php">New Customer</a></td>
			<td align="right"><a href="./index.php">Main Menu</a></td>		
		</tr>
		<tr>
			<td> </td>
			<td>&nbsp;</td>		
		</tr>
		</form>
		<?php
		}
		else if($_REQUEST['step']==1){
		$rscustomer=mysqli_query($conn,"select customers_firstname,customers_lastname from customers where customers_id=".$_SESSION['customerid']);
		list($customerfirstname,$customerlastname)=mysqli_fetch_row($rscustomer);
		?>
		<form action="online_order.php?customersid=<?=$customerid;?>" method="post" onsubmit=" return Validate(this)">
		<tr>
			<td>Member Name:</td>
			<td><?=$customerfirstname ?> <?=$customerlastname ?></td>
		</tr>
		<tr>
			<td>Choose Product:</td>	
			<?php
			$rsproduct=mysqli_query($conn,"select products_id,products_name from products_description");
			?>
			<td>
				<select name="productid">
					
					<option value="0">Please Select</option>
					<?php
					while(list($productid,$productname)=mysqli_fetch_row($rsproduct)){
						echo'<option value="'.$productid.'">'.$productname.'</option>';
					}
					?>
				
				</select>
			</td>		
		</tr>
		<tr>
			<td>Quatity:</td>
			<td><input type="Text" name="qty" value="0"></td>
		</tr>
		<tr>
			<td colspan="2">
		<table align="center">
		<tr>
		<td>
			<input type="Submit" name="continue" value="Continue" class="command">
			<input type="hidden" name="step" value="1">
		</form>
		</td>
		<td>
		<form action="checkout.php">
			<input type="Submit" name="cancel" value="Cancel" class="command">
		</form>
		</td>
		<td>

		<form action="online_order.php">
			<input type="Submit" name="abort" value="Quit Shopping" class="command">
		</form>
		</td>
		</tr>
		
		</table>
		</tr>

		
		
		<?php
		}
		?>
	</table>
	
	</div>
	</TBODY>	 
</table> 