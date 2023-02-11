<?php
ini_set('max_execution_time', 0);
//ini_set('display_errors', 0);
//error_reporting(E_ALL ^ E_NOTICE);

/* $Id$
 *
 * This script will allow the admin
 * to upload their CSV file to the server.
 * it will then parse this file, initiating
 * any of the commission rules that match
 * the specific category.
 *
 */

require_once("session_check.inc");
require_once("../common_files/include/global.inc");
define('CSV_DIR', base_admin_path()."/csv/");

$useMagento = true;

function get_noPurchaseRequiredMember_children($memberAmico, $info, $arr=array(), $depth=0, $depthPath='')
{
    if( !empty($memberAmico) and !empty($info) )
    {
        global $conn;

        $depth++;
        $depthPath .= $memberAmico . '#';

        $noPurchaseRequired_members_child_sql = " SELECT tm1.amico_id FROM tbl_member tm1 INNER JOIN tbl_member tm2 ON tm1.int_parent_id = tm2.int_member_id WHERE tm2.amico_id = '{$memberAmico}'";
        $noPurchaseRequired_members_child_query = mysqli_query($conn,$noPurchaseRequired_members_child_sql) or die(mysqli_error($conn));

        while( $amico_id = mysqli_fetch_row($noPurchaseRequired_members_child_query) )
        {
            if($amico_id[0] == $memberAmico) { continue; }
            //echo '<pre>'; var_dump($amico_id[0]); die();

            //echo $amico_id[0] . "<br/>";

            if( !empty($info[ $amico_id[0] ]) ) {
                $arr['children'][$amico_id[0]] = $info[ $amico_id[0] ];

                if(!empty($arr['children'][$amico_id[0]]['MP']))
                {
                    foreach($arr['children'][$amico_id[0]]['MP'] as $invoice => $invoiceCommissionLine )
                    {
                        list($memberpath, $src_member, $level, $commissionable, $amount, $date) = explode("|", $invoiceCommissionLine);

                        $memberpath = $depthPath.$memberpath;
                        $level += $depth;
                        $arr['MP'][$invoice] = $memberpath."|".$src_member."|".$level."|".$commissionable."|".$amount."|".$date;
                    }
                }
                if(!empty($arr['children'][$amico_id[0]]['RMP']))
                {
                    foreach($arr['children'][$amico_id[0]]['RMP'] as $invoice => $invoiceCommissionLine )
                    {
                        list($memberpath, $src_member, $level, $commissionable, $amount, $date) = explode("|", $invoiceCommissionLine);

                        $memberpath = $depthPath.$memberpath;
                        $level += $depth;
                        $arr['RMP'][$invoice] = $memberpath."|".$src_member."|".$level."|".$commissionable."|".$amount."|".$date;
                    }
                }
            }
            else {
                //$depth++;
                //$depthPath .= "{$amico_id[0]}#";
                $arr = get_noPurchaseRequiredMember_children($amico_id[0], $info, $arr, $depth, $depthPath);
            }
        }
    }
    //echo '<pre>'; var_dump($arr); echo '</pre>';
    return $arr;
}

