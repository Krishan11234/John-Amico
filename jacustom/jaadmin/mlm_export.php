<?php
$page_name = 'Export Tasks to CSV';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

//debug(true, true, $_POST);

if( !empty($_POST['submit']) && ( !empty($_POST['goto']) && $_POST['goto'] == 'export' ) ) {
    $start_date = $end_date = '';

    if( validate_date_string($_POST['start_date'], '/') ) {
        $dt = DateTime::createFromFormat("Y/m/d", $_POST['start_date']);
        $start_date = date('Y-m-d', $dt->getTimestamp());
    } else {
        $error_messages['start_date'] = 'Please select a valid date. Format should be: YYYY-MM-DD.';
    }
    if( validate_date_string($_POST['end_date'], '/') ) {
        $dt = DateTime::createFromFormat("Y/m/d", $_POST['end_date']);
        $end_date = date('Y-m-d', $dt->getTimestamp());
    } else {
        $error_messages['end_date'] = 'Please select a valid date. Format should be: YYYY-MM-DD.';
    }
    if( in_array($_POST['type'], array('', 'Error', 'Comment')) ) {
        $type = trim($_POST['type']);
    } else {
        $error_messages['type'] = 'Please select a valid type.';
    }
    if( in_array($_POST['status'], array('', 'Pending', 'Resolved')) ) {
        $status = trim($_POST['status']);
    } else {
        $error_messages['status'] = 'Please select a valid Status.';
    }

    //debug(true, true, $_POST);

    if( $start_date > $end_date ) {
        $error_messages['end_date'] = 'Please select a future date from the "Starting Date".';
    }

    if( empty($error_messages) ) {
        $sql = "
            SELECT me.id, me.date1, me.category, me.type, me.notes, me.status, UNIX_TIMESTAMP(me.date2) as date2, mem.ec_id, mem.amico_id, cus.customers_firstname, cus.customers_lastname
            FROM tbl_mlm_errors me
            INNER JOIN tbl_member mem ON mem.int_customer_id = me.mlm_id
            INNER JOIN customers cus ON mem.int_customer_id = cus.customers_id
        ";

        if( !empty($start_date) ) { $conditions[] = " me.date1 >= '$start_date' "; }
        if( !empty($end_date) ) { $conditions[] = " me.date2 <= '$end_date' "; }
        if( !empty($type) ) { $conditions[] = " me.type = '$type' "; }
        if( !empty($status) ) { $conditions[] = " me.status = '$status' "; }

        if(!empty($conditions)) {
            $sql .= " WHERE ".implode(' AND ', $conditions)." ";
        }

        //debug(true, true, $_POST, $sql);

        $res = mysqli_query($conn,$sql);

        if(mysqli_num_rows($res) < 1) {
            $error_messages['no_results'] = "Sorry! No results found.";
        }

        if(empty($error_messages)) {

            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename="report_export.csv";');

            $f = fopen('php://output', 'w');

            fputcsv($f, array('Member ID', 'Name', 'Comment', 'Category', 'Status', 'Date Entered', 'Type', 'EC Id') );

            $pending = 0;

            while ($row = mysqli_fetch_assoc($res)) {
                $data = array( $row['amico_id'], $row['customers_firstname'] . " " . $row['customers_lastname'], $row['notes'], $row['category'], $row['status'], date('m/d/Y g:ia', strtotime($row['date1'])), $row['type'], $row['ec_id'] );

                fputcsv($f, $data);
            }

            fclose($f);

            exit;
        }

    }

}

require_once("templates/header.php");
require_once("templates/sidebar.php");



?>

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
                <form name="set_password" onSubmit="return checkPass();" class="form-bordered" action="" method="post" enctype="multipart/form-data">
                    <div class="col-xs-12 col-lg-6 col-md-8 centering">
                        <header class="panel-heading">
                            <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                        </header>
                        <?php if(!empty($error_messages)): ?>
                            <div class="messages">
                                <div class="alert alert-danger">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <ul>
                                        <li><?php echo implode('</li><li>', $error_messages); ?></li>
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="panel-body pb-lg pt-lg mb-lg mt-lg">
                            <div class="row form-group <?php echo ( !empty($error_messages['type']) ? 'has-error' : '' ); ?> ">
                                <label class="col-md-4 control-label" for="type">Type</label>
                                <div class="col-md-8">
                                    <select name="type" class="form-control" id="type">
                                        <option value="">All</option>
                                        <option value="Error">Error</option>
                                        <option value="Comment">Comment</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row form-group <?php echo ( !empty($error_messages['status']) ? 'has-error' : '' ); ?> ">
                                <label class="col-md-4 control-label" for="status">Status</label>
                                <div class="col-md-8">
                                    <select name="status" class="form-control" id="status">
                                        <option value="">All</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Resolved">Completed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row form-group <?php echo ( !empty($error_messages['start_date']) ? 'has-error' : '' ); ?> ">
                                <label class="col-md-4 control-label" for="start_date">Date range</label>
                                <div class="col-md-8">
                                    <div class="input-daterange input-group" data-plugin-datepicker="" data-plugin-options="{format:'yyyy/mm/dd'}">
                                        <span class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </span>
                                        <input type="text" class="form-control" name="start_date" id="start_date" required>
                                        <span class="input-group-addon">to</span>
                                        <input type="text" class="form-control" name="end_date" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <footer class="panel-footer text-center">
                            <input type="hidden" name="goto" value="export">
                            <input type="submit" value="Export" name="submit" />
                        </footer>
                    </div>
                </form>
                <div class="clearfix"></div>
            </section>
            <?php if( !empty($logs) ) {
                echo implode('<br/>', $logs);
            } ?>
        </div>
    </div>


<?php
require_once("templates/footer.php");
