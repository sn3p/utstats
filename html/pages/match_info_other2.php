<?php
//include('pages/match_info_killsmatrix.php');

include('includes/weaponstats.php');
weaponstats($mid, 0, 'Weapons Summary - '.$ass_att.' Attacking');

IF($mid2 != NULL) {
	echo '<br>';
	weaponstats($mid2, 0, 'Weapons Summary - '.$ass_att2.' Attacking');
}

echo'
<br>
<table class = "box" border="0" cellpadding="0" cellspacing="0" width="720">
  <tbody><tr>
    <td class="heading" colspan="7" align="center">Pickups Summary - '.$ass_att.' Attacking</td>
  </tr>
  <tr>
    <td class="smheading" align="center">Player</td>
    <td class="smheading" align="center" width="80">Pads</td>
    <td class="smheading" align="center" width="80">Armour</td>
    <td class="smheading" align="center" width="80">Keg</td>
    <td class="smheading" align="center" width="80">Invisibility</td>
    <td class="smheading" align="center" width="80">Shield Belt</td>
    <td class="smheading" align="center" width="80">Damage Amp</td>
  </tr>';

$sql_pickups = "SELECT p.pid, pi.name, p.country, SUM(p.pu_pads) AS pu_pads, SUM(p.pu_armour) AS pu_armour, SUM(p.pu_keg) AS pu_keg,
SUM(p.pu_invis) AS pu_invis, SUM(p.pu_belt) AS pu_belt, SUM(p.pu_amp) AS pu_amp
FROM uts_player as p, uts_pinfo AS pi  WHERE p.pid = pi.id AND pi.banned <> 'Y' AND matchid = $mid GROUP BY pid ORDER BY name ASC";
$q_pickups = mysqli_query($GLOBALS["___mysqli_link"], $sql_pickups) or die(mysqli_error($GLOBALS["___mysqli_link"]));
$i = 0;
while ($r_pickups = zero_out(mysqli_fetch_array($q_pickups))) {
     $i++;
     $class = ($i % 2) ? 'grey' : 'grey2';

	  $r_pname = $r_pickups[name];
	  $myurl = urlencode($r_pname);

	  echo'
	  <tr>
		<td nowrap class="darkhuman" align="left"><a class="darkhuman" href="./?p=matchp&amp;mid='.$mid.'&amp;pid='.$r_pickups[pid].'">'.FormatPlayerName($r_pickups[country], $r_pickups[pid], $r_pname, $gid, $gamename).'</a></td>
		<td class="'.$class.'" align="center">'.$r_pickups[pu_pads].'</td>
		<td class="'.$class.'" align="center">'.$r_pickups[pu_armour].'</td>
		<td class="'.$class.'" align="center">'.$r_pickups[pu_keg].'</td>
		<td class="'.$class.'" align="center">'.$r_pickups[pu_invis].'</td>
		<td class="'.$class.'" align="center">'.$r_pickups[pu_belt].'</td>
		<td class="'.$class.'" align="center">'.$r_pickups[pu_amp].'</td>
	  </tr>';
}

IF($mid2 == NULL) {
} else {
	echo'</tbody></table>
	<br>
	<table class = "zebra box" border="0" cellpadding="0" cellspacing="0" width="720">
	  <tbody><tr>
		<td class="heading" colspan="7" align="center">Pickups Summary - '.$ass_att2.' Attacking</td>
	  </tr>
	  <tr>
		<td class="smheading" align="center">Player</td>
		<td class="smheading" align="center" width="80">Pads</td>
		<td class="smheading" align="center" width="80">Armour</td>
		<td class="smheading" align="center" width="80">Keg</td>
		<td class="smheading" align="center" width="80">Invisibility</td>
		<td class="smheading" align="center" width="80">Shield Belt</td>
		<td class="smheading" align="center" width="80">Damage Amp</td>
	  </tr>';

	$sql_pickups = "SELECT p.pid, pi.name, p.country, SUM(p.pu_pads) AS pu_pads, SUM(p.pu_armour) AS pu_armour, SUM(p.pu_keg) AS pu_keg,
	SUM(p.pu_invis) AS pu_invis, SUM(p.pu_belt) AS pu_belt, SUM(p.pu_amp) AS pu_amp
	FROM uts_player as p, uts_pinfo AS pi  WHERE p.pid = pi.id AND pi.banned <> 'Y' AND matchid = $mid2 GROUP BY pid ORDER BY name ASC";
	$q_pickups = mysqli_query($GLOBALS["___mysqli_link"], $sql_pickups) or die(mysqli_error($GLOBALS["___mysqli_link"]));
	$i = 0;
	while ($r_pickups = zero_out(mysqli_fetch_array($q_pickups))) {
     $i++;
     $class = ($i % 2) ? 'grey' : 'grey2';

	  $r_pname = $r_pickups[name];
	  $myurl = urlencode($r_pname);

		  echo'
		  <tr>
			<td nowrap align="left"><a href="./?p=matchp&amp;mid='.$mid.'&amp;pid='.$r_pickups[pid].'">'.FormatPlayerName($r_pickups[country], $r_pickups[pid], $r_pname, $gid, $gamename).'</a></td>
			<td class="'.$class.'" align="center">'.$r_pickups[pu_pads].'</td>
			<td class="'.$class.'" align="center">'.$r_pickups[pu_armour].'</td>
			<td class="'.$class.'" align="center">'.$r_pickups[pu_keg].'</td>
			<td class="'.$class.'" align="center">'.$r_pickups[pu_invis].'</td>
			<td class="'.$class.'" align="center">'.$r_pickups[pu_belt].'</td>
			<td class="'.$class.'" align="center">'.$r_pickups[pu_amp].'</td>
		  </tr>';
	}
}

