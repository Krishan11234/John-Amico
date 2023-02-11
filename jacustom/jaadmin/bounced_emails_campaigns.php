<?php
$page_name = 'Bounced Emails';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$actions = array('view');

$member_type_name = 'Bounced Email Campaign';
$member_type_name_plural = "{$member_type_name}s";
$self_page = basename(__FILE__);
$page_url = base_admin_url() . "/{$self_page}?1=1";



$limit = 50;
$page = ((!empty($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1);

$limit_start = ($page * $limit) - $limit;
$limit_end = ($page * $limit);
$conditions = $sortby = array();


if (!in_array($_GET['action'], $actions)) {

    $sql = "SELECT *, DATE_FORMAT(created, '%b %D, %Y - %l:%i%p') as date FROM mailchimp_bounced_emails_campaigns ";

    $sortby = '';
    $sortby = "ORDER BY created DESC";

    //debug(true, true, $designations, ( in_array($designations, array('', NULL, null, false)) ) );

    $field_details = array(
        'name' => 'Campaign',
        'date' => 'Report Uploaded Date',
        'view' => array(
            'link' => $page_url . '&action=view&id=ID_FIELD_VALUE',
            'id_field' => 'id',
            'name' => 'View',
            'button' => true,
        ),
        /*'export' => array(
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
        ),*/
    );

    $id_field = 'id';

} else {
    if( !empty($_GET['action']) && !empty($_GET['id']) ) {
        $action = filter_var($_GET['action'], FILTER_SANITIZE_STRING);
        $campaign_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

        if($action == 'view') {

            $campaignNameSql = "SELECT name FROM mailchimp_bounced_emails_campaigns WHERE id='{$campaign_id}'";
            $campaignNameQuery = mysqli_query($conn, $campaignNameSql);
            if(mysqli_num_rows($campaignNameQuery)) {
                $campaignName = mysqli_fetch_assoc($campaignNameQuery);
                $campaignName = $campaignName['name'];

                $member_type_name = 'Bounced Email';
                $member_type_name_plural = "{$member_type_name}s for \"{$campaignName}\"";
                $self_page = basename(__FILE__);
                $page_url = base_admin_url() . "/{$self_page}?1=1&&action=view&id={$campaign_id}";

                $sql = "SELECT * FROM mailchimp_bounced_emails ";

                $conditions[] = " campaign_id='$campaign_id' ";

                $sortby = '';
                $sortby = "ORDER BY email ASC";

                $field_details = array(
                    'email' => 'Email',
                    'firstname' => 'First Name',
                    'lastname' => 'Last Name',
                );
            }
        }
    }
}



if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$sql .= " $sortby ";

//$query_pag_data = " $condition LIMIT $start, $per_page";
$data_num_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

mysqli_store_result($conn);
$numrows = mysqli_num_rows($data_num_query);

//echo $sql; die();

$sql .= " LIMIT $limit OFFSET $limit_start ";
//echo $sql;
$data_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));


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
