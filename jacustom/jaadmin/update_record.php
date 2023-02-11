<?php
/*  Project Name		: Amico.com
   	Program name		: Admin Email.php
 	Program function	: manage admin emails.
 	Created Date  		: 16 Jun, 2003
 	Last Modified		: 16 Jun, 2003
	Author				: Shehran
	Developed by	    : Colbridge Web Logics - www.colbridge.com 
--------------------------------------------------------------------*/
require_once("session_check.inc");
require_once("../common_files/include/global.inc");

?>
<html>
<head>
<title>Amico - Manage Records</title>
<script language="JavaScript" src="<?php echo base_js_url(); ?>/form_check.js"></script>
<script language="JavaScript" src="<?php echo base_js_url(); ?>/calendar.js"></script>
<script language="JavaScript">
<!--
function Validate(theform)
{
   if(theform.memberid.value<= 0){
	  alert("Please select a Member");
	  theform.memberid.focus();
	  return false;	
	}
   if(!isValidDate(theform.recdate.value)){
	  theform.recdate.focus();
	  return false;	
	}
   if((theform.salesrecord.value)==0||(theform.salesrecord.value=="")){
	  alert("Please enterelect a Sales Record");
	  theform.salesrecord.focus();
	  return false;	
	}
	return true;
}	
//-->
</script>
<link href="../css/calendarstyle.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="#ffffff" BOTTOMMARGIN="0" LEFTMARGIN="0" MARGINHEIGHT="0" MARGINWIDTH="0" RIGHTMARGIN="0" TOPMARGIN="0">
<DIV ALIGN="center">
<table border="0" cellpadding="0" cellspacing="0" width="600">
  <TBODY>	
    <tr>
   		<td align="center" colspan="9"><img name="toplogo" src="../images/logo.gif" border="0">
		</td> 
	</tr>
  </tbody>
</table>
</DIV>

 <?
 $rssalesrecord = mysqli_query($conn,"select *,
				      DATE_FORMAT(dtt_record, \"%m/%d/%Y\") AS recdate 
				from tbl_salesrecord 
				where int_salesrecord_id='$id'");
	list($rec_id, $mem_id, $dtt, $record, $active, $description,$reward,$recdate) = mysqli_fetch_row($rssalesrecord);
 ?>
<DIV ALIGN="center">
<font style="font-size: 16pt; font-weight: bold; font-family: Airal;">Update Record</font><br />
<TABLE BORDER="0" WIDTH="600" CELLPADDING="0" CELLSPACING="0">
<form name="form1" action="act_salesrecord.php" method="post" onSubmit="return Validate(this);">
<input type="hidden" name="rec_id" value="<?=$rec_id;?>">
		  <TR>
		      <TD align="right"><FONT face="Arial" size="2" color="Maroon">Member ID:&nbsp;</FONT></TD>
		      <TD colspan="2">
				<select name="member_id">
			  	<option value="0">None</option>
				<?
				$rsmember=mysqli_query($conn,"select m.int_member_id,
							      c.customers_firstname,
							      c.customers_lastname 
							from tbl_member m 
							left outer join customers c 
							on c.customers_id=m.int_customer_id");
				while(list($member_id,$firstname,$lastname)=mysqli_fetch_row($rsmember)){
					echo'<option value="'.$member_id.'"';
					if ($member_id == $mem_id):
						echo " selected";
					endif;
					echo '>'.$firstname.' '.$lastname.'</option>';
				}
				?>
				</select>
			  </TD></TR>    
		  <TR>
		      <TD height="30" align="right"><FONT face="Arial" size="2" color="Maroon"> Record Date:&nbsp;</FONT></TD>
		      <td>
				<input name="recdate" id="recdate" size="17"i value="<?=$recdate;?>">
			  	<input type="reset" value=" ... " onclick="return showCalendar('recdate', 'mm/dd/y');" style="font-size: 10pt; height: 22; font-family:
				arial; filter:progid:DXImageTransform.Microsoft.Gradient(endColorstr='#ffffff', startColorstr='#CCCCCC', gradientType='1');">
			  </td>
		  </TR>  
		  <TR>
		    <TD height="30" align="right"><FONT face="Arial" size="2" color="Maroon">Record:&nbsp;</FONT></TD>
		    <TD><INPUT maxLength="100" name="salesrecord" size="17" value="<?=$record;?>"></TD>
			</TR> 
		<TR>
                    <TD height="30" align="right"><FONT face="Arial" size="2" color="Maroon">Description:&nbsp;</FONT></TD>
                    <TD><INPUT maxLength="255" name="description" size="40" value="<?=$description;?>"></TD>
                        </TR> 
						<TR>
                    <TD height="30" align="right"><FONT face="Arial" size="2" color="Maroon">Reward:&nbsp;</FONT></TD>
                    <TD><INPUT maxLength="255" name="reward" size="40" value="<?=$reward;?>"></TD>
                        </TR> 
		  <TR>
		  	<TD vAlign="top">&nbsp;</TD>
		    <TD vAlign="top">
			<table>
				<tr>
				    <TD vAlign="top">
						<input type="submit" name="Update" value="Update"
							 style="font-size: 10pt; height: 22; font-family:
							 arial; filter:progid:DXImageTransform.Microsoft.Gradient
						 (endColorstr='#ffffff', startColorstr='#CCCCCC', gradientType='1');">
					 </TD>		
				  <form action="index.php" method="post">
				  	<TD vAlign="top">&nbsp;</TD>
				    <TD vAlign="top">
						<input type="submit" name="Cancel" value="Cancel"
							 style="font-size: 10pt; height: 22; font-family:
							 arial; filter:progid:DXImageTransform.Microsoft.Gradient
							(endColorstr='#ffffff', startColorstr='#CCCCCC', gradientType='1');">
					</TD>
				  </form>			
			  	</tr>
			</td>
		</tr>
	</table>
	</TD>
  </TR>	
</TABLE>
</body>
</html>
