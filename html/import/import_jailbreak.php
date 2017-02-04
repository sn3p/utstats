<?php
// Get team releases
	$q_releases = small_query("SELECT COUNT(*) AS releases FROM uts_temp_$uid WHERE col1 = 'team_released' AND col3 = $playerid");
	$r_releases = $q_releases['releases'];

// Get the real suicide count 
//	(Jail releases should not be counted as suicides)
	$q_suicides = small_query("SELECT COUNT(*) AS suicides FROM uts_temp_$uid WHERE col1 = 'suicide' AND col2 = $playerid and col4 <> 'RedeemerDeath' and col4 <> 'JailRelease'");
	$r_suicides = $q_suicides['suicides'];
	
	$r_frags = $r_kills - $r_suicides;
	$r_efficiency = get_dp(($r_kills / ($r_kills + $r_deaths + $r_suicides + $r_teamkills)) * 100);
	
	
	mysql_query("	UPDATE 	uts_player 
						SET 		ass_obj = '$r_releases', 
									suicides = '$r_suicides',
									frags = '$r_frags',
									eff = '$r_efficiency'
						WHERE 	id = $playerecordid;") or die(mysql_error());	
		
?>