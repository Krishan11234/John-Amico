<?php

require_once(dirname(__FILE__) . "/../common_files/include/global.inc");

function found_chapter($zip_code) {
    global $conn;

    $add = '';
    $query_zip = mysqli_query($conn, "SELECT * FROM zipdata WHERE zipcode='$zip_code'");
    if (mysqli_num_rows($query_zip) == 0) {
        return ('Zip not found!');
    }
    else {
        $f = mysqli_fetch_array($query_zip);
        $lat = $f["lat"];
        $lon = $f["lon"];

        $add .= " AND (POW((69.1*(lon-\"$lon\")*cos($lat/57.3)),\"2\")+POW((69.1*(lat-\"$lat\")),\"2\"))<(tbl_member.miles*tbl_member.miles)";
        $query = mysqli_query($conn, "SELECT * FROM tbl_member, address_book, zipdata WHERE tbl_member.mtype='c' AND tbl_member.int_customer_id=address_book.customers_id AND address_book.entry_postcode=zipdata.zipcode " . $add . " ORDER BY (address_book.entry_postcode-$zip_code)") or die (mysql_error());

        if (mysqli_num_rows($query) > 0) {
            $f = mysqli_fetch_array($query);
            return '#' . $f['amico_id'];
        }
        else {
            $query = mysqli_query($conn, "SELECT * FROM tbl_member, address_book WHERE tbl_member.mtype='c' AND tbl_member.int_customer_id=address_book.customers_id ORDER BY (entry_postcode-" . $zip_code . ") asc") or die (mysql_error());
            $f = mysqli_fetch_array($query);
            $id = $f['amico_id'];

            return 'Not found! Nearest is #' . $id . ', they are located within ' . distance($zip_code, $f['entry_postcode']) . ' miles.';
        };
    };
}

function distance($zipOne, $zipTwo) {
    global $conn;

    $query = mysqli_query($conn, "SELECT * FROM zipdata WHERE zipcode = '$zipOne'");
    $f = mysqli_fetch_array($query);
    $lat1 = $f["lat"];
    $lon1 = $f["lon"];

    $query = mysqli_query($conn, "SELECT * FROM zipdata WHERE zipcode = '$zipTwo'");
    $f = mysqli_fetch_array($query);
    $lat2 = $f["lat"];
    $lon2 = $f["lon"];

    $lat1 = $lat1 * M_PI / 180.0;
    $lon1 = $lon1 * M_PI / 180.0;
    $lat2 = $lat2 * M_PI / 180.0;
    $lon2 = $lon2 * M_PI / 180.0;

    $delta_lat = $lat2 - $lat1;
    $delta_lon = $lon2 - $lon1;

    $temp = pow(sin($delta_lat / 2.0), 2) + cos($lat1) * cos($lat2) * pow(sin($delta_lon / 2.0), 2);

    $EARTH_RADIUS = 3956;
    $distance = $EARTH_RADIUS * 2 * atan2(sqrt($temp), sqrt(1 - $temp));

    return round($distance, 2);

}


function members_to_chapter($customer_id) {
    global $conn;

    $query = mysqli_query($conn, "SELECT * FROM address_book WHERE customers_id='$customer_id' AND address_book_id='1'") or die (mysql_error());
    $f = mysqli_fetch_array($query);
    $zip_code = $f['entry_postcode'];

    $query = mysqli_query($conn, "SELECT * FROM tbl_member WHERE int_customer_id='$customer_id'") or die (mysql_error());
    $f = mysqli_fetch_array($query);
    $miles = $f['miles'];
    $chapter_id = $f['amico_id'];

    if ($f['mtype'] == 'c') {

        $query_zip = mysqli_query($conn, "SELECT * FROM zipdata WHERE zipcode='$zip_code'");

        //echo '<pre>'; var_dump(mysqli_num_rows($query_zip), $zip_code, "SELECT * FROM address_book WHERE customers_id='$customer_id' AND address_book_id='1'"); die();

        if (mysqli_num_rows($query_zip) == 0) {
            return ('Zip not found!');
        }
        else {
            $f = mysqli_fetch_array($query_zip);
            $lat = $f["lat"];
            $lon = $f["lon"];

            $add .= " AND (POW((69.1*(lon-\"$lon\")*cos($lat/57.3)),\"2\")+POW((69.1*(lat-\"$lat\")),\"2\"))<($miles*$miles)";
            $query = mysqli_query($conn, "SELECT amico_id FROM tbl_member, address_book, zipdata WHERE tbl_member.mtype='m' AND tbl_member.int_customer_id=address_book.customers_id AND address_book.entry_postcode=zipdata.zipcode " . $add . " GROUP BY amico_id") or die (mysql_error());

//echo "SELECT * FROM tbl_member, address_book, zipdata WHERE tbl_member.mtype='m' AND tbl_member.int_customer_id=address_book.customers_id AND address_book.entry_postcode=zipdata.zipcode ".$add." GROUP BY amico_id";


            $res = mysqli_query($conn, "UPDATE tbl_member SET chapter_id='' WHERE chapter_id='" . $chapter_id . "'") or die (mysql_error());

            while ($f = mysqli_fetch_array($query)) {
                $res = mysqli_query($conn, "UPDATE tbl_member SET chapter_id='$chapter_id' WHERE amico_id='" . $f['amico_id'] . "'") or die (mysql_error());
            }
        }
    }
}

