<?php
require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("../common_files/include/common_functions.php");

$redirect_to_url = base_member_url(). "/member_login.php";

if( !isset($_SERVER['HTTP_REFERER']) or empty($_SERVER['HTTP_REFERER']) or empty($_GET['key']) ) {
    header("Location: $redirect_to_url");
} else {
    /**
     * This is done to hide from the user how we are passing information here.
     */
    $ec_custom_login_key = explode( "::", base64_decode($_GET['key']) );
    $amico_id = isset($ec_custom_login_key[0]) ? $ec_custom_login_key[0] : null;
    $useremail = isset($ec_custom_login_key[1]) ? $ec_custom_login_key[1] : null;
    $user_mtype = isset($ec_custom_login_key[2]) ? $ec_custom_login_key[2] : null;

    if(isset($user_mtype) and $user_mtype=="e") {
        /* $amico_id is used as $username */
        $username = $amico_id;

        $query = 'select c.customers_id, c.customers_email_address, c.customers_password, m.mtype
        from tbl_member m left outer join customers c on c.customers_id=m.int_customer_id
        WHERE m.bit_active=1 AND m.mtype="m" AND m.amico_id="'.$amico_id.'"';

        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_array($result)) {
            $customers_password = $row["customers_password"];
            $customers_email_address = $row["customers_email_address"];
            $password = $customers_password;
        }

        /**
         * Check if the email matches the email from the database
         */
        if(isset($customers_email_address) and isset($useremail) and $customers_email_address==$useremail) {
           /**
            * Log out the current user
            */
            session_unregister("ses_member_id");
            $_SESSION['member']['is_member'] = false;
            $_SESSION['member'] = array();
            setcookie("membid", '', (time() - (3600 * 24 * 30)));

            /**
             * Login as the new user
             */
            if(isset($username) and !empty($username)) {
                do_member_login($username, $password);
            }
        }
    }

    header("Location: $redirect_to_url");
}
