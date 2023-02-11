<?php


// parameters for server at site
$db="johnamico"; // johnamico
$host="mariadb"; // localhost
$username="root";
$password="root";
$magento_table_prfixe = 'stws_';
$naxum_global_pass = "LtmR:.pinz%CXP@S*?g'J04g";

// define constants
define("DB",$db);
define("HOST",$host);
define("USERNAME",$username);
define("PASSWORD",$password);
define("MAGENTO_TABLE_PREFIX", $magento_table_prfixe);
define("NAXUM_GLOBAL_PASSWORD", $naxum_global_pass);
define("CDN_ENABLED", false);

define("SHOP_URL_WITHOUT_SCHEME", 'localhost:1337');

define("ENCRYPTION_KEY", "227da206097e0dce6fb7ea27df854aba745a20638d8a4c60f72b4e8aa846b552");

define("IS_LIVE_SITE", true);

// define constants (shared)
define("DB_shared","floydware_db");
define("HOST_shared","sql1");
define("USERNAME_shared","share");
define("PASSWORD_shared","amicofloyd08");

// md5 string for unsubscribe
define("UNSUBSCRIBE_STRING", "4434098958");
// Custom Base URL
define("CUSTOM_BASE_URL", "https://www.johnamico.com/");

$connct = mysqli_connect(HOST, USERNAME, PASSWORD, DB);
$conn = $connct;

//error_reporting('E_ALL & ~E_NOTICE');

function is_display_page() { return true; }
function is_display_admin_newStylist_page() { return true; }
function is_get_orders_from_magento() { return true; }
