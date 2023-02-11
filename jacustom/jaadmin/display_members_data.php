<?php

require_once("../common_files/include/global.inc");



$no_data = false;

if(!empty($data_query) && !empty($numrows) && !empty($field_details) ) {
    $fields = array_keys($field_details);
} else {
    $no_data = true;
}
/*if(!empty($data_array) && !empty($field_details)) {
    $fields = array_keys($data_array);
} else {
    $no_data = true;
}*/

$total_records = $numrows;
$no_of_paginations = ceil($total_records / $limit);
$cur_page = $page;

if ($cur_page >= 7) {
    $start_loop = $cur_page - 3;
    if ($no_of_paginations > $cur_page + 3)
        $end_loop = $cur_page + 3;
    else if ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6) {
        $start_loop = $no_of_paginations - 6;
        $end_loop = $no_of_paginations;
    } else {
        $end_loop = $no_of_paginations;
    }
} else {
    $start_loop = 1;
    if ($no_of_paginations > 7)
        $end_loop = 7;
    else
        $end_loop = $no_of_paginations;
}

if(empty($action_page__id_handler)) {
    $action_page__id_handler = 'memberid';
}

if( !isset($display_data_from_data_page) ) {
    $display_data_from_data_page = true;
}

if( !isset($displayPagination) ) {
    $displayPagination = true;
}
if( !isset($magento_order_update_page) ) {
    $magento_order_update_page = false;
}

//debug(true, true, $numrows, $data_query, $field_details, $no_of_paginations, $cur_page);


?>

