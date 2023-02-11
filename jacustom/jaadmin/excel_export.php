<?php
require_once("../common_files/include/global.inc");

function calculateSales($year = "", $amico_id = "") {
    global $conn;

	if(date("m") >= 11)
	{	
		if($year == "last")
		{
			$last_year = date('Y')-1;
			$second_year = date('Y');
		}
		else
		{
			$last_year = date('Y') ;
			$second_year = date('Y') + 1;
		}
	}
	else
	{
		if($year == "last")
		{
			$last_year = date('Y')-2;
			$second_year = date('Y')-1;
		}
		else
		{
			$last_year = date('Y') - 1;
			$second_year = date('Y');
		}

	}

//	$sql = "SELECT b.* FROM bw_invoices b, tbl_member t WHERE t.amico_id = b.ID AND b.InvoiceDate >='".$last_year."-01-01' AND b.InvoiceDate <= '".$last_year."-12-12' AND t.amico_id = '".$amico_id."'";
	$sql = "SELECT b.* FROM bw_invoices b, tbl_member t WHERE t.amico_id = b.ID AND b.InvoiceDate >='".$last_year."-11-01' AND b.InvoiceDate <= '".$second_year."-10-31' AND t.amico_id = '".$amico_id."'";
	$res = mysqli_query($conn,$sql) or die(mysql_error());

	$total = 0;
	while($row = mysqli_fetch_assoc($res))
	{
		$sql2 = "SELECT Description, ID, (UnitPrice * ShipQty) as total, UnitPrice, ShipQty FROM bw_invoice_line_items WHERE FKEntity = '".$row['SKOEInvoice']."'";
		$res2 = mysqli_query($conn,$sql2) or die(mysql_error());
		$second_total = 0;
		while($row2 = mysqli_fetch_assoc($res2))
		{
			$second_total+=$row2['total'];
			$total+=$row2['total'];
		}
	}
	
	return round($total,2);
}

function calculateGoal($amount = "1000", $num = 1, $amico_id)
{
	
	$row = "";
	$goal = (float)calculateSales("last", $amico_id);
    $goal += (float)$amount;

	$remaining = $goal - calculateSales("", $amico_id); 
	
	if($remaining < 0)
	{
		$remaining = "won";
	}
	else
	{
		$remaining = "$".number_format($remaining, 2);
	}

	$row.= 	$remaining."\t";

	return $row;
}


