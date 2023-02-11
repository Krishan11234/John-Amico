<?php
require("../include/global.inc");

$query = mysqli_query($conn,"SELECT * FROM customers WHERE customers_id='$member_id'");
$f=mysqli_fetch_array($query);

$fname=$f['customers_firstname'];
$lname=$f['customers_lastname'];
$name=$fname.' '.$lname;
$email=$f['customers_email_address'];
$phone=$f['customers_telephone'];
$number_tickets=$t;
$number_tickets_dinner=$td;
$exp_date=$f['cc_expiry_date'];
$ccv=$f['cc_cvv'];
$card_type=$f['cc_type'];
$card_number=$f['cc_number'];
$amount=($td*20+$t*20+$tbl*36);
$amount_tickets=intval($t+$td);

$query = mysqli_query($conn,"SELECT * FROM address_book WHERE customers_id='$member_id' and address_book_id='1'");
$f=mysqli_fetch_array($query);

$address=$f['entry_street_address'].' '.$f['entry_street_address2'];
$city=$f['entry_city'];
$zip=$f['entry_postcode'];
?>


<html>
	<body>

<form action="http://www.chicagochicagobeautyshow.com/ja_order.php" method="post" name="form1">
 <input type="hidden" name="act" value="1">
 <input type="hidden" name="fname" value="<?=$fname?>">
 <input type="hidden" name="lname" value="<?=$lname?>">
 <input type="hidden" name="name" value="<?=$name?>">
 <input type="hidden" name="email" value="<?=$email?>">
 <input type="hidden" name="phone" value="<?=$phone?>">
 <input type="hidden" name="number_tickets" value="<?=$t?>">
 <input type="hidden" name="number_tickets_dinner" value="<?=$td?>">
 <input type="hidden" name="number_tickets_dinner2" value="<?=$tbl?>">
 <input type="hidden" name="exp_date" value="<?=$exp_date?>">
 <input type="hidden" name="ccv" value="<?=$ccv?>">
 <input type="hidden" name="card_type" value="<?=$card_type?>">
 <input type="hidden" name="card_number" value="<?=$card_number?>">
 <input type="hidden" name="amount" value="<?=$amount?>">
 <input type="hidden" name="amount_tickets" value="<?=$amount_tickets?>">
 <input type="hidden" name="address" value="<?=$address?>">
 <input type="hidden" name="state" value="<?=$state?>">
 <input type="hidden" name="city" value="<?=$city?>">
 <input type="hidden" name="zip" value="<?=$zip?>">
 <input type="hidden" name="names" value="<?=$guests?>">
</form>

<script>document.form1.submit();</script>

	</body>
</html>

