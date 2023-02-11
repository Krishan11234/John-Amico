<?php
/*	Project Name		: amico
   	Program name		: global.inc
 	Program function	: global file which will be included in all php pages where ever necessary.
 	Created Date  		: 16 jun 2003
 	Last Modified		: 16 jun 2003
	Author				: Colbridge
	Developed by	    : Colbridge Web Logics - www.colbridge.com */
//-----------------------------------------------------------------------------

// parameters for local dev server
/*$db="amico_full";
$host="sql1";
$username="Administrator";
$password="password"; */

// parameters for server at site
$db="amico_full";
$host="sql1";
$username="amico";
$password="full"; 

// define constants	
define("DB",$db);
define("HOST",$host);
define("USERNAME",$username);
define("PASSWORD",$password);	
    
$connct = mysql_connect(HOST, USERNAME, PASSWORD);

define("CONN",$connct);
			
$check=mysql_select_db(DB,CONN);

// to check weather the database exists or not   
if ($check){
	$DBhasSetup = 1;
}
else{
	$DBhasSetup = 0;
}	
	

// ------function to open the database ---------------
function open_db($db,$host,$username,$password) {
	 $connct = mysql_connect($host,$username,$password);
     if ($connct) 
	 { 
        if (!mysql_select_db($db,$connct)) 
		{
            $status = mysql_error();
		} else 
		{
            $status= 0;
        }
		return $connct;
 	}  
	else 
	{
        $status = mysql_error();
    }
}

// ------function to close the database ---------------
function close_db($connct) {
    if ( mysql_close($connct) ) {
        $status = 1;
    } else {
        $status = 0;
    }
    return($status);
}

// ----- function to insert values in a table------
function insert_fields($connct, $table, $in_fieldlist, $in_values){
$sql="insert into $table ($in_fieldlist) values ($in_values)";
	 $result =  mysql_db_query(DB,$sql,$connct);
 if (!$result) 
 {
   echo("Unable to Insert value into ".$table. " table....<br>");
   echo $sql;
   die(mysql_error());
   return;
 }
  return $result;
}

// ----- function to select records from a table------
function sel_rows($connct,$table,$fieldlist,$condition){
	$sql="select $fieldlist from $table $condition ";	
	$result =  mysql_db_query(DB,$sql,$connct);
	if (!$result){
	   echo("Unable to Fetch ". $fieldlist ." from ". $table ." table.....<br>");
	   die(mysql_error());
	   return;
	}
	return $result;
}

// ----- function to delete rows from a table------
function del_rows($connct, $table,$condition){
	$sql="delete from $table $condition";
    $result =  mysql_db_query(DB,$sql,$connct);
	if (!$result){
		echo("Unable to Delete rows from ".$table . " table....<br>");
		echo $sql;
		die(mysql_error());
	 	return;
	}
	return $result;
}

// ----- function to update records in a table------
function update_rows($connct, $table, $fieldlist, $condition){
	$sql="update $table set $fieldlist $condition";
    $result =  mysql_db_query(DB,$sql,$connct);
	if (!$result){
		echo("Unable to Update ". $fieldlist ." in ". $table ." table......<br>");
		echo $sql;	
		die(mysql_error());
		return;
	}
	return $result;
}

// ----- function to send mail ----
/*	four parameters..
	1.	$mail_to  		: the mail address to send the mail
	2.  $mail_from		: from address
	3.  $sitelink		: Corresponding link for the site. 
	4.  $message		: Actual message

function send_mail($mail_to,$mail_from, $sitelink, $message) {
	$m_stat = mail($mail_to, $the_message, "$sitelink \n $message \n \n \n ", "From: $mail_from \nReply-To: $mail_from \nX-Mailer: PHP/" . phpversion()); 
}*/
function log_admin($int_admin_id, $str_username){
	mysqli_query($conn,"insert into tbl_admin_logs set int_admin_id='$int_admin_id', str_username='$str_username', dt=now()");
}
?>