<?php
// Get dom player scores
	$sql_domplayer = "SELECT col3 FROM uts_temp_$uid WHERE col1 = 'dom_playerscore_update' AND col2 = $playerid ORDER BY id DESC LIMIT 0,1";
	$q_domplayer = mysqli_query($GLOBALS["___mysqli_link"], $sql_domplayer) or die(mysqli_error($GLOBALS["___mysqli_link"]));
	$r_domplayer = mysqli_fetch_array($q_domplayer);
	$domplayer = $r_domplayer[col3];
	if (empty($domplayer)) {
		$domplayer = 0;
	}
	$updatedomplayer = "UPDATE uts_player SET ass_obj = $domplayer WHERE id = $playerecordid";
	mysqli_query($GLOBALS["___mysqli_link"], $updatedomplayer) or die("Error idom1:" . mysqli_error($GLOBALS["___mysqli_link"]));

// Who did the control points
	$r_domcp = small_query("SELECT count(id) AS domcpcount FROM uts_temp_$uid WHERE col1 = 'controlpoint_capture' AND col3 = $playerid");
	$domcpcount = $r_domcp[domcpcount];
	$upd_domcp = "UPDATE uts_player SET dom_cp = $domcpcount WHERE id = $playerecordid";
	mysqli_query($GLOBALS["___mysqli_link"], $upd_domcp) or die("Error idom2:" . mysqli_error($GLOBALS["___mysqli_link"]));	
		
?>