<?php if( $display_data_from_data_page ) :?>
    <div class="row">
        <?php if(!$no_data) : ?>
            <div class="col-xs-12 data_pagination_wrapper">
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-4 pagination_info_wrapper">
                        <?php if($displayPagination) : ?>
                            <div class="dataTables_info" id="datatable-default_info" role="status" aria-live="polite">Showing <?php echo $limit_start+1;?> to <?php echo ( ($limit_end > $total_records ) ? $total_records : $limit_end );?> of total <?php echo $total_records;?> entries</div>
                        <?php else: ?>
                            <div class="dataTables_info" id="datatable-default_info" role="status" aria-live="polite">Showing <?php echo $total_records;?> of total <?php echo $total_records;?> entries</div>
                        <?php endif; ?>
                    </div>
                    <?php if($displayPagination) : ?>
                        <div class="col-xs-12 col-sm-6 col-md-12 col-lg-2 pagination_goto_wrapper">
                            <div class="dataTables_goto" id="datatable-goto">
                                <div class="">
                                    <div class="col-xs-8 no_padding_l ">
                                        <input id="gotopagenumber" type="text" class="form-control" size="3" value="<?php echo ($cur_page > 1) ? $cur_page : ''; ?>" placeholder="Page Number" />
                                    </div>
                                    <div class="col-xs-4 no_padding_l no_padding_r">
                                        <button type="submit" class="btn btn-primary full-width" onclick="var value = Number(document.getElementById('gotopagenumber').value);
                                            if( (value > 0) && (Math.floor(value) == value) ) { window.location.href= ('<?php echo pagination_url('PAGE_NUMBER'); ?>').replace('PAGE_NUMBER', value); } else { alert('Please enter a numeric and positive value'); }">Go</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-12 col-lg-6 pagination_wrapper">
                            <div class="dataTables_paginate paging_bs_normal" id="datatable-ajax_paginate">
                                <ul class="pagination">
                                    <?php
                                        if ($cur_page > 1) {
                                            $pre = $cur_page - 1;

                                    ?>
                                            <li data-p="1" class="first"><a href="<?php echo pagination_url(); ?>"><span class="fa fa-step-backward"></span></a></li>
                                            <li data-p="<?php echo $pre; ?>" class="prev" ><a href="<?php echo pagination_url($pre); ?>"><span class="fa fa-chevron-left"></span></a></li>
                                    <?php } else { ?>
                                            <li class="prev disabled" ><a href="#"><span class="fa fa-chevron-left"></span></a></li>
                                    <?php }?>

                                    <?php
                                    for ($i = $start_loop; $i <= $end_loop; $i++) {

                                        if ($cur_page == $i) {
                                            echo '<li class="active" p="' . $i . '" ><a href="'.pagination_url($i).'">' . $i . '</a></li>';
                                        } else {
                                            //$msg .= "<li p='$i' class='active'>{$i}</li>";
                                            echo '<li class="" p="' . $i . '" ><a href="'.pagination_url($i).'">' . $i . '</a></li>';
                                        }
                                    }
                                    ?>

                                    <?php
                                    if ($cur_page < $no_of_paginations) {
                                        $nex = $cur_page + 1;

                                        ?>
                                        <li data-p="<?php echo $nex; ?>" class="next"><a href="<?php echo pagination_url($nex); ?>"><span class="fa fa-chevron-right"></span></a></li>
                                        <li data-p="<?php echo $no_of_paginations; ?>" class="last"><a href="<?php echo pagination_url($no_of_paginations); ?>"><span class="fa fa-step-forward"></span></a></li>
                                    <?php } else { ?>
                                        <li class="next disabled"><a href="#"><span class="fa fa-chevron-right"></span></a></li>
                                    <?php }?>

                                </ul>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="col-sm-12 col-md-12 col-lg-8"></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-xs-12 data_wrapper">
                <div class="row">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <?php if(!empty($data_query) && !empty($fields)) : ?>
                                <table class="table table-bordered table-striped mb-none">
                                    <?php $th = array(); ?>
                                    <?php foreach($field_details as $field_key => $field_name) {
                                        $classes = (in_array($field_key, array('actions')) ? 'actions_button_cell' : '');
                                        $class = !empty( $classes ) ? $classes : '';

                                        if( is_array($field_name) ) {
                                            $th[$field_key] = '<th'.(!empty($class) ? " class='$class' " : '').'>' . $field_name['name'] . '</th>';
                                        } else {
                                            $th[$field_key] = '<th'.(!empty($class) ? " class='$class' " : '').'>' . $field_name . '</th>';
                                        }
                                    }?>

                                    <?php ob_start(); ?>
                                    <?php $unsetted_fields = array(); ?>
                                    <?php while($res = mysqli_fetch_assoc($data_query)): ?>
                                        <tr>
                                            <?php foreach($fields as $field) {
                                                if( is_array($field_details[$field]) && ( !in_array($field, $unsetted_fields) )  ) {
                                                    ?>
                                                    <td>
                                                        <?php if( $field == 'order_total' ) {?>

                                                            <!--<form method="post" action="<?php /*echo $_SERVER['PHP_SELF'];*/?>">
                                                                <input type="hidden" name="goto" value="update">
                                                                <input type="hidden" name="start_date" value="<?php /*echo $start_date; */?>">
                                                                <input type="hidden" name="end_date" value="<?php /*echo $end_date; */?>">
                                                                <input type="hidden" name="sort" value="<?php /*echo $sort; */?>">
                                                                <input type="hidden" name="page" value="<?php /*echo $page; */?>">
                                                                <input type="hidden" name="<?php /*echo $action_page__id_handler; */?>" value="<?php /*echo $res[$id_field]; */?>">
                                                                <div class="row form-group">
                                                                    <div class="col-lg-7">
                                                                        <input type="text" name="total" class="form-control" value="<?php /*echo number_format($res['order_total'], 2); */?>" size="10">
                                                                    </div>
                                                                    <div class="mb-md hidden-lg hidden-xl"></div>
                                                                    <div class="col-lg-4">
                                                                        <button type="submit" name="submit" value="Update" class="command btn btn-primary btn-warning">Update</button>
                                                                    </div>
                                                                </div>
                                                            </form>-->
                                                        <?php } ?>
                                                        <?php if( ($field_details[$field]['type'] == 'html') ) {?>
                                                            <?php echo print_html($field_details[$field], $field, $res); ?>
                                                        <?php } ?>
                                                        <?php if( empty($field_details[$field]['multi_fields']) ) { ?>
                                                                <?php echo print_text( $field_details[$field], $res ); ?>
                                                        <?php } else { ?>
                                                            <?php
                                                            if( !empty($field_details[$field]['multi_fields']) && is_array($field_details[$field]['multi_fields']) ) {
                                                               foreach($field_details[$field]['multi_fields'] as $multi_field)
                                                               {
                                                                   $break = ( empty($field_details[$field]['inline']) ? '<br/>' : '' );
                                                                   echo print_text( $multi_field, $res ) . $break;
                                                               }
                                                            }
                                                            ?>
                                                        <?php } ?>
                                                    </td>
                                                    <?php
                                                } else {
                                                    if( in_array($field, array('bit_active', 'status')) ) {
                                                        ?>
                                                        <td>
                                                            <form action="<?php echo $action_page; ?>" method="post">
                                                                <input type="hidden" name="alpabet" value="<?php echo $alpabet; ?>">
                                                                <input type="hidden" name="designations" value="<?php echo $designations; ?>">
                                                                <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                                                                <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
                                                                <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
                                                                <input type="hidden" name="<?php echo $action_page__id_handler; ?>" value="<?php echo $res[$id_field]; ?>">
                                                                <input type="hidden" name="page" value="<?php echo $page; ?>">
                                                                <input type="hidden" name="active" value="<?php echo $res[$field]; ?>">
                                                                <input type="hidden" name="mtype" value="<?php echo $mtype; ?>">
                                                                <input type="hidden" name="amico_id_filter" value="<?php echo $amico_id_filter; ?>">
                                                                <?php if( !empty($res[$field]) ) {
                                                                    echo '<input type="submit" name="activate" value="   Active   " class="command success">';
                                                                } else {
                                                                    echo '<input type="submit" name="activate" value="   Deactive   " class="command danger">';
                                                                }
                                                                ?>

                                                            </form>
                                                        </td>
                                                        <?php
                                                    } elseif (in_array($field, array('actions'))) {
                                                        ?>
                                                        <td class="actions_button_cell" style="min-width: 175px;">
                                                        <?php if( $magento_order_update_page ) {?>
                                                            <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" onSubmit="if(!confirm('Are you sure you want to delete this record?')){return false;}">
                                                                <input type="hidden" name="<?php echo $action_page__id_handler; ?>" value="<?php echo $res[$id_field]; ?>">
                                                                <input type="hidden" name="goto" value="delete">
                                                                <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
                                                                <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
                                                                <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                                                                <input type="hidden" name="page" value="<?php echo $page; ?>">

                                                                <input type="hidden" name="amico_id_filter" value="<?php echo $amico_id_filter; ?>">
                                                                <input type="hidden" name="name_filter_first" value="<?php echo $name_filter_first; ?>">
                                                                <input type="hidden" name="name_filter_last" value="<?php echo $name_filter_last; ?>">

                                                                <button type="submit" name="delete" value="Delete" class="command btn btn-primary btn-danger">Delete</button>
                                                            </form>
                                                        <?php } else { ?>
                                                            <?php
                                                            $fieldkeys = array_keys($field_details);
                                                            $input = preg_quote('actions_', '~');
                                                            $matches = preg_grep("~". $input . '~', $fieldkeys);
                                                            $matches_count = count($matches);

                                                            if( $matches_count > 0 ) {
                                                                foreach( $matches as $kesy_key => $field_keys_key ) {
                                                                    $displayThisButton = true;

                                                                    if( is_array($field_details[$field_keys_key]) ) {
                                                                        $field_name = $field_details[$field_keys_key]['action'];
                                                                        $button_type = $field_details[$field_keys_key]['button_type'];

                                                                        if( !empty($field_details[$field_keys_key]['display_condition']) ) {
                                                                            if( isset( $res[ $field_details[$field_keys_key]['display_condition']['field'] ] ) ) {
                                                                                $displayThisButton = ( $res[ $field_details[$field_keys_key]['display_condition']['field'] ] == $field_details[$field_keys_key]['display_condition']['value'] ) ? $field_details[$field_keys_key]['display_condition']['result'] : !$field_details[$field_keys_key]['display_condition']['result'] ;
                                                                            }
                                                                        }

                                                                        //echo '<pre>'; var_dump( $displayThisButton, $res[ $field_details[$field_keys_key]['display_condition']['field'] ], $field_details[$field_keys_key]['display_condition']['value']  ); echo '</pre>';//die();

                                                                    } else {
                                                                        list($field_name, $button_type) = explode('__', $field_details[$field_keys_key]);
                                                                    }

                                                                    if($displayThisButton) :
                                                                    ?>

                                                                    <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" style="display: inline-block;">
                                                                        <input type="hidden" name="<?php echo $action_page__id_handler; ?>" value="<?php echo $res[$id_field]; ?>">
                                                                        <input type="hidden" name="goto" value="<?php echo $field_name; ?>">
                                                                        <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
                                                                        <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
                                                                        <input type="hidden" name="designations" value="<?php echo $designations; ?>">
                                                                        <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                                                                        <input type="hidden" name="page" value="<?php echo $page; ?>">
                                                                        <input type="hidden" name="params[status_filter]" value="<?php echo $status_filter; ?>">
                                                                        <input type="hidden" name="params[page]" value="<?php echo $page; ?>">

                                                                        <input type="hidden" name="amico_id_filter" value="<?php echo $amico_id_filter; ?>">
                                                                        <input type="hidden" name="name_filter_first" value="<?php echo $name_filter_first; ?>">
                                                                        <input type="hidden" name="name_filter_last" value="<?php echo $name_filter_last; ?>">

                                                                        <input type="submit"  name="<?php echo $field_name; ?>" value="<?php echo ucfirst($field_name); ?>" class="command btn <?php echo $button_type; ?>" />
                                                                    </form>
                                                                    <?php
                                                                    endif;

                                                                    //unset($field_details[$field_keys_key]]);
                                                                    //unset($fields[$field_keys_key]]);
                                                                    unset($th[$field_keys_key]);

                                                                    $unsetted_fields[] = $field_keys_key;
                                                                }
                                                                //die();

                                                            }

                                                            ?>

                                                            <form action="<?php echo $self_page; ?>" method="post" <?php echo ( ($matches_count > 0) ? 'style="display:inline-block"' : '' ) ; ?>>
                                                                <?php if(empty($no_edit_button)) : ?>
                                                                    <input type="hidden" name="alpabet" value="<?php echo $alpabet; ?>">
                                                                    <input type="hidden" name="designations" value="<?php echo $designations; ?>">
                                                                    <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                                                                    <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
                                                                    <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
                                                                    <input type="hidden" name="<?php echo $action_page__id_handler; ?>" value="<?php echo $res[$id_field]; ?>">
                                                                    <input type="hidden" name="mtype" value="<?php echo $mtype; ?>">
                                                                    <input type="hidden" name="page" value="<?php echo $page; ?>">
                                                                    <input type="hidden" name="params[status_filter]" value="<?php echo $status_filter; ?>">
                                                                    <input type="hidden" name="params[page]" value="<?php echo $page; ?>">
                                                                    <input type="hidden" name="goto" value="update">
                                                                    <input type="submit" name="edit" value=" Edit " class="command">&nbsp;
                                                                <?php endif; ?>
                                                                <?php if(empty($no_delete_butotn)) : ?>
                                                                    <input type="button" name="delete" value="Delete" class="danger" onclick="return confirmCleanUp('<?php echo pagination_url($page, base_admin_url() ."/$action_page?$action_page__id_handler=$res[$id_field]&delete=1&goto=delete"); ?>')">
                                                                <?php endif; ?>
                                                                <input class="<?php echo isset($res['subscribed']) && $res['subscribed'] == 0 ? 'danger' : 'command success'; ?>" value="Emails" type="button">
                                                            </form>
                                                        <?php } ?>
                                                        </td>
                                                        <?php
                                                    } else {
                                                        //if( isset($res[$field]) ) {

                                                        //echo '<pre>'; var_dump( !in_array($field, $unsetted_fields), $field, $unsetted_fields  ); echo '</pre>'; //die();

                                                        if( !in_array($field, $unsetted_fields) ) {
                                                            echo '<td>' . $res[$field] . '</td>';
                                                        }
                                                    }
                                                }
                                            }?>
                                        </tr>
                                    <?php endwhile; ?>
                                    <?php $tbody = ob_get_contents(); ?>
                                    <?php ob_end_clean(); ?>

                                    <thead><tr><?php echo implode('', $th); ?></tr></thead>
                                    <tbody><?php echo $tbody; ?></tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="col-xs-12">
                <span>No Data Found!</span>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php


