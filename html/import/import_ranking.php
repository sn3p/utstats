<?php
	if ($playerbanned) return;
// Get sums of different events

		// Work out all possible ranking scores
		$r_cnt = small_query("SELECT
		SUM(frags*0.5) AS frags, SUM(deaths*0.25) AS deaths, SUM(suicides*0.25) AS suicides, SUM(teamkills*2) AS teamkills,
		SUM(flag_taken*1) AS flag_taken, SUM(flag_pickedup*1) AS flag_pickedup, SUM(flag_return*1) AS flag_return, SUM(flag_capture*10) AS flag_capture, SUM(flag_cover*3) AS flag_cover,
		SUM(flag_seal*2) AS flag_seal, SUM(flag_assist*5) AS flag_assist, SUM(flag_kill*2) AS flag_kill,
		SUM(dom_cp*10) AS dom_cp, SUM(ass_obj*10) AS ass_obj,
		SUM(spree_double*1) AS spree_double, SUM(spree_multi*1) AS spree_multi, SUM(spree_ultra*1) AS spree_ultra, SUM(spree_monster*2) AS spree_monster,
		SUM(spree_kill*1) AS spree_kill, SUM(spree_rampage*1) AS spree_rampage, SUM(spree_dom*1.5) AS spree_dom, SUM(spree_uns*2) AS spree_uns, SUM(spree_god*3) AS spree_god,
		SUM(gametime) AS gametime 
		FROM uts_player WHERE pid = $pid and gid = $gid and matchid <= $matchid");

		// Work out per game ranking amounts
		$rank_ctf = $r_cnt[flag_taken]+$r_cnt[flag_pickedup]+$r_cnt[flag_return]+$r_cnt[flag_capture]+$r_cnt[flag_cover]+$r_cnt[flag_seal]+$r_cnt[flag_assist]+$r_cnt[flag_kill];
		$rank_ass = $r_cnt[ass_obj];
		$rank_dom = $r_cnt[com_cp];
		$rank_jb = $r_cnt[ass_obj] * 0.15;
		$rank_bt = $r_cnt[flag_capture];
		$rank_fpos = $r_cnt[frags]+$r_cnt[spree_double]+$r_cnt[spree_multi]+$r_cnt[spree_ultra]+$r_cnt[spree_monster]+$r_cnt[spree_kill]+$r_cnt[spree_rampage]+$r_cnt[spree_dom]+$r_cnt[spree_uns]+$r_cnt[spree_god];
		$rank_fneg = $r_cnt[deaths]+$r_cnt[suicides]+$r_cnt[teamkills];
		$r_gametime = ceil($r_cnt[gametime]/60);
		
		
		// Select rank record
		$r_rankp = small_query("SELECT id, time, rank, matches FROM uts_rank WHERE pid = '$pid' AND gid = '$gid'");
		$rank_id = $r_rankp[id];
		$rank_gametime = $r_rankp[time];
		$rank_crank = $r_rankp[rank];
		$rank_matches = $r_rankp[matches];

		// Work out initial rank dependant on game, if no game known use DM ranking
		IF ($gamename == "Assault" || $gamename == "Assault (insta)") {
			$rank_nrank = $rank_ass+$rank_fpos-$rank_fneg;
		} elseIF ($gamename == "Capture the Flag" || $gamename == "Capture the Flag (insta)") {
			$rank_nrank = $rank_ctf+$rank_fpos-$rank_fneg;
		} elseIF ($gamename == "Domination" || $gamename == "Domination (insta)") {
			$rank_nrank = $rank_dom+$rank_fpos-$rank_fneg;
		} elseIF ($gamename == "JailBreak" || $gamename == "JailBreak (insta)") {
			$rank_nrank = $rank_jb+$rank_fpos-$rank_fneg;
		} elseIF ($gamename == "Bunny Track") {
			// The complain about the old system which was based on captures/time only was that noobs
			// would vote easy maps only and therefore get many caps/hour. This mean much points for the
			// noobs and fewer for the good players. The new ranking would be based on the fastest captime.
			$rank_bt = 0;
			$sql_btmaprank = "SELECT e.col2 AS no, COUNT(e.col2) AS count FROM uts_events AS e, uts_player AS p WHERE p.pid = $pid AND p.gid = $gid AND p.playerid = e.playerid AND e.matchid = p.matchid AND e.matchid <= $matchid AND e.col2 > 0 AND e.col2 <= 5 GROUP BY e.col2";
			$q_btmaprank = mysqli_query($GLOBALS["___mysqli_link"], $sql_btmaprank) or die ("Can't retrieve \$q_btmaprank: ". mysqli_error($GLOBALS["___mysqli_link"]));
			while($r_btmaprank = mysqli_fetch_assoc($q_btmaprank)) {
				IF ($r_btmaprank[no] == 1) {
				      $rank_bt += $r_btmaprank[count] * 10;
				} elseIF ($r_btmaprank[no] == 2) {
				      $rank_bt += $r_btmaprank[count] * 8;
				} elseIF ($r_btmaprank[no] == 3) {
				      $rank_bt += $r_btmaprank[count] * 6;
				} elseIF ($r_btmaprank[no] == 4) {
				      $rank_bt += $r_btmaprank[count] * 4;
				} elseIF ($r_btmaprank[no] == 5) {
				      $rank_bt += $r_btmaprank[count] * 2;
				}
				// if ($dbg) echo "| pid: $r_btmaprank[no]*$r_btmaprank[count] => $rank_bt<br>"; 				
			}

			$rank_nrank = $rank_bt;
		} else {
			$rank_nrank = $rank_fpos-$rank_fneg;
		}

		// Average the rank over game minutes
		if ($r_gametime == 0) {
		      // some bug occured, ignore
		      if ($dbg) echo "Skip ranking. " ;

		      return;
		}

		$rank_nrank = ($rank_nrank/$r_gametime) * 600;
		if ($dbg) echo "Points: $rank_nrank<br>Time: $r_gametime<br>";
		
		// Add rank gametime to previous amount
		$rank_gametime = $r_gametime;

		// Reduce ranking if player hasnt played that much
		IF ($rank_gametime < 10) return;
		
		IF ($rank_gametime < 50) {
			$rank_nrank = $rank_nrank*.25;
		}
		
		IF ($rank_gametime >= 50 && $rank_gametime < 100) {
			$rank_nrank = $rank_nrank*.50;
		}
		
		IF ($rank_gametime >= 100 && $rank_gametime < 200) {
			$rank_nrank = $rank_nrank*.70;
		}

		IF ($rank_gametime >= 200 && $rank_gametime < 300) {
			$rank_nrank = $rank_nrank*.85;
		}

		if ($dbg) echo "Reduced: $rank_nrank<br>";

		// Add new rank record if one does not exist
		IF($rank_id == NULL) {
			mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_rank SET time = '$r_gametime', pid = '$pid', gid = '$gid', rank = '0', matches = '0';") or die(mysqli_error($GLOBALS["___mysqli_link"]));
			$rank_id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_link"]))) ? false : $___mysqli_res);
			$rank_gametime = 0;
 			$rank_crank = 0;
			$rank_matches = 0;
		}
				
		// Add number of matches played
		$rank_matches = $rank_matches+1;

		// Work out effective rank given
		$eff_rank = $rank_nrank-$rank_crank;

		// if ($dbg) echo "", intval($rank_crank), "-", intval($rank_nrank). "-", $rank_matches, " ";

		// Add effective rank points given to uts_player record
		mysqli_query($GLOBALS["___mysqli_link"], "UPDATE uts_player SET rank = $eff_rank WHERE id = $playerecordid") or die(mysqli_error($GLOBALS["___mysqli_link"]));

		// Update the rank
		mysqli_query($GLOBALS["___mysqli_link"], "UPDATE uts_rank SET time = '$rank_gametime', rank = '$rank_nrank', prevrank = '$rank_crank', matches = '$rank_matches' WHERE id = $rank_id;") or die(mysqli_error($GLOBALS["___mysqli_link"]));
?>
