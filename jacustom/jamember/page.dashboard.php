<?php

//require_once('../include/global.inc');
global $admin_path;

$menus = get_menus();
$link_parents = get_menu_link_parents($menus);
//echo '<pre>'; print_r($menus); die();
?>

<div role="main" class="content-body">
    <header class="page-header">
        <h2>Professional Panel</h2>

        <div class="right-wrapper pull-right">
            <!--<ol class="breadcrumbs">
                <li>
                    <a href="<?php /*echo base_member_url(); */?>">
                        <i class="fa fa-home"></i>
                    </a>
                </li>
                <li><span>Administrator Control Panel</span></li>
            </ol>-->


            <a class="sidebar-right-toggle" ></a>
        </div>
    </header>

    <!-- start: page -->
    <div class="row panels_wrapper">
        <div class="col-xs-12">
        <?php
        if(!empty($menus)) {
            $user_menus = $menus['member'];

            if (!empty($user_menus)) {
                $active_item_link = get_active_item_link();
                $active_items = (!empty($link_parents[$active_item_link]) ? $link_parents[$active_item_link] : array());

                $total_panels = count($user_menus);
                $panels_per_row = 3;
                $current_panel = $panels_placed = 0;
                $total_panel_rows = ceil($total_panels / $panels_per_row);

                foreach ($user_menus as $menu_items_key => $menu_items) {
                    $childHtml = $menuHtml = '';
                    $panel_has_children = (!empty($menu_items['children']) ? true : false);

                    if($current_panel == 0 && $panel_has_children) echo '<div class="row admin-control">';

                    if( $panel_has_children ) :
                    ?>
                    <div class="col-md-6 col-lg-4 col-xs-12">
                        <div class="panel panel-primary">
                            <div class="panel-heading"><?php echo $menu_items['name']; ?></div>
                            <div class="panel-body text-center centering">

                                <?php
                                if(!empty($menu_items['children']) && is_array($menu_items['children'])) {

                                    $total_items = count($menu_items['children']);
                                    $items_per_row = 3;
                                    $current_item = $items_placed = 0;
                                    $total_item_rows = ceil($total_items / $items_per_row);

                                    //debug(false, false, $total_items, $total_item_rows);

                                    foreach ($menu_items['children'] as $menu_item_key => $menu_item_sub1) {

                                        if($current_item == 0) echo '<div class="row">';
                                        ?>
                                        <div class="col-md-6 col-lg-4 col-xs-6 text-center item">
                                            <a class="link" href="<?php echo ( !empty($menu_item_sub1['stand_alone']) ? process_menu_link($menu_item_sub1['link']) : base_member_url().'/'.process_menu_link($menu_item_sub1['link']) ); ?>" <?php echo ( !empty($menu_item_sub1['newtab']) ? 'target="_blank"' : '' ); ?> >
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
                                            echo '</div>';
                                        }
                                    }
                                }
                                ?>

                            </div>
                        </div>
                    </div>
                    <?php
                        $current_panel++;
                        $panels_placed++;
                        //$total_panels--;

                        if( ($current_panel == 3) || ($total_panels == ($items_placed)) ) { $current_panel = 0; }
                    endif;

                    if($current_panel == 0 && $panel_has_children) {
                        //$total_panel_rows--;
                        echo '</div>';
                    }
                }
            }
        }
        ?>
        </div>
    </div>
</div>