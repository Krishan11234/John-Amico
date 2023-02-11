<?php
$page_title = 'John Amico - Manage Customer';

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

if(isset($_POST['update'])){
	 $table = "customers";
	 $fieldlist="customers_firstname='{$_POST['firstname']}', customers_lastname='{$_POST['lastname']}', customers_email_address='{$_POST['email']}', customers_telephone='{$_POST['phone']}', customers_fax='{$_POST['fax']}', customers_password='{$_POST['password']}' ";

	 $condition=" where customers_id = {$_POST['customerid']}";
	 $result=update_rows($conn, $table, $fieldlist, $condition);
	 $customer_id= $_POST['customerid'];
	 $table = "address_book";
	 $fieldlist="entry_company='{$_POST['company']}', entry_firstname='{$_POST['firstname']}', entry_lastname='{$_POST['lastname']}', entry_street_address='{$_POST['streetadd']}', entry_postcode='{$_POST['postcode']}', entry_city='{$_POST['city']}', entry_country_id='{$_POST['country']}', entry_zone_id='{$_POST['zone']}'";
	 $condition=" where customers_id = {$_POST['customerid']} and address_book_id=1";
	 $result=update_rows($conn, $table, $fieldlist, $condition);
    if($_POST['ismember']==1){
		if($_POST['newmemberid']==""){
			$newmemberid=0;
		}
		else{
			$newmemberid=$_POST['newmemberid'];
		}
		$table = "tbl_member";				// inserting values to setting table
		$in_fieldlist="int_parent_id,int_designation_id,int_customer_id,str_title,dat_last_visit,bit_active";
		$in_values="$newmemberid,1,{$_POST['customerid']},'Mr','$today',1";
		$result=insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values
	}
	
	$rscustomer1=mysqli_query($conn,"select * from tbl_customer_notes where int_customer_id=".$_POST['customerid']);
	$today = date("Y/m/d");
	if(mysqli_num_rows($rscustomer1)>0){
		$table = "tbl_customer_notes";
		$fieldlist="str_comments='".$_POST['comments']."',str_notes='".$_POST['notes']."',dtt_notes='".$today."'";
		$condition=" where int_customer_id = {$_POST['customerid']}";
		$result=update_rows($conn, $table, $fieldlist, $condition);
	}
	else{
		$table = "tbl_customer_notes";				// inserting values to setting table
		$in_fieldlist="int_customer_id,str_comments,str_notes,dtt_notes,bit_active";
		$in_values="{$_POST['customerid']}, '{$_POST['comments']}', '{$_POST['notes']}','$today',1";
		$result=insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values
	}
header("Location: contact_info.php");
}


?>

<script language="JavaScript" src="<?php echo base_js_url(); ?>/form_check.js"></script>
<script language="JavaScript">
<!--