function ntocw($number, $length)
{
 $number = preg_replace("/[$,A-Za-z ]/", "", $number);
 
 list($dollars, $cents) = explode(".", $number);
 
 if(!empty($cents))
 {
  if(strlen($cents) < 2)
  {
   $cents = $cents . "0";
  }
  else if(strlen($cents) > 2)
  {
   $divby = pow(10, (strlen($cents) - 2));
   $cents = round($cents / $divby, 0);
  }
 }
 else
 {
  $cents = "00";
 }
 $cents = $cents . "/100";
 
 if(!empty($dollars))
 {
  $num_to_words[0] = "Zero";
  $num_to_words[1] = "One";
  $num_to_words[2] = "Two";
  $num_to_words[3] = "Three";
  $num_to_words[4] = "Four";
  $num_to_words[5] = "Five";
  $num_to_words[6] = "Six";
  $num_to_words[7] = "Seven";
  $num_to_words[8] = "Eight";
  $num_to_words[9] = "Nine";
  $num_to_words[10] = "Ten";
  $num_to_words[11] = "Eleven";
  $num_to_words[12] = "Twelve";
  $num_to_words[13] = "Thirteen";
  $num_to_words[14] = "Fourteen";
  $num_to_words[15] = "Fifteen";
  $num_to_words[16] = "Sixteen";
  $num_to_words[17] = "Seventeen";
  $num_to_words[18] = "Eighteen";
  $num_to_words[19] = "Nineteen";
  $num_to_words[20] = "Twenty";
  $num_to_words[30] = "Thirty";
  $num_to_words[40] = "Forty";
  $num_to_words[50] = "Fifty";
  $num_to_words[60] = "Sixty";
  $num_to_words[70] = "Seventy";
  $num_to_words[80] = "Eighty";
  $num_to_words[90] = "Ninety";
 
  $subsets[1] = "";
  $subsets[2] = "Thousand";
  $subsets[3] = "Million";
  $subsets[4] = "Billion";
  $subsets[5] = "Trillion";
 
  $pad_cnt = 3 - (strlen($dollars) % 3);
  
  for($cnt=0;$cnt<$pad_cnt && $pad_cnt<3;$cnt++)
  {
   $dollars = "0" . $dollars;
  }
  
  $subset_cnt = strlen($dollars) / 3;
  
  $array_index = 0;
  
  $word = "";
  
  for($cnt=$subset_cnt;$cnt>0;$cnt--)
  {
   $subset = substr($dollars, $array_index, 3);

   if($subset[0] > 0)
   {
    $word .= $num_to_words[$subset[0]] . " Hundred and ";
   }

   if($subset[1] > 1)
   {
    $word .= $num_to_words[$subset[1]."0"] . " ";
    if($subset[2] > 0)
    {
     $word .= $num_to_words[$subset[2]] . " ";
    }
   }
   else if($subset[1] == 1)
   {
    $word .= $num_to_words[$subset[1].$subset[2]] . " ";
   }
   else
   {
    if($subset[2] > 0)
    {
     $word .= $num_to_words[$subset[2]] . " ";
    }
   }
   
   $word .= $subsets[$cnt] . " ";
   
   $array_index += 3;
  }
  
  $dollars = rtrim($word);
 }
 else
 {
  $dollars = "Zero";
 }

 $padding = " - ";
 
 if(strlen($dollars)+strlen($padding)+strlen($cents) < $length)
 {
  $pad_cnt = $length - (strlen($dollars)+strlen($padding)+strlen($cents));
  
  $padding = " ";
  
  for($cnt=0;$cnt<=$pad_cnt;$cnt++)
  {
   $padding .= "-";
  }
  
  $padding .= " ";
 }
 
 return $dollars . $padding . $cents;
}

function get_member_title($default_title, $active_members)
{
    $output = $default_title;

    if($active_members >= 12) $output = "JA Master Recruiting Director";
    else if($active_members >= 9) $output = "JA Senior Recruiting Director";
    else if($active_members >= 6) $output = "JA Recruitng Director";
    else if($active_members >= 3) $output = "JA Developing Recruiter";
    else if($active_members >= 1) $output = "JA Mentor";
    else if($active_members == 0) $output = "Member";

    return $output;
}
/**/
Header("Content-type: application/vnd.ms-excel; name='excel'");
Header("Content-Disposition: attachment; filename=STW-exported.xls");
Header("Content-Description: Excel output");
/**/
echo "amico_id \tint_sales \tint_commission \tcheck_string \tfirst_name \tlast_name \taddress1 \taddress2 \tcity \tstate \tzip \tpassword \tactive_members \tcurrent_title \thighest_title \tdate_achieved \tfirst_time \tactive commission total \tactive commission check_string \tphone \tec\t last_year \tcurrent_score ";
//echo "\t 1_goal_R \t2_goal_R \t3_goal_R \t4_goal_R \t5_goal_R ";
echo "\tltd \t \n";

$sql = "SELECT tm.int_member_id, tm.int_parent_id FROM stw_data stw INNER JOIN tbl_member tm ON stw.member_id = tm.amico_id WHERE stw.report_id = '$id' GROUP BY stw.member_id ORDER BY stw.member_id";
$result = mysqli_query($conn,$sql);

$member_parents = array();
while($row = mysqli_fetch_array($result))
{
	$member_parents[$row['int_member_id']] = $row['int_parent_id'];
}

