<?php
$page_name = 'System Services Information';
$page_title = $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$is_popup = true;

//$display_header = false;
//require_once("templates/header.php");

$member_id = $_SESSION['member']['ses_member_id'];
$session_user = $_SESSION['member']['session_user'];

$memberid_got = !empty($memberid_got) ? $memberid_got : ( !empty($_GET['memberid']) ? filter_var($_GET['memberid'], FILTER_SANITIZE_NUMBER_INT) : 0 );
$memberid = $memberid_got;

if(empty($memberid)) {
    exit;
}

$a_amico = mysqli_fetch_array(mysqli_query($conn,"select amico_id, mtype from tbl_member where int_member_id='$member_id'"));
$user_amico_id = $a_amico['amico_id'];
$user_mtype    = $a_amico['mtype'];


$rsmember=mysqli_query($conn,"select c.customers_id,c.customers_firstname,c.customers_lastname,c.customers_email_address,customers_password,customers_telephone,customers_fax from customers c inner join tbl_member m on c.customers_id=m.int_customer_id where m.int_member_id='$memberid'");
list($customersid,$firstname,$lastname,$email,$pass,$phone,$fax)=mysqli_fetch_row($rsmember);
$customers_id = $customersid;

//echo "$user_amico_id {} $user_mtype {}".$_SESSION['ses_member_id'];

if($user_mtype == "e" || $user_mtype == "m") {
    if ($_POST['goto'] == 'save__cinfo_system_service') {
        $query = "delete from  extra_s_customers where customers_id='$customers_id'";
        mysqli_query($conn, $query);

        foreach ($_POST['field'] as $key => $val) {
            $query = "insert into extra_s_customers set field_id='$key', customers_id='$customers_id', val='$val' ";
            mysqli_query($conn, $query);
        }

        $msg = "The information has been saved!";
    }

    if ($_POST['goto_notes'] == 'save__cinfo_system_service') {

        foreach ($_POST['fields'] as $key => $val) {
            $query = "delete from  extra_s_notes where customers_id='$customers_id' and item_id='{$val['item_id']}'and SKOEInvoice='{$val['SKOEInvoice']}'";
            mysqli_query($conn, $query);

            $query = "insert into extra_s_notes set customers_id='$customers_id', item_id='{$val['item_id']}', SKOEInvoice='{$val['SKOEInvoice']}', notes='{$val['notes']}', date='{$val['date']}'";
            mysqli_query($conn, $query);
        }

        $msg = "The information has been saved!";
    }
}
?>

