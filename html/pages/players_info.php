<?php
global $pic_enable;
$pid = isset($pid) ? $pid : $_GET['pid'];
$pid = preg_replace('/\D/', '', $pid);

$r_info = small_query("SELECT name, country, banned FROM uts_pinfo WHERE id = '$pid'");
if (!$r_info) {
	echo "Player not found";
	include("includes/footer.php");
	exit;
}

if ($r_info['banned'] == 'Y') {
	if (isset($is_admin) and $is_admin) {
		echo "Warning: Banned player - Admin override<br>";
	} else {
		echo "Sorry, this player has been banned!";
		include("includes/footer.php");
		exit;
	}
}

$playername = $r_info['name'];

if (isset($_GET['togglewatch'])) {
	$status = ToggleWatchStatus($pid);
	include('includes/header.php');
	if ($status == 1) {
		echo htmlentities($playername) ." has been added to your watchlist";
	} else {
		echo htmlentities($playername) ." has been removed from your watchlist";
	}
	echo "<br>";
	$target = $PHP_SELF .'?p=pinfo&amp;pid='. $pid;
	echo 'You will be taken back to the <a href="'. $target .'">'. htmlentities($playername) .'\'s page</a> in a moment.';
	echo '<meta http-equiv="refresh" content="2;URL='. $target .'">';
	return;
}


if (isset($_GET['pics'])) {
	$gid = $_GET['gid'];
	$gid = preg_replace("/\D/", "", $gid);
	if (!$pic_enable) {
		echo "Sorry, pictures are disabled by the administrator";
		return;
	}
	$oururl = $_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];
	$oururl = str_replace("index.php", "", $oururl);

	echo '<div class="pages" align="left">';
	require('includes/config_pic.php');
	$disp = false;
	foreach($pic as $num => $options) {
		if	(!$options['enabled']) continue;
		if ($options['gidrequired'] and empty($gid)) continue;
		$disp = true;
		$pinfourl = "http://${oururl}?p=pinfo&pid=$pid";
		$lgid = ($options['gidrequired']) ? $gid : 0;
		$imgurl = "http://${oururl}pic.php/$num/$pid/$lgid/.".$options['output']['type'];
		echo '<table class="box" border="0" cellspacing="2" cellpadding="1" align="center"><tr>';
		echo '<td colspan="2" align="center"><img src="'. $imgurl .'" border="0" /></td>';
		echo '</tr><tr>';
		echo '<td class="smheading">BB Code:</td><td><textarea rows="1" cols="85">'. str_replace(' ', '&nbsp;', htmlentities('[url='.$pinfourl.'][img]'.$imgurl.'[/img][/url]')) .'</textarea></td>';
		echo '</tr><tr>';
		echo '<td class="smheading">HTML Code:</td><td><textarea rows="1" cols="85">'. str_replace(' ', '&nbsp;', htmlentities('<a href="'.$pinfourl.'" target="_blank"><img src="'.$imgurl.'" border="0"></img></a>')) .'</textarea></td>';
		echo '</tr></table><br><br>';
	}
	if (!$disp) echo "Sorry, no pictures in this category";
	echo '</div>';
	return;
}





echo'
<table class="box" border="0" cellpadding="1" cellspacing="2" width="710">
  <tbody><tr>
    <td class="heading" colspan="12" align="center">Career Summary for '.FlagImage($r_info['country'], false).' '.htmlentities($playername).' ';

if (PlayerOnWatchlist($pid)) {
 	echo '<a href="?p=pinfo&amp;pid='.$pid.'&amp;togglewatch=1&amp;noheader=1"><img src="images/unwatch.png" width="17" height="11" border="0" alt="" title="You are watching this player. Click to remove from your watchlist."></a>';
} else {
 	echo '<a href="?p=pinfo&amp;pid='.$pid.'&amp;togglewatch=1&amp;noheader=1"><img src="images/watch.png" width="17" height="11" border="0" alt="" title="Click to add this player to your watchlist."></a>';
}

