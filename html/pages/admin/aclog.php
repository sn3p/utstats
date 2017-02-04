<?php
if (empty($import_adminkey) or isset($_REQUEST['import_adminkey']) or $import_adminkey != $adminkey) die('bla');

$filename = (!empty($_REQUEST['filename'])) ? my_stripslashes($_REQUEST['filename']) : '';
$filename = str_replace(array('..', '/', '\\', '<', ':'), array(), $filename);


if (!empty($filename)) {
	if (!file_exists('logs/ac/'. $filename) or !is_file('logs/ac/'. $filename)) die('bla');
	if (isset($_REQUEST['del'])) {
		unlink('logs/ac/'. $filename);
		$filename = '';
	}
}




if (empty($filename)) {
	echo'<br><table border="0" cellpadding="1" cellspacing="2" width="600">
	<tr>
		<td class="smheading" align="center" width="100%" colspan="4">Available AnthChecker Logs:</td>
	</tr>';
	$logdir = opendir('logs/ac');
	$logs = array();
	$sort = array();
	$i = 0;
	while (false !== ($filename = readdir($logdir))) {
		if (!is_file('logs/ac/'. $filename)) continue;
		if ($filename == '.htaccess' or $filename == 'index.htm') continue;
		$i++;
		$logs[$i] = $filename;
		$extra = 0;
		if (substr($filename, -4) == '.bz2') $extra = 4;
		if (substr($filename, -3) == '.gz') $extra = 3;

		$sort[$i] = substr($filename, strlen($filename) - (23 + $extra), 19);
	}
	closedir($logdir);
	if (count($logs) == 0) {
		echo '<tr><td class="grey" colspan="4">No logs available!</td></tr>';
	} else {
		arsort($sort);
		$i = 0;
		foreach($sort as $id => $date) {
			$log = $logs[$id];
			$extra = 0;
			if (substr($log, -4) == '.bz2') $extra = 4;
			if (substr($log, -3) == '.gz') $extra = 3;

			$i++;
			$class = ($i%2) ? 'grey' : 'grey2';
			echo '<tr><td class="'.$class.'">';
			$tmp = substr($log, strlen($log) - (23 + $extra), 19);
			$tmp = str_replace('.', '', $tmp);
			$ts = mtimestamp($tmp);
			echo '  <a class="'.$class.'" href="admin.php?key='.$adminkey.'&amp;action='.$action.'&amp;filename='.urlencode($log).'">'.date('Y-m-d H:i', $ts).'</a>';
			echo '</td><td class="'.$class.'">';
			echo '<a class="'.$class.'" href="admin.php?key='.$adminkey.'&amp;action='.$action.'&amp;filename='.urlencode($log).'">'.substr($log, 6, strlen($log) - 30 - $extra).'</a>';
			echo '</td><td class="'.$class.'" align="right">';
			$d_size = file_size_info(filesize('logs/ac/'. $log));
			echo $d_size['size'] .' '. $d_size['type'];
			echo '</td><td class="'.$class.'" align="center">';
			echo '<a href="admin.php?key='.$adminkey.'&amp;action='.$action.'&amp;filename='.urlencode($log).'&amp;del=1"><img src="images/del.png" border="0" width="16" height="16" title="Delete" alt="Delete" /></a>';
			echo '</td></tr>';
		}	
	}
}


if (!empty($filename)) {
	if (!file_exists('logs/ac/'. $filename) or !is_file('logs/ac/'. $filename)) die('bla');
	echo'<br><table border="0" cellpadding="1" cellspacing="2" width="600">
	<tr>
		<td class="smheading" align="center" width="95%" colspan="3">'.$filename.'</td>
		<td class="smheading" align="center" width="5%" align="right">';
		echo '<a href="admin.php?key='.$adminkey.'&amp;action='.$action.'&amp;filename='.urlencode($filename).'&amp;del=1"><img src="images/del.png" border="0" width="16" height="16" title="Delete" alt="Delete" /></a>';
		echo '</td>
	</tr>';
	
	$fp = my_fopen('logs/ac/'.$filename, 'rb', $compression = NULL);
	if (!$fp) die("Error opening file");
	
	$i = 0;
	echo '<tr><td class="grey" colspan="4"><span style="font-family: monospace;">';
	
	while (($line = my_fgets($fp, 5000, $compression)) !== FALSE) {
		/*
		$i++;
		$class = ($i%2) ? 'grey' : 'grey2';
		echo '<tr><td class="'.$class.'" colspan="4">';
		echo '<span style="font-family: monospace;">';
		echo htmlentities($line);
		echo '</span>';
		echo '</td></tr>';
		*/
		echo wordwrap(htmlentities($line), 80, '<br />', 1) ."<br />";
	}
	echo '</span></td></tr>';
	my_fclose($fp, $compression);
}

echo '</tr>';
if (!empty($filename)) echo'<tr><td class="smheading" align="center" colspan="4"><a class="grey" href="./admin.php?key='.$_REQUEST[key].'&amp;action='.$action.'">Go Back To Logfile Overview</a></td></tr>';
echo'<tr><td class="smheading" align="center" colspan="4"><a class="grey" href="./admin.php?key='.$_REQUEST[key].'">Go Back To Admin Page</a></td></tr>';
echo '</table>';

?>