$sql_firstblood = small_query("SELECT pi.name, pi.country, m.firstblood FROM uts_match AS m, uts_pinfo AS pi WHERE m.firstblood = pi.id AND m.id = $mid");
if (!$sql_firstblood) $sql_firstblood = array('country' => '', 'name' => '(unknown)', 'firstblood' => NULL);
$sql_multis = small_query("SELECT SUM(spree_double) AS spree_double, SUM(spree_multi) AS spree_multi, SUM(spree_ultra) AS spree_ultra, SUM(spree_monster)  AS spree_monster FROM uts_player WHERE matchid = $mid");

IF($mid2 == NULL) {
} else {
$sql_firstblood2 = small_query("SELECT pi.name, pi.country, m.firstblood FROM uts_match AS m, uts_pinfo AS pi WHERE m.firstblood = pi.id AND m.id = $mid2");
if (!$sql_firstblood2) $sql_firstblood2 = array('country' => '', 'name' => '(unknown)', 'firstblood' => NULL);
$sql_multis2 = small_query("SELECT SUM(spree_double) AS spree_double, SUM(spree_multi) AS spree_multi, SUM(spree_ultra) AS spree_ultra, SUM(spree_monster)  AS spree_monster FROM uts_player WHERE matchid = $mid2");
}
echo'</tbody></table>
<br>
<table class = "zebra box" border="0" cellpadding="0" cellspacing="0" width="720">
  <tbody><tr>
    <th class="heading" colspan="2" align="center">Special Events - '.$ass_att.' Attacking</th>
    <th class="heading" colspan="2" align="center">Special Events - '.$ass_att2.' Attacking</th>
  </tr>
  <tr>
    <th align="center" width="150">First Blood</th>
    <th align="center" width="150">'.FormatPlayerName($sql_firstblood[country], $sql_firstblood[firstblood], $sql_firstblood[name], $gid, $gamename).'</th>
    <th align="center" width="150">First Blood</th>
    <th align="center" width="150">'.FormatPlayerName($sql_firstblood2[country], $sql_firstblood[firstblood], $sql_firstblood2[name], $gid, $gamename).'</th>
  </tr>
  <tr>
    <th align="center">Double Kills</th>
    <th align="center">'.$sql_multis[spree_double].'</td>
    <th align="center">Double Kills</td>
    <th align="center">'.$sql_multis2[spree_double].'</td>
  </tr>
  <tr>
    <td align="center">Multi Kills</td>
    <td align="center">'.$sql_multis[spree_multi].'</td>
    <td align="center">Multi Kills</td>
    <td align="center">'.$sql_multis2[spree_multi].'</td>
  </tr>
  <tr>
    <td align="center">Ultra Kills</td>
    <td  align="center">'.$sql_multis[spree_ultra].'</td>
    <td class="dark" align="center">Ultra Kills</td>
    <td class="grey2" align="center">'.$sql_multis2[spree_ultra].'</td>
  </tr>
  <tr>
    <td class="dark" align="center">Monster Kills</td>
    <td class="grey" align="center">'.$sql_multis[spree_monster].'</td>
    <td class="dark" align="center">Monster Kills</td>
    <td class="grey" align="center">'.$sql_multis2[spree_monster].'</td>
  </tr>
</tbody></table>';
?>
