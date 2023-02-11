<?php
require_once("session_check.inc");
require_once("../common_files/include/global.inc");

function DownloadFile($filename) 
{ 
    // Verify filename 
	if (empty($filename) || !file_exists($filename)) 
    { 
        return FALSE; 
    } 
    // Create download file name to be displayed to user 
    $saveasname = basename($filename);
    
	// Send binary filetype HTTP header
    header('Content-Type: application/octet-stream'); 
    // Send content-length HTTP header 
    header('Content-Length: '.filesize($filename)); 
    // Send content-disposition with save file name HTTP header
	header('Content-Disposition: attachment; filename="'.$saveasname.'"'); 
    header ("Pragma: no-cache"); 
	header ('Expires: 0'); 
	header ('Cache-Control: must-revalidate, post-check=0, pre-check=0'); 
	//header ('Pragma: public'); 
	
	// Output file 
    readfile($filename); 
    // Done 
    return TRUE; 
} 


$reccount = 0;
$rscustomer = mysqli_query($conn,"SELECT m.amico_id,
c.customers_firstname, 
c.customers_lastname, 
c.customers_email_address,
c.customers_telephone,
c.customers_fax, 
c.customers_password,
c.ssn,
a.entry_street_address, 
a.entry_street_address2,
a.entry_city, 
a.entry_zone_id, 
a.entry_postcode,
a.entry_company,
c.customers_id,
m2.amico_id AS parent_id
	FROM customers c, 
	address_book a, 
	tbl_member m ,
	tbl_member m2
	WHERE c.customers_id = a.customers_id 
	AND m.int_customer_id = c.customers_id 
	AND m.int_parent_id = m2.int_member_id
	AND a.address_book_id = 1 
	AND c.exported =  'N'");

while (list($amico_id,
	    $customers_firstname,
	    $customers_lastname,
	    $customers_email_address,
	    $customers_telephone,
	    $customers_fax, 
		$customers_password,
		$customers_ssn,
	    $entry_street_address,
		$entry_street_address2,
	    $entry_city,
	    $entry_state,
	    $entry_postcode,
		$entry_company,
		$customers_id,
		$parent_id) = mysqli_fetch_row($rscustomer)) {

	$rsordertotal = mysqli_query($conn,"SELECT zone_code FROM zones WHERE zone_id=$entry_state");
	list($state) = mysqli_fetch_row($rsordertotal);
	
	//Query to set the 'exported' field to 'Y' once this record
	//has been downloaded.
	$upd = mysqli_query($conn,"UPDATE customers SET exported='Y' WHERE customers_id=$customers_id");


	//$TransactionType="".",";
	$CustomerID="\"".$amico_id."\",";
	$CustomerName="\"".$customers_firstname.' '.$customers_lastname."\",";
	$AddressLine1="\"".$entry_company."\",";
	$AddressLine2="\"".$entry_street_address."\",";
	$City ="\"".$entry_city."\",";
	$State="\"".$state."\",";
	$ZIPCode="\"".$entry_postcode."\",";
	$TelephoneNumber="\"".$customers_telephone."\",";
	$ContactName="\"".$parent_id."\",";
	$Emailaddress="\"".$customers_email_address."\",";
	$CommentLine1="\"".$customers_password."\",";
	$CommentLine2="\"".$customers_ssn."\",";
	$ResellerSalesTaxID="\"".""."\",";
	$CustomerSinceDates="\"".""."\",";
	$FaxNumber="\"".$customers_fax."\"\n";

	$strrecord[$reccount]="/UPDATE,".$CustomerID.$CustomerName.$AddressLine1.$AddressLine2.$City.$State.$ZIPCode.$TelephoneNumber.$ContactName.$Emailaddress.$CommentLine1.$CommentLine2.$ResellerSalesTaxID.$CustomerSinceDates.$FaxNumber;
	$reccount=$reccount+1;
	

}

$ft=fopen("./customer.csv","w");
for($i=0;$i<$reccount;$i++){
	fputs($ft,$strrecord[$i]);
}
fclose($ft);
DownloadFile("./customer.csv");