$sql = "
SELECT sd.member_id, tm.amico_id, tm.ec_id, tm.int_member_id, SUM( sd.amount )  AS amount, SUM( sd.commissioned )  AS commissioned, c.customers_firstname, c.customers_lastname, c.customers_password, c.customers_telephone, ab.entry_street_address, ab.*, z.zone_name, td.str_designation, td.int_designation_id
FROM stw_data sd
INNER JOIN tbl_member tm ON sd.member_id=tm.amico_id
INNER JOIN tbl_designation td ON tm.int_designation_id = td.int_designation_id
LEFT JOIN customers c ON tm.int_customer_id=c.customers_id
LEFT JOIN address_book ab ON tm.int_customer_id=ab.customers_id AND ab.address_book_id='3'
LEFT JOIN zones z ON ab.entry_zone_id=z.zone_id
LEFT JOIN address_book ab2 ON tm.int_customer_id=ab2.customers_id AND ab2.address_book_id='2'
WHERE sd.report_id = '$id'
AND sd.type IN ('MP', 'RMP')
GROUP BY sd.member_id
ORDER BY sd.member_id";
//$sql .= " LIMIT 10";
$res = mysqli_query($conn,$sql);
echo mysqli_error($conn);


while($row = mysqli_fetch_array($res))
{
	//if( $row['member_id'] != 'W01005345' ) { continue; }

    $active_members = 0;

	foreach($member_parents AS $m_id => $p_id)
	{
		if($row['int_member_id'] == $p_id)
		{
			$active_members++;
		}
	}

	$sql = "SELECT SUM(sd.amount), SUM(sd.commissioned) 
        FROM stw_data sd 
        INNER JOIN tbl_member tm ON sd.src_member_id = tm.amico_id 
        -- INNER JOIN orders o ON sd.invoice_id = o.orders_id 
        INNER JOIN customers c ON tm.int_customer_id = c.customers_id 
        WHERE sd.report_id = '$id' AND sd.member_id = '{$row['member_id']}' AND sd.type = 'RMP'";
	$res2 = mysqli_query($conn,$sql);
	echo mysqli_error($conn);
	list($rmp_amount, $rmp_commissioned) = mysqli_fetch_row($res2);

	//echo '<pre>'; print_r( array($sql, $rmp_amount, $rmp_commissioned) ); die();

	$sql = "SELECT SUM(sd.amount), SUM(sd.commissioned) 
        FROM stw_data sd 
        INNER JOIN tbl_member tm ON sd.member_id=tm.amico_id 
        LEFT JOIN customers c ON tm.int_customer_id=c.customers_id 
        LEFT JOIN address_book ab ON tm.int_customer_id=ab.customers_id AND ab.address_book_id='3' 
        LEFT JOIN zones z ON ab.entry_zone_id=z.zone_id 
        WHERE sd.report_id = '$id' AND sd.member_id = '{$row['member_id']}' AND sd.level <= '$active_members' AND sd.type = 'MP' 
        GROUP BY sd.member_id ORDER BY sd.member_id";
	$res2 = mysqli_query($conn,$sql);
	echo mysqli_error($conn);
	list($fla_amount, $fla_commissioned) = mysqli_fetch_row($res2);

	//$reg_amount = number_format(round($row['amount'] + $rmp_amount, 2), 2);
	//$reg_commissioned = number_format(round($row['commissioned'] + $rmp_commissioned, 2), 2);
    $reg_amount = number_format(round($row['amount'], 2), 2);
    $reg_commissioned = number_format(round($row['commissioned'], 2), 2);
	$fla_amount = number_format(round($fla_amount + $rmp_amount, 2), 2);
	$fla_commissioned = number_format(round($fla_commissioned + $rmp_commissioned, 2), 2);

	if($row['zone_name'] == NULL)
	{
		$state = $row['entry_state'];
	}
	else
	{
		$state = $row['zone_name'];
	}

    $sql = "SELECT td.str_designation, td.int_designation_id, DATE_FORMAT(sr.report_time, '%c/%e/%Y %l:%i%p') AS hd_date_achieved
    FROM past_designations pd
    INNER JOIN tbl_designation td ON pd.int_designation_id=td.int_designation_id
    INNER JOIN stw_reports sr ON pd.report_id=sr.report_id
    WHERE pd.member_id = '{$row['member_id']}'
    AND sr.report_id < '$id'
    ORDER BY pd.int_designation_id DESC LIMIT 1";
	$result1 = mysqli_query($conn,$sql);
	echo mysqli_error($conn);
	list($highest_designation, $highest_designation_id, $hd_date_achieved) = mysqli_fetch_row($result1);

	$sql = "SELECT int_designation_id FROM past_designations WHERE member_id = '{$row['member_id']}' AND int_designation_id = '{$row['int_designation_id']}'";
	$result1 = mysqli_query($conn,$sql);
	echo mysqli_error($conn);
	if(mysqli_num_rows($result1) > 0 && $row['int_designation_id'] > $highest_designation_id)
	{
		$first_time_designation = $row['str_designation'];
	}
	else
	{
		$first_time_designation = "";
	}


	echo $row['member_id']."\t".$reg_amount."\t".$reg_commissioned."\t".ntocw($reg_commissioned, 64)."\t".$row['entry_firstname']."\t".$row['entry_lastname']."\t".$row['entry_street_address']."\t".$row['entry_street_address2']."\t".$row['entry_city']."\t".$state."\t".$row['entry_postcode']."\t".$row['customers_password'];
    echo "\t".$active_members;
    echo "\t".get_member_title($row['str_designation'], $active_members); //current_title
    echo "\t".$highest_designation; //highest_title
    echo "\t".$hd_date_achieved; //date_achieved
    echo "\t".$first_time_designation; //first_time
    echo "\t".$fla_commissioned."\t".ntocw($fla_commissioned, 64)."\t";
	echo $row['customers_telephone']."\t";

	$sql7 = "SELECT c.customers_firstname, c.customers_lastname FROM customers c, tbl_member t WHERE t.int_customer_id=c.customers_id AND t.amico_id = '".$row['ec_id']."'";
	$res7 = mysqli_query($conn,$sql7)  or die(mysqli_error($conn));
	$row7 = mysqli_fetch_assoc($res7);
	$ec = $row7['customers_firstname']." ".$row7['customers_lastname'];
	echo $ec."\t";
	echo "$".number_format(calculateSales("last", $row['amico_id']),2)." \t";
	echo "$".number_format(calculateSales("", $row['amico_id']),2)." \t";

	$amount=0;

	for($i=0;$i<=4;$i++)
	{
		$amount = $amount + 1000;
		//echo calculateGoal($amount, $num, $row['amico_id']);
		//echo "\t";

	}

	$ltd = 0;

	$sql = "SELECT SUM(commissioned) AS commissioned FROM stw_data WHERE member_id = '{$row['member_id']}' AND report_id <= '114' AND report_id <= '$id' AND type IN ('MP','RMP') GROUP BY member_id";
	$res2 = mysqli_query($conn,$sql);
	echo mysqli_error($conn);
	
	if(mysqli_num_rows($res2) > 0)
	{
		$ltd += mysqli_result($res2, 0);
	}

	$sql = "SELECT SUM(commissioned) AS commissioned FROM stw_data WHERE member_id = '{$row['member_id']}' AND report_id > '114' AND report_id <= '$id' AND type = 'RMP' GROUP BY member_id";
	$res2 = mysqli_query($conn,$sql);
	echo mysqli_error($conn);
	
	if(mysqli_num_rows($res2) > 0)
	{
		$ltd += mysqli_result($res2, 0);
	}

	$sql = "SELECT report_id FROM stw_reports WHERE report_id > '114' AND report_id <= '$id' ORDER BY report_id";
	$res2 = mysqli_query($conn,$sql);
	echo mysqli_error($conn);

	while($row2 = mysqli_fetch_assoc($res2))
	{
		$sql = "SELECT COUNT(tm.int_member_id) FROM stw_data sd INNER JOIN tbl_member tm ON sd.member_id = tm.amico_id WHERE tm.int_parent_id = '{$row['int_member_id']}' AND sd.report_id = '{$row2['report_id']}' GROUP BY sd.member_id";
		$res3 = mysqli_query($conn,$sql);
		echo mysqli_error($conn);

		$active_members = mysqli_num_rows($res3);

		$sql = "SELECT SUM(commissioned) AS commissioned FROM stw_data WHERE member_id = '{$row['member_id']}' AND report_id = '{$row2['report_id']}' AND level <= '$active_members' AND type = 'MP' GROUP BY member_id";
		$res3 = mysqli_query($conn,$sql);
		echo mysqli_error($conn);

		if(mysqli_num_rows($res3) > 0)
		{
			$ltd += mysqli_result($res3, 0);
		}
	}

	echo $ltd . "\t\n";
}

