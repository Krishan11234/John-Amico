<?php

require_once(dirname(__FILE__) . '/config.php');
require_once(dirname(__FILE__) . '/common_functions.php');
if( php_sapi_name() !== 'cli' ) {
    require_once(dirname(__FILE__) . '/menu.php');
}

if( is_admin_logged_in() ) {
    require_once( base_admin_path() . '/functions.php');
}

// parameters for local dev server
/*$db="amico_full";
$host="sql1";
$username="Administrator";
$password="password"; */




//echo '<pre>'; var_dump($conn); die();

//define("CONN",$connct);
//define("conn",$connct);
//$check=mysql_select_db(DB,CONN);

$_get=$_GET;
$_post=$_POST;
$_session = !empty($_SESSION) ? $_SESSION : array();

if(!empty($_GET)) extract($_GET);
if(!empty($_POST)) extract($_POST);
//if(!empty($_SESSION)) extract($_SESSION);
if(!empty($_SERVER)) extract($_SERVER);
if(!empty($_FILES)) extract($_FILES);


$login_error_texts = array(
    1 => 'Please enter a valid User ID and Password.',
    2 => 'Please enter a valid User ID.',
    3 => 'Please enter a valid Password.',
    4 => 'Your login information is not correct. Please Re-Enter a valid User Name and Password.',
    5 => 'Account inactive or disabled. Please contact site admin.',
);



// to check weather the database exists or not   
if (empty($conn->connect_error)){
	$DBhasSetup = 1;
    $check = 1;
}
else{
	$DBhasSetup = 0;
    $check = 0;
}	
	
if( !function_exists('open_db') ) {
    // ------function to open the database ---------------
    function open_db($db, $host, $username, $password) {
        $connct = mysqli_connect($host, $username, $password, $db);
        if ($connct) {
            $status = 0;
            return $connct;
        }
        else {
            $status = mysqli_error($connct);

            return $status;
        }
    }
}

if( !function_exists('close_db') ) {
    // ------function to close the database ---------------
    function close_db($connct) {
        if (empty($connct)) {
            global $connct;
        }

        if (mysqli_close($connct)) {
            $status = 1;
        }
        else {
            $status = 0;
        }
        return ($status);
    }
}

if( !function_exists('insert_fields') ) {
    // ----- function to insert values in a table------
    function insert_fields($connct, $table, $in_fieldlist, $in_values) {
        if (empty($connct)) {
            global $connct;
        }

        $sql = "insert into $table ($in_fieldlist) values ($in_values)";
        $result = mysqli_query($connct, $sql);
        if (!$result) {
            echo("Unable to Insert value into " . $table . " table....<br>");
            echo $sql;
            die(mysqli_error($connct));
            return;
        }
        return $result;
    }
}

if( !function_exists('sel_rows') ) {
    // ----- function to select records from a table------
    function sel_rows($connct, $table, $fieldlist, $condition) {
        if (empty($connct)) {
            global $connct;
        }

        $sql = "select $fieldlist from $table $condition ";
        $result = mysqli_query($connct, $sql);
        if (!$result) {
            echo("Unable to Fetch " . $fieldlist . " from " . $table . " table.....<br>");
            die(mysqli_error($connct));
            return;
        }
        return $result;
    }
}

if( !function_exists('del_rows') ) {
    // ----- function to delete rows from a table------
    function del_rows($connct, $table, $condition) {
        if (empty($connct)) {
            global $connct;
        }

        $sql = "delete from $table $condition";
        $result = mysqli_query($connct, $sql);
        if (!$result) {
            echo("Unable to Delete rows from " . $table . " table....<br>");
            echo $sql;
            die(mysqli_error($connct));
            return;
        }
        return $result;
    }
}

if( !function_exists('update_rows') ) {
    // ----- function to update records in a table------
    function update_rows($connct, $table, $fieldlist, $condition) {
        if (empty($connct)) {
            global $connct;
        }

        $sql = "update $table set $fieldlist $condition";
        //echo '<pre>'; echo $sql; echo '</pre>';
        $result = mysqli_query($connct, $sql);
        if (!$result) {
            echo("Unable to Update " . $fieldlist . " in " . $table . " table......<br>");
            echo $sql;
            die(mysqli_error($connct));
            return;
        }
        return $result;
    }
}

