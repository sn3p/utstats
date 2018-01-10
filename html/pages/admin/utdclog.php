<?php
if (empty($import_adminkey) or isset($_REQUEST['import_adminkey']) or $import_adminkey != $adminkey) die('bla');

$filename = (!empty($_REQUEST['filename'])) ? my_stripslashes($_REQUEST['filename']) : '';
$filename = str_replace(array('..', '/', '\\', '<', ':'), array(), $filename);


if (!empty($filename)) {
	if (!file_exists('logs/utdc/'. $filename) or !is_file('logs/utdc/'. $filename)) die('bla');
	if (isset($_REQUEST['del'])) {
		unlink('logs/utdc/'. $filename);
		$filename = '';
	}
}




if (empty($filename)) {
	echo'<br><table class = "box" border="0" cellpadding="0" cellspacing="0" width="600">
	<tr>
		<td class="smheading" align="center" width="100%" colspan="4">Available UTDC Logs:</td>
	</tr>';
	$logdir = opendir('logs/utdc');
	$logs = array();
	$sort = array();
	$i = 0;
	while (false !== ($filename = readdir($logdir))) {
		if (!is_file('logs/utdc/'. $filename)) continue;
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
			$d_size = file_size_info(filesize('logs/utdc/'. $log));
			echo $d_size['size'] .' '. $d_size['type'];
			echo '</td><td class="'.$class.'" align="center">';
			echo '<a href="admin.php?key='.$adminkey.'&amp;action='.$action.'&amp;filename='.urlencode($log).'&amp;del=1"><img src="assets/images/del.png" border="0" width="16" height="16" title="Delete" alt="Delete" /></a>';
			echo '</td></tr>';
		}
	}
}


if (!empty($filename)) {
	if (!file_exists('logs/utdc/'. $filename) or !is_file('logs/utdc/'. $filename)) die('bla');
	echo'<br><table class = "box" border="0" cellpadding="0" cellspacing="0" width="600">
	<tr>
		<td class="smheading" align="center" width="95%" colspan="3">'.$filename.'</td>
		<td class="smheading" align="center" width="5%" align="right">';
		echo '<a href="admin.php?key='.$adminkey.'&amp;action='.$action.'&amp;filename='.urlencode($filename).'&amp;del=1"><img src="assets/images/del.png" border="0" width="16" height="16" title="Delete" alt="Delete" /></a>';
		echo '</td>
	</tr>';
	if (substr($filename, -4) == '.enc') {
		echo '<tr><td class="grey" colspan="4"><span style="font-family: monospace;">';
		echo '<a href = "pages/admin/utdcshot.php?filename='.urlencode($filename).'" target = "_blank"><img src = "pages/admin/utdcshot.php?filename='.urlencode($filename).'" width = 100% border = 0></a>';
		echo '</span></td></tr>';
	}
	else {
		$fp = my_fopen('logs/utdc/'.$filename, 'rb', $compression = NULL);
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

		// look for a matching utdc screenshot
		$logdir = opendir('logs/utdc');
		if (substr($filename, -4) == '.bz2') $extra = 4;
		if (substr($filename, -3) == '.gz') $extra = 3;
		while (false !== ($file = readdir($logdir))) {
			if (!is_file('logs/utdc/'. $filename)) continue;
			if ($filename == '.htaccess' or $filename == 'index.htm') continue;
			if ((substr($file, -4) == '.enc') and
			    ((substr($filename, strlen($filename)-(23 + $extra), 19) == substr($file, strlen($file)-23, 19)) or ((substr($filename, strlen($filename)-(23 + $extra), 17) == substr($file, strlen($file)-23, 17)) and (abs(intval(substr($file, strlen($file)- 6, 2)) - intval(substr($filename, strlen($filename)-(6 + $extra), 17)))  <=3)))) {
				echo '<tr>
					  <td class="smheading" align="center" width="95%" colspan="3">'.$file.'</td>
					  <td class="smheading" align="center" width="5%" align="right">
							<a href="admin.php?key='.$adminkey.'&amp;action='.$action.'&amp;filename='.urlencode($file).'&amp;del=1">
								<img src="assets/images/del.png" border="0" width="16" height="16" title="Delete" alt="Delete" />
							</a>
						</td>
					</tr>';
				echo '<tr><td class="grey" colspan="4"><span style="font-family: monospace;">';
				echo '<a href="pages/admin/utdcshot.php?filename='.urlencode($file).'" target="_blank"><img src="pages/admin/utdcshot.php?filename='.urlencode($file).'" width="100%" border="0"></a>';
				echo '</span></td></tr>';
			}
		}
		closedir($logdir);
	}
}

echo '</tr>';
if (!empty($filename)) echo'<tr><td class="smheading" align="center" colspan="4"><a class="grey" href="./admin.php?key='.$_REQUEST[key].'&amp;action='.$action.'">Go Back To Logfile Overview</a></td></tr>';
echo'<tr><td class="smheading" align="center" colspan="4"><a class="grey" href="./admin.php?key='.$_REQUEST[key].'">Go Back To Admin Page</a></td></tr>';
echo '</table>';

?>
