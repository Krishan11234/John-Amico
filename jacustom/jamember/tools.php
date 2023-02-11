<?php
$page_name = 'Business Building Tools';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


$member_type_name = 'Tool';
$member_type_name_plural = 'Tools';
$self_page = 'tools.php';


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
                    <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                </header>
                <div class="panel-body ">
                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped text-center">
                                <thead>
                                <th class="" width="25%">Name</th>
                                <th class="" width="">Description</th>
                                </thead>
                                <tbody>
                                <?php
                                $sql = "SELECT * FROM tool_links ORDER BY tool_title";
                                $result2 = mysqli_query($conn,$sql);

                                if(mysqli_num_rows($result2) > 0) {
                                    while($res = mysqli_fetch_array($result2)) {
                                        ?>
                                        <tr>
                                            <td class="text-left title" id="title-<?php echo $res['tool_id']?>">
                                                <a href="<?php echo $res['tool_link']; ?>" target="_blank" class="link" id="link-<?php echo $res['tool_id']?>"><?php echo $res['tool_title']?></a>
                                            </td>
                                            <td class="text-left desc" id="desc-<?php echo $res['tool_id']?>"><?php echo $res['tool_description']?></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="2" class="text-center">There are currently no tool links.</td></tr>';
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

