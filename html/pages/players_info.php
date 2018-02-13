<?php

global $pic_enable;
$pid = isset($pid) ? $pid : $_GET['pid'];
$pid = mysql_real_escape_string(preg_replace('/\D/', '', $pid));

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
    echo "<div class='watchlistbox'><h2>Added!</h2><span class='watchlist'>" .htmlentities($playername)." has been added to your watchlist</span>";
  } else {
    echo "<div class='watchlistbox'><h2>Removed!</h2><span class='watchlist'>" .htmlentities($playername) ." has been removed from your watchlist</span>";
  }

  echo "<br>";
  $target = $PHP_SELF .'?p=pinfo&amp;pid='. $pid;
  echo '<span class="watchlist">Do you want to go to <a href="'. $target .'">'. htmlentities($playername) .'\'s page</a> or go to your Watchlist?.<br><a class="navCTA" href="'.$target.'" role="button">Player page</a> <a class="navCTA" href="?p=watchlist" role="button">Watchlist</a>';
  echo '<div class="darksearch">Or search another player:<br>
    <span><input type="text" class="search square" placeholder="Search player..." name="name"><input class="searchbutton" type="submit" value="Search"></span></div></div>';
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
    if (!$options['enabled']) continue;
    if ($options['gidrequired'] and empty($gid)) continue;
    $disp = true;
    $pinfourl = "http://${oururl}?p=pinfo&pid=$pid";
    $lgid = ($options['gidrequired']) ? $gid : 0;
    $imgurl = "http://${oururl}pic.php/$num/$pid/$lgid/.".$options['output']['type'];
    echo '<table class="zebra box" border="0" cellspacing="0" cellpadding="0" align="center"><tr>';
    echo '<td colspan="2" align="center"><img src="'. $imgurl .'" border="0" /></td>';
    echo '</tr><tr>';
    echo '<td class="smheading">BB Code:</td><td><textarea rows="1" cols="85">'. str_replace(' ', '&nbsp;', htmlentities('[url='.$pinfourl.'][img]'.$imgurl.'[/img][/url]')) .'</textarea></td>';
    echo '</tr><tr>';
    echo '<td class="smheading">HTML Code:</td><td><textarea rows="1" cols="85">'. str_replace(' ', '&nbsp;', htmlentities('<a href="'.$pinfourl.'" target="_blank"><img src="'.$imgurl.'" border="0"></img></a>')) .'</textarea></td>';
    echo '</tr></table><br><br>';
  }
  if (!$disp) {
		echo "Sorry, no pictures in this category";
	}
  echo '</div>';
  return;
}

echo '
<table class="zebra box" border="0" cellpadding="0" cellspacing="0" width="700">
<tbody>
	<tr>
    <th class="heading" colspan="14" align="center">Career Summary for '.htmlentities($playername).'  ';

if (PlayerOnWatchlist($pid)) {
  echo '<a href="?p=pinfo&amp;pid='.$pid.'&amp;togglewatch=1&amp;noheader=1"><img src="assets/images/unwatch.png" border="0" class="tooltip" title="You are watching this player. Click to remove from your watchlist."></a>';
} else {
  echo '<a href="?p=pinfo&amp;pid='.$pid.'&amp;togglewatch=1&amp;noheader=1"><img src="assets/images/watch.png"  border="0" class="tooltip" title="Click to add this player to your watchlist."></a>';
}