if( !function_exists('send_mail') ) {
    // ----- function to send mail ----
    /*	four parameters..
	1.	$mail_to  		: the mail address to send the mail
	2.  $mail_from		: from address
	3.  $sitelink		: Corresponding link for the site. 
	4.  $message		: Actual message

function send_mail($mail_to,$mail_from, $sitelink, $message) {
	$m_stat = mail($mail_to, $the_message, "$sitelink \n $message \n \n \n ", "From: $mail_from \nReply-To: $mail_from \nX-Mailer: PHP/" . phpversion()); 
}*/
}

if( !function_exists('log_admin') ) {
    function log_admin($int_admin_id, $str_username, $user_type = 'm') {
        global $connct;

        mysqli_query($connct, "insert into tbl_admin_logs set user_type='$user_type', int_admin_id='$int_admin_id', str_username='$str_username', dt=now()");
    }
}

if( !function_exists('make_customer_file') ) {
    function make_customer_file($customer_id) {
        global $connct;

        $sql = "SELECT m.amico_id,
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
			a2.entry_firstname as s_firstname,
			a2.entry_lastname as s_lastname, 
			a2.entry_street_address as s_street_address, 
			a2.entry_street_address2 as s_street_address2,
			a2.entry_city as s_city, 
			a2.entry_zone_id as s_zone_id, 
			a2.entry_postcode as s_postcode,
			a2.entry_company,
			c.customers_id,
			m2.amico_id AS parent_id,
			ec.timestamp,
			m.ec_id
		FROM customers c, 
			address_book a, 
			address_book a2,
			tbl_member m ,
			tbl_member m2,
			tbl_member_ec ec
		WHERE c.customers_id = a.customers_id 
		AND c.customers_id = a2.customers_id
		AND m.int_customer_id = c.customers_id 
		AND m.int_parent_id = m2.int_member_id
		AND m.amico_id = ec.amico_id
		AND a.address_book_id = 1 
		AND a2.address_book_id = 2
		AND c.customers_id =  '$customer_id'";
//	echo $sql."<br>";
//	exit;
        $rscustomer = mysqli_query($connct, $sql);

        list($amico_id,
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
            $s_firstname,
            $s_lastname,
            $s_street_address,
            $s_street_address2,
            $s_city,
            $s_zone_id,
            $s_postcode,
            $s_entry_company,
            $customers_id,
            $parent_id,
            $join_date,
            $ec_id) = mysqli_fetch_row($rscustomer);

        $rsordertotal = mysqli_query($connct, "SELECT zone_code FROM zones WHERE zone_id=$entry_state");
        list($state) = mysqli_fetch_row($rsordertotal);

        //$TransactionType="".",";
        $CustomerID = "\"" . $amico_id . "\",";
        $CustomerName = "\"" . $customers_firstname . ' ' . $customers_lastname . "\",";
        $AddressLine1 = "\"" . $entry_street_address . "\",";
//	$AddressLine2		="\"" . $entry_street_address2 . "\",";
        $CityState = "\"" . $entry_city . ", " . $state . "\",";
        $ZIPCode = "\"" . $entry_postcode . "\",";
        $TelephoneNumber = "\"" . $customers_telephone . "\",";
        $ContactName = "\"" . $parent_id . "\",";
        $CommentLine1 = "\"" . $customers_password . "\",";
        $CommentLine2 = "\"" . $customers_ssn . "\",";
        $DefaultTerms = "\"3\", ";
        $DefaultSalesRep = "" . $ec_id . ", ";
        $DefaultSalesAcct = "1, ";
        $DefaultCustomerNotes = "1, ";
        $DefaultStdItemPrice = ", ";
        $SubjectToFinanceCharge = ", ";
        $AllowDunningMsgs = ", ";
        $PrintStatements = ", ";
        $UseBalanceFwd = ", ";
        $DefaultDiscountRate = ", ";

        if ($state == "IL") {
            $FirstDefaultSalesTxId = "\"COOK\",";
        }
        else {
            $FirstDefaultSalesTxId = ", ";
        }

        $SecondDefaultTxId = ", ";
        $ThirdDefaultTxId = ", ";
        $CreditLimit = ", ";
        $HighestBalance = ", ";
        $YearToDateSales = ", ";
        $YearToDateAdjustment = ", ";
        $MonthToDateSales = ", ";
        $MonthToDateAdjustment = ", ";
        $OnCreditHold = ", ";
        $CustomerCategory = ", ";
        $ResellerSalesTaxID = ", ";
        $FaxNumber = "" . $customers_fax . ", ";
        $Emailaddress = "" . $customers_email_address . ",";
        $ShipToName = "\"" . $s_firstname . ' ' . $s_lastname . "\", ";
        $ShipToAddress1 = "" . $s_street_address . ", ";
        $ShipToAddress2 = "" . $s_street_address2 . ", ";

        $rsshipstate = mysqli_query($connct, "SELECT zone_code FROM zones WHERE zone_id = '" . $s_zone_id . "'");
        list($sstate) = mysqli_fetch_row($rsshipstate);

        $ShipToCityState = "\"" . $s_city . ", " . $sstate . "\", ";
        $ShipToZipcode = "" . $s_postcode . ", ";
        $PurchasingContact = ", ";
        $PurchasingContactNum = ", ";
        $PurchasingContactFax = ", ";
        $PurchasingContactEmail = ", ";
        $Custom1 = ", ";
        $Custom2 = ", ";
        $Custom3 = ", ";
        $Custom4 = ", ";
        $Custom5 = ", ";
        $DefaultICPart = " ";

        //$CustomerSinceDates	="\"" . date('m/d/y', strtotime($join_date)) . "\",";

        $fp = fopen($_SERVER["DOCUMENT_ROOT"] . "/customer_files/AEC102808!" . $customer_id . ".txt", "w");
        fwrite($fp, "/UPDATE," . $CustomerID . $CustomerName . $AddressLine1 . $AddressLine2 . $CityState . $ZIPCode . $TelephoneNumber . $ContactName . $CommentLine1 . $CommentLine2 . $DefaultTerms . $DefaultSalesRep . $DefaultSalesAcct . $DefaultCustomerNotes . $DefaultStdItemPrice . $SubjectToFinanceCharge . $AllowDunningMsgs . $PrintStatements . $UseBalanceFwd . $DefaultDiscountRate . $FirstDefaultSalesTxId . $SecondDefaultTxId . $ThirdDefaultTxId . $CreditLimit . $HighestBalance . $YearToDateSales . $YearToDateAdjustment . $MonthToDateSales . $MonthToDateAdjustment . $OnCreditHold . $CustomerCategory . $ResellerSalesTaxID . $FaxNumber . $Emailaddress . $ShipToName . $ShipToAddress1 . $ShipToAddress2 . $ShipToCityState . $ShipToZipcode . $PurchasingContact . $PurchasingContactNum . $PurchasingContactFax . $PurchasingContactEmail . $Custom1 . $Custom2 . $Custom3 . $Custom4 . $Custom5 . $DefaultICPart);
        fclose($fp);
    }
}