<!--<div role="main" class="content-body extra_information <?php /*echo ( $is_popup ? 'no-margin-left' : '' ); */?> ">-->
    <div class="row ">
        <div class="col-xs-12 centering">
            <section class="panel">
                <header class="panel-heading">
                    <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                </header>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-12 profile ">
                            <div class="row">
                                <?php if(!empty($msg)): ?>
                                    <div class="message">
                                        <div class="alter alert-success"><?php echo $msg;?></div>
                                    </div>
                                <?php endif;?>

                                <?php
                                $query = "select id, title from extra_service_systems where title is not null and title!='' and item_id = '' order by id ";
                                $q = mysqli_query($conn,$query);
                                $first_protion_loaded = false;

                                if( mysqli_num_rows($q) ) :
                                    $first_protion_loaded = true;
                                ?>
                                    <div class="col-md-4">
                                        <form name="cinfo_system_service" id="cinfo_system_service" action="" method="post">
                                            <section class="panel panel-primary">
                                                <header class="panel-heading"><h2 class="panel-title text-center">Customer Fields</h2></header>
                                                <div class="panel-body">
                                                    <input type="hidden" name="memberid" value="<?=$memberid?>" >
                                                    <input type="hidden" name="goto" value="save__cinfo_system_service">

                                                    <?php
                                                    while ($row = mysqli_fetch_array($q)) :
                                                        $aaa = mysqli_fetch_array(mysqli_query($conn,"select val from extra_s_customers where field_id='".$row['id']."' and customers_id='$customers_id'"));
                                                    ?>
                                                        <div class="form-group">
                                                            <label class="col-xs-4 form-control-label" for=""><?=$row['title']?></label>
                                                            <div class="col-xs-8">
                                                                <input type="text" class="form-control" name="field[<?=$row['id']?>]" value="<?=$aaa['val']?>">
                                                            </div>
                                                        </div>

                                                    <?php endwhile; ?>
                                                </div>
                                                <footer class="panel-footer">
                                                    <div class="row">
                                                        <div class="col-md-12 text-center">
                                                            <button type="Submit" name="submit" value="send_emails" class="command  btn btn-default btn-success mr-lg">Submit</button>
                                                            <button type="reset" class="btn btn-default btn-warning ml-lg">Reset</button>
                                                        </div>
                                                    </div>
                                                </footer>
                                            </section>
                                        </form>
                                    </div>
                                <?php endif; ?>

                                <div class="<?php echo (!empty($first_protion_loaded) ? 'col-md-8' : 'col-md-12') ?>">
                                    <form name="cinfo" id="cinfo" action="" method="post">
                                        <section class="panel panel-primary">
                                            <header class="panel-heading"><h2 class="panel-title text-center">Service Fields</h2></header>
                                            <div class="panel-body">
                                                <input type="hidden" name="memberid" value="<?=$memberid?>" >
                                                <input type="hidden" name="goto_notes" value="save__cinfo_system_service">

                                                <?php if($user_mtype == "e" || $user_mtype == "m") : ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-striped">
                                                            <tr>
                                                                <th>Product Category</th>
                                                                <th>Product</th>
                                                                <th>Date Educated</th>
                                                                <th>Part #</th>
                                                                <th>Description</th>
                                                                <th>Date Last Ordered</th>
                                                                <th>Qty</th>
                                                                <th>Notes</th>
                                                            </tr>

                                                            <?php
                                                            $query = "select * from extra_service_systems where title is not null and title!='' and item_id != '' order by category ASC ";
                                                            $q = mysqli_query($conn,$query);

                                                            $i=0;
                                                            while ($row = mysqli_fetch_array($q)) :
                                                                $line_items_sql = "
                                                                    SELECT bwli.*, bwi.InvoiceDate, bwi.SKOEInvoice as SKOEI, esn.*
                                                                    FROM tbl_member tbm LEFT JOIN bw_invoices bwi ON tbm.amico_id = bwi.ID
                                                                    LEFT JOIN bw_invoice_line_items bwli ON bwi.SKOEInvoice = bwli.FKEntity
                                                                    LEFT JOIN extra_s_notes esn ON esn.SKOEInvoice = bwi.SKOEInvoice
                                                                    WHERE tbm.int_customer_id = '$customers_id' and bwli.ID='{$row['item_id']}' ORDER BY bwi.InvoiceDate DESC LIMIT 0,1
                                                                ";

                                                                //echo '<pre>';echo $line_items_sql;
                                                                //debug(false, false, $row);

                                                                $line_items = mysqli_fetch_array(mysqli_query($conn,$line_items_sql));
                                                                ?>

                                                                <?php if( !empty($line_items['SKOEI']) ) :?>
                                                                    <tr>
                                                                        <td>
                                                                            <input type="hidden" name="fields[<?=$row['id']?>][SKOEInvoice]" value="<?=$line_items['SKOEI'];?>" />
                                                                            <input type="hidden" name="fields[<?=$row['id']?>][item_id]" value="<?=$row['item_id'];?>" />
                                                                            <?php echo $row['category'];?>
                                                                        </td>
                                                                        <td><?php echo $row['title'];?></td>
                                                                        <td>
                                                                            <?php
                                                                            if ( empty($line_items['date']) ) {
                                                                                $class = 'form-control datepicker';
                                                                                $type = 'text';
                                                                                $line_item = '';
                                                                            } else {
                                                                                $class = '';
                                                                                $type = 'hidden';
                                                                                $line_item = $line_items['date'];
                                                                            }

                                                                            echo $line_item;
                                                                            ?>
                                                                            <input type="<?=$type;?>" class="<?=$class;?>" name="fields[<?=$row['id']?>][date]" value="<?=$line_items['date']?>" size="20" >
                                                                        </td>
                                                                        <td><?php echo $row['item_id'];?></td>
                                                                        <td><?php echo $line_items['Description'];?></td>
                                                                        <td><?php echo $line_items['InvoiceDate'];?></td>
                                                                        <td><?php echo $line_items['ShipQty'];?></td>
                                                                        <td>
                                                                            <textarea class="form-control" style="width: 250px;" name="fields[<?=$row['id']?>][notes]"><?=$line_items['notes']?></textarea>
                                                                        </td>
                                                                    </tr>
                                                                <?php else :?>
                                                                    <?php
                                                                    $line_items = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM extra_s_notes esn WHERE customers_id = '$customers_id' and item_id='{$row['item_id']}' and SKOEInvoice = 0 ORDER BY id DESC LIMIT 0,1"));

                                                                    $prod_name = mysqli_fetch_array(mysqli_query($conn,"SELECT pd.products_name FROM products_description pd LEFT JOIN products p ON pd.products_id = p.products_id WHERE p.products_model = '{$row['item_id']}' "));
                                                                    ?>
                                                                    <tr>
                                                                        <td>
                                                                            <input type="hidden" name="fields[<?=$row['id']?>][SKOEInvoice]" value="<?=$line_items['SKOEI'];?>" />
                                                                            <input type="hidden" name="fields[<?=$row['id']?>][item_id]" value="<?=$row['item_id'];?>" />
                                                                            <?php echo $row['category'];?>
                                                                        </td>
                                                                        <td><?php echo $row['title'];?></td>
                                                                        <td>
                                                                            <?php
                                                                            if ( empty($line_items['date']) ) {
                                                                                $class = 'form-control datepicker';
                                                                                $type = 'text';
                                                                                $line_item = '';
                                                                            } else {
                                                                                $class = '';
                                                                                $type = 'hidden';
                                                                                $line_item = $line_items['date'];
                                                                            }

                                                                            echo $line_item;
                                                                            ?>
                                                                            <input type="<?=$type;?>" class="<?=$class;?>" name="fields[<?=$row['id']?>][date]" value="<?=$line_items['date']?>" size="20" >
                                                                        </td>
                                                                        <td><?php echo $row['item_id'];?></td>
                                                                        <td><?php echo $prod_name['products_name'];?></td>
                                                                        <td>N/A</td>
                                                                        <td>N/A</td>
                                                                        <td>
                                                                            <textarea class="form-control" style="width: 250px;" name="fields[<?=$row['id']?>][notes]"><?=$line_items['notes']?></textarea>
                                                                        </td>
                                                                    </tr>

                                                                <?php endif;?>
                                                            <?php endwhile; ?>

                                                        </table>
                                                    </div>
                                                <?php elseif( $user_mtype == 'c' ) :?>
                                                    <?php
                                                    $query = "select title, id from extra_service_systems where title is not null and title!='' order by id ";
                                                    $q = mysqli_query($conn,$query);

                                                    while ($row = mysqli_fetch_array($q)) {
                                                        $aaa = mysqli_fetch_array(mysqli_query($conn,"select val from extra_s_customers where field_id='".$row['id']."' and customers_id='$customers_id'"));
                                                        ?>
                                                        <div class="form-group">
                                                            <label class="col-xs-4 form-control-label" for=""><?=$row['title']?></label>
                                                            <div class="col-xs-8">
                                                                <input type="text" class="form-control" name="field[<?=$row['id']?>]" value="<?=$aaa['val']?>">
                                                            </div>
                                                        </div>
                                                        <?php } ?>
                                                <?php endif;?>
                                            </div>
                                            <footer class="panel-footer">
                                                <div class="row">
                                                    <div class="col-md-12 text-center">
                                                        <?php if($user_mtype == "e" || $user_mtype == "m") : ?>
                                                            <button type="Submit" name="submit" value="send_emails" class="command  btn btn-default btn-success mr-lg">Submit</button>
                                                            <button type="reset" class="btn btn-default btn-warning ml-lg">Reset</button>
                                                        <?php elseif( $user_mtype == 'c' ) :?>
                                                            <button type="button" class="btn btn-default btn-warning ml-lg" onclick="window.close()">Close Window</button>
                                                        <?php endif;?>
                                                    </div>
                                                </div>
                                            </footer>
                                        </section>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>


<?php
//require_once("templates/footer.php");