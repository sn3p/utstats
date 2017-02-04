<?php
// Who did the objectives
	$r_assobj = small_query("SELECT count(id) AS assobjcount FROM uts_temp_$uid WHERE col1 = 'assault_obj' AND col2 = $playerid");
	$assobjcount = $r_assobj[assobjcount];
	$upd_assobj = "UPDATE uts_player SET ass_obj = $assobjcount WHERE id = $playerecordid";
	mysql_query($upd_assobj) or die(mysql_error());

// Get assault game code (unique code give for the 2 games played)

	$r_asscode = small_query("SELECT col2 FROM uts_temp_$uid WHERE col1 = 'assault_gamecode' LIMIT 0,1");
	$asscode = $r_asscode[col2];
	$updateasscode = "UPDATE uts_match SET assaultid = '$asscode' WHERE id = $matchid";
	mysql_query($updateasscode) or die(mysql_error());

// Get Which Teams Attacking

	$r_assteam = small_query("SELECT col2 FROM uts_temp_$uid WHERE col1 = 'assault_attacker' LIMIT 0,1");
	$assteam = $r_assteam[col2];
	$updateassteam = "UPDATE uts_match SET ass_att = $assteam WHERE id = $matchid;";
	mysql_query($updateassteam) or die(mysql_error());

// Did they do it?

	$sql_asswin = "SELECT col0 FROM uts_temp_$uid WHERE col1 = 'game_end' AND col2 = 'Assault succeeded!' LIMIT 0,1";
	$q_asswin = mysql_query($sql_asswin) or die(mysql_error());
	$asswin = 0;

	while ($r_asswin = mysql_fetch_array($q_asswin)) {
			IF ($r_asswin[col0] != NULL ) { $asswin = 1; }
	}

	$updateasswin = "UPDATE uts_match SET ass_win = $asswin WHERE id = $matchid;";
	mysql_query($updateasswin) or die(mysql_error());
?>
