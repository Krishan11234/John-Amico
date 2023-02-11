<?php

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$member = ( !empty($_GET['memberid']) ? $_GET['memberid'] : '' );

header("Location: " . base_member_url() . "/notes.php?add=1&memberid=$member");
exit;