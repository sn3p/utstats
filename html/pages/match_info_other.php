<?php
include('pages/match_info_killsmatrix.php');

include('includes/weaponstats.php');
weaponstats($mid, NULL);

echo'<br>
<table class = "box" border="0" cellpadding="0" cellspacing="2" width="600">
  <tbody><tr>
	<td class="heading" colspan="11" align="center">Special Events</td>
  </tr>
  <tr>
  	<td class="smheading" align="center" rowspan="2" width="">Player</td>
  	<td class="smheading" align="center" rowspan="2" width="60">First Blood</td>
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

$sql_firstblood = small_query("SELECT firstblood FROM uts_match WHERE id = $mid");
$sql_multis = "SELECT p.pid, pi.name, p.country, SUM(spree_double) AS spree_double, SUM(spree_multi) AS spree_multi,
SUM(spree_ultra) AS spree_ultra, SUM(spree_monster)  AS spree_monster,
SUM(spree_kill) AS spree_kill, SUM(spree_rampage) AS spree_rampage, SUM(spree_dom) AS spree_dom,
SUM(spree_uns) AS spree_uns, SUM(spree_god) AS spree_god
FROM uts_player as p, uts_pinfo AS pi
WHERE p.pid = pi.id  AND pi.banned <> 'Y' AND matchid = $mid
GROUP BY pid, p.country
ORDER BY name ASC";

$q_multis = mysql_query($sql_multis) or die(mysql_error());
$i = 0;
while ($r_multis = zero_out(mysql_fetch_array($q_multis))) {
	$i++;
	$class = ($i % 2) ? 'grey' : 'grey2';
	$r_pname = $r_multis[name];
	$myurl = urlencode($r_pname);

  echo'
  <tr>
  	<td nowrap class="darkhuman" align="left"><a class="darkhuman" href="./?p=matchp&amp;mid='.$mid.'&amp;pid='.$r_multis['pid'].'">'.FormatPlayerName($r_multis[country], $r_multis[pid], $r_pname, $gid, $gamename).'</a></td>
  	<td class="'.$class.'" align="center">', ($sql_firstblood['firstblood'] == $r_multis['pid'] ? "Yes": ""), '</td>
  	<td class="'.$class.'" align="center">'.$r_multis[spree_double].'</td>
  	<td class="'.$class.'" align="center">'.$r_multis[spree_multi].'</td>
  	<td class="'.$class.'" align="center">'.$r_multis[spree_ultra].'</td>
  	<td class="'.$class.'" align="center">'.$r_multis[spree_monster].'</td>
  	<td class="'.$class.'" align="center">'.$r_multis[spree_kill].'</td>
  	<td class="'.$class.'" align="center">'.$r_multis[spree_rampage].'</td>
  	<td class="'.$class.'" align="center">'.$r_multis[spree_dom].'</td>
  	<td class="'.$class.'" align="center">'.$r_multis[spree_uns].'</td>
  	<td class="'.$class.'" align="center">'.$r_multis[spree_god].'</td>
  </tr>';
}

// No items in insta matches or lms
if ((strpos($gamename, '(insta)') === false) && (strpos($gamename, "Last Man Standing") === false)) {
	  echo'</tbody></table><br>
	<table class = "box" border="0" cellpadding="0" cellspacing="2" width="600">
	<tbody><tr>
		<td class="heading" colspan="7" align="center">Pickups Summary</td>
	</tr>
	<tr>
		<td class="smheading" align="center">Player</td>
		<td class="smheading" align="center" width="71">Pads</td>
		<td class="smheading" align="center" width="71">Armour</td>
		<td class="smheading" align="center" width="71">Keg</td>
		<td class="smheading" align="center" width="71">Invisibility</td>
		<td class="smheading" align="center" width="71">Shield<br>Belt</td>
		<td class="smheading" align="center" width="71">Damage Amp</td>
	</tr>';

	$sql_pickups = "SELECT p.pid, pi.name, p.country, SUM(p.pu_pads) AS pu_pads, SUM(p.pu_armour) AS pu_armour, SUM(p.pu_keg) AS pu_keg,
	SUM(p.pu_invis) AS pu_invis, SUM(p.pu_belt) AS pu_belt, SUM(p.pu_amp) AS pu_amp
	FROM uts_player as p, uts_pinfo AS pi
  WHERE p.pid = pi.id AND pi.banned <> 'Y' AND matchid = $mid
  GROUP BY pid, p.country
  ORDER BY name ASC";

	$q_pickups = mysql_query($sql_pickups) or die(mysql_error());
	$i = 0;
	while ($r_pickups = zero_out(mysql_fetch_array($q_pickups))) {
		$i++;
		$class = ($i % 2) ? 'grey' : 'grey2';
		$r_pname = $r_pickups[name];
		$myurl = urlencode($r_pname);

		echo'
		<tr>
			<td nowrap class="darkhuman" align="left"><a class="darkhuman" href="./?p=matchp&amp;mid='.$mid.'&amp;pid='.$r_pickups['pid'].'">'.FormatPlayerName($r_pickups[country], $r_pickups[pid], $r_pname, $gid, $gamename).'</a></td>
			<td class="'.$class.'" align="center">'.$r_pickups[pu_pads].'</td>
			<td class="'.$class.'" align="center">'.$r_pickups[pu_armour].'</td>
			<td class="'.$class.'" align="center">'.$r_pickups[pu_keg].'</td>
			<td class="'.$class.'" align="center">'.$r_pickups[pu_invis].'</td>
			<td class="'.$class.'" align="center">'.$r_pickups[pu_belt].'</td>
			<td class="'.$class.'" align="center">'.$r_pickups[pu_amp].'</td>
		</tr>';
	}
}
echo'</tbody></table>';

/* $sql_firstblood = small_query("SELECT pi.name, pi.country, m.firstblood FROM uts_match AS m, uts_pinfo AS pi WHERE m.firstblood = pi.id AND m.id = $mid");
if (!$sql_firstblood) $sql_firstblood = array('country' => '', 'name' => '(unknown)', 'firstblood' => NULL);

echo'
<br>
<table border="0" cellpadding="1" cellspacing="2" width="200">
  <tbody>
  <tr>
    <td class="heading" colspan="2" align="center">First Blood</td>
  </tr>
  <tr>
    <td class="grey2" align="center" width="150">'.FormatPlayerName($sql_firstblood[country], $sql_firstblood[firstblood], $sql_firstblood[name], $gid, $gamename).'</td>
  </tr>
</tbody></table>'; */
?>
