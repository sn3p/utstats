<?php
if (empty($import_adminkey) or isset($_REQUEST['import_adminkey']) or $import_adminkey != $adminkey) die('bla');
	
if (isset($_REQUEST['submit'])) {
	foreach($_REQUEST['name'] as $id => $bla) {
		if ($id == 0) {
			if (empty($_REQUEST['name'][$id])) continue;
			mysql_query("	INSERT	INTO	uts_games
									SET	name = '". my_addslashes($_REQUEST['name'][$id]) ."',
											gamename = '(user defined)';") or die(mysql_error());
		} else {
			mysql_query("	UPDATE	uts_games
									SET	name = '". my_addslashes($_REQUEST['name'][$id]) ."'
									WHERE	id =  '$id';") or die(mysql_error());
		}
	}
}

echo'<br><table border="0" cellpadding="1" cellspacing="2" width="600">
<tr>
	<td class="medheading" align="center" colspan="2">Edit games</td>
</tr>
<tr>
	<td class="smheading">GameName</td>
	<td class="smheading">DisplayName</td>
</tr>
<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
<input type="hidden" name="key" value="'.$adminkey.'">
<input type="hidden" name="action" value="'.$action.'">
';


$sql_games = "SELECT id, gamename, name FROM uts_games ORDER BY gamename ASC;";
$q_games = mysql_query($sql_games) or die(mysql_error());
$i = 0;
while ($r_games = mysql_fetch_array($q_games)) {
	$i++;
	$class = ($i%2) ? 'grey' : 'grey2';
	echo '<tr>';
	echo '<td class="'.$class.'">'.htmlentities($r_games['gamename']).'</td>';
	echo '<td class="'.$class.'"><input class="searchform" type="text" name="name['.$r_games['id'].']" value="'.$r_games['name'].'"></td>';
	echo '</tr>';
}
echo'
<tr>
	<td colspan="2">&nbsp;</td>
</tr>
<tr>
	<td class="smheading" colspan="2" align="center">Add new</td>
</tr>
<tr>
	<td class="grey">(n/a)</td>
	<td class="grey"><input class="searchform" type="text" name="name[0]" value=""></td></td>
</tr>
<tr>
	<td colspan="2">&nbsp;</td>
</tr>
<tr>
	<td class="smheading" colspan="2" align="center">Submit</td>
</tr>
';


echo '<tr>';
echo '<td class="darkgrey" colspan="4" align="center"><input class="searchformb" type="Submit" name="submit" value="Save"></td>';
echo '</tr>';

echo'</form>
	<tr>
	<td class="smheading" align="center" colspan="4"><a class="grey" href="./admin.php?key='.$_REQUEST[key].'">Go Back To Admin Page</a></td>
</tr>
</table>';
	
?>
