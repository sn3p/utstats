<?php
if (empty($import_adminkey) or isset($_REQUEST['import_adminkey']) or $import_adminkey != $adminkey) die('bla');

$options['title'] = 'Extended Player Info';
$options['requireconfirmation'] = false;
$i = 0;
$options['vars'][$i]['name'] = 'v_pid';
$options['vars'][$i]['type'] = 'player';
$options['vars'][$i]['prompt'] = 'Player?';
$options['vars'][$i]['caption'] = 'Player:';
$i++;

if (isset($_REQUEST['pid'])) {
	$pid = $_REQUEST['pid'];
}else {
	$results = adminselect($options);
	$pid = $results['v_pid'];
}

$is_admin = true;
include('pages/players_info.php');

echo '<br>';

$sql_ips = "SELECT INET_NTOA(p.ip) AS ip, COUNT(p.id) AS matches, MIN(m.time) AS first, MAX(m.time) AS last FROM uts_player AS p, uts_match AS m WHERE p.pid = ".$pid." AND m.id = p.matchid GROUP BY ip ORDER BY ip";
$q_ips = mysqli_query($GLOBALS["___mysqli_link"], $sql_ips) or die("Can't get ip's: " . mysqli_error($GLOBALS["___mysqli_link"]));
echo '
<table class = "box" border="0" cellpadding="0" cellspacing="0" width="720">
  <tbody>
  <tr>
    <td class="heading" colspan="5" align="center">IP\'s used</td>
  </tr>
  <tr>
    <td class="smheading" width="80" align = "center">IP</td>
    <td class="smheading" width="180" align = "left">Hostname</td>
    <td class="smheading" width="60" align = "center">Matches</td>
    <td class="smheading" width="200" align = "left">First</td>
    <td class="smheading" width="200" align = "left">Last</td>
  </tr>';

while ($r_ips = mysqli_fetch_assoc($q_ips)) {
  echo '
  <tr>
    <td class="grey" align = "center">'.$r_ips['ip'].'</td>
    <td class="grey" align = "left">'.gethostbyaddr($r_ips['ip']).'</td>
    <td class="grey" align = "center">'.$r_ips['matches'].'</td>
    <td class="grey" align = "left">'.mdate($r_ips['first']).'</td>
    <td class="grey" align = "left">'.mdate($r_ips['last']).'</td>
  </tr>';
}

echo '
  </tbody>
</table>
<div class="opnote">* Hostnames are real time and might have been different at the time of playing. *</div>
<br>';

((mysqli_free_result($q_ips) || (is_object($q_ips) && (get_class($q_ips) == "mysqli_result"))) ? true : false);

$sql_fakes = "SELECT INET_NTOA(p1.ip) AS ip, pi.name FROM uts_player AS p1, uts_player AS p2, uts_pinfo AS pi WHERE p1.pid = ".$pid." AND p1.ip = p2.ip AND p1.pid <> p2.pid AND pi.id = p2.pid GROUP BY pi.name";
$q_fakes = mysqli_query($GLOBALS["___mysqli_link"], $sql_fakes) or die("Can't retrieve fake nicks: " . mysqli_error($GLOBALS["___mysqli_link"]));
echo '
<table class = "box" border="0" cellpadding="0" cellspacing="0" width="480">
  <tbody>
  <tr>
    <td class="heading" colspan="2" align="center">Possible aliasses</td>
  </tr>
  <tr>
    <td class="smheading" width="120" align = "center">Nick</td>
    <td class="smheading" width="360" align = "center">IP</td>
  </tr>';

if (mysqli_num_rows($q_fakes) == 0) {
	      echo '
	  <tr>
	    <td class="grey" align = "center" colspan="2">No other names found</td>
	  </tr>';
}
else {
	while($r_fakes = mysqli_fetch_assoc($q_fakes)) {
	      echo '
	  <tr>
	    <td class="grey" align = "center">'.$r_fakes[ip].'</td>
	    <td class="grey" align = "center">'.$r_fakes[name].'</td>
	  </tr>';
	}
}
echo '
  </tbody>
</table><br>';
?>
