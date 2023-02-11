<?php
$page_name = 'News';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");



$member_type_name = 'News';
$member_type_name_plural = 'News';
$self_page = 'news.php';


$limit = 50;
$page = ((!empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) ? $_REQUEST['page'] : 1);

$limit_start = ($page * $limit) - $limit;
$limit_end = ($page * $limit);


$conditions = $sortby = array();


//debug(true, false, $status_filter, (!in_array((string)$_REQUEST['params']['status_filter'], array('1','0'), true)), $_POST);

$sql = "select date_FORMAT(str_date, '%M %D, %Y') AS `date`, str_title,str_news,bit_active from tbl_news ";

$sortby = '';
$sortby = "order by str_date desc";

//debug(true, true, $designations, ( in_array($designations, array('', NULL, null, false)) ) );

$conditions[] = "bit_active='1'";

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$sql .= " $sortby ";


$field_details = array(
    'str_date' => 'Date',
    'str_title' => 'Title',
);

$id_field = 'int_news_id';

$action_page__id_handler = 'newsid';


//$query_pag_data = " $condition LIMIT $start, $per_page";
$data_num_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

mysqli_store_result($conn);
$numrows = mysqli_num_rows($data_num_query);

//echo $sql;

$data_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));


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

        <div class="row member_news_wrapper">
            <section class="panel">
                <div class="col-xs-12">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center"><?php echo $page_title; ?></h2>
                    </header>
                    <div class="panel-body">
                        <div class="row">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <?php
                                    if( !empty($numrows) ) :
                                        while($news = mysqli_fetch_object($data_query)) :
                                    ?>
                                            <tr><td class="strong text-center" style="font-size: 14px; background: antiquewhite;"><?php echo $news->date; ?> <?php echo $news->str_title; ?></td></tr>
                                            <tr>
                                                <td>
                                                    <?php
                                                    $content = $news->str_news;

                                                    //$content = str_replace(array('http://www.johnamico.com/UserFiles/', 'http://johnamico.com/UserFiles/', 'https://www.johnamico.com/UserFiles/', 'https://johnamico.com/UserFiles/'), base_url()."/common_files/images/UserFiles/", $content);

                                                    $content = str_replace(array('http://www.johnamico.com', 'http://johnamico.com', 'https://www.johnamico.com', 'https://johnamico.com'), base_url(), $content);

                                                    echo $content;
                                                    ?>
                                                    <div class="m-lg p-lg"></div>
                                                </td>
                                            </tr>

                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </section>
        </div>
    </div>


<?php
require_once("templates/footer.php");