function confirmCleanUp(Link) {
   if (confirm("Are you sure you want to delete this Customer? \n\n(This action cannot be undone)\nThe childs of this member will be put under the parent of this member")) {
      location.href=Link;
   }
}
//ADDED FOR WAIT
function open_wait_window(msg)
{
 var left = (screen.width-500)/2;
 var top = (screen.height-40)/2;

 waitwin = window.open('wait.php?msg='+msg, null, 'width=500, height=40, left='+left+', top='+top);
 
 return;
}
///END
function changevalues(theform)
{
 if (theform.shipping.value !=""){
 	theform.shiped.value = theform.shipping.value;}
 else{
 	theform.shiped.value ="";	
 }
}
function enableid(theform){

	if(theform.ismember.checked){
		theform.newmemberid.disabled=false;
		theform.newmemberid.focus();
	}
	else{
		theform.newmemberid.value="";
		theform.newmemberid.disabled=true;
	}
	
}
function Validate(theform)
{
	if(isEmpty(theform.firstname.value)){
    	alert("Please enter the First Name");
		theform.firstname.focus();
	    return false;
	} 	
	if(isEmpty(theform.lastname.value)){
    	alert("Please enter the Last Name");
		theform.lastname.focus();
	    return false;
	}
	if(isEmpty(theform.email.value)){
    	alert("Please enter the Email Address");
		theform.email.focus();
	    return false;
	} 
	if(theform.email.value.length != 0 ){
	  var retval = emailCheck(theform.email.value)
	  if (retval == false){
		 theform.email.focus();
  		 return false;
	  }	  	
	 }
	if(isEmpty(theform.streetadd.value)){
    	alert("Please enter the Street Address");
		theform.streetadd.focus();
	    return false;
	}
	if(isEmpty(theform.postcode.value)){
    	alert("Please enter the Postcode");
		theform.postcode.focus();
	    return false;
	}  	
	if(isEmpty(theform.city.value)){
    	alert("Please enter the City");
		theform.city.focus();
	    return false;
	} 
	if(theform.zone.value<=0){
    	alert("Please select a State");
		theform.zone.focus();
	    return false;
	} 
	if(theform.country.value<=0){
    	alert("Please select a Country");
		theform.country.focus();
	    return false;
	} 
	if(isEmpty(theform.phone.value)){
    	alert("Please enter the Phone Number");
		theform.phone.focus();
	    return false;
	}
	return true;
}	//-->

</script>


<?php 	