if( !function_exists('get_welcome_email') ) {
    function get_welcome_email($name) {
        $output = '';

        $output .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';

        $output .= '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
        $output .= '<head>';
        $output .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        $output .= '<meta name="description" content="" />';
        $output .= '<meta name="keywords" content="" />';
        $output .= '<title>John Amico</title>';
        $output .= '</head>';

        $output .= '<body>';
        $output .= '<style type="text/css">' . "\n";
        $output .= 'body {margin:0;padding:0;background:url("' . base_url() . '/welcome/body_bg.gif") repeat-x 0 0;}' . "\n";
        $output .= 'img {border:0px;}' . "\n";
        $output .= '* {outline:none;}' . "\n";
        $output .= 'body, a:link, a:visited, a:hover, a:active, input, select, textarea {font-family:Verdana;font-size:11px;color:#000;}' . "\n";
        $output .= '.header, .content, .footer {width:600px;margin:0 auto;}' . "\n";
        $output .= '.header {position:relative;top:0;left:0;z-index:1;height:200px;padding:5px 0 0;text-align:center;}' . "\n";
        $output .= '.john {position:absolute;top:60px;left:470px;z-index:5;}' . "\n";
        $output .= '.content {padding:0 0 50px;text-align:justify;}' . "\n";
        $output .= '.footer_wrap {position:relative;top:0;left:0;z-index:1;background:url("' . base_url() . '/welcome/foot_bg.gif") repeat-x center 0;}' . "\n";
        $output .= '.footer {width:600px;height:28px;background:url("' . base_url() . '/welcome/foot_bg.gif") repeat-x center 0;padding:29px 0 0;text-align:center;}' . "\n";
        $output .= '.art {position:absolute;top:-115px;right:0;z-index:5;width:120px;height:145px;background:url("' . base_url() . '/welcome/art.gif") no-repeat 0 0;}' . "\n";
        $output .= '</style>';

        $output .= '<div class="header">';
        $output .= '<a href="' . base_url() . '/" title="John Amico"><img src="' . base_url() . '/welcome/logo.gif" alt="John Amico" /></a>';
        $output .= '<div class="john">';
        $output .= '<img src="' . base_url() . '/welcome/john.jpg" alt="John Amico" title="John Amico" /><br />John Amico, Jr.<br />President';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '<div class="content">';
        $output .= '<p>Dear <b>' . $name . '</b>,</p>';
        $output .= '<p>I would like to personally congratulate you and welcome you as a new member of John Amico Haircare Products - the oldest family-owned haircare company in America!  For over 40 years and two generations, John Amico has provided diversion-free professional products direct to salons.  The trust and integrity that John Amico places with each salon owner and the trust salon owners place with us has proven that John Amico products are never sold to non-professional stores such as drug, department or grocery stores.  This commitment ensures that your satisfied clients return to you to purchase their favorite John Amico haircare products resulting in a successful and profitable salon.</p>';
        $output .= '<p>At John Amico, your membership includes unique privileges. We have hundreds of professional service and retail products to satisfy your needs.   Regardless of your salon size, we make it a point to get to know you.  In fact, we have helped salon owners of all sizes from the home studio owner to the salon chain.  It\'s our job to understand your needs and goals and create a plan to help you achieve them. As a new member, we offer a FREE trip (including flight and hotel) to the John Amico National Training Center which needs to be redeemed within your first 90 days.  Training is very important to us and it\'s critical to your success.  By understanding the features and benefits of your new service and retail products, you will be able to satisfy your clients needs.  Your John Amico Discovery Kit, which you may have already opened, contains a brief product knowledge manual and several of our service and retail favorites.  Remember, the "Discovery Kit" is just a sampling of the hundreds of products we offer...we have most everything that your salon needs.</p>';
        $output .= '<p>In addition to supporting your business needs, we have also been able to fulfill many salon owners\' dreams.  Dreams of all sorts, including: new salons, homes, cars and more.  These dreams all have been accomplished by becoming an educator with John Amico. This is a great opportunity for you!  We provide the training to get you started.  Plus, the best part of being an educator is that you can be paid over and over again from teaching one class.  Being an educator is fun, motivating and rewarding.  Make your dreams come true by talking with your educational coordinator on how to get started.</p>';
        $output .= '<p>John Amico is very unique in the beauty industry.  We trust you will find your career with us a lifetime experience.  We will be contacting you in the next few days to learn more about you.  In the meantime, start using the products today.  Let your enthusiasm move you to explore the wonderful world of John Amico.  A whole new beginning is there for you, you only need to get started.</p>';
        $output .= '<p>Sincerely,<br /><br /></p>';
        $output .= '<p>';
        $output .= 'Johnny Amico<br />';
        $output .= 'John.amicojr@johnamico.com<br />';
        $output .= '1-800-JohnAmico ext. 333';
        $output .= '</p>';
        $output .= '</div>';
        $output .= '<div class="footer_wrap">';
        $output .= '<div class="art"></div>';
        $output .= '<div class="footer"><img src="' . base_url() . '/welcome/foot_text.gif" alt="1-800-JohnAmico. 4731 S. 136th St. Crestwood, IL 60445. www.johnamico.com" title="1-800-JohnAmico. 4731 S. 136th St. Crestwood, IL 60445. www.johnamico.com" /></div>';
        $output .= '</div>';
        $output .= '</body>';
        $output .= '</html>';

        return $output;
    }
}

