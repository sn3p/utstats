<?php
$mid = preg_replace('/\D/', '', $_GET[mid]);
$pid = preg_replace('/\D/', '', $_GET[pid]);

if ($pid != "") {
	include("match_player.php");
} else {
	include("match_info.php");
}
?>