if(isset($_POST['customerid']) and $_POST['customerid'] > 0){
   $rsselcustomer = mysqli_query($conn,"select c.customers_id, c.customers_email_address, c.customers_telephone, c.customers_fax, c.customers_password,n.str_comments,n.str_notes,n.dtt_notes from customers c left outer join tbl_customer_notes n on c.customers_id=n.int_customer_id WHERE c.customers_id = {$_POST['customerid']}");
   list($customerid,$email,$phone,$fax,$password,$comments,$notes,$notesdate)= mysqli_fetch_row($rsselcustomer);
      
   $rsseladdress1 = mysqli_query($conn,"select entry_company, entry_firstname, entry_lastname, entry_street_address, entry_postcode, entry_city, entry_country_id, entry_zone_id from address_book WHERE customers_id = $customerid and address_book_id=1");
   list($company,$firstname,$lastname,$streetadd,$postcode,$city,$country,$zone)= mysqli_fetch_row($rsseladdress1);
   
   $rsseladdress2 = mysqli_query($conn,"select entry_company, entry_firstname, entry_lastname, entry_street_address, entry_postcode, entry_city, entry_country_id, entry_zone_id from address_book WHERE customers_id = $customerid and address_book_id=2");
   list($sh_company, $sh_firstname,$sh_lastname,$sh_streetadd,$sh_postcode,$sh_city,$sh_country,$sh_zone)= mysqli_fetch_row($rsseladdress2);
   $nr = "NO";
}
?>
<div role="main" class="content-body">
    <header class="page-header">
        <h2>Manage Customers</h2>

        <div class="right-wrapper pull-right">
            <ol class="breadcrumbs">
                <li>
                    <a href="<?php echo base_admin_url(); ?>">
                        <i class="fa fa-home"></i>
                    </a>
                </li>
                <li><span>Manage Customers</span></li>
            </ol>


            <a class="sidebar-right-toggle"></a>
        </div>
    </header>

    <div class="row">
        <div class="col-xs-12">
            <div class="panel-body">
                <div class="table-responsive">
                    <table align="center" cellspacing="0" cellpadding="3">
                             <tr id="one">
                              <td align="left">Retrieving Data</td>
                              <td align="right" width="150">Percent Complete: </td>
                              <td width="100"><table width="100%" cellspacing="0" cellpadding="0" border="0" style="border: 1px solid black"><tr><td><table cellspacing="0" cellpadding="0" border="0" bgcolor="green"><tr><td id="cd_meter" width="0" height="12"></td></tr></table></td></tr></table></td>
                              <td><font id='cd_percent'>0%</font></td>
                             </tr>
                             </table>

                    <TABLE BORDER="0" WIDTH="95%" CELLPADDING="0" CELLSPACING="0">

                        <TR>
                        <TD valign="bottom" height="30" colspan="2" ALIGN="center">
                            <FONT face="Arial" SIZE="+1">&nbsp;</FONT>
                        </TD>
                      </TR>
                      <TR>
                        <TD><center>
                        <?php
                            if(!isset($_POST['customerid']) and (!isset($_POST['add']))){
                            ?>


                            <table bgcolor="black" border="0" cellspacing="0" cellpadding="2">
                                        <tr>
                                            <td>
                                                <table width="100%" border="0" cellspacing="1" cellpadding="2">
                                        <tr>
                                            <td bgcolor="#003162" colspan="3">
                                                <font color="white"> <a href="contact_info.php?sort=1" class="linkers">First Name</a>,<a href="contact_info.php?sort=2" class="linkers">Last Name</a></font>
                                            </td>
                                            <td bgcolor="#003162" colspan="3">
                                                <font color="white">Email Address </font>
                                            </td>
                                            <td align="center" bgcolor="#003162">
                                                <font color="white">Command?</font>
                                            </td>
                                        </tr>
                                    <?php
                                    if($_GET['sort']==1) {
                                        $sql = "select m.int_member_id,m.int_designation_id,c.customers_id,c.customers_email_address,c.customers_firstname,c.customers_lastname,m.bit_active,n.str_comments,n.str_notes,n.dtt_notes from customers c inner join tbl_member m on m.int_customer_id=c.customers_id left outer join tbl_customer_notes n on n.int_customer_id=c.customers_id order by c.customers_firstname";
                                    }
                                    if($_GET['sort']==2) {
                                        $sql = "select m.int_member_id,m.int_designation_id,c.customers_id,c.customers_email_address,c.customers_firstname,c.customers_lastname,m.bit_active,n.str_comments,n.str_notes,n.dtt_notes from customers c inner join tbl_member m on m.int_customer_id=c.customers_id left outer join tbl_customer_notes n on n.int_customer_id=c.customers_id order by c.customers_lastname";
                                    } else {
                                        $sql = "select m.int_member_id,m.int_designation_id,c.customers_id,c.customers_email_address,c.customers_firstname,c.customers_lastname,m.bit_active,n.str_comments,n.str_notes,n.dtt_notes from customers c inner join tbl_member m on m.int_customer_id=c.customers_id left outer join tbl_customer_notes n on n.int_customer_id=c.customers_id order by c.customers_firstname";
                                    }
                                    //$sql .= ' LIMIT 0,50';
                                    $rsselallcustomer = mysqli_query($conn,$sql);
                                    $num_rows=mysqli_num_rows($rsselallcustomer);

                                     echo "<script language=\"javascript\">document.getElementById('one').bgColor = '#EEEEEE';</script>\n";
                                     $cd_percent = 0;
                                     $cd_percent_cnt = 0;
                                     $cd_count=$num_rows;
                                    while(list( $memberid, $designation, $customerid, $email, $firstname, $lastname,$active,$comments,$notes,$notesdate) = mysqli_fetch_row($rsselallcustomer)){
                                      $cd_percent_cnt++;
                                      $cd_percent_new = ceil(($cd_percent_cnt/$cd_count)*100);
                                      if($cd_percent_new != $cd_percent)
                                      {
                                       $cd_percent = $cd_percent_new;
                                       echo "<script language=\"javascript\">document.getElementById('cd_percent').innerHTML = '".$cd_percent."%';document.getElementById('cd_meter').width=$cd_percent;</script>\n";
                                       flush();
                                      }
                                    ?>
                                        <tr>
                                            <td bgcolor="silver" colspan="3"><?=$firstname.', '.$lastname?></td>
                                            <td bgcolor="silver" colspan="3">
                                                <?=$email?>
                                            </td>

                                        <form action="contact_info.php" method="post">
                                            <td align="center" bgcolor="silver">
                                                <input type="hidden" name="customerid" value="<?=$customerid?>">
                                                <?php
                                                echo '<input type="submit" name="edit" value=" Edit " class="command">&nbsp;';
                                                ?>
                                            </td>
                                        </form>
                                    </tr>
                                    <?php
                                    }
                                    mysqli_free_result($rsselallcustomer);
                                    ?>
                                </table>
                                </td>
                            </tr>
                        </table>
                            <?php
                            }
                        else{
                            ?>

                        <table border="0" cellspacing="0" cellpadding="0">
                            <form name="theform" action="contact_info.php" method="post" onSubmit="return Validate(this);" enctype="multipart/form-data">
                                <input type ="hidden" name="image_hid" value="<?=$image?>">
                                <?php
                                if($nr == "NO"){ ?>
                                    <input type="hidden" name="customerid" value="<?=$customerid?>">
                                <?php
                                }
                                if(isset($dup)){
                                echo ('<tr>');
                                    echo ('<td colspan="2" height="30" align="center" valign="middle"><font face="Arial, Helvetica" size="2" color="Red">Requested UserName is not available, please re-enter.</font></td>');
                                echo ('</tr>');
                                }
                                echo ('<tr>');
                                    echo ('<td>&nbsp;</td>');
                                    echo ('<td><FONT face="Arial" size="2" color="Maroon">');
                                        if($nr == "NO"){
                                            echo('Edit');
                                            }
                                        echo(' Customer');
                                        ?>	</font>
                                    </td>
                                </tr>
                                <tr>
                                    <td  align="center" colspan="2">&nbsp;</td>
                                </tr>
                                <!--start contact billing info-->
                                <tr>
                                    <td  align="center" colspan="2">&nbsp;</td>
                                </tr>

                                <tr>
                                    <td align="right"><FONT face="Arial" size="2" color="Maroon">First Name:&nbsp;</font></td>
                                    <td>
                                        <input type="Text" name="firstname" size="20" maxlength="20" value="<?=$firstname?>">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right"><FONT face="Arial" size="2" color="Maroon">Last Name:&nbsp;</td>
                                    <td>
                                        <input type="Text" name="lastname" size="20" maxlength="20" value="<?=$lastname?>">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right"><FONT face="Arial" size="2" color="Maroon">Email Address:&nbsp;</font> </td>
                                    <td>
                                        <input type="Text" name="email" size="30" value="<?=$email?>">
                                        </td>
                                </tr>
                                <tr>
                                    <td align="right"><FONT face="Arial" size="2" color="Maroon">Company :&nbsp;</font></td>
                                    <td>
                                        <input type="Text" name="company" size="30" value="<?=$company?>">
                                        </td>
                                </tr>
                                 <tr>
                                    <td align="right"><FONT face="Arial" size="2" color="Maroon">Street Address :&nbsp;</font></td>
                                    <td>
                                        <input type="Text" name="streetadd" size="40" value="<?=$streetadd?>">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right"><FONT face="Arial" size="2" color="Maroon">Post Code :&nbsp;</font></td>
                                    <td>
                                        <input type="Text" name="postcode" size="20" maxlength="20" value="<?=$postcode?>">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right"><FONT face="Arial" size="2" color="Maroon">City :&nbsp;</font></td>
                                    <td>
                                        <input type="Text" name="city" size="20" maxlength="20" value="<?=$city?>">
                                    </td>
                                </tr>
                                 <tr>
                                    <td align="right"><FONT face="Arial" size="2" color="Maroon">State:&nbsp;</font></td>
                                    <td>
                                        <select name="zone">
                                            <option value = 0 >None Selected</option>
                                            <?php
                                            $rs_zone = mysqli_query($conn,"select z.zone_id, z.zone_name from zones z, countries c  where z.zone_country_id=c.countries_id and c.countries_iso_code_3 ='USA' order by zone_name");
                                            while(list($i_zoneid, $s_zone)= mysqli_fetch_row($rs_zone)){
                                                if($i_zoneid==$zone)
                                                    echo '<option value ='.$i_zoneid.' selected>'.$s_zone.'</option>';
                                                else
                                                    echo '<option value ='.$i_zoneid.' >'.$s_zone.'</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right"><FONT face="Arial" size="2" color="Maroon">Country :&nbsp;</font> </td>
                                    <td>
                                        <select name="country">
                                            <option>None Selected</option>
                                            <?php
                                            $rs_country = mysqli_query($conn,"select countries_id,countries_name from countries order by countries_name");
                                            while(list($i_countryid, $s_country)= mysqli_fetch_row($rs_country)){
                                                if($i_countryid==$country)
                                                    echo '<option value ='.$i_countryid.' selected>'.$s_country.'</option>';
                                                else
                                                    echo '<option value ='.$i_countryid.'>'.$s_country.'</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right"><FONT face="Arial" size="2" color="Maroon">Phone Number:&nbsp;</font></td>
                                    <td>
                                        <input type="Text" name="phone" size="40" maxlength="20" value="<?=$phone?>">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right"><FONT face="Arial" size="2" color="Maroon">Fax Number:&nbsp;</font> </td>
                                    <td>
                                        <input type="Text" name="fax" size="40" maxlength="20" value="<?=$fax?>">
                                    </td>
                                </tr>
                                <?
                                if($nr == "NO"){
                                $rsmember=mysqli_query($conn,"select int_parent_id from tbl_member where int_customer_id=".$_POST['customerid']);
                                if(mysqli_num_rows($rsmember)<=0){

                                ?>
                                <tr>
                                    <td align="right"><FONT face="Arial" size="2" color="Maroon">Register as Member:&nbsp;</font> </td>
                                    <td>
                                        <input type="Checkbox" name="ismember" onclick="enableid(theform)" value="1">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right"><FONT face="Arial" size="2" color="Maroon">Refering Member's ID:&nbsp;</font> </td>
                                    <td>
                                        <input type="Text" name="newmemberid" size="10" maxlength="20" disabled>
                                    </td>
                                </tr>
                                <?
                                }
                                else{
                                list($parentid)=mysqli_fetch_row($rsmember);
                                ?>
                                <tr>
                                    <td align="right"><FONT face="Arial" size="2" color="Maroon">Refering Member's ID:&nbsp;</font> </td>
                                    <td>
                                    <?php
                                        if($parentid==0)
                                            echo 'No Parent';
                                        else
                                            echo $parentid;
                                    ?></td>
                                </tr>
                                <?}}?>

                                <tr>
                                    <td align="right" valign="top"><FONT face="Arial" size="2" color="Maroon">Client Comments:&nbsp;</font> </td>
                                    <td>
                                        <textarea name="comments" cols="41" rows="5"><?=$comments?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" valign="top"><FONT face="Arial" size="2" color="Maroon">Notes:&nbsp;</font> </td>
                                    <td>
                                        <textarea name="notes" cols="41" rows="5"><?=$notes?></textarea>
                                    </td>
                                </tr>
                            <!--end of contact billing info-->
                                <tr>
                                    <input type="hidden" name="customerid" value=<?=$customerid?>>
                                    <td align="left" colspan="2">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>
                                    <table border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td>
                                                  <input type="Submit" name="update" value="Update" class="command">
                                                  <input type="hidden" name="isadd" value="0">
                                            </td>

                            </form>
                            <form action="contact_info.php" method="post">
                                            <td>&nbsp;
                                                <input type="Submit" name="cancel" value="Cancel" class="command">
                                            </td>
                            </form>
                                        </tr>
                                      </table>
                                      <?php
                                      }
                                      ?>
                                    </td>
                                </tr>
                    </table>
                    </TD>
                    </TR>
                    </TABLE>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
require_once("templates/footer.php");