function get_children_info($root, $parent, $info, $depth, $period, $path = "") {
    global $conn, $useMagento;

	$depth++;

	if($depth > 6)
	{
		return $info;
	}

	if(empty($path))
	{
		$path .= $parent;
    }
	else
	{
		$path .= "#".$parent;
	}

	$sql = "SELECT tm1.amico_id FROM tbl_member tm1 INNER JOIN tbl_member tm2 ON tm1.int_parent_id = tm2.int_member_id WHERE tm2.amico_id = '$parent'";
	$result = mysqli_query($conn,$sql);
	echo mysqli_error($conn);

	while($amico_id = mysqli_fetch_row($result))
	{
		if(isset($info[$amico_id[0]]))
		{
			foreach($info[$amico_id[0]]['MP'] as $i_id => $data)
			{
				list($memberpath, $src_member, $level, $commissionable, $amount, $date) = explode("|", $data);
				$memberpath = $path."#".$memberpath;
				
				if($src_member == $amico_id[0])
				{
					$info[$root]['MP'][$i_id] = $memberpath."|".$src_member."|".$depth."|".$commissionable."|".$amount."|".$date;
    			}
			}
		}

		list($p_month, $p_year) = explode("|", $period);

		$p_ts = mktime(0, 0, 0, $p_month, 1, $p_year);

        $sql = "SELECT orders_id, date_purchased FROM orders WHERE refering_member = '$amico_id[0]' AND date_purchased >= '".date("Y", $p_ts)."-".date("m", $p_ts)."-01 00:00:00' AND date_purchased <= '".date("Y", $p_ts)."-".date("m", $p_ts)."-".date("t", $p_ts)." 23:59:59'";

        if( $useMagento ) {
            // Querying From Magento
            $sql = "SELECT IF(oa.ja_oldsite_order_id = '' OR 0, o.increment_id, oa.ja_oldsite_order_id) AS orders_id, DATE_FORMAT(o.created_at, '%Y-%m-%d') as date_purchased, oa.ja_oldsite_order_id, o.entity_id, o.subtotal,
                    o.grand_total, (o.grand_total-(o.shipping_amount+o.tax_amount)) AS commission_calculatable_total, 
                    (SELECT SUM(oi.row_total_incl_tax - oi.discount_amount) FROM ".MAGENTO_TABLE_PREFIX."sales_flat_order_item AS oi WHERE o.entity_id=oi.order_id) AS original_total
                    FROM " . MAGENTO_TABLE_PREFIX . "sales_flat_order AS o
                    INNER JOIN " . MAGENTO_TABLE_PREFIX . "amasty_amorderattr_order_attribute AS oa ON oa.order_id = o.entity_id
                    WHERE
                        o.created_at >= '".date("Y", $p_ts)."-".date("m", $p_ts)."-01 00:00:00'
                        AND o.created_at <= '".date("Y", $p_ts)."-".date("m", $p_ts)."-".date("t", $p_ts)." 23:59:59'
                        AND oa.jareferrer_self=0
                        AND oa.jareferrer_amicoid='{$amico_id[0]}'
            ";
            //$sql .= " AND oa.jareferrer_amico_member_id = 0 ";
        }

		$ord_res = mysqli_query($conn,$sql);
		echo mysqli_error($conn);

		while($orders_id = mysqli_fetch_array($ord_res))
		{
			$sql = "SELECT op.products_model, (op.final_price * op.products_quantity) as final_price, tcr.* FROM orders_products op LEFT JOIN tbl_commision_rule tcr ON op.products_model = tcr.str_commision_rule WHERE op.orders_id = '$orders_id[0]'";

            if( $useMagento ) {
                $sql = "SELECT IFNULL(oi.sku, '') as sku, 
                        ({$orders_id['commission_calculatable_total']} * ((oi.row_total_incl_tax-oi.discount_amount)/{$orders_id['original_total']})) as final_price, tcr.*
                        FROM " . MAGENTO_TABLE_PREFIX . "sales_flat_order_item AS oi
                        LEFT JOIN tbl_commision_rule AS tcr ON IFNULL(oi.sku, '') = tcr.str_commision_rule
                        WHERE
                            oi.order_id = '{$orders_id['entity_id']}'
                ";
            }

			$prod_res = mysqli_query($conn,$sql);
			echo mysqli_error($conn);

			$commissionable = 0;
			$amount = 0;

			while($prdinfo = mysqli_fetch_array($prod_res))
			{
				if(!empty($prdinfo['int_commision_rule_id']))
				{
					if($prdinfo['int_value'] <= 0)
					{
						continue;
					}
					else if($prdinfo['bit_percentage'] == 1)
					{
						$commissionable += round(($prdinfo['int_value']/100)*$prdinfo['final_price'], 2);
					}
					else
					{
						$commissionable += $prdinfo['int_value'];
					}
				}
				else
				{
					$commissionable += $prdinfo['final_price'];
				}

				$amount += $prdinfo['final_price'];
			}

            if(!empty($orders_id['subtotal'])) {
                $amount = ($amount > $orders_id['subtotal']) ? $orders_id['subtotal'] : $amount;
                $commissionable = ($commissionable > $orders_id['subtotal']) ? $orders_id['subtotal'] : $commissionable;
            }

			$info[$root]['RMP'][$orders_id[0]] = $path."#".$amico_id[0]."|".$amico_id[0]."|".$depth."|".round($commissionable, 2)."|".$amount."|".$orders_id[1];
		}


		$info = get_children_info($root, $amico_id['0'], $info, $depth, $period, $path);
	}

	return $info;
}

function csv_explode($void)
{
 $holder = "";
 $final = array();
 
 for($count = 0; $count < strlen($void); $count++)
 {
  if($void[$count] != "," && $count < strlen($void))
  {
   if($void[$count] == "\"")
   {
    $count++;
    while($void[$count] != "\"")
	{
	 $holder = $holder . $void[$count];
	 $count++;
	}
   }
   else
   {
    $holder = $holder . $void[$count];
   }
  }
  else
  {
   $final[] = $holder;
   $holder = "";
  }
 }
 $final[] = $holder;
 $holder = "";
 
 return $final;
}


function get_member_designation_id($active_members)
{
    $output = 1;

    if($active_members >= 12) $output = 5;
    else if($active_members >= 9) $output = 4;
    else if($active_members >= 6) $output = 3;
    else if($active_members >= 3) $output = 2;
    else if($active_members >= 1) $output = 1;

    return $output;
}


