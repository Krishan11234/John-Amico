<?php 
require("../include/frontend_session_check.inc");

require("../include/global.inc");

require("../include/query_results_limit.php");

$a_amico = mysqli_fetch_array(mysqli_query($conn,"select * from tbl_member where int_member_id='".$_SESSION['ses_member_id']."'"));
$user_amico_id = $a_amico['amico_id'];
$user_mtype    = $a_amico['mtype'];

//echo "$user_amico_id {} $user_mtype {}".$_SESSION[ses_member_id];

if($_GET['delete']==1){
	$rslist=mysqli_query($conn,"select str_member_contact_list from tbl_member_contact_list where int_member_id=$_SESSION['ses_member_id']");
	list($contactlist)=mysqli_fetch_row($rslist);
	$listmembers=explode(",",$contactlist);
	for($i=0;$i<(count($listmembers)-1);$i++){
		if($_GET['delmemberid']!=$listmembers[$i]){
			$newmembers=$newmembers.$listmembers[$i].',';
		}
	}
	$sql="update tbl_member_contact_list set str_member_contact_list='".$newmembers."' where int_member_id=$_SESSION['ses_member_id']";
	$result =  mysql_db_query(DB,$sql,CONN);
}

if (($action == 'mass_email') && (!$HTTP_POST_VARS['back_x']) ) {
    $filename='contact_organizer.php';
    switch ($HTTP_POST_VARS['email_to']) {
      case 'downline':
        /*$mail_query = "SELECT customers.customers_firstname,
			      customers.customers_lastname,
			      customers.customers_email_address,
			      tbl_member.int_customer_id
		       FROM customers
		       LEFT JOIN tbl_member
		       ON tbl_member.int_customer_id=customers.customers_id 
		       WHERE tbl_member.int_parent_id='{$_SESSION[ses_member_id]}'";*/
		$rslist=mysqli_query($conn,"select str_member_contact_list from tbl_member_contact_list where int_member_id=$_SESSION['ses_member_id']");
		list($contactlist)=mysqli_fetch_row($rslist);
		$contactlist=substr($contactlist,0,(strlen($contactlist)-1));
		$mail_query= "select c.customers_firstname,c.customers_lastname,c.customers_email_address from customers c inner join tbl_member m on c.customers_id=m.int_customer_id where m.int_member_id in(".$contactlist.") ";
	//echo $mail_query;
	$results = mysqli_query($conn,$mail_query);
		while ($rows = mysqli_fetch_array($results)) {
           $this_to  = $rows['customers_firstname']." ".$rows['customers_lastname'];
           $this_to .= " <".$rows['customers_email_address'].">";
          mail($this_to,$_POST['subject'],$_POST['message'],"from:".$_POST['from']);
        
        } // while

        break;
      case 'other':
        $mail_query = "SELECT DISTINCT SUBSTRING_INDEX(customers_name, '\ ', 1) AS customers_firstname,
			      SUBSTRING_INDEX(customers_name, '\ ', -1) AS customers_lastname,
			      customers_email_address
		       FROM orders
		       WHERE customers_id='0'
		       AND int_member_id='{$_SESSION['ses_member_id']}'";

	$results = mysqli_query($conn,$mail_query);
        while ($rows = mysqli_fetch_row($results)) {
           $this_to  = $rows['customers_firstname']." ".$rows['customers_lastname'];
           $this_to .= " <".$rows['customers_email_address'].">";

           mail($this_to,$subject,$message,"from:".TITLE." <".EMAIL.">");

        } // while

        break;
      case 'both':
	$mail_query = "SELECT customers.customers_firstname,
                              customers.customers_lastname,
                              customers.customers_email_address,
                              tbl_member.int_customer_id
                       FROM customers
                       LEFT JOIN tbl_member
                       ON tbl_member.int_customer_id=customers.customers_id
                       WHERE tbl_member.int_parent_id='{$_SESSION['ses_member_id']}'";

        $results = mysqli_query($conn,$mail_query);
        while ($rows = mysqli_fetch_row($results)) {
           $this_to  = $rows['customers_firstname']." ".$rows['customers_lastname'];
           $this_to .= " <".$rows['customers_email_address'].">";

           mail($this_to,$subject,$message,"from:".TITLE." <".EMAIL.">");

	} //while

	$mail_query2 = "SELECT SUBSTRING_INDEX(customers_name, '\ ', 1) AS customers_firstname,
                               SUBSTRING_INDEX(customers_name, '\ ', -1) AS customers_lastname,
                               customers_email_address
                       FROM orders
                       WHERE customers_id='0'
                       AND int_member_id='{$_SESSION['ses_member_id']}'";
					   echo $mail_query;
        
        $results = mysqli_query($conn,$mail_query2);
        while ($rows = mysqli_fetch_row($results)) {
           $this_to  = $rows['customers_firstname']." ".$rows['customers_lastname'];
           $this_to .= " <".$rows['customers_email_address'].">";

           mail($this_to,$subject,$message,"from:".TITLE." <".EMAIL.">");

        } // while	        

	break;
    } //switch
/*
        $results = mysqli_query($conn,$mail_query);
        while ($rows = mysqli_fetch_row($results)) {
	   $this_to  = $rows['customers_firstname']." ".$rows['customers_lastname'].";
	   $this_to .= " <".$rows['customers_email_address'].">";
           
	   mail($this_to,$subject,$message,"from:".TITLE." <".EMAIL.">");
        
	} // while
	 Header("Location: ". $filename ."?action=success&customers_email_address=".$HTTP_POST_VARS['customers_email_address']);
*/
	 Header("Location: ". $filename ."?action=success");

  } //if

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../css/login.css" rel="stylesheet" type="text/css">

