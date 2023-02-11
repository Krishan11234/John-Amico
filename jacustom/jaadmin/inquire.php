<?php
$page_name = 'Share the Wealth Inquirey List';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


$member_type_name = 'Member';
$member_type_name_plural = 'Members';
$self_page = 'inquire.php';
$page_url = base_admin_url() . '/inquire.php?1=1';
$action_page = 'inquire.php';
$action_page_url = base_admin_url() . '/inquire.php?1=1';

/*if ($filter_month=="") {$filter_month=date("m");};
if ($filter_year=="") {$filter_year=date("Y");};*/


$table_headers = array('id'=>'ID', 'date'=>'Date', 'firstname'=>'First Name', 'lastname'=> 'Last Name', 'phone'=> 'Phone', 'email'=>'Email', 'street'=>'Address', 'street2'=>'Address 2', 'city'=>'City', 'state'=>'State', 'zip'=>'Zip Code', 'comment'=>'Comment', 'howdidyouhear'=>'How Did You Hear', 'kindofperson'=>'Kind of Person', 'actions'=>'Action');

$limit = 30;
$page = ((!empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) ? $_REQUEST['page'] : 1);

$limit_start = ($page * $limit) - $limit;
$limit_end = ($page * $limit);


if ($_GET['delete'] && ($_GET['delete']==1) && !empty($_GET['id']) ) {
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    $delsql="DELETE FROM stw_inquire WHERE id = '$id' LIMIT 1";
    $result=mysqli_query($conn,$delsql) or die(mysql_error());
    $delsub=0;

    header("Location: ".pagination_url($page, $self_page));
}

$conditions = $sortby = array();
//$designations = !empty($_REQUEST['designations']) ? (!is_numeric($_REQUEST['designations']) ? NULL : $_REQUEST['designations']) : NULL;


$sql = "SELECT *, DATE_FORMAT(`date`, '%m/%d/%Y') as `date` FROM stw_inquire ";

$sortby = '';
$sortby = "ORDER BY id DESC";

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$sql .= " $sortby ";


$field_details = $table_headers;

$id_field = 'id';


//$query_pag_data = " $condition LIMIT $start, $per_page";
$data_num_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

mysqli_store_result($conn);
$numrows = mysqli_num_rows($data_num_query);

//echo $sql;

$sql .= " LIMIT $limit OFFSET $limit_start ";
//echo $sql;
$data_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));


$no_edit_button = true;
$action_page__id_handler = 'id';

?>

<script>var collapse_left_sidebar=true;</script>

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
            <div class="col-xs-12">
                <header class="panel-heading">
                    <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                </header>
                <div class="panel-body">
                    <!--<div class="row">
                        <div class="col-xs-12 filter_wrapper ">
                            <div class="table-responsive">
                                <div class="col-lg-4 col-md-8 col-sm-10 col-xs-12 centering date_range_wrapper">
                                    <form method="POST">
                                        <table class="table table-bordered table-striped mb-none">
                                            <tr>
                                                <td align="center" colspan="2"><strong>Date Range Selection</strong></td>
                                            </tr>
                                            <tr>
                                                <td>Report Month</td>
                                                <td>
                                                    <select name="filter_month">
                                                        <?php
/*                                                        $months = array('January','February','March','April','May','June','July','August','September','October','November','December');
                                                        $i = 1;
                                                        foreach($months as $month) {
                                                            $selected = ( ($i == $filter_month ) ? 'selected' : '' );
                                                            echo "<option value='$i' $selected>$month</option>";
                                                            $i++;
                                                        }
                                                        */?>
                                                    </select> /
                                                    <select name="filter_year">
                                                        <?php
/*                                                        $start=2006;
                                                        $end=date("Y");

                                                        while ($start<($end+1)) {
                                                            $selected = ( ($start == $filter_year ) ? 'selected' : '' );
                                                            echo '<option value="'.$start.'" '.$selected.'>'.$start.'</option>';
                                                            $start++;
                                                        };

                                                        */?>

                                                    </select>
                                                </td>

                                            </tr>
                                            <tr>
                                                <td align="center" colspan="2">
                                                    <input type="submit" class="mb-xs mt-xs mr-xs btn btn-xs btn-primary" value="Submit">
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>-->
                    <?php require_once('display_members_data.php'); ?>
                </div>
            </div>
            <div class="clearfix"></div>
        </section>
    </div>
</div>


<?php
require_once("templates/footer.php");