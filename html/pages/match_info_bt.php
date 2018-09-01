<?php
/* get events first
$sql_caprecords = "SELECT playerid, col3 FROM uts_events WHERE matchid = $mid AND col1 = 'btcap' ORDER BY col3 DESC";
$q_caprecords = mysql_query($sql_caprecords);

while ($r_caprecords = mysql_fetch_array($q_caprecords)) {
  $caprecords[$r_caprecords['playerid']] = btcaptime($r_caprecords['col3']);
} */

echo'
<table class = "box" border="0" cellpadding="0" cellspacing="0" width="720">
  <tbody><tr>
    <td class="heading" colspan="12" align="center">Game Summary</td>
  </tr>
  <tr>
    <td class="hlheading" colspan="12" align="center">Team: Red</td>
  </tr>
    <tr>
    <td class="smheading" rowspan="2" align="center">Player</td>
    <td class="smheading" rowspan="2" align="center" width="90">Time</td>
    <td class="smheading" colspan="2" align="center" width="90">Score</td>
    <td class="smheading" rowspan="2" align="center" width="50">Flag Captures</td>
    <td class="smheading" rowspan="2" align="center" width="50">Fastest Capture</td>
    <td class="smheading" rowspan="2" align="center" width="50">Suicides</td>
  </tr>
  <tr>
    <td class="smheading" align="center">Team</td>
    <td class="smheading" align="center">Player</td>
  </tr>';

$sql_msred = "SELECT p.playerid, pi.name, pi.banned, p.country, p.pid, p.gamescore, p.gametime, p.flag_capture, p.suicides, p.rank, MIN(e.col3) AS captime
	FROM uts_pinfo AS pi, uts_player AS p
	LEFT JOIN uts_events AS e
	ON p.playerid = e.playerid AND p.matchid = e.matchid AND e.col1 = 'btcap'
	WHERE p.pid = pi.id AND p.matchid = $mid AND team = 0
	GROUP BY p.playerid
	ORDER BY e.col1 DESC, (0 + e.col3) ASC, gamescore DESC";
$q_msred = mysqli_query($GLOBALS["___mysqli_link"], $sql_msred) or die(mysqli_error($GLOBALS["___mysqli_link"]));
$i = 0;
while ($r_msred = zero_out(mysqli_fetch_array($q_msred))) {
	  if (!$r_msred['playerid']) {
		$r_msred['playerid'] = 0;
	  }
	  $i++;
	  $class = ($i % 2) ? 'grey' : 'grey2';

	  $redpname = $r_msred[name];
	  $myurl = urlencode($r_msred[name]);



	echo'<tr>';
	if ($r_msred['banned'] != 'Y') {
		echo '<td nowrap class="darkhuman" align="left"><a class="darkhuman" href="./?p=matchp&amp;mid='.$mid.'&amp;pid='.$r_msred['pid'].'">'.FormatPlayerName($r_msred[country], $r_msred['pid'], $redpname, $gid, $gamename, true, $r_msred['rank']).'</a></td>';
	} else {
		$r_msred ['gamescore'] = '-';
		$r_msred ['suicides'] = '-';
		$r_msred ['flag_capture'] = '-';
		echo '<td nowrap class="darkhuman" align="left"><span style="text-decoration: line-through;">'.FormatPlayerName($r_msred[country], $r_msred['pid'], $redpname, $gid, $gamename, true, $r_msred['rank']).'</span></td>';
	}

	echo '
		<td class="'.$class.'" align="center">'.GetMinutes($r_msred[gametime]).'</td>
		<td class="'.$class.'" align="center"></td>
		<td class="'.$class.'" align="center">'.$r_msred[gamescore].'</td>
		<td class="'.$class.'" align="center">'.$r_msred[flag_capture].'</td>
		<td class="'.$class.'" align="center">'.btcaptime($r_msred[captime]).'</td>
		<td class="'.$class.'" align="center">'.$r_msred[suicides].'</td>
	  </tr>';
}

$teamscore = small_query("SELECT t0score AS teamscore FROM uts_match WHERE id = $mid");
$msredtot = small_query("SELECT SUM(gamescore) AS gamescore, SUM(suicides) AS suicides, SUM(flag_capture) AS flag_capture FROM uts_player WHERE matchid = $mid AND team = 0 ORDER BY gamescore DESC");


