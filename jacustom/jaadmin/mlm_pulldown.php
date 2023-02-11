<?php
$page_title = 'John Amico - Manage MLM';

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

$menus = get_menus();
$link_parents = get_menu_link_parents($menus);

?>

    <div role="main" class="content-body">
        <header class="page-header">
            <h2>Manage MLM</h2>

            <div class="right-wrapper pull-right">
                <!--<ol class="breadcrumbs">
                <li>
                    <a href="<?php /*echo base_admin_url(); */?>">
                        <i class="fa fa-home"></i>
                    </a>
                </li>
                <li><span>Administrator Control Panel</span></li>
            </ol>-->


                <a class="sidebar-right-toggle" ></a>
            </div>
        </header>

        <!-- start: page -->
        <div class="row mlm_manage_wrapper panels_wrapper">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10 mlm_manage centering">
                <div class="panel panel-primary">
                    <div class="panel-heading">MLM Manager</div>
                    <div class="panel-body">
                    <?php
                    if(!empty($menus)) {
                        $user_menus = $menus['admin'];

                        if(!empty($user_menus['mlm_controls']['children']['manage_mlm']['children']) && is_array($user_menus['mlm_controls']['children']['manage_mlm']['children'])) {

                            $items = $user_menus['mlm_controls']['children']['manage_mlm']['children'];

                            $total_items = count($items);
                            $items_per_row = 3;
                            $current_item = $items_placed = 0;
                            $total_item_rows = ceil($total_items / $items_per_row);

                            //debug(false, false, $total_items, $total_item_rows);

                            foreach ($items as $menu_item_key => $menu_item_sub1) {

                                //if($current_item == 0) echo '<div class="row">';
                                ?>

                                <div class="col-md-6 col-lg-4 col-xs-6 text-center item">
                                    <a class="link" href="<?php echo base_admin_url(); ?>/<?php echo $menu_item_sub1['link']; ?>">
                                        <div class="">
                                            <i class="fa <?php echo $menu_item_sub1['icon']; ?> fa-2 icon" aria-hidden="true"></i>
                                            <h5 class="name"><?php echo $menu_item_sub1['name']; ?></h5>
                                        </div>
                                    </a>
                                </div>
                                <?php
                                $current_item++;
                                $items_placed++;
                                //$total_items--;

                                if( ($current_item == 3) || ($total_items == ($items_placed)) ) { $current_item = 0; }

                                if($current_item == 0) {
                                    //$total_item_rows--;
                                    //echo '</div>';
                                }
                            }
                        }
                    }
                    ?>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?php
require_once("templates/footer.php");