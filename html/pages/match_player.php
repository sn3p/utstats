<?php

$mid = preg_replace('/\D/', '', $_GET[mid]);
$pid = preg_replace('/\D/', '', $_GET[pid]);

$r_infos = small_query("SELECT p.playerid, p.country, pi.name, pi.banned, p.gid, g.name AS gamename
  FROM uts_player p, uts_pinfo pi, uts_games g
  WHERE p.gid = g.id AND p.pid = pi.id AND p.pid = '$pid' AND matchid = '$mid' LIMIT 0,1;");

if (!$r_infos) {
  echo "Unable to retrieve data!";
  include("includes/footer.php");
  exit;
}

if ($r_infos['banned'] == 'Y') {
  if (isset($is_admin) and $is_admin) {
    echo "Warning: Banned player - Admin override<br>";
  } else {
    echo "Sorry, this player has been banned!";
    include("includes/footer.php");
    exit;
  }
}

$playerid = $r_infos['playerid'];
$playername = $r_infos['name'];
$country = $r_infos['country'];
$gamename = $r_infos['gamename'];
$gid = $r_infos['gid'];

echo '
<table class="zebra box" border="0" cellpadding="0" cellspacing="0" width="700">
<tbody>
  <tr>
    <th class="heading" align="center">Individual Match Stats for</th>
  </tr>
  <tr>
    <th class="smheading">
      <div class="player-name">
        <a href="./?p=pinfo&amp;pid='.$pid.'">'.FlagImage($country) .' '. htmlentities($playername) .'</a>
      </div>
      <div>'.
        RankImageOrText($pid, $playername, NULL, $gid, $gamename, true, '%IT% in %GN% with %RP% ranking points.')
      .'</div>
      <a class="navCTA" href="./?p=pinfo&amp;pid='.$pid.'" role="button">Player page</a>
      <a class="navCTA" href="?p=pinfo&amp;pid='.$pid.'&amp;togglewatch=1&amp;noheader=1" role="button">';

      if (PlayerOnWatchlist($pid)) {
        echo 'Remove from Watchlist';
      } else {
        echo 'Add to Watchlist';
      };

echo '</a>
    </th>
  </tr>
</tbody></table><br>';

// Get Summary Info
include("pages/match_info_server.php");

echo '
<br>
<table class="zebra box" border="0" cellpadding="0" cellspacing="0" width="700">
<tbody>
  <tr>
    <th class="heading" colspan="8" align="center">Game Summary</th>
  </tr>
  <tr>
    <th class="smheading" align="center" width="40">Frags</th>
    <th class="smheading" align="center" width="40">Kills</th>
    <th class="smheading" align="center" width="50">Deaths</th>
    <th class="smheading" align="center" width="60">Suicides</th>
    <th class="smheading" align="center" width="70">Efficiency</th>
    <th class="smheading" align="center" width="50">Accuracy</th>
    <th class="smheading" align="center" width="50">Avg TTL</th>
    <th class="smheading" align="center" width="50">Time</th>
  </tr>';

  $r_gsumm = zero_out(small_query("SELECT gamescore, frags, SUM(frags+suicides) AS kills, deaths, suicides, teamkills, eff, accuracy, ttl, gametime, spree_kill, spree_rampage, spree_dom, spree_uns, spree_god
    FROM uts_player
    WHERE matchid = $mid AND pid = '$pid'
    GROUP BY pid, gamescore, frags, deaths, suicides, teamkills, eff, accuracy, ttl, gametime, spree_kill, spree_rampage, spree_dom, spree_uns, spree_god"));

echo '
  <tr>
    <td align="center">'.$r_gsumm[frags].'</td>
    <td align="center">'.$r_gsumm[kills].'</td>
    <td align="center">'.$r_gsumm[deaths].'</td>
    <td align="center">'.$r_gsumm[suicides].'</td>
    <td align="center">'.$r_gsumm[eff].'</td>
    <td align="center">'.$r_gsumm[accuracy].'</td>
    <td align="center">'.$r_gsumm[ttl].'</td>
    <td align="center">'.GetMinutes($r_gsumm[gametime]).'</td>
  </tr>
</tbody>
</table>
<br>
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
  </tr>';

$r_gsumm = zero_out(small_query("SELECT spree_double, spree_multi, spree_ultra, spree_monster, spree_kill, spree_rampage, spree_dom, spree_uns, spree_god
  FROM uts_player
  WHERE matchid = $mid AND pid = '$pid'
  GROUP BY pid, spree_double, spree_multi, spree_ultra, spree_monster, spree_kill, spree_rampage, spree_dom, spree_uns, spree_god"));

$sql_firstblood = small_query("SELECT firstblood FROM uts_match WHERE id = $mid");

if ($sql_firstblood[firstblood] == $pid) {
  $firstblood = "Yes";
} else {
  $firstblood = "No";
}

echo '
  <tr>
    <td align="center">'.$firstblood.'</td>
    <td align="center">'.$r_gsumm[spree_double].'</td>
    <td align="center">'.$r_gsumm[spree_multi].'</td>
    <td align="center">'.$r_gsumm[spree_ultra].'</td>
    <td align="center">'.$r_gsumm[spree_monster].'</td>
    <td align="center">'.$r_gsumm[spree_kill].'</td>
    <td align="center">'.$r_gsumm[spree_rampage].'</td>
    <td align="center">'.$r_gsumm[spree_dom].'</td>
    <td align="center">'.$r_gsumm[spree_uns].'</td>
    <td align="center">'.$r_gsumm[spree_god].'</td>
  </tr>
</tbody>
</table>
<br>';

include('includes/weaponstats.php');
weaponstats($mid, $pid);

$r_pings = small_query("SELECT lowping, avgping, highping FROM uts_player WHERE pid = $pid  and matchid = $mid and lowping > 0");

if ($r_pings and $r_pings['lowping']) {
  echo '<br>
  <table class="zebra box" border="0" cellpadding="0" cellspacing="0">
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
    <td align="center">'.ceil($r_pings['lowping']).'</td>
    <td align="center">'.ceil($r_pings['avgping']).'</td>
    <td align="center">'.ceil($r_pings['highping']).'</td>
  </tr>
  </tbody></table>';
}

?>
