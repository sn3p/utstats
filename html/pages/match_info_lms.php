<?php

	global $gamename, $gid;
	$r_info = small_query("SELECT teamgame, t0score, t1score, t2score, t3score FROM uts_match WHERE id = '$mid'");
	if (!$r_info) die("Match not found");
	$teams = ($r_info['teamgame'] == 'True') ? true : false;
	$teamscore[-1] = 0;
	$teamscore[0] = $r_info['t0score'];
	$teamscore[1] = $r_info['t1score'];
	$teamscore[2] = $r_info['t2score'];
	$teamscore[3] = $r_info['t3score'];


	$cols = 11;
	$oldteam = -1;


	echo'
	<table class = "box" border="0" cellpadding="0" cellspacing="0" width="700">
	<tbody><tr>
		<td class="heading" colspan="'.$cols.'" align="center">Player Summary</td>
	</tr>';


	$sql_players = "SELECT pi.name, pi.banned, p.playerid, p.pid, p.team, p.country, p.gametime, p.gamescore, p.frags, p.deaths, p.suicides, p.teamkills, p.eff, p.accuracy, p.ttl, p.rank, MAX(e.col2)
	FROM uts_pinfo AS pi, uts_player AS p
	LEFT JOIN uts_events AS e
	ON p.playerid = e.playerid AND p.matchid = e.matchid AND e.col1 = 'out' AND p.gamescore = 0
	WHERE p.pid = pi.id AND p.matchid = $mid
	GROUP BY p.playerid
	ORDER BY p.gamescore DESC, (0+e.col2) DESC";

	$q_players = mysqli_query($GLOBALS["___mysqli_link"], $sql_players) or die(mysqli_error($GLOBALS["___mysqli_link"]));
	$header = true;

	$totals['gamescore'] = 0;
	if ($extra) $totals[$extra] = 0;
	$totals['frags'] = 0;
	$totals['kills'] = 0;
	$totals['deaths'] = 0;
	$totals['suicides'] = 0;
	$totals['teamkills'] = 0;
	$totals['eff'] = 0;
	$totals['acc'] = 0;
	$totals['ttl'] = 0;
	$num = 0;

	while ($r_players = zero_out(mysqli_fetch_array($q_players))) {
		if ($header) {
			$header = false;
			echo '
			<tr>
				<th class="smheading " align="center">Player</th>
				<th class="smheading " align="center" width="50">Time</th>
				<th class="smheading " align="center" width="50">Score</th>';
			if ($extra) echo'    <th class="smheading " align="center" width="50">'.htmlentities($extratitle).'</th>';
			echo'
				<th class="smheading tooltip" align="center" width="40" title="Frags: A player\'s frag count is equal to their kills minus suicides.  In team games team kills (not team suicides) are also subtracted from the player\'s kills.">F</th>
				<th class="smheading tooltip" align="center" width="40" title="Number of times a player kills another player.">K</th>
				<th class="smheading tooltip" align="center" width="40" title="Kills: Number of times a player gets killed by another player.">D</th>
				<th class="smheading tooltip" align="center" width="40" title="Suicides: Number of times a player dies due to action of their own cause. Suicides can be environment induced (drowning, getting crushed, falling) or weapon related (fatal splash damage from their own weapon).">S</th>';
			if ($teams) echo '<th class="smheading tooltip" align="center" width="40" title="Team Kills: Number of times a player in a team based game kills someone on their own team.">TK</th>';
			echo '
				<th class="smheading tooltip" align="center" width="55" title="Efficiency: A ratio that denotes the player\'s kill skill by comparing it with his overall performance.  A perfect efficiency is equal to 1 (100%), anything less than 0.5 (50%) is below average. Formula: Kills / (Kills + Deaths + Suicides [+Team Kills])">Eff.</th>
				<th class="smheading tooltip" align="center" width="55" title="Accuracy: Overall accuracy when using all weapons.  Most accurate in insta but also very accurate in normal weapons.">Acc.</th>
				<th class="smheading tooltip" align="center" width="50" title="Average Time to Live: The length of time a player is in a game in seconds divided by how many times he/she dies, thus giving an average time of how long he/she will live.">Avg TTL</th>
			</tr>';
		}

		$eff = get_dp($r_players['eff']);
		$acc = get_dp($r_players['accuracy']);
		$ttl = GetMinutes($r_players['ttl']);
		$kills = $r_players['frags'] + $r_players['suicides'];
		$pname = $r_players['name'];

		$totals['gamescore'] += $r_players['gamescore'];
		if ($extra) $totals[$extra] += $r_players[$extra];
		$totals['frags'] += $r_players['frags'];
		$totals['kills'] += $kills;
		$totals['deaths'] += $r_players['deaths'];
		$totals['suicides'] += $r_players['suicides'];
		$totals['teamkills'] += $r_players['teamkills'];
		$totals['eff'] += $r_players['eff'];
		$totals['acc'] += $r_players['accuracy'];
		$totals['ttl'] += $r_players['ttl'];
		$num++;
		
		if ($r_players['banned'] == 'Y') {
			$eff = '-';
			$acc = '-';
			$ttl = '-';
			$kills = '-';
			$r_players['gamescore'] = '-';
			$r_players[$extra] = '-';
			$r_players['frags'] = '-';
			$r_players['deaths'] = '-';
			$r_players['suicides'] = '-';
			$r_players['teamkills'] = '-';
		}


		$class = ($num % 2) ? 'grey' : 'grey2';
		echo '<tr>';
		if ($r_players['banned'] != 'Y') {
			echo '<td nowrap class="darkhuman" align="left"><a class="darkhuman" href="./?p=matchp&amp;mid='.$mid.'&amp;pid='.$r_players['pid'].'">'.FormatPlayerName($r_players['country'], $r_players['pid'], $r_players['name'], $gid, $gamename, true, $r_players['rank']).'</a></td>';
		} else {
			echo '<td nowrap class="darkhuman" align="left"><span style="text-decoration: line-through;">'.FormatPlayerName($r_players['country'], $r_players['pid'], $r_players['name'], $gid, $gamename, true, $r_players['rank']).'</span></td>';
		}
		echo '<td class="'.$class.'" align="center">'.GetMinutes($r_players[gametime]).'</td>';
		echo '<td class="'.$class.'" align="center">'.$r_players[gamescore].'</td>';
		echo '<td class="'.$class.'" align="center">'.(empty($r_players['MAX(e.col2)']) ? '' : GetMinutes($r_players['MAX(e.col2)'])).'</td>';

		echo '<td class="'.$class.'" align="center">'.$r_players[frags].'</td>';
		echo '<td class="'.$class.'" align="center">'.$kills.'</td>';
		echo '<td class="'.$class.'" align="center">'.$r_players[deaths].'</td>';
		echo '<td class="'.$class.'" align="center">'.$r_players[suicides].'</td>';

		echo '<td class="'.$class.'" align="center">'.$eff.'</td>';
		echo '<td class="'.$class.'" align="center">'.$acc.'</td>';
		echo '<td class="'.$class.'" align="center">'.$ttl.'</td>';
		echo '</tr>';
	}
	if ($num == 0) $num = 1;
	$eff = get_dp($totals['eff'] / $num);
	$acc = get_dp($totals['acc'] / $num);
	$ttl = GetMinutes($totals['ttl'] / $num);


	echo '<tr>';
	echo '<td nowrap class="dark" align="center">Totals</td>';
	echo '<td class="darkgrey" align="center"></td>';
	echo '<td class="darkgrey" align="center">'.$totals[gamescore].'</td>';
	echo '<td class="darkgrey" align="center"></td>';
	echo '<td class="darkgrey" align="center">'.$totals[frags].'</td>';
	echo '<td class="darkgrey" align="center">'.$totals[kills].'</td>';
	echo '<td class="darkgrey" align="center">'.$totals[deaths].'</td>';
	echo '<td class="darkgrey" align="center">'.$totals[suicides].'</td>';

	if ($teams) echo '<td class="darkgrey" align="center">'.$totals[teamkills].'</td>';

	echo '<td class="darkgrey" align="center">'.$eff.'</td>';
	echo '<td class="darkgrey" align="center">'.$acc.'</td>';
	echo '<td class="darkgrey" align="center">'.$ttl.'</td>';
	echo '</tr>';
	echo '</tbody></table><br>';
