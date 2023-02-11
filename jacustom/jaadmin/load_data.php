<?php
require_once("../common_files/include/global.inc");

if($_POST['page'])
{
	$mtype='m';
	$page = $_POST['page'];
	$cur_page = $page;
	$page -= 1;
	$per_page = 30;
	$previous_btn = true;
	$next_btn = true;
	$first_btn = true;
	$last_btn = true;
	$start = $page * $per_page;
	$condition=' Where ';
	$alpabet = $_POST['alpha'];
	$designations = $_POST['designation'];
	$sort = $_POST['sorti'];
		if(($designations=='none') and ($alpabet!='ALL'))
		{
			$condition.="mem.mtype='$mtype' and cus.customers_firstname like('".$alpabet."%') order by cus.customers_firstname";
		}
		elseif(($alpabet!='ALL') and ($designations!=0))
		{
			if($sort==0)
			{
				$condition.="mem.mtype='$mtype' and  mem.int_designation_id=$designations and cus.customers_firstname like('".$alpabet."%') order by cus.customers_firstname";
			}
			else
			{
				$condition.="mem.mtype='$mtype' and  mem.int_designation_id=$designations and cus.customers_lastname like('".$alpabet."%') order by cus.customers_lastname";
			}
		
		}
		elseif(($alpabet=='ALL') and ($designations!=0))
		{
			$condition.="mem.mtype='$mtype' and  mem.int_designation_id=$designations order by cus.customers_firstname";
		}
		elseif(($alpabet!='ALL') and ($designations==0))
		{
			if($sort==0)
			{
				$condition.="mem.mtype='$mtype' and  cus.customers_firstname like('".$alpabet."%') order by cus.customers_firstname";
			}
			else
			{
				$condition.="mem.mtype='$mtype' and  cus.customers_lastname like('".$alpabet."%') order by cus.customers_lastname";
			}
		}
		elseif(($alpabet=='ALL') and ($designations==0))
		{
			//echo "all";
			$condition="";
			
		}
		else
		{
			$condition="";
		}
	//echo $condition;die;
	//$query_pag_data = "select m.int_member_id,m.int_designation_id,m.amico_id,c.customers_id,c.customers_email_address,c.customers_firstname,c.customers_lastname,m.bit_active, m.ec_id, m.new_ec_id from tbl_member m left join customers c on m.int_customer_id=c.customers_id order by c.customers_firstname LIMIT $start, $per_page";
	
	$query_pag_data = "SELECT mem.int_member_id,mem.int_designation_id,mem.amico_id,cus.customers_id,cus.customers_email_address,cus.customers_firstname,cus.customers_lastname,mem.bit_active, mem.ec_id, mem.new_ec_id From tbl_member as mem LEFT JOIN customers as cus ON mem.int_customer_id = cus.customers_id $condition LIMIT $start, $per_page";
	$result_pag_data = mysqli_query($conn,$query_pag_data) or die('MySql Error' . mysql_error());
    echo $query_pag_data;
	$msg = "<table id='tablepaging' class='yui table table-bordered table-striped mb-none' align='center' >
					<thead><tr>
						<th>First Name,Last Name</th>
						<th>Email Address</th>
						<th>Amico ID</th>
						<th>Join Date</th>
						<th>Ec ID</th>
						<th>Active?</th>
						<th colspan='1'>Commands</th>
					</tr></thead>";
	while ($row = mysqli_fetch_array($result_pag_data)) {
	$query=mysqli_query($conn,"SELECT * FROM `tbl_member_ec` WHERE `amico_id`='".$amico_id."' ORDER BY timestamp asc LIMIT 1");
	$f=mysqli_fetch_array($query);
	
	//echo "<pre>";print_r($row);
	//$htmlmsg=htmlentities($row['r_name']);
	//$msg .= "<li><b>" . $row['r_id'] . "</b> " . $htmlmsg . "</li>";
	$firstname=$row['customers_firstname'];
	$lastname=$row['customers_lastname'];
	$email=$row['customers_email_address'];
	$amico_id=$row['amico_id'];
	$memberid=$row['int_member_id'];
	$designations=$row['int_designation_id'];
	//echo "<pre>";print_r($f);
	$joindate = substr($f['timestamp'], 0, 10);
	$ec_id = $row['ec_id'];
	$active = $row['bit_active'];
	if($active == 1){
		$color = 'green';}
	else{
		$color = 'red';}
	if($active == 1){
		$submit = '<input type="submit" name="activate" value="   Active   " class="command" >';
	}
	else{ 
		$submit ='<input type="submit" name="activate" value=" Deactive " class="command danger">';
	}
	$msg.="<tr><td >$firstname , $lastname</td><td >$email</td><td >$amico_id</td><td >$joindate</td> <td  align=center>$ec_id</td><td align='center' ><form action='act_members.php' method='post'>
	<input type='hidden' name='memberid' value=$memberid>
							<input type='hidden' name='active' value=$active>
							<input type='hidden' name='alpabet' value=$alpabet>
							<input type='hidden' name='designations' value=$designations>
							<input type='hidden' name='sort' value='$sort'>
							$submit
							</form>
						</td>
						
					
						<td align='center' >
						<form action='members.php' method='post'>
							<input type='hidden' name='memberid' value=$memberid>
							<input type='submit' name='edit' value=' Edit ' class='command'>&nbsp;	
							<input type='button' name='delete' value='Delete'
							style='font-size: 10pt; height: 22; font-family:
							arial; filter:progid:DXImageTransform.Microsoft.Gradient
							(endColorstr=\"#ffffff\", startColorstr=\"#CCCCCC\"
							, gradientType=\"1\");' onClick='return	confirmCleanUp(\"act_members.php?memberid=". $memberid ."&delete=1\")'>
							</form>
						</td>
						
					</tr>";
					   /*********/
 
    /*********/
    
}
//die;
$msg = "<div class='data'><ul>" . $msg . "</ul></div>"; // Content for Data

//echo "<pre>";print_r($msg);die;
/* --------------------------------------------- */
$query_pag_num = "SELECT COUNT(*) AS count FROM tbl_member as mem left join customers as cus on mem.int_customer_id=cus.customers_id $condition";
$result_pag_num = mysqli_query($conn,$query_pag_num);
$row = mysqli_fetch_array($result_pag_num);
$count = $row['count'];

$no_of_paginations = ceil($count / $per_page);

/* ---------------Calculating the starting and endign values for the loop----------------------------------- */
if ($cur_page >= 7) {
    $start_loop = $cur_page - 3;
    if ($no_of_paginations > $cur_page + 3)
        $end_loop = $cur_page + 3;
    else if ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6) {
        $start_loop = $no_of_paginations - 6;
        $end_loop = $no_of_paginations;
    } else {
        $end_loop = $no_of_paginations;
    }
} else {
    $start_loop = 1;
    if ($no_of_paginations > 7)
        $end_loop = 7;
    else
        $end_loop = $no_of_paginations;
}
/* ----------------------------------------------------------------------------------------------------------- */
$msg .= "<div class='col-xs-6'><div class=\"dataTables_paginate paging_bs_normal\" id=\"datatable-ajax_paginate\"><ul class=\"pagination\">";



// FOR ENABLING THE FIRST BUTTON
if ($first_btn && $cur_page > 1) {
    //$msg .= "<li p='1' class='active'>First</li>";
    $msg .= '<li p="1" class="first"><a href="#"><span class="fa fa-step-backward"></span></a></li>';
} else if ($first_btn) {
    //$msg .= "<li p='1' class='inactive'>First</li>";
}

// FOR ENABLING THE PREVIOUS BUTTON
if ($previous_btn && $cur_page > 1) {
    $pre = $cur_page - 1;
    //$msg .= "<li p='$pre' class='active'>Previous</li>";
    //$msg .= "<li p='$pre' class='active'>Previous</li>";
    $msg .= '<li class="prev" p="'.$pre.'" ><a href="#"><span class="fa fa-chevron-left"></span></a></li>';
} else if ($previous_btn) {
    //$msg .= "<li class='inactive'>Previous</li>";
    $msg .= '<li class="prev disabled" ><a href="#"><span class="fa fa-chevron-left"></span></a></li>';
}
for ($i = $start_loop; $i <= $end_loop; $i++) {

    if ($cur_page == $i) {
        //$msg .= "<li p='$i' style='color:#fff;background-color:#006699;' class='active'>{$i}</li>";
        $msg .= '<li class="active" p="' . $i . '" ><a href="#">' . $i . '</a></li>';
    } else {
        //$msg .= "<li p='$i' class='active'>{$i}</li>";
        $msg .= '<li class="" p="' . $i . '" ><a href="#">' . $i . '</a></li>';
    }
}

// TO ENABLE THE NEXT BUTTON
if ($next_btn && $cur_page < $no_of_paginations) {
    $nex = $cur_page + 1;
    //$msg .= "<li p='$nex' class='active'>Next</li>";
    $msg .= '<li p="'.$nex.'" class="next"><a href="#"><span class="fa fa-chevron-right"></span></a></li>';
} else if ($next_btn) {
    //$msg .= "<li class='inactive'>Next</li>";
    $msg .= '<li class="next disabled"><a href="#"><span class="fa fa-chevron-right"></span></a></li>';
}

// TO ENABLE THE END BUTTON
if ($last_btn && $cur_page < $no_of_paginations) {
    //$msg .= "<li p='$no_of_paginations' class='active'>Last</li>";
    $msg .= '<li p="'.$no_of_paginations.'" class="last"><a href="#"><span class="fa fa-step-forward"></span></a></li>';
} else if ($last_btn) {
    //$msg .= "<li p='$no_of_paginations' class='inactive'>Last</li>";
}
$goto = "<div class='col-xs-2 text-left'><div class='goto_wrapper'><div class='input-group mb-md'><input type='text' class='goto form-control input-sm mb-md' size='1' /><span class='input-group-btn'><button id='go_btn' class='go_btn btn btn-success btn-sm' type='button'>Go!</button></span></div><div class='clearfix'></div></div></div>";
$total_string = "<div class='col-xs-4 text-right'><div class='pagination_text_wrapper'><span class='total' a='$no_of_paginations'>Page <b>" . $cur_page . "</b> of <b>$no_of_paginations</b></span></div></div>";
$msg = $msg . "</ul></div></div>". $goto . $total_string . "";  // Content for pagination
echo $msg;
}

