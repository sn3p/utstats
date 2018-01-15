<?php
if (empty($import_adminkey) or isset($_REQUEST['import_adminkey']) or $import_adminkey != $adminkey) die('Access denied');

$filename = (!empty($_REQUEST['filename'])) ? my_stripslashes($_REQUEST['filename']) : '';
$filename = str_replace(array('..', '/', '\\', '<', ':'), array(), $filename);

function InvertSort($curr_field, $order, $sort) {
	if ($curr_field != $order) return(($curr_field == "time") ? "DESC" : "ASC");
	if ($sort == 'ASC') return('DESC');
	return('ASC');
}

function SortPic($curr_field, $order, $sort) {
	if ($curr_field != $order) return;
	$fname = 'assets/images/s_'. strtolower($sort) .'.png';
	if (!file_exists($fname)) return;
	return('&nbsp;<img src="'. $fname .'" border="0" width="11" height="9" alt="" title="('.strtolower($sort).'ending)">');
}

// Get filter, order and set sorting
if (isset($_GET[order])) {
	$order = my_addslashes($_GET[order]);
	setcookie('uts_ace_order', $_GET['order'], time()+60*60*24*30*365);
}
else if (isset($_COOKIE['uts_ace_order'])){
	$order = $_REQUEST['uts_ace_order'];
}
else {
	$order = "time";
}

if (isset($_GET[sort])) {
	$sort = my_addslashes($_GET[sort]);
	setcookie('uts_ace_sort', $_GET['sort'], time()+60*60*24*30*365);
}
else if (isset($_COOKIE['uts_ace_sort'])){
	$sort = $_REQUEST['uts_ace_sort'];
}
else {
	$sort = ($order == "time") ? "DESC" : "ASC";
}

if (isset($_GET[timeout])) {
	$timeout = my_addslashes($_GET[timeout]);
	setcookie('uts_ace_timeout', $_GET['timeout'], time()+60*60*24*30*365);
}
else if (isset($_COOKIE['uts_ace_timeout'])){
	$timeout = $_REQUEST['uts_ace_timeout'];
}
else {
	$timeout = 0;
}

if (isset($_GET[show])) {
	$show = my_addslashes($_GET[show]);
	setcookie('uts_ace_show', $_GET['show'], time()+60*60*24*30*365);
}
else if (isset($_COOKIE['uts_ace_show'])){
	$show = $_REQUEST['uts_ace_show'];
}
else {
	$show = "week";
}

if (!empty($filename)) {
	if (!file_exists('logs/ace/'. $filename) or !is_file('logs/ace/'. $filename)) die('File not found');
	if (isset($_REQUEST['del'])) {
		unlink('logs/ace/'. $filename);
		$filename = '';
	}
}