function print_html($filedDetails, $fieldKey, $res) {
    $output = '';

    if( empty($filedDetails['html']) ) {
        return $output;
    }

    if( !empty($filedDetails['prefix']) ) {
        $output .= $filedDetails['prefix'];
    }
    $output .= $filedDetails['html'];
    if( !empty($filedDetails['prefix']) ) {
        $output .= $filedDetails['suffix'];
    }

    if( !empty($res) && !empty($filedDetails['id_field']) ) {
        if( is_array($filedDetails['id_field']) ) {
            foreach($filedDetails['id_field'] as $idFieldReplaceKey => $idField) {
                if( isset($res[$idField]) ) {
                    $output = str_replace($idFieldReplaceKey, $res[$idField], $output);
                } else {
                    $output = str_replace($idFieldReplaceKey, '', $output);
                }
            }
        }
    }

    return $output;
}

function print_text($field, $res) {

    $output = '';

    if(!empty($field['field_type'])) {
        switch ($field['field_type']) {
            case 'text_from_callback':
                $text = text_from_callback__display_functions($field, $res);
                break;
        }
    } else {
        if( !empty($field['text_from_callback']) ) {
            $text = text_from_callback__display_functions($field['text_from_callback'], $res);
        }
    }

    if( !empty($field['link_from_callback']) ) {
        $link = link_from_callback__display_functions($field['link_from_callback'], $res);
    }

    if( !empty($field['name']) || !empty($text) ) {
        if( !empty($field['link']) ) {
            /*debug(true, false, $field['attributes']); */

            $forward = true;
            if(!empty( $field['check_function']['function'] ) ) {
                $check_funciton  = $field['check_function'];

                $params = ( !empty($check_funciton['params']) && is_array($check_funciton['params']) ) ? $check_funciton['params'] : array();
                if(!empty($params) ) {
                    foreach($params as $k=>$param) {
                        if( !empty($check_funciton['params_field'][$k]) ) {
                            $param_field = $check_funciton['params_field'][$k];

                            $params[$k] = str_replace($param_field, $res[ $param_field ], $param);
                        }
                    }
                }

                $forward = call_user_func_array($check_funciton['function'], $params);
            }

            if($forward) {

                $linkLink = (!empty($field['link']) ?
                    ((!empty($field['id_field']) && isset($res[$field['id_field']])) ? str_replace('ID_FIELD_VALUE', $res[$field['id_field']], $field['link']) : $field['link'])
                    : '#');
                $anchorLink = ( !empty($link) ) ? $link : $linkLink;

                $output .= "<a";
                $output .= " href=\"$anchorLink\" ";
                $output .= "
                    class=\"". (!empty($field['button']) ? 'btn btn-primary' : '')." ".(!empty($field['button_extra_class']) ? $field['button_extra_class'] : '')."\"
                    ".(!empty($field['newtab']) ? 'target="_blank"' : '');


                $attributes = '';
                if (!empty($field['attributes']) && is_array($field['attributes'])) {
                    foreach ($field['attributes'] as $attr_key => $attr_val) {
                        $attributes .= " $attr_key=\"$attr_val\" ";
                    }
                }
                $attributes = str_replace('ID_FIELD_VALUE', $res[$field['id_field']], $attributes);
                //debug(true, false, $attributes);
                $output .= $attributes;
                $output .= ' >';

                if (!empty($field['text_to_display'])) {
                    if (!empty($field['id_field']) && isset($res[$field['id_field']])) {
                        $linkContent = str_replace('ID_FIELD_VALUE', $res[$field['id_field']], $field['text_to_display']);
                    }
                    else {
                        $linkContent = $field['text_to_display'];
                    }

                    if (!empty($field['text_field']) && isset($res[$field['text_field']])) {
                        $linkContent = str_replace('TEXT_FIELD_VALUE', $res[$field['text_field']], $linkContent);
                    }
                }
                else {
                    $linkContent = !empty($text) ? $text : $field['name'];
                }

                $output .= $linkContent;
                $output .= '</a>';

             }

        } else {
            $output = $text;
        }
    }

    //echo '<pre>'; var_dump( $field, $res, $output ); die();

    return $output;
}