exit;

$sql = "SELECT tm.int_member_id, tm.int_parent_id FROM stw_data stw INNER JOIN tbl_member tm ON stw.member_id = tm.amico_id WHERE stw.report_id = '$id' GROUP BY stw.member_id ORDER BY stw.member_id";
$result = mysqli_query($conn,$sql);

$member_parents = array();
while($row = mysqli_fetch_array($result))
{
	$member_parents[$row['int_member_id']] = $row['int_parent_id'];
}

$sql = "SELECT s.member_id, SUM( s.amount )  AS amount,
	SUM( s.commissioned )  AS commissioned, 
	c.customers_firstname, 
	c.customers_lastname,
	c.customers_password,
	a.entry_street_address,
	a2.entry_street_address,
	a.entry_city,
	z.zone_name,
	a.entry_postcode,
	t.int_member_id
FROM stw_data s 
	inner join tbl_member t on t.amico_id = s.member_id
	inner join customers c on  t.int_customer_id = c.customers_id 
	inner join address_book a on  a.customers_id = c.customers_id AND  a.address_book_id = '1' 
	left  join address_book a2 on a2.customers_id = c.customers_id AND a2.address_book_id = '2' 
	inner join zones z on a.entry_zone_id=z.zone_id 
WHERE s.report_id =  '".$id."' 
GROUP  BY s.member_id 
ORDER  BY s.member_id 
";



