<?php
if (empty($import_adminkey) or isset($_REQUEST['import_adminkey']) or $import_adminkey != $adminkey) die('bla');
	
$options['title'] = 'Delete Player';
$i = 0;
$options['vars'][$i]['name'] = 'pid';
$options['vars'][$i]['type'] = 'player';
$options['vars'][$i]['prompt'] = 'Choose the player you want to delete:';
$options['vars'][$i]['caption'] = 'Player to delete:';
$i++;

$results = adminselect($options);


$pid = $results['pid'];
$playerid = $pid;

echo'<table border="0" cellpadding="0" cellspacing="0" width="600">
<tr>
	<td class="smheading" align="center" colspan="2">Deleting Player</td>
</tr>
<tr>
	<td class="smheading" align="left">Removing Kill Matrix Entries:</td>';
	$q_match = mysqli_query($GLOBALS["___mysqli_link"], "SELECT matchid, playerid FROM uts_player WHERE pid = '$pid'") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	while ($r_match = mysqli_fetch_array($q_match)) {
		mysqli_query($GLOBALS["___mysqli_link"], "DELETE FROM uts_killsmatrix WHERE matchid = '${r_match['matchid']}' AND (killer = '${r_match['playerid']}' OR victim = '${r_match['playerid']}')") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	}
	echo'<td class="grey" align="left">Done</td>
</tr>

<tr>
	<td class="smheading" align="left" width="300">Removing Player Info:</td>';
$r_pinfo = small_query("SELECT banned FROM uts_pinfo WHERE id = $playerid");
if ($r_pinfo['banned'] != 'Y') { 
	mysqli_query($GLOBALS["___mysqli_link"], "DELETE FROM uts_pinfo WHERE id = $playerid") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	echo'<td class="grey" align="left" width="300">Done</td>';
} else {
	echo'<td class="grey" align="left" width="300">No (player banned)</td>';
}
echo '
</tr>
<tr>
	<td class="smheading" align="left">Removing Player Match Events:</td>';
mysqli_query($GLOBALS["___mysqli_link"], "DELETE e.* FROM uts_player as p, uts_events as e WHERE p.pid = $playerid AND p.playerid = e.playerid AND p.matchid = e.matchid") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	echo'<td class="grey" align="left">Done</td>
</tr>
<tr>
	<td class="smheading" align="left">Removing Player Match Records:</td>';
mysqli_query($GLOBALS["___mysqli_link"], "DELETE FROM uts_player WHERE pid = $playerid") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	echo'<td class="grey" align="left">Done</td>
</tr>
<tr>
	<td class="smheading" align="left">Removing Player Rank:</td>';
mysqli_query($GLOBALS["___mysqli_link"], "DELETE FROM uts_rank WHERE pid = $playerid") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	echo'<td class="grey" align="left">Done</td>
</tr>
<tr>
	<td class="smheading" align="left">Removing Player Weapon Stats:</td>';
mysqli_query($GLOBALS["___mysqli_link"], "DELETE FROM uts_weaponstats WHERE pid = $playerid") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	echo'<td class="grey" align="left">Done</td>
</tr>
<tr>
	<td class="smheading" align="left" width="200">Amending Global Weapon Stats:</td>';
	mysqli_query($GLOBALS["___mysqli_link"], "DELETE FROM uts_weaponstats WHERE matchid='0' AND pid='0'") or die(mysqli_error($GLOBALS["___mysqli_link"]));

	$q_weaponstats = mysqli_query($GLOBALS["___mysqli_link"], "SELECT weapon, SUM(kills) AS kills, SUM(shots) AS shots, SUM(hits) as hits, SUM(damage) as damage, AVG(acc) AS acc FROM uts_weaponstats WHERE matchid = '0'  GROUP BY weapon") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	while ($r_weaponstats = mysqli_fetch_array($q_weaponstats)) {
		mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_weaponstats SET matchid='0', pid='0', weapon='${r_weaponstats['weapon']}', kills='${r_weaponstats['kills']}', shots='${r_weaponstats['shots']}', hits='${r_weaponstats['hits']}', damage='${r_weaponstats['damage']}', acc='${r_weaponstats['acc']}'") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	}

	echo'<td class="grey" align="left" width="400">Done</td>
</tr>
<tr>
	<td class="smheading" align="center" colspan="2">Player Deleted - <a href="./admin.php?key='.$_REQUEST[key].'">Go Back To Admin Page</a></td>
</tr></table>';
?>
