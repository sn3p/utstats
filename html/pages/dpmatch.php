<?php
if (empty($import_adminkey) or isset($_REQUEST['import_adminkey']) or $import_adminkey != $adminkey) die('bla');
	
$options['title'] = 'Delete Player from Match';
$i = 0;
$options['vars'][$i]['name'] = 'pid';
$options['vars'][$i]['type'] = 'player';
$options['vars'][$i]['prompt'] = 'Choose the player you want to delete from a match:';
$options['vars'][$i]['caption'] = 'Player to delete:';
$i++;
$options['vars'][$i]['name'] = 'mid';
$options['vars'][$i]['type'] = 'match';
$options['vars'][$i]['whereplayer'] = 'pid';
$options['vars'][$i]['prompt'] = 'Choose the match you want to delete the player from:';
$options['vars'][$i]['caption'] = 'Match:';
$i++;

$results = adminselect($options);


$matchid = $results['mid'];
$pid = $results['pid'];

echo'<br><table border="0" cellpadding="1" cellspacing="2" width="600">
<tr>
	<td class="smheading" align="center" colspan="2">Delete Player From Match ID '.$matchid.'</td>
</tr>';

echo'<tr>
	<td class="smheading" align="left" width="200">Amending Players Rank:</td>';

$q_radjust = small_query("SELECT pid, gid, rank FROM uts_player WHERE id = $pid");
if (!$q_radjust) {
	$sql_crank = false;
} else {
	$rank_pid = $q_radjust[pid];
	$rank_gid = $q_radjust[gid];
	$rank = $q_radjust[rank];

	$sql_crank = small_query("SELECT id, rank, matches FROM uts_rank WHERE pid = $rank_pid AND gid = '$rank_gid'");
}
if (!$sql_crank) {
	echo'<td class="grey" align="left" width="400">Player not in rankings</td>';
} else {
	$rid = $sql_crank[id];
	$newrank = $sql_crank[rank]-$rank;
	$oldrank = $sql_crank[rank];
	$matchcount = $sql_crank[matches]-1;
	
	mysql_query("UPDATE uts_rank SET rank = $newrank, prevrank = $oldrank, matches = $matchcount WHERE id = $rid") or die(mysql_error());
	mysql_query("DELETE FROM uts_rank WHERE matches = 0") or die(mysql_error());
	
	echo'<td class="grey" align="left" width="400">Done</td>';
}
echo'</tr>
<tr>
	<td class="smheading" align="left">Removing Kill Matrix Entries:</td>';
	$q_match = mysql_query("SELECT matchid, playerid FROM uts_player WHERE pid = '$pid' and matchid = '$matchid'") or die(mysql_error());
	while ($r_match = mysql_fetch_array($q_match)) {
		mysql_query("DELETE FROM uts_killsmatrix WHERE matchid = '${r_match['matchid']}' AND (killer = '${r_match['playerid']}' OR victim = '${r_match['playerid']}')") or die(mysql_error());
	}
	echo'<td class="grey" align="left">Done</td>
</tr>


<tr>
	<td class="smheading" align="left" width="200">Removing Player Weapon Stats:</td>';
mysql_query("DELETE FROM uts_weaponstats WHERE matchid = $matchid AND pid = $pid") or die(mysql_error());
	echo'<td class="grey" align="left" width="400">Done</td>
</tr>
<tr>
	<td class="smheading" align="left" width="200">Removing Player From Match:</td>';
mysql_query("DELETE FROM uts_player WHERE matchid = $matchid AND pid = $pid") or die(mysql_error());
	echo'<td class="grey" align="left" width="400">Done</td>
</tr>
<tr>
	<td class="smheading" align="left" width="200">Amending Player Weapon Stats:</td>';
mysql_query("DELETE FROM uts_weaponstats WHERE matchid IN ('$matchid','0') AND pid = '$pid'") or die(mysql_error());

$q_weaponstats = mysql_query("SELECT SUM(kills) AS kills, SUM(shots) AS shots, SUM(hits) as hits, SUM(damage) as damage, AVG(acc) AS acc FROM uts_weaponstats WHERE pid = '$pid'  GROUP BY weapon") or die(mysql_error());
while ($r_weaponstats = mysql_fetch_array($q_weaponstats)) {
	mysql_query("INSERT INTO uts_weaponstats SET matchid='0', pid='$pid', kills='${r_weaponstats['kills']}', shots='${r_weaponstats['shots']}', hits='${r_weaponstats['hits']}', damage='${r_weaponstats['damage']}', acc='${r_weaponstats['acc']}'") or die(mysql_error());
}
	echo'<td class="grey" align="left" width="400">Done</td>
</tr>
<tr>
	<td class="smheading" align="left" width="200">Amending Global Weapon Stats:</td>';
	mysql_query("DELETE FROM uts_weaponstats WHERE matchid='0' AND pid='0'") or die(mysql_error());

	$q_weaponstats = mysql_query("SELECT weapon, SUM(kills) AS kills, SUM(shots) AS shots, SUM(hits) as hits, SUM(damage) as damage, AVG(acc) AS acc FROM uts_weaponstats WHERE matchid = '0'  GROUP BY weapon") or die(mysql_error());
	while ($r_weaponstats = mysql_fetch_array($q_weaponstats)) {
		mysql_query("INSERT INTO uts_weaponstats SET matchid='0', pid='0', weapon='${r_weaponstats['weapon']}', kills='${r_weaponstats['kills']}', shots='${r_weaponstats['shots']}', hits='${r_weaponstats['hits']}', damage='${r_weaponstats['damage']}', acc='${r_weaponstats['acc']}'") or die(mysql_error());
	}

	echo'<td class="grey" align="left" width="400">Done</td>
</tr>

<tr>
	<td class="smheading" align="center" colspan="2">Match Deleted - <a href="./admin.php?key='.$_REQUEST[key].'">Go Back To Admin Page</a></td>
</tr></table>';

?>
