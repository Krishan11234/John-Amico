<?php
require_once("session_check.inc");
require_once("../common_files/include/global.inc");

function DownloadFile($filename) {
    //return true;

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

function getStateAbbrev($state) {
    global $conn;

	if($state != "")
	{
		list($abbrev) = mysqli_fetch_row(mysqli_query($conn,"SELECT zone_code FROM zones WHERE zone_name = '".$state."'"));

		return $abbrev;
	}
}

$reccount = 0;
$strrecord = array();
$rsorder = mysqli_query($conn,"SELECT 	o.orders_id,
					o.customers_id,
			       	o.customers_name,
			      	o.customers_street_address, 
				o.customers_city, 
				o.customers_state, 
				o.customers_postcode, 
				o.delivery_name, 
				o.delivery_street_address, 
				o.delivery_city, 
				o.delivery_state, 
				o.delivery_postcode, 
				o.cc_type, 
				o.cc_owner, 
				o.cc_number, 
				o.cc_expires,
				DATE_FORMAT(o.date_purchased, '%m/%d/%Y') as date_purchased,
				o.orders_status, t.amico_id, t.ec_id	 
			FROM orders o, tbl_member t WHERE o.int_member_id=t.int_member_id AND o.exported='N' LIMIT 0,100");

while (list($orders_id,
		$customers_id, 
	    $customers_name,
	    $customers_street_address,
	    $customers_city,
	    $customers_state,
	    $customers_postcode,
	    $delivery_name,
	    $delivery_street_address,
	    $delivery_city,
	    $delivery_state,
	    $delivery_postcode,
	    $cc_type,
	    $cc_owner,
	    $cc_number,
	    $cc_expires,
		$date_purchased,
	    $order_status,
		$amico_id, $ec_id) = mysqli_fetch_row($rsorder)) {

	$rsordertotal = mysqli_query($conn,"SELECT value FROM orders_total WHERE orders_id=$orders_id AND sort_order=5")	;
	list($value)  = mysqli_fetch_row($rsordertotal);


	//Query to set the 'exported' field in the db to 'Y' after this
	//entry has been grabbed.
	$upd = mysqli_query($conn,"UPDATE orders SET exported='Y' WHERE orders_id=$orders_id");

	//Remove the CC info. once this record has been downloaded.
	$cc_rem = mysqli_query($conn,"UPDATE orders SET cc_type='',cc_owner='',cc_number='',cc_expires='' WHERE orders_id=$orders_id");

	//SECION FOR /SOHDR
	$Process="/"."";
	$Line="SOHDR".",";
	$CustomerID=$amico_id.",";
	$OrderStatus="O".",";
	$OrderNumber=$orders_id.",";
	$Description=$Note="Online Purchase".",";
	$PurchaseOrderNumber="".",";
	$QuoteDate="".",";
	$OrderDate=$date_purchased.",";
	$RequiredDate=$date_purchased.",";
	$ReleaseDate=$date_purchased.",";
	$ShipVia="UPS".",";
	$FOB="O".",";
	$Terms="3".",";
	$IsTaxable="".",";
	$SalesRep=$ec_id.",";
	$Note="Online Purchase".",";
	$ConvertProbability="".",";
	$ReasonForHold="".",";
	$EcID=",".$ec_id."\n";
	// END /SOHDR



	//SECTION FOR /SOSUM
	$ProcessedIndicator="/"."";
	$LineIdentifie="SOSUM".",";
	$TaxID1="".",";
	$TaxID2="".",";
	$TaxID3="".",";
	$TaxAmount1="".","; 
	$TaxAmount2="".",";
	$TaxAmount3="".",";
	$MiscellaneousCharges ="".",";
	$Discount="".",";
	$FreightCharges="".",";
	$BillToName=$customers_name.",";
	$BillToAddress1=$customers_street_address.",";
	$BillToAddress2="".",";
	$BillToCity=$customers_city.",";
	$BillToState=getStateAbbrev($customers_state).",";
	$BillToZip=$customers_postcode.",";
	$ShipToName=$delivery_name.",";
	$ShipToAddress1=$delivery_street_address.",";
	$ShipToAddress2="".",";
	$ShipToCity=$delivery_city.",";
	$ShipToState=getStateAbbrev($delivery_state).",";
	$ShipToZip=$delivery_postcode.",";
	$DepositsApplied="".",";
	$PaymentAmount="0".",";
	if(trim($cc_type)!=""){
		$PaymentCashAccount="1".",";
		$PaymentCheckNumber ="-1".",";
		$CreditCardCompany=$cc_type.",";
		$CreditCardType=$cc_type.",";
		$CreditCardHolder=$cc_owner.",";
		$CreditCardNumber=$cc_number.",";
		$CreditCardExpiration=$cc_expires.",";
	}
	else{
		$PaymentCashAccount="1".",";
		$PaymentCheckNumber ="-1".",";
		$CreditCardCompany="".",";
		$CreditCardType="".",";
		$CreditCardHolder="".",";
		$CreditCardNumber="".",";
		$CreditCardExpiration="".",";
	}	
	$ApprovalCode=""."\n";
	//END /SOSUM

		//SOHDR
	$strrecord[$reccount]=$Process.$Line.$CustomerID.$OrderStatus.$OrderNumber.$Description.$PurchaseOrderNumber.$QuoteDate.$OrderDate.$RequiredDate.$ReleaseDate.$ShipVia.$FOB.$Terms.$IsTaxable.$SalesRep.$Note.$ConvertProbability.$ReasonForHold.$EcID;
	$reccount=$reccount+1;
		//QUERY FOR PRODUCTS
		$query="SELECT * FROM orders_products WHERE orders_id='".$orders_id."'";
		$res=mysqli_query($conn,$query) or die(mysql_error());
		while($row=mysqli_fetch_array($res)){
			// END QUERY FOR PRODUCTS
			//SOSLI
			//SECTION FOR /SOLI
			$Process2="/"."";
			$LineIdentify="SOLI".",";
			$Type="P".",";
			$Part=$row['products_model'].","; // Need more
			$Description1=$row['products_name'].",";
			$Description2="".",";
			$Description3="".",";
			$OrderQuantity=$row['products_quantity'].",";
			$UnitPrice=$row['final_price'].",";
			$DiscoutnAmount="".",";
			$ExtendedPrice=round($row['final_price']*$row['products_quantity'],2).",";
			$SalesAcount="1".",";
			$IsTaxable="Y".",";
			$WarehouseNumber=""."\n";
			//END /SOLI
			$strrecord[$reccount]=$Process2.$LineIdentify.$Type.$Part.$Description1.$Description2.$Description3.$OrderQuantity.$UnitPrice.$DiscoutnAmount.$ExtendedPrice.$SalesAcount.$IsTaxable.$WarehouseNumber;
			$reccount=$reccount+1;
		}	
	//SOSUM

	$strrecord[$reccount]=$ProcessedIndicator.$LineIdentifie.$TaxID1.$TaxID2.$TaxID3.$TaxAmount1.$TaxAmount2.$TaxAmount3.$MiscellaneousCharges.$Discount.$FreightCharges.$BillToName.$BillToAddress1.$BillToAddress2.$BillToCity.$BillToState.$BillToZip.$ShipToName.$ShipToAddress1.$ShipToAddress2.$ShipToCity.$ShipToState.$ShipToZip.$DepositsApplied.$PaymentAmount.$PaymentCashAccount.$PaymentCheckNumber.$CreditCardCompany.$CreditCardType.$CreditCardHolder.$CreditCardNumber.$CreditCardExpiration.$ApprovalCode;
	$reccount=$reccount+1;


}

//$ft=fopen( dirname(__FILE__) . "/csv/orders_export/order.txt","w");
//echo 'fopen failed. reason: ', error_get_last();

//echo '<pre>'; print_r($strrecord); die();

$orderFilePath = dirname(__FILE__) . "/csv/orders_export/order.txt";
file_put_contents($orderFilePath, implode(PHP_EOL, $strrecord));

/*for($i=0;$i<$reccount;$i++){
	fputs($ft,$strrecord[$i]);
}*/

//fclose($ft);

DownloadFile("./csv/orders_export/order.txt");

//Save a copy of this CSV file on the server.
$today = date("Ymd");
rename("./csv/orders_export/order.txt", "./csv/orders_export/order.txt.$today");
