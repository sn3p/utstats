<?php

// Get last map, time, scores
$qlastMaps = small_query("SELECT id, mapfile, time, t0score, t1score, t2score, t3score FROM uts_match WHERE time = (SELECT MAX(time) FROM uts_match)");
$lastMapId = $qlastMaps['id'];
$lastMapFile = $qlastMaps['mapfile'];
$lastMapFileName = rtrim($lastMapFile, ".unr");

$lastMapTime = $qlastMaps['time'];
$lastMapScore0 = $qlastMaps['t0score'];
$lastMapScore1 = $qlastMaps['t1score'];
$lastMapScore2 = $qlastMaps['t2score'];
$lastMapScore3 = $qlastMaps['t3score'];
$moreThan2Teams = ($lastMapScore2!=0);

$mappic = getMapImageName($lastMapFileName);

// quick hack to show empty one at front page if no map
if (!file_exists($mappic) || $mappic == "assets/images/maps/blank_large.png") {
  $mappic = "assets/images/maps/emptyfront.jpg";
}

echo '
<center>
<table width="900"><tr><th class="heading"><center>Last Map Updated</center></th></tr></table>

<div class="recentheader" style="background-image: url(\''.$mappic.'\');background-size: 100% 100%;">
  <div class="carousel-caption">

    <table style="width:100%; padding-top: 25px;">
			<tr>
        <td colspan=3><p class="carousel-header">'.$lastMapFileName.'</p></td>
      </tr>
			<tr>
				<td class="carousel-red';
				if ($moreThan2Teams) {
					echo '-small';
				}
				echo '">'.$lastMapScore0.'</td>
				<td ';
				if ($moreThan2Teams) {
					echo 'rowspan=2 ';
				}
				echo '
				class="carousel-text" style="width:30%;">'.mdate($lastMapTime).' </td>
				<td class="carousel-blue';
				if  ($moreThan2Teams) {
					echo '-small';
				}
				echo '">'.$lastMapScore1.'</td>
			</tr>';

			if ($moreThan2Teams) {
  			echo '
  			<tr>
					<td class="carousel-green-small">'.$lastMapScore2.'</td>
					<td class="carousel-gold-small">'.$lastMapScore3.'</td>
				</tr>';
			}

		echo '</table>
    <p></p>
    <a class="navCTA" href="?p=match&mid='.$lastMapId.'" role="button">View stats</a>
  </div>
</div>
<br>

<table width="900" class="box zebra" border="0" cellpadding="0" cellspacing="0">
<tbody>
  <tr>
    <th class="heading" colspan="7" align="center">Last 10 Matches</th>
  </tr>
  <tr>
    <th class="smheading" align="center" width="40">ID</th>
    <th class="smheading" align="center" width="220">Date/Time</th>
    <th class="smheading" align="center" width="140">Match Type</th>
    <th class="smheading" align="center">Map</th>
    <th class="smheading" align="center" width="200">Scores</th>
  </tr>';

$sql_recent = "SELECT m.id, m.time, g.name AS gamename, m.mapfile, m.gametime, t0score, t1score, t2score, t3score, (SELECT count(p.id) FROM uts_player AS p WHERE m.id = p.matchid) as players FROM uts_match AS m, uts_games AS g WHERE g.id = m.gid $where ORDER BY m.time DESC LIMIT 10";
$q_recent = mysqli_query($GLOBALS["___mysqli_link"], $sql_recent) or die(mysqli_error($GLOBALS["___mysqli_link"]));

while ($r_recent = mysqli_fetch_array($q_recent)) {
  $r_time = mdate($r_recent[time]);
  $r_mapfile = un_ut($r_recent[mapfile]);
  $r_gametime = GetMinutes($r_recent[gametime]);
  $winner = max($r_recent[t0score], $r_recent[t1score], $r_recent[t2score], $r_recent[t3score]);

  if ($winner == $r_recent[t0score]) {
    $winnercolor = "red";
    $winmsg = "Red is the winner!";
  }
  elseif ($winner == $r_recent[t1score]) {
    $winnercolor = "blue";
    $winmsg = "Blue is the winner!";
  }
  elseif ($winner == $r_recent[t2score]) {
    $winnercolor = "green";
    $winmsg = "Green is the winner!";
  }
  else {
    $winnercolor = "gold";
    $winmsg = "Gold is the winner!";
  };

  echo'
  <tr class="clickableRow" href="./?p=match&amp;mid='.$r_recent[id].'">
  	<td align="center">'.$r_recent[id].'</td>
  	<td nowrap align="center"><a href="./?p=match&amp;mid='.$r_recent[id].'">'.$r_time.'</a></td>
  	<td nowrap align="center">'.$r_recent[gamename].'</td>
  	<td align="center">'.$r_mapfile.'</td>
  	<td class="tooltip" title="'.$winmsg.'" align="center">
      <span class="redbox">'.$r_recent[t0score].'</span><span class="bluebox">'.$r_recent[t1score].'</span>';

    	if ($moreThan2Teams) {
      	echo '<span class="greenbox">'.$r_recent[t2score].' </span> <span class="goldbox">'.$r_recent[t3score].'</span>';
    	}

	  echo '</td>
  </tr>';
}

