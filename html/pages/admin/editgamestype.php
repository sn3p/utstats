<?php
if (empty($import_adminkey) or isset($_REQUEST['import_adminkey']) or $import_adminkey != $adminkey) die('bla');
	
$sql_server = "SELECT id, servername, serverip FROM uts_match GROUP BY servername, serverip ORDER BY servername ASC";
$q_server = mysql_query($sql_server) or die(mysql_error());
$servernames  = array('0' => '');
$serverips = array('0' => '*');
while ($r_server = mysql_fetch_array($q_server)) {
	$servernames[$r_server['id']] = $r_server['servername'];
	$serverips[$r_server['id']] = $r_server['serverip'];
}

$sql_games = "SELECT id, gamename, name FROM uts_games ORDER BY gamename ASC";
$q_games = mysql_query($sql_games) or die(mysql_error());
$gamedisplaynames = array('0' => '');
$gamenames = array('0' => '*');
while ($r_games = mysql_fetch_array($q_games)) {
	$gamenames[$r_games['id']] = $r_games['gamename'];
	$gamedisplaynames[$r_games['id']] = $r_games['name'];
}


if (isset($_REQUEST['submit'])) {
	mysql_query("	INSERT	INTO	uts_gamestype
							SET	serverip = '". my_addslashes($_REQUEST['serverip']) ."',
									gamename = '". my_addslashes($_REQUEST['gamename']) ."',
									mutator = '". my_addslashes($_REQUEST['mutator']) ."',
									gid = '". my_addslashes($_REQUEST['gid']) ."'
					") or die(mysql_error());
	
	if (isset($_REQUEST['update'])) {
		echo'<br><table border="0" cellpadding="1" cellspacing="2" width="600">
				<tr>
					<td class="smheading" align="center" colspan="2">Updating...</td>
				</tr>
				
				
				
				<tr>
					<td class="smheading" align="left" width="200">Updating Player Records</td>';
		$where = 'WHERE 1';
		if ($_REQUEST['serverip'] != '*') {
			$where .= " AND m.serverip = '". my_addslashes($_REQUEST['serverip']) ."'";
		}
		if ($_REQUEST['gamename'] != '*') {
			$gids = array_keys($gamenames, my_stripslashes($_REQUEST['gamename']));
			$where .= " AND m.gid IN (".implode(',', $gids).")";
		}
		if ($_REQUEST['mutator'] != '*') {
			$where .= " AND m.mutators LIKE '%".my_addslashes($_REQUEST['mutator'])."%'";
		}
		
		mysql_query("UPDATE uts_player p, uts_match m SET p.gid = '". my_addslashes($_REQUEST['gid']) ."' $where AND m.id = p.matchid;") or die(mysql_error());
		echo'<td class="grey" align="left" width="400">Done (updated '.mysql_affected_rows().' records)</td>
				</tr>
				
				
				
				
				<tr>
					<td class="smheading" align="left" width="200">Updating Matches</td>';
		mysql_query("UPDATE uts_match m SET m.gid = '". my_addslashes($_REQUEST['gid']) ."' $where;") or die(mysql_error());
		echo'<td class="grey" align="left" width="400">Done (updated '.mysql_affected_rows().' matches)</td>
				</tr>
				
				
				
				
				
				<tr>
					<td class="smheading" align="left" width="200">Re-Calcuating Rankings</td>';
		
		if ($_REQUEST['gamename'] != '*') {
			$gids[] = $_REQUEST['gid'];
			$where = "WHERE gid IN (".implode(',', $gids).")";
		} else {
			$where = 'WHERE 1';
		}
		mysql_query("DELETE FROM uts_rank $where;") or die(mysql_error());
		
		$sql_nrank = "SELECT SUM(p.gametime) AS time, p.pid, p.gid, SUM(p.rank) AS rank, COUNT(p.matchid) AS matches FROM uts_player p, uts_pinfo pi $where AND pi.id = p.pid AND pi.banned <> 'Y' GROUP BY p.gid, p.pid";
		$q_nrank = mysql_query($sql_nrank) or die(mysql_error());
		$num_ranks = 0;
		while ($r_nrank = mysql_fetch_array($q_nrank)) {
			mysql_query("INSERT INTO uts_rank SET time = '${r_nrank['time']}', pid = ${r_nrank['pid']}, gid = ${r_nrank['gid']}, rank = '${r_nrank['rank']}', prevrank = '${r_nrank['rank']}', matches = ${r_nrank['matches']}") or die(mysql_error());
			$num_ranks++;
		}
		echo'<td class="grey" align="left" width="400">Done (recalculated '.$num_ranks.' rankings)</td>
				</tr>
				<tr>
					<td class="smheading" align="center" colspan="2">Update finished..</td>
				</tr>
			  </table>';
	}
}

if (isset($_REQUEST['del'])) {
	mysql_query("	DELETE 	FROM	uts_gamestype
						WHERE		id = '". my_addslashes($_REQUEST['del']) ."'
					") or die(mysql_error());
}



echo'<br><table border="0" cellpadding="1" cellspacing="2" width="600">
<tr>
	<td class="medheading" align="center" colspan="8">Current Mappings</td>
</tr>
<tr>
	<td class="smheading" width="130">&nbsp;Server IP</td>
	<td class="smheading" width="20"></td>
	<td class="smheading" width="130">&nbsp;Game Name</td>
	<td class="smheading" width="20"></td>
	<td class="smheading" width="130">&nbsp;Mutatorlist contains</td>
	<td class="smheading" width="20"></td>
	<td class="smheading" width="130">&nbsp;Game</td>
	<td class="smheading" width="20"></td>
</tr>';






$sql_gamestype = "SELECT id, serverip, gamename, mutator, gid FROM uts_gamestype ORDER BY id ASC;";
$q_gamestype = mysql_query($sql_gamestype) or die(mysql_error());
$i = 0;
while ($r_gamestype = mysql_fetch_array($q_gamestype)) {
	$i++;
	$class = ($i%2) ? 'grey' : 'grey2';
	echo '<tr>';
	echo '<td class="'.$class.'">&nbsp;'.htmlentities($r_gamestype['serverip']).'</td>';
	echo '<td class="smheading" align="center">+</td>';
	echo '<td class="'.$class.'">&nbsp;'.htmlentities($r_gamestype['gamename']).'</td>';
	echo '<td class="smheading" align="center">+</td>';
	echo '<td class="'.$class.'">&nbsp;'.htmlentities($r_gamestype['mutator']).'</td>';
	echo '<td class="smheading" align="center">=</td>';
	echo '<td class="'.$class.'">&nbsp;'.htmlentities($gamedisplaynames[$r_gamestype['gid']]).'</td>';
	echo '<td class="'.$class.'" align="center">';
		echo '<a href="'.$_SERVER['PHP_SELF'].'?action='.$action.'&amp;key='.$adminkey.'&amp;del='.$r_gamestype['id'].'">';
		echo '<img src="images/del.png" border="0" width="16" height="16" title="Delete" alt="Delete" />';
		echo '</a>';
	echo '</td>';
	echo '</tr>';
}

echo '</tr></table>';








echo'<br><table border="0" cellpadding="1" cellspacing="2" width="600">
<tr>
	<td class="medheading" align="center" colspan="2">Add new gamestype</td>
</tr>
<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
<input type="hidden" name="key" value="'.$adminkey.'">
<input type="hidden" name="action" value="'.$action.'">
';

$class='grey';
echo '<tr>';
echo '<td class="smheading" width="170">If server =</td>';
echo '<td class="'.$class.'">';
echo '<select class="searchform" name="serverip">';
foreach($serverips as $id => $bla) {
	echo '<option value="'.$serverips[$id].'">'. $serverips[$id]; 
	if (!empty($servernames[$id])) echo ' ('. $servernames[$id] .')';
	echo '</option>';
	}
echo '</select>';
echo '</td></tr>';


echo '<tr><td class="smheading" nowrap>and gamename =</td>';
echo '<td class="'.$class.'">';

echo '<select class="searchform" name="gamename">';
foreach($gamenames as $id => $bla) {
	if ($gamenames[$id] == '(user defined)') continue;
	echo '<option value="'.$gamenames[$id].'">'. $gamenames[$id]; 
	if (!empty($gamedisplaynames[$id])) echo ' ('. $gamedisplaynames[$id] .')';
	echo '</option>';
}
echo '</select>';
echo '</td></tr>';


echo '<tr><td class="smheading" nowrap>and mutatorlist contains</td>';
echo '<td class="'.$class.'">';
echo '<input type="text" class="searchform" name="mutator" value="*"> <span class="text2">(case insensitive substring)</span>';
echo '</td></tr>';


echo '<tr><td class="smheading" nowrap>==&gt; assume gametype:</td>';
echo '<td class="'.$class.'">';
	
echo '<select class="searchform" name="gid">';
foreach($gamenames as $id => $bla) {
	if ($gamenames[$id] == '*') continue;
	echo '<option value="'.$id.'">'. $gamenames[$id]; 
	if (!empty($gamedisplaynames[$id])) echo ' ['. $gamedisplaynames[$id] .']';
	echo '</option>';
}
echo '</select>';
echo '</td></tr>';

echo '<tr><td class="smheading">Update existing matches:</td>';
echo '<td class="'.$class.'">';
echo '<input type="checkbox" checked name="update"> <span class="text2">(this cannot be undone easily!)</span>';
echo '</td></tr>';


echo '<tr>';
echo '<td class="'.$class.'" colspan="2" align="center"><input class="searchformb" type="Submit" name="submit" value="Add"></td>';
echo '</tr>';

echo'</form>
	<tr>
	<td class="smheading" align="center" colspan="2"><a class="grey" href="./admin.php?key='.$_REQUEST[key].'">Go Back To Admin Page</a></td>
</tr>
</table>';
	
?>
