<?php


// parameters for server at site
$db="sharethewealth";
$host="localhost";
$username="sharethewealth";
$password="HXJ5sDYzBe2Ycyaq";
$magento_table_prfixe = 'stws_';
$naxum_global_pass = 'LtmR:.pinz%CXP@S*?g\'J04g';

// define constants
define("DB",$db);
define("HOST",$host);
define("USERNAME",$username);
define("PASSWORD",$password);
define("MAGENTO_TABLE_PREFIX", $magento_table_prfixe);
define("NAXUM_GLOBAL_PASSWORD", $naxum_global_pass);
define("CDN_ENABLED", false);

define("IS_LIVE_SITE", false);
define("IS_LOCAL_SITE", false);

// define constants (shared)
define("DB_shared","floydware_db");
define("HOST_shared","sql1");
define("USERNAME_shared","share");
define("PASSWORD_shared","amicofloyd08");

$connct = mysqli_connect(HOST, USERNAME, PASSWORD, DB);
$conn = $connct;

error_reporting('E_ALL & ~E_NOTICE');

function is_display_page() { return true; }
function is_display_admin_newStylist_page() { return true; }
function is_get_orders_from_magento() { return true; }