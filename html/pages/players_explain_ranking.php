<?php
function row($name = NULL, $amount = 0, $multiplier = 0, $extra_multiplier = true) {
	static $i = 0;
	if (empty($name)) {
		echo '<tr><td colspan="4" height="3"></td></tr>';
		$i = 0;
		return(0);
	}
	$i++;
	$class = ($i%2) ? 'grey' : 'grey2';
	if ($extra_multiplier) $multiplier *= 600;
	$points = $amount * $multiplier;
	
	$d_points = get_dp($points);
	if ($points % 1 == 0) $d_points = ceil($points); 
	echo '<tr>';
	echo '<td>'. htmlentities($name) .'</td>';
	echo '<td align="center">'. $amount .'</td>';
	echo '<td align="center">'. $multiplier .'</td>';
	echo '<td align="right">'. $d_points .'</td>';
	echo '</tr>';
	return($points);
}

$pid = isset($pid) ? $pid : $_GET['pid'];
$gid = isset($gid) ? $gid : $_GET['gid'];

$pid = preg_replace('/\D/', '', $pid);
$gid = preg_replace('/\D/', '', $gid);

$r_info = small_query("SELECT name, country, banned FROM uts_pinfo WHERE id = '$pid'");
if (!$r_info) {
	echo "Player not found";
	include("includes/footer.php");
	exit;
}

if ($r_info['banned'] == 'Y') {
	if (isset($is_admin) and $is_admin) {
		echo "Warning: Banned player - Admin override<br>";
	} else {
		echo "Sorry, this player has been banned!";
		include("includes/footer.php");
		exit;
	}
}

$playername = $r_info['name'];

$r_game = small_query("SELECT name, gamename FROM uts_games WHERE id = '$gid'");
if (!$r_game) {
	echo "Game ($gid) not found.";
	include("includes/footer.php");
	exit;
}
$real_gamename = $r_game['gamename'];