echo '
	 </td>
  </tr>
  <tr>
	 <td class="smheading" align="center">Match Type</td>
    <td class="smheading" align="center">Score</td>
    <td class="smheading" align="center" '.OverlibPrintHint('F').'>F</td>
    <td class="smheading" align="center" '.OverlibPrintHint('K').'>K</td>
    <td class="smheading" align="center" '.OverlibPrintHint('D').'>D</td>
    <td class="smheading" align="center" '.OverlibPrintHint('S').'>S</td>
    <td class="smheading" align="center" '.OverlibPrintHint('TK').'>TK</td>
    <td class="smheading" align="center" '.OverlibPrintHint('EFF').'>Eff.</td>
    <td class="smheading" align="center" '.OverlibPrintHint('ACC').'>Acc.</td>
    <td class="smheading" align="center" '.OverlibPrintHint('TTL').'>Avg TTL</td>
    <td class="smheading" align="center">Matches</td>
    <td class="smheading" align="center">Hours</td>
  </tr>';

$sql_plist = "SELECT g.name AS gamename, SUM(p.gamescore) AS gamescore, SUM(p.frags) AS frags, SUM(p.kills) AS kills, SUM(p.deaths) AS deaths,
SUM(p.suicides) AS suicides, SUM(p.teamkills) AS teamkills, SUM(kills+deaths+suicides+teamkills) AS sumeff, AVG(p.accuracy) AS accuracy, AVG(p.ttl) AS ttl,
COUNT(p.id) AS games, SUM(p.gametime) as gametime
FROM uts_player AS p, uts_games AS g WHERE p.gid = g.id AND p.pid = '$pid' GROUP BY p.gid";

$q_plist = mysql_query($sql_plist) or die(mysql_error());
while ($r_plist = mysql_fetch_array($q_plist)) {

	  $gametime = sec2hour($r_plist[gametime]);
	  $eff = get_dp($r_plist[kills]/$r_plist[sumeff]*100);
	  $acc = get_dp($r_plist[accuracy]);
	  $ttl = GetMinutes($r_plist[ttl]);

	  echo'<tr>
		<td class="dark" align="center">'.$r_plist[gamename].'</td>
		<td class="grey" align="center">'.$r_plist[gamescore].'</td>
		<td class="grey" align="center">'.$r_plist[frags].'</td>
		<td class="grey" align="center">'.$r_plist[kills].'</td>
		<td class="grey" align="center">'.$r_plist[deaths].'</td>
		<td class="grey" align="center">'.$r_plist[suicides].'</td>
		<td class="grey" align="center">'.$r_plist[teamkills].'</td>
		<td class="grey" align="center">'.$eff.'</td>
		<td class="grey" align="center">'.$acc.'</td>
		<td class="grey" align="center">'.$ttl.'</td>
		<td class="grey" align="center">'.$r_plist[games].'</td>
		<td class="grey" align="center">'.$gametime.'</td>
	  </tr>';
}

