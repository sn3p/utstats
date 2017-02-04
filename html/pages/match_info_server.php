<?php
echo'
<table class="box" border="0" cellpadding="1" cellspacing="2">
  <tbody><tr>
    <td class="heading" colspan="6" align="center">Totals for This Match</td>
  </tr>
    <tr>
    <td class="smheading" align="center" width="45">Team Score</td>
    <td class="smheading" align="center" width="50">Player Score</td>
    <td class="smheading" align="center" width="45">Frags</td>
    <td class="smheading" align="center" width="45">Kills</td>
    <td class="smheading" align="center" width="50">Deaths</td>
    <td class="smheading" align="center" width="60">Suicides</td>
  </tr>';

// Get Summary Info
$teamscore = small_query("SELECT SUM(t0score + t1score + t2score + t3score) AS result FROM uts_match WHERE id = $mid");
$playerscore = small_query("SELECT SUM(gamescore) AS result FROM uts_player WHERE matchid = $mid");
$fragcount = small_query("SELECT SUM(frags) AS result FROM uts_match WHERE id = $mid");
$killcount = small_query("SELECT SUM(kills) AS result FROM uts_match WHERE id = $mid");
$deathcount = small_query("SELECT SUM(deaths) AS result FROM uts_match WHERE id = $mid");
$suicidecount = small_query("SELECT SUM(suicides) AS result FROM uts_match WHERE id = $mid");

echo'
  <tr>
    <td class="smheading" align="center" width="45">'.$teamscore[result].'</td>
    <td class="smheading" align="center" width="50">'.$playerscore[result].'</td>
    <td class="smheading" align="center" width="45">'.$fragcount[result].'</td>
    <td class="smheading" align="center" width="45">'.$killcount[result].'</td>
    <td class="smheading" align="center" width="50">'.$deathcount[result].'</td>
    <td class="smheading" align="center" width="60">'.$suicidecount[result].'</td>
  </tr>';

// Teamgame? Then show score
if ($teamgame) {
	echo '
  <tr>
    <td class="heading" align="center" valign="middle" colspan="6">';
	echo '
	      Score:';
	if ($r_info[t0]) {
	      echo '
	      '.$r_info[t0score];
	}
	if ($r_info[t1]) {
	      echo '
	      - '.$r_info[t1score];
	}
	if ($r_info[t2]) {
	      echo '
	      - '.$r_info[t2score];
	}
	if ($r_info[t3]) {
	      echo '
	      - '.$r_info[t3score];
	}
	echo '
    </td>
  </tr>';
}

echo '
</tbody></table>
<br>
<table class = "box" border="0" cellpadding="1" cellspacing="2" width="720">
  <tbody><tr>
    <td class="heading" colspan="4" align="center">Unreal Tournament Match Stats</td>
  </tr>';

$matchinfo = small_query("SELECT m.time, m.servername, g.name AS gamename, m.gamename AS real_gamename, m.gid, m.mapname, m.mapfile, m.serverinfo, m.gameinfo, m.mutators, m.serverip FROM uts_match AS m, uts_games AS g WHERE m.gid = g.id AND m.id = $mid");
$matchdate = mdate($matchinfo[time]);
$gamename = $matchinfo[gamename];
$real_gamename = $matchinfo[real_gamename];
$gid = $matchinfo[gid];

$mapname = un_ut($matchinfo[mapfile]);
$mappic = strtolower("images/maps/".$mapname.".jpg");

if (file_exists($mappic)) {
} else {
   $mappic = ("images/maps/blank.jpg");
}

  $myurl = urlencode($mapname);

  echo'
  <tr>
    <td class="dark" align="center" width="110">Match Date</td>
    <td class="grey" align="center">'.$matchdate.'</td>
    <td class="dark" align="center" width="110">Server</td>
    <td class="grey" align="center" width="146"><a class="grey" href="./?p=sinfo&amp;serverip='.$matchinfo[serverip].'">'.$matchinfo[servername].'</a></td>
  </tr>
  <tr>
    <td class="dark" align="center">Match Type</td>
    <td class="grey" align="center">'.$gamename.'</td>
    <td class="dark" align="center">Map Name</td>
    <td class="greyhuman" align="center"><a class="grey" href="./?p=minfo&amp;map='.$myurl.'">'.$matchinfo[mapname].'</a></td>
  </tr>
  <tr>
    <td class="dark" align="center">Server Info</td>
    <td class="grey" align="center">'.$matchinfo[serverinfo].'</td>
    <td class="dark" align="center" rowspan="4" colspan="2"><img border="0" alt="'.$mapname.'" title="'.$mapname.'" src="'.$mappic.'"></td>
  </tr>
  <tr>
    <td class="dark" align="center">Game Info</td>
    <td class="grey" align="center">'.$matchinfo[gameinfo].'</td>
  </tr>
  <tr>
    <td class="dark" align="center">Mutators</td>
    <td class="grey" align="center">'.$matchinfo[mutators].'</td>
  </tr>
</tbody></table>
<br>';
?>
