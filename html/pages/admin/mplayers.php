<?php
if (empty($import_adminkey) or isset($_REQUEST['import_adminkey']) or $import_adminkey != $adminkey) die('bla');
	
$options['title'] = 'Merge Players';
$i = 0;
$options['vars'][$i]['name'] = 'mplayer1';
$options['vars'][$i]['type'] = 'player';
$options['vars'][$i]['prompt'] = 'Choose player to merge to:';
$options['vars'][$i]['caption'] = 'Player to merge to:';
$i++;
$options['vars'][$i]['name'] = 'mplayer2';
$options['vars'][$i]['type'] = 'player';
$options['vars'][$i]['prompt'] = 'Choose player to merge from:';
$options['vars'][$i]['caption'] = 'Player to merge from:';
$options['vars'][$i]['exclude'] = 'mplayer1';
$i++;

$results = adminselect($options);



$mplayer1 = $results['mplayer1'];
$mplayer2 = $results['mplayer2'];


$mp1name = small_query("SELECT name FROM uts_pinfo WHERE id = $mplayer1");
$mp2name = small_query("SELECT name FROM uts_pinfo WHERE id = $mplayer2");

echo'<br><table border="0" cellpadding="0" cellspacing="0" width="600">
<tr>
	<td class="smheading" align="center" colspan="2">Merging '.$mp2name[name].' Into '.$mp1name[name].'</td>
</tr>
<tr>
	<td class="smheading" align="left" width="200">Removing Info Records</td>';
mysqli_query($GLOBALS["___mysqli_link"], "DELETE FROM uts_pinfo WHERE id = $mplayer2") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	echo'<td class="grey" align="left" width="400">Done</td>
</tr>
<tr>
	<td class="smheading" align="left" width="200">Updating Player Records</td>';
mysqli_query($GLOBALS["___mysqli_link"], "UPDATE uts_player SET pid = $mplayer1  WHERE pid = $mplayer2") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	echo'<td class="grey" align="left" width="400">Done</td>
</tr>
<tr>
	<td class="smheading" align="left" width="200">Updating Weapon Records</td>';
mysqli_query($GLOBALS["___mysqli_link"], "UPDATE uts_weaponstats SET pid = $mplayer1  WHERE pid = $mplayer2") or die(mysqli_error($GLOBALS["___mysqli_link"]));
mysqli_query($GLOBALS["___mysqli_link"], "DELETE FROM uts_weaponstats WHERE pid = $mplayer2") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	echo'<td class="grey" align="left" width="400">Done</td>
</tr>
<tr>
	<td class="smheading" align="left" width="200">Amending Player Weapon Stats:</td>';
mysqli_query($GLOBALS["___mysqli_link"], "DELETE FROM uts_weaponstats WHERE matchid='0' AND pid = '$mplayer1'") or die(mysqli_error($GLOBALS["___mysqli_link"]));

$q_weaponstats = mysqli_query($GLOBALS["___mysqli_link"], "SELECT weapon, SUM(kills) AS kills, SUM(shots) AS shots, SUM(hits) as hits, SUM(damage) as damage, AVG(acc) AS acc FROM uts_weaponstats WHERE pid = '$mplayer1'  GROUP BY weapon") or die(mysqli_error($GLOBALS["___mysqli_link"]));
while ($r_weaponstats = mysqli_fetch_array($q_weaponstats)) {
	mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_weaponstats SET matchid='0', pid='$mplayer1',  weapon='${r_weaponstats['weapon']}', kills='${r_weaponstats['kills']}', shots='${r_weaponstats['shots']}', hits='${r_weaponstats['hits']}', damage='${r_weaponstats['damage']}', acc='${r_weaponstats['acc']}'") or die(mysqli_error($GLOBALS["___mysqli_link"]));
}
	echo'<td class="grey" align="left" width="400">Done</td>
</tr>
<tr>
	<td class="smheading" align="left" width="200">Updating First Blood Records</td>';
mysqli_query($GLOBALS["___mysqli_link"], "UPDATE uts_match SET firstblood = $mplayer1  WHERE firstblood = $mplayer2") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	echo'<td class="grey" align="left" width="400">Done</td>
</tr>
<tr>
	<td class="smheading" align="left" width="200">Temporary Rank</td>';
mysqli_query($GLOBALS["___mysqli_link"], "UPDATE uts_rank SET pid = $mplayer2 WHERE pid= $mplayer1") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	echo'<td class="grey" align="left" width="400">Done</td>
</tr>
<tr>
	<td class="smheading" align="left" width="200">Creating New Rank</td>';

$sql_nrank = "SELECT SUM(time) AS time, pid, gid, AVG(rank) AS rank, AVG(prevrank) AS prevrank, SUM(matches) AS matches FROM uts_rank WHERE pid = $mplayer2 GROUP BY pid, gid";
$q_nrank = mysqli_query($GLOBALS["___mysqli_link"], $sql_nrank) or die(mysqli_error($GLOBALS["___mysqli_link"]));
while ($r_nrank = mysqli_fetch_array($q_nrank)) {

	mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_rank SET time = '$r_nrank[time]', pid = $mplayer1, gid = $r_nrank[gid], rank = '$r_nrank[rank]', prevrank = '$r_nrank[prevrank]', matches = $r_nrank[matches]") or die(mysqli_error($GLOBALS["___mysqli_link"]));
}

	echo'<td class="grey" align="left" width="400">Done</td>
</tr>
<tr>
	<td class="smheading" align="left" width="200">Removing Old Rank</td>';
mysqli_query($GLOBALS["___mysqli_link"], "DELETE FROM uts_rank WHERE pid = $mplayer2") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	echo'<td class="grey" align="left" width="400">Done</td>
</tr>
<tr>
	<td class="smheading" align="center" colspan="2">Player Record Merged - <a href="./admin.php?key='.$_REQUEST[key].'">Go Back To Admin Page</a></td>
</tr>
</table>';

?>
