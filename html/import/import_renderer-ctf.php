<?php
include_once('includes/renderer-dm.php');
include_once('includes/renderer-ctf.php');

// Only run script if at least 5 minutes were played
if (($time_gameend-$time_gamestart)>300 && count(array_unique($playernames))>=2) {
	try {
		$safe_uid = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $uid);

		prepCTFdata($safe_uid);

		renderScoreGraph($safe_uid);

		// Get relevant data
		list($datafrags, $derivfrags, $topFraggers, $counter, $datafragsteam, $derivfragsteam, $topTeams) = parseDMdata($safe_uid);

		renderFragBarsTeams($datafragsteam, $derivfragsteam, $topTeams, $counter);

		if ($playernumberofteams == 2)
			renderDataPickups($safe_uid);

	} catch (Exception $e) {
		// Do nothing, but simply do not block import
	}
}

?>
