<?php

require_once "includes/renderer-general-import.php";

$safe_uid = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $uid);

// Get relevant info
$playerinfo = getPlayerTeam($uid);
$playernames = $playerinfo[0];
$playerteams = $playerinfo[1];
$playernumberofteams = $playerinfo[2];

$time_gameinfo = getGameStartEndRatio($uid);
$time_gamestart = $time_gameinfo[0];
$time_gameend = $time_gameinfo[1];
$time_ratio_correction = $time_gameinfo[2];

?>