if( !function_exists('email_send') ) {
    function email_send($msg, $email, $subject) {
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'Content-Transfer-Encoding: base64' . "\r\n";
        $headers .= "From: Johnny Amico <john.amicojr@johnamico.com>\r\n";

        $msg = rtrim(chunk_split(base64_encode($msg)));

        if (mail($email, $subject, $msg, $headers)) {
            return TRUE;
        }
        else {
            return FALSE;
        }
    }
}

if( !function_exists('mysqli_result') ) {
    function mysqli_result($res, $row = 0, $col = 0) {
        $numrows = mysqli_num_rows($res);
        if ($numrows && $row <= ($numrows - 1) && $row >= 0) {
            mysqli_data_seek($res, $row);
            $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
            if (isset($resrow[$col])) {
                return $resrow[$col];
            }
        }
        return false;
    }
}

if( !empty($admin_path) && ($admin_path == 'admin_renovated') ) {
    require_once("query_results_limit.php");
}

if( !function_exists('pagination_url') ) {
    function pagination_url($page_number=1, $page_url='') {
        global $designations, $sort, $alpabet, $start_date, $end_date, $sortorder, $sby, $amico_id_filter, $filters;

        //echo '<pre>'; var_dump($filters, $page_url); echo '</pre>';die();
        $filters = !empty($filters) ? $filters : array();
        $url_args = array();

        if(empty($page_url)) { global $page_url; }

        $filters['page'] = $page_number;
        if(empty($designations) && empty($filters['designations']) ) {
            $filters['designations'] = !empty($_REQUEST['designations']) ? ( !is_numeric($_REQUEST['designations']) ? NULL : $_REQUEST['designations']) : NULL;
        }
        if(empty($sort) && empty($filters['sort']) ) {
            $filters['sort'] = ( isset($_REQUEST['sort']) && is_numeric($_REQUEST['sort']) ?  $_REQUEST['sort'] : '' );
        }
        if(empty($alpabet) && empty($filters['alpabet']) ) {
            $filters['alpabet'] = ( !empty($_REQUEST['alpabet']) ? filter_var($_REQUEST['alpabet'], FILTER_SANITIZE_STRING) : '' ) ;
        }

        if( empty($filters['searchitemid']) ) $filters['searchitemid'] = !empty($_REQUEST['searchitemid']) ? filter_var($_REQUEST['searchitemid'], FILTER_SANITIZE_STRING) : '';
        if( empty($filters['lastorderdate']) ) $filters['lastorderdate'] = !empty($_REQUEST['lastorderdate']) ? filter_var($_REQUEST['lastorderdate'], FILTER_SANITIZE_STRING) : '';
        if( empty($filters['nametype']) ) $filters['nametype'] = (!empty($_REQUEST['nametype']) && !empty($name_types[ $_REQUEST['nametype'] ]) ) ? $_REQUEST['nametype'] : '';
        if( empty($filters['searchname']) ) $filters['searchname'] = !empty($_REQUEST['searchname']) ? filter_var($_REQUEST['searchname'], FILTER_SANITIZE_STRING) : '';
        if( empty($filters['searchcity']) ) $filters['searchcity'] = !empty($_REQUEST['searchcity']) ? filter_var($_REQUEST['searchcity'], FILTER_SANITIZE_STRING) : '';
        if( empty($filters['searchstate']) ) $filters['searchstate'] = !empty($_REQUEST['searchstate']) ? filter_var($_REQUEST['searchstate'], FILTER_SANITIZE_STRING) : '';
        if( empty($filters['mlm_id']) ) $filters['mlm_id'] = !empty($_REQUEST['mlm_id']) ? filter_var($_REQUEST['mlm_id'], FILTER_SANITIZE_STRING) : '';
        if( empty($filters['b']) ) $filters['b'] = !empty($_REQUEST['b']) ? filter_var($_REQUEST['b'], FILTER_SANITIZE_STRING) : '';

        if( empty($filters['sby']) ) $filters['sby'] = !empty($sby) ? filter_var($sby, FILTER_SANITIZE_STRING) : '';
        if( empty($filters['sorder']) ) $filters['sorder'] = !empty($sortorder) ? filter_var($sortorder, FILTER_SANITIZE_STRING) : '';
        if( empty($filters['start_date']) ) $filters['start_date'] = !empty($start_date) ? filter_var($start_date, FILTER_SANITIZE_STRING) : '';
        if( empty($filters['end_date']) ) $filters['end_date'] = !empty($end_date) ? filter_var($end_date, FILTER_SANITIZE_STRING) : '';
        if( empty($filters['amico_id_filter']) ) $filters['amico_id_filter'] = !empty($amico_id_filter) ? filter_var($amico_id_filter, FILTER_SANITIZE_STRING) : '';


        $url = $page_url . ( is_numeric($filters['sort']) ? '&sort=' . $filters['sort'] : '' ) . ( is_numeric($filters['designations']) ? '&designations=' . $filters['designations'] : '' );

        //echo '<pre>'; var_dump($filters); die();
        $filters = array_filter($filters);

        if( !empty($filters) ) {
            foreach($filters as $fk=>$fv) {
                if( !in_array($fk, array('sort', 'designations')) ) {
                    $url_args[] = "$fk=" . urlencode($fv);
                }
            }

            $url .= "&".implode('&', $url_args);
        }

        return $url;
    }
}