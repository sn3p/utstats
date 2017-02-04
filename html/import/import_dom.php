<?php
// Get dom player scores
	$sql_domplayer = "SELECT col3 FROM uts_temp_$uid WHERE col1 = 'dom_playerscore_update' AND col2 = $playerid ORDER BY id DESC LIMIT 0,1";
	$q_domplayer = mysql_query($sql_domplayer) or die(mysql_error());
	$r_domplayer = mysql_fetch_array($q_domplayer);
	$domplayer = $r_domplayer[col3];
	if (empty($domplayer)) {
		$domplayer = 0;
	}
	$updatedomplayer = "UPDATE uts_player SET ass_obj = $domplayer WHERE id = $playerecordid";
	mysql_query($updatedomplayer) or die("Error idom1:" . mysql_error());

// Who did the control points
	$r_domcp = small_query("SELECT count(id) AS domcpcount FROM uts_temp_$uid WHERE col1 = 'controlpoint_capture' AND col3 = $playerid");
	$domcpcount = $r_domcp[domcpcount];
	$upd_domcp = "UPDATE uts_player SET dom_cp = $domcpcount WHERE id = $playerecordid";
	mysql_query($upd_domcp) or die("Error idom2:" . mysql_error());	
		
?>