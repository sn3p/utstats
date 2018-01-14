<?php
// get the caps, captimes and store them in the events database
	$ttl = 0;
	if ($qc_deaths[col4] + $q_suicides[col4] == $qm_gameinfofl[col3]) {
		// echo " get out time ";
		$outtime = small_query("SELECT col0, col1, col2 FROM uts_temp_$uid WHERE (col1 = 'kill' AND col4 = $playerid) or (col1 = 'suicide' AND col2 = $playerid) ORDER BY CONVERT(col0, UNSIGNED INTEGER) DESC LIMIT 0,1");
		$col0 = $outtime[col0];
		$col1 = 'out';
		$col2 = intval($outtime[col0] - $gamestart); //time
		$col3 = $outtime[col2]; // killer
		$col4 = "";

		$sql_eventslms = "INSERT INTO uts_events 
			      (matchid, playerid, col0, col1, col2, col3, col4) VALUES
			      ($matchid, $playerid, '$col0', '$col1', '$col2', '$col3', '$col4')";
		mysqli_query($GLOBALS["___mysqli_link"], $sql_eventslms) or die (mysqli_error($GLOBALS["___mysqli_link"]));

		// Fix ttl
		$ttl = ($outtime[col0] - $gamestart) / ($qc_deaths[col4] + $q_suicides[col4]);

		mysqli_query($GLOBALS["___mysqli_link"], "UPDATE uts_player SET ttl = $ttl WHERE id = $playerecordid") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	}
	else {
		$disconnect = small_query("SELECT col0 FROM uts_temp_$uid WHERE col1 = 'player' AND col2 = 'Disconnect' AND col3 = $playerid");
		if ((!empty($disconnect[col0])) and (intval($disconnect[col0]) < intval($gameend))) {
			// echo " get out time ";
			$outtime = $player_left - $gamestart;
			$q_out = mysqli_query($GLOBALS["___mysqli_link"], $sql_out);
			$col0 = $disconnect[col0];
			$col1 = 'out';
			$col2 = intval($disconnect[col0] - $gamestart); //time
			$col3 = "Disconnect";
			$col4 = "";

			$sql_eventslms = "INSERT INTO uts_events 
				      (matchid, playerid, col0, col1, col2, col3, col4) VALUES
				      ($matchid, $playerid, '$col0', '$col1', '$col2', '$col3', '$col4')";
			mysqli_query($GLOBALS["___mysqli_link"], $sql_eventslms) or die (mysqli_error($GLOBALS["___mysqli_link"]));

			// Update ttl and set score to 0
			$ttl = ($disconnect[col0] - $gamestart) / ($qc_deaths[col4] + $q_suicides[col4]);

			mysqli_query($GLOBALS["___mysqli_link"], "UPDATE uts_player SET ttl = $ttl, gamescore = 0 WHERE id = $playerecordid") or die(mysqli_error($GLOBALS["___mysqli_link"]));
		}
	}
?>
