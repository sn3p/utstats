<?php
if (empty($import_adminkey) or isset($_REQUEST['import_adminkey']) or $import_adminkey != $adminkey) die('bla');
	
$options['title'] = 'Merge Servers';
$i = 0;
$options['vars'][$i]['name'] = 'mserver1';
$options['vars'][$i]['type'] = 'server';
$options['vars'][$i]['prompt'] = 'Choose the server to merge to:';
$options['vars'][$i]['caption'] = 'Server to merge to:';
$i++;
$options['vars'][$i]['name'] = 'mserver2';
$options['vars'][$i]['type'] = 'server';
$options['vars'][$i]['prompt'] = 'Choose the server to merge from:';
$options['vars'][$i]['caption'] = 'Server to merge from:';
$options['vars'][$i]['exclude'] = 'mserver1';
$i++;

$results = adminselect($options);


$mserver1 = $results[mserver1];
$mserver2 = $results[mserver2];

$q_myserver1 = small_query("SELECT servername, serverip FROM uts_match WHERE id = $mserver1");
$q_myserver2 = small_query("SELECT servername, serverip FROM uts_match WHERE id = $mserver2");

echo'<br><table border="0" cellpadding="1" cellspacing="2" width="600">
<tr>
	<td class="smheading" align="center" colspan="2">Merging '.$q_myserver2[servername].' ('.$q_myserver2[serverip].')<br>
	Into '.$q_myserver1[servername].' ('.$q_myserver1[serverip].')</td>
</tr>
<tr>
	<td class="smheading" align="left" width="200">Merging Records</td>';
mysql_query("UPDATE uts_match SET serverip = '". addslashes($q_myserver1[serverip]) ."', servername = '". addslashes($q_myserver1[servername]) ."' WHERE serverip = '". addslashes($q_myserver2[serverip]) ."' and servername = '". addslashes($q_myserver2[servername]) ."'") or die(mysql_error());
	echo'<td class="grey" align="left" width="400">Done</td>
</tr>
<tr>
	<td class="smheading" align="center" colspan="2">Server Records Merged - <a href="./admin.php?key='.$_REQUEST[key].'">Go Back To Admin Page</a></td>
</tr></table>';

?>