function text_from_callback__display_functions($field=null, $res=null) {
    $text = '';

    if( !empty($field['value']) && is_array($field['value']) ) {
        $value = $field['value'];
        $forward = true;

        if(!empty( $value['check_function'] ) ) {
            $params = ( !empty($value['check_function_params']) && is_array($value['check_function_params']) ) ? $value['check_function_params'] : array();
            if(!empty($params) ) {
                foreach($params as $k=>$param) {
                    if( !empty($value['check_function_params_field'][$k]) ) {
                        $param_field = $value['check_function_params_field'][$k];

                        $params[$k] = str_replace($param_field, $res[ $param_field ], $param);
                    }
                }
            }

            $forward = call_user_func_array($value['function'], $params);
        }

        if($forward) {
            if (!empty($value['function'])) {
                $params = (!empty($value['params']) && is_array($value['params'])) ? $value['params'] : array();
                if (!empty($params)) {
                    foreach ($params as $k => $param) {
                        if (!empty($value['params_field'][$k])) {
                            $param_field = $value['params_field'][$k];

                            $params[$k] = str_replace($param_field, $res[$param_field], $param);
                        }
                    }
                }

                $text .= call_user_func_array($value['function'], $params);

                if(!empty($field['value']['prefix'])) {
                    $text = $field['value']['prefix'] . $text;
                }
                if(!empty($field['value']['suffix'])) {
                    $text = $text . $field['value']['suffix'];
                }
            }
        }
    }

    //echo '<pre>'; var_dump( $field, $res, $text ); die();

    return $text;
}

