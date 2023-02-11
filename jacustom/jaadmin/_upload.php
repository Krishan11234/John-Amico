<?php
require_once("session_check.inc");
require_once("../common_files/include/global.inc");

if ($act=='1') {
    $imported=0;

    if( !empty($_POST['chapter_id']) ) {

        $chapter_id = filter_var($_POST['chapter_id'], FILTER_SANITIZE_STRING);

        $filename = $_FILES['ffile']['tmp_name'];

        $handle = fopen($filename, 'r');

        //skip first line
        $buffer = fgets($handle, 4096);

        if( !empty($handle) ) {
            // delete previously imported members
            $query = mysqli_query($conn, "SELECT * FROM tbl_member WHERE  chapter_id='" . $chapter_id . "' AND is_salon='yes'") or die (mysql_error());
            while ($f = mysqli_fetch_array($query)) {
                $member_id = $f['int_member_id'];
                $customer_id = $f['int_customer_id'];

                $q = mysqli_query($conn, "DELETE FROM tbl_member WHERE int_member_id='$member_id'") or die (mysql_error());
                $q = mysqli_query($conn, "DELETE FROM customers WHERE customers_id='$customer_id'") or die (mysql_error());
                $q = mysqli_query($conn, "DELETE FROM address_book WHERE customers_id='$customer_id'") or die (mysql_error());
            }
        }

        while (!feof($handle)) {

            $line = fgets($handle, 4096);

            $split = explode("	", $line);

            $str_title = addslashes(trim($split[9]));
            $customers_firstname = addslashes(trim($split[8]));
            $customers_lastname = addslashes(trim($split[7]));
            $customers_telephone = addslashes(trim($split[6]));
            $entry_company = addslashes(trim($split[1]));
            $entry_firstname = addslashes(trim($split[8]));
            $entry_lastname = addslashes(trim($split[7]));
            $entry_street_address = addslashes(trim($split[2]));
            $entry_postcode = addslashes(trim($split[5]));
            $entry_city = addslashes(trim($split[3]));
            $entry_state = addslashes(trim($split[4]));

            $state_id = '';
            $q = mysqli_query($conn, "SELECT * FROM zones WHERE zone_code='$entry_state'") or die (mysql_error());
            $f = mysqli_fetch_array($q);
            $entry_state = $f['zone_id'];


            // check if such Amico ID already exists
            if ($entry_company != '') {

                $insert = mysqli_query($conn, "INSERT INTO `tbl_member` (`chapter_id`, `amico_id`, `int_parent_id`, `int_designation_id`, `str_title`, `bit_active`, `int_downline_price_level`, `int_price_level`, `mtype`, `ec_id`, `is_salon`) VALUES ('$chapter_id', '$amico_id', '$int_parent_id', '$int_designation_id', '$str_title', '1', '$int_downline_price_level', '$int_price_level', 'm', '$ec_id', 'yes')") or die (mysql_error());
                $member_id = mysqli_insert_id($conn);

                $insert = mysqli_query($conn, "INSERT INTO `customers` (`customers_firstname`, `customers_lastname`, `customers_email_address`, `customers_telephone`, `customers_telephone1`, `customers_telephone2`, `customers_fax`, `customers_password`, `ssn`, `license_number`) VALUES ('$customers_firstname', '$customers_lastname', '$customers_email_address', '$customers_telephone', '$customers_telephone1', '$customers_telephone2', '$customers_fax', '$customers_password', '$ssn', '$license_number')") or die (mysql_error());
                $customer_id = mysqli_insert_id($conn);

                $insert = mysqli_query($conn, "INSERT INTO `address_book` (`customers_id`, `address_book_id`, `entry_company`, `entry_firstname`, `entry_lastname`, `entry_street_address`, `entry_street_address2`, `entry_postcode`, `entry_city`, `entry_state`, `entry_country_id`) VALUES ('$customer_id', '1', '$entry_company', '$entry_firstname', '$entry_lastname', '$entry_street_address', '$entry_street_address2', '$entry_postcode', '$entry_city', '$entry_state', '223')") or die (mysql_error());

                $insert = mysqli_query($conn, "INSERT INTO `address_book` (`customers_id`, `address_book_id`) VALUES ('$customer_id', '2')") or die (mysql_error());
                $insert = mysqli_query($conn, "INSERT INTO `address_book` (`customers_id`, `address_book_id`) VALUES ('$customer_id', '3')") or die (mysql_error());

                $update = mysqli_query($conn, "UPDATE `tbl_member` SET `int_customer_id`='$customer_id' WHERE `int_member_id`='$member_id'") or die (mysql_error());


                $imported++;
            }

            $error = $imported . " salons have been added.";

        }

        fclose($handle);
    }
}

?>
<html>
<head>
<title>JA Administrative Section - Upload Salons</title>

<style>
<!--
.command {font-size: 10pt; height: 22; font-family:arial; filter:progid:DXImageTransform.Microsoft.Gradient
(endColorstr='#ffffff', startColorstr='#CCCCCC', gradientType='1')}

-->
</style>

<link href="../css/login.css" rel="stylesheet" type="text/css">
<link href="../css/stylesheet.css" rel="stylesheet" type="text/css">

</head>
<body bgcolor="#ffffff" BOTTOMMARGIN="0" LEFTMARGIN="0" MARGINHEIGHT="0" 
	MARGINWIDTH="0" RIGHTMARGIN="0" TOPMARGIN="0">

<br/>
<FONT face="Arial" SIZE="2">
&nbsp;&nbsp;Salons from the file will be added to website's database.<br/>
&nbsp;&nbsp;Amico ID should not be empty!
<br/><br/>

<? if ($error!='') {echo '<p align="center">'.$error.'</p>';}; ?>
                    

<center>

<form action="_upload.php" method="post" ENCTYPE="multipart/form-data">
<TABLE BORDER="0" WIDTH="95%" CELLPADDING="0" CELLSPACING="0">
  <TR>
  	<TD ALIGN="right"><FONT face="Arial" SIZE="2">Browse file:</FONT></TD>
	<td>&nbsp;<input type="file" name="ffile"/>
  </TR> 
  <TR>
<TD align="center">
</TABLE>
<input type="submit" value="Submit">
<input type="hidden" name="chapter_id" value="<?=$amico_id?>">
<input type="hidden" name="act" value="1">
</form>

</center>
</body>
</html>
