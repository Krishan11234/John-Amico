<?php
//include "inquire_config.inc.php";

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

if(isset($_POST['submit']))
{
	$sql="INSERT INTO salon_inquire SET User_name='".$_POST['User_name']."', business='".$_POST['business']."', address='".$_POST['address']."', city='".$_POST['city']."', province='".$_POST['province']."', postal_code='".$_POST['postal_code']."', dayime_phone='".$_POST['dayime_phone']."', other_phone='".$_POST['other_phone']."', fax_number='".$_POST['fax_number']."', User_email='".$_POST['User_email']."', company='".$_POST['company']."', position='".$_POST['position']."', services='".$_POST['services']."', have_cruise='".$_POST['have_cruise']."',  Who_may_be_joining_you ='".$_POST['Who_may_be_joining_you']."',  working_at_location='".$_POST['working_at_location']."',  date_mailed='".$_POST['mailed_year']."-".$_POST['mailed_month']."-".$_POST['mailed_day']."', date_followup='".$_POST['follow_year']."-".$_POST['follow_month']."-".$_POST['follow_day']."', call_completed='".$_POST['call_completed']."', notes ='".$_POST['notes']."', source ='".$_POST['source']."'";
	$res=mysqli_query($conn,$sql) or die(mysql_error());
	echo "<script>window.opener.document.location.reload();window.self.close();</script>";
    exit;
}

?>
<form method="post" action="<?=$_SERVER['PHP_SELF'];?>">
<input type="hidden" name="id" value="<?=$_REQUEST['id'];?>">
<table>
	<tr>
		<td>Name:</tD>
		<td><input type="text" name="User_name"></td>
	</tr>
	<tr>
		<td>Business Name:</tD>
		<td><input type="text" name="business"></td>
	</tr>
	<tr>
	<tr>
		<td>Address:</tD>
		<td><input type="text" name="address"></td>
	</tr>
	<tr>
	<tr>
		<td>City:</tD>
		<td><input type="text" name="city"></td>
	</tr>
	<tr>
	<tr>
		<td>State/ Province:</tD>
		<td><input type="text" name="province"></td>
	</tr>
	<tr>
	<tr>
		<td>Postal/ Zip Code:</tD>
		<td><input type="text" name="postal_code"></td>
	</tr>
	<tr>
	<tr>
		<td>Day Phone:</tD>
		<td><input type="text" name="dayime_phone"></td>
	</tr>
	<tr>
	<tr>
		<td>Other Phone:</tD>
		<td><input type="text" name="other_phone" ></td>
	</tr>
	<tr>
	<tr>
		<td>Fax:</tD>
		<td><input type="text" name="fax_number" value="<?=$row['fax_number'];?>"></td>
	</tr>
	<tr>
	<tr>
		<td>Email address:</tD>
		<td><input type="text" name="User_email" value="<?=$row['User_email'];?>"></td>
	</tr>
	<tr>
	<tr>
		<td>Type of Company:</tD>
		<td><input type="text" name="company" value="<?=$row['company'];?>"></td>
	</tr>
	<tr>
	<tr>
		<td>Your Position:</tD>
		<td><input type="text" name="position" value="<?=$row['position'];?>"></td>
	</tr>
	<tr>
	<tr>
		<td>Company Services:</tD>
		<td><input type="text" name="services" value="<?=$row['services'];?>"></td>
	</tr>
	<tr>
	<tr>
		<td>Have you cruised before:</tD>
		<td><input type="text" name="have_cruise" value="<?=$row['have_cruise'];?>"></td>
	</tr>
	<tr>
	<tr>
		<td>Who is joining you:</tD>
		<td><input type="text" name="Who_may_be_joining_you" value="<?=$row['Who_may_be_joining_you'];?>"></td>
	</tr>
	<tr>
	<tr>
		<td># Working at your location:</tD>
		<td><input type="text" name="working_at_location" value="<?=$row['working_at_location'];?>"></td>
	</tr>
	<tr>
<?$date_mailed = explode("-",$row['date_mailed']);?>
	<tr>
		<td>Date Mailed:</tD>
		<td>
			<select name="mailed_month">
				<?for($i=1;$i<=12;$i++){?>
					<option value="<?=$i;?>"><?=$i;?></option>
				<?}?>
			</select>
			<select name="mailed_day">
				<?for($i=1;$i<=31;$i++){?>
					<option value="<?=$i;?>" ><?=$i;?></option>
				<?}?>
			</select>
				<select name="mailed_year">
				<?for($i=2005;$i<=date('Y');$i++){?>
					<option value="<?=$i;?>" ><?=$i;?></option>
				<?}?>
			</select>

		</td>
	</tr>
	<tr>
	
	<?$date_followup = explode("-",$row['date_mailed']);?>
	<tr>
		<td>Date Followed up:</tD>
		<td>
			<select name="follow_month">
				<?for($i=1;$i<=12;$i++){?>
					<option value="<?=$i;?>"><?=$i;?></option>
				<?}?>
			</select>
			<select name="follow_day">
				<?for($i=1;$i<=31;$i++){?>
					<option value="<?=$i;?>"><?=$i;?></option>
				<?}?>
			</select>
				<select name="follow_year">
                    <?for($i=2005;$i<=(date('Y')+1);$i++){?>
					<option value="<?=$i;?>"><?=$i;?></option>
				<?}?>
			</select>

		</td>
	</tr>
	<tr>
	<tr>
		<td>Call Completed:</tD>
		<td><input type="text" name="call_completed" value="<?=$row['call_completed'];?>"></td>
	</tr>
	<tr>
	<tr>
	<tr>
		<td>Notes:</tD>
		<td><input type="textarea" name="notes" value="<?=$row['notes'];?>"></td>
	</tr>
	<tr>
	<tr>
	<tr>
		<td>Source:</tD>
		<td><input type="source" name="source" value="<?=$row['source'];?>"></td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr>

		<td colspan="2" align="center"><input type="submit" name="submit" value="Add Person">&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="Cancel" onClick="window.close()"></td>
	</tr>
</table>