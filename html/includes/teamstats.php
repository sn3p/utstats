<?php
function teamstats($mid, $title, $extra = NULL, $extratitle = NULL, $order = 'gamescore DESC') {
	global $gamename, $gid;
	$r_info = small_query("SELECT teamgame, t0score, t1score, t2score, t3score FROM uts_match WHERE id = '$mid'");
	if (!$r_info) die("Match not found");
	$teams = ($r_info['teamgame'] == 'True') ? true : false;
	$teamscore[-1] = 0;
	$teamscore[0] = $r_info['t0score'];
	$teamscore[1] = $r_info['t1score'];
	$teamscore[2] = $r_info['t2score'];
	$teamscore[3] = $r_info['t3score'];


	$cols = 10;
	if ($teams) $cols++;
	if ($extra) $cols++;

	$oldteam = -1;


	echo'
	<table class = "box" border="0" cellpadding="0" cellspacing="2" width="600">
	<tbody><tr>
		<td class="heading" colspan="'.$cols.'" align="center">'.htmlentities($title).'</td>
	</tr>';


	$sql_players = "SELECT  pi.name, pi.banned, p.pid, p.team, p.country, p.gametime, p.gamescore, p.frags, p.deaths, p.suicides, p.teamkills, p.eff, p.accuracy, p.ttl, p.rank".(($extra) ? ', p.'.$extra.' AS '.$extra  : '')."
	FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $mid
	ORDER BY".(($teams) ? ' team ASC,' : '')." $order";
	$q_players = mysql_query($sql_players) or die(mysql_error());
	$header = true;
	teamstats_init_totals($totals, $num);
	while ($r_players = zero_out(mysql_fetch_array($q_players))) {
		$r_players['dom_cp'] = $r_players['gamescore'] - $r_players['frags'];
		
		$r_players['team'] = intval($r_players['team']);
		if ($teams and $oldteam != $r_players['team']) {
			if ($r_players['team'] != 0) teamstats_team_totals($totals, $num, $teams, $extra, $teamscore[$oldteam]);
			$oldteam = $r_players['team'];
			teamstats_init_totals($totals, $num, $extra);

			switch(intval($r_players['team'])) {
				case 0:	$teamname = 'Red'; break;
				case 1:	$teamname = 'Blue'; break;
				case 2:	$teamname = 'Green'; break;
				case 3:	$teamname = 'Gold'; break;
			}
			echo'<tr><td class="hlheading" colspan="'.$cols.'" align="center">Team: '.$teamname.'</td></tr>';
			$header = true;
		}
		if ($header) {
			$header = false;
			echo '
			<tr>
				<td class="smheading" align="center">Player</td>
				<td class="smheading" align="center" width="50">Time</td>
				<td class="smheading" align="center" width="50">Score</td>';
			if ($extra) echo'    <td class="smheading" align="center" width="50">'.htmlentities($extratitle).'</td>';
			echo'
				<td class="smheading" align="center" width="40" '.OverlibPrintHint('F').'>F</td>
				<td class="smheading" align="center" width="40" '.OverlibPrintHint('K').'>K</td>
				<td class="smheading" align="center" width="40" '.OverlibPrintHint('D').'>D</td>
				<td class="smheading" align="center" width="40" '.OverlibPrintHint('S').'>S</td>';
			if ($teams) echo '<td class="smheading" align="center" width="40" '.OverlibPrintHint('TK').'>TK</td>';
			echo '
				<td class="smheading" align="center" width="55" '.OverlibPrintHint('EFF').'>Eff.</td>
				<td class="smheading" align="center" width="55" '.OverlibPrintHint('ACC').'>Acc.</td>
				<td class="smheading" align="center" width="50" '.OverlibPrintHint('TTL').'>Avg TTL</td>
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
		echo '<td class="'.$class.'" align="center">'.GetMinutes($r_players['gametime']/TIMERATIO).'</td>';
		echo '<td class="'.$class.'" align="center">'.$r_players['gamescore'].'</td>';

		if ($extra) echo '<td class="'.$class.'" align="center">'.$r_players[$extra].'</td>';

		echo '<td class="'.$class.'" align="center">'.$r_players['frags'].'</td>';
		echo '<td class="'.$class.'" align="center">'.$kills.'</td>';
		echo '<td class="'.$class.'" align="center">'.$r_players['deaths'].'</td>';
		echo '<td class="'.$class.'" align="center">'.$r_players['suicides'].'</td>';

		if ($teams) echo '<td class="'.$class.'" align="center">'.$r_players['teamkills'].'</td>';

		echo '<td class="'.$class.'" align="center">'.$eff.'</td>';
		echo '<td class="'.$class.'" align="center">'.$acc.'</td>';
		echo '<td class="'.$class.'" align="center">'.$ttl.'</td>';
		echo '</tr>';
	}
	teamstats_team_totals($totals, $num, $teams, $extra, $teamscore[$oldteam]);
	echo '</tbody></table><br>';

}

function teamstats_init_totals(&$totals, &$num, $extra = null) {
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
}

function teamstats_team_totals(&$totals, $num, $teams, $extra, $teamscore) {
	if ($num == 0) $num = 1;
	$eff = get_dp($totals['eff'] / $num);
	$acc = get_dp($totals['acc'] / $num);
	$ttl = GetMinutes($totals['ttl'] / $num);


	echo '<tr>';
	echo '<td nowrap class="dark" align="center">Totals</td>';
	echo '<td class="darkgrey" align="center"></td>';
	if ($teams) {
		echo '<td class="darkgrey" align="center"><strong>'.$teamscore.'</strong> ('.$totals[gamescore].')</td>';
	} else {
		echo '<td class="darkgrey" align="center">'.$totals[gamescore].'</td>';
	}
	if ($extra) echo '<td class="darkgrey" align="center">'.$totals[$extra].'</td>';

	echo '<td class="darkgrey" align="center">'.$totals[frags].'</td>';
	echo '<td class="darkgrey" align="center">'.$totals[kills].'</td>';
	echo '<td class="darkgrey" align="center">'.$totals[deaths].'</td>';
	echo '<td class="darkgrey" align="center">'.$totals[suicides].'</td>';

	if ($teams) echo '<td class="darkgrey" align="center">'.$totals[teamkills].'</td>';

	echo '<td class="darkgrey" align="center">'.$eff.'</td>';
	echo '<td class="darkgrey" align="center">'.$acc.'</td>';
	echo '<td class="darkgrey" align="center">'.$ttl.'</td>';
	echo '</tr>';
}
?>