if (empty($filename)) {
	echo '
	<script language = "javascript">

	function timefilter() {
		var filter = document.filter.show.value;
		window.open("admin.php?key='.$adminkey.'&action='.$action.'&show="+filter, "_parent");
	}

	function toggletimeouts() {
		if (document.filter.timeouts.checked == true) {
			window.open("admin.php?key='.$adminkey.'&action='.$action.'&timeout=1", "_parent");
		}
		else {
			window.open("admin.php?key='.$adminkey.'&action='.$action.'&timeout=0", "_parent");
		}
	}
	</script>
	<form name="filter">
	<table class="box" border="0" cellpadding="0" cellspacing="0" width="720">
	<tr>
		<td class="heading" align="center" width="100%" colspan="5">Available ACE Logs:</td>
	</tr>
	<tr>
		<td class="smheading" align="center" width="100%" colspan="5">Filter:
		<select onchange = "javascript:timefilter()" name = "show">
			<option value = "all"'.($show == "all" ? ' selected=selected' : '').'>All</option>
			<option value = "day"'.($show == "day" ? ' selected=selected' : '').'>Last day</option>
			<option value = "week"'.($show == "week" ? ' selected=selected' : '').'>Last week</option>
			<option value = "month"'.($show == "month" ? ' selected=selected' : '').'>Last month</option>
		</select>
		<input name = "timeouts" type = "checkbox" value = 1 onchange = "javascript:toggletimeouts()"'.($timeout == 1 ? ' checked="yes"' : '').'> Show timeouts</td>
	</tr>
  <tr>
    <td class="smheading" align="center"><a class="smheading" href="admin.php?key='.$adminkey.'&amp;action='.$action.'&amp;order=time&amp;sort='.InvertSort('time', $order, $sort).'">Time</a>'.SortPic('time', $order, $sort).'</td>
    <td class="smheading" align="center"><a class="smheading" href="admin.php?key='.$adminkey.'&amp;action='.$action.'&amp;order=name&amp;sort='.InvertSort('name', $order, $sort).'">Player Name</a>'.SortPic('name', $order, $sort).'</td>
    <td class="smheading" align="center"><a class="smheading" href="admin.php?key='.$adminkey.'&amp;action='.$action.'&amp;order=reason&amp;sort='.InvertSort('reason', $order, $sort).'">Kick Reason</a>'.SortPic('reason', $order, $sort).'</td>
    <td class="smheading" align="center" colspan = 2></td>
  </tr>';
	$logdir = opendir('logs/ace');
	$logs = array();
	$sortlogs = array();
	$i = 0;
	while (false !== ($filename = readdir($logdir))) {
		if (!is_file('logs/ace/'. $filename)) continue;
		if ($filename == '.htaccess' or $filename == 'index.htm') continue;

		// ereg_match('/\d{4}\.\d{2}.\d{2}.\d{2}.\d{2}.\d{2}/', $filename, &$date);
		// $adate = preg_split('/\./', $date[0]);
		preg_match('/\d{4}\.\d{2}.\d{2}.\d{2}.\d{2}.\d{2}/', $filename, $date);
		$adate = explode(".", $date[0]);

		// filter on days
		if ($show != "all") {
			// calculate days
			$logAgeInDays = abs(strtotime(date("Y/m/d")) - strtotime("$adate[0]/$adate[1]/$adate[2]"))/68400-1;
			if (($show == "day" and $logAgeInDays > 1)
				or ($show == "week" and $logAgeInDays > 7)
				or ($show == "month" and $logAgeInDays > 31)) {
				continue;
			}
		}

		$i++;
		$TimeStamp = "unknown";
		$PlayerName = "unknown";
		$KickReason = "unknown";

		if (substr($filename, strlen($filename) - strlen($import_ace_screenshot_extension)) == $import_ace_screenshot_extension
		and substr($filename, 0, strlen($import_ace_screenshot_start)) == $import_ace_screenshot_start) {
			// Screenshot
			$TimeStamp = "$adate[2]-$adate[1]-$adate[0] / $adate[3]:$adate[4]:$adate[5]";
			$PlayerName = "Unknown";
			$KickReason = "Screenshot";
		}
		else {
			// logfile
			$compression = null;
			$fp = my_fopen("logs/ace/" . $filename, "rb", $compression);

			if (!$fp) die("Error opening file");

			while (($line = my_fgets($fp, 5000, $compression)) !== FALSE) {
				$info = preg_split('/\s/', $line, 3);
				$info[2] = preg_replace('/[\r\n]+/', '', $info[2]);
				if ($info[1] == "TimeStamp....:") {
					$TimeStamp = $info[2];
				}
				else if ($info[1] == "PlayerName...:") {
					$PlayerName = $info[2];
				}
				else if ($info[1] == "KickReason...:") {
					$KickReason = $info[2];
				}
				else if ($info[1] == "RequestedBy..:") {
					$KickReason = "Requested Screenshot";
				}
				else if ($info[1] == "WARNING:") {
					$ainfo = preg_split('/\s/', $info[2]);
					if ($ainfo[5] == "UDP") {
					      $TimeStamp = "$adate[2]-$adate[1]-$adate[0] / $adate[3]:$adate[4]:$adate[5]";
					      $PlayerName = $ainfo[1];
					      $KickReason = "Proxy/Tunnel";
					}
				}
				else if ($info[2] == ": Kicked - [REASON] Timeout during check spawn") {
					$TimeStamp = "$adate[2]-$adate[1]-$adate[0] / $adate[3]:$adate[4]:$adate[5]";
					$PlayerName = preg_replace('/[\[\]]/', '', $info[1]);
					$KickReason = "Timeout during check spawn";
				}
			}
			my_fclose($fp, $compression);
		}

		// don't show timeout logs?
		if (($timeout == 0)
			and (($KickReason == "Timeout during check spawn")
			or ($KickReason == "Timeout during checks")
			or ($KickReason == "Timeout during initial check"))) {
			continue;
		}

		$logs[$i] = array($filename, $TimeStamp, $PlayerName, $KickReason);
		$extra = 0;
		if (substr($filename, -4) == '.bz2') $extra = 4;
		if (substr($filename, -3) == '.gz') $extra = 3;

		if ($order == "time") {
			$sortlogs[$i] = $date[0];
		}
		else if ($order == "name") {
			$sortlogs[$i] = strtolower($PlayerName);
		}
		else if ($order == "reason") {
			$sortlogs[$i] = strtolower($KickReason);
		}
	}
	closedir($logdir);
	if (count($logs) == 0) {
		echo '<tr><td class="grey" colspan="5">No logs available!</td></tr>';
	} else {
		if ($sort == "ASC") {
			asort($sortlogs);
		}
		else {
			arsort($sortlogs);
		}
		$i = 0;
		foreach($sortlogs as $id => $date) {
			$log = $logs[$id][0];
			if (empty($log)) {
			      continue;
			}
			$TimeStamp = $logs[$id][1];
			$PlayerName = $logs[$id][2];
			$KickReason = $logs[$id][3];
			$Screenshot = $logs[$id][4];
			$extra = 0;
			if (substr($log, -4) == '.bz2') $extra = 4;
			if (substr($log, -3) == '.gz') $extra = 3;

			$i++;
			$class = ($i%2) ? 'grey' : 'grey2';
			echo '<tr><td class="'.$class.'">';
			$tmp = substr($log, strlen($log) - (23 + $extra), 19);
			$tmp = str_replace('.', '', $tmp);
			// $ts = mtimestamp($tmp);
			echo '<a class="'.$class.'" href="admin.php?key='.$adminkey.'&amp;action='.$action.'&amp;filename='.urlencode($log).'">'.$TimeStamp.'</a>';
			echo '</td><td class="'.$class.'">';
			echo '<a class="'.$class.'" href="admin.php?key='.$adminkey.'&amp;action='.$action.'&amp;filename='.urlencode($log).'">'.$PlayerName.'</a>';
			echo '</td><td class="'.$class.'">';
			echo '<a class="'.$class.'" href="admin.php?key='.$adminkey.'&amp;action='.$action.'&amp;filename='.urlencode($log).'">'.$KickReason.'</a>';
			echo '</td><td class="'.$class.'" align="right">';
			$d_size = file_size_info(filesize('logs/ace/'. $log));
			echo $d_size['size'] .' '. $d_size['type'];
			echo '</td><td class="'.$class.'" align="center">';
			echo '<a href="admin.php?key='.$adminkey.'&amp;action='.$action.'&amp;filename='.urlencode($log).'&amp;del=1"><img src="assets/images/del.png" border="0" width="16" height="16" title="Delete" alt="Delete" /></a>';
			echo '</td></tr>';
		}
	}
	echo '</form>';
}


