<?php
require_once("../common_files/include/global.inc");
require_once("session_check.inc");

if(isset($_POST['add'])){
	if(isset($_POST['chk_percent']))
		$percent=1;
	else
		$percent=0;
		
		$table = "tbl_commision_rule";				// inserting values to setting table
		$in_fieldlist="str_commision_rule,bit_commisionable,bit_percentage,int_value,bit_active";
		$in_values="'{$_POST['txt_rulename']}',1,$percent,{$_POST['txt_value']},1";
		$result=insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values
}
	
elseif(isset($_POST['update'])){
	if(isset($_POST['chk_percent']))
		$percent=1;
	else
		$percent=0;
	 
	 $table = "tbl_commision_rule";
	 $fieldlist="str_commision_rule='{$_POST['txt_rulename']}', bit_percentage=$percent,int_value={$_POST['txt_value']}";
	 $condition=" where int_commision_rule_id = {$_POST['ruleid']}";
	 $result=update_rows($conn, $table, $fieldlist, $condition);
}
elseif(isset($_POST['activate'])){
	 if($_POST['active']==0){$active=1;}else{$active=0;}
	 $table = "tbl_commision_rule";
	 $fieldlist="bit_active=$active";
	 $condition=" where int_commision_rule_id = {$_POST['ruleid']}";
	 $result=update_rows($conn, $table, $fieldlist, $condition);
}
elseif(isset($_GET['delete'])){
	$table = "tbl_commision_rule";
	$condition=" where int_commision_rule_id = {$_GET['ruleid']}";
	$result=del_rows($conn, $table, $condition);// function call to delete
}

$page = ( ( !empty($_REQUEST['page']) && is_numeric($_REQUEST['page']) ) ? $_REQUEST['page'] : 1 );
$sort = ( isset($_REQUEST['sort']) && is_numeric($_REQUEST['sort']) ?  filter_var($_REQUEST['sort'], FILTER_SANITIZE_NUMBER_INT) : '' );
$alpabet = ( !empty($_REQUEST['alpabet']) ? filter_var($_REQUEST['alpabet'], FILTER_SANITIZE_STRING) : 'A' ) ;

header("Location: commision_rules.php?1". ( is_numeric($sort) ? '&sort=' . $sort : '' ) . ( is_numeric($designations) ? '&designations=' . $designations : '' ) . ( !empty($alpabet) ? '&alpabet=' . $alpabet : '' ) . '&page=' . $page);
