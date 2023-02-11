<?php
$page_name = 'Video Education';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");

header("Location: " . base_admin_url().'/videos_new.php');
exit;

require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");



$member_type_name = 'Video';
$member_type_name_plural = 'Videos';
$self_page = 'videos.php';


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
        <div class="col-md-12 col-xs-12 centering">
            <section class="panel">
                <header class="panel-heading">
                    <h2 class="panel-title text-center">List of <?php echo $member_type_name_plural; ?></h2>
                </header>
                <div class="panel-body ">
                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped video_list">
                                <thead>
                                <th class="" width="35%">Title</th>
                                <th class="" width="">Description</th>
                                </thead>
                                <tbody>
                                <?php
                                $sql = "SELECT * FROM video_links ORDER BY video_title";
                                $result2 = mysqli_query($conn,$sql);

                                if(mysqli_num_rows($result2) > 0) {
                                    while($res = mysqli_fetch_array($result2)) {
                                        //debug(true, false, $res);
                                        ?>
                                        <tr>
                                            <td class="text-left title strong" style="font-size: 14px;" id="title-<?php echo $res['video_id']?>"><?php echo $res['video_title']?></td>
                                            <td class="desc" id="desc-<?php echo $res['video_id']?>"><?php echo $res['video_description']?></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="2" class="text-center">There are currently no videos.</td></tr>';
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>


<?php
require_once("templates/footer.php");

