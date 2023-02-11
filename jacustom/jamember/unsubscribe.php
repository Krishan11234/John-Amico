<?php

require_once("../common_files/include/config.php");

// var_dump(base_url());exit;

if(isset($_GET['member_id']) && isset($_GET['validation_hash']) && isset($_GET['subscription_member_id'])) {
	$expected = md5($_GET['member_id'].UNSUBSCRIBE_STRING);

	if($_GET['validation_hash'] != $expected){
		throw new Exception("Validation failed");
	}

	$check_unsubscribe_sql = " SELECT * FROM tbl_member_subscription WHERE ref_member_id=".$_GET['member_id']." AND sub_member_id=".$_GET['subscription_member_id']." AND subscribed= 0 LIMIT 1";
    $query = mysqli_query($conn, $check_unsubscribe_sql);
    $rows = mysqli_num_rows($query);

    if( mysqli_num_rows($query) > 0 ) {
        echo 'Sorry, you have already unsubscribed. <br />
			  <p>
       	  	  <p>Contact store for more details regarding this cancellation  of subscription. </p>
       	      <p><span class="_CONTENT_"><br/>
   	      	  Thank you for using our product(s). Visit our <a style=\'font-weight:bold\' href="'.CUSTOM_BASE_URL.'">store</a> for more products and exciting offers.<br/>
   	          </span></p>';
   	  exit;
    }

	$unsubscribe_sql = "INSERT INTO tbl_member_subscription (ref_member_id, sub_member_id, subscribed, created_at) VALUES (". $_GET['member_id'].",".$_GET['subscription_member_id'].", 0,'". date('Y-m-d H:i:s'). "')";
	if(mysqli_query($conn, $unsubscribe_sql)){
		$subscription_text = 'Your subscription has been cancelled. <br />
							  <p>
		               	  	  <p>Contact store for more details regarding this cancellation  of subscription. </p>
		               	      <p><span class="_CONTENT_"><br/>
		           	      	  Thank you for using our product(s). Visit our <a style=\'font-weight:bold\' href="'.CUSTOM_BASE_URL.'">store</a> for more products and exciting offers.<br/>
		           	          </span></p>';

		echo $subscription_text; 
		exit;
	}
}