function parse_csv_file($file) {
    global $conn, $useMagento;

	if(file_exists(CSV_DIR.$file) && $csv = fopen(CSV_DIR.$file, "r"))
	{
		$sql = "INSERT INTO stw_reports (report_time) VALUES (Now())";
		mysqli_query($conn,$sql);
		echo mysqli_error($conn);

		$r_id = mysqli_insert_id($conn);

        if( empty($r_id) ) {
            echo "Couldn't create STW report to database.<br/>";
            die();
        }

		fgets($csv, 1024);

		$period = "";

        while(!feof($csv))
		{
		
			list($m_id, $i_id, $i_date, $item, $price) = explode(',',fgets($csv, 1024));

			if(empty($period))
			{
				list($month, $day, $year) = explode("/", $i_date);
				
				$period = "$month|$year";
			}

			if($price <= 0)
			{
				continue;
			}

			$commissionable = $price;

			$sql = "SELECT * FROM tbl_commision_rule WHERE str_commision_rule = '".addslashes($item)."' AND bit_active = 1";
			$result = mysqli_query($conn,$sql);
			echo mysqli_error($conn);

			if(mysqli_num_rows($result) > 0)
			{
				$rule = mysqli_fetch_array($result);

				if($rule['int_value'] <= 0)
				{
					continue;
				}
				else if($rule['bit_percentage'] == 1)
				{
					$commissionable = round(($rule['int_value']/100)*$price, 2);
				}
				else
				{
					$commissionable = $rule['int_value'];
				}
			}

			if(isset($info[$m_id]['MP'][$i_id]))
			{
				list($member_path, $src_member, $level, $commissionable_tot, $amount_tot, $date) = explode("|", $info[$m_id]['MP'][$i_id]);
				
				$commissionable_tot += $commissionable;
				$amount_tot += $price;

				$info[$m_id]['MP'][$i_id] = $m_id."|".$m_id."|0|".rtrim($commissionable_tot)."|".rtrim($amount_tot)."|".rtrim($date);
			}
			else
			{
				$info[$m_id]['MP'][$i_id] = $m_id."|".$m_id."|0|".rtrim($commissionable)."|".rtrim($price)."|".rtrim($i_date);
			}

			ksort($info);

			list($p_month, $p_year) = explode("|", $period);

			$p_ts = mktime(0, 0, 0, $p_month, 1, $p_year);
            $invoiceDates[$p_ts] = $p_ts;

			$sql = "SELECT orders_id, date_purchased FROM orders WHERE refering_member = '$m_id' AND date_purchased >= '".date("Y", $p_ts)."-".date("m", $p_ts)."-01 00:00:00' AND date_purchased <= '".date("Y", $p_ts)."-".date("m", $p_ts)."-".date("t", $p_ts)." 23:59:59'";

            if( $useMagento ) {
                // Querying From Magento
                $sql = "SELECT IF(oa.ja_oldsite_order_id = '' OR 0, o.increment_id, oa.ja_oldsite_order_id) AS orders_id, DATE_FORMAT(o.created_at, '%Y-%m-%d') as date_purchased, oa.ja_oldsite_order_id, o.entity_id, o.subtotal, 
                    o.grand_total, (o.grand_total-(o.shipping_amount+o.tax_amount)) AS commission_calculatable_total, 
                    (SELECT SUM(oi.row_total_incl_tax - oi.discount_amount) FROM ".MAGENTO_TABLE_PREFIX."sales_flat_order_item AS oi WHERE o.entity_id=oi.order_id) AS original_total
                    FROM ".MAGENTO_TABLE_PREFIX."sales_flat_order AS o
                    INNER JOIN ".MAGENTO_TABLE_PREFIX."amasty_amorderattr_order_attribute AS oa ON oa.order_id = o.entity_id
                    WHERE
                        o.created_at >= '".date("Y", $p_ts)."-".date("m", $p_ts)."-01 00:00:00'
                        AND o.created_at <= '".date("Y", $p_ts)."-".date("m", $p_ts)."-".date("t", $p_ts)." 23:59:59'
                        AND oa.jareferrer_self=0
                        AND oa.jareferrer_amicoid='$m_id'
                 ";
                //$sql .= " AND oa.jareferrer_amico_member_id = 0 ";
            }

            $ord_res = mysqli_query($conn,$sql);
			echo mysqli_error($conn);

			//echo $sql; die();

            if( mysqli_num_rows($ord_res) > 0 ) {
                //echo $sql; echo PHP_EOL.PHP_EOL;//die();
                //$rmps[$m_id]['RMP']['orders_sql'] = $sql;
            }

			while($orders_id = mysqli_fetch_array($ord_res))
			{
				$sql = "SELECT op.products_model, (op.final_price * op.products_quantity) as final_price, tcr.* FROM orders_products op LEFT JOIN tbl_commision_rule tcr ON op.products_model = tcr.str_commision_rule WHERE op.orders_id = '$orders_id[0]'";

                if( $useMagento ) {
                    $sql = "SELECT IFNULL(oi.sku, '') AS sku, 
                            ({$orders_id['commission_calculatable_total']} * ((oi.row_total_incl_tax-oi.discount_amount)/{$orders_id['original_total']})) as final_price, tcr.*
                            FROM ".MAGENTO_TABLE_PREFIX."sales_flat_order_item AS oi
                            LEFT JOIN tbl_commision_rule AS tcr ON IFNULL(oi.sku, '') = tcr.str_commision_rule
                            WHERE
                                oi.order_id = '{$orders_id['entity_id']}'
                    ";
                }

				$prod_res = mysqli_query($conn,$sql);
				echo mysqli_error($conn);

                //$rmps[$m_id]['RMP']['orders_product_sql'] = $sql;

                $commissionable = 0;
				$amount = 0;

				while($prdinfo = mysqli_fetch_array($prod_res))
				{
					if(!empty($prdinfo['int_commision_rule_id']))
					{
						if($prdinfo['int_value'] <= 0)
						{
							continue;
						}
						else if($prdinfo['bit_percentage'] == 1)
						{
							$commissionable += round(($prdinfo['int_value']/100)*$prdinfo['final_price'], 2);
						}
						else
						{
							$commissionable += $prdinfo['int_value'];
						}
					}
					else
					{
						$commissionable += $prdinfo['final_price'];
					}

					$amount += $prdinfo['final_price'];
				}

                if(!empty($orders_id['subtotal'])) {
                    $amount = ($amount > $orders_id['subtotal']) ? $orders_id['subtotal'] : $amount;
                    $commissionable = ($commissionable > $orders_id['subtotal']) ? $orders_id['subtotal'] : $commissionable;
                }

				$info[$m_id]['RMP'][$orders_id[0]] = $m_id."|".$m_id."|0|".round($commissionable, 2)."|".$amount."|".$orders_id[1];

                /*if( !empty( $info[$m_id]['RMP'][$orders_id[0]] ) ) {
                    $rmps[$m_id]['RMP']['orders'][$orders_id[0]] = $info[$m_id]['RMP'][$orders_id[0]];
                    $rmps[$m_id]['RMP']['orders_count_main']= count( $rmps[$m_id]['RMP']['orders'] );
                }*/
			}


			$memberIdsCollectedInTheInfoArr[$m_id] = $m_id;

		}
        //die();
	
		fclose($csv);


		//Now collect all other Consumer Purchases form Database
        //which was referred by Some Amico Member Other than W00888888

        //$memberIdsCollectedInTheInfoArr= array();

        if( !empty($memberIdsCollectedInTheInfoArr) && !empty($invoiceDates) ) {

            $invoiceDates = array_unique($invoiceDates);
            $memberIdsCollectedInTheInfoArr = array_unique($memberIdsCollectedInTheInfoArr);
            $memberIdsCollectedInTheInfoArr[] = 'W00888888';

            foreach ($invoiceDates as $p_ts) {


                $sql = "SELECT orders_id, date_purchased, refering_member AS m_id, entity_id, oa.jareferrer_self AS member_self_order FROM orders WHERE refering_member NOT IN ('".implode("','",$memberIdsCollectedInTheInfoArr)."') AND date_purchased >= '" . date("Y", $p_ts) . "-" . date("m", $p_ts) . "-01 00:00:00' AND date_purchased <= '" . date("Y", $p_ts) . "-" . date("m", $p_ts) . "-" . date("t", $p_ts) . " 23:59:59'";

                if ($useMagento) {
                    // Querying From Magento
                    $sql = "SELECT IF(oa.ja_oldsite_order_id = '' OR 0, o.increment_id, oa.ja_oldsite_order_id) AS orders_id, DATE_FORMAT(o.created_at, '%Y-%m-%d') as date_purchased, oa.ja_oldsite_order_id, o.entity_id, oa.jareferrer_amicoid AS m_id, o.subtotal, 
                            o.grand_total, (o.grand_total-(o.shipping_amount+o.tax_amount)) AS commission_calculatable_total, 
                            (SELECT SUM(oi.row_total_incl_tax - oi.discount_amount) FROM ".MAGENTO_TABLE_PREFIX."sales_flat_order_item AS oi WHERE o.entity_id=oi.order_id) AS original_total
                            FROM " . MAGENTO_TABLE_PREFIX . "sales_flat_order AS o
                            INNER JOIN " . MAGENTO_TABLE_PREFIX . "amasty_amorderattr_order_attribute AS oa ON oa.order_id = o.entity_id
                            WHERE
                                o.created_at >= '" . date("Y", $p_ts) . "-" . date("m", $p_ts) . "-01 00:00:00'
                                AND o.created_at <= '" . date("Y", $p_ts) . "-" . date("m", $p_ts) . "-" . date("t", $p_ts) . " 23:59:59'
                                AND oa.jareferrer_self=0
                                AND oa.jareferrer_amicoid NOT IN ('".implode("','",$memberIdsCollectedInTheInfoArr)."')
                         ";
                    //$sql .= " AND oa.jareferrer_amico_member_id = 0 ";
                }

                //echo $sql; die();

                $ord_res = mysqli_query($conn, $sql);
                echo mysqli_error($conn);

                if (mysqli_num_rows($ord_res) > 0) {
                    //echo $sql; echo PHP_EOL.PHP_EOL;//die();
                    //$rmps[$m_id]['RMP']['orders_sql'] = $sql;
                }

                if (mysqli_num_rows($ord_res) > 0) {
                    while ($orders_id = mysqli_fetch_assoc($ord_res)) {

                        $m_id = $orders_id['m_id'];

                        // At this stage, the rule is, an Ambassador will receive commission if the
                        // total sale (own purchase + referred sale) is $200 or more. But, for Ambassador Pros
                        // We will not collect the Self orders
                        if( !is_amico_an_ambassador($m_id) && !empty($orders_id['member_self_order']) ) {
                            continue;
                        }

                        $sql = "SELECT op.products_model, (op.final_price * op.products_quantity) as final_price, tcr.* FROM orders_products op LEFT JOIN tbl_commision_rule tcr ON op.products_model = tcr.str_commision_rule WHERE op.orders_id = '$orders_id[0]'";

                        if ($useMagento) {
                            $sql = "SELECT IFNULL(oi.sku, '') AS sku, 
                                    ({$orders_id['commission_calculatable_total']} * ((oi.row_total_incl_tax-oi.discount_amount)/{$orders_id['original_total']})) as final_price, tcr.*
                                    FROM " . MAGENTO_TABLE_PREFIX . "sales_flat_order_item AS oi
                                    LEFT JOIN tbl_commision_rule AS tcr ON IFNULL(oi.sku, '') = tcr.str_commision_rule
                                    WHERE
                                        oi.order_id = '{$orders_id['entity_id']}'
                                ";
                        }

                        //echo $sql; die();

                        $prod_res = mysqli_query($conn, $sql);
                        echo mysqli_error($conn);

                        //$rmps[$m_id]['RMP']['orders_product_sql'] = $sql;

                        $commissionable = 0;
                        $amount = 0;

                        while ($prdinfo = mysqli_fetch_assoc($prod_res)) {

                            if (!empty($prdinfo['int_commision_rule_id'])) {
                                if ($prdinfo['int_value'] <= 0) {
                                    continue;
                                }
                                else {
                                    if ($prdinfo['bit_percentage'] == 1) {
                                        $commissionable += round(($prdinfo['int_value'] / 100) * $prdinfo['final_price'], 2);
                                    }
                                    else {
                                        $commissionable += $prdinfo['int_value'];
                                    }
                                }
                            }
                            else {
                                $commissionable += $prdinfo['final_price'];
                            }

                            $amount += $prdinfo['final_price'];
                        }

                        if(!empty($orders_id['subtotal'])) {
                            $amount = ($amount > $orders_id['subtotal']) ? $orders_id['subtotal'] : $amount;
                            $commissionable = ($commissionable > $orders_id['subtotal']) ? $orders_id['subtotal'] : $commissionable;
                        }

                        $info[$m_id]['RMP'][$orders_id['orders_id']] = $m_id . "|" . $m_id . "|0|" . round($commissionable, 2) . "|" . $amount . "|" . $orders_id['date_purchased'] . "|" . (int)$orders_id['member_self_order'];

                        /*if( !empty( $info[$m_id]['RMP'][$orders_id[0]] ) ) {
                            $rmps[$m_id]['RMP']['orders'][$orders_id[0]] = $info[$m_id]['RMP'][$orders_id[0]];
                            $rmps[$m_id]['RMP']['orders_count_main']= count( $rmps[$m_id]['RMP']['orders'] );
                        }*/

                        //print_r( $info[$m_id]['RMP'][$orders_id['orders_id']] ); die();
                    }
                }
            }
        }


        foreach ($info as $m_id => $invoices)
		{
			$info = get_children_info($m_id, $m_id, $info, 0, $period);

			ksort($info[$m_id]['MP']);

			if(isset($info[$m_id]['RMP']))
			{
				ksort($info[$m_id]['RMP']);

                /*if( !empty($info[$m_id]['RMP']) ) {
                    $rmps[$m_id]['RMP']['orders'] = $info[$m_id]['RMP'];
                    $rmps[$m_id]['RMP']['orders_count_all']= count( $rmps[$m_id]['RMP']['orders'] );
                }*/
			}
		}


		// Find "No Purchase Required" members, don't include
        // if they are already in the $info array
		$noPurchaseRequired_sql = " SELECT int_member_id, amico_id FROM tbl_member WHERE bit_no_purchase_required=1 ";
        $noPurchaseRequired_query = mysqli_query($conn, $noPurchaseRequired_sql) or die(mysqli_error($conn));
        while( $noPurchaseRequired_member = mysqli_fetch_assoc($noPurchaseRequired_query) )
        {
            if( empty($info[ $noPurchaseRequired_member['amico_id'] ]['MP']) ) {
                $noPurchaseRequired_members_amico[] = $noPurchaseRequired_member['amico_id'];
                $noPurchaseRequired_members_memberId[] = $noPurchaseRequired_member['int_member_id'];
            }
        }

        // This is the list for the filtered "No Purchase Required" members
        // who are not included in the $info array. Now, find the members
        // whose child members has commission on the $info array. We will
        // give these filtered members commission.
        if(!empty($noPurchaseRequired_members_amico))
        {
            foreach($noPurchaseRequired_members_amico as $nprAmico)
            {
                $info[$nprAmico] = empty($info[$nprAmico]) ? array('MP' => array(), 'RMP' => array()) : $info[$nprAmico];
                $nprAmicoComs = get_noPurchaseRequiredMember_children($nprAmico, $info);
                if(!empty($nprAmicoComs)) {
                    $arrays[$nprAmico] = $nprAmicoComs;
                    if(!empty($arrays[$nprAmico]['MP']) && is_array($arrays[$nprAmico]['MP']) ) {
                        $info[$nprAmico]['MP'] += $arrays[$nprAmico]['MP'];
                    }
                    if(!empty($arrays[$nprAmico]['RMP']) && is_array($arrays[$nprAmico]['RMP']) ) {
                        $info[$nprAmico]['RMP'] += $arrays[$nprAmico]['RMP'];
                    }
                }
            }
        }

        //echo '<pre>'; print_r(array($arrays, $noPurchaseRequired_members_amico, $info['W01012907'], $info['W01009021']) ); echo '</pre>'; die();
?>
					<table border="1" cellpadding="2" cellspacing="0">
					 <th>Member ID</th>
					 <th>Sales</th>
					 <th>Amount Commissionable</th>
					 <th>Commission Earned</th>
<?php

        $amicocommissionableMP = $amicocommissionableRMPSqls = array();

		foreach ($info as $m_id => $invoices)
		{
			$sales = 0;
			$commissionable = 0;
			$commission_earned = 0;
			//echo $m_id;
            $stop_processing = false;


            $ambassadorAmico = '';
            if( is_amico_an_ambassador($m_id) ) {
                $ambassadorAmico = $m_id;
                //$amicocommissionableMP[$ambassadorAmico] = 0;
            }

            if(isset($invoices['MP'])) {
                foreach ($invoices['MP'] as $i_id => $data)
                {
                    list($member_path, $src_member, $level, $i_commissionable, $i_amount, $date) = explode("|", $data);

                    if($level == 0)
                    {
                        if( !empty($ambassadorAmico) && ($ambassadorAmico == $member_path) ) {
                            $ambassadorCommissionsAble[] = $i_commissionable;
                        } else {
                            $commissionable += $i_commissionable;
                        }
                    }
                }
            }

			// At this stage, if the Current member has less than $100 "Child Member Purchase",
            // but has Consumer purchases who bought directly using this member's ID,
            // we are going to give the member a Commission Percentage

			//if($commissionable < 100) { continue; }

            if( ($commissionable < 100)  ) {

                //If the member is chosen as "No Purchase Required", then
                //We will ignore the "$100 child member purchase required" rule.
			    if( !get_is_noPurchaseRequired_by_amico($m_id) && empty($ambassadorAmico) ) {
                    $stop_processing = true;
                }
			}

			if(!empty($ambassadorAmico)) {
                //$stop_processing = true;
            }

			//if( $ambassadorAmico ) { echo "<pre>ProcessStoppedFor: $m_id</pre><br/>"; }
            //if($m_id == 'A00000010') { print_r($invoices); var_dump($stop_processing);  }

            if( !$stop_processing ) {
                $commissionable = 0;



                if(isset($invoices['MP']))
                {
                    foreach ($invoices['MP'] as $i_id => $data) {
                        list($member_path, $src_member, $level, $i_commissionable, $i_amount, $date) = explode("|", $data);

                        // Get custom comission info
                        $sql = "SELECT bit_custom_comission, expire_custom_comission FROM tbl_member WHERE amico_id='$src_member'";
                        $query = mysqli_query($conn, $sql);

                        list($bit_custom_comission, $expire_custom_comission) = mysqli_fetch_row($query);
                        // Set comission, if custom comission not expire set 20% for current user invoice
                        $cur_date = (int)strtotime($date);
                        if ($expire_custom_comission)
                            $comission_expire = (int)strtotime($expire_custom_comission);
                        else
                            $comission_expire = 0;


                        // We are not giving any commission to the Ambassadors for their own purchases,
                        // but we are calculating them to match $200 or more
                        $is_sourceAmico_anAmbassador = is_amico_an_ambassador($src_member);
                        if ($is_sourceAmico_anAmbassador) {
                            $amicocommissionableMP[$src_member][$i_id] = $i_commissionable;
                            //var_dump($src_member, $amicocommissionableMP); //die();
                        }


                        if ($level == 0 || $level == 1 || $level == 2 || $level == 3 || $level == 6) {
                            if (((int)$bit_custom_comission == 1) && ($cur_date < $comission_expire)) {
                                if ((int)$level == 1) {
                                    $i_percentage = 20;
                                } elseif ((int)$level == 0) {
                                    $i_percentage = 5;
                                } else {
                                    $i_percentage = 0;
                                }

                            } else {
                                $i_percentage = 5;
                            }

                        } else if ($level == 4 || $level == 5) {
                            if (((int)$bit_custom_comission == 1) && ($cur_date < $comission_expire)) {
                                $i_percentage = 0;
                            } else {
                                $i_percentage = 2;
                            }
                        }
                        if (!empty($ambassadorAmico)) {
                            if ($level == 0) {
                                $i_percentage = 0;
                            }
                        }

                        //var_dump($m_id, $i_id, ($level > 0), $i_percentage);

                        if (empty($i_percentage)) {
                            continue;
                        }

                        $i_commission_earned = round(($i_percentage / 100) * $i_commissionable, 2);

                        list($month, $day, $year) = explode('/', $date);
                        $sql_date = $year . '-' . $month . '-' . $day;

                        $sql = "INSERT INTO stw_data (type, report_id, member_id, src_member_id, invoice_id, invoice_date, level, member_path, amount, commissionable, commissioned, percentage) VALUES ('MP', '$r_id', '$m_id', '$src_member', '$i_id', '$sql_date', '$level', '$member_path', '$i_amount', '$i_commissionable', '$i_commission_earned', '$i_percentage')";

                        //if(!empty($ambassadorAmico) ) { var_dump($ambassadorAmico, $i_id, ($level > 0)); }

                        if (!empty($ambassadorAmico) && ($level > 0)) {
                            $amicocommissionableRMPSqls[$ambassadorAmico][$i_id] = $sql;
                            continue;
                        }

                        mysqli_query($conn, $sql);
                        echo mysqli_error($conn);

                        $sales += $i_amount;
                        $commissionable += $i_commissionable;
                        $commission_earned += $i_commission_earned;
                    }
                }
            }
            else {
                $commissionable = 0;
			}

            if(!empty($ambassadorAmico)) {
                //echo '<pre>1: '; var_dump($amicocommissionableMP, $amicocommissionableRMPSqls); echo '</pre>'; //die();
            }


			if(isset($invoices['RMP']))
			{
				foreach ($invoices['RMP'] as $i_id => $data)
				{
					list($member_path, $src_member, $level, $i_commissionable, $i_amount, $date, $is_selfPurchase) = explode("|", $data);
					
					if($i_commissionable <= 0)
					{
						continue;
					}

                    $i_percentage = null;

					// At this stage, if the Current member has less than $100 "Child Member Purchase",
                    // but has Consumer purchases who bought directly using this member's ID,
                    // we are going to give the member a Commission Percentage
					if($level == 0)
					{
						$i_percentage = 35;
                        if(!empty($ambassadorAmico)) {
                            $i_percentage = 30;
                        }
					}
					else if( ($level == 1 || $level == 2 || $level == 3 || $level == 6) && !$stop_processing )
					{
						$i_percentage = 3.25;
					}
					else if( ($level == 4 || $level == 5) && !$stop_processing )
					{
						$i_percentage = 1.30;
					}

                    // We are not giving any commission to the Ambassadors for their own purchases,
                    // but we are calculating them to match $200 or more
                    $is_sourceAmico_anAmbassador = is_amico_an_ambassador($src_member);
                    if( $is_sourceAmico_anAmbassador ) {
                        $amicocommissionableMP[$src_member][$i_id] = $i_commissionable;
                    }

					if(empty($i_percentage)) {
					    continue;
					}

                    $i_commission_earned = round(($i_percentage/100)*$i_commissionable, 2);

                    $sql = "INSERT INTO stw_data (type, report_id, member_id, src_member_id, invoice_id, invoice_date, level, member_path, amount, commissionable, commissioned, percentage) VALUES ('RMP', '$r_id', '$m_id', '$src_member', '$i_id', '$date', '$level', '$member_path', '$i_amount', '$i_commissionable', '$i_commission_earned', '$i_percentage')";

                    //if(!empty($ambassadorAmico) ) { var_dump($ambassadorAmico, $i_id, ($level > 0)); }

                    if(!empty($ambassadorAmico) && ($level > 0) ) {
                        $amicocommissionableRMPSqls[$ambassadorAmico][$i_id] = $sql;
                        continue;
                    }

                    mysqli_query($conn,$sql);
                    echo mysqli_error($conn);

                    //echo '<pre>'; print_r(array($i_id, $data, $sql) ); echo '</pre>'; //die();


					$sales += $i_amount;
					$commissionable += $i_commissionable;
					$commission_earned += $i_commission_earned;
				}
			}

			//echo '<pre>'; var_dump($amicocommissionableMP, $amicocommissionableRMPSqls); die();

?>
				   <tr>
					<td><?=$m_id?></td>
					<td align="right">$<?=number_format(round($sales, 2), 2)?></td>
					<td align="right">$<?=number_format(round($commissionable, 2), 2)?></td>
					<td align="right">$<?=number_format(round($commission_earned, 2), 2)?></td>
				   </tr>
<?
			$sql = "SELECT int_member_id FROM tbl_member WHERE amico_id = '$m_id'";
			$result = mysqli_query($conn,$sql);
			echo mysqli_error($conn);
			$int_member_id = mysqli_result($result, 0);

			$sql = "INSERT INTO tbl_commision_sales_history (int_member_id, int_commision, int_sales, dtt_calculate, int_month, int_year) VALUES ('$int_member_id', '".round($commission_earned, 2)."', '".round($sales, 2)."', NOW(), '".date("m", $p_ts)."', '".date("Y", $p_ts)."')";
			mysqli_query($conn,$sql);
			echo mysqli_error($conn);
		}

        //echo '<pre>2: '; var_dump($amicocommissionableMP, $amicocommissionableRMPSqls); echo '</pre>'; //die();

        if(!empty($amicocommissionableMP) && !empty($amicocommissionableRMPSqls)) {
            foreach($amicocommissionableMP as $ambAmicoId => $personalSales) {
                $personalSale = array_sum($personalSales);
                if($personalSale >= 200 && !empty($amicocommissionableRMPSqls[$ambAmicoId]) )
                {
                    mysqli_query($conn, implode('; ', (array)$amicocommissionableRMPSqls[$ambAmicoId]) );
                    echo mysqli_error($conn);

                    unset($amicocommissionableMP[$ambAmicoId], $amicocommissionableRMPSqls[$ambAmicoId]);
                }
            }
        }

?>
				</table><br>
				<center><a href="excel_export.php?id=<?=$r_id?>"><font color="#0000CC">Export To Excel</font></a></center><br>
<?php

	}

    $sql = "SELECT tm.int_member_id, tm.int_parent_id
    FROM stw_data stw
    INNER JOIN tbl_member tm ON stw.member_id = tm.amico_id
    WHERE stw.report_id = '".$r_id."'
    GROUP BY stw.member_id
    ORDER BY stw.member_id";
    $result = mysqli_query($conn,$sql);

    $member_parents = array();
    while($row = mysqli_fetch_assoc($result))
    {
        $member_parents[$row['int_member_id']] = $row['int_parent_id'];
    }

    $member_active_members = array();
    foreach($member_parents as $parent_id)
    {
      if(!isset($member_active_members[$parent_id])) $member_active_members[$parent_id] = 1;
      else $member_active_members[$parent_id]++;
    }

    foreach($member_active_members as $int_member_id => $active_members)
    {
        $int_designation_id = get_member_designation_id($active_members);

        $sql = "UPDATE tbl_member SET int_designation_id='$int_designation_id' WHERE int_member_id = '$int_member_id'";
		mysqli_query($conn,$sql);
		echo mysqli_error($conn);
    }


    $sql = "SELECT amico_id, int_designation_id FROM tbl_member GROUP BY int_member_id ORDER BY int_member_id";
    $result = mysqli_query($conn,$sql);
    echo mysqli_error($conn);

    while($row = mysqli_fetch_row($result))
    {
    	$sql = "INSERT INTO past_designations SET report_id = '$r_id', member_id='$row[0]', int_designation_id='$row[1]'";
        mysqli_query($conn,$sql);
    }

	return;
}


    $page_name = 'STW Report Upload';
    $page_title = 'John Amico - ' . $page_name;

    require_once("templates/header.php");
    require_once("templates/sidebar.php");

    $member_type_name = 'STW Report Upload';
    //$member_type_name_plural = 'STW Reports';
    $self_page = 'stw_reports.php';
    $page_url = base_admin_url() . '/stw_reports.php?1=1';


    ?>
    <script type="text/javascript">
        <!--
        function fileReg() {
            var Doc = document.file_upload.elements['csv'];

            //console.log(Doc);

            if (! /(STW)?\.(csv)$/.test(Doc.value)) {
                alert('The file must be called STW.csv in order for the upload to work');
                return false;
            }
            else {
                return true;
            }
        }
        //-->
    </script>


    <div role="main" class="content-body">
        <header class="page-header">
            <h2><?php echo $page_name; ?></h2>

            <div class="right-wrapper pull-right">
                <ol class="breadcrumbs">
                    <li>
                        <a href="<?php echo base_admin_url(); ?>">
                            <i class="fa fa-home"></i>
                        </a>
                    </li>
                    <li><span><?php echo $page_name; ?></span></li>
                </ol>
                <a class="sidebar-right-toggle"></a>
            </div>
        </header>

        <div class="row ">
            <section class="panel">
                <form name="file_upload" class="form-bordered" onSubmit="return fileReg();" action="" method="post" enctype="multipart/form-data">
                    <div class="col-xs-12 col-lg-10 col-md-10 centering">
                        <header class="panel-heading">
                            <h2 class="panel-title text-center">Upload CSV file</h2>
                        </header>
                        <div class="panel-body pb-lg pt-lg mb-lg mt-lg">
                            <div class="form-group">
                                <label class="col-md-3 control-label">File Upload</label>
                                <div class="col-md-6">
                                    <input type="file" name="csv" class="form-control">
                                    <input type="hidden" value="1" name="Upload">
                                </div>
                            </div>
                        </div>
                        <footer class="panel-footer text-center">
                            <input type="submit" value="Upload" />
                        </footer>
                    </div>
                </form>
                <div class="clearfix"></div>
            </section>
            <section class="panel">
                <div class="col-xs-12">
                    <?php
                    //parse_csv_file("STW.csv");

                    /* If a file has been selected for upload... */

                    //echo '<pre>'; var_dump($_POST, $_FILES); echo ''; die();

                    if ( ($Upload == 1) && !empty($_FILES['csv']) ):
                        $file_info = pathinfo( $_FILES['csv']['name'] );

                        if( !empty($file_info) ) {
                            $fileName = $file_info['filename'] . "___" . date('d-M-Y__G-i') . "." . $file_info['extension'];
                            $csv_file = CSV_DIR . $fileName;
                            $csv_name = $_FILES['csv']['name'];
                        }

                        echo "<center><pre>";
                        if (move_uploaded_file($_FILES['csv']['tmp_name'], $csv_file)):
                            echo "Received ".$csv_name." successfully.<br />";
                            parse_csv_file($fileName);
                        else:
                            echo "Problem with file upload.<br />";
                            echo "<br>".$csv_file."<br>".$csv_name."<br>";
                            print_r($_FILES);
                            exit;
                        endif;
                        echo "</pre></center>";

                        unset($Upload);
                    endif;
                    ?>
                </div>
            </section>
        </div>
    </div>


<?php
require_once("templates/footer.php");