echo '</th>
</tr>
<tr>
  <th class="smheading" align="center">Match Type</th>
  <th class="smheading" align="center">Score</th>
  <th class="smheading tooltip" align="center" width="40" title="Frags: A player\'s frag count is equal to their kills minus suicides.  In team games team kills (not team suicides) are also subtracted from the player\'s kills.">F</th>
  <th class="smheading tooltip" align="center" width="40" title="Number of times a player kills another player.">K</th>
  <th class="smheading tooltip" align="center" width="40" title="Kills: Number of times a player gets killed by another player.">D</th>
  <th class="smheading tooltip" align="center" width="40" title="Suicides: Number of times a player dies due to action of their own cause. Suicides can be environment induced (drowning, getting crushed, falling) or weapon related (fatal splash damage from their own weapon).">S</th>
  <th class="smheading tooltip" align="center" width="40" title="Team Kills: Number of times a player in a team based game kills someone on their own team.">TK</th>
  <th class="smheading tooltip" align="center" width="55" title="Efficiency: A ratio that denotes the player\'s kill skill by comparing it with his overall performance.  A perfect efficiency is equal to 1 (100%), anything less than 0.5 (50%) is below average. Formula: Kills / (Kills + Deaths + Suicides [+Team Kills])">Eff.</th>
  <th class="smheading tooltip" align="center" width="55" title="Accuracy: Overall accuracy when using all weapons.  Most accurate in insta but also very accurate in normal weapons.">Acc.</th>
  <th class="smheading tooltip" align="center" width="50" title="Average Time to Live: The length of time a player is in a game in seconds divided by how many times he/she dies, thus giving an average time of how long he/she will live.">Avg TTL</th>
  <th class="smheading" align="center">Matches</th>
  <th class="smheading" align="center">Wins</th>
  <th class="smheading" align="center">Win Percentage</th>
  <th class="smheading" align="center">Hours</th>
</tr>';

$sql_plist = "SELECT 
    	g.name AS gamename, 
    	SUM(p.gamescore) AS gamescore, 
    	COUNT(p.gamescore) as played, 
    	SUM(p.frags) AS frags, 
    	SUM(p.kills) AS kills, 
    	SUM(p.deaths) AS deaths,
    	SUM(p.suicides) AS suicides, 
    	SUM(p.teamkills) AS teamkills, 
    	SUM(p.kills+p.deaths+p.suicides+p.teamkills) AS sumeff, 
    	AVG(p.accuracy) AS accuracy, 
    	AVG(p.ttl) AS ttl, 
    	SUM(IF(
    	p.team = 0, 
    	IF((m.t0score > m.t1score AND m.t0score > m.t2score AND m.t0score > m.t3score), 1, 0), 
    	IF(
            p.team = 1, 
            IF((m.t1score > m.t0score AND m.t1score > m.t2score AND m.t1score > m.t3score), 1, 0),
        	IF(
            	p.team = 2,
            	IF((m.t2score > m.t0score AND m.t2score > m.t1score AND m.t2score > m.t3score), 1, 0),
                IF((m.t3score > m.t0score AND m.t3score > m.t1score AND m.t3score > m.t2score), 1, 0)
            )
        )
)) as wins, 
    	COUNT(p.id) AS games, 
    	SUM(p.gametime) as gametime
	FROM 
    	uts_player AS p, 
    	uts_games AS g, 
    	uts_match as m 
    WHERE 
    	p.gid = g.id AND m.id = p.matchid
    AND 
    	p.pid = '$pid'
    GROUP BY 
    	p.gid";
		
$q_plist = mysql_query($sql_plist) or die(mysql_error());

while ($r_plist = mysql_fetch_array($q_plist)) {
  $gametime = sec2hour($r_plist[gametime]);
  $eff = get_dp($r_plist[kills]/$r_plist[sumeff]*100);
  $acc = get_dp($r_plist[accuracy]);
  $ttl = GetMinutes($r_plist[ttl]);
  $winpercent = round($r_plist[wins]/$r_plist[games]*100, 2);

  echo'
  <tr>
    <td align="center">'.$r_plist[gamename].'</td>
    <td align="center">'.$r_plist[gamescore].'</td>
    <td align="center">'.$r_plist[frags].'</td>
    <td align="center">'.$r_plist[kills].'</td>
    <td align="center">'.$r_plist[deaths].'</td>
    <td align="center">'.$r_plist[suicides].'</td>
    <td align="center">'.$r_plist[teamkills].'</td>
    <td align="center">'.$eff.'</td>
    <td align="center">'.$acc.'</td>
    <td align="center">'.$ttl.'</td>
    <td  align="center">'.$r_plist[games].'</td>
	<td  align="center">'.$r_plist[wins].'</td>
	<td  align="center">'.$winpercent.'%</td>
    <td align="center">'.$gametime.'</td>
  </tr>';
}

