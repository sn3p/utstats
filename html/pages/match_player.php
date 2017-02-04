<?php
$mid = preg_replace('/\D/', '', $_GET[mid]);
$pid = preg_replace('/\D/', '', $_GET[pid]);

$r_infos = small_query("SELECT p.playerid, p.country, pi.name, pi.banned, p.gid, g.name AS gamename
  FROM uts_player p, uts_pinfo pi, uts_games g
  WHERE p.gid = g.id AND p.pid = pi.id AND p.pid = '$pid'
  AND matchid = '$mid'
  LIMIT 0,1;");

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

echo'
<table class = "box" border="0" cellpadding="1" cellspacing="2" width="720">
  <tbody><tr>
    <td class="heading" align="center">Individual Match Stats for
      <a href="./?p=pinfo&amp;pid='.$pid.'">'.FlagImage($country) .' '. htmlentities($playername) .'</a>
      <span style="font-size: 70%">'. RankImageOrText($pid, $playername, NULL, $gid, $gamename, true, '(%IT% in %GN% with %RP% ranking points)') .'</span>
    </td>
  </tr>
</tbody></table>
<br>';

// Get Summary Info
include("pages/match_info_server.php");

echo '
<br>
<table class = "box" border="0" cellpadding="0" cellspacing="2" width="400">
  <tbody><tr>
    <td class="heading" colspan="8" align="center">Game Summary</td>
  </tr>
  <tr>
    <td class="smheading" align="center" width="40">Frags</td>
    <td class="smheading" align="center" width="40">Kills</td>
    <td class="smheading" align="center" width="50">Deaths</td>
    <td class="smheading" align="center" width="60">Suicides</td>
    <td class="smheading" align="center" width="70">Efficiency</td>
    <td class="smheading" align="center" width="50">Accuracy</td>
    <td class="smheading" align="center" width="50">Avg TTL</td>
    <td class="smheading" align="center" width="50">Time</td>
  </tr>';

$r_gsumm = zero_out(small_query("SELECT gamescore, frags, SUM(frags+suicides) AS kills, deaths, suicides, teamkills, eff, accuracy, ttl, gametime, spree_kill, spree_rampage, spree_dom, spree_uns, spree_god
FROM uts_player
WHERE matchid = $mid AND pid = '$pid'
GROUP BY pid, gamescore, frags, deaths, suicides, teamkills, eff, accuracy, ttl, gametime, spree_kill, spree_rampage, spree_dom, spree_uns, spree_god"));

  echo'
  <tr>
    <td class="grey" align="center">'.$r_gsumm[frags].'</td>
    <td class="grey" align="center">'.$r_gsumm[kills].'</td>
    <td class="grey" align="center">'.$r_gsumm[deaths].'</td>
    <td class="grey" align="center">'.$r_gsumm[suicides].'</td>
    <td class="grey" align="center">'.$r_gsumm[eff].'</td>
    <td class="grey" align="center">'.$r_gsumm[accuracy].'</td>
    <td class="grey" align="center">'.$r_gsumm[ttl].'</td>
    <td class="grey" align="center">'.GetMinutes($r_gsumm[gametime]).'</td>
  </tr>';

echo'
</tbody></table>
<br>
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

$r_gsumm = zero_out(small_query("SELECT spree_double, spree_multi, spree_ultra, spree_monster, spree_kill, spree_rampage, spree_dom, spree_uns, spree_god
FROM uts_player
WHERE matchid = $mid AND pid = '$pid'
GROUP BY pid, spree_double, spree_multi, spree_ultra, spree_monster, spree_kill, spree_rampage, spree_dom, spree_uns, spree_god"));

$sql_firstblood = small_query("SELECT firstblood FROM uts_match WHERE id = $mid");

IF ($sql_firstblood[firstblood] == $pid) {
  $firstblood = "Yes";
} else {
  $firstblood = "No";
}

echo'
  <tr>
    <td class="grey" align="center">'.$firstblood.'</td>
    <td class="grey" align="center">'.$r_gsumm[spree_double].'</td>
    <td class="grey" align="center">'.$r_gsumm[spree_multi].'</td>
    <td class="grey" align="center">'.$r_gsumm[spree_ultra].'</td>
    <td class="grey" align="center">'.$r_gsumm[spree_monster].'</td>
    <td class="grey" align="center">'.$r_gsumm[spree_kill].'</td>
    <td class="grey" align="center">'.$r_gsumm[spree_rampage].'</td>
    <td class="grey" align="center">'.$r_gsumm[spree_dom].'</td>
    <td class="grey" align="center">'.$r_gsumm[spree_uns].'</td>
    <td class="grey" align="center">'.$r_gsumm[spree_god].'</td>
  </tr>
  </tbody></table>
<br>';

include('includes/weaponstats.php');
weaponstats($mid, $pid);

$r_pings = small_query("SELECT lowping, avgping, highping FROM uts_player WHERE pid = $pid  and matchid = $mid and lowping > 0");
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

?>
