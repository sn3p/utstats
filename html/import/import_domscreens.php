
<?php	
	include_once('includes/domstats.php');

	$safe_uid = mysql_real_escape_string($uid);

	// Get relevant info
	$playerinfo = getPlayerTeam($uid);
	$playernames = $playerinfo[0];
	$playerteams = $playerinfo[1];

	$gameinfo = getGameStartEndRatio($uid);
	$time_gamestart = $gameinfo[0];
	$time_gameend = $gameinfo[1];
	$time_ratio_correction = $gameinfo[2];
	
	$tableTempIdom = generateTempTable($safe_uid);
	$ampTimes = generateAmpTimes($safe_uid);
	renderDataTotal($safe_uid,$tableTempIdom);
	renderDataCPs($safe_uid,$tableTempIdom);
	renderDataPickups($safe_uid);
	renderAmpBars($safe_uid,$tableTempIdom);
	
	// drop table
	mysql_query("DROP TABLE $tableTempIdom") or die(mysql_error());
?>