$r_cnt = small_query("SELECT
		SUM(frags) AS frags, SUM(deaths) AS deaths, SUM(suicides) AS suicides, SUM(teamkills) AS teamkills,
		SUM(flag_taken) AS flag_taken, SUM(flag_pickedup) AS flag_pickedup, SUM(flag_return) AS flag_return, SUM(flag_capture) AS flag_capture, SUM(flag_cover) AS flag_cover,
		SUM(flag_seal) AS flag_seal, SUM(flag_assist) AS flag_assist, SUM(flag_kill) AS flag_kill,
		SUM(dom_cp) AS dom_cp, SUM(ass_obj) AS ass_obj,
		SUM(spree_double) AS spree_double, SUM(spree_multi) AS spree_multi, SUM(spree_ultra) AS spree_ultra, SUM(spree_monster) AS spree_monster,
		SUM(spree_kill) AS spree_kill, SUM(spree_rampage) AS spree_rampage, SUM(spree_dom) AS spree_dom, SUM(spree_uns) AS spree_uns, SUM(spree_god) AS spree_god,
		SUM(gametime) AS gametime 
		FROM uts_player WHERE pid = $pid and gid = $gid");






echo '
<table class="zebra box" border="0" cellpadding="0" cellspacing="0" width="700">
<tbody>
	<tr>
	    <th colspan=4 class="heading" align="center"><a href="?p=pinfo&amp;pid='.$pid.'">'.FlagImage($r_info['country'], false).' '.htmlentities($playername).'</a>\'s '. htmlentities($r_game['name']) .' ranking explained </th>
		</tr>
		<tr>
	
		<td class="smheading" width="250"></td>
		<td class="smheading" width="80" align="center">Amount</td>
		<td class="smheading" width="80" align="center">Multiplier</td>
		<td class="smheading" width="100" align="right">Points</t>
	</tr>';

if ($real_gamename == "Bunny Track") {
	$sql_btmaprank = "SELECT e.col2 AS no, COUNT(e.col2) AS count FROM uts_events AS e, uts_player AS p WHERE p.pid = $pid AND p.gid = $gid AND p.playerid = e.playerid AND e.matchid = p.matchid AND e.col2 > 0 AND e.col2 <= 5 GROUP BY e.col2";
	$q_btmaprank = mysqli_query($GLOBALS["___mysqli_link"], $sql_btmaprank) or die ("Can't retrieve \$q_btmaprank: ". mysqli_error($GLOBALS["___mysqli_link"]));
	$mapranks = array();
	while($r_btmaprank = mysqli_fetch_assoc($q_btmaprank)) {
		$mapranks[$r_btmaprank[no]] = $r_btmaprank[count];
	}

	$t_points += row('First place', (!empty($mapranks[1]) ? $mapranks[1] : 0), 10);
	$t_points += row('Second place', (!empty($mapranks[2]) ? $mapranks[2] : 0), 8);
	$t_points += row('Third place', (!empty($mapranks[3]) ? $mapranks[3] : 0), 6);
	$t_points += row('Fourth place', (!empty($mapranks[4]) ? $mapranks[4] : 0), 4);
	$t_points += row('Fifth place', (!empty($mapranks[5]) ? $mapranks[5] : 0), 2);
}
else {
	$t_points = 0;
	$t_points += row('Frags', $r_cnt['frags'], 0.5);
	$t_points += row('Deaths', $r_cnt['deaths'], -0.25);
	$t_points += row('Suicides', $r_cnt['suicides'], -0.25 );
	$t_points += row('Teamkills', $r_cnt['teamkills'], -2);
echo '<tr><td colspan=4 class="weapspacer"></td></tr>';
	$t_points += row('Flag Takes', $r_cnt['flag_taken'], 1);
	$t_points += row('Flag Pickups', $r_cnt['flag_pickedup'], 1);
	$t_points += row('Flag Returns', $r_cnt['flag_return'], 1);
	$t_points += row('Flag Captures', $r_cnt['flag_capture'], 10);
	$t_points += row('Flag Covers', $r_cnt['flag_cover'], 3);
	$t_points += row('Flag Seals', $r_cnt['flag_seal'], 2);
	$t_points += row('Flag Assists', $r_cnt['flag_assist'], 5);
	$t_points += row('Flag Kills', $r_cnt['flag_kill'], 2);
echo '<tr><td colspan=4 class="weapspacer"></td></tr>';
	$t_points += row('Controlpoint Captures', $r_cnt['dom_cp'], 10);
	if (strpos($real_gamename, 'Assault') !== false) {
		$t_points += row('Assault Objectives', $r_cnt['ass_obj'], 10);
	} else {
		$t_points += row('Assault Objectives', 0, 10);
	}
	if (strpos($real_gamename, 'JailBreak') !== false) {
		$t_points += row('Team Releases', $r_cnt['ass_obj'], 1.5);
	} else {
		$t_points += row('Team Releases', 0, 1.5);
	} 
echo '<tr><td colspan=4 class="weapspacer"></td></tr>';
	$t_points += row('Double Kills', $r_cnt['spree_double'], 1);
	$t_points += row('Multi Kills', $r_cnt['spree_multi'], 1);
	$t_points += row('Ultra Kills', $r_cnt['spree_ultra'], 1);
	$t_points += row('Monster Kills', $r_cnt['spree_monster'], 2);
echo '<tr><td colspan=4 class="weapspacer"></td></tr>';
	$t_points += row('Killing Sprees', $r_cnt['spree_kill'], 1);
	$t_points += row('Rampages', $r_cnt['spree_rampage'], 1);
	$t_points += row('Dominatings', $r_cnt['spree_dom'], 1.5);
	$t_points += row('Unstoppables', $r_cnt['spree_uns'], 2);
	$t_points += row('Godlikes', $r_cnt['spree_god'], 3);
};
	
echo '<tr><td colspan=4 class="weapspacer"></td></tr>';
	
echo '<tr>	<td class="smheading">Total</td>
				<td  class="smheading" align="center"></td>
				<td  class="smheading" align="center"></td>
				<td  class="smheading" align="right">'. ceil($t_points) .'</td>
		</tr>';

$gametime = ceil($r_cnt['gametime'] / 60);
$t_points = $t_points / $gametime;
echo '<tr>	<td>Divided by game minutes</td>
				<td  align="center">'.$gametime.'</td>
				<td  align="center"></td>
				<td  align="right">'. get_dp($t_points) .'</td>
		</tr>';
		
IF ($gametime < 10) {
	$t_points += row('Penalty for playing < 10 minutes', get_dp($t_points), 0, false);
}

IF ($gametime >= 10 && $gametime < 50) {
	$t_points += row('Penalty for playing < 50 minutes', get_dp($t_points), -0.75, false);
}

IF ($gametime >= 50 && $gametime < 100) {
	$t_points += row('Penalty for playing < 100 minutes', get_dp($t_points), -0.5, false);
}

IF ($gametime >= 100 && $gametime < 200) {
	$t_points += row('Penalty for playing < 200 minutes', get_dp($t_points), -0.3, false);
}

IF ($gametime >= 200 && $gametime < 300) {
	$t_points += row('Penalty for playing < 300 minutes', get_dp($t_points), -0.15, false);
}
echo '<tr><td colspan=4 class="weapspacer"></td></tr>';
echo '<tr>	<td class="totals"><strong>Ranking points</strong></td>
				<td class="totals" align="center"></td>
				<td class="totals" align="center"></td>
				<td class="totals" align="right"><strong>'. get_dp($t_points) .'</strong></td>
		</tr>';
echo '</tbody></table>';
?>