$r_sumplist = small_query("SELECT SUM(p.gamescore) AS gamescore, SUM(p.frags) AS frags, SUM(p.kills) AS kills, SUM(p.deaths) AS deaths,
SUM(p.suicides) AS suicides, SUM(p.teamkills) AS teamkills, SUM(p.kills+p.deaths+p.suicides+p.teamkills) AS sumeff,
AVG(p.accuracy) AS accuracy, AVG(p.ttl) AS ttl, COUNT(p.id) AS games, SUM(IF(
    	p.team = 0, 
    	IF((m.t0score > m.t1score AND m.t0score > m.t2score AND m.t0score > m.t3score), 1, 0), 
    	IF(
            p.team = 1, 
            IF((m.t1score > m.t0score AND m.t1score > m.t2score AND m.t1score > m.t3score), 1, 0),
        	IF(
            	p.team = 2,
            	IF((m.t2score > m.t0score AND m.t2score > m.t1score AND m.t2score > m.t3score), 1, 0),
                IF((m.t3score > m.t0score AND m.t3score > m.t1score AND m.t3score > m.t2score), 1, 0)
            )
        )
)) as wins,  SUM(p.gametime) as gametime
FROM uts_player p, uts_match m WHERE p.matchid = m.id AND pid = '$pid'");

$gametime = sec2hour($r_sumplist[gametime]);
$eff = get_dp($r_sumplist[kills]/$r_sumplist[sumeff]*100);
$acc = get_dp($r_sumplist[accuracy]);
$ttl = GetMinutes($r_sumplist[ttl]);
$winpercent = round($r_sumplist[wins]/$r_sumplist[games]*100, 2);

echo'
<tr>
  <td align="center">Totals</td>
  <td align="center">'.$r_sumplist[gamescore].'</td>
  <td align="center">'.$r_sumplist[frags].'</td>
  <td align="center">'.$r_sumplist[kills].'</td>
  <td align="center">'.$r_sumplist[deaths].'</td>
  <td align="center">'.$r_sumplist[suicides].'</td>
  <td align="center">'.$r_sumplist[teamkills].'</td>
  <td align="center">'.$eff.'</td>
  <td align="center">'.$acc.'</td>
  <td align="center">'.$ttl.'</td>
  <td align="center">'.$r_sumplist[games].'</td>
  <td align="center">'.$r_sumplist[wins].'</td>
  <td align="center">'.$winpercent.'%</td>
  <td align="center">'.$gametime.'</td>
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
  <table class="zebra box" border="0" cellpadding="0" cellspacing="0" width="700">
  <tbody>
  <tr>
    <th class="heading" colspan="11" align="center">Assault, Domination and CTF Events Summary</th>
  </tr>
  <tr>
    <th align="center" rowspan="2">Assault Objectives</th>
    <th align="center" rowspan="2">Control Point Captures</th>
    <th align="center" colspan="9">Capture The Flag</th>
  </tr>
  <tr>
    <th align="center">Flag Takes</th>
    <th align="center">Flag Pickups</th>
    <th align="center">Flag Drops</th>
    <th align="center">Flag Assists</th>
    <th align="center">Flag Covers</th>
    <th align="center">Flag Seals</th>
    <th align="center">Flag Captures</th>
    <th align="center">Flag Kills</th>
    <th align="center">Flag Returns</th>
  </tr>
  <tr>
    <td align="center">'.$sql_cdatot[ass_obj].'</td>
    <td align="center">'.$sql_cdatot[dom_cp].'</td>
    <td align="center">'.$sql_cdatot[flag_taken].'</td>
    <td align="center">'.$sql_cdatot[flag_pickedup].'</td>
    <td align="center">'.$sql_cdatot[flag_dropped].'</td>
    <td align="center">'.$sql_cdatot[flag_assist].'</td>
    <td align="center">'.$sql_cdatot[flag_cover].'</td>
    <td align="center">'.$sql_cdatot[flag_seal].'</td>
    <td align="center">'.$sql_cdatot[flag_capture].'</td>
    <td align="center">'.$sql_cdatot[flag_kill].'</td>
    <td align="center">'.$sql_cdatot[flag_return].'</td>
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
  <table class="zebra box" border="0" cellpadding="0" cellspacing="0" width="700">
  <tbody>
  <tr>
    <th class="heading" colspan="10" align="center">Special Events</th>
  </tr>
  <tr>
    <th class="smheading" align="center" rowspan="2" width="60">First Blood</th>
    <th class="smheading tooltip" align="center" colspan="4" width="160" title="If you manage to kill more 2 than people within a short space of time you get a Double Kill, 3 is a Multi Kill etc">Multis</th>
    <th class="smheading tooltip" align="center" colspan="5" width="200" title="Special event: If you manage to kill 5 or more opponents without dying yourself, you will be on a killing spree. If you kill more than 10 opponents, you are on a rampage, etc.">Sprees</th>
  </tr>
  <tr>
    <th class="smheading tooltip" align="center" width="40" title="Killed 2 people in a short space of time without dying himself/herself">Dbl</th>
    <th class="smheading tooltip" align="center" width="40" title="Killed 3 people in a short space of time without dying himself/herself">Multi</th>
    <th class="smheading tooltip" align="center" width="40" title="Killed 4 people in a short space of time without dying himself/herself">Ultra</th>
    <th class="smheading tooltip" align="center" width="40" title="Killed 5 people in a short space of time without dying himself/herself">Mons</th>
    <th class="smheading tooltip" align="center" width="40" title="Killed 5 people in a row without dying himself/herself">Kill</th>
    <th class="smheading tooltip" align="center" width="40" title="Killed 10 people in a row without dying himself/herself">Ram</th>
    <th class="smheading tooltip" align="center" width="40" title="Killed 15 people in a row without dying himself/herself">Dom</th>
    <th class="smheading tooltip" align="center" width="40" title="Killed 20 people in a row without dying himself/herself">Uns</th>
    <th class="smheading tooltip" align="center" width="40" title="Killed 25 people in a row without dying himself/herself">God</th>
  </tr>
  <tr>
    <td align="center">'.$sql_firstblood[fbcount].'</td>
    <td align="center">'.$sql_multis[spree_double].'</td>
    <td align="center">'.$sql_multis[spree_multi].'</td>
    <td align="center">'.$sql_multis[spree_ultra].'</td>
    <td align="center">'.$sql_multis[spree_monster].'</td>
    <td align="center">'.$sql_multis[spree_kill].'</td>
    <td align="center">'.$sql_multis[spree_rampage].'</td>
    <td align="center">'.$sql_multis[spree_dom].'</td>
    <td align="center">'.$sql_multis[spree_uns].'</td>
    <td align="center">'.$sql_multis[spree_god].'</td>
  </tr>
  </tbody></table>
  <br>';
}

