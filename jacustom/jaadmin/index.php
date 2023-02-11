<?php

$page_title = 'John Amico - Admin Main Menu';

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

//Dashboard
require_once("page.dashboard.php");

require_once("templates/footer.php");