<link href="js/jscalendar/calendar-blue.css" rel="stylesheet" type="text/css" media="all">
<script language="javascript" src="js/jscalendar/calendar.js"></script>
<script language="javascript" src="js/jscalendar/calendar-en.js"></script>
<script language="javascript" src="js/jscalendar/calendar-setup.js"></script>

<style type="text/css">
.black1 {
font-size: 10pt;
}
</style>

<script language="JavaScript" type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
function confirmCleanUp(Link) {
   if (confirm("Are you sure you want to delete this Contact?")) {
	  location.href=Link;
   }
}
//-->
</script>
</head>

<body background="../images/bg_site.gif" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="100%" height="100%" border="0" cellpadding="5" cellspacing="0">
  <tr>
    <td width="215" valign="top"> 
      <div align="center">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr> 
            <td> <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr> 
                  <td height="35"><font color="#666666" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>:: 
                    CONTACT ORGANIZER ::</strong></font></td>
                </tr>
                <tr> 
                  <td height="30"><a href="contact_organizer.php">Contact List</a></td>
				</tr>
								<tr> 
                  <td height="30"><a href="update_my_info.php">Update My Info</a></td>
                </tr
                <tr> 
                  <td height="30"><a href="calender.php">Calendar of events</a></td>
                </tr>
                <tr> 
                  <td height="30"><a href="task_list.php">Task List</a></td>
                </tr>
                <tr> 
                  <td height="30"><a href="annotate_a_note_start.php">Annotate 
                    a Note</a></td>
                </tr>
                <tr> 
                  <td height="30"><a href="notes.php">Notes</a></td>
                </tr>
                <tr> 
                  <td height="30"><a href="customers.php">Customer Management</a></td>
                </tr>
				<tr> 
                  <td height="30"><a href="../welcome.php">Main Menu</a></td>
                </tr>
				
                <tr> 
                  <td height="30"><a href="../logout.php" target="_parent">:: 
                    logout ::</a></td>
                </tr>
                <tr> 
                  <td>&nbsp;</td>
                </tr>
              </table></td>
          </tr>
          <tr>
            <td>