$r_sumplist = small_query("SELECT SUM(gamescore) AS gamescore, SUM(frags) AS frags, SUM(kills) AS kills, SUM(deaths) AS deaths,
SUM(suicides) AS suicides, SUM(teamkills) AS teamkills, SUM(kills+deaths+suicides+teamkills) AS sumeff,
AVG(accuracy) AS accuracy, AVG(ttl) AS ttl, COUNT(id) AS games, SUM(gametime) as gametime
FROM uts_player WHERE pid = '$pid'");

$gametime = sec2hour($r_sumplist[gametime]);
$eff = get_dp($r_sumplist[kills]/$r_sumplist[sumeff]*100);
$acc = get_dp($r_sumplist[accuracy]);
$ttl = GetMinutes($r_sumplist[ttl]);

  echo'
  <tr>
    <td class="dark" align="center"><b>Totals</b></td>
	<td class="darkgrey" align="center">'.$r_sumplist[gamescore].'</td>
	<td class="darkgrey" align="center">'.$r_sumplist[frags].'</td>
	<td class="darkgrey" align="center">'.$r_sumplist[kills].'</td>
	<td class="darkgrey" align="center">'.$r_sumplist[deaths].'</td>
	<td class="darkgrey" align="center">'.$r_sumplist[suicides].'</td>
	<td class="darkgrey" align="center">'.$r_sumplist[teamkills].'</td>
	<td class="darkgrey" align="center">'.$eff.'</td>
	<td class="darkgrey" align="center">'.$acc.'</td>
	<td class="darkgrey" align="center">'.$ttl.'</td>
	<td class="darkgrey" align="center">'.$r_sumplist[games].'</td>
	<td class="darkgrey" align="center">'.$gametime.'</td>
  </tr>
</tbody></table>
<br>';

$q_assgids = mysql_query("SELECT id FROM uts_games WHERE gamename LIKE '%Assault%';") or die(mysql_error());
$assgids = array();
while ($r_assgids = mysql_fetch_array($q_assgids)) {
	$assgids[] = $r_assgids['id'];
}
$assquery = (count($assgids) > 0) ? 'SUM(IF (gid IN ('. implode(',', $assgids) .'), ass_obj, 0)) AS ass_obj' : '0 AS ass_obj';
	$sql_cdatot = zero_out(small_query("SELECT SUM(dom_cp) AS dom_cp, $assquery, SUM(flag_taken) AS flag_taken,
SUM(flag_pickedup) AS flag_pickedup, SUM(flag_dropped) AS flag_dropped, SUM(flag_assist) AS flag_assist, SUM(flag_cover) AS flag_cover,
SUM(flag_seal) AS flag_seal, SUM(flag_capture) AS flag_capture, SUM(flag_kill)as flag_kill,
SUM(flag_return) AS flag_return FROM uts_player WHERE pid = '$pid'"));

if ($sql_cdatot[ass_obj] || $sql_cdatot[dom_cp] || $sql_cdatot[flag_taken] || $sql_cdatot[flag_pickedup] || $sql_cdatot[flag_dropped] || $sql_cdatot[flag_assist] || $sql_cdatot[flag_cover] || $sql_cdatot[flag_seal] || $sql_cdatot[flag_capture] || $sql_cdatot[flag_kill] || $sql_cdatot[flag_return]) {
	echo '
	<table class = "box" border="0" cellpadding="1" cellspacing="2" width="600">
	  <tbody><tr>
	    <td class="heading" colspan="11" align="center">Assault, Domination and CTF Events Summary</td>
	  </tr>
	  <tr>
	    <td class="dark" align="center" rowspan="2">Assault Objectives</td>
	    <td class="dark" align="center" rowspan="2">Control Point Captures</td>
	    <td class="dark" align="center" colspan="9">Capture The Flag</td>
	  </tr>
	  <tr>
	    <td class="dark" align="center">Flag Takes</td>
	    <td class="dark" align="center">Flag Pickups</td>
	    <td class="dark" align="center">Flag Drops</td>
	    <td class="dark" align="center">Flag Assists</td>
	    <td class="dark" align="center">Flag Covers</td>
	    <td class="dark" align="center">Flag Seals</td>
	    <td class="dark" align="center">Flag Captures</td>
	    <td class="dark" align="center">Flag Kills</td>
	    <td class="dark" align="center">Flag Returns</td>
	  </tr>';

	  echo'
	  <tr>
	    <td class="grey" align="center">'.$sql_cdatot[ass_obj].'</td>
	    <td class="grey" align="center">'.$sql_cdatot[dom_cp].'</td>
	    <td class="grey" align="center">'.$sql_cdatot[flag_taken].'</td>
	    <td class="grey" align="center">'.$sql_cdatot[flag_pickedup].'</td>
	    <td class="grey" align="center">'.$sql_cdatot[flag_dropped].'</td>
	    <td class="grey" align="center">'.$sql_cdatot[flag_assist].'</td>
	    <td class="grey" align="center">'.$sql_cdatot[flag_cover].'</td>
	    <td class="grey" align="center">'.$sql_cdatot[flag_seal].'</td>
	    <td class="grey" align="center">'.$sql_cdatot[flag_capture].'</td>
	    <td class="grey" align="center">'.$sql_cdatot[flag_kill].'</td>
	    <td class="grey" align="center">'.$sql_cdatot[flag_return].'</td>
	  </tr>
	</tbody></table>
	<br>';
}

$sql_firstblood = zero_out(small_query("SELECT COUNT(id) AS fbcount FROM uts_match WHERE firstblood = '$pid'"));
$sql_multis = zero_out(small_query("SELECT SUM(spree_double) AS spree_double, SUM(spree_multi) AS spree_multi,
SUM(spree_ultra) AS spree_ultra, SUM(spree_monster)  AS spree_monster,
SUM(spree_kill) AS spree_kill, SUM(spree_rampage) AS spree_rampage, SUM(spree_dom) AS spree_dom,
SUM(spree_uns) AS spree_uns, SUM(spree_god) AS spree_god
FROM uts_player WHERE pid = '$pid'"));

if ($sql_firstblood[fbcount] || $sql_multis[spree_double] || $sql_multis[spree_multi] || $sql_multis[spree_ultra] || $sql_multis[spree_monster] || $sql_multis[spree_kill] || $sql_multis[spree_rampage] || $sql_multis[spree_dom] || $sql_multis[spree_uns] || $sql_multis[spree_god] ) {
	echo '
	<table class = "box" border="0" cellpadding="0" cellspacing="2" width="400">
	  <tbody><tr>
	    <td class="heading" colspan="10" align="center">Special Events</td>
	  </tr>
	  <tr>
	    <td class="smheading" align="center" rowspan="2" width="40">First Blood</td>
	    <td class="smheading" align="center" colspan="4" width="160" '.OverlibPrintHint('Multis').'>Multis</td>
	    <td class="smheading" align="center" colspan="5" width="200" '.OverlibPrintHint('Sprees').'>Sprees</td>
	  </tr>
	  <tr>
	    <td class="smheading" align="center" width="40" '.OverlibPrintHint('DK').'>Dbl</td>
	    <td class="smheading" align="center" width="40" '.OverlibPrintHint('MK').'>Multi</td>
	    <td class="smheading" align="center" width="40" '.OverlibPrintHint('UK').'>Ultra</td>
	    <td class="smheading" align="center" width="40" '.OverlibPrintHint('MOK').'>Mons</td>
	    <td class="smheading" align="center" width="40" '.OverlibPrintHint('KS').'>Kill</td>
	    <td class="smheading" align="center" width="40" '.OverlibPrintHint('RA').'>Ram</td>
	    <td class="smheading" align="center" width="40" '.OverlibPrintHint('DO').'>Dom</td>
	    <td class="smheading" align="center" width="40" '.OverlibPrintHint('US').'>Uns</td>
	    <td class="smheading" align="center" width="40" '.OverlibPrintHint('GL').'>God</td>
	  </tr>';

	  echo'
	  <tr>
		<td class="grey" align="center">'.$sql_firstblood[fbcount].'</td>
		<td class="grey" align="center">'.$sql_multis[spree_double].'</td>
		<td class="grey" align="center">'.$sql_multis[spree_multi].'</td>
		<td class="grey" align="center">'.$sql_multis[spree_ultra].'</td>
		<td class="grey" align="center">'.$sql_multis[spree_monster].'</td>
		<td class="grey" align="center">'.$sql_multis[spree_kill].'</td>
		<td class="grey" align="center">'.$sql_multis[spree_rampage].'</td>
		<td class="grey" align="center">'.$sql_multis[spree_dom].'</td>
		<td class="grey" align="center">'.$sql_multis[spree_uns].'</td>
		<td class="grey" align="center">'.$sql_multis[spree_god].'</td>
	  </tr>
	  </tbody></table>
	<br>';
}

$r_pickups = zero_out(small_query("SELECT SUM(pu_pads) AS pu_pads, SUM(pu_armour) AS pu_armour, SUM(pu_keg) AS pu_keg,
SUM(pu_invis) AS pu_invis, SUM(pu_belt) AS pu_belt, SUM(pu_amp) AS pu_amp
FROM uts_player WHERE pid = '$pid'"));

if ($r_pickups[pu_pads] || $r_pickups[pu_armour]  || $r_pickups[pu_keg]  || $r_pickups[pu_invis] || $r_pickups[pu_belt] || $r_pickups[pu_amp] ) {
	echo '
	<table class = "box" border="0" cellpadding="0" cellspacing="2" width="480">
	  <tbody><tr>
	    <td class="heading" colspan="6" align="center">Pickups Summary</td>
	  </tr>
	  <tr>
	    <td class="smheading" align="center" width="80">Pads</td>
	    <td class="smheading" align="center" width="80">Armour</td>
	    <td class="smheading" align="center" width="80">Keg</td>
	    <td class="smheading" align="center" width="80">Invisibility</td>
	    <td class="smheading" align="center" width="80">Shield Belt</td>
	    <td class="smheading" align="center" width="80">Damage Amp</td>
	  </tr>
	  <tr>
		<td class="grey" align="center">'.$r_pickups[pu_pads].'</td>
		<td class="grey" align="center">'.$r_pickups[pu_armour].'</td>
		<td class="grey" align="center">'.$r_pickups[pu_keg].'</td>
		<td class="grey" align="center">'.$r_pickups[pu_invis].'</td>
		<td class="grey" align="center">'.$r_pickups[pu_belt].'</td>
		<td class="grey" align="center">'.$r_pickups[pu_amp].'</td>
	  </tr>
	  </tbody></table>
	<br>';
}

include('includes/weaponstats.php');
weaponstats(0, $pid);

echo '<br>';

// bt records
$sql_btrecords = "
SELECT
  m.mapfile AS map,
  e.col3 AS time,
  e.col4 AS date
FROM
  uts_match AS m,
  uts_events AS e,
  uts_player AS p
WHERE
  p.pid = $pid AND
  p.playerid = e.playerid AND
  e.matchid = p.matchid AND
  m.id = p.matchid AND
  e.col1 = 'btcap'
GROUP BY
  m.mapfile
ORDER BY
  m.mapfile,
  0 + e.col3 ASC";

$q_btrecords = mysql_query($sql_btrecords) or die (mysql_error());
if (mysql_num_rows($q_btrecords) > 0) {
	echo '
	<table class = "box" border="0" cellpadding="0" cellspacing="2" width="600">
	  <tbody><tr>
	    <td class="heading" colspan="4" align="center">Bunny Track Personal Records</td>
	  </tr>
	  <tr>
	    <td class="smheading" align="center" width="200">Map</td>
	    <td class="smheading" align="center" width="80">N&deg;</td>
	    <td class="smheading" align="center" width="80">Time</td>
	    <td class="smheading" align="center" width="200">Date</td>
	  </tr>';
	while ($r_btrecords = mysql_fetch_array($q_btrecords)) {
		$map = un_ut($r_btrecords['map']);
		$myurl = urlencode($map);
		$maprank = 1 + small_count("SELECT DISTINCT p.pid AS rank FROM uts_player as p, uts_events AS e, uts_match as m WHERE (m.mapfile = '" . addslashes($map) . "' OR m.mapfile = '" . addslashes($map) . ".unr') AND m.id = e.matchid AND e.matchid = p.matchid AND e.playerid = p.playerid AND e.col3 < ".$r_btrecords['time'] . " AND e.col1 = 'btcap'");
		echo '
			<tr><td class = "dark">&nbsp;<a class="darkhuman" href="./?p=minfo&amp;map='.$myurl.'">'.htmlentities($map).'</a></td>
			    <td class = "grey" align = "center">', $maprank, '</td>
			    <td class = "grey" align = "center">', btcaptime($r_btrecords['time']), '</td>
			    <td class = "grey" align = "center">', gmdate('d-m-Y h:i a', $r_btrecords['date']), '</td></tr>';
	}
	echo '
	  </tbody></table>
	<br>';
}

// Do graph stuff
$bgwhere = "pid = '$pid'";
include("pages/graph_pbreakdown.php");


// Player's ranks
echo'<table class = "box" border="0" cellpadding="1" cellspacing="1">
  <tbody><tr>
    <td class="heading" colspan="6" align="center">Ranking</td>
  </tr>
  <tr>
    <td class="smheading" align="center" width="50">N&deg;</td>
    <td class="smheading" align="center" width="140">Match Type</td>
    <td class="smheading" align="center" width="80">Rank</td>
    <td class="smheading" align="center" width="50">Matches</td>
	 <td class="smheading" align="center" width="50">Explain</td>';
	 if ($pic_enable and basename($_SERVER['PATH_TRANSLATED']) != 'admin.php') echo '<td class="smheading" align="center" width="50">Pics</td>';
echo '</tr>';

$sql_rank = "SELECT g.name AS gamename, r.rank, r.prevrank, r.matches, r.gid, r.pid FROM uts_rank AS r, uts_games AS g WHERE r.gid = g.id AND r.pid = '$pid';";
$q_rank = mysql_query($sql_rank) or die(mysql_error());
while ($r_rank = mysql_fetch_array($q_rank)) {
	$r_no = small_query("SELECT (COUNT(*) + 1) AS no FROM uts_rank WHERE gid= '${r_rank['gid']}' and rank > ". get_dp($r_rank['rank']) ."9");
	echo'<tr>
				<td class="grey" align="center">'.RankImageOrText($r_rank['pid'], $name, $r_no['no'], $r_rank['gid'], $r_rank['gamename'], false, '%IT%').'</td>
		<td class="grey" align="center">'.$r_rank['gamename'].'</td>
		<td class="grey" align="center">'.get_dp($r_rank['rank']) .' '. RankMovement($r_rank['rank'] - $r_rank['prevrank']) . '</td>
		<td class="grey" align="center">'.$r_rank['matches'].'</td>';
		echo '<td class="grey" align="center"><a class="grey" href="?p=pexplrank&amp;pid='.$pid.'&amp;gid='.$r_rank['gid'].'">(Click)</a></td>';
	if ($pic_enable and basename($_SERVER['PATH_TRANSLATED']) != 'admin.php') echo '<td class="grey" align="center"><a class="grey"  href="?p=pinfo&amp;pid='.$pid.'&amp;gid='.$r_rank['gid'].'&amp;pics=1">(Click)</a></td>';
	echo '</tr>';
}

echo '</tbody></table>';


$r_pings = small_query("SELECT MIN(lowping * 1) AS lowping, AVG(avgping * 1) AS avgping, MAX(highping * 1) AS highping FROM uts_player WHERE pid = $pid and lowping > 0");
if ($r_pings and $r_pings['lowping']) {
echo '
	<br>
	<table class = "box" border="0" cellpadding="0" cellspacing="2">
	<tbody><tr>
		<td class="heading" colspan="6" align="center">Pings</td>
	</tr>
	<tr>
		<td class="smheading" align="center" width="80">Min</td>
		<td class="smheading" align="center" width="80">Avg</td>
		<td class="smheading" align="center" width="80">Max</td>
	</tr>
	<tr>
		<td class="grey" align="center">'.ceil($r_pings['lowping']).'</td>
		<td class="grey" align="center">'.ceil($r_pings['avgping']).'</td>
		<td class="grey" align="center">'.ceil($r_pings['highping']).'</td>
	</tr>
	</tbody></table>';
}




echo'<br><table class="box" border="0" cellpadding="1" cellspacing="1">
  <tbody><tr>
    <td class="heading" colspan="6" align="center">Last 50 Games</td>
  </tr>
  <tr>
    <td class="smheading" align="center" width="80">Match ID</td>
    <td class="smheading" align="center" width="220">Date/Time</td>
    <td class="smheading" align="center" width="140">Match Type</td>
    <td class="smheading" align="center">Map</td>';
	if (isset($is_admin) and $is_admin) echo '<td class="smheading" align="center">IP Used</td>';
  echo'</tr>';

$sql_recent = "SELECT m.id, m.time, g.name AS gamename, m.mapfile, INET_NTOA(p.ip) AS ip FROM uts_match m, uts_player p, uts_games g
WHERE p.pid = '$pid' AND m.id = p.matchid AND m.gid = g.id ORDER BY time DESC LIMIT 0,50";
$q_recent = mysql_query($sql_recent) or die(mysql_error());
while ($r_recent = mysql_fetch_array($q_recent)) {

	  $r_time = mdate($r_recent[time]);
	  $r_mapfile = un_ut($r_recent[mapfile]);

	  echo'
	  <tr>
		<td class="dark" align="center"><a class="darkid" href="./?p=match&amp;mid='.$r_recent[id].'">'.$r_recent[id].'</a></td>
		<td class="dark" align="center"><a class="darkhuman" href="./?p=match&amp;mid='.$r_recent[id].'">'.$r_time.'</a></td>
		<td class="grey" align="center">'.$r_recent[gamename].'</td>
		<td class="grey" align="center">'.$r_mapfile.'</td>';
		if (isset($is_admin) and $is_admin) echo '<td class="grey" align="center">'. $r_recent[ip].'</td>';

	  echo '</tr>';
}

echo'
</tbody></table>
';
?>
