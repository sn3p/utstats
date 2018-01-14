<?php

function weaponstats($_mid, $_pid, $title = 'Weapons Summary') {
  global $gamename, $gid;

  $sql_weapons = "SELECT  w.matchid,
                  w.pid AS playerid,
                  w.weapon,
                  w.kills,
                  w.shots,
                  w.hits,
                  w.damage,
                  w.acc,
                  pi.name AS playername,
                  pi.country AS country,
                  pi.banned AS banned,
                  wn.id AS weaponid,
                  wn.name AS weaponname,
                  wn.image AS weaponimg,
                  wn.sequence AS sequence
                FROM uts_weapons AS wn,
                    uts_weaponstats AS w
            LEFT JOIN uts_pinfo AS pi
              ON    w.pid = pi.id
            WHERE    w.matchid = '$_mid'
              AND  w.pid = '$_pid'
              AND  (wn.id = w.weapon)
              AND wn.hide <> 'Y'";

  if ($_pid == 0 and $_mid != 0) {
    $sql_weapons = "SELECT  w.matchid,
                    w.pid AS playerid,
                    w.weapon,
                    SUM(w.kills) AS kills,
                    SUM(w.shots) AS shots,
                    SUM(w.hits)  AS hits,
                    SUM(w.damage) AS damage,
                    AVG(w.acc) AS acc,
                    pi.name AS playername,
                    pi.country AS country,
                    pi.banned AS banned,
                    wn.id AS weaponid,
                    wn.name AS weaponname,
                    wn.image AS weaponimg,
                    wn.sequence AS sequence,
                    wn.hide AS hideweapon
FROM uts_weapons AS wn,
uts_weaponstats AS w
              LEFT JOIN uts_pinfo AS pi
                ON    w.pid = pi.id
              WHERE    w.matchid = '$_mid'
                AND  (wn.id = w.weapon)
                AND wn.hide <> 'Y'
              GROUP BY  w.pid,
                    w.weapon";
  }

  $q_weapons = mysqli_query($GLOBALS["___mysqli_link"], $sql_weapons) or die(mysqli_error($GLOBALS["___mysqli_link"]));
  while ($r_weapons = zero_out(mysqli_fetch_array($q_weapons))) {
    $weaponid = intval($r_weapons['weaponid']);
    $playerid = intval($r_weapons['playerid']);

    // Don't include banned players
    if ($r_weapons['banned'] != 'Y') $psort[$playerid] = strtolower($r_weapons['playername']);

    if ($r_weapons['damage'] > 1000000) $r_weapons['damage'] = round($r_weapons['damage'] / 1000, 0) .'K';
    // if ($r_weapons['damage'] > 1000) $r_weapons['damage'] = round($r_weapons['damage'] / 1000, 0) .'K';

    $wd[$playerid]['playername']        = $r_weapons['playername'];
    $wd[$playerid]['country']           = $r_weapons['country'];
    $wd[$playerid]['banned']            = $r_weapons['banned'];
    $wd[$playerid][$weaponid]['kills']  = $r_weapons['kills'];
    $wd[$playerid][$weaponid]['shots']  = $r_weapons['shots'];
    $wd[$playerid][$weaponid]['hits']   = $r_weapons['hits'];
    $wd[$playerid][$weaponid]['damage'] = $r_weapons['damage'];
    $wd[$playerid][$weaponid]['acc']    = ((!empty($r_weapons['acc'])) ? get_dp($r_weapons['acc']) : '');

    if (!isset($wsort[$weaponid]) and $r_weapons['hideweapon'] != 'Y') {
      $wsort[$weaponid] = intval($r_weapons['sequence']);
      $weapons[$weaponid]['name']     = $r_weapons['weaponname'];
      $weapons[$weaponid]['image']    = $r_weapons['weaponimg'];
      $weapons[$weaponid]['sequence'] = $r_weapons['sequence'];
    }
  }
  if (!isset($psort)) return;

  asort($psort);
  asort($wsort);

  $playercol = 1;
  if (count($wsort) < 3) {
    $one = true;
    $colspan = 5;
    if (count($psort) == 1) {
      $playercol = 0;
    }
  } else {
    $one = false;
    $colspan = 1;
  }

  echo '
  <table class="box zebra" border="0" cellpadding="0" cellspacing="0" width="700">
    <tbody>
    <tr>
      <th class="heading" colspan="'. ((count($wsort) * $colspan) + $playercol) .'" align="center">'.htmlentities($title).'</th>
    </tr>';

  if ($one) {
    ws_header($wsort, $weapons, $colspan, $one, $playercol);

    echo '<tr>';
    foreach($wsort as $wid => $bar) {
      for ($i = 1; $i <= $colspan; $i++) {
        switch($i) {
          case 1: $extra = 'Kills'; break;
          case 2: $extra = 'Shots'; break;
          case 3: $extra = 'Hits'; break;
          case 4: $extra = 'Acc'; break;
          case 5: $extra = 'Dmg'; break;
        }
        $extra = '<span style="font-size: 100%">'. $extra .'</span>';
        echo '<th class="smheading" align="center" width="35">'.$extra.'</th>';
      }
    }
    echo '</tr>';

    $i = 0;
    foreach($psort as $pid => $foo) {
      $i++;
      echo '<tr class="clickableRow" href="./?p=matchp&amp;mid='.$_mid.'&amp;pid='.urlencode($pid).'">';
      if ($playercol) {
        echo '<td nowrap align="left"><a href="./?p=matchp&amp;mid='.$_mid.'&amp;pid='.
					urlencode($pid).'">'.FormatPlayerName($wd[$pid]['country'], $pid,  $wd[$pid]['playername'], $gid, $gamename)
				.'</a></td>';
      }
      foreach($wsort as $wid => $bar) {
        ws_cell($wd, $pid, $wid, 'kills', $i);
        ws_cell($wd, $pid, $wid, 'shots', $i);
        ws_cell($wd, $pid, $wid, 'hits', $i);
        ws_cell($wd, $pid, $wid, 'acc', $i);
        ws_cell($wd, $pid, $wid, 'damage', $i);
      }
      echo '</tr>';
    }
  } else {
    ws_block($wd, $weapons, $wsort, $psort, $colspan, $playercol, $one, $_mid, $gamename, 'Kills', 'kills');
    ws_block($wd, $weapons, $wsort, $psort, $colspan, $playercol, $one, $_mid, $gamename, 'Shots', 'shots');
    ws_block($wd, $weapons, $wsort, $psort, $colspan, $playercol, $one, $_mid, $gamename, 'Hits', 'hits');
    ws_block($wd, $weapons, $wsort, $psort, $colspan, $playercol, $one, $_mid, $gamename, 'Damage', 'damage');
    ws_block($wd, $weapons, $wsort, $psort, $colspan, $playercol, $one, $_mid, $gamename, 'Accuracy', 'acc');
  }

  echo '</tbody></table>';
}

