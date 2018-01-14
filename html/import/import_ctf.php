<?php
// Get Player Flag Events Count
	$sql_playerctf = "SELECT col1, COUNT(col1) AS flag_count FROM uts_temp_$uid WHERE (col1 LIKE 'flag_%' OR col1 = 'cover' OR col1 = 'seal') AND col2 = $playerid GROUP BY col1";
	$q_playerctf = mysqli_query($GLOBALS["___mysqli_link"], $sql_playerctf);

	$flag_taken = 0;
	$flag_dropped = 0;
	$flag_return = 0;
	$flag_capture = 0;
	$flag_cover = 0;
	$flag_seal = 0;
	$flag_assist = 0;
	$flag_kill = 0;
	$flag_pickedup = 0;

	while ($r_playerctf = mysqli_fetch_array($q_playerctf)) {

		// Cycle through events and see what the player got

		IF ($r_playerctf[col1] == "flag_taken") { $flag_taken = $r_playerctf[flag_count]; }
		IF ($r_playerctf[col1] == "flag_dropped") { $flag_dropped = $r_playerctf[flag_count]; }
		IF ($r_playerctf[col1] == "flag_returned") { $flag_return = $r_playerctf[flag_count]; }
		IF ($r_playerctf[col1] == "flag_captured") { $flag_capture = $r_playerctf[flag_count]; }
		IF ($r_playerctf[col1] == "flag_cover" or $r_playerctf[col1] == "Flag_cover" or $r_playerctf[col1] == "cover") { $flag_cover = $r_playerctf[flag_count]; }
		IF ($r_playerctf[col1] == "flag_seal" or $r_playerctf[col1] == "Flag_seal" or $r_playerctf[col1] == "seal") { $flag_seal = $r_playerctf[flag_count]; }
		IF ($r_playerctf[col1] == "flag_assist" or $r_playerctf[col1] == "Flag_assist") { $flag_assist = $r_playerctf[flag_count]; }
		IF ($r_playerctf[col1] == "flag_kill" or $r_playerctf[col1] == "Flag_kill") { $flag_kill = $r_playerctf[flag_count]; }
		IF ($r_playerctf[col1] == "flag_pickedup" or $r_playerctf[col1] == "flag_pickedup") { $flag_pickedup = $r_playerctf[flag_count]; }
	}

	$sql_playerflags = "	UPDATE 	uts_player
								SET 		flag_taken = $flag_taken,
											flag_dropped = $flag_dropped,
											flag_return = $flag_return,
											flag_capture = $flag_capture,
											flag_cover = $flag_cover,
											flag_seal = $flag_seal,
											flag_assist = $flag_assist,
											flag_kill = $flag_kill,
											flag_pickedup = $flag_pickedup
								WHERE 	id = $playerecordid";
	mysqli_query($GLOBALS["___mysqli_link"], $sql_playerflags) or die(mysqli_error($GLOBALS["___mysqli_link"]));
?>
