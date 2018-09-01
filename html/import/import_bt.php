<?php
// get the caps, captimes and store them in the events database
	$sql_capbt = "SELECT col0, col1, col2, col3, col4 FROM uts_temp_$uid WHERE (col1 = 'cap' OR col1 = 'btcap') AND col2 = $playerid";
	$q_capbt = mysqli_query($GLOBALS["___mysqli_link"], $sql_capbt);

	$cap_speed = 0;
        $cap_time = 0;
	$cap_gametime = 0;
	$flag_capture = 0;

	while ($r_capbt = mysqli_fetch_array($q_capbt)) {
	      if ($cap_speed == 0 || $r_capbt['col3'] > $cap_speed) {
		    $cap_speed = $r_capbt['col3'];
		    $col0 = $r_capbt['col0']; // time
		    $col1 = $r_capbt['col1']; // cap
		    $col2 = $r_capbt['col2']; // playerid
		    $col3 = $r_capbt['col3']; // speed in 2000 - seconds (old system) or 600000 - hundreds of seconds (new system)
		    $col4 = $r_capbt['col4']; // date in seconds since epoch
	      }
	      $flag_capture++;
        }

	if ($cap_speed != 0) {
		# use col2 to store the rank, col3 to store the captime in seconds and col4 to store the date of the record 
		$col2 = small_count("SELECT DISTINCT col2 FROM uts_temp_$uid WHERE col1 = 'cap' AND col3 > $col3") + 1;
		if ($col1 == "btcap") {
			$col3 = ceil(600000 - $col3) / 100;
		}
		else if ($col1 == "cap") {
			$col3 = ceil((2000 - $col3-1)*100/1.1) / 100;
			$col1 = "btcap";
		}
		$col3 = sprintf("%01.2f", $col3);
		$sql_eventsbt = "INSERT INTO uts_events 
		      (matchid, playerid, col0, col1, col2, col3, col4) VALUES
		      ($matchid, $playerid, '$col0', '$col1', '$col2', '$col3', '$col4')";
		mysqli_query($GLOBALS["___mysqli_link"], $sql_eventsbt) or die (mysqli_error($GLOBALS["___mysqli_link"]));
	}

	if ($flag_capture > 0) {
	$sql_playerflags = "	UPDATE 	uts_player
								SET 	flag_capture = $flag_capture
								WHERE 	id = $playerecordid";
	mysqli_query($GLOBALS["___mysqli_link"], $sql_playerflags) or die(mysqli_error($GLOBALS["___mysqli_link"]));
	}
?>
