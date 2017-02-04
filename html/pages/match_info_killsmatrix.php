<?php
function PrintVertical($text) {
	$len = strlen($text);
	$ret = '';
	for ($i = 0; $i < $len; $i++) {
		$ret .= substr($text, $i, 1) .'<br>';
	}
	return($ret);
}

// Retrieve the killmatrix
$sql_km = "	SELECT	killer, 
							victim, 
							kills
				FROM 		uts_killsmatrix
				WHERE 	matchid = $mid;";
							
$q_km = mysql_query($sql_km) or die(mysql_error());
while ($r_km = mysql_fetch_array($q_km)) {
	$km[intval($r_km['killer'])][intval($r_km['victim'])] = $r_km['kills'];
}

// No matrix: bye
if (!isset($km)) return;

// Are we processing a teamgame?
$qm_teamgame = small_query("SELECT teamgame FROM uts_match WHERE id = '$mid';");
$teamgame = $qm_teamgame['teamgame'];
$teamgame = ($teamgame == 'False') ? false : true;

// Get the players of this match
$sql_players = "	SELECT	p.pid,
									p.playerid,
									pi.name,
									pi.country,
									pi.banned,
									p.team,
									p.suicides
						FROM		uts_player p,
									uts_pinfo pi
						WHERE		(p.pid = pi.id)
							AND	matchid = '$mid'
						ORDER	BY	team ASC,
									gamescore DESC;";
$q_players = mysql_query($sql_players) or die(mysql_error());
while ($r_players = mysql_fetch_array($q_players)) {
	$players[intval($r_players['playerid'])] = array(	'pid' 		=> $r_players['pid'],
																		'name' 		=> $r_players['name'],
																		'country'	=> $r_players['country'],
																		'banned'		=> $r_players['banned'],
																		'suicides'	=> intval($r_players['suicides']),
																		'team' 		=> intval($r_players['team']));
}


// Table header
$extra = $teamgame ? 3 : 2;
echo '<table class = "box" border="0" cellpadding="1" cellspacing="2">
  <tbody><tr>
    <td class="heading" colspan="'. (count($players) + $extra) .'" align="center">Kills Match Up</td>
  </tr>
  <tr>
    <td class="dark" colspan="'.$extra.'" rowspan="'.$extra.'" align="center">&nbsp;</td>
    <td class="dark" colspan="'. count($players).'" align="center"><strong>Victim</strong></td>
  </tr>
  <tr>';

// Victims
foreach($players as $player) {


	echo '<td class="darkhuman" align="center" onmouseover="overlib(\''. 
			str_replace('"', '\\\'', QuoteHintText(FormatPlayerName($player['country'], $player['pid'], $player['name'], $gid, $gamename))) .'\');" onmouseout="nd();">
			<a class="darkhuman" href="?p=matchp&amp;mid='. $mid .'&amp;pid='. urlencode($player['pid']). '">'.
			PrintVertical($player['name']) .
			'</a></td>';
}  
echo  '</tr><tr>';

// Team colors victims
if ($teamgame) {
	foreach($players as $player) {
		switch($player['team']) {
			case 0: $teamcolor = 'redteam'; break;
			case 1: $teamcolor = 'blueteam'; break;
			case 2: $teamcolor = 'greenteam'; break;
			case 3: $teamcolor = 'goldteam'; break;
		}
		echo '<td class="'. $teamcolor .'" align="center" width="20">
				&nbsp;</td>';
	}  
	echo '</tr>';
}

// Killer rows
$first = true;
$i = 0;
foreach($players as $kid => $killer) {
	if ($killer['banned'] == 'Y') continue;
	$i++;
	echo '<tr>';
	if ($first) echo'<td class="dark" rowspan="'. count($players) .'" align="center" width="20"><strong>K<br>i<br>l<br>l<br>e<br>r</strong></td>';
	echo '<td nowrap class="darkhuman" align="left" style="width: 150px;">';
	echo '<a class="darkhuman" href="?p=matchp&amp;mid='. $mid .'&amp;pid='. urlencode($killer['pid']). '">'.
			FormatPlayerName($killer['country'], $killer['pid'], $killer['name'], $gid, $gamename) .'&nbsp;</a></td>';
	if ($teamgame) {
		switch($killer['team']) {
			case 0: $teamcolor = 'redteam'; break;
			case 1: $teamcolor = 'blueteam'; break;
			case 2: $teamcolor = 'greenteam'; break;
			case 3: $teamcolor = 'goldteam'; break;
		}
		echo '<td class="'. $teamcolor .'" align="center" width="20">&nbsp;</td>';
	}
	foreach($players as $vid => $victim) {
		$class = ($kid == $vid) ? 'darkgrey' : 'grey';
		//if  ($i % 2) $class .= '2';
		echo '<td class="'. $class .'" align="center" width="20">';
		if ($kid == $vid) {
			$val = ($killer['suicides'] != 0) ? $killer['suicides'] : '&nbsp;';
		} else {
			$val = (isset($km[$kid][$vid])) ? $km[$kid][$vid] : '&nbsp';
		}
		echo $val .'</td>';
	}

	$first = false;
}  

echo '</tbody></table><br>';
?>