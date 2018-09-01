<?php
global $s_lastvisit;

echo'
<table class = "zebra box" border="0" cellpadding="0" cellspacing="0" width="900">
  <tbody>
  <tr>
    <th class="heading" align="center" colspan="12">Your Watchlist</th>
  </tr>
  <tr>
    <th class="smheading" colspan="12">
      <form name="playersearch" method="post" action="./?p=psearch">
        <div class="darksearch">
          <span>
            <input type="text" class="search square" placeholder="Search player..." name="name" value="'.htmlentities($playername).'">
            <input class="searchbutton" type="submit" value="Search">
          </span>
        </div>';

$watchlist = GetCurrentWatchlist();

if (count($watchlist) > 25) $watchlist = array_slice($watchlist, 0, 25);

if (count($watchlist) == 0) {
  echo '
  <tr>
    <td>
      <p class="pages">
        Your watchlist is empty!
        <br><br>
        You can add players to your watchlist by clicking the appropriate icon on the header of their career summary page.
      </p>
    </td>
  </tr>
  </tbody></table>';
  return;
}

echo '
<tr>
  <th align="center" width="150">Player Name</th>
  <th align="center">Last Match</th>
  <th align="center">Matches</th>
  <th align="center">Score</th>
  <th align="center">Frags</th>
  <th align="center">Kills</th>
  <th align="center">Deaths</th>
  <th align="center">Suicides</th>
  <th align="center">Eff</th>
  <th align="center">Acc</th>
  <th align="center">TTL</th>
  <th align="center">Hours</th>
</tr>';

$i = 0;

foreach ($watchlist as $pid) {
  $sql_players = "SELECT pi.id AS pid,
                  pi.name,
                  pi.country,
                  m.time,
                  m.id AS mid
            FROM  uts_pinfo pi,
                  uts_match m,
                  uts_player p
            WHERE pi.id = '$pid'
              AND p.matchid = m.id
              AND p.pid = pi.id
            ORDER BY m.time DESC
            LIMIT 0,1";

  $sql_pinfo = "  SELECT COUNT(*) AS games,
                  SUM(p.gamescore) as gamescore,
                  SUM(p.frags) AS frags,
                  SUM(p.kills) AS kills,
                  SUM(p.deaths) AS deaths,
                  SUM(p.suicides) as suicides,
                  AVG(p.eff) AS eff,
                  AVG(p.accuracy) AS accuracy,
                  AVG(p.ttl) AS ttl,
                  SUM(gametime) as gametime
            FROM  uts_player AS p
            WHERE p.pid = '$pid'
            GROUP BY p.pid";

  $r_pinfo = small_query($sql_pinfo);

  $q_players = mysqli_query($GLOBALS["___mysqli_link"], $sql_players) or die(mysqli_error($GLOBALS["___mysqli_link"]));

  while ($r_players = mysqli_fetch_array($q_players)) {
    $i++;
    $new = (mtimestamp($r_players['time']) > $s_lastvisit) ? true : false;
    $class = ($i % 2) ? 'grey' : 'grey2';
    echo '<tr class="clickableRow" href="?p=pinfo&amp;pid='. $r_players['pid'] .'">';
    echo '<td align="left"><a href="?p=pinfo&amp;pid='. $r_players['pid'] .'">';
    echo FormatPlayerName($r_players['country'], $r_players['pid'], $r_players['name']);
    echo '</a></td>';
    echo '<td align="center"><a href="?p=match&amp;mid='. $r_players['mid'] .'">';;
    if  ($new) echo "<strong>";
    echo date("Y-m-d H:i", mtimestamp($r_players['time']));
    if ($new) echo "</strong>";
    echo '</a>
    </td>
    <td align="center">'.$r_pinfo['games'].'</td>
    <td align="center">'.$r_pinfo['gamescore'].'</td>
    <td align="center">'.$r_pinfo['frags'].'</td>
    <td align="center">'.$r_pinfo['kills'].'</td>
    <td align="center">'.$r_pinfo['deaths'].'</td>
    <td align="center">'.$r_pinfo['suicides'].'</td>
    <td align="center">'.get_dp($r_pinfo['eff']).'</td>
    <td align="center">'.get_dp($r_pinfo['accuracy']).'</td>
    <td align="center">'.GetMinutes($r_pinfo['ttl']).'</td>
    <td align="center">'.sec2hour($r_pinfo['gametime']).'</td>
    </tr>';
  }
}

echo '</tbody></table>';
?>
