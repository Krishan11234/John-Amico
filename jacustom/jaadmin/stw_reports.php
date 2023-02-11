<?php
$page_name = 'STW Reports';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$actions = array('view', 'view_alt');

if( !empty($_GET['action']) && !empty($_GET['id']) ) {
    $action = filter_var($_GET['action'], FILTER_SANITIZE_STRING);
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    //if (in_array($_GET['action'], $actions)) {
    if( !empty($id) ) {
        switch ($action) {
            case "delete":
                $sql = "DELETE FROM stw_data WHERE report_id = '$id'";
                mysqli_query($conn, $sql);

                $sql = "DELETE FROM stw_reports WHERE report_id = '$id'";
                mysqli_query($conn, $sql);



                break;

            case 'view':
            case 'view_alt':
                require_once("page.stw_report_view.php");

                break;
        }
    }
    //}
}

if (!in_array($_GET['action'], $actions)) {
    require_once("templates/header.php");
    require_once("templates/sidebar.php");

    $member_type_name = 'STW Report';
    $member_type_name_plural = 'STW Reports';
    $self_page = 'stw_reports.php';
    $page_url = base_admin_url() . '/stw_reports.php?1=1';


    $limit = 50;
    $page = ((!empty($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1);

    $limit_start = ($page * $limit) - $limit;
    $limit_end = ($page * $limit);


    $conditions = $sortby = array();
    //$designations = !empty($_REQUEST['designations']) ? (!is_numeric($_REQUEST['designations']) ? NULL : $_REQUEST['designations']) : NULL;
    //$sort = ( !empty($_REQUEST['sort']) ?  $_REQUEST['sort'] : '' );
    //$sort = (isset($_REQUEST['sort']) && is_numeric($_REQUEST['sort']) ? $_REQUEST['sort'] : '');
    //$alpabet = (!empty($_REQUEST['alpabet']) ? filter_var($_REQUEST['alpabet'], FILTER_SANITIZE_STRING) : 'A');


    $sql = "SELECT *, DATE_FORMAT(report_time, '%b %D, %Y - %l:%i%p') as date FROM stw_reports ";

    $sortby = '';
    $sortby = "ORDER BY report_time DESC";

    //debug(true, true, $designations, ( in_array($designations, array('', NULL, null, false)) ) );


    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    $sql .= " $sortby ";


    $field_details = array(
        'date' => 'Report Uploaded Date',
        'view' => array(
            'link' => $page_url . '&action=view&id=ID_FIELD_VALUE',
            'id_field' => 'report_id',
            'name' => 'View',
            'newtab' => true,
            'button' => true,
        ),
        'view_alt' => array(
            'link' => $page_url . '&action=view_alt&id=ID_FIELD_VALUE',
            'id_field' => 'report_id',
            'name' => 'View Alt',
            'newtab' => true,
            'button' => true,
        ),
        'export' => array(
            'link' => base_admin_url() . '/excel_export.php?id=ID_FIELD_VALUE',
            'id_field' => 'report_id',
            'name' => 'Export to Excel',
            'button' => true,
        ),
        'delete' => array(
            'link' => "javascript: if(confirm('Are you sure you want to delete this report?')) {location.href='$page_url&action=delete&id=ID_FIELD_VALUE';}",
            'id_field' => 'report_id',
            'name' => 'Delete',
            'button' => true,
            'button_extra_class' => 'btn-danger',
        ),
    );

    $id_field = 'report_id';


    //$query_pag_data = " $condition LIMIT $start, $per_page";
    $data_num_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

    mysqli_store_result($conn);
    $numrows = mysqli_num_rows($data_num_query);

    //echo $sql;

    $sql .= " LIMIT $limit OFFSET $limit_start ";
    //echo $sql;
    $data_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

}


function get_member_title($default_title, $active_members) {
    $output = $default_title;

    if ($active_members >= 12) {
        $output = "Executive Director";
    } else {
        if ($active_members >= 9) {
            $output = "Senior Director";
        } else {
            if ($active_members >= 6) {
                $output = "Director";
            } else {
                if ($active_members >= 3) {
                    $output = "Developing Director";
                } else {
                    if ($active_members >= 1) {
                        $output = "Educator";
                    } else {
                        if ($active_members == 0) {
                            $output = "Member";
                        }
                    }
                }
            }
        }
    }

    return $output;
}


?>

<?php if (!in_array($_GET['action'], $actions)) : ?>

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

        <?php /*if ( !empty($email_sending_success) ) : */?><!--
            <div class="row">
                <section class="panel">
                    <div class="message alert alert-success">
                        Success: Mail has been successfully sent.
                    </div>
                </section>
            </div>
        <?php /*endif; */?>
        <?php /*if ( !empty($error_message) ) : */?>
            <div class="row">
                <section class="panel">
                    <div class="message alert alert-danger">
                        <ul><li><?php /*echo implode('</li><li>', $error_message);*/?></li></ul>
                    </div>
                </section>
            </div>
        --><?php /*endif; */?>
        <div class="row ">
            <section class="panel">
                <div class="col-xs-12 col-lg-10 col-md-10 centering">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center">List of <?php echo $member_type_name_plural; ?></h2>
                    </header>
                    <div class="panel-body">
                        <?php require_once('display_members_data.php'); ?>
                    </div>
                </div>
                <div class="clearfix"></div>
            </section>
        </div>
    </div>


    <?php
    require_once("templates/footer.php");

endif;