<p align="center" class="copysmallblack"><img src="../images/ja.gif" width="123" height="101"></p>
              </td>
          </tr>
        </table>
      </div></td>
    <td><table width="90%" border="0" align="center" cellpadding="5" cellspacing="0">
        <tr> 
          <td bgcolor="#999999"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>JOHN 
            AMICO Contact Organizer</strong></font></td>
        </tr>
        <tr> 
          <td bgcolor="#CCCCCC" class="copysmallblack">
              <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr> 
                  <td width="320" valign="top">
				<table width="100%" style="border: solid #999999 1px;" border="0" cellspacing="2" cellpadding="2">
					<form action="contact_organizer.php" method="post" onsubmit="" name="frm_index">
                      <tr>
			<td colspan="4" align="center" class="loginsmall">
				Use the below interface to search through your contacts.
				<br />
				<br />
			</td>
			</tr>
			<tr> 
                        <td  class="loginsmall" align="right" nowrap="nowrap">
				Search Name:&nbsp;
			</td>
                        <td valign="top">
                            <input name="searchname" type="text" size="15" value="<?=$_POST['searchname']?>">
			</td>
			<td align="left">	
				<select name="nametype">
					<option value="customers_lastname"<?if($_POST['nametype']=="customers_lastname") echo " selected";?>>Last Name</option>
					<option value="customers_firstname"<?if($_POST['nametype']=="customers_firstname") echo " selected";?>>First Name</option>
				</select>
			</td>
                        
			<?php
				echo'<td width="24%"><a href=javascript:frm_index.submit()>GO!</a></td>';
                        ?>
						
                      </tr>
                      <tr> 
                        <td class="loginsmall" align="right" nowrap="nowrap">
				Search City:&nbsp;
			</td>
                        <td colspan="2">
                            <input name="searchcity" type="text" size="25" value="<?=$_POST['searchcity']?>">
                        </td>
  			
			<?php
				echo'<td width="24%"><a href=javascript:frm_index.submit()>GO!</a></td>';
                        ?>

                      </tr>
                      <tr> 
                        <td  class="loginsmall" align="right" nowrap="nowrap">
				Search State:&nbsp;
			</td>
                        <td colspan="2">
				<select name="searchstate">
				<option value="">-- All states --</option>
				<?php
					$rssearch=mysqli_query($conn,"select zone_id,zone_name, zone_country_id from zones WHERE zone_country_id='223'");
					while(list($zoneid,$zonename)=mysqli_fetch_row($rssearch)){
				?>
					<option value="<?=$zoneid?>"><?=$zonename?></option>
				<?php
					}
				?>
				</select>
                         </td>
    					
			<?php
				echo'<td width="24%"><a href=javascript:frm_index.submit()>GO!</a></td>';
                        ?>
                      </tr>

				 </td>
		</tr>
                <tr> 
                        <td  class="loginsmall" align="right" nowrap="nowrap">
				Last Order Date:&nbsp;
			</td>
                        <td colspan="2">
                            <input name="lastorderdate" type="text" size="20" value="<?=$_POST['lastorderdate']?>" id="lastorderdate">&nbsp;
	<input type="button" name="Submit" value="..." id="ButtonRenewalDate" onClick=" Calendar.setup();" />
										<script type="text/javascript">
										Calendar.setup({
										 inputField     :    "lastorderdate",   
										 ifFormat       :    "%Y-%m-%d",  
										 showsTime      :    false,          
										 button         :    "ButtonRenewalDate",  
										 singleClick    :    true,            
										 timeFormat     :    12,
										 step           :    1                 
										});
									</script>
			</td>
    					
			<?php
				echo'<td width="24%"><a href=javascript:frm_index.submit()>GO!</a></td>';
                        ?>
                      </tr>     
					  </form>
              </table>
            </div>
	 </td>
	 <td width="1%">&nbsp;</td>
	 <td>
		<form name="mass_email" action="<?$_SERVER['PHP_SELF']?>?action=mass_email" method="post">
		<table align="center" border="0" style="border: solid #999999 1px;" cellpadding="2" cellspacing="2">
		 <tbody>
		  <tr>
		   <td class="loginsmall" colspan="2" align="center">
