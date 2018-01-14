<?php
include('pages/match_info_killsmatrix.php');
include('includes/hints.php');
include('includes/weaponstats.php');
weaponstats($mid, NULL);

echo '<br>
<table class="zebra box" border="0" cellpadding="0" cellspacing="0" width="700">
<tbody>
  <tr>
    <th class="heading" colspan="11" align="center">Special Events</th>
    </tr>
    <tr>
    <th class="smheading" align="center" rowspan="2" width="220"><img src="assets/images/player.jpg"></th>
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

$sql_firstblood = small_query("SELECT firstblood FROM uts_match WHERE id = $mid");
$sql_multis = "SELECT p.pid, pi.name, p.country, SUM(spree_double) AS spree_double, SUM(spree_multi) AS spree_multi,
  SUM(spree_ultra) AS spree_ultra, SUM(spree_monster)  AS spree_monster,
  SUM(spree_kill) AS spree_kill, SUM(spree_rampage) AS spree_rampage, SUM(spree_dom) AS spree_dom,
  SUM(spree_uns) AS spree_uns, SUM(spree_god) AS spree_god
  FROM uts_player as p, uts_pinfo AS pi
  WHERE p.pid = pi.id  AND pi.banned <> 'Y' AND matchid = $mid
  GROUP BY pid, p.country
  ORDER BY name ASC";

$q_multis = mysqli_query($GLOBALS["___mysqli_link"], $sql_multis) or die(mysqli_error($GLOBALS["___mysqli_link"]));
$i = 0;

while ($r_multis = zero_out(mysqli_fetch_array($q_multis))) {
  $i++;
  $class = ($i % 2) ? 'grey' : 'grey2';
  $r_pname = $r_multis[name];
  $myurl = urlencode($r_pname);

  echo'
  <tr class="clickableRow" href="./?p=matchp&amp;mid='.$mid.'&amp;pid='.$r_multis['pid'].'">
    <td nowrap align="left"><a href="./?p=matchp&amp;mid='.$mid.'&amp;pid='.$r_multis['pid'].'">'.FormatPlayerName($r_multis[country], $r_multis[pid], $r_pname, $gid, $gamename).'</a></td>
    <td align="center">', ($sql_firstblood['firstblood'] == $r_multis['pid'] ? "Yes": ""), '</td>
    <td align="center">'.$r_multis[spree_double].'</td>
    <td align="center">'.$r_multis[spree_multi].'</td>
    <td align="center">'.$r_multis[spree_ultra].'</td>
    <td align="center">'.$r_multis[spree_monster].'</td>
    <td align="center">'.$r_multis[spree_kill].'</td>
    <td align="center">'.$r_multis[spree_rampage].'</td>
    <td align="center">'.$r_multis[spree_dom].'</td>
    <td align="center">'.$r_multis[spree_uns].'</td>
    <td align="center">'.$r_multis[spree_god].'</td>
  </tr>';
}

// No items in insta matches or lms
if ((strpos($gamename, '(insta)') === false) && (strpos($gamename, "Last Man Standing") === false) && (strpos($gamename, "iCTF") === false) && (strpos($gamename, "iTDM") === false) && (strpos($gamename, "iDM") === false) && (strpos($gamename, "iDOM") === false)) {

  $anyPickups = false;
  $contentTable = "";

  $sql_pickups = "SELECT p.pid, pi.name, p.country, SUM(p.pu_pads) AS pu_pads, SUM(p.pu_armour) AS pu_armour, SUM(p.pu_keg) AS pu_keg,
    SUM(p.pu_invis) AS pu_invis, SUM(p.pu_belt) AS pu_belt, SUM(p.pu_amp) AS pu_amp
    FROM uts_player as p, uts_pinfo AS pi
    WHERE p.pid = pi.id AND pi.banned <> 'Y' AND matchid = $mid
    GROUP BY pid, p.country
    ORDER BY name ASC";

  $q_pickups = mysqli_query($GLOBALS["___mysqli_link"], $sql_pickups) or die(mysqli_error($GLOBALS["___mysqli_link"]));
  $i = 0;

  while ($r_pickups = zero_out(mysqli_fetch_array($q_pickups))) {
    $i++;
    $class = ($i % 2) ? 'grey' : 'grey2';
    $r_pname = $r_pickups[name];
    $myurl = urlencode($r_pname);

    if (!$anyPickups && ($r_pickups[pu_pads] > 0 || $r_pickups[pu_armour] > 0 || $r_pickups[pu_keg] > 0 || $r_pickups[pu_invis] > 0 || $r_pickups[pu_belt] > 0 || $r_pickups[pu_amp] > 0)) {
      $anyPickups = true;
    }

    $contentTable .= '
    <tr class="clickableRow" href="./?p=matchp&amp;mid='.$mid.'&amp;pid='.$r_pickups['pid'].'">
      <td nowrap align="left"><a href="./?p=matchp&amp;mid='.$mid.'&amp;pid='.$r_pickups['pid'].'">'.FormatPlayerName($r_pickups[country], $r_pickups[pid], $r_pname, $gid, $gamename).'</a></td>
      <td align="center">'.$r_pickups[pu_pads].'</td>
      <td align="center">'.$r_pickups[pu_armour].'</td>
      <td align="center">'.$r_pickups[pu_keg].'</td>
      <td align="center">'.$r_pickups[pu_invis].'</td>
      <td align="center">'.$r_pickups[pu_belt].'</td>
      <td align="center">'.$r_pickups[pu_amp].'</td>
    </tr>';
  }

  if ($anyPickups) {
    echo '</tbody></table><br>
    <table class="zebra box" border="0" cellpadding="0" cellspacing="0" width="700">
    <tbody><tr>
      <th class="heading" colspan="8" align="center">Pickups Summary</th>
    </tr>
    <tr>
      <th class="smheading" align="center" width="220"><img src="assets/images/player.jpg"></th>
      <th class="smheading" align="center" width="71">Pads</th>
      <th class="smheading" align="center" width="71">Armour</th>
      <th class="smheading" align="center" width="71">Keg</th>
      <th class="smheading" align="center" width="71">Invisibility</th>
      <th class="smheading" align="center" width="71">Shield<br>Belt</th>
      <th class="smheading" align="center" width="71">Damage Amp</th>
    </tr>';

    echo $contentTable;
  }
}

echo '</tbody></table>';

?>