if (!empty($filename)) {
	if (!file_exists('logs/ace/'. $filename) or !is_file('logs/ace/'. $filename)) die('File not found (2)');
	echo'<br><table class="box" border="0" cellpadding="0" cellspacing="0" width="720">
	<tr>
		<td class="smheading" align="center" width="95%" colspan="4">'.$filename.'</td>
		<td class="smheading" align="center" width="5%" align="right">';
		echo '<a href="admin.php?key='.$adminkey.'&amp;action='.$action.'&amp;filename='.urlencode($filename).'&amp;del=1"><img src="assets/images/del.png" border="0" width="16" height="16" title="Delete" alt="Delete" /></a>';
		echo '</td>
	</tr>';
	if (substr($filename, strlen($filename) - strlen($import_ace_screenshot_extension)) == $import_ace_screenshot_extension
		and substr($filename, 0, strlen($import_ace_screenshot_start)) == $import_ace_screenshot_start) {
		// screenshot
		echo '<tr><td class="grey" colspan="5"><span style="font-family: monospace;">';
			echo '<a href = "logs/ace/'.preg_replace('/\+/', '%20', urlencode($filename)).'" target = "_blank"><img src = "logs/ace/'.preg_replace('/\+/', '%20', urlencode($filename)).'" width = 100% border = 0></a>';
		echo '</span></td></tr>';
	}
	else {
		$fp = my_fopen('logs/ace/'.$filename, 'rb', $compression = NULL);
		if (!$fp) die("Error opening file");

		$i = 0;
		echo '<tr><td class="grey" colspan="5"><span style="font-family: monospace;">';

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
			$info = preg_split('/\s/', $line, 3);
			$info[2] = preg_replace('/[\r\n]/', '', $info[2]);
			if ($info[1] == "FileName.....:") {
				$Screenshot = basename($info[2]);
			}

		}
		echo '</span></td></tr>';
		my_fclose($fp, $compression);

		// look for a matching ace screenshot
		if (isset($Screenshot) and file_exists('logs/ace/' . $Screenshot)) {
			echo 	'<tr>
				  <td class="smheading" align="center" width="95%" colspan="4">'.$Screenshot.'</td>
				  <td class="smheading" align="center" width="5%" align="right">';
			echo '<a href="admin.php?key='.$adminkey.'&amp;action='.$action.'&amp;filename='.preg_replace('/\+/', '%20', urlencode($Screenshot)).'&amp;del=1"><img src="assets/images/del.png" border="0" width="16" height="16" title="Delete" alt="Delete" /></a>';
			echo '</td>
			  </tr>';

			echo '<tr><td class="grey" colspan="5"><span style="font-family: monospace;">';
			echo '<a href="logs/ace/'.preg_replace('/\+/', '%20', urlencode($Screenshot)).'" target="_blank"><img src="logs/ace/'.preg_replace('/\+/', '%20', urlencode($Screenshot)).'" width=100% border=0></a>';
			echo '</span></td></tr>';
		}
	}
}

echo '</tr>';
if (!empty($filename)) echo'<tr><td class="smheading" align="center" colspan="5"><a class="grey" href="./admin.php?key='.$_REQUEST[key].'&amp;action='.$action.'">Go Back To Logfile Overview</a></td></tr>';
echo'<tr><td class="smheading" align="center" colspan="5"><a class="grey" href="./admin.php?key='.$_REQUEST[key].'">Go Back To Admin Page</a></td></tr>';
echo '</table>';

?>
