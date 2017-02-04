<?php
if (empty($import_adminkey) or isset($_REQUEST['import_adminkey']) or $import_adminkey != $adminkey) die('bla');
	
$ban = ($_REQUEST['saction'] == 'ban') ? true : false;

if ($ban) {
	$options['title'] = 'Ban Player';
} else {
	$options['title'] = 'Unban Player';
}
$i = 0;
$options['vars'][$i]['name'] = 'pid';
$options['vars'][$i]['type'] = 'player';
if ($ban) {
	$options['vars'][$i]['whereisbanned'] = 'N';
} else {
	$options['vars'][$i]['whereisbanned'] = 'Y';
}
$options['vars'][$i]['prompt'] = 'Player?';
$options['vars'][$i]['caption'] = 'Player:';
$i++;

$results = adminselect($options);


$pid = $results['pid'];

if ($ban) {
	echo'<br><table border="0" cellpadding="1" cellspacing="2" width="600">
	<tr>
		<td class="smheading" align="center" colspan="2">Banning Player</td>
	</tr>
	<tr>
		<td class="smheading" align="left">Removing Player Rank:</td>';
	mysql_query("DELETE FROM uts_rank WHERE pid = $pid") or die(mysql_error());
		echo'<td class="grey" align="left">Done</td>
	</tr>
	<tr>
		<td class="smheading" align="left">Updating Player Record:</td>';
	mysql_query("UPDATE uts_pinfo SET banned = 'Y' WHERE id = $pid") or die(mysql_error());
		echo'<td class="grey" align="left">Done</td>
	</tr>
	
	<tr>
		<td class="smheading" align="center" colspan="2">Player Banned - <a href="./admin.php?key='.$_REQUEST[key].'">Go Back To Admin Page</a></td>
	</tr></table>';
} else {
	echo'<table border="0" cellpadding="1" cellspacing="2" width="600">
	<tr>
		<td class="smheading" align="center" colspan="2">Unbanning Player</td>
	</tr>
	<tr>
		<td class="smheading" align="left">Updating Player Record:</td>';
	mysql_query("UPDATE uts_pinfo SET banned = 'N' WHERE id = $pid") or die(mysql_error());
		echo'<td class="grey" align="left">Done</td>
	</tr>
	<tr>
		<td class="smheading" align="left" width="200">Restoring Rankings</td>';
	
	$sql_nrank = "SELECT SUM(gametime) AS time, pid, gid, SUM(rank) AS rank, COUNT(matchid) AS matches FROM uts_player WHERE pid = $pid GROUP BY pid, gid";
	$q_nrank = mysql_query($sql_nrank) or die(mysql_error());
	while ($r_nrank = mysql_fetch_array($q_nrank)) {
	
		mysql_query("INSERT INTO uts_rank SET time = '$r_nrank[time]', pid = $pid, gid = $r_nrank[gid], rank = '$r_nrank[rank]', prevrank = '$r_nrank[rank]', matches = $r_nrank[matches]") or die(mysql_error());
	}
	
		echo'<td class="grey" align="left" width="400">Done</td>
	</tr>	
	<tr>
		<td class="smheading" align="center" colspan="2">Player Unbanned - <a href="./admin.php?key='.$_REQUEST[key].'">Go Back To Admin Page</a></td>
	</tr></table>';
}
	
?>
