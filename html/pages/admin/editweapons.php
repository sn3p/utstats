<?php
if (empty($import_adminkey) or isset($_REQUEST['import_adminkey']) or $import_adminkey != $adminkey) die('bla');
	
if (isset($_REQUEST['submit'])) {
	foreach($_REQUEST['image'] as $id => $bla) {
		mysql_query("	UPDATE	uts_weapons
								SET	image = '". my_addslashes($_REQUEST['image'][$id]) ."',
										sequence = '". my_addslashes($_REQUEST['sequence'][$id]) ."',
										hide = '". (isset($_REQUEST['hide'][$id]) ? 'Y' : 'N') ."'
								WHERE	id =  '$id';") or die(mysql_error());
	}
}

echo'<br><table border="0" cellpadding="1" cellspacing="2" width="600">
<tr>
	<td class="medheading" align="center" colspan="4">Edit weapons</td>
</tr>
<tr>
	<td class="smheading">Name</td>
	<td class="smheading" align="center" '.OverlibPrintHint('', 'Image to display instead of the weapon\'s name<br>Should exist in images/weapons/<br>Leave empty to display the weapon name').'>Image</td>
	<td class="smheading" '.OverlibPrintHint('', 'Use this number to set the weapons order to your liking').'>Order</td>
	<td class="smheading" '.OverlibPrintHint('', 'If checked, this weapon won\'t be shown (including kills, shots, acc, ...)').'>Hide</td>
</tr>
<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
<input type="hidden" name="key" value="'.$adminkey.'">
<input type="hidden" name="action" value="'.$action.'">
';


$sql_weapons = "SELECT id, name, image, sequence, hide FROM uts_weapons ORDER BY sequence ASC;";
$q_weapons = mysql_query($sql_weapons) or die(mysql_error());
$i = 0;
while ($r_weapons = mysql_fetch_array($q_weapons)) {
	$i++;
	$class = ($i%2) ? 'grey' : 'grey2';
	echo '<tr>';
	echo '<td class="'.$class.'">'.htmlentities($r_weapons['name']).'</td>';
	echo '<td class="'.$class.'">';
	echo '<table border="0" cellspacing="0" cellpadding="0" width="100%"><tr><td width="60%">';
	echo '<input class="searchform" type="text" name="image['.$r_weapons['id'].']" value="'.$r_weapons['image'].'">';
	echo '</td><td width="40%" align="center">';
	if (!empty($r_weapons['image'])) echo ' <img src="images/weapons/'. $r_weapons['image'] .'" border="0">';
	echo' </td></tr></table></td>';
	echo '<td class="'.$class.'"><input class="searchform" type="text" name="sequence['.$r_weapons['id'].']" value="'.$r_weapons['sequence'].'" size="3" maxlength="3"></td>';
	echo '<td class="'.$class.'"><input class="searchform" type="checkbox" name="hide['.$r_weapons['id'].']" '.($r_weapons['hide'] == 'Y' ? 'checked' : '').'></td>';
	echo '</tr>';
}

echo '<tr>';
echo '<td class="darkgrey" colspan="4" align="center"><input class="searchformb" type="Submit" name="submit" value="Save"></td>';
echo '</tr>';

echo'</form>
	<tr>
	<td class="smheading" align="center" colspan="4"><a class="grey" href="./admin.php?key='.$_REQUEST[key].'">Go Back To Admin Page</a></td>
</tr></table>';
	
?>
