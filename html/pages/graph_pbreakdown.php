<?php
$max_height = 80;

// Hourly Breakdown
$sql_ghours = "SELECT HOUR(m.time) AS res_hour, COUNT(p.id) AS res_count
FROM uts_match m, uts_player p WHERE $bgwhere AND m.id = p.matchid GROUP by res_hour";
$q_ghours = mysqli_query($GLOBALS["___mysqli_link"], $sql_ghours) or die(mysqli_error($GLOBALS["___mysqli_link"]));
$hour_max = 0;
$hour_sum = 0;
while ($r_ghours = mysqli_fetch_array($q_ghours)) {
		$gb_hour[$r_ghours['res_hour']] = $r_ghours['res_count'];
		if ($r_ghours['res_count'] > $hour_max) $hour_max = $r_ghours['res_count'];
		$hour_sum += $r_ghours['res_count'];
}
if ($hour_max == 0) return;

// Daily Breakdown
// We use WEEKDAY rather then DAYOFWEEK because now the week starts with Monday instead of Sunday
$sql_gdays = "SELECT WEEKDAY(time) AS res_day, COUNT(*) AS res_count
FROM uts_match m, uts_player p WHERE $bgwhere AND m.id = p.matchid GROUP by res_day";
$q_gdays = mysqli_query($GLOBALS["___mysqli_link"], $sql_gdays) or die(mysqli_error($GLOBALS["___mysqli_link"]));
$day_max = 0;
$day_sum = 0;
while ($r_gdays = mysqli_fetch_array($q_gdays)) {
		$gb_day[$r_gdays['res_day']] = $r_gdays['res_count'];
		if ($r_gdays['res_count'] > $day_max) $day_max = $r_gdays['res_count'];
		$day_sum += $r_gdays['res_count'];
}

// Monthly Breakdown
$sql_gmonths = "SELECT MONTH(m.time) AS res_month, COUNT(p.id) AS res_count
FROM uts_match m, uts_player p WHERE $bgwhere AND m.id = p.matchid GROUP by res_month";
$q_gmonths = mysqli_query($GLOBALS["___mysqli_link"], $sql_gmonths) or die(mysqli_error($GLOBALS["___mysqli_link"]));
$month_max = 0;
$month_sum = 0;
while ($r_gmonths = mysqli_fetch_array($q_gmonths)) {
		$gb_month[$r_gmonths['res_month']] = $r_gmonths['res_count'];
		if ($r_gmonths['res_count'] > $month_max) $month_max = $r_gmonths['res_count'];
		$month_sum += $r_gmonths['res_count'];
}


echo'
<table class = "box" border="0" cellpadding="0" cellspacing="0">
<tbody>
<tr><td>
<table border="0" cellpadding="0" cellspacing="0">
  <tbody>
  <tr>
    <td class="heading" align="center" colspan="47">Hourly, Daily and Monthly Activity '.$gtitle.'</td>
  </tr>
  <tr>
    <td class="dark" align="center" colspan="47" height="10"></td>
  </tr>
  <tr>
	<td class="dark" align="center" width="15"></td>';

// Hourly
for ($i = 0; $i <= 23; $i++) {
	if (!isset($gb_hour[$i])) $gb_hour[$i] = 0;
	$title = $gb_hour[$i] .' ('. get_dp($gb_hour[$i] / $hour_sum * 100) .' %)';
	echo '<td class="dark" align="center" valign="bottom" width="15"><img border="0" src="assets/images/bars/v_bar'. ($i % 16 + 1) .'.png" width="18" height="'.(int)($gb_hour[$i] / $hour_max * $max_height).'" alt="'. $title .'" title="'. $title .'"></td>';
}

echo '<td class="dark" align="center" valign="bottom" width="15"></td>';

// Daily
for ($i = 0; $i <= 6; $i++) {
	if (!isset($gb_day[$i])) $gb_day[$i] = 0;
	$title = $gb_day[$i] .' ('. get_dp($gb_day[$i] / $day_sum * 100) .' %)';
	echo '<td class="dark" align="center" valign="bottom" width="15"><img border="0" src="assets/images/bars/v_bar'. ($i % 16 + 1) .'.png" width="18" height="'.(int)($gb_day[$i] / $day_max * $max_height).'" alt="'. $title .'" title="'. $title .'"></td>';
}

echo '<td class="dark" align="center" valign="bottom" width="15"></td>';

// Monthly
for ($i = 1; $i <= 12; $i++) {
	if (!isset($gb_month[$i])) $gb_month[$i] = 0;
	$title = $gb_month[$i] .' ('. get_dp($gb_month[$i] / $month_sum * 100) .' %)';
	echo '<td class="dark" align="center" valign="bottom" width="15"><img border="0" src="assets/images/bars/v_bar'. (($i + 8) % 16 + 1) .'.png" width="18" height="'.(int)($gb_month[$i] / $month_max * $max_height).'" alt="'. $title .'" title="'. $title .'"></td>';
}
echo '<td class="dark" align="center" valign="bottom" width="15"></td>';
echo'</tr><tr>
	<td class="grey" align="center" width="15"></td>
	<td class="grey" align="center">0</td>
	<td class="grey" align="center">1</td>
	<td class="grey" align="center">2</td>
	<td class="grey" align="center">3</td>
	<td class="grey" align="center">4</td>
	<td class="grey" align="center">5</td>
	<td class="grey" align="center">6</td>
	<td class="grey" align="center">7</td>
	<td class="grey" align="center">8</td>
	<td class="grey" align="center">9</td>
	<td class="grey" align="center">10</td>
	<td class="grey" align="center">11</td>
	<td class="grey" align="center">12</td>
	<td class="grey" align="center">13</td>
	<td class="grey" align="center">14</td>
	<td class="grey" align="center">15</td>
	<td class="grey" align="center">16</td>
	<td class="grey" align="center">17</td>
	<td class="grey" align="center">18</td>
	<td class="grey" align="center">19</td>
	<td class="grey" align="center">20</td>
	<td class="grey" align="center">21</td>
	<td class="grey" align="center">22</td>
	<td class="grey" align="center">23</td>
	<td class="grey" align="center" width="10"></td>
	<td class="grey" align="center">M</td>
	<td class="grey" align="center">T</td>
	<td class="grey" align="center">W</td>
	<td class="grey" align="center">T</td>
	<td class="grey" align="center">F</td>
	<td class="grey" align="center">S</td>
	<td class="grey" align="center">S</td>
	<td class="grey" align="center" width="10"></td>
	<td class="grey" align="center">J</td>
	<td class="grey" align="center">F</td>
	<td class="grey" align="center">M</td>
	<td class="grey" align="center">A</td>
	<td class="grey" align="center">M</td>
	<td class="grey" align="center">J</td>
	<td class="grey" align="center">J</td>
	<td class="grey" align="center">A</td>
	<td class="grey" align="center">S</td>
	<td class="grey" align="center">O</td>
	<td class="grey" align="center">N</td>
	<td class="grey" align="center">D</td>
	<td class="grey" align="center" width="15"></td>
</tr>
</tbody></table>
</tr>
</tbody>
</table>
<br>';
?>
