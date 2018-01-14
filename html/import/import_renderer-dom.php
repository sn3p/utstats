<?php
include_once('includes/renderer-dom.php');
include_once('includes/renderer-dm.php');

// Only run script if at least 5 minutes were played
if (($time_gameend-$time_gamestart) > 300 && count(array_unique($playernames)) >= 8) {
	$tableTempIdom = generateTempTable($safe_uid);
	$ampTimes = generateAmpTimes($safe_uid);

	try {
		renderDataTotal($safe_uid, $tableTempIdom);
		renderDataCPs($safe_uid, $tableTempIdom);

		renderDataPickups($safe_uid);
		renderAmpBars($safe_uid, $tableTempIdom);

		list($datafrags, $derivfrags, $topFraggers, $counter, $datafragsteam, $derivfragsteam, $topTeams) = parseDMdata($safe_uid);
		renderFragBarsTeams($datafragsteam, $derivfragsteam, $topTeams, $counter);

	} catch (Exception $e) {
		// Do nothing, but simply do not block import
	}

	// drop table
	mysqli_query($GLOBALS["___mysqli_link"], "DROP TABLE $tableTempIdom") or die(mysqli_error($GLOBALS["___mysqli_link"]));
}

?>