$r_pickups = zero_out(small_query("SELECT SUM(pu_pads) AS pu_pads, SUM(pu_armour) AS pu_armour, SUM(pu_keg) AS pu_keg,
  SUM(pu_invis) AS pu_invis, SUM(pu_belt) AS pu_belt, SUM(pu_amp) AS pu_amp
  FROM uts_player WHERE pid = '$pid'"));

if ($r_pickups[pu_pads] || $r_pickups[pu_armour]  || $r_pickups[pu_keg]  || $r_pickups[pu_invis] || $r_pickups[pu_belt]) {
  echo '
  <table class="zebra box" border="0" cellpadding="0" cellspacing="0" width="700">
  <tbody>
  <tr>
    <th class="heading" colspan="7" align="center">Pickups Summary</th>
  </tr>
  <tr>
    <th class="smheading" align="center" width="80">Pads</th>
    <th class="smheading" align="center" width="80">Armour</th>
    <th class="smheading" align="center" width="80">Keg</th>
    <th class="smheading" align="center" width="80">Invisibility</th>
    <th class="smheading" align="center" width="80">Shield Belt</th>
    <th class="smheading" align="center" width="80">Damage Amp</th>
  </tr>
  <tr>
    <td align="center">'.$r_pickups[pu_pads].'</td>
    <td align="center">'.$r_pickups[pu_armour].'</td>
    <td align="center">'.$r_pickups[pu_keg].'</td>
    <td align="center">'.$r_pickups[pu_invis].'</td>
    <td align="center">'.$r_pickups[pu_belt].'</td>
    <td align="center">'.$r_pickups[pu_amp].'</td>
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
  m.mapfile, e.col3, e.col4
ORDER BY
  m.mapfile,
  0 + e.col3 ASC";

$q_btrecords = mysql_query($sql_btrecords) or die (mysql_error());

if (mysql_num_rows($q_btrecords) > 0) {
  echo '
  <table class="zebra box" border="0" cellpadding="0" cellspacing="0" width="700">
  <tbody>
  <tr>
    <th class="heading" colspan="4" align="center">Bunny Track Personal Records</th>
  </tr>
  <tr>
    <th class="smheading" align="center" width="200">Map</th>
    <th class="smheading" align="center" width="80">N&deg;</th>
    <th class="smheading" align="center" width="80">Time</th>
    <th class="smheading" align="center" width="200">Date</th>
  </tr>';

  while ($r_btrecords = mysql_fetch_array($q_btrecords)) {
    $map = un_ut($r_btrecords['map']);
    $myurl = urlencode($map);
    $maprank = 1 + small_count("SELECT DISTINCT p.pid AS rank FROM uts_player as p, uts_events AS e, uts_match as m WHERE (m.mapfile = '" . addslashes($map) . "' OR m.mapfile = '" . addslashes($map) . ".unr') AND m.id = e.matchid AND e.matchid = p.matchid AND e.playerid = p.playerid AND e.col3 < ".$r_btrecords['time'] . " AND e.col1 = 'btcap'");

    echo '
    <tr>
      <td>&nbsp;<a href="./?p=minfo&amp;map='.$myurl.'">'.htmlentities($map).'</a></td>
      <td align = "center">', $maprank, '</td>
      <td align = "center">', btcaptime($r_btrecords['time']), '</td>
      <td align = "center">', gmdate('d-m-Y h:i a', $r_btrecords['date']), '</td></tr>';
  }
  echo '</tbody></table>
  <br>';
}

// Do graph stuff
$bgwhere = "pid = '$pid'";
//include("pages/graph_pbreakdown.php");

// Player's ranks
echo '<table class="zebra box" border="0" cellpadding="0" cellspacing="0" width=700>
<tbody><tr>
  <th class="heading" colspan="6" align="center">Ranking</th>
</tr>
<tr>
  <th class="smheading" align="center" width="50">N&deg;</th>
  <th class="smheading" align="center" width="140">Match Type</th>
  <th class="smheading" align="center" width="80">Rank</th>
  <th class="smheading" align="center" width="50">Matches</th>
<th class="smheading" align="center" width="50">Explain</th>';

if ($pic_enable and basename($_SERVER['PATH_TRANSLATED']) != 'admin.php') {
  echo '<th class="smheading" align="center" width="50">Pics</th>';
}
echo '</tr>';

$sql_rank = "SELECT g.name AS gamename, r.rank, r.prevrank, r.matches, r.gid, r.pid FROM uts_rank AS r, uts_games AS g WHERE r.gid = g.id AND r.pid = '$pid';";
$q_rank = mysql_query($sql_rank) or die(mysql_error());

while ($r_rank = mysql_fetch_array($q_rank)) {
  $r_no = small_query("SELECT (COUNT(*) + 1) AS no FROM uts_rank WHERE gid= '${r_rank['gid']}' and rank > ". get_dp($r_rank['rank']) ."9");

  echo'<tr>
  <td align="center">'.RankImageOrText($r_rank['pid'], $name, $r_no['no'], $r_rank['gid'], $r_rank['gamename'], false, '%IT%').'</td>
  <td align="center">'.$r_rank['gamename'].'</td>
  <td align="center">'.get_dp($r_rank['rank']) .' '. RankMovement($r_rank['rank'] - $r_rank['prevrank']) . '</td>
  <td align="center">'.$r_rank['matches'].'</td>';

  echo '<td align="center"><a href="?p=pexplrank&amp;pid='.$pid.'&amp;gid='.$r_rank['gid'].'">(Click)</a></td>';
  if ($pic_enable and basename($_SERVER['PATH_TRANSLATED']) != 'admin.php') {
    echo '<td  align="center"><a href="?p=pinfo&amp;pid='.$pid.'&amp;gid='.$r_rank['gid'].'&amp;pics=1">(Click)</a></td>';
  }
  echo '</tr>';
}
echo '</tbody></table>';

$r_pings = small_query("SELECT MIN(lowping * 1) AS lowping, AVG(avgping * 1) AS avgping, MAX(highping * 1) AS highping FROM uts_player WHERE pid = $pid and lowping > 0");

if ($r_pings and $r_pings['lowping']) {
  echo '
  <br>
  <table class="zebra box" border="0" cellpadding="0" cellspacing="0" width=700>
  <tbody>
    <tr>
      <th class="heading" colspan="6" align="center">Pings</th>
    </tr>
    <tr>
      <th class="smheading" align="center" width="80">Min</th>
      <th class="smheading" align="center" width="80">Avg</th>
      <th class="smheading" align="center" width="80">Max</th>
    </tr>
    <tr>
      <td  align="center">'.ceil($r_pings['lowping']).'</td>
      <td  align="center">'.ceil($r_pings['avgping']).'</td>
      <td  align="center">'.ceil($r_pings['highping']).'</td>
    </tr>
  </tbody></table>';
}

$mcount = $r_sumplist[games];
$ecount = $mcount/50;
$ecount2 = number_format($ecount, 0, '.', '');

if ($ecount > $ecount2) {
  $ecount2 = $ecount2+1;
}

$fpage = 0;
if ($ecount < 1) { $lpage = 0; }
else { $lpage = $ecount2-1; }

$cpage = mysql_real_escape_string(preg_replace('/\D/', '', $_REQUEST["page"]));
if ($cpage == "") { $cpage = "0"; }

$qpage = $cpage*50;
$tfpage = $cpage+1;
$tlpage = $lpage+1;
$ppage = $cpage-1;

echo '<br>
<table class="zebra box" border="0" cellpadding="0" cellspacing="0" width=700>
<tbody><tr>
  <th class="heading" colspan="6" align="center">Matches played</th>
</tr>
<tr>
  <th class="smheading" align="center" width="60">ID</th>
  <th class="smheading" align="center" width="220">Date/Time</th>
  <th class="smheading" align="center" width="140">Match Type</th>
  <th class="smheading" align="center">Map</th>';
if (isset($is_admin) and $is_admin) echo '<td class="smheading" align="center">IP Used</td>';
echo'</tr>';

$sql_recent = "SELECT m.id, m.time, g.name AS gamename, m.mapfile, INET_NTOA(p.ip) AS ip FROM uts_match m, uts_player p, uts_games g
  WHERE p.pid = '$pid' AND m.id = p.matchid AND m.gid = g.id ORDER BY time DESC LIMIT $qpage,50";
$q_recent = mysql_query($sql_recent) or die(mysql_error());

while ($r_recent = mysql_fetch_array($q_recent)) {
  $r_time = mdate($r_recent[time]);
  $r_mapfile = un_ut($r_recent[mapfile]);

  echo'
  <tr class="clickableRow" href="./?p=match&amp;mid='.$r_recent[id].'">
  <td  align="center">'.$r_recent[id].'</td>
  <td  align="center"><a href="./?p=match&amp;mid='.$r_recent[id].'">'.$r_time.'</a></td>
  <td  align="center">'.$r_recent[gamename].'</td>
  <td  align="center">'.$r_mapfile.'</td>';
  if (isset($is_admin) and $is_admin) echo '<td  align="center">'. $r_recent[ip].'</td>';

  echo '</tr>';
}

echo'</tbody></table>';

$ppageurl = "<a class=\"pages\" href=\"./?p=pinfo&amp;pid=$pid&amp;page=$ppage\">[Previous]</a>";
if ($ppage < "0") { $ppageurl = "[Previous]"; }

$npage = $cpage+1;
$npageurl = "<a class=\"pages\" href=\"./?p=pinfo&amp;pid=$pid&amp;page=$npage\">[Next]</a>";
if ($npage >= "$ecount") { $npageurl = "[Next]"; }

$fpageurl = "<a class=\"pages\" href=\"./?p=pinfo&amp;pid=$pid&amp;page=$fpage\">[First]</a>";
if ($cpage == "0") { $fpageurl = "[First]"; }

$lpageurl = "<a class=\"pages\" href=\"./?p=pinfo&amp;pid=$pid&amp;page=$lpage\">[Last]</a>";
if ($cpage == "$lpage") { $lpageurl = "[Last]"; }

echo '<div class="pages"><b>Page ['.$tfpage.'/'.$tlpage.'] Selection: '.$fpageurl.' / '.$ppageurl.' / '.$npageurl.' / '.$lpageurl.'</b></div>';

?>
