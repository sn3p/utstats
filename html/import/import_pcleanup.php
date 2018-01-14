<?php
$sql_tgame = small_query("SELECT teamgame FROM uts_match WHERE id = $matchid");
IF($sql_tgame == "True") {
	$rem_srecord = "DELETE FROM uts_player WHERE matchid = $matchid AND team > 3";
	mysqli_query($GLOBALS["___mysqli_link"], $rem_srecord);
}

$cleaned = false;
// Get list of players
$sql_pname = "SELECT pid, name FROM uts_player, uts_pinfo AS pi WHERE matchid = $matchid AND pid = pi.id";
$q_pname = mysqli_query($GLOBALS["___mysqli_link"], $sql_pname);
while ($r_pname = mysqli_fetch_array($q_pname)) {
	$playername = addslashes($r_pname[name]);
	$pid = $r_pname['pid'];


	// Check if player has more than 1 record
	$q_ids = mysqli_query($GLOBALS["___mysqli_link"], "SELECT playerid FROM uts_player WHERE pid = '$pid' AND matchid = $matchid");
	
	IF (mysqli_num_rows($q_ids) > 1) {
		$numrecords = mysqli_num_rows($q_ids);
		echo $r_pname[name] .' ';
		// get all the ids this player had
		$playerids	= array();
		while ($r_ids = mysqli_fetch_array($q_ids)) {
			$playerids[] = $r_ids['playerid'];
		}
		
		$r_newplayerid = small_query("SELECT (MAX(playerid) + 1) AS newplayerid FROM uts_player WHERE matchid = $matchid");
		$newplayerid = $r_newplayerid['newplayerid'];
			
		// Fix the events table
		foreach ($playerids as $i => $oldplayerid) {
			mysqli_query($GLOBALS["___mysqli_link"], "UPDATE uts_events SET playerid = $newplayerid WHERE playerid = $oldplayerid AND matchid = $matchid");
		}

		// Fix matchcount in ranking table
		// mysql_query("UPDATE uts_rank SET matches = matches - ". ($numrecords - 1) ." WHERE pid = '$pid' AND gid = '$gid'") or die(mysql_error());
		
		// ***********************
		// UPDATE THE KILLS MATRIX
		$sql_kmupdate = "	SELECT 	victim, 
											SUM(kills) AS kills 
								FROM 		uts_killsmatrix 
								WHERE 	matchid = $matchid 
									AND 	killer IN (". implode(",", $playerids) .") 
								GROUP BY	victim;";
								
		$q_kmupdate = mysqli_query($GLOBALS["___mysqli_link"], $sql_kmupdate);
		while ($r_kmupdate = mysqli_fetch_array($q_kmupdate)) {
			mysqli_query($GLOBALS["___mysqli_link"], "	INSERT 	
								INTO 		uts_killsmatrix 
								SET 		matchid = $matchid, 
											killer = $newplayerid, 
											victim = ${r_kmupdate['victim']},
											kills	= ${r_kmupdate['kills']};");
		}
		
		$sql_kmupdate = "	SELECT 	killer, 
											SUM(kills) AS kills 
								FROM 		uts_killsmatrix 
								WHERE 	matchid = $matchid 
									AND 	victim IN (". implode(",", $playerids) .") 
								GROUP BY	killer;";
								
		$q_kmupdate = mysqli_query($GLOBALS["___mysqli_link"], $sql_kmupdate);
		while ($r_kmupdate = mysqli_fetch_array($q_kmupdate)) {
			mysqli_query($GLOBALS["___mysqli_link"], "	INSERT 	
								INTO 		uts_killsmatrix 
								SET 		matchid = $matchid, 
											killer = ${r_kmupdate['killer']}, 
											victim = $newplayerid,
											kills	= ${r_kmupdate['kills']};");
		}
		
		mysqli_query($GLOBALS["___mysqli_link"], "	DELETE
							FROM		uts_killsmatrix
							WHERE 	matchid = $matchid 
								AND 	(killer IN (". implode(",", $playerids) .")
								OR		 victim IN (". implode(",", $playerids) ."));");
		
				
		// FINISHED UPDATING THE KILLS MATRiX
		// **********************************
		
		
		// Get non summed information
		
		$r_truepinfo1 = small_query("SELECT insta, pid, team, isabot, country, ip, gid FROM uts_player WHERE pid = '$pid' AND matchid = $matchid LIMIT 0,1");
		// fix rejoin as spec
		if ($r_treupinfo1['team'] == 255) {
		    $r_trueteam = small_query("SELECT team FROM uts_player WHERE pid = '$pid' AND matchid = $matchid AND team != 255 LIMIT 0,1");
		    if ($r_trueteam['team'] < 255) {
			    $r_truepinfo1['team'] = $r_trueteam['team'];
		    }
		}

		// Group Player Stuff so we only have 1 player record per match
		if ($gamename == "Bunny Track") {
			// In bunnytrack the gamescore is 2000 - fastest time in seconds so use MAX gamescore rather then SUM
			$r_truepinfo2 = small_query("SELECT
			SUM(gametime) AS gametime,
			MAX(gamescore) AS gamescore,
			AVG(lowping) AS lowping,
			AVG(highping) AS highping,
			AVG(avgping) AS avgping,
			SUM(frags) AS frags,
			SUM(deaths) AS deaths,
			SUM(kills) AS kills,
			SUM(suicides) AS suicides,
			SUM(teamkills) AS teamkills,
			AVG(eff) AS eff,
			AVG(accuracy) AS accuracy,
			AVG(ttl) AS ttl,
			SUM(flag_taken) AS flag_taken,
			SUM(flag_pickedup) AS flag_pickedup,
			SUM(flag_dropped) AS flag_dropped,
			SUM(flag_return) AS flag_return,
			SUM(flag_capture) AS flag_capture,
			SUM(flag_cover) AS flag_cover,
			SUM(flag_seal) AS flag_seal,
			SUM(flag_assist) AS flag_assist,
			SUM(flag_kill) AS flag_kill,
			SUM(dom_cp) AS dom_cp,
			SUM(ass_obj) AS ass_obj,
			SUM(spree_double) AS spree_double,
			SUM(spree_triple) AS spree_triple,
			SUM(spree_multi) AS spree_multi,
			SUM(spree_mega) AS spree_mega,
			SUM(spree_ultra) AS spree_ultra,
			SUM(spree_monster) AS spree_monster,
			SUM(spree_kill) AS spree_kill,
			SUM(spree_rampage) AS spree_rampage,
			SUM(spree_dom) AS spree_dom,
			SUM(spree_uns) AS spree_uns,
			SUM(spree_god) AS spree_god,
			SUM(pu_pads) AS pu_pads,
			SUM(pu_armour) AS pu_armour,
			SUM(pu_keg) AS pu_keg,
			SUM(pu_invis) AS pu_invis,
			SUM(pu_belt) AS pu_belt,
			SUM(pu_amp) AS pu_amp,
			SUM(rank) AS rank
			FROM uts_player WHERE matchid = $matchid AND pid = '$pid'");
		}
		else {
			$r_truepinfo2 = small_query("SELECT
			SUM(gametime) AS gametime,
			SUM(gamescore) AS gamescore,
			AVG(lowping) AS lowping,
			AVG(highping) AS highping,
			AVG(avgping) AS avgping,
			SUM(frags) AS frags,
			SUM(deaths) AS deaths,
			SUM(kills) AS kills,
			SUM(suicides) AS suicides,
			SUM(teamkills) AS teamkills,
			AVG(eff) AS eff,
			AVG(accuracy) AS accuracy,
			AVG(ttl) AS ttl,
			SUM(flag_taken) AS flag_taken,
			SUM(flag_pickedup) AS flag_pickedup,
			SUM(flag_dropped) AS flag_dropped,
			SUM(flag_return) AS flag_return,
			SUM(flag_capture) AS flag_capture,
			SUM(flag_cover) AS flag_cover,
			SUM(flag_seal) AS flag_seal,
			SUM(flag_assist) AS flag_assist,
			SUM(flag_kill) AS flag_kill,
			SUM(dom_cp) AS dom_cp,
			SUM(ass_obj) AS ass_obj,
			SUM(spree_double) AS spree_double,
			SUM(spree_triple) AS spree_triple,
			SUM(spree_multi) AS spree_multi,
			SUM(spree_mega) AS spree_mega,
			SUM(spree_ultra) AS spree_ultra,
			SUM(spree_monster) AS spree_monster,
			SUM(spree_kill) AS spree_kill,
			SUM(spree_rampage) AS spree_rampage,
			SUM(spree_dom) AS spree_dom,
			SUM(spree_uns) AS spree_uns,
			SUM(spree_god) AS spree_god,
			SUM(pu_pads) AS pu_pads,
			SUM(pu_armour) AS pu_armour,
			SUM(pu_keg) AS pu_keg,
			SUM(pu_invis) AS pu_invis,
			SUM(pu_belt) AS pu_belt,
			SUM(pu_amp) AS pu_amp,
			SUM(rank) AS rank
			FROM uts_player WHERE matchid = $matchid AND pid = '$pid'");
		}

		// Remove all of this player's records
		$rem_precord = "DELETE FROM uts_player WHERE matchid = $matchid AND pid = '$pid'";
		mysqli_query($GLOBALS["___mysqli_link"], $rem_precord);
		
		// Add this new record to match
		$upd_precord = "	INSERT 
								INTO 		uts_player 
								SET		matchid = $matchid,
											insta = '$r_truepinfo1[insta]',
											playerid = '$newplayerid',
											pid = '$pid',
											team = '$r_truepinfo1[team]',
											isabot = '$r_truepinfo1[isabot]',
											country = '$r_truepinfo1[country]',
											ip = '$r_truepinfo1[ip]',
											gid = '$r_truepinfo1[gid]',
											gametime = '$r_truepinfo2[gametime]',
											gamescore = '$r_truepinfo2[gamescore]',
											lowping = '".(int)$r_truepinfo2[lowping]."',
											highping = '".(int)$r_truepinfo2[highping]."',
											avgping = '".(int)$r_truepinfo2[avgping]."',  
											frags = '$r_truepinfo2[frags]',
											deaths = '$r_truepinfo2[deaths]',
											kills = '$r_truepinfo2[kills]',
											suicides = '$r_truepinfo2[suicides]',
											teamkills = '$r_truepinfo2[teamkills]',
											eff = '$r_truepinfo2[eff]',
											accuracy = '$r_truepinfo2[accuracy]',
											ttl = '$r_truepinfo2[ttl]',
											flag_taken = '$r_truepinfo2[flag_taken]',
											flag_dropped = '$r_truepinfo2[flag_dropped]',
											flag_return = '$r_truepinfo2[flag_return]',
											flag_capture = '$r_truepinfo2[flag_capture]',
											flag_cover = '$r_truepinfo2[flag_cover]',
											flag_seal = '$r_truepinfo2[flag_seal]',
											flag_assist = '$r_truepinfo2[flag_assist]',
											flag_kill = '$r_truepinfo2[flag_kill]',
											flag_pickedup = '$r_truepinfo2[flag_pickedup]',
											dom_cp = '$r_truepinfo2[dom_cp]',
											ass_obj = '$r_truepinfo2[ass_obj]',
											spree_double = '$r_truepinfo2[spree_double]',
											spree_triple = '$r_truepinfo2[spree_triple]',
											spree_multi = '$r_truepinfo2[spree_multi]',
											spree_mega = '$r_truepinfo2[spree_mega]',
											spree_ultra = '$r_truepinfo2[spree_ultra]',
											spree_monster = '$r_truepinfo2[spree_monster]',
											spree_kill = '$r_truepinfo2[spree_kill]',
											spree_rampage = '$r_truepinfo2[spree_rampage]',
											spree_dom = '$r_truepinfo2[spree_dom]',
											spree_uns = '$r_truepinfo2[spree_uns]',
											spree_god = '$r_truepinfo2[spree_god]',
											pu_pads = '$r_truepinfo2[pu_pads]',
											pu_armour = '$r_truepinfo2[pu_armour]',
											pu_keg = '$r_truepinfo2[pu_keg]',
											pu_invis = '$r_truepinfo2[pu_invis]',
											pu_belt = '$r_truepinfo2[pu_belt]',
											pu_amp = '$r_truepinfo2[pu_amp]',
											rank = '$r_truepinfo2[rank]';";
		mysqli_query($GLOBALS["___mysqli_link"], $upd_precord) or die(mysqli_error($GLOBALS["___mysqli_link"]));
		$cleaned = true;
	}
}
if ($cleaned and $html) echo "<br />";
?>