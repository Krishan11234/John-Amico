<?php
Header("Content-type: application/vnd.ms-excel; name='excel'");
Header("Content-Disposition: attachment; filename=salon_inquire.xls");
//include "inquire_config.inc.php";
require_once('../common_files/include/global.inc');

if ($delsub) {
	$delsql="DELETE FROM salon_inquire WHERE id = $id LIMIT 1";
	$result=mysqli_query($conn,$delsql) or die(mysql_error());
	$delsub=0;
    header("Location: ".base_url()."/admin/index.php");
}

$sql="SELECT *, DATE_FORMAT(date, '%m/%d/%Y') as date FROM salon_inquire";
$res=mysqli_query($conn,$sql) or die(mysql_error());

echo "<html>";
echo "<head>";
echo "<title>Share the Wealth</title>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">";
echo "</head>";

echo "<body bgcolor=\"#FFFFFF\" text=\"#000000\">";
echo "<h3 align=center>Share the Wealth Inquirey List</h3>";

echo "<table border=\"1\" align=\"center\" width=\"100%\">";
echo "<tr bgcolor=\"#000000\">"; 
		
echo "<td align=\"center\" valign=\"top\"><font color=\"#ffffff\"><b>ID</b></font></td>";
echo "<td align=\"center\" valign=\"top\"><font color=\"#ffffff\"><b>Date</b></font></td>";
echo "<td align=\"center\" valign=\"top\"><font color=\"#ffffff\"><b>Name</b></font></td>";
echo "<td align=\"center\" valign=\"top\"><font color=\"#ffffff\"><b>Business Name</b></font></td>";
echo "<td align=\"center\" valign=\"top\"><font color=\"#ffffff\"><b>Address</b></font></td>";
echo "<td align=\"center\" valign=\"top\"><font color=\"#ffffff\"><b>City</b></font></td>";
echo "<td align=\"center\" valign=\"top\"><font color=\"#ffffff\"><b>State/Province</b></font></td>";
echo "<td align=\"center\" valign=\"top\"><font color=\"#ffffff\"><b>Postal/Zip Code</b></font></td>";
echo "<td align=\"center\" valign=\"top\"><font color=\"#ffffff\"><b>Daytime Phone</b></font></td>";
echo "<td align=\"center\" valign=\"top\"><font color=\"#ffffff\"><b>Other Phone</b></font></td>";
echo "<td align=\"center\" valign=\"top\"><font color=\"#ffffff\"><b>Fax</b></font></td>";
echo "<td align=\"center\" valign=\"top\"><font color=\"#ffffff\"><b>Email Address</b></font></td>";
echo "<td align=\"center\" valign=\"top\"><font color=\"#ffffff\"><b>Type of Company</b></font></td>";
echo "<td align=\"center\" valign=\"top\"><font color=\"#ffffff\"><b>Company Position</b></font></td>";
echo "<td align=\"center\" valign=\"top\"><font color=\"#ffffff\"><b>Company Services</b></font></td>";
echo "<td align=\"center\" valign=\"top\"><font color=\"#ffffff\"><b>Have you Cruised Before</b></font></td>";
echo "<td align=\"center\" valign=\"top\"><font color=\"#ffffff\"><b>Who is Joining You</b></font></td>";
echo "<td align=\"center\" valign=\"top\"><font color=\"#ffffff\"><b>Number Working at Your Locations</b></font></td>";

echo "</tr>";
while($row=mysqli_fetch_array($res)){
echo "<tr> ";
		echo "<td align=\"left\" valign=\"center\">".$row['id']."</td>";
		echo "<td align=\"left\" valign=\"center\">".$row['date']."</td>";
		echo "<td align=\"left\" valign=\"center\">".$row['User_name']."</td>";
		echo "<td align=\"left\" valign=\"center\">".$row['business']."</td>";
		echo "<td align=\"left\" valign=\"center\">".$row['address']."</td>";
		echo "<td align=\"left\" valign=\"center\">".$row['city']."</td>";
		echo "<td align=\"left\" valign=\"center\">".$row['province']."</td>";
		echo "<td align=\"left\" valign=\"center\">".$row['postal_code']."</td>";
		echo "<td align=\"left\" valign=\"center\">".$row['dayime_phone']."</td>";
		echo "<td align=\"left\" valign=\"center\">".$row['other_phone']."</td>";
		echo "<td align=\"left\" valign=\"center\">".$row['fax_number']."</td>";
		echo "<td align=\"left\" valign=\"center\"><a href=\"mailto: ".$row['User_email']."\">".$row['User_email']."</a></td>";
		echo "<td align=\"left\" valign=\"center\">".$row['company']."</td>";
		echo "<td align=\"left\" valign=\"center\">".$row['position']."</td>";
		echo "<td align=\"left\" valign=\"center\">".$row['services']."</td>";
		echo "<td align=\"left\" valign=\"center\">".$row['have_cruise']."</td>";
		echo "<td align=\"left\" valign=\"center\">".$row['Who_may_be_joining_you']."</td>";
		echo "<td align=\"left\" valign=\"center\">".$row['working_at_location']."</td>";
echo	"</tr>";
}
echo "</table>";