function get_next_amico_id($mtype='') {
    global $conn;

    $prefix = 'W';
    if($mtype == 'a') {
        $prefix = 'A';
    }

    $sql2="SELECT amico_id FROM tbl_member WHERE amico_id LIKE '{$prefix}%' ";
    if( !empty($mtype) ) {
        $sql2 .= " AND mtype='$mtype' ";
    }
    $sql2 .= " order by amico_id DESC limit 0,1 ";

    $res=mysqli_query($conn,$sql2) or die(mysql_error());
    $row=mysqli_fetch_row($res);
    $string=explode("{$prefix}0",$row[0]);

    if( !empty($mtype) ) {
        $amicoid="{$prefix}0".($string[1]+1);
    } else {
        $amicoid = $string[1]+1;
    }

    return $amicoid;
}

function validate_zip_code($zip_code) {
    global $conn;

    $valid = false;

    if( !empty($zip_code) ) {
        $sql = "SELECT zipcode FROM zipdata WHERE zipcode='$zip_code'";
        $query = mysqli_query($conn, $sql);

        if( mysqli_num_rows($query) > 0 ) {
            $valid = mysqli_fetch_object($query);
            $valid = $valid->zipcode;
        }
    }

    return $valid;
}
function validate_amico_member($amico_member_id) {
    global $conn;

    $valid = false;

    if( !empty($amico_member_id) ) {
        $sql = "SELECT int_member_id FROM tbl_member WHERE amico_id='$amico_member_id'";
        $query = mysqli_query($conn, $sql);

        if( mysqli_num_rows($query) > 0 ) {
            $valid = mysqli_fetch_object($query);
            $valid = $valid->int_member_id;
        }
    }

    return $valid;
}
function validate_memberid($member_id) {
    global $conn;

    $valid = false;

    if( !empty($member_id) ) {
        $sql = "SELECT int_member_id FROM tbl_member WHERE int_member_id='$member_id'";
        $query = mysqli_query($conn, $sql);

        if( mysqli_num_rows($query) > 0 ) {
            $valid = mysqli_fetch_object($query);
            $valid = $valid->int_member_id;
        }
    }

    return $valid;
}
function validate_ec_id($ec_id) {
    global $conn;

    $valid = false;

    if( !empty($ec_id) ) {
        $sql = "SELECT ec_id FROM tbl_member WHERE ec_id='$ec_id'";
        $query = mysqli_query($conn, $sql);

        if( mysqli_num_rows($query) > 0 ) {
            $valid = mysqli_fetch_object($query);
            $valid = $valid->ec_id;
        }
    }

    return $valid;
}
function validate_new_amico_id_is_unique($amico_id) {
    global $conn;

    $valid = false;

    if( !empty($amico_id) ) {
        $amico_id = mysqli_real_escape_string($conn, $amico_id);

        $sql = "SELECT ec_id FROM tbl_member WHERE amico_id='$amico_id'";
        $query = mysqli_query($conn, $sql);

        if( mysqli_num_rows($query) < 1 ) {
            $valid = true;
        }
    }

    return $valid;
}
function validate_customerid($customerid) {
    global $conn;

    $valid = false;

    if( !empty($ec_id) ) {
        $sql = "select customers_id from customers where customers_id=$customerid'";
        $query = mysqli_query($conn, $sql);

        if( mysqli_num_rows($query) > 0 ) {
            $valid = mysqli_fetch_object($query);
            $valid = $valid->customers_id;
        }
    }

    return $valid;
}

function randomPassword() {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}