$result = mysqli_query($conn,$sql);
echo mysqli_error($conn);

while($row = mysqli_fetch_array($result))
{
	$active_members = 0;
	
	foreach($member_parents AS $m_id => $p_id)
	{
		if($row['int_member_id'] == $p_id)
		{
			$active_members++;
		}
	}

	
  $commission_total = 0;

  $sql2 = "SELECT stw.*, tm.int_designation_id, td.str_designation, c.* FROM stw_data stw INNER JOIN tbl_member tm ON stw.src_member_id = tm.amico_id INNER JOIN tbl_designation td ON tm.int_designation_id = td.int_designation_id INNER JOIN customers c ON tm.int_customer_id = c.customers_id WHERE stw.report_id = '$id' AND stw.member_id = '{$row['member_id']}' AND stw.type = 'MP' and level<='$active_members'  ORDER BY stw.level, c.customers_lastname";
  $result2 = mysqli_query($conn,$sql2);
  while($row2 = mysqli_fetch_array($result2))
  {
   $commission_total += $row2['commissioned'];
  }
	
	echo $row['member_id']."\t".$row['amount']."\t".$row['commissioned']."\t".ntocw($row['commissioned'], 64)."\t".$row['customers_firstname']."\t".$row['customers_lastname']."\t".$row['entry_street_address']."\t".$row['entry_street_address2']."\t".$row['entry_city']."\t".$row['zone_name']."\t".$row['entry_postcode']."\t".$row['customers_password']."\t".$active_members."\t".$commission_total."\t".ntocw($commission_total, 64)."\t";
	echo $row['customers_telephone']."\t";

	$sql7 = "SELECT c.customers_firstname, c.customers_lastname FROM customers c, tbl_member t WHERE t.int_customer_id=c.customers_id AND t.amico_id = '".$row['ec_id']."'";
	$res7 = mysqli_query($conn,$sql7)  or die(mysql_error());
	$row7 = mysqli_fetch_assoc($res7);
	$ec = $row7['customers_firstname']." ".$row7['customers_lastname'];
	echo $ec."\t";
	echo "$".number_format(calculateSales("last", $amico_id),2)." \t";
	echo "$".number_format(calculateSales("", $amico_id),2)." \t";

	for($i=0;$i<=4;$i++)
	{
		$amount = $amount + 1000;
		echo calculateGoal($amount, $num);

	}
	for($i=0;$i<=7;$i++)
	{
		$amount = $amount + 2500;
		echo calculateGoal($amount, $num);
	}


	echo "\n";
}

