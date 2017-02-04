<?php
echo'
<table class = "box" border="0" cellpadding="1" cellspacing="2" width="720">
  <tbody><tr>
    <td class="heading" align="center">Totals Summary</td>
  </tr>
</tbody></table>
<br>
<table class="box" border="0" cellpadding="1" cellspacing="2">
  <tbody><tr>
    <td class="medheading" colspan="10" align="center">Summary</td>
  </tr>
  <tr>
    <td class="smheading" align="center" width="150">Game Type</td>
    <td class="smheading" align="center" width="45">Score</td>
    <td class="smheading" align="center" width="35">Frags</td>
    <td class="smheading" align="center" width="35">Kills</td>
    <td class="smheading" align="center" width="35">Suicides</td>
    <td class="smheading" align="center" width="35">Team Kills</td>
    <td class="smheading" align="center" width="50">Matches</td>
    <td class="smheading" align="center" width="45">Hours</td>
  </tr>';

$sql_totsumm = "SELECT g.name AS gamename, SUM(p.gamescore) AS gamescore, SUM(p.frags) AS frags, SUM(p.kills) AS kills, SUM(p.suicides) AS suicides, SUM(p.teamkills) AS teamkills, COUNT(DISTINCT p.matchid) AS matchcount, SUM(p.gametime) AS sumgametime
FROM uts_player AS p, uts_games AS g
WHERE p.gid = g.id
GROUP BY g.name
ORDER BY gamename ASC";

$q_totsumm = mysql_query($sql_totsumm) or die(mysql_error());

while ($r_totsumm = zero_out(mysql_fetch_array($q_totsumm))) {
	$gametime = sec2hour($r_totsumm[sumgametime]);

	echo'
	  <tr>
	    <td class="dark" align="center">'.$r_totsumm[gamename].'</td>
	    <td class="grey" align="center">'.$r_totsumm[gamescore].'</td>
	    <td class="grey" align="center">'.$r_totsumm[frags].'</td>
	    <td class="grey" align="center">'.$r_totsumm[kills].'</td>
	    <td class="grey" align="center">'.$r_totsumm[suicides].'</td>
	    <td class="grey" align="center">'.$r_totsumm[teamkills].'</td>
	    <td class="grey" align="center">'.$r_totsumm[matchcount].'</td>
	    <td class="grey" align="center">'.$gametime.'</td>
	  </tr>';
}

