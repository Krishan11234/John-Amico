<?php

//require_once('../include/global.inc');
global $admin_path;

$menus = get_menus();
$link_parents = get_menu_link_parents($menus);
//echo '<pre>'; print_r($menus); die();

function process_menu_link($link) {
    /*if(!empty($link)) {
        $link = str_replace(array('SHOP_URL'), base_shop_url(), $link);
    }*/
    return $link;
}

?>

<!-- start: sidebar -->
        <aside id="sidebar-left" class="sidebar-left">

            <div class="sidebar-header">
                <div class="sidebar-title">
                    Navigation
                </div>
                <div class="sidebar-toggle hidden-xs" data-toggle-class="sidebar-left-collapsed" data-target="html" data-fire-event="sidebar-left-toggle">
                    <i class="fa fa-bars" aria-label="Toggle sidebar"></i>
                </div>
            </div>

            <div class="nano">
                <div class="nano-content">
                    <nav id="menu" class="nav-main" role="navigation">
                        <ul class="nav nav-main">
                            <?php
                            if(!empty($menus)) {
                                $user_menus = $menus['member'];

                                if(!empty($user_menus)) {
                                    $active_item_link = get_active_item_link();
                                    $active_items = ( !empty($link_parents[$active_item_link]) ? $link_parents[$active_item_link] : array() );

                                    foreach ($user_menus as $menu_items_key => $menu_items) {

                                        //if( in_array($menu_items_key, array('member_media_artist')) && !is_member_a_stylist() ) { continue; }
                                        //if( in_array($menu_items_key, array('member_media_artist')) && is_mediaArtist_enabled() ) { continue; }

                                        //var_dump( in_array($menu_items_key, $active_items),$menu_items_key, $active_items, $active_item_link ); die();
                                        $childHtml = $menuHtml = '';

                                        $has_children = (!empty($menu_items['children']) ? true : false);

                                        $menuHtml .= '<li class="'.( ($has_children) ? ' nav-parent ' : '' ) . ( in_array($menu_items_key, $active_items) ? ' nav-active nav-expanded ' : '' ) .'">';

                                        if($has_children) {
                                            $childHtml .= '<a><i class="'.$menu_items['icon'].'" aria-hidden="true"></i><span>'.$menu_items['name'].'</span></a>';
                                            $childHtml .= '<ul class="nav nav-children">';

                                            if(!empty($menu_items['children']) && is_array($menu_items['children'])) {
                                                foreach ($menu_items['children'] as $menu_item_key => $menu_item_sub1) {
                                                    //echo '<pre>'; print_r($menu_item_sub1); die();

                                                    $has_children = (!empty($menu_item_sub1['children']) ? true : false);
                                                    $childHtml .= '<li class="'.( ($has_children) ? 'nav-parent' : '' ). ( in_array($menu_item_key, $active_items) ? ' nav-active nav-expanded ' : '' ) .'">';

                                                    if($has_children) {
                                                        $childHtml .= '<a><span>'.$menu_items['name'].'</span></a>';
                                                        $childHtml .= '<ul class="nav nav-children">';

                                                        if(!empty($menu_item_sub1['children']) && is_array($menu_item_sub1['children'])) {
                                                            foreach ($menu_item_sub1['children'] as $menu_item_sub1_item_key => $menu_item_sub1_item) {
                                                                $has_children = (!empty($menu_item_sub1_item['children']) ? true : false);

                                                                $childHtml .= '<li class="'.( ($has_children) ? 'nav-parent' : '' ). ( in_array($menu_item_sub1_item_key, $active_items) ? ' nav-active nav-expanded ' : '' ) .'">';
                                                                if(!empty($menu_item_sub1_item['link'])) {
                                                                    $childHtml .= '<a href="'.( !empty($menu_item_sub1_item['stand_alone']) ? process_menu_link($menu_item_sub1_item['link']) : base_member_url().'/'.process_menu_link($menu_item_sub1_item['link']) ).'" '.( !empty($menu_item_sub1_item['newtab']) ? 'target="_blank"' : '' ).' >'.$menu_item_sub1_item['name'].'</a>';
                                                                }
                                                            }
                                                        }

                                                        $childHtml .= '</ul>';
                                                    } else {
                                                        $childHtml .= '<a href="'.( !empty($menu_item_sub1['stand_alone']) ? process_menu_link($menu_item_sub1['link']) : base_member_url().'/'.process_menu_link($menu_item_sub1['link']) ).'" '.( !empty($menu_item_sub1['newtab']) ? 'target="_blank"' : '' ).' >'.$menu_item_sub1['name'].'</a>';
                                                    }
                                                    $childHtml .= '</li>';
                                                }
                                            }
                                            $childHtml .= '</ul>';
                                        } else {
                                            $childHtml .= '<a href="'.( !empty($menu_items['stand_alone']) ? process_menu_link($menu_items['link']) : base_member_url().'/'.process_menu_link($menu_items['link']) ).'" '.( !empty($menu_items['newtab']) ? 'target="_blank"' : '' ).' >';
                                            $childHtml .= '<i class="'.$menu_items['icon'].'" aria-hidden="true"></i><span>'.$menu_items['name'].'</span></a>';
                                        }

                                        $menuHtml .= $childHtml;
                                        $menuHtml .= '</li>';

                                        echo $menuHtml;
                                    }
                                    //echo '<pre>'; print_r($menuHtml); die();
                                }
                            }

                            //echo $menuHtml;

                            ?>

                        </ul>
                    </nav>

                    <hr class="separator" />

                </div>

                <script>
                    // Preserve Scroll Position
                    if (typeof localStorage !== 'undefined') {
                        if (localStorage.getItem('sidebar-left-position') !== null) {
                            var initialPosition = localStorage.getItem('sidebar-left-position'),
                                sidebarLeft = document.querySelector('#sidebar-left .nano-content');

                            sidebarLeft.scrollTop = initialPosition;
                        }
                    }

                    jQuery(document).ready(function($){

                        if( typeof(collapse_left_sidebar) == 'undefined' ) {
                            collapse_left_sidebar = 0;

                            window.addEventListener("resize", function () {
                                //console.log(window, window.outerWidth);
                                if (window.outerWidth <= 1300) {
                                    collapse_left_sidebar = true;
                                    collapse_left_sidebar_func(collapse_left_sidebar);
                                } else {
                                    collapse_left_sidebar = false;
                                    collapse_left_sidebar_func(collapse_left_sidebar);
                                }
                            });
                        }

                        if (window.outerWidth <= 1300) {
                            collapse_left_sidebar_func(true);
                        }

                    });

                    function collapse_left_sidebar_func(collapse_sidebar, always=false) {
                        //console.log(collapse_sidebar, ( typeof window.alwaysHideSidebar == 'undefined'), always);

                        if( typeof window.alwaysHideSidebar == 'undefined') {
                            window.alwaysHideSidebar = always;
                        }

                        if(collapse_sidebar == false) {
                            collapse_sidebar = window.alwaysHideSidebar;
                        }

                        var HTML_HasClass = $('html').hasClass('sidebar-left-collapsed');

                        if( collapse_sidebar ) {
                            if( !HTML_HasClass ) {
                                $('html').addClass('sidebar-left-collapsed');
                            }
                        } else {
                            if( HTML_HasClass ) {
                                $('html').removeClass('sidebar-left-collapsed');
                            }
                        }
                    }


                </script>

            </div>

        </aside>
        <!-- end: sidebar -->