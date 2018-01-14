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
$sql_km = "SELECT killer, victim, kills
           FROM uts_killsmatrix
           WHERE matchid = $mid;";

$q_km = mysqli_query($GLOBALS["___mysqli_link"], $sql_km) or die(mysqli_error($GLOBALS["___mysqli_link"]));
while ($r_km = mysqli_fetch_array($q_km)) {
  $km[intval($r_km['killer'])][intval($r_km['victim'])] = $r_km['kills'];
}

// No matrix: bye
if (!isset($km)) return;

// Are we processing a teamgame?
$qm_teamgame = small_query("SELECT teamgame FROM uts_match WHERE id = '$mid';");
$teamgame = $qm_teamgame['teamgame'];
$teamgame = ($teamgame == 'False') ? false : true;

// Get the players of this match
$sql_players = "  SELECT  p.pid,
                  p.playerid,
                  pi.name,
                  pi.country,
                  pi.banned,
                  p.team,
                  p.suicides
            FROM    uts_player p,
                  uts_pinfo pi
            WHERE    (p.pid = pi.id)
              AND  matchid = '$mid'
            ORDER  BY  team ASC,
                  gamescore DESC;";
$q_players = mysqli_query($GLOBALS["___mysqli_link"], $sql_players) or die(mysqli_error($GLOBALS["___mysqli_link"]));
while ($r_players = mysqli_fetch_array($q_players)) {
  $players[intval($r_players['playerid'])] = array(  'pid'     => $r_players['pid'],
                                    'name'     => $r_players['name'],
                                    'country'  => $r_players['country'],
                                    'banned'    => $r_players['banned'],
                                    'suicides'  => intval($r_players['suicides']),
                                    'team'     => intval($r_players['team']));
}

// Table header
$extra = $teamgame ? 3 : 2;

echo '
<table class="zebra" border="0" cellpadding="0" cellspacing="0" width="700">
  <tbody><tr>
    <th class="heading" colspan="'. (count($players) + $extra) .'" align="center">Kills Match Up</th>
  </tr>
  <tr>
    <th class="smheading" colspan="'.$extra.'" rowspan="'.$extra.'" align="center"><center><img src="assets/images/arrow.png"></th>
  </tr>
  <tr>';

// Victims
foreach($players as $player) {
  echo '<th align="center" class="tooltip" title="'.($player['name']).'" href="?p=matchp&amp;mid='. $mid .'&amp;pid='. urlencode($player['pid']). '">
  <div class="vertical">';
  if (strlen($player['name']) > 10) {
    echo substr($player['name'], 0, 10);
  } else {
    echo $player['name'] ;
  };
  echo '</div>
  </th>';
}
echo '</tr>
<tr>';

// Team colors victims
if ($teamgame) {
  foreach($players as $player) {
    switch($player['team']) {
      case 0: $teamcolor = 'redteamb'; break;
      case 1: $teamcolor = 'blueteamb'; break;
      case 2: $teamcolor = 'greenteamb'; break;
      case 3: $teamcolor = 'goldteamb'; break;
    }
    echo '<td class="'. $teamcolor .'" align="center" width="25" height="25">
      <img src="assets/images/victim.png" height="15">
    </td>';
  }
  echo '</tr>';
}

// Killer rows
$first = true;
$i = 0;

foreach($players as $kid => $killer) {
  if ($killer['banned'] == 'Y') continue;
  $i++;
  echo '<tr class="clickableRow" href="?p=matchp&amp;mid='. $mid .'&amp;pid='. urlencode($killer['pid']). '">';
  if ($first) echo'<td class="smheading" rowspan="'. count($players) .'" align="center" width="20"> <img src="assets/images/xhair.png"> </td>';
  echo '<td nowrap align="left" style="width: 220px;">';
  echo '<a href="?p=matchp&amp;mid='. $mid .'&amp;pid='. urlencode($killer['pid']). '">'.
      FormatPlayerName($killer['country'], $killer['pid'], $killer['name'], $gid, $gamename) .'&nbsp;</a></td>';
  if ($teamgame) {
    switch($killer['team']) {
      case 0: $teamcolor = 'redteamb'; break;
      case 1: $teamcolor = 'blueteamb'; break;
      case 2: $teamcolor = 'greenteamb'; break;
      case 3: $teamcolor = 'goldteamb'; break;
    }
    echo '<td class="'. $teamcolor .'" align="center" width="30" height="25"><img src="assets/images/xhair.png" height="15"></td>';
  }
  foreach($players as $vid => $victim) {
    $class = ($kid == $vid) ? 'suicide' : 'killCell';
    //if  ($i % 2) $class .= '2';
    echo '<td class="'.$class.' tooltip" title="'.($victim['name']).'" href="?p=matchp&amp;mid='. $mid .'&amp;pid='. urlencode($player['pid']). '" align="center" width="20">';
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