$sql_summtot = zero_out(small_query("SELECT SUM(gamescore) AS gamescore, SUM(frags) AS frags, SUM(kills) AS kills, SUM(suicides) AS suicides, SUM(teamkills) AS teamkills, COUNT(DISTINCT matchid) AS matchcount, SUM(gametime) AS sumgametime
FROM uts_player"));

$gametime2 = sec2hour($sql_summtot[sumgametime]);

echo'
    <tr>
    <td class="dark" align="center"><b>Totals</b></td>
	    <td class="grey" align="center">'.$sql_summtot[gamescore].'</td>
	    <td class="grey" align="center">'.$sql_summtot[frags].'</td>
	    <td class="grey" align="center">'.$sql_summtot[kills].'</td>
	    <td class="grey" align="center">'.$sql_summtot[suicides].'</td>
	    <td class="grey" align="center">'.$sql_summtot[teamkills].'</td>
	    <td class="grey" align="center">'.$sql_summtot[matchcount].'</td>
	    <td class="grey" align="center">'.$gametime2.'</td>
  </tr>
</tbody></table>
<br>
<table class = "box" border="0" cellpadding="1" cellspacing="2" width="600">
  <tbody><tr>
    <td class="medheading" colspan="11" align="center">Assault, Domination and CTF Events Summary</td>
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

 $q_assgids = mysql_query("SELECT id FROM uts_games WHERE gamename LIKE '%Assault%';") or die(mysql_error());
 $assgids = array();
 while ($r_assgids = mysql_fetch_array($q_assgids)) {
 	$assgids[] = $r_assgids['id'];
 }
 $assquery = (count($assgids) > 0) ? 'SUM(IF (gid IN ('. implode(',', $assgids) .'), ass_obj, 0)) AS ass_obj' : '0 AS ass_obj';

 $sql_cdatot = zero_out(small_query("SELECT SUM(dom_cp) AS dom_cp, $assquery, SUM(flag_taken) AS flag_taken,
 SUM(flag_pickedup) AS flag_pickedup, SUM(flag_dropped) AS flag_dropped, SUM(flag_assist) AS flag_assist, SUM(flag_cover) AS flag_cover,
 SUM(flag_seal) AS flag_seal, SUM(flag_capture) AS flag_capture, SUM(flag_kill)as flag_kill,
 SUM(flag_return) AS flag_return FROM uts_player"));

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
<br>
<table class = "box" border="0" cellpadding="1" cellspacing="2" width="500">
  <tbody><tr>
    <td class="medheading" colspan="4" align="center">Special Events</td>
  </tr>';

$sql_firstblood = zero_out(small_count("SELECT firstblood FROM uts_match WHERE firstblood != ''"));
$sql_multis = zero_out(small_query("SELECT SUM(spree_double) AS spree_double, SUM(spree_multi) AS spree_multi, SUM(spree_ultra) AS spree_ultra, SUM(spree_monster)  AS spree_monster FROM uts_player"));
$sql_sprees = zero_out(small_query("SELECT SUM(spree_kill) AS spree_kill, SUM(spree_rampage) AS spree_rampage, SUM(spree_dom) AS spree_dom, SUM(spree_uns) AS spree_uns, SUM(spree_god) AS spree_god FROM uts_player"));

  echo'
  <tr>
    <td class="smheading" align="center" colspan="2" width="250">Special/Multis</td>
    <td class="smheading" align="center" colspan="2" width="250">Sprees</td>
  </tr>
  <tr>
    <td class="dark" align="center" width="150">First Blood</td>
    <td class="grey" align="center" width="100">'.$sql_firstblood.'</td>
    <td class="dark" align="center" width="150">Killing Spree</td>
    <td class="grey" align="center" width="100">'.$sql_sprees[spree_kill].'</td>
  </tr>
  <tr>
    <td class="dark" align="center">Double Kills</td>
    <td class="grey" align="center">'.$sql_multis[spree_double].'</td>
    <td class="dark" align="center">Rampage</td>
    <td class="grey" align="center">'.$sql_sprees[spree_rampage].'</td>
  </tr>
  <tr>
    <td class="dark" align="center">Multi Kills</td>
    <td class="grey" align="center">'.$sql_multis[spree_multi].'</td>
    <td class="dark" align="center">Dominating</td>
    <td class="grey" align="center">'.$sql_sprees[spree_dom].'</td>
  </tr>
  <tr>
    <td class="dark" align="center">Ultra Kills</td>
    <td class="grey" align="center">'.$sql_multis[spree_ultra].'</td>
    <td class="dark" align="center">Unstoppable</td>
    <td class="grey" align="center">'.$sql_sprees[spree_uns].'</td>
  </tr>
  <tr>
    <td class="dark" align="center">Monster Kills</td>
    <td class="grey" align="center">'.$sql_multis[spree_monster].'</td>
    <td class="dark" align="center">Godlike</td>
    <td class="grey" align="center">'.$sql_sprees[spree_god].'</td>
  </tr>
</tbody></table>
<br>';

include('includes/weaponstats.php');
weaponstats(0, 0);

echo'<br>
<table class = "box" border="0" cellpadding="1" cellspacing="2" width="710">
  <tbody><tr>
    <td class="heading" align="center">Totals for Players</td>
  </tr>
</tbody></table>';

// NGStats Style Total Highs (All Time)
$sql_chighfrags = small_query("SELECT p.pid, pi.name, p.country, SUM(frags) AS frags , SUM(gametime) AS sumgametime, COUNT(matchid) AS mcount FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' GROUP BY pid, p.country HAVING sumgametime > 1800 ORDER BY frags DESC LIMIT 0,1");
$sql_chighdeaths = small_query("SELECT p.pid, pi.name, p.country, SUM(deaths) AS deaths , SUM(gametime) AS sumgametime, COUNT(matchid) AS mcount FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' GROUP BY pid, p.country HAVING sumgametime > 1800 ORDER BY deaths DESC LIMIT 0,1");
$sql_chighkills = small_query("SELECT p.pid, pi.name, p.country, SUM(kills) AS kills , SUM(gametime) AS sumgametime, COUNT(matchid) AS mcount FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' GROUP BY pid, p.country HAVING sumgametime > 1800 ORDER BY kills DESC LIMIT 0,1");
$sql_chighsuicides = small_query("SELECT p.pid, pi.name, p.country, SUM(suicides) AS suicides , SUM(gametime) AS sumgametime, COUNT(matchid) AS mcount FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' GROUP BY pid, p.country HAVING sumgametime > 1800 ORDER BY suicides DESC LIMIT 0,1");
$sql_chighteamkills = small_query("SELECT p.pid, pi.name, p.country, SUM(teamkills) AS teamkills , SUM(gametime) AS sumgametime, COUNT(matchid) AS mcount FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' GROUP BY pid, p.country HAVING sumgametime > 1800 ORDER BY teamkills DESC LIMIT 0,1");
$sql_chigheff = small_query("SELECT p.pid, pi.name, p.country, AVG(eff) AS eff , SUM(gametime) AS sumgametime, COUNT(matchid) AS mcount FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' GROUP BY pid, p.country HAVING sumgametime > 1800 ORDER BY eff DESC LIMIT 0,1");
$sql_chighaccuracy = small_query("SELECT p.pid, pi.name, p.country, AVG(accuracy) AS accuracy , SUM(gametime) AS sumgametime, COUNT(matchid) AS mcount FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' GROUP BY pid, p.country HAVING sumgametime > 1800 ORDER BY accuracy DESC LIMIT 0,1");
$sql_chighttl = small_query("SELECT p.pid, pi.name, p.country, AVG(ttl) AS ttl , SUM(gametime) AS sumgametime, COUNT(matchid) AS mcount FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' GROUP BY pid, p.country HAVING sumgametime > 1800 ORDER BY ttl DESC LIMIT 0,1");
$sql_chighflag_capture = small_query("SELECT p.pid, pi.name, p.country, SUM(flag_capture) AS flag_capture , SUM(gametime) AS sumgametime, COUNT(matchid) AS mcount FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' GROUP BY pid, p.country HAVING sumgametime > 1800 ORDER BY flag_capture DESC LIMIT 0,1");
$sql_chighflag_kill = small_query("SELECT p.pid, pi.name, p.country, SUM(flag_kill) AS flag_kill , SUM(gametime) AS sumgametime, COUNT(matchid) AS mcount FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' GROUP BY pid, p.country HAVING sumgametime > 1800 ORDER BY flag_kill DESC LIMIT 0,1");
$sql_chighdom_cp = small_query("SELECT p.pid, pi.name, p.country, SUM(dom_cp) AS dom_cp , SUM(gametime) AS sumgametime, COUNT(matchid) AS mcount FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' GROUP BY pid, p.country HAVING sumgametime > 1800 ORDER BY dom_cp DESC LIMIT 0,1");

$ass_obj_check = small_query("SELECT COUNT(id) AS idcount FROM uts_games WHERE gamename LIKE '%Assault%';") or die(mysql_error());
if ($ass_obj_check[idcount] > 0 ) {
	$sql_chighass_obj = small_query("SELECT p.pid, pi.name, p.country, SUM(ass_obj) AS ass_obj , SUM(gametime) AS sumgametime, COUNT(matchid) AS mcount FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' GROUP BY pid HAVING sumgametime > 1800 ORDER BY ass_obj DESC LIMIT 0,1");
} else {
	$sql_chighass_obj = "";
}

$sql_chighspree_monster = small_query("SELECT p.pid, pi.name, p.country, SUM(spree_monster) AS spree_monster , SUM(gametime) AS sumgametime, COUNT(matchid) AS mcount FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' GROUP BY pid, p.country HAVING sumgametime > 1800 ORDER BY spree_monster DESC LIMIT 0,1");
$sql_chighspree_god = small_query("SELECT p.pid, pi.name, p.country, SUM(spree_god) AS spree_god , SUM(gametime) AS sumgametime, COUNT(matchid) AS mcount FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' GROUP BY pid, p.country HAVING sumgametime > 1800 ORDER BY spree_god DESC LIMIT 0,1");
$sql_chighrank = small_query("SELECT p.pid, pi.name, p.country, SUM(rank) AS rank , SUM(gametime) AS sumgametime, COUNT(matchid) AS mcount FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' GROUP BY pid, p.country HAVING sumgametime > 1800 ORDER BY rank DESC LIMIT 0,1");

echo'<br>
<table class = "box" border="0" cellpadding="1" cellspacing="2" width="550">
  <tbody>
  <tr>
    <td class="medheading" colspan="5" align="center">Career Highs</td>
  </tr>
  <tr>
    <td class="smheading" align="center" width="200">Category</td>
    <td class="smheading" align="center" width="200">Player</td>
    <td class="smheading" align="center" width="50">Amount</td>
    <td class="smheading" align="center" width="50">Hours</td>
    <td class="smheading" align="center" width="50">Matches</td>
  </tr>';

if ($sql_chighfrags and $sql_chighfrags[frags]) {
  echo '
  <tr>
    <td class="dark" align="center">Frags</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_chighfrags[pid].'">'.FlagImage($sql_chighfrags['country'], false).' '.$sql_chighfrags[name].'</a></td>
    <td class="grey" align="center">'.$sql_chighfrags[frags].'</td>
    <td class="grey" align="center">'.sec2hour($sql_chighfrags[sumgametime]).'</td>
    <td class="grey" align="center">'.$sql_chighfrags[mcount].'</td>
  </tr>';
}
if ($sql_chighdeaths and $sql_chighdeaths[deaths]) {
  echo '
  <tr>
    <td class="dark" align="center">Deaths</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_chighdeaths[pid].'">'.FlagImage($sql_chighdeaths['country'], false).' '.$sql_chighdeaths[name].'</a></td>
    <td class="grey" align="center">'.$sql_chighdeaths[deaths].'</td>
    <td class="grey" align="center">'.sec2hour($sql_chighdeaths[sumgametime]).'</td>
    <td class="grey" align="center">'.$sql_chighdeaths[mcount].'</td>
  </tr>';
}
if ($sql_chighkills and $sql_chighkills[kills]) {
  echo '
  <tr>
    <td class="dark" align="center">Kills</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_chighkills[pid].'">'.FlagImage($sql_chighkills['country'], false).' '.$sql_chighkills[name].'</a></td>
    <td class="grey" align="center">'.$sql_chighkills[kills].'</td>
    <td class="grey" align="center">'.sec2hour($sql_chighkills[sumgametime]).'</td>
    <td class="grey" align="center">'.$sql_chighkills[mcount].'</td>
  </tr>';
}
if ($sql_chighsuicides and $sql_chighsuicides[suicides]) {
  echo '
  <tr>
    <td class="dark" align="center">Suicides</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_chighsuicides[pid].'">'.FlagImage($sql_chighsuicides['country'], false).' '.$sql_chighsuicides[name].'</a></td>
    <td class="grey" align="center">'.$sql_chighsuicides[suicides].'</td>
    <td class="grey" align="center">'.sec2hour($sql_chighsuicides[sumgametime]).'</td>
    <td class="grey" align="center">'.$sql_chighsuicides[mcount].'</td>
  </tr>';
}
if ($sql_chighteamkills and $sql_chighteamkills[teamkills]) {
  echo '
  <tr>
    <td class="dark" align="center">Team Kills</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_chighteamkills[pid].'">'.FlagImage($sql_chighteamkills['country'], false).' '.$sql_chighteamkills[name].'</a></td>
    <td class="grey" align="center">'.$sql_chighteamkills[teamkills].'</td>
    <td class="grey" align="center">'.sec2hour($sql_chighteamkills[sumgametime]).'</td>
    <td class="grey" align="center">'.$sql_chighteamkills[mcount].'</td>
  </tr>';
}
if ($sql_chigheff and $sql_chigheff[eff]) {
  echo '
  <tr>
    <td class="dark" align="center">Efficiency</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_chigheff[pid].'">'.FlagImage($sql_chigheff['country'], false).' '.$sql_chigheff[name].'</a></td>
    <td class="grey" align="center">'.get_dp($sql_chigheff[eff]).'</td>
    <td class="grey" align="center">'.sec2hour($sql_chigheff[sumgametime]).'</td>
    <td class="grey" align="center">'.$sql_chigheff[mcount].'</td>
  </tr>';
}
if ($sql_chighaccuracy and $sql_chighaccuracy[accuracy]) {
  echo '
  <tr>
    <td class="dark" align="center">Accuracy</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_chighaccuracy[pid].'">'.FlagImage($sql_chighaccuracy['country'], false).' '.$sql_chighaccuracy[name].'</a></td>
    <td class="grey" align="center">'.get_dp($sql_chighaccuracy[accuracy]).'</td>
    <td class="grey" align="center">'.sec2hour($sql_chighaccuracy[sumgametime]).'</td>
    <td class="grey" align="center">'.$sql_chighaccuracy[mcount].'</td>
  </tr>';
}
if ($sql_chighttl and $sql_chighttl[ttl]) {
  echo '
  <tr>
    <td class="dark" align="center">TTL</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_chighttl[pid].'">'.FlagImage($sql_chighttl['country'], false).' '.$sql_chighttl[name].'</a></td>
    <td class="grey" align="center">'.get_dp($sql_chighttl[ttl]).'</td>
    <td class="grey" align="center">'.sec2hour($sql_chighttl[sumgametime]).'</td>
    <td class="grey" align="center">'.$sql_chighttl[mcount].'</td>
  </tr>';
}
if ($sql_chighflag_capture and $sql_chighflag_capture[flag_capture]) {
  echo '
  <tr>
    <td class="dark" align="center">Flag Caps</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_chighflag_capture[pid].'">'.FlagImage($sql_chighflag_capture['country'], false).' '.$sql_chighflag_capture[name].'</a></td>
    <td class="grey" align="center">'.$sql_chighflag_capture[flag_capture].'</td>
    <td class="grey" align="center">'.sec2hour($sql_chighflag_capture[sumgametime]).'</td>
    <td class="grey" align="center">'.$sql_chighflag_capture[mcount].'</td>
  </tr>';
}
if ($sql_chighflag_kill and $sql_chighflag_kill[flag_kill]) {
  echo '
  <tr>
    <td class="dark" align="center">Flag Kills</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_chighflag_kill[pid].'">'.FlagImage($sql_chighflag_kill['country'], false).' '.$sql_chighflag_kill[name].'</a></td>
    <td class="grey" align="center">'.$sql_chighflag_kill[flag_kill].'</td>
    <td class="grey" align="center">'.sec2hour($sql_chighflag_kill[sumgametime]).'</td>
    <td class="grey" align="center">'.$sql_chighflag_kill[mcount].'</td>
  </tr>';
}
if ($sql_chighdom_cp and $sql_chighdom_cp[dom_cp]) {
  echo '
  <tr>
    <td class="dark" align="center">Domination Control Points</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_chighdom_cp[pid].'">'.FlagImage($sql_chighdom_cp['country'], false).' '.$sql_chighdom_cp[name].'</a></td>
    <td class="grey" align="center">'.$sql_chighdom_cp[dom_cp].'</td>
    <td class="grey" align="center">'.sec2hour($sql_chighdom_cp[sumgametime]).'</td>
    <td class="grey" align="center">'.$sql_chighdom_cp[mcount].'</td>
  </tr>';
}
if ($sql_chighass_obj and $sql_chighass_obj[ass_obj]) {
  echo '
  <tr>
    <td class="dark" align="center">Assault Objectives</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_chighass_obj[pid].'">'.FlagImage($sql_chighass_obj['country'], false).' '.$sql_chighass_obj[name].'</a></td>
    <td class="grey" align="center">'.$sql_chighass_obj[ass_obj].'</td>
    <td class="grey" align="center">'.sec2hour($sql_chighass_obj[sumgametime]).'</td>
    <td class="grey" align="center">'.$sql_chighass_obj[mcount].'</td>
  </tr>';
}
if ($sql_chighspree_monster and $sql_chighspree_monster[spree_monster]) {
  echo '
  <tr>
    <td class="dark" align="center">Monster Kills</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_chighspree_monster[pid].'">'.FlagImage($sql_chighspree_monster['country'], false).' '.$sql_chighspree_monster[name].'</a></td>
    <td class="grey" align="center">'.$sql_chighspree_monster[spree_monster].'</td>
    <td class="grey" align="center">'.sec2hour($sql_chighspree_monster[sumgametime]).'</td>
    <td class="grey" align="center">'.$sql_chighspree_monster[mcount].'</td>
  </tr>';
}
if ($sql_chighspree_god and $sql_chighspree_god[spree_god]) {
  echo '
  <tr>
    <td class="dark" align="center">Godlikes</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_chighspree_god[pid].'">'.FlagImage($sql_chighspree_god['country'], false).' '.$sql_chighspree_god[name].'</a></td>
    <td class="grey" align="center">'.$sql_chighspree_god[spree_god].'</td>
    <td class="grey" align="center">'.sec2hour($sql_chighspree_god[sumgametime]).'</td>
    <td class="grey" align="center">'.$sql_chighspree_god[mcount].'</td>
  </tr>';
}
if ($sql_chighrank and $sql_chighrank[rank]) {
  echo '
  <tr>
    <td class="dark" align="center">Rank Points</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_chighrank[pid].'">'.FlagImage($sql_chighrank['country'], false).' '.$sql_chighrank[name].'</a></td>
    <td class="grey" align="center">'.get_dp($sql_chighrank[rank]).'</td>
    <td class="grey" align="center">'.sec2hour($sql_chighrank[sumgametime]).'</td>
    <td class="grey" align="center">'.$sql_chighrank[mcount].'</td>
  </tr>';
}
echo '
</tbody></table>
<br>';

// NGStats Style Total Highs (Single Match)

$sql_mhighfrags = small_query("SELECT p.matchid, p.pid, pi.name, p.country, SUM(frags) AS frags , SUM(gametime) AS sumgametime FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' AND frags > 0 GROUP BY matchid, pid, country HAVING sumgametime > 600 ORDER BY frags DESC LIMIT 0,1");
$sql_mhighdeaths = small_query("SELECT p.matchid, p.pid, pi.name, p.country, SUM(deaths) AS deaths , SUM(gametime) AS sumgametime FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' AND deaths > 0 GROUP BY matchid, pid, country HAVING sumgametime > 600 ORDER BY deaths DESC LIMIT 0,1");
$sql_mhighkills = small_query("SELECT p.matchid, p.pid, pi.name, p.country, SUM(kills) AS kills , SUM(gametime) AS sumgametime FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' AND kills > 0 GROUP BY matchid, pid, country HAVING sumgametime > 600 ORDER BY kills DESC LIMIT 0,1");
$sql_mhighsuicides = small_query("SELECT p.matchid, p.pid, pi.name, p.country, SUM(suicides) AS suicides , SUM(gametime) AS sumgametime FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' AND suicides > 0 GROUP BY matchid, pid, country HAVING sumgametime > 600 ORDER BY suicides DESC LIMIT 0,1");
$sql_mhighteamkills = small_query("SELECT p.matchid, p.pid, pi.name, p.country, SUM(teamkills) AS teamkills , SUM(gametime) AS sumgametime FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' AND teamkills > 0 GROUP BY matchid, pid, country HAVING sumgametime > 600 ORDER BY teamkills DESC LIMIT 0,1");
$sql_mhigheff = small_query("SELECT p.matchid, p.pid, pi.name, p.country, SUM(eff) AS eff , SUM(gametime) AS sumgametime FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' AND eff > 0 GROUP BY matchid, pid, country HAVING sumgametime > 600 ORDER BY eff DESC LIMIT 0,1");
$sql_mhighaccuracy = small_query("SELECT p.matchid, p.pid, pi.name, p.country, SUM(accuracy) AS accuracy , SUM(gametime) AS sumgametime FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' AND accuracy > 0 GROUP BY matchid, pid, country HAVING sumgametime > 600 ORDER BY accuracy DESC LIMIT 0,1");
$sql_mhighttl = small_query("SELECT p.matchid, p.pid, pi.name, p.country, SUM(ttl) AS ttl , SUM(gametime) AS sumgametime FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' AND ttl > 0 GROUP BY matchid, pid, country HAVING sumgametime > 600 ORDER BY ttl DESC LIMIT 0,1");
$sql_mhighflag_capture = small_query("SELECT p.matchid, p.pid, pi.name, p.country, SUM(flag_capture) AS flag_capture , SUM(gametime) AS sumgametime FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' AND flag_capture > 0 GROUP BY matchid, pid, country HAVING sumgametime > 600 ORDER BY flag_capture DESC LIMIT 0,1");
$sql_mhighflag_kill = small_query("SELECT p.matchid, p.pid, pi.name, p.country, SUM(flag_kill) AS flag_kill , SUM(gametime) AS sumgametime FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' AND flag_kill > 0 GROUP BY matchid, pid, country HAVING sumgametime > 600 ORDER BY flag_kill DESC LIMIT 0,1");
$sql_mhighdom_cp = small_query("SELECT p.matchid, p.pid, pi.name, p.country, SUM(dom_cp) AS dom_cp , SUM(gametime) AS sumgametime FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' AND dom_cp > 0 GROUP BY matchid, pid, country HAVING sumgametime > 600 ORDER BY dom_cp DESC LIMIT 0,1");

$ass_obj_check = small_query("SELECT COUNT(id) AS idcount FROM uts_games WHERE gamename LIKE '%Assault%';") or die(mysql_error());
if ($ass_obj_check[idcount] > 0 ) {
	$sql_mhighass_obj = small_query("SELECT p.matchid, p.pid, pi.name, p.country, SUM(ass_obj) AS ass_obj , SUM(gametime) AS sumgametime FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' AND ass_obj > 0 GROUP BY matchid, pid, country HAVING sumgametime > 600 ORDER BY ass_obj DESC LIMIT 0,1");
} else {
	$sql_mhighass_obj = "";
}

$sql_mhighspree_monster = small_query("SELECT p.matchid, p.pid, pi.name, p.country, SUM(spree_monster) AS spree_monster , SUM(gametime) AS sumgametime FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' AND spree_monster > 0 GROUP BY matchid, pid, country HAVING sumgametime > 600 ORDER BY spree_monster DESC LIMIT 0,1");
$sql_mhighspree_god = small_query("SELECT p.matchid, p.pid, pi.name, p.country, SUM(spree_god) AS spree_god , SUM(gametime) AS sumgametime FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' AND spree_god > 0 GROUP BY matchid, pid, country HAVING sumgametime > 600 ORDER BY spree_god DESC LIMIT 0,1");
$sql_mhighrank = small_query("SELECT p.matchid, p.pid, pi.name, p.country, SUM(rank) AS rank , SUM(gametime) AS sumgametime FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.banned <> 'Y' AND rank > 0 GROUP BY matchid, pid, country HAVING sumgametime > 600 ORDER BY rank DESC LIMIT 0,1");

echo'<table class = "box" border="0" cellpadding="1" cellspacing="2" width="480">
  <tbody>
  <tr>
    <td class="medheading" colspan="4" align="center">Match Highs</td>
  </tr>
  <tr>
    <td class="smheading" align="center" width="175">Category</td>
    <td class="smheading" align="center" width="175">Player</td>
    <td class="smheading" align="center" width="65">Amount</td>
    <td class="smheading" align="center" width="65">Match</td>
  </tr>';

if ($sql_mhighfrags) {
  echo '
  <tr>
    <td class="dark" align="center">Frags</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_mhighfrags[pid].'">'.FlagImage($sql_mhighfrags['country'], false).' '.$sql_mhighfrags[name].'</a></td>
    <td class="grey" align="center">'.$sql_mhighfrags[frags].'</td>
    <td class="grey" align="center"><a class="greyhuman" href="./?p=match&amp;mid='.$sql_mhighfrags[matchid].'">(click)</a></td>
  </tr>';
}
if ($sql_mhighdeaths) {
  echo '
  <tr>
    <td class="dark" align="center">Deaths</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_mhighdeaths[pid].'">'.FlagImage($sql_mhighdeaths['country'], false).' '.$sql_mhighdeaths[name].'</a></td>
    <td class="grey" align="center">'.$sql_mhighdeaths[deaths].'</td>
    <td class="grey" align="center"><a class="greyhuman" href="./?p=match&amp;mid='.$sql_mhighdeaths[matchid].'">(click)</a></td>
  </tr>';
}
if ($sql_mhighkills) {
  echo '
  <tr>
    <td class="dark" align="center">Kills</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_mhighkills[pid].'">'.FlagImage($sql_mhighkills['country'], false).' '.$sql_mhighkills[name].'</a></td>
    <td class="grey" align="center">'.$sql_mhighkills[kills].'</td>
    <td class="grey" align="center"><a class="greyhuman" href="./?p=match&amp;mid='.$sql_mhighkills[matchid].'">(click)</a></td>
  </tr>';
}
if ($sql_mhighsuicides) {
  echo '
  <tr>
    <td class="dark" align="center">Suicides</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_mhighsuicides[pid].'">'.FlagImage($sql_mhighsuicides['country'], false).' '.$sql_mhighsuicides[name].'</a></td>
    <td class="grey" align="center">'.$sql_mhighsuicides[suicides].'</td>
    <td class="grey" align="center"><a class="greyhuman" href="./?p=match&amp;mid='.$sql_mhighsuicides[matchid].'">(click)</a></td>
  </tr>';
}
if ($sql_mhighteamkills) {
  echo '
  <tr>
    <td class="dark" align="center">Team Kills</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_mhighteamkills[pid].'">'.FlagImage($sql_mhighteamkills['country'], false).' '.$sql_mhighteamkills[name].'</a></td>
    <td class="grey" align="center">'.$sql_mhighteamkills[teamkills].'</td>
    <td class="grey" align="center"><a class="greyhuman" href="./?p=match&amp;mid='.$sql_mhighteamkills[matchid].'">(click)</a></td>
  </tr>';
}
if ($sql_mhigheff) {
  echo '
  <tr>
    <td class="dark" align="center">Efficiency</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_mhigheff[pid].'">'.FlagImage($sql_mhigheff['country'], false).' '.$sql_mhigheff[name].'</a></td>
    <td class="grey" align="center">'.get_dp($sql_mhigheff[eff]).'</td>
    <td class="grey" align="center"><a class="greyhuman" href="./?p=match&amp;mid='.$sql_mhigheff[matchid].'">(click)</a></td>
  </tr>';
}
if ($sql_mhighaccuracy) {
  echo '
  <tr>
    <td class="dark" align="center">Accuracy</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_mhighaccuracy[pid].'">'.FlagImage($sql_mhighaccuracy['country'], false).' '.$sql_mhighaccuracy[name].'</a></td>
    <td class="grey" align="center">'.get_dp($sql_mhighaccuracy[accuracy]).'</td>
    <td class="grey" align="center"><a class="greyhuman" href="./?p=match&amp;mid='.$sql_mhighaccuracy[matchid].'">(click)</a></td>
  </tr>';
}
if ($sql_mhighttl) {
  echo '
  <tr>
    <td class="dark" align="center">TTL</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_mhighttl[pid].'">'.FlagImage($sql_mhighttl['country'], false).' '.$sql_mhighttl[name].'</a></td>
    <td class="grey" align="center">'.get_dp($sql_mhighttl[ttl]).'</td>
    <td class="grey" align="center"><a class="greyhuman" href="./?p=match&amp;mid='.$sql_mhighttl[matchid].'">(click)</a></td>
  </tr>';
}
if ($sql_mhighflag_capture) {
  echo '
  <tr>
    <td class="dark" align="center">Flag Caps</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_mhighflag_capture[pid].'">'.FlagImage($sql_mhighflag_capture['country'], false).' '.$sql_mhighflag_capture[name].'</a></td>
    <td class="grey" align="center">'.$sql_mhighflag_capture[flag_capture].'</td>
    <td class="grey" align="center"><a class="greyhuman" href="./?p=match&amp;mid='.$sql_mhighflag_capture[matchid].'">(click)</a></td>
  </tr>';
}
if ($sql_mhighflag_kill) {
  echo '
  <tr>
    <td class="dark" align="center">Flag Kills</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_mhighflag_kill[pid].'">'.FlagImage($sql_mhighflag_kill['country'], false).' '.$sql_mhighflag_kill[name].'</a></td>
    <td class="grey" align="center">'.$sql_mhighflag_kill[flag_kill].'</td>
    <td class="grey" align="center"><a class="greyhuman" href="./?p=match&amp;mid='.$sql_mhighflag_kill[matchid].'">(click)</a></td>
  </tr>';
}
if ($sql_mhighdom_cp) {
  echo '
  <tr>
    <td class="dark" align="center">Domination Control Points</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_mhighdom_cp[pid].'">'.FlagImage($sql_mhighdom_cp['country'], false).' '.$sql_mhighdom_cp[name].'</a></td>
    <td class="grey" align="center">'.$sql_mhighdom_cp[dom_cp].'</td>
    <td class="grey" align="center"><a class="greyhuman" href="./?p=match&amp;mid='.$sql_mhighdom_cp[matchid].'">(click)</a></td>
  </tr>';
}
if ($sql_mhighass_obj) {
  echo '
  <tr>
    <td class="dark" align="center">Assault Objectives</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_mhighass_obj[pid].'">'.FlagImage($sql_mhighass_obj['country'], false).' '.$sql_mhighass_obj[name].'</a></td>
    <td class="grey" align="center">'.$sql_mhighass_obj[ass_obj].'</td>
    <td class="grey" align="center"><a class="greyhuman" href="./?p=match&amp;mid='.$sql_mhighass_obj[matchid].'">(click)</a></td>
  </tr>';
}
if ($sql_mhighspree_monster) {
  echo '
  <tr>
    <td class="dark" align="center">Monster Kills</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_mhighspree_monster[pid].'">'.FlagImage($sql_mhighspree_monster['country'], false).' '.$sql_mhighspree_monster[name].'</a></td>
    <td class="grey" align="center">'.$sql_mhighspree_monster[spree_monster].'</td>
    <td class="grey" align="center"><a class="greyhuman" href="./?p=match&amp;mid='.$sql_mhighspree_monster[matchid].'">(click)</a></td>
  </tr>';
}
if ($sql_mhighspree_god) {
  echo '
  <tr>
    <td class="dark" align="center">Godlikes</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_mhighspree_god[pid].'">'.FlagImage($sql_mhighspree_god['country'], false).' '.$sql_mhighspree_god[name].'</a></td>
    <td class="grey" align="center">'.$sql_mhighspree_god[spree_god].'</td>
    <td class="grey" align="center"><a class="greyhuman" href="./?p=match&amp;mid='.$sql_mhighspree_god[matchid].'">(click)</a></td>
  </tr>';
}
if ($sql_mhighrank) {
  echo '
  <tr>
    <td class="dark" align="center">Rank Points</td>
    <td nowrap class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$sql_mhighrank[pid].'">'.FlagImage($sql_mhighrank['country'], false).' '.$sql_mhighrank[name].'</a></td>
    <td class="grey" align="center">'.get_dp($sql_mhighrank[rank]).'</td>
    <td class="grey" align="center"><a class="greyhuman" href="./?p=match&amp;mid='.$sql_mhighrank[matchid].'">(click)</a></td>
  </tr>';
}

echo '
</tbody></table>
<br>
<table class = "box" border="0" cellpadding="1" cellspacing="2" width="480">
  <tbody>
<tr>
    <td class="medheading" colspan="4" align="center">Weapon Career Highs</td>
  </tr>
  <tr>
    <td class="smheading" align="center" width="175">Category</td>
    <td class="smheading" align="center" width="175">Player</td>
    <td class="smheading" align="center" width="65">Kills</td>
    <td class="smheading" align="center" width="65">Matches</td>
  </tr>
';


$sql_mweapons = "SELECT id, name, image FROM uts_weapons WHERE hide <> 'Y' ORDER BY sequence, id ASC";
$q_mweapons = mysql_query($sql_mweapons) or die(mysql_error());

while ($r_mweapons = mysql_fetch_array($q_mweapons)) {
	$wid =  $r_mweapons[id];

	$sql_mweaponsl = "SELECT w.pid AS playerid, pi.name AS name, pi.country AS country, SUM(w.kills) as kills, COUNT(DISTINCT w.matchid) AS mcount
    FROM uts_weaponstats AS w
    LEFT JOIN uts_pinfo AS pi ON w.pid = pi.id
    WHERE w.weapon = '$wid' AND w.pid > 0 AND w.matchid <> 0 AND pi.banned <> 'Y'
    GROUP BY w.pid
    ORDER BY kills DESC LIMIT 0,1";

	$q_mweaponsl = mysql_query($sql_mweaponsl) or die(mysql_error());

	while ($r_mweaponsl = mysql_fetch_array($q_mweaponsl)) {
    echo '<tr>
      <td class="dark" align="center">'.$r_mweapons[name].'</td>
      <td class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$r_mweaponsl[playerid].'">'.FlagImage($r_mweaponsl[country], false).' '.$r_mweaponsl[name].'</a></td>
      <td class="grey" align="center">'.$r_mweaponsl[kills].'</td>
      <td class="grey" align="center">'.$r_mweaponsl[mcount].'</td>
    </tr>';
	}
}

echo '</tbody></table>
<br>';

// NGStats Style Weapon Highs (All Time)

echo '<table class = "box" border="0" cellpadding="1" cellspacing="2" width="480">
<tbody>
  <tr>
    <td class="medheading" colspan="4" align="center">Weapon Match Highs</td>
  </tr>
  <tr>
    <td class="smheading" align="center" width="175">Category</td>
    <td class="smheading" align="center" width="175">Player</td>
    <td class="smheading" align="center" width="65">Kills</td>
    <td class="smheading" align="center" width="65">Match</td>
  </tr>';

$sql_mweapons = "SELECT id, name, image FROM uts_weapons WHERE hide <> 'Y' ORDER BY sequence, id ASC";
$q_mweapons = mysql_query($sql_mweapons) or die(mysql_error());

while ($r_mweapons = mysql_fetch_array($q_mweapons)) {
	$wid =  $r_mweapons[id];
	$sql_mweaponsl = "SELECT w.matchid, w.pid AS playerid, pi.name AS name, pi.country AS country, w.kills FROM uts_weaponstats AS w LEFT JOIN uts_pinfo AS pi ON w.pid = pi.id WHERE w.weapon = '$wid' AND w.pid > 0 AND w.matchid > 0 AND pi.banned <> 'Y' ORDER BY w.kills DESC LIMIT 0,1";
	$q_mweaponsl = mysql_query($sql_mweaponsl) or die(mysql_error());

	while ($r_mweaponsl = mysql_fetch_array($q_mweaponsl)) {
    echo '<tr>
      <td class="dark" align="center">'.$r_mweapons[name].'</td>
      <td class="greyhuman" align="center"><a class="greyhuman" href="./?p=pinfo&amp;pid='.$r_mweaponsl[playerid].'">'.FlagImage($r_mweaponsl[country], false).' '.$r_mweaponsl[name].'</a></td>
      <td class="grey" align="center">'.$r_mweaponsl[kills].'</td>
      <td class="grey" align="center"><a class="greyhuman" href="./?p=match&amp;mid='.$r_mweaponsl[matchid].'">(click)</a></td>
    </tr>';
	}
}

// NGStats Style Weapon Highs (Single Match)
echo'</tbody></table>';

?>
