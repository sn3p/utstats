<?php
if (empty($import_adminkey) or isset($_REQUEST['import_adminkey']) or $import_adminkey != $adminkey) die('bla');
if (isset($_REQUEST['droptable'])) {
	$droptable = my_addslashes($_REQUEST['droptable']);
	if (substr($droptable, 0, 9) == 'uts_temp_' and strlen($droptable) == 17) {
		mysql_query("DROP TABLE $droptable;") or die(mysql_error());
	} else {
		die('NO!');
	}
}


// Graph width
$max_width = 150;
function nf($number) {
	return(number_format($number));
}


echo'<table class = "box" border="0" cellpadding="1" cellspacing="2" width="600">
<tr>
	<td class="smheading" align="center" height="25" colspan="4">Database Statistics</td>
</tr>';

$q_dbsize = mysql_query("SHOW table STATUS") or die(mysql_error());
$tot_size = 0;
$tot_rows = 0;
$max_size = 0;
while ($r_dbsize = mysql_fetch_array($q_dbsize)) {
	if (substr($r_dbsize['Name'], 0, 4) != 'uts_') continue;
	$size = $r_dbsize['Data_length'] + $r_dbsize['Index_length'];
	$rows = $r_dbsize['Rows'];
	$tables[] = array	(
							'name'		=>	$r_dbsize['Name'],
							'size'		=>	$size,
							'rows'		=> $rows
							);
	$tot_size += $size;
	$tot_rows += $rows;
	if ($max_size < $size) $max_size = $size;

}

$i = 0;
foreach($tables as $table) {
	$i++;
	$class = ($i%2) ? 'grey' : 'grey2';

	$d_size = file_size_info($table['size']);
	$title = get_dp($table['size'] / $tot_size * 100) .' %';
	echo'<tr>
		<td class="smheading" align="left" width="200">';
	if (substr($table['name'], 0, 9) == 'uts_temp_' and strlen($table['name']) == 17) {
		echo '<a href="admin.php?key='. urlencode($adminkey) .'&amp;action=main&amp;droptable='.htmlentities($table['name']).'"><img src="images/del.png" border="0" width="16" height="16" title="Click to drop this table" alt="Delete" /></a><em>'.$table['name'].'</em>';
	} else {
		echo $table['name'];
	}
	echo '</td>
		<td class="'.$class.'" align="right">'.nf($table['rows']).' rows</td>
		<td class="'.$class.'" align="right">'.$d_size['size'] .' '. $d_size['type'].'</td>
		<td class="'.$class.'" width="'.($max_width + 5).'"><img border="0" src="images/bars/h_bar'. ($i % 16 + 1) .'.png" height="10" width="'.(int)($table['size'] / $max_size * $max_width).'" alt="'. $title .'" title="'. $title .'"></td>
	</tr>';
}

$d_size = file_size_info($tot_size);
echo'<tr>
	<td class="smheading" align="left" width="200">Total Database Size</td>
	<td class="darkgrey" align="right">'.nf($tot_rows).' rows</td>
	<td class="darkgrey" align="right">'.$d_size['size'] .' '. $d_size['type'].'</td>
	<td class="darkgrey" >&nbsp;</td>
</tr>
</table><br>';

echo'<table border="0" cellpadding="1" cellspacing="2" width="600">
	<tr><td width="100%">';

echo '
	<ul>';
		echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=mplayers">Merge Players</a></li>';
		echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=mservers">Merge Servers</a></li>';
		echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=plm">Merge IPs with more than 1 Player</a></li>';
		echo '<br>';
		echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=dmatch">Delete Match</a></li>';
		echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=dpmatch">Delete Player From Match</a></li>';
		echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=dplayer">Delete Player</a></li>';
		echo '<br>';
		echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=pban&amp;saction=ban">Ban Player</a></li>';
		echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=pban&amp;saction=unban">Unban Player</a></li>';
		echo '<br>';
		echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=pinfo">Extended Player Info</a></li>';
		echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=ipsearch">Search IP</a></li>';
		if ($import_utdc_download_enable) {
			echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=utdclog">View UTDC logs</a></li>';
		}
		if ($import_ac_download_enable) {
			echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=aclog">View AnthChecker logs</a></li>';
		}
		if ($import_ace_download_enable) {
			echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=acelog">View ACE logs</a></li>';
		}
		echo '<br>';
		echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=editweapons">Edit Weapons</a></li>';
		echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=editgames">Add/Edit Game Names</a></li>';
		echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=editgamestype">Add/Edit Game Types</a></li>';
		echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=recalcranking">Recalculate Rankings</a></li>';
		echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=recalcflags">Recalculate Countryflags</a></li>';
		echo '<br>';
		echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=check">Check server settings</a></li>';
		echo '<li><a href="admin.php?key='. urlencode($adminkey) .'&amp;action=emptydb">Empty the database</a></li>';
echo '
</ul>
';



echo'</td></tr></table>';
?>
