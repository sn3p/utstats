<?php
$id = preg_replace('/\D/', '', $_GET["id"]);
$wid = preg_replace('/\D/', '', $_GET["wid"]);
$stage = my_addslashes($_GET["stage"]);
$oururl = $_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];
$oururl = str_replace("index.php", "", $oururl);
$rtype = my_addslashes($_GET["rtype"]);

if (empty($id)) {
      die("No ID given");
}

IF ($rtype == "clanbase") {
	include("pages/report_cb.php");
}

IF ($rtype == "bbcode") {
	include("pages/report/bbcode.php");
}

IF ($rtype == "clanbase" && $stage == "generate") {
	include("pages/report/clanbase.php");
}
?>