echo '</tbody>
<tbody>
  <tr>
    <td colspan="6" class="totals" >
      <a href="?p=recent">Show all games</a>
    </td>
  </tr>
</tbody>
</table>
<br>';

echo'
<table class="box zebra" border="0" cellpadding="1" cellspacing="1" width="900">
  <thead>
  <tr>
    <th class="heading" colspan="5" align="center">UTStats Summary</th>
  </tr>
  <tr>
    <th class="smheading" align="center">Players</th>
    <th class="smheading" align="center">Servers</th>
    <th class="smheading" align="center">Maps</th>
    <th class="smheading" align="center">Matches Logged</th>
    <th class="smheading" align="center">Player Hours</th>
  </tr>
  <thead>';

// Get Summary Info
$r_playercount = small_query("SELECT COUNT(*) AS result FROM uts_pinfo");
$playercount = $r_playercount['result'];
$servercount = small_count("SELECT DISTINCT servername FROM uts_match");
$mapcount = small_count("SELECT COUNT(mapfile) AS result FROM uts_match GROUP BY mapfile");
$r_matchcount = small_query("SELECT COUNT(*) AS result FROM uts_match");
$matchcount= $r_matchcount['result'];
$hourscount = small_query("SELECT SUM(gametime) AS result FROM uts_player");
$gametime = sec2hour($hourscount[result]);

echo '<tbody>
  <tr>
    <td align="center">'.$playercount.'</td>
    <td align="center">'.$servercount.'</td>
    <td align="center">'.$mapcount.'</td>
    <td align="center">'.$matchcount.'</td>
    <td align="center">'.$gametime.'</td>
  </tr>
</tbody>
</table>
<br>

<table class="box zebra" border="0" cellpadding="1" cellspacing="1" width="900">
<thead>
  <tr>
		<th class="heading" colspan="8" align="center">Game Summary</th>
  </tr>
  <tr>
		<th class="smheading" align="center" width="150">Match Type</th>
		<th class="smheading" align="center" width="52">Frags</th>
		<th class="smheading" align="center" width="52">Kills</th>
		<th class="smheading" align="center" width="52">Suicides</th>
		<th class="smheading" align="center" width="40">Team Kills</th>
		<th class="smheading" align="center" width="52">Matches</th>
		<th class="smheading" align="center" width="52">Game Hours</th>
  </tr>
<thead>
<tbody>';

$sql_gamesummary = "SELECT g.id AS gid, g.name AS gamename, SUM(p.frags) AS frags, SUM(p.kills) AS kills, SUM(p.suicides) AS suicides, SUM(p.teamkills) AS teamkills, COUNT(DISTINCT p.matchid) AS matchcount
  FROM uts_player AS p, uts_games AS g
  WHERE p.gid = g.id
  GROUP BY gamename, gid
  ORDER BY gamename ASC";

$q_gamesummary = mysqli_query($GLOBALS["___mysqli_link"], $sql_gamesummary) or die(mysqli_error($GLOBALS["___mysqli_link"]));

while ($r_gamesummary = mysqli_fetch_array($q_gamesummary)) {
	$gid = $r_gamesummary[gid];

	$q_gametime = small_query("SELECT SUM(gametime) AS gametime FROM uts_match WHERE gid = '$gid'");
	$gametime = sec2hour($q_gametime[gametime]);

	echo '
  <tr>
    <td align="center">'.$r_gamesummary[gamename].'</td>
		<td align="center">'.$r_gamesummary[frags].'</td>
		<td align="center">'.$r_gamesummary[kills].'</td>
		<td align="center">'.$r_gamesummary[suicides].'</td>
		<td align="center">'.$r_gamesummary[teamkills].'</td>
		<td align="center">'.$r_gamesummary[matchcount].'</td>
		<td align="center">'.$gametime.'</td>
  </tr>';
}

$totalsummary = small_query("SELECT SUM(p.frags) AS frags, SUM(p.kills) AS kills, SUM(p.suicides) AS suicides, SUM(p.teamkills) AS teamkills, COUNT(DISTINCT p.matchid) AS matchcount, SUM(p.gametime) AS gametime
  FROM uts_player AS p, uts_games AS g
  WHERE p.gid = g.id");

$q_gametime = small_query("SELECT SUM(gametime) AS gametime FROM uts_match");
$gametime = sec2hour($q_gametime[gametime]);

echo '
  <tr>
		<td class="totals" align="center"><b>Totals for All Players</b></td>
		<td class="totals" align="center">'.$totalsummary[frags].'</td>
		<td class="totals" align="center">'.$totalsummary[kills].'</td>
		<td class="totals" align="center">'.$totalsummary[suicides].'</td>
		<td class="totals" align="center">'.$totalsummary[teamkills].'</td>
		<td class="totals" align="center">'.$totalsummary[matchcount].'</td>
		<td class="totals" align="center">'.$gametime.'</td>
  </tr>
</tbody>
</table>
<br>';

// Do graph stuff
$gtitle = "Across All Servers";
$bgwhere = "id >= 0";
include("pages/graph_mbreakdown.php");

?>
