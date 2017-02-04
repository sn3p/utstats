<?php
if (empty($import_adminkey) or isset($_REQUEST['import_adminkey']) or $import_adminkey != $adminkey) die('bla');

$options['title'] = 'Recalculate Countryflags';
$options['requireconfirmation'] = false;
$i = 0;
$options['vars'][$i]['name'] = 'start';
$options['vars'][$i]['type'] = 'static';
$options['vars'][$i]['options'] = 'No|Yes';
$options['vars'][$i]['exitif'] = 'No';
$options['vars'][$i]['prompt'] = 'Are you sure';
$options['vars'][$i]['caption'] = 'Sure:';
$i++;

$results = adminselect($options);

if ($results['start'] != 'Yes') {
	include('pages/admin/main.php');
	exit;
}
@ignore_user_abort(true);
@set_time_limit(0);

include("includes/geoip.inc");

/* Opens the database file */
$gi = geoip_open("GeoIP.dat",GEOIP_STANDARD);


echo'<br><table border="0" cellpadding="1" cellspacing="2" width="600">
<tr>
	<td class="smheading" align="center" colspan="2">Recalculating Countryflags</td>
</tr>';

echo'</tr>
<tr>
	<td class="smheading" align="left">Recalculating Rankings:</td>';
	echo'<td class="grey" align="left">';

	echo'Recalculating pinfo table...<br>';
	/* update pinfo table */
	$sql_pids = mysql_query("SELECT uts_pinfo.id as pid, uts_pinfo.country as country, uts_player.ip as ip FROM uts_pinfo, uts_player WHERE uts_pinfo.id = uts_player.pid GROUP BY uts_player.pid;") or die(mysql_error());
		while($sql_pid = mysql_fetch_array($sql_pids))
		{
			$playercountry = strtolower(geoip_country_code_by_addr($gi,long2ip($sql_pid['ip'])));

			if ($playercountry != $sql_pid['country'] )
			{
				mysql_query("UPDATE uts_pinfo SET country = '$playercountry' WHERE id = '".$sql_pid['pid']."'") or die(mysql_error());
			}
		}

	echo'Recalculating player table...<br>';
	/* update player table */
	$sql_pids = mysql_query("SELECT pid, ip, country FROM uts_player");
		while ($sql_pid = mysql_fetch_array($sql_pids))
		{
			$playercountry = strtolower(geoip_country_code_by_addr($gi,long2ip($sql_pid['ip'])));

			if ($playercountry != $sql_pid['country'])
			{
				mysql_query("UPDATE uts_player SET country = '$playercountry' WHERE pid = '".$sql_pid['pid']."'") or die(mysql_error());
			}
		}
	echo 'Done</td>
</tr>

<tr>
	<td class="smheading" align="center" colspan="2">Countryflags recalculated - <a href="./admin.php?key='.$_REQUEST[key].'">Go Back To Admin Page</a></td>
</tr></table>';

?>
