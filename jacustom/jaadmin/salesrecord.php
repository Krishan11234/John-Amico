<?php
$page_name = 'Sales Records';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

$is_edit = false;

if( (!empty($_GET['action']) && ($_GET['action'] == 'edit')) && ( !empty($_GET['id']) && is_numeric($_GET['id']) ) ) {
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    $rssalesrecord = mysqli_query($conn,"select *,
                          DATE_FORMAT(dtt_record, \"%m/%d/%Y\") AS recdate
                    from tbl_salesrecord
                    where int_salesrecord_id='{$id}'");
    if( mysqli_num_rows($rssalesrecord) > 0 ) {
        list($rec_id, $mem_id, $dtt, $record, $active, $description, $reward, $recdate) = mysqli_fetch_row($rssalesrecord);
        $is_edit = true;
    }
}

?>

<script language="JavaScript" src="<?php echo base_js_url(); ?>/calendar.js"></script>
<script language="JavaScript">
<!--
function Validate(theform) {
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

    <div class="row">
        <?php if( empty($is_edit) ) : ?>
            <section class="panel">
                <div class="col-md-10 col-sm-12 col-xs-12 centering">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center">Records List</h2>
                    </header>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-stripped mb-none">
                                <tr>
                                    <th>Member ID</th>
                                    <th>Member Name</th>
                                    <th>Record Date</th>
                                    <th>Record</th>
                                    <th>Description</th>
                                    <th>Reward</th>
                                    <th colspan="2" class="text-center">Command</th>
                                </tr>
                                 <?php
                                 $rssalesrecord = mysqli_query($conn,"select *,DATE_FORMAT(dtt_record, \"%m/%d/%Y\") AS recdate from tbl_salesrecord");
                                 $x = 0;
                                 while ($row = mysqli_fetch_assoc($rssalesrecord)):
                                    $tbc = ($x % 2)	? '#EEEEEE' : '#CCCCCC';
                                    $rsmember = mysqli_query($conn,"SELECT m.int_member_id,
                                                    c.customers_firstname,
                                                    c.customers_lastname
                                                 FROM tbl_member m
                                                 LEFT OUTER JOIN customers c
                                                 ON c.customers_id=m.int_customer_id
                                                 WHERE m.int_member_id='{$row['int_member_id']}'");
                                    list($member_id, $firstname, $lastname) = mysqli_fetch_row($rsmember);
                                 ?>
                                    <tr align="center" bgcolor="<?=$tbc;?>">
                                        <td><?=$member_id;?></td>
                                        <td><?=$firstname.' '.$lastname;?></td>
                                        <td><?=$row['recdate'];?></td>
                                        <td><?=$row['int_salesrecord'];?></td>
                                        <td><?=$row['description'];?></td>
                                        <td><?=$row['reward'];?></td>
                                        <td><a class="btn btn-primary" href="salesrecord.php?action=edit&id=<?php echo $row['int_salesrecord_id'];?>">Edit</a></td>
                                        <td><a class="btn btn-primary btn-danger" onclick="return confirmCleanUp('<?php echo base_admin_url(); ?>/act_salesrecord.php?action=delete&id=<?php echo $row['int_salesrecord_id'];?>')" href="#">Delete</a></td>
                                    </tr>
                                 <?php
                                    ++$x;
                                 endwhile;
                                 ?>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
        <section class="panel">
            <div class="col-lg-8 col-md-10 col-sm-12 col-xs-12 centering add_record_wrapper">
                <header class="panel-heading">
                    <h2 class="panel-title text-center"><?php echo ( !empty($is_edit) ? 'Edit' : 'Add New' ); ?> Record</h2>
                </header>
                <div class="panel-body">
                    <div class="table-responsive">
                        <form name="form1" action="act_salesrecord.php" method="post" onSubmit="return Validate(this);">
                            <table class="table table-bordered table-stripped mb-none">
                                <TR>
                                    <TD align="right"><FONT face="Arial" size="2" color="Maroon">Member ID:&nbsp;</FONT></TD>
                                    <TD colspan="2">
                                        <select name="member_id">
                                            <option value="0">None</option>
                                            <?php
                                            $rsmember=mysqli_query($conn,"select m.int_member_id,
                                                          c.customers_firstname,
                                                          c.customers_lastname
                                                    from tbl_member m
                                                    left outer join customers c
                                                    on c.customers_id=m.int_customer_id");
                                            while(list($member_id,$firstname,$lastname)=mysqli_fetch_row($rsmember)){
                                                echo'<option value="'.$member_id.'" '.( (!empty($is_edit) && !empty($mem_id) && ($mem_id == $member_id)) ? 'selected' : '' ).' >'.$firstname.' '.$lastname.'</option>';
                                            }
                                            ?>
                                        </select>
                                    </TD>
                                </TR>
                                <TR>
                                    <TD height="30" align="right"><FONT face="Arial" size="2" color="Maroon"> Record Date:&nbsp;</FONT></TD>
                                    <td>
                                        <input name="recdate" id="recdate" size="17" value="<?php echo ( (!empty($is_edit) && !empty($recdate)) ? $recdate : '' ); ?>">
                                        <input type="reset" value=" ... " onclick="return showCalendar('recdate', 'mm/dd/y');">
                                    </td>
                                </TR>
                                <TR>
                                    <TD height="30" align="right"><FONT face="Arial" size="2" color="Maroon">Record:&nbsp;</FONT></TD>
                                    <TD><INPUT maxLength="100" name="salesrecord" size="17" value="<?php echo ( (!empty($is_edit) && !empty($record)) ? $record : '' ); ?>"></TD>
                                </TR>
                                <TR>
                                    <TD height="30" align="right"><FONT face="Arial" size="2" color="Maroon">Description:&nbsp;</FONT></TD>
                                    <TD><INPUT maxLength="255" name="description" size="40" value="<?php echo ( (!empty($is_edit) && !empty($description)) ? $description : '' ); ?>"></TD>
                                </TR>
                                <TR>
                                    <TD height="30" align="right"><FONT face="Arial" size="2" color="Maroon">Reward:&nbsp;</FONT></TD>
                                    <TD><INPUT maxLength="255" name="reward" size="40" value="<?php echo ( (!empty($is_edit) && !empty($reward)) ? $reward : '' ); ?>"></TD>
                                </TR>
                                <TR>
                                    <TD vAlign="top">&nbsp;</TD>
                                    <TD vAlign="top">
                                        <?php if( !empty($is_edit) && !empty($rec_id) ) {
                                            echo '<input type="hidden" name="rec_id" value="'. $rec_id .'">';
                                        } ?>
                                        <input type="submit" name="<?php echo ( empty($is_edit) ? 'Insert' : 'Update'); ?>" value="<?php echo ( empty($is_edit) ? 'Insert' : 'Update'); ?>" >
                                        <form action="<?php echo base_admin_url(); ?>/salesrecord.php" method="post">
                                            <button type="<?php echo ( empty($is_edit) ? 'reset': 'submit'); ?>" name="Cancel" value="Cancel" class="btn btn-danger">Cancel</button>
                                        </form>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>


<?php
require_once("templates/footer.php");