function ws_header(&$wsort, &$weapons, $colspan, $one, $playercol) {
  echo '<tr>';
  if ($playercol and $playercol != -1) echo '<td class="smheading" align="center" width="220" '.(($one) ? 'rowspan="2"' : '') .'><img src="assets/images/playersmall.png" style="max-width:50px; max-height:50px;"></td>';
  if ($playercol == -1) echo '<td class="smheading" align="center" width="220">&nbsp;</td>';

  foreach($wsort as $wid => $bar) {
    if (!empty($weapons[$wid]['image'])) {
      $content = '<img border="0" class="tooltip" style="min-width: 15px; max-width:40px; max-height:50px;" src="assets/images/weapons/'.$weapons[$wid]['image'].'" alt="'.$weapons[$wid]['name'].'" title="'.$weapons[$wid]['name'].'"></a>';
    } else {
      $content = '<span style="font-size: 60%;">'.$weapons[$wid]['name'].'</span>';
    }
    echo '<td class="smheading" align="center" '. (($one) ? 'colspan="'.$colspan.'"' : 'width="35"') .'>'.$content.'</td>';

  }
  echo '</tr>';
}

function ws_cell(&$wd, $pid, $wid, $field, $i) {
  $content = '';
  if (isset($wd[$pid][$wid][$field])) $content = $wd[$pid][$wid][$field];
  $class = ($i % 2) ? 'grey' : 'grey2';
  echo '
    <td align="center">'.$content.'</td>';
}

function ws_block(&$wd, &$weapons, &$wsort, &$psort, &$colspan, $playercol, $one,$_mid, $gamename, $caption, $field) {
  global $gamename, $gid;
  if (count($psort) != 1) {
    echo '
    <tr>
      <td class="weapspacer" height="5" colspan="'. ((count($wsort) * $colspan) + $playercol) .'" align="center"></td>
    </tr>
    <tr>
      <th class="smheading" height="20" colspan="'. ((count($wsort) * $colspan) + $playercol) .'" align="center">'.$caption.'</th>
    </tr>';
    ws_header($wsort, $weapons, $colspan, $one, $playercol);
  }
  if (count($psort) == 1) {
    $playercol = -1;
    if ($field == 'kills') ws_header($wsort, $weapons, $colspan, $one, $playercol);
  }

  $i = 0;
  foreach($psort as $pid => $foo) {
    $i++;
    echo '<tr class="clickableRow" href="./?p=matchp&amp;mid='.$_mid.'&amp;pid='.urlencode($pid).'">';
    if ($playercol and $playercol != -1) echo '<td nowrap align="left"><a href="./?p=matchp&amp;mid='.$_mid.'&amp;pid='.urlencode($pid).'">'.FormatPlayerName($wd[$pid]['country'], $pid, $wd[$pid]['playername'], $gid, $gamename).'</a></td>';
    if ($playercol == -1) echo '<td nowrap class="dark" align="center">'.$caption.'</td>';
    foreach($wsort as $wid => $bar) {
      ws_cell($wd, $pid, $wid, $field, $i);
    }
    echo '</tr>';
  }
}

?>