function link_from_callback__display_functions($field=null, $res=null) {
    $link = '';

    if( !empty($field['link_value']) && is_array($field['link_value']) ) {
        $value = $field['link_value'];
        $forward = true;

        if(!empty( $value['check_function'] ) ) {
            $params = ( !empty($value['check_function_params']) && is_array($value['check_function_params']) ) ? $value['check_function_params'] : array();
            if(!empty($params) ) {
                foreach($params as $k=>$param) {
                    if( !empty($value['check_function_params_field'][$k]) ) {
                        $param_field = $value['check_function_params_field'][$k];

                        $params[$k] = str_replace($param_field, $res[ $param_field ], $param);
                    }
                }
            }

            $forward = call_user_func_array($value['function'], $params);
        }

        if($forward) {
            if (!empty($value['function'])) {
                $params = (!empty($value['params']) && is_array($value['params'])) ? $value['params'] : array();
                if (!empty($params)) {
                    foreach ($params as $k => $param) {
                        if (!empty($value['params_field'][$k])) {
                            $param_field = $value['params_field'][$k];

                            $params[$k] = str_replace($param_field, $res[$param_field], $param);
                        }
                    }
                }

                $link .= call_user_func_array($value['function'], $params);

                if(!empty($field['value']['prefix'])) {
                    $text = $field['value']['prefix'] . $text;
                }
                if(!empty($field['value']['suffix'])) {
                    $text = $text . $field['value']['suffix'];
                }
            }
        }
    }

    return $link;
}