<?php
$max_height = 100;

// Hourly Breakdown
$sql_ghours = "SELECT HOUR(time) AS res_hour, COUNT(*) AS res_count
  FROM uts_match
	WHERE $bgwhere
	GROUP by res_hour";

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
	FROM uts_match
	WHERE $bgwhere
	GROUP by res_day";

$q_gdays = mysqli_query($GLOBALS["___mysqli_link"], $sql_gdays) or die(mysqli_error($GLOBALS["___mysqli_link"]));
$day_max = 0;
$day_sum = 0;

while ($r_gdays = mysqli_fetch_array($q_gdays)) {
  $gb_day[$r_gdays['res_day']] = $r_gdays['res_count'];
  if ($r_gdays['res_count'] > $day_max) $day_max = $r_gdays['res_count'];
  $day_sum += $r_gdays['res_count'];
}

// Monthly Breakdown
$sql_gmonths = "SELECT MONTH(time) AS res_month, COUNT(*) AS res_count
	FROM uts_match
	WHERE $bgwhere
	GROUP by res_month";

$q_gmonths = mysqli_query($GLOBALS["___mysqli_link"], $sql_gmonths) or die(mysqli_error($GLOBALS["___mysqli_link"]));
$month_max = 0;
$month_sum = 0;

while ($r_gmonths = mysqli_fetch_array($q_gmonths)) {
  $gb_month[$r_gmonths['res_month']] = $r_gmonths['res_count'];
  if ($r_gmonths['res_count'] > $month_max) $month_max = $r_gmonths['res_count'];
  $month_sum += $r_gmonths['res_count'];
}

// very dirty hack, to deal with the $bgwhere containing an OR
// if it contains an OR, all literals should be prefixed with "m."
if (substr_count($bgwhere, ' or ') == 0){
  $bgwhere = 'm.' . $bgwhere;
} else {
  $bgwhere = substr($bgwhere, 1, -1);
  $part = explode(' or ', $bgwhere);
  $bgwhere = '';
  foreach($part as $i){
    $bgwhere .= 'm.' . $i . ' OR ';
  }
  $bgwhere = '(' . substr($bgwhere, 0, -4) . ')';
}

// Country Breakdown
$sql_gcountries = "SELECT country AS res_country, COUNT(*) AS res_count
	FROM (SELECT p.country AS country FROM uts_player AS p, uts_match AS m
  WHERE m.id = p.matchid AND $bgwhere
	GROUP BY p.pid) AS res_table
	GROUP BY res_country ORDER BY res_count DESC";

$q_gcountries = mysqli_query($GLOBALS["___mysqli_link"], $sql_gcountries) or die(mysqli_error($GLOBALS["___mysqli_link"]));
$country_max = 0;
$country_sum = 0;
$i = 0;

while ($r_gcountries = mysqli_fetch_array($q_gcountries)) {
  $gb_country[$i] = $r_gcountries['res_country'] . ";" . $r_gcountries['res_count'];
  if ($r_gcountries['res_count'] > $country_max) $country_max = $r_gcountries['res_count'];
  $country_sum += $r_gcountries['res_count'];
  $i++;
}

echo '
<table class="box" border="0" cellpadding="0" cellspacing="0">
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
  echo '<td class="dark-mbreakdown" align="center" valign="bottom" width="15"><img border="0" src="assets/images/bars/v_bar'. ($i % 16 + 1) .'.png" width="18" height="'.(int)($gb_hour[$i] / $hour_max * $max_height).'" alt="'. $title .'" title="'. $title .'"></td>';
}

echo '<td class="dark" align="center" valign="bottom" width="15"></td>';

// Daily
for ($i = 0; $i <= 6; $i++) {
  if (!isset($gb_day[$i])) $gb_day[$i] = 0;
  $title = $gb_day[$i] .' ('. get_dp($gb_day[$i] / $day_sum * 100) .' %)';
  echo '<td class="dark-mbreakdown" align="center" valign="bottom" width="15"><img border="0" src="assets/images/bars/v_bar'. ($i % 16 + 1) .'.png" width="18" height="'.(int)($gb_day[$i] / $day_max * $max_height).'" alt="'. $title .'" title="'. $title .'"></td>';
}

echo '<td class="dark" align="center" valign="bottom" width="15"></td>';

// Monthly
for ($i = 1; $i <= 12; $i++) {
  if (!isset($gb_month[$i])) $gb_month[$i] = 0;
  $title = $gb_month[$i] .' ('. get_dp($gb_month[$i] / $month_sum * 100) .' %)';
  echo '<td class="dark-mbreakdown" align="center" valign="bottom" width="15"><img border="0" src="assets/images/bars/v_bar'. (($i + 8) % 16 + 1) .'.png" width="18" height="'.(int)($gb_month[$i] / $month_max * $max_height).'" alt="'. $title .'" title="'. $title .'"></td>';
}

echo '<td class="dark" align="center" valign="bottom" width="15"></td>';
echo'</tr><tr>
  <td class="grey-mbreakdown" align="center" width="15"></td>
  <td class="grey-mbreakdown" align="center">0</td>
  <td class="grey-mbreakdown" align="center">1</td>
  <td class="grey-mbreakdown" align="center">2</td>
  <td class="grey-mbreakdown" align="center">3</td>
  <td class="grey-mbreakdown" align="center">4</td>
  <td class="grey-mbreakdown" align="center">5</td>
  <td class="grey-mbreakdown" align="center">6</td>
  <td class="grey-mbreakdown" align="center">7</td>
  <td class="grey-mbreakdown" align="center">8</td>
  <td class="grey-mbreakdown" align="center">9</td>
  <td class="grey-mbreakdown" align="center">10</td>
  <td class="grey-mbreakdown" align="center">11</td>
  <td class="grey-mbreakdown" align="center">12</td>
  <td class="grey-mbreakdown" align="center">13</td>
  <td class="grey-mbreakdown" align="center">14</td>
  <td class="grey-mbreakdown" align="center">15</td>
  <td class="grey-mbreakdown" align="center">16</td>
  <td class="grey-mbreakdown" align="center">17</td>
  <td class="grey-mbreakdown" align="center">18</td>
  <td class="grey-mbreakdown" align="center">19</td>
  <td class="grey-mbreakdown" align="center">20</td>
  <td class="grey-mbreakdown" align="center">21</td>
  <td class="grey-mbreakdown" align="center">22</td>
  <td class="grey-mbreakdown" align="center">23</td>
  <td class="grey-mbreakdown" align="center" width="10"></td>
  <td class="grey-mbreakdown" align="center">M</td>
  <td class="grey-mbreakdown" align="center">T</td>
  <td class="grey-mbreakdown" align="center">W</td>
  <td class="grey-mbreakdown" align="center">T</td>
  <td class="grey-mbreakdown" align="center">F</td>
  <td class="grey-mbreakdown" align="center">S</td>
  <td class="grey-mbreakdown" align="center">S</td>
  <td class="grey-mbreakdown" align="center" width="10"></td>
  <td class="grey-mbreakdown" align="center">J</td>
  <td class="grey-mbreakdown" align="center">F</td>
  <td class="grey-mbreakdown" align="center">M</td>
  <td class="grey-mbreakdown" align="center">A</td>
  <td class="grey-mbreakdown" align="center">M</td>
  <td class="grey-mbreakdown" align="center">J</td>
  <td class="grey-mbreakdown" align="center">J</td>
  <td class="grey-mbreakdown" align="center">A</td>
  <td class="grey-mbreakdown" align="center">S</td>
  <td class="grey-mbreakdown" align="center">O</td>
  <td class="grey-mbreakdown" align="center">N</td>
  <td class="grey-mbreakdown" align="center">D</td>
  <td class="grey-mbreakdown" align="center" width="15"></td>
</tr>
</tbody></table>
</tr>
</tbody>
</table>
<br><br>';

global $a_countries;
// The number of different countries we want to display
$no_countries = 20;

// Check if there are more countries then $no_countries; if so, we can have a "others" column
if ( count($gb_country) < $no_countries ){
  $max_cntry = count($gb_country);
  $collspan = $max_cntry + 2;
  $others = false;
} else {
  $max_cntry = $no_countries;
  $collspan = $max_cntry + 3;
  $others = true;
}

echo'
<table class = "box" border="0" cellpadding="0" cellspacing="0">
<tbody>
<tr><td><table border="0" cellpadding="0" cellspacing="0">
  <tbody>
  <tr>
    <td class="heading" align="center" colspan="' . $collspan . '">&nbsp;&nbsp;Origin of Players&nbsp;&nbsp;</td>
  </tr>
  <tr>
    <td class="dark" align="center" colspan="' . $collspan . '" height="10"></td>
  </tr>
  <tr>
  <td class="dark" align="center" width="15"></td>';

// Countries
$x = 0;
for ($i = 0; $i < $max_cntry; $i++) {
  if (!isset($gb_hour[$i])) $gb_hour[$i] = 0;
  $country = explode(";",$gb_country[$i]);
  $title = $a_countries[$country[0]] .': ' . $country[1] . ' ('. get_dp($country[1] / $country_sum * 100) .' %)';
  echo '<td class="dark-mbreakdown" align="center" valign="bottom" width="20"><img border="0" src="assets/images/bars/v_bar'. ($i % 16 + 1) .'.png" width="20" height="'.(int)($country[1] / $country_max * $max_height).'" alt="'. $title .'" title="'. $title .'"></td>';
  $x += $country[1];
}

if($others){
  $countries_left = $country_sum - $x;
  $title = 'Other Countries: ' . $countries_left . ' ('. get_dp($countries_left / $country_sum * 100) .' %)';
  echo '<td class="dark-mbreakdown" align="center" valign="bottom" width="20"><img border="0" src="assets/images/bars/v_bar'. ($max_cntry % 16 + 1) .'.png" width="20" height="'.(int)($countries_left / $country_max * $max_height).'" alt="'. $title .'" title="'. $title .'"></td>';
};

echo '<td class="dark" align="center" valign="bottom" width="18"></td>';
echo'</tr>
	<tr>
  <td class="grey" align="center" width="18"></td>';

  for ($i = 0; $i < $max_cntry; $i++) {
    $country = explode(";",$gb_country[$i]);
    $country = strtoupper($country[0]);
    echo '<td class="grey-mbreakdown" align="center">' . $country . '</td>';
  }

	if($others){
	  echo '<td class="grey" align="center">--</td>';
	}

	echo '<td class="grey" align="center" width="15"></td>
</tr>
</tbody></table>
</tr>
</tbody>
</table>
<br>';

?>