echo'
  <tr>
    <td class="dark" align="center">Totals</td>
		<td class="darkgrey" align="center"></td>
		<td class="darkgrey" align="center">'.$teamscore[teamscore].'</td>
		<td class="darkgrey" align="center">'.$msredtot[gamescore].'</td>
		<td class="darkgrey" align="center">'.$msredtot[flag_capture].'</td>
		<td class="darkgrey" align="center"></td>
		<td class="darkgrey" align="center">'.$msredtot[suicides].'</td>
  </tr>';

echo'
  <tr>
    <td class="hlheading" colspan="12" align="center">Team: Blue</td>
  </tr>
    <tr>
    <td class="smheading" rowspan="2" align="center">Player</td>
    <td class="smheading" rowspan="2" align="center" width="90">Time</td>
    <td class="smheading" colspan="2" align="center" width="90">Score</td>
    <td class="smheading" rowspan="2" align="center" width="50">Flag Captures</td>
    <td class="smheading" rowspan="2" align="center" width="50">Fastest Capture</td>
    <td class="smheading" rowspan="2" align="center" width="50">Suicides</td>
  </tr>
  <tr>
    <td class="smheading" align="center">Team</td>
    <td class="smheading" align="center">Player</td>
  </tr>';

$sql_msblue = "SELECT p.playerid, pi.name, pi.banned, p.country, p.pid, p.gamescore, p.gametime, p.flag_capture, p.suicides, p.rank, MIN(e.col3) AS captime
	FROM uts_pinfo AS pi, uts_player AS p
	LEFT JOIN uts_events AS e
	ON p.playerid = e.playerid AND p.matchid = e.matchid AND e.col1 = 'btcap'
	WHERE p.pid = pi.id AND p.matchid = $mid AND team = 1
	GROUP BY p.playerid
	ORDER BY e.col1 DESC, (0 + e.col3) ASC, gamescore DESC";
$q_msblue = mysqli_query($GLOBALS["___mysqli_link"], $sql_msblue) or die(mysqli_error($GLOBALS["___mysqli_link"]));
$i = 0;
while ($r_msblue = zero_out(mysqli_fetch_array($q_msblue))) {
	  if (!$r_msblue['playerid']) {
		$r_msblue['playerid'] = 0;
	  }
	  $i++;
	  $class = ($i % 2) ? 'grey' : 'grey2';

	  $bluepname = $r_msblue[name];
	  $myurl = urlencode($r_msblue[name]);



	echo'<tr>';
	if ($r_msblue['banned'] != 'Y') {
		echo '<td nowrap class="darkhuman" align="left"><a class="darkhuman" href="./?p=matchp&amp;mid='.$mid.'&amp;pid='.$r_msblue['pid'].'">'.FormatPlayerName($r_msblue[country], $r_msblue['pid'], $bluepname, $gid, $gamename, true, $r_msblue['rank']).'</a></td>';
	} else {
		$r_msblue ['gamescore'] = '-';
		$r_msblue ['suicides'] = '-';
		$r_msblue ['flag_capture'] = '-';
		echo '<td nowrap class="darkhuman" align="left"><span style="text-decoration: line-through;">'.FormatPlayerName($r_msblue[country], $r_msblue['pid'], $bluepname, $gid, $gamename, true, $r_msblue['rank']).'</span></td>';
	}

	echo '
		<td class="'.$class.'" align="center">'.GetMinutes($r_msblue[gametime]).'</td>
		<td class="'.$class.'" align="center"></td>
		<td class="'.$class.'" align="center">'.$r_msblue[gamescore].'</td>
		<td class="'.$class.'" align="center">'.$r_msblue[flag_capture].'</td>
		<td class="'.$class.'" align="center">'.btcaptime($r_msblue[captime]).'</td>
		<td class="'.$class.'" align="center">'.$r_msblue[suicides].'</td>
	  </tr>';
}

$teamscore = small_query("SELECT t1score AS teamscore FROM uts_match WHERE id = $mid");
$msbluetot = small_query("SELECT SUM(gamescore) AS gamescore, SUM(suicides) AS suicides, SUM(flag_capture) AS flag_capture FROM uts_player WHERE matchid = $mid AND team = 1 ORDER BY gamescore DESC");


echo'
  <tr>
    <td class="dark" align="center">Totals</td>
		<td class="darkgrey" align="center"></td>
		<td class="darkgrey" align="center">'.$teamscore[teamscore].'</td>
		<td class="darkgrey" align="center">'.$msbluetot[gamescore].'</td>
		<td class="darkgrey" align="center">'.$msbluetot[flag_capture].'</td>
		<td class="darkgrey" align="center"></td>
		<td class="darkgrey" align="center">'.$msbluetot[suicides].'</td>
  </tr>';


echo'</tbody></table>
<br>';

?>