<?
//$user_amico_id = $a_amico['amico_id'];
//$user_mtype    = $a_amico['mtype'];
if($user_mtype == 'e'){
?>
			Use this interface to send an email to everyone <br>
			who has purchased products.<br />
			<br />
<?
}else{
?>		
			Use this interface to send an email to all of the <br />
			members of your downline, everyone who has purchased <br />
			products but is not a member of your downline, or both.<br />
			<br />
<?
}
?>
		   </td>
		  </tr>
		   <?

                	if ($action == 'success') {
        	   ?>
                  	<tr>
                   	 <td class="fieldRequired" colspan="2">&nbsp;Success: Mail has been successfully sent.</td>
                  	</tr>
        	   <?

        		}

        	   ?>
		  <tr>
		   <td class="loginsmall" align="right">
			Send Email To:&nbsp;
		   </td>
		   <td>
			<select name="email_to">
			<?if($user_mtype == 'm'){?>
				<option value="downline">My Downline</option>
			<?}?>	
				<option value="other">Customers</option>
			<?if($user_mtype == 'm'){?>
				<option value="both">Both</option>
			<?}?>	
			</select>
		   </td>
		  </tr>
		  <tr>
		   <td align="right" class="loginsmall">
		   	Subject:&nbsp;
		   </td>
		   <td>
			<input type="text" name="subject" size="50">
		   </td>
		  </tr>
		  <tr>
		   <td align="right" class="loginsmall">
			From:&nbsp;
		   </td>
		   <?$res=mysqli_query($conn,"SELECT customers_email_address FROM customers WHERE customers_id='".$_SESSION['ses_member_id']."'");?>
		   <?$row_s=mysqli_fetch_row($res);?>
		   <td>
			<input type="text" size="50" name="from" value="<?=$_SESSION['customers_email_address']?>">
		   </td>
		  </tr>
		  <tr>
		   <td align="right" valign="top" class="loginsmall">
			Message:&nbsp;
		   </td>
		   <td>
			<textarea name="message" cols="50" rows="12">
			</textarea>
		   </td>
		  </tr>
		  <tr>
			 <?='<td align=\"right\"><a href=javascript:document.mass_email.submit()>GO!</a></td>'?>	
		   <td>
			&nbsp;
		   </td>
		  </tr>
		 </tbody>
		</table>
		</form> 		
	 </td>
        </tr>
      <?if($user_mtype == 'm'){?>  
        <tr> 
          <td bgcolor="#999999" colspan="3"> <div align="center"> 
              <table width="100%" border="0" cellspacing="1" cellpadding="2">
                <tr class="copysmallblack"> 
                  <td width="7%" bgcolor="#CBDBBB"><div align="center"><strong>Info</strong></div></td>
                  <td width="24%" height="22" bgcolor="#B7C3D3"> <div align="center"><strong>Last, 
                      First Name</strong></div></td>
                  <td width="14%" bgcolor="#D1D9E3"> <div align="center"><strong>Member 
                      ID</strong></div></td>
                  <td width="20%" bgcolor="#A8D0D1"> <div align="center"><strong>Designation </strong></div></td>
                  <!--<td width="10%" bgcolor="#CBDBBB"> <div align="center"><strong>edit</strong></div></td>-->
                  <td width="10%" bgcolor="#DCE7D1"> <div align="center"><strong>email</strong></div></td>
                  <td width="10%" bgcolor="#A8D0D1"> <div align="center"><strong>orders</strong></div></td>
                  <!--<td width="10%" bgcolor="#D1D9E3"> <div align="center"><strong>delete</strong></div></td>-->
                </tr>
              	<?php
				$rslist=mysqli_query($conn,"select str_member_contact_list from tbl_member_contact_list where int_member_id=$_SESSION['ses_member_id']");
				list($contactlist)=mysqli_fetch_row($rslist);
				$contactlist=substr($contactlist,0,(strlen($contactlist)-1));
				if(trim($contactlist)!=""){
					
					if(trim($_POST['searchname'])!=""){
						$rslist=mysqli_query($conn,"select m.int_member_id, m.amico_id, m.int_designation_id, c.customers_firstname,c.customers_lastname,c.customers_email_address from customers c left outer join tbl_member m on c.customers_id=m.int_customer_id where m.int_member_id in(".$contactlist.") and c.".$_POST['nametype']." like ('".$_POST['searchname']."%') order by c.customers_lastname");
					}
					elseif(trim($_POST['searchcity'])!=""){
						$rslist=mysqli_query($conn,"select m.int_member_id, m.amico_id, m.int_designation_id, c.customers_firstname,c.customers_lastname,c.customers_email_address from customers c left outer join tbl_member m on c.customers_id=m.int_customer_id  left outer join address_book a on c.customers_id=a.customers_id where m.int_member_id in(".$contactlist.") and a.entry_city like('".$_POST['searchcity']."%') and address_book_id=1 order by c.customers_lastname");
					}
					elseif(trim($_POST['searchstate'])!=""){
						$rslist=mysqli_query($conn,"select m.int_member_id, m.amico_id, m.int_designation_id, c.customers_firstname,c.customers_lastname,c.customers_email_address from customers c left outer join tbl_member m on c.customers_id=m.int_customer_id  left outer join address_book a on c.customers_id=a.customers_id where m.int_member_id in(".$contactlist.") and a.entry_zone_id ='".$_POST['searchstate']."' and address_book_id=1 order by c.customers_lastname");
					}
					else{
						$rslist=mysqli_query($conn,"select m.int_member_id, m.amico_id, m.int_designation_id, c.customers_firstname,c.customers_lastname,c.customers_email_address from customers c inner join tbl_member m on c.customers_id=m.int_customer_id where m.int_member_id in(".$contactlist.")  order by c.customers_lastname");
						//echo "elect m.int_member_id, m.amico_id, m.int_designation_id, c.customers_firstname,c.customers_lastname,c.customers_email_address from customers c inner join tbl_member m on c.customers_id=m.int_customer_id where m.int_member_id in(".$contactlist.") ";
					}
				}
				while(list($memberid,$amico_id, $designation,$firstname,$lastname,$email)=mysqli_fetch_row($rslist)){
					if($designation=="1"){
						$designation="I.P.R.";
					}
					elseif($designation=="2"){
						$designation="Team Leader";
					}
					elseif($designation=="3"){
						$designation="Senior Leader";
					}
					elseif($designation=="4"){
						$designation="Master Leader";
					}else {
						$desingation="Director";
					}
					echo'<form  action="contact_organizer.php" name="frm_contact" method="post">';
					echo'<tr bgcolor="#FFFFFF" class="loginsmall">';
					echo('<td><div align="center"><a href="#" onClick="MM_openBrWindow(\'contact_info.php?memberid='.$memberid.'\',\'info\',\'scrollbars=no,width=400,height=550\')"><img src="../images/icons/ico_info.gif" width="18" height="15" border="0"></a>');
				    echo'</div></td>';
				    echo'<td height="25">'.$lastname.', '.$firstname.'</td>';
				    echo'<td>'.$amico_id.'</td>';
				    echo'<td align=center>'.$designation.'</td>';
				    
				    echo'<td width="10%"> <div align="center"><a href="mailto:'.$email.'"><img src="../images/icons/ico_kontakt.gif" width="18" height="16" border="0"></a></div></td>';
				    echo'<td width="10%"> <div align="center"><a href="invoice_report.php?memberid='.$memberid.'"><img src="../images/icons/ico_liste_s.gif" width="22" height="16" border="0"></div></td>';
					echo'</tr>';
					echo'<input type="Hidden" name="memberid" value="'.$memberid.'">';
					echo'</form>';
				}
				?>
			  </table>
            </div>
	 </td>
        </tr>
        
	<?
      } elseif($user_mtype == 'e'){
      	
      if(!$camp){	
      	$camp = "customers_lastname";
      }
//echo "order1 = $order<br>";      
      if(!$order){
      	$order = "desc";
      }elseif ($order == "asc"){
      	$order = "desc";
      }elseif ($order == "desc"){
      	$order = "asc";
      }
      	
//echo "order2 = $order<br>";      
    ?>  
        <tr> 
          <td bgcolor="#999999" colspan="3"> <div align="center"> 
              <table width="100%" border="0" cellspacing="1" cellpadding="2">
                <!--      
                <tr class="copysmallblack"> 
                  <td width="7%" bgcolor="#CBDBBB"><div align="center"><strong>Info</strong></div></td>
                  <td width="24%" height="22" bgcolor="#B7C3D3"> <div align="center"><a href="contact_organizer.php?camp=customers_lastname&order=<?=$order?>"><strong>Last, First Name</strong></div></td>
                  <td width="14%" bgcolor="#D1D9E3"> <div align="center"><a href="contact_organizer.php?camp=amico_id&order=<?=$order?>"><strong>Member ID</strong></div></td>
                  <td width="10%" bgcolor="#DCE7D1"> <div align="center"><strong>Last Order Date</strong></div></td>
                  <td width="10%" bgcolor="#DCE7D1"> <div align="center"><strong>Average sale</strong></div></td>
                  <td width="10%" bgcolor="#DCE7D1"> <div align="center"><strong>Month To Date</strong></div></td>
                  <td width="10%" bgcolor="#DCE7D1"> <div align="center"><strong>Year To Date</strong></div></td>
                
                  <td width="10%" bgcolor="#DCE7D1"> <div align="center"><strong>email</strong></div></td>
                  <td width="10%" bgcolor="#A8D0D1"> <div align="center"><strong>orders</strong></div></td>
                  
                </tr>
      			-->
              	<?php
				$rslist=mysqli_query($conn,"select str_member_contact_list from tbl_member_contact_list where int_member_id=$_SESSION['ses_member_id']");
				list($contactlist)=mysqli_fetch_row($rslist);
				$contactlist=substr($contactlist,0,(strlen($contactlist)-1));
				if(trim($contactlist)!=""){
					
					if(trim($_POST['searchname'])!=""){
						$rslist=mysqli_query($conn,"select m.int_member_id, m.amico_id, m.int_designation_id, c.customers_firstname,c.customers_lastname,c.customers_email_address from customers c left outer join tbl_member m on c.customers_id=m.int_customer_id where m.int_member_id in(".$contactlist.") and c.".$_POST['nametype']." like ('".$_POST['searchname']."%') order by c.customers_lastname");
					}
					elseif(trim($_POST['searchcity'])!=""){
						$rslist=mysqli_query($conn,"select m.int_member_id, m.amico_id, m.int_designation_id, c.customers_firstname,c.customers_lastname,c.customers_email_address from customers c left outer join tbl_member m on c.customers_id=m.int_customer_id  left outer join address_book a on c.customers_id=a.customers_id where m.int_member_id in(".$contactlist.") and a.entry_city like('".$_POST['searchcity']."%') and address_book_id=1 order by c.customers_lastname");
					}
					elseif(trim($_POST['searchstate'])!=""){
						$rslist=mysqli_query($conn,"select m.int_member_id, m.amico_id, m.int_designation_id, c.customers_firstname,c.customers_lastname,c.customers_email_address from customers c left outer join tbl_member m on c.customers_id=m.int_customer_id  left outer join address_book a on c.customers_id=a.customers_id where m.int_member_id in(".$contactlist.") and a.entry_zone_id ='".$_POST['searchstate']."' and address_book_id=1 order by c.customers_lastname");
					}
					else{
						$rslist=mysqli_query($conn,"select m.int_member_id, m.amico_id, m.int_designation_id, c.customers_firstname,c.customers_lastname,c.customers_email_address from customers c inner join tbl_member m on c.customers_id=m.int_customer_id where m.int_member_id in(".$contactlist.")  order by c.customers_lastname");
						//echo "elect m.int_member_id, m.amico_id, m.int_designation_id, c.customers_firstname,c.customers_lastname,c.customers_email_address from customers c inner join tbl_member m on c.customers_id=m.int_customer_id where m.int_member_id in(".$contactlist.") ";
					}
				}
				
//24 incepem 
//$user_amico_id				
$ec_id = $user_amico_id;

//echo "order2,5 = $order<br>";      

      if ($order == "asc"){
      	$order = "desc";
      }elseif ($order == "desc"){
      	$order = "asc";
      }
///echo "order3 = $order<br>";      
//	concat('<a href="#" onClick="MM_openBrWindow("contact_info.php?memberid=', m.int_member_id ,'\',\'info\',\'scrollbars=no,width=400,height=550\')"><img src="../images/icons/ico_info.gif" width="18" height="15" border="0"></a>') as Info 	
	$query = " 
	#SELECT m.int_member_id, m.amico_id, m.int_designation_id, c.customers_firstname,c.customers_lastname,c.customers_email_address 
	SELECT 
	concat('<a href=\"#\" onClick=\"MM_openBrWindow(\'contact_info2.php?memberid=', m.int_member_id ,'\',\'info\',\'scrollbars=yes,width=400,height=550\')\"><img src=\"../images/icons/ico_info.gif\" width=\"18\" height=\"15\" border=\"0\"></a>') as Info ,
	concat('<a href=\"#\" onClick=\"MM_openBrWindow(\'contact_info_extra.php?memberid=', m.int_member_id ,'\',\'info\',\'scrollbars=no,width=400,height=650\')\"><img src=\"../images/icons/g.jpg\" width=\"18\" height=\"15\" border=\"0\"></a>') 	as G ,
	m.amico_id as MemberID, 
	
	concat(c.customers_lastname, ', ', c.customers_firstname) as Name, 	
	
	#concat('<a href=\"#\" onClick=\"MM_openBrWindow(\'add_comments.php?b=add&mlm_id=', m.int_customer_id ,'\',\'comments\',\'scrollbars=yes,width=1000,height=600\')\">C</a>') 	as C ,
	#concat('<a href=\"#\" onClick=\"MM_openBrWindow(\'comments.php?mlm_id=', m.int_customer_id ,'\',\'comments\',\'scrollbars=yes,width=1000,height=600\')\">PC</a>') 	as PC ,
	
	concat('<a href=\"#\">C</a>') as C ,
	concat('<a href=\"#\" onClick=\"MM_openBrWindow(\'add_comments.php?b=&mlm_id=', m.int_customer_id ,'\',\'comments\',\'scrollbars=yes,width=1000,height=600\')\">PC</a>') 	as PC ,
	
	#m.amico_id as _ppp_, 
	#m.amico_id as _lod_, 
	#m.amico_id as _as_, 
	#m.amico_id as _mtd_, 
	#m.amico_id as _ytd_, 

	m.ppp as _ppp_, 
	m.lod as _lod_, 
	m.as as _as_, 
	m.mtd as _mtd_, 
	m.ytd as _ytd_, 
	
	#m.amico_id as email, 
	concat('<a href=\"mailto:', c.customers_email_address, '\"><img src=\"../images/icons/ico_kontakt.gif\" width=\"18\" height=\"16\" border=\"0\"></a>') as email, 
	#m.amico_id as orders  
	concat('<a href=\"invoice_report.php?memberid=', m.int_member_id,'\"><img src=\"../images/icons/ico_liste_s.gif\" width=\"22\" height=\"16\" border=\"0\">') as orders, 

	concat('<a href=\"#\" onClick=\"MM_openBrWindow(\'quick_order.php?memberid=', m.amico_id ,'\',\'info\',\'scrollbars=yes,width=550,height=600\')\"><img src=\"../images/icons/ico_info.gif\" width=\"18\" height=\"15\" border=\"0\"></a>') as QuickOrder
	
	FROM customers c";

if (trim($_POST['lastorderdate'])!=""){
$query.=", bw_invoices o ";  
};

 
$query.=" inner join tbl_member m ON c.customers_id=m.int_customer_id  
	left outer join address_book a on c.customers_id=a.customers_id
	WHERE m.ec_id='$user_amico_id' AND m.bit_active=1	
	"; 

if (trim($_POST['lastorderdate'])!=""){
$query.=" AND m.amico_id=o.ID";  

};        

if ($camp=="_lod_") {
$query.=" AND (m.lod!='0000-00-00' AND m.as!='0') ";
};



//making filter work
if(trim($_POST['searchname'])!=""){
$query.=" and c.".$_POST['nametype']." like ('".$_POST['searchname']."%')";
};

if(trim($_POST['searchcity'])!=""){
$query.=" and a.entry_city like('".$_POST['searchcity']."%') ";
};

if(trim($_POST['searchstate'])!=""){
$query.=" and a.entry_zone_id ='".$_POST['searchstate']."'";
};

if (trim($_POST['lastorderdate'])!=""){
$query.=" and m.lod='".$_POST['lastorderdate']."' ";
};

$query.=" GROUP BY m.amico_id";


	if(eregi("localhost", $_SERVER['SERVER_NAME'])){
		//$query .= "limit 0, 50 ";
	}

//echo'<td width="10%"> <div align="center"><a href="mailto:'.$email.'"><img src="../images/icons/ico_kontakt.gif" width="18" height="16" border="0"></a></div></td>';
//echo'<td width="10%"> <div align="center"><a href="invoice_report.php?memberid='.$memberid.'"><img src="../images/icons/ico_liste_s.gif" width="22" height="16" border="0"></div></td>';
	
	
	//echo nl2br($query); 
	$fields_alias = array("_lod_"=> "LOD", "_as_"=> "AS", "_mtd_"=> "Month<br>To<br>Date", "_ytd_"=> "Year To Date" );
	$fields_alias = array("_ppp_"=> "P", "_lod_"=> "LOD", "_as_"=> "AS", "_mtd_"=> "MTD", "_ytd_"=> "YTD");
	$l2 = 50; 
	query_results_limit($query, $camp_start, $order_start, $order_coresp_array, $fields_alias, $script_name, $l2, $head_title, $no_results_msg);
	
/*	

	$rslist = mysqli_query($conn,$query);
	
				while(list($memberid,$amico_id, $designation,$firstname,$lastname,$email)=mysqli_fetch_row($rslist)){

//Last Order Date	

	$query_lod = " 
	SELECT o.date_purchased  
	FROM orders o 
	inner JOIN customers c on c.customers_id=o.customers_id   
	inner join tbl_member m ON c.customers_id=m.int_customer_id  
	WHERE m.ec_id='$ec_id' AND m.amico_id='$amico_id' 
	order by o.orders_id desc 
	limit 1
	";
	$a_lod = mysqli_fetch_array(mysqli_query($conn,$query_lod));

	if($a_lod[0]){
		$lod = $a_lod[0];
	}else {
		$lod = "N/A";
	}

//Month To Date
//sum of totals of orders created this month 
$query_current_month = "
	SELECT sum(ot.value) as ss  
	FROM orders o 
	inner join orders_total ot on ot.orders_id=o.orders_id and class='ot_total'  
	inner JOIN customers c on c.customers_id=o.customers_id   
	inner join tbl_member m ON c.customers_id=m.int_customer_id  
	WHERE m.ec_id='$ec_id' AND m.amico_id='$amico_id'  and o.date_purchased>='".date("Y")."-".date("m")."-01 00:00:00'
	group by o.orders_id 
	order by o.orders_id desc   
 	";
	$a_sum_current_month =mysqli_fetch_array(mysqli_query($conn,$query_current_month));
	
//Year To Date
//sum of totals of orders created this year  
$query_current_year = "
	SELECT sum(ot.value) as ss  
	FROM orders o 
	inner join orders_total ot on ot.orders_id=o.orders_id and class='ot_total'  
	inner JOIN customers c on c.customers_id=o.customers_id   
	inner join tbl_member m ON c.customers_id=m.int_customer_id  
	WHERE m.ec_id='$ec_id' AND m.amico_id='$amico_id'  and o.date_purchased>='".date("Y")."-01-01 00:00:00'
	group by o.orders_id 
	order by o.orders_id desc   
 	";
	$a_sum_current_year =mysqli_fetch_array(mysqli_query($conn,$query_current_year));

$query_avg_sale  = "
	SELECT count(o.orders_id) as ss  
	FROM orders o 
	inner JOIN customers c on c.customers_id=o.customers_id   
	inner join tbl_member m ON c.customers_id=m.int_customer_id  
	WHERE m.ec_id='$ec_id' AND m.amico_id='$amico_id'  
	group by o.orders_id 
	order by o.orders_id desc   
 	";
	$a_sum_avg_sale =mysqli_fetch_array(mysqli_query($conn,$query_avg_sale));

$query_avg_sale2  = "
	SELECT DISTINCT (date_format( o.date_purchased, '%m/%Y' ) )  as ss  
	FROM orders o 
	inner join orders_total ot on ot.orders_id=o.orders_id and class='ot_total'  
	inner JOIN customers c on c.customers_id=o.customers_id   
	inner join tbl_member m ON c.customers_id=m.int_customer_id  
	WHERE m.ec_id='$ec_id' AND m.amico_id='$amico_id'  
	group by o.orders_id 
	order by o.orders_id desc   
 	";

	$nr2 = mysqli_num_rows(mysqli_query($conn,$query_avg_sale2));
	if($nr2){
		$avg = $a_sum_avg_sale[0]/$nr2;
	} else {
		$avg = 0;
	}					
					echo'<form  action="contact_organizer.php" name="frm_contact" method="post">';
					echo'<tr bgcolor="#FFFFFF" class="loginsmall">';
					echo('<td><div align="center"><a href="#" onClick="MM_openBrWindow(\'contact_info.php?memberid='.$memberid.'\',\'info\',\'scrollbars=no,width=400,height=550\')"><img src="../images/icons/ico_info.gif" width="18" height="15" border="0"></a>');
				    echo'</div></td>';
				    echo'<td height="25">'.$lastname.', '.$firstname.'</td>';
				    echo'<td>'.$amico_id.'</td>';
//				    echo'<td align=center>'.$designation.'</td>';

//                  <td width="10%" bgcolor="#DCE7D1"> <div align="center"><strong>Year To Date</strong></div></td>
				    echo'<td align=center nowrap>'.$lod.'</td>';
				    echo'<td align=center nowrap>'.$avg.'</td>';
				    echo'<td align=center nowrap>'.$a_sum_current_month["ss"].'</td>';
				    echo'<td align=center nowrap>'.$a_sum_current_year["ss"].'</td>';

                  
				    echo'<td width="10%"> <div align="center"><a href="mailto:'.$email.'"><img src="../images/icons/ico_kontakt.gif" width="18" height="16" border="0"></a></div></td>';
				    echo'<td width="10%"> <div align="center"><a href="invoice_report.php?memberid='.$memberid.'"><img src="../images/icons/ico_liste_s.gif" width="22" height="16" border="0"></div></td>';
					echo'</tr>';
					echo'<input type="Hidden" name="memberid" value="'.$memberid.'">';
					echo'</form>';
				}
				?>
			  </table>
            </div>
	 </td>
        </tr>
        
	<?
*/	
      }
	?>        
	
	<tr>
	 <td>
			 <br />

                                 <?php
								 $sql="SELECT int_customer_id FROM tbl_member WHERE amico_id='$_SESSION['ses_member_id']'";
								$res=mysqli_query($conn,$sql) or die(mysql_error());
								 $row=mysqli_fetch_row($res);
                                        $query = "SELECT orders.orders_id,
                                                         orders.customers_name,
                                                         orders.date_purchased,
                                                         orders_products.orders_products_id,
                                                         orders_products.products_id,
                                                         orders_total.value
                                                  FROM orders
                                                  LEFT JOIN orders_products
                                                  ON orders.orders_id=orders_products.orders_id
                                                  LEFT JOIN orders_total
                                                  ON orders.orders_id=orders_total.orders_id
                                                  AND orders_total.class='ot_subtotal'
                                                  WHERE orders.customers_id='0'
                                                  AND orders.int_member_id='$_SESSION['ses_member_id']'";
												  
                                        if ($non_m = mysqli_query($conn,$query)):
                                                if (mysqli_num_rows($non_m) > 0):
                                 ?>

                        <div align="left" class="copysmallblack">Non Member Purchases</div>
                        <table width="100%" border="0" cellspacing="0" cellpadding="5" bgcolor="#999999">
                         <tr>
                          <td>

                                <table width="100%" border="0" cellspacing="1" cellpadding="1">
                                 <tr class="copysmallblack">
                                  <td height="22" bgcolor="#CBDBBB">
                                        <div align="center">
                                        <strong>Name</strong>
                                        </div>
                                  </td>
                                  <td bgcolor="#CFE5E6">
                                        <div align="center">
                                        <strong>Dollar Amount</strong>
                                        </div>
                                  </td>
                                  <td bgcolor="#A8D0D1">
                                        <div align="center">
                                        <strong>Date</strong>
                                        </div>
                                  </td>
                                  <td bgcolor="#D1D9E3">
                                        <div align="center">
                                        <strong>View Order</strong>
                                        </div>
                                  </td>
                                 </tr>

                                 <?php

                                                        while ($nm = mysqli_fetch_object($non_m)) {

                                 ?>

                                 <tr class="loginsmall">
                                  <td bgcolor="#FFFFFF">
                                        <?=$nm->customers_name?>
                                  </td>
                                  <td bgcolor="#FFFFFF" align="center">
$<?=$nm->value?>
                                  </td>
                                  <td bgcolor="#FFFFFF">
                                        <?=$nm->date_purchased?>
                                  </td>
                                  <td bgcolor="#FFFFFF">
                                        <a href="/shop/account_history_info.php?page=1&order_id=<?=$nm->orders_id?>">OrderID: <?=$nm->orders_id?></a>
                                  </td>
                                 </tr>

                                 <?php

                                                        } //End While
                                                        mysql_free_result($non_m);

                                                        echo "</table>";
                                                        echo "</td>";
                                                        echo "</tr>";
                                                        echo "</table>";
                                                        echo "</div>";
                                                endif;
                                        else:
						echo "Error: Final Query on \'contact_organizer.php\' Failed.<br />";
					endif;
                                 ?>

          </td>
        </tr>
      </table>


	</td>
       </tr>
      </table> 
   </td>
  </tr>
</table>
</body>
</html>
