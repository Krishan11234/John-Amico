<?php

/**
 * @param integer $member_id
 * @return integer
 */
function get_parent_id($member_id) {
    global $conn;
    $query = db_query("SELECT `int_parent_id` FROM `tbl_member` WHERE `int_member_id`='" . $member_id . "'") or die (mysqli_error($conn));
    $f = mysqli_fetch_array($query);

    return $f['int_parent_id'];
}

/**
 * @param $ec_id
 * @return array
 */
function get_amico($ec_id) {
    global $conn;
    $query = db_query("SELECT * FROM `tbl_member` WHERE `amico_id`='" . $ec_id . "'") or die (mysqli_error($conn));
    return mysqli_fetch_array($query);
}

/**
 * @return bool
 */
function user_is_chapter_president() {
    $user = get_amico($_SESSION['session_user']);

    return ($user['mtype']=='c');
}

/**
 * @param null $user_id
 * @return bool
 */
function user_is_affiliate($user_id=null) {

    if(is_null($user_id)) {
        $user_id = $_SESSION['session_user'];
    }

    $user = get_amico($user_id);

    return ($user['mtype']=='m');
}

/**
 * @return bool
 */
function user_is_education_coordinator() {
    $user = get_amico($_SESSION['session_user']);

    return ($user['mtype']=='e');
}

$level = 2;

function get_parent_level($parent_id, $ec_member_id) {

    global $level;

    $parent_id = get_parent_id($parent_id);
    if ($parent_id == $ec_member_id) {
        $ret = $level;
        $level = 2;
        return $ret;
    } else {
        $level++;
        if($level > 10) {
            $level = 2;
            return 99;
        }
        return get_parent_level($parent_id, $ec_member_id);
    }
}

/**
 * @param $ec_id
 * @param $amigo_id
 * @return integer
 */
function get_level($ec_id, $amigo_id) {
    global $conn;

    $level_to_member = 99;

    $f = get_amico($ec_id);
    $ec_member_id = $f['int_member_id'];

    //level 1
    $query = db_query("SELECT `int_parent_id` FROM `tbl_member` WHERE `amico_id`='" . $amigo_id . "'") or die (mysqli_error($conn));
    $f = mysqli_fetch_array($query);
    $parent_id = $f['int_parent_id'];
    if ($parent_id == $ec_member_id) {
        $level_to_member = 1;
    } else {
        //level2
        $parent_id = get_parent_id($parent_id);
        if ($parent_id == $ec_member_id) {
            $level_to_member = 2;
        } else {
            //level3
            $parent_id = get_parent_id($parent_id);
            if ($parent_id == $ec_member_id) {
                $level_to_member = 3;
            } else {
                //level4
                $parent_id = get_parent_id($parent_id);
                if ($parent_id == $ec_member_id) {
                    $level_to_member = 4;
                } else {
                    //level5
                    $parent_id = get_parent_id($parent_id);
                    if ($parent_id == $ec_member_id) {
                        $level_to_member = 5;
                    } else {
                        //level6
                        $parent_id = get_parent_id($parent_id);
                        if ($parent_id == $ec_member_id) {
                            $level_to_member = 6;
                        } else {
                            //level7
                            $parent_id = get_parent_id($parent_id);
                            if ($parent_id == $ec_member_id) {
                                $level_to_member = 7;
                            } else {
                                //level8
                                $parent_id = get_parent_id($parent_id);
                                if ($parent_id == $ec_member_id) {
                                    $level_to_member = 8;
                                } else {
                                    //level9
                                    $parent_id = get_parent_id($parent_id);
                                    if ($parent_id == $ec_member_id) {
                                        $level_to_member = 9;
                                    } else {
                                        //level10
                                        $parent_id = get_parent_id($parent_id);
                                        if ($parent_id == $ec_member_id) {
                                            $level_to_member = 10;
                                        } else {

                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    return $level_to_member;
}

/**
 * @param integer $amigo_id
 * @param integer $year
 * @param double $ytd
 * @return double
 */
function get_ytd_by_year($amigo_id, $year, $ytd=null) {
    global $conn;

    if( empty($ytd) ) {
        $db_column_name = 'ytd' . $year;

        $order_date1 = $year . '-01-01 00:00:00';
        $order_date2 = ($year + 1) . '-01-01 00:00:00';

        $query_current_year = "SELECT FKEntity, ShipQty, UnitPrice
            FROM bw_invoices, bw_invoice_line_items
            WHERE bw_invoices.ID='$amigo_id' AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity
            AND bw_invoices.OrderDate>='$order_date1' AND bw_invoices.OrderDate<='$order_date2'";

        //if($year == 2015) { debug(true, true, $query_current_year); }

        $query = db_query($query_current_year) or die (mysqli_error($conn));

        $ytd = 0;
        while ($f = mysqli_fetch_array($query)) {
            $ytd = $ytd + $f['ShipQty'] * $f['UnitPrice'];
        }

        db_query("UPDATE `tbl_member` SET $db_column_name='$ytd' WHERE `amico_id`='$amigo_id'") or die (mysqli_error($conn));
    }

    return $ytd;
}

function db_query($sql) {
    global $conn;

    return mysqli_query($conn,$sql);
}
