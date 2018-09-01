<?php
function add_info($name, $value) {
	if ($value == '' or $value === NULL) return('');
	return(htmlentities($name) ." ". htmlentities($value) ."<br>");
}

@ignore_user_abort(true);
@set_time_limit(0);

if (isset($_REQUEST['rememberkey'])) setcookie('uts_importkey', $_REQUEST['key'], time()+60*60*24*30*365);
if (isset($_COOKIE['uts_importkey'])) $adminkey = $_REQUEST['uts_importkey'];

require ("includes/config.php");
require ("includes/functions.php");

$compatible_actor_versions = array('0.4.0', '0.4.1', '0.4.2', 'beta 4.0', 'beta 4.1', 'beta 4.2');

// Get key from web browser
if (isset($_REQUEST['key'])) $adminkey = $_REQUEST['key'];
if (!isset($adminkey)) $adminkey = '';

// Debugging mode?
$debug = isset($_REQUEST['debug']) ? $_REQUEST['debug'] : false;

// Output HTML?
$html = isset($_REQUEST['html']) ? $_REQUEST['html'] : true;

// Were running from the command line (cron-jobs)
if (php_sapi_name() == 'cli' or !isset($_SERVER['SERVER_PORT']) or !$_SERVER['SERVER_PORT'])  {
	// No password needed when in cli mode.
	$adminkey = $import_adminkey;
	// There is no time limit when running the cli. And no page to reload :)
	$import_reload_after = 0;
	// No browser, no HTML
	$html = false;
	// Chdir to our homedir
	if (!empty($import_homedir)) chdir($import_homedir);
}

if ($html) include ("includes/header.php");

DeBugMessage("Execute: import.php");

if ($html) {
	echo'<table class = "box" border="0" cellpadding="1" cellspacing="2" width="720">
	<tr>
		<td class="heading" align="center" colspan="2">Importing Latest Log Files</td>
	</tr>';
}

if (empty($import_adminkey)) {
	if ($html) echo'<tr><td class="smheading" align="left" width="150">Error:</td><td class="grey" align="left">';
	echo "\$import_adminkey not set in config.php!\n";
	if ($html) {
		echo '</td></tr></table>';
		include("includes/footer.php");
	}
	return;
}

if (!empty($adminkey) and $adminkey != $import_adminkey) {
	if ($html) echo'<tr><td class="smheading" align="left" width="150">Error:</td><td class="grey" align="left">';
	echo "Keys do not match\n";
	$adminkey = '';
	if (!$html) return;
}

if (empty($adminkey)) {
	if (!$html) die('Please provide the adminkey' ."\n");
	echo'<tr>
		  <td class="smheading" align="left" width="150">Enter Admin key:</td>
		  <td class="grey" align="left"><form NAME="adminkey" ACTION="import.php">
		  <input TYPE="text" NAME="key" MAXLENGTH="35" SIZE="20" CLASS="searchform">
		  <input TYPE="submit" VALUE="Submit" CLASS="searchformb">
		  <input TYPE="checkbox" NAME="rememberkey"> Remember the key
		  </form></td>
		</tr></table>';
	include("includes/footer.php");
	return;
}

if (!@is_dir('logs')) {
	if ($html) echo'<tr><td class="smheading" align="left" width="150">Error:</td><td class="grey" align="left">';
	echo "Can't find the logs directory!\n";
	if ($html) echo "<br>";
	echo "Current working directory is: ". getcwd() ."\n";
	if ($html) echo "<br>";
	if (!$html) echo "You forgot to cd to my home directory? Take a look at \$import_homedir in config.php.\n";
	if ($html) {
		echo '</td></tr></table>';
		include("includes/footer.php");
	}
	return;
}

if ($html) echo'</table><br>';
echo "\n";

$start_time = time();
$files = isset($_REQUEST['files']) ? $_REQUEST['files'] : 0;
$elapsed = isset($_REQUEST['elapsed']) ? $_REQUEST['elapsed'] : 0;

if ($ftp_use and !isset($_GET['no_ftp'])) {
	DeBugMessage("Use ftp: yes\n\$ftp_use: " . gettype($ftp_use) . ":". var_export($ftp_use, true));
	require("includes/ftp.php");
	$elapsed = $elapsed - (time() - $start_time);
}
else {
	ob_flush();
	DeBugMessage("Use ftp: no\n\$ftp_use: " . gettype($ftp_use) . ":". var_export($ftp_use, true));
}

$logdir = opendir('logs');

DeBugMessage("Open logdir and read logs");

while (false !== ($filename = readdir($logdir))) {
// Our (self set) timelimit exceeded => reload the page to prevent srcipt abort
	if (!empty($import_reload_after) and $start_time + $import_reload_after <= time()) {
		if (!$html) die('Time limit exceeded - unable to reload page (no HTML output)' ."\n");

		$elapsed = $elapsed + time() - $start_time;
		$target = $PHP_SELF ."?key=". urlencode($adminkey) ."&amp;".str_rand()."=".str_rand()."&amp;no_ftp=1&amp;debug=$debug&amp;files=$files&amp;elapsed=$elapsed";
		echo '<meta http-equiv="refresh" content="2;URL='. $target .'">';

		echo'<br><table class = "box" border="0" cellpadding="1" cellspacing="2" width="720">
		  <tr>
			<td class="heading" align="center" colspan="2">Maximum execution time exeeded; restarting ...</td>
		  </tr>
		  </table>';

		include("includes/footer.php");
		return;
	}

	$oldfilename = $filename;
	$filename = 'logs/' . $filename;
	$backupfilename = 'logs/backup/' . $oldfilename;

	// UTDC log: Move to logs/utdc/
	if ($import_utdc_download_enable
		and substr($filename, strlen($filename) - strlen($import_utdc_log_extension)) == $import_utdc_log_extension
		and substr($oldfilename, 0, strlen($import_utdc_log_start)) == $import_utdc_log_start) {
			if ($import_utdc_log_compress == 'no') $import_utdc_log_compress = 'yes';
			if ($html) {
				echo'<table class="box" border="0" cellpadding="1" cellspacing="2">
				<tr>
					<td class="smheading" align="center" height="25" width="550" colspan="2">UTDC log: '.$oldfilename.'</td>
				</tr>
				<tr>
					<td class="smheading" align="left" width="350">';
			} else {
				echo "UTDC log: $oldfilename:\n";
			}
			echo 'Moving to logs/utdc/: ';
			if ($html) echo '</td><td class="grey" align="left" width="200">';
			echo backup_logfile($import_utdc_log_compress, $filename, 'logs/utdc/'.$oldfilename, true) . "\n";
			if ($html) echo '</td></tr></table><br>';
			echo "\n\n";
			unlink($filename);
			continue;
	}

	// UTDC shot: Move to logs/utdc/
	if ($import_utdc_download_enable
		and substr($filename, strlen($filename) - strlen($import_utdc_screenshot_extension)) == $import_utdc_screenshot_extension
		and substr($oldfilename, 0, strlen($import_utdc_screenshot_start)) == $import_utdc_screenshot_start) {
			if ($import_utdc_log_compress == 'no') $import_utdc_log_compress = 'yes';
			if ($html) {
				echo'<table class="box" border="0" cellpadding="1" cellspacing="2">
				<tr>
					<td class="smheading" align="center" height="25" width="550" colspan="2">UTDC log: '.$oldfilename.'</td>
				</tr>
				<tr>
					<td class="smheading" align="left" width="350">';
			} else {
				echo "UTDC log: $oldfilename:\n";
			}
			echo 'Moving to logs/utdc/: ';
			if ($html) echo '</td><td class="grey" align="left" width="200">';
			echo backup_logfile("none", $filename, 'logs/utdc/'.$oldfilename, true) . "\n";
			if ($html) echo '</td></tr></table><br>';
			echo "\n\n";
			unlink($filename);
			continue;
	}

	// ACE log: Move to logs/ace/
	if ($import_ace_download_enable
		and substr($filename, strlen($filename) - strlen($import_ace_log_extension)) == $import_ace_log_extension
		and substr($oldfilename, 0, strlen($import_ace_log_start)) == $import_ace_log_start) {
			if ($import_ace_log_compress == 'no') $import_ace_log_compress = 'yes';
			if ($html) {
				echo'<table class="box" border="0" cellpadding="1" cellspacing="2">
				<tr>
					<td class="smheading" align="center" height="25" width="550" colspan="2">ACE log: '.$oldfilename.'</td>
				</tr>
				<tr>
					<td class="smheading" align="left" width="350">';
			} else {
				echo "ACE log: $oldfilename:\n";
			}
			echo 'Moving to logs/ace/: ';
			if ($html) echo '</td><td class="grey" align="left" width="200">';
			echo backup_logfile($import_ace_log_compress, $filename, 'logs/ace/'.$oldfilename, true) . "\n";
			if ($html) echo '</td></tr></table><br>';
			echo "\n\n";
			unlink($filename);
			continue;
	}

	if ($import_ace_download_enable
		and substr($filename, strlen($filename) - strlen($import_ace_screenshot_extension)) == $import_ace_screenshot_extension
		and substr($oldfilename, 0, strlen($import_ace_screenshot_start)) == $import_ace_screenshot_start) {
			if ($html) {
				echo'<table class="box" border="0" cellpadding="1" cellspacing="2">
				<tr>
					<td class="smheading" align="center" height="25" width="550" colspan="2">ACE Screenshot: '.$oldfilename.'</td>
				</tr>
				<tr>
					<td class="smheading" align="left" width="350">';
			} else {
				echo "ACE screenshot: $oldfilename:\n";
			}
			echo 'Moving to logs/ace/: ';
			if ($html) echo '</td><td class="grey" align="left" width="200">';
			echo backup_logfile("none", $filename, 'logs/ace/'.$oldfilename, false) . "\n";
			if ($html) echo '</td></tr></table><br>';
			echo "\n\n";
			unlink($filename);
			continue;
	}

	// AnthChecker log: Move to logs/ac/
	if ($import_ac_download_enable
		and substr($filename, strlen($filename) - strlen($import_ac_log_extension)) == $import_ac_log_extension
		and substr($oldfilename, 0, strlen($import_ac_log_start)) == $import_ac_log_start) {
			if ($import_ac_log_compress == 'no') $import_ac_log_compress = 'yes';
			if ($html) {
				echo'<table class="box" border="0" cellpadding="1" cellspacing="2">
				<tr>
					<td class="smheading" align="center" height="25" width="550" colspan="2">AC log: '.$oldfilename.'</td>
				</tr>
				<tr>
					<td class="smheading" align="left" width="350">';
			} else {
				echo "AC log: $oldfilename:\n";
			}
			echo 'Moving to logs/ac/: ';
			if ($html) echo '</td><td class="grey" align="left" width="200">';
			echo backup_logfile($import_ac_log_compress, $filename, 'logs/ac/'.$oldfilename, true) . "\n";
			if ($html) echo '</td></tr></table><br>';
			echo "\n\n";
			unlink($filename);
			continue;
	}

	if (substr($filename, strlen($filename) - strlen($import_log_extension)) != $import_log_extension) continue;
	if (substr($oldfilename, 0, strlen($import_log_start)) != $import_log_start) continue;

	// Create a unique ID
	$uid = str_rand();

	// Check if there are any logs to do ...

	// Create our temp Table

	for (;;) {
		$sql = "CREATE ". ($import_use_temporary_tables ? 'TEMPORARY ' : '') ."TABLE `uts_temp_$uid` (
		`id` mediumint(5) NOT NULL,
		`col0` char(20) NOT NULL default '',
		`col1` char(120) NOT NULL default '',
		`col2` char(120) NOT NULL default '',
		`col3` char(120) NOT NULL default '',
		`col4` char(120) NOT NULL default '',
		KEY `part1` (`col1` (20),`col2` (20)),
		KEY `part2` (`col0` (20),`col1` (20),`col2` (20)),
		KEY `full` (`col0` (20),`col1` (20),`col2` (20),`col3` (20),`col4` (20))
		) ENGINE=". ($import_use_heap_tables ? 'HEAP' : 'MyISAM') .";";

		$result = mysqli_query($GLOBALS["___mysqli_link"], $sql);
		if ($result) break;

		if (mysqli_errno($GLOBALS["___mysqli_link"]) == 1044 and $import_use_temporary_tables) {
			echo "<br><strong>WARNING: Unable to create temporary table (". mysqli_error($GLOBALS["___mysqli_link"]) .")<br>";
			echo "I'll retry without using MySQL's temporary table feature (see \$import_use_temporary_tables in config.php for details).<br><br></strong>";
			$import_use_temporary_tables = false;
			continue;
		}
		die("<br><strong>Unable to create the temporary table:<br>". mysqli_error($GLOBALS["___mysqli_link"]) ."<br><br></strong>");
	}
	$id = 0;

	if ($html) {
		echo'<table class="box" border="0" cellpadding="1" cellspacing="2">
		<tr>
			<td class="smheading" align="center" height="25" width="550" colspan="2">Importing '.$oldfilename.'</td>
		</tr>
		<tr>
			<td class="smheading" align="left" width="350">';
	} else {
		echo "Importing $oldfilename:\n";
	}
	echo 'Creating Temp MySQL Table: ';
	if ($html) echo '</td><td class="grey" align="left" width="200">';
	echo "uts_temp_$uid\n";
	if ($html) echo '</td></tr><tr><td class="smheading" align="left" width="350">';
	echo 'Backing Up Log File: ';
	if ($html) echo '</td><td class="grey" align="left" width="200">';

	// Copy the file to backup folder first
	echo backup_logfile($import_log_backup, $filename, $backupfilename, true) . "\n";

	if ($html) echo '</td><tr><td class="smheading" align="left" width="350">';
	echo 'Player Data Moved to Temp MySQL: ';
	if ($html) echo '</td>';

	// Create sql for NGLog
	$row = 1;
	$handle = fopen("$filename", "r");

	while (($data = my_fgets($handle, 5000)) !== FALSE) {
		if ($debug) debug_output('Raw input         ', $data);
		$data = preg_replace('/[\x00]/', '', $data);
		if ($debug) debug_output('After preg_replace', $data);
		$data = explode("\t", $data);

		$num = count($data);
		$row++;

		for ($c=0; $c < 1; $c++) {
			$col0 = addslashes($data[0]);
			$col1 = addslashes($data[1]);
			$col2 = addslashes($data[2]);
			$col3 = addslashes($data[3]);
			$col4 = addslashes($data[4]);

			$col0 = trim($col0, " \n\r");
			$col1 = trim($col1, " \n\r");
			$col2 = trim($col2, " \n\r");
			$col3 = trim($col3, " \n\r");
			$col4 = trim($col4, " \n\r");

			$id++;
			mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_temp_$uid VALUES ($id, '$col0', '$col1', '$col2', '$col3', '$col4');") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	   }
	}
	fclose($handle);
	$files++;

	if ($html) echo'<td class="grey" align="left" width="200">';
	echo "Yes\n";

	$log_incompatible = false;
	$actor_version = 'unknown';
	$qm_logtype = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'info' AND col2 = 'Log_Standard'");
	if ($qm_logtype['col3'] == 'UTStats') {
		$qm_logversion = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'info' AND col2 = 'Log_Version'");
		$actor_version = $qm_logversion['col3'];
	}

	if (!in_array($actor_version, $compatible_actor_versions)) {
		if ($import_incompatible_logs) {
			if ($html) echo '</td></tr><tr><td class="smheading" align="left" width="350" style="background-color: red;">';
			echo "WARNING: ";
			if ($html) echo '</td><td class="grey" align="left" width="200" style="background-color: red;">';
			echo "This logfile was created using an incompatible UTStats server actor version ($actor_version). You may experience strange results and/or bugs!\n";
		} else {
			$log_incompatible = true;
		}
	}

	if ($html) echo '</td></tr><tr><td class="smheading" align="left" width="350">';
	echo "Match Data Created: ";
	if ($html) echo '</td><td class="grey" align="left" width="200">';

	// Get the match table info
	$qm_time = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'info' AND col2 = 'Absolute_Time'");
	$qm_servername = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'info' AND col2 = 'Server_ServerName'");
	$qm_serverip = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'info' AND col2 = 'True_Server_IP'");
	$qm_serverport = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'info' AND col2 = 'Server_Port'");
	$qm_gamename = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'game' AND col2 = 'GameName'");

	$qm_gamestart = small_query("SELECT col0 FROM uts_temp_$uid WHERE col1 = 'game' AND col2 = 'realstart'");
	$qm_gameend = small_query("SELECT col0 FROM uts_temp_$uid WHERE col1 = 'game_end'");

	$qm_insta = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'game' AND col2 = 'insta'");
	$qm_tournament = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'game' AND col2 = 'TournamentMode'");
	$qm_teamgame = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'game' AND col2 = 'TeamGame'");
	$qm_mapname = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'map' AND col2 = 'Title'");
	$qm_mapfile = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'map' AND col2 = 'Name'");
	$qm_frags = small_query("SELECT SUM(col4) AS frags FROM uts_temp_$uid WHERE col1 = 'stat_player' AND col2 = 'frags'");
	$qm_kills = small_query("SELECT SUM(col4) AS kills FROM uts_temp_$uid WHERE col1 = 'stat_player' AND col2 = 'kills'");
	$qm_suicides = small_query("SELECT SUM(col4) AS suicides FROM uts_temp_$uid WHERE col1 = 'stat_player' AND col2 = 'suicides'");
	$qm_deaths = small_query("SELECT SUM(col4) AS deaths FROM uts_temp_$uid WHERE col1 = 'stat_player' AND col2 = 'deaths'");
	$qm_teamkills = small_query("SELECT SUM(col4) AS teamkills FROM uts_temp_$uid WHERE col1 = 'stat_player' AND col2 = 'teamkills'");

	$qm_playercount = small_count("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'player' AND col2 = 'rename' GROUP BY col3");

	$s_frags = $qm_frags[frags];
	$s_suicides = $qm_suicides[suicides];
	$s_deaths = $qm_deaths[deaths];

	// Add teamkills only if its a team game, else add them to kills total
	if ($qm_teamgame[col3] == "True") {
		$s_kills = $qm_kills[kills];
		$s_teamkills = $qm_teamkills[teamkills];
	} else {
		$s_kills = $qm_kills[kills]+$qm_teamkills[teamkills];
		$s_teamkills = 0;
	}

	// Change the gamename to bunny track if needed:
	if (($qm_gamename[col3] == "BunnyTrack2") || (($qm_gamename[col3] == "Capture the Flag") && (strtolower(substr($qm_mapfile[col3], 0, 7)) == "ctf-bt-"))) {
		$qm_gamename[col3] = "Bunny Track";
	}

	// Check if anything happened, if it didnt stop everything now
	//
	if (($qm_kills[kills] == 0 && $qm_deaths[deaths] == 0) && ($qm_gamename[col3] != "Bunny Track")) {
		echo "No (Empty Match)\n";
		if ($html) echo '</td></tr>';
	} elseIF (($qm_playercount < 2) || (($qm_gamename == "Bunny Track") and ($qm_playercount < 1)))  {
		echo "No (Not Enough Players)\n";
		if ($html) echo '</td></tr>';
	} elseIF ($log_incompatible)  {
		echo "No (Logfile incompatible [created by UTStats $actor_version])\n";
		if ($html) echo '</td></tr>';
	} elseIF ($import_ignore_if_gametime_less_than != 0 and ceil(($qm_gameend[col0] - $qm_gamestart[col0]) / 60) < $import_ignore_if_gametime_less_than)  {
		echo "No (game too short [". ceil(($qm_gameend[col0] - $qm_gamestart[col0]) / 60) ." &lt; $import_ignore_if_gametime_less_than minutes])\n";
		if ($html) echo '</td></tr>';
	} else {

		$sql_mutators = "SELECT col3 FROM uts_temp_$uid WHERE col1 = 'game' AND col2 = 'GoodMutator'";
		$q_mutators = mysqli_query($GLOBALS["___mysqli_link"], $sql_mutators);
		while ($r_mutators = mysqli_fetch_array($q_mutators)) {
			$qm_mutators .= "".$r_mutators[col3].", ";
		}


		$qm_serveran = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'info' AND col2 = 'Server_AdminName'");
		$qm_serverae = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'info' AND col2 = 'Server_AdminEmail'");
		$qm_serverm1 = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'info' AND col2 = 'Server_MOTDLine1'");
		$qm_serverm2 = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'info' AND col2 = 'Server_MOTDLine2'");
		$qm_serverm3 = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'info' AND col2 = 'Server_MOTDLine3'");
		$qm_serverm4 = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'info' AND col2 = 'Server_MOTDLine4'");

		$qm_gameinfotl = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'game' AND col2 = 'TimeLimit'");
		$qm_gameinfofl = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'game' AND col2 = 'FragLimit'");
		$qm_gameinfogt = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'game' AND col2 = 'GoalTeamScore'");
		$qm_gameinfomp = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'game' AND col2 = 'MaxPlayers'");
		$qm_gameinfoms = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'game' AND col2 = 'MaxSpectators'");
		$qm_gameinfogs = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'game' AND col2 = 'GameSpeed'");
		$qm_gameinfout = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'game' AND col2 = 'UseTranslocator'");
		$qm_gameinfoff = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'game' AND col2 = 'FriendlyFireScale'");
		$qm_gameinfows = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'game' AND col2 = 'WeaponsStay'");

		$gametime = $qm_time[col3];
		$servername = addslashes($qm_servername[col3]);
		$serverip = $qm_serverip[col3];
		$serverport = $qm_serverport[col3];
		$gamename = addslashes($qm_gamename[col3]);
		$servergametime = get_dp($qm_gameend[col0] - $qm_gamestart[col0]);
                $gamestart = $qm_gamestart[col0];
		$gameend = $qm_gameend[col0];

		$tournament = $qm_tournament[col3];
		$teamgame = $qm_teamgame[col3];
		$mapname = addslashes($qm_mapname[col3]);
		$mapfile = addslashes($qm_mapfile[col3]);

		// Lazy Hack for unknown gametypes
		$unknowngt = substr("$mapfile", 0, 3);	// Gets first 3 characters

		if ($unknowngt == "JB-") {
			$gamename = "JailBreak";
			$teamgame = 'True';
		}

		// Append insta to game if it was an insta game
		if (($qm_insta[col3] == "True") && ($gamename != "Bunny Track")) { $gameinsta = 1; $gamename = "$gamename (insta)"; } else { $gameinsta = 0; }

		// Get the unique ID of this gametype.
		// Create a new one if it has none yet.
		$r_gid = small_query("SELECT id FROM uts_games WHERE gamename = '$gamename'");
		if ($r_gid) {
			$gid = $r_gid['id'];
		} else {
			mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_games SET gamename = '$gamename', name = '$gamename'") or die(mysqli_error($GLOBALS["___mysqli_link"]));
			$gid = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_link"]))) ? false : $___mysqli_res);
		}

		// Check wheter we want to override the gametype for this match
		// (Useful if we want a server to have separate stats for one server or if we want to
		// combine DM and TDM rankings or ...)

		// Read all rules
		if (!isset($overriderules)) {
			$overriderules = array();
			$sql_overriderules = "SELECT id, serverip, gamename, mutator, gid FROM uts_gamestype ORDER BY id ASC;";
			$q_overriderules = mysqli_query($GLOBALS["___mysqli_link"], $sql_overriderules);
			while ($r_overriderules = mysqli_fetch_array($q_overriderules)) {
				$overriderules[$r_overriderules['id']]['serverip'] = $r_overriderules['serverip'];
				$overriderules[$r_overriderules['id']]['gamename'] = $r_overriderules['gamename'];
				$overriderules[$r_overriderules['id']]['mutator'] = $r_overriderules['mutator'];
				$overriderules[$r_overriderules['id']]['gid'] = $r_overriderules['gid'];
			}
		}

		// Check if one of our overriderules applies to this match
		foreach($overriderules as $rule) {
			if ($rule['serverip'] != '*' and $rule['serverip'] != "$serverip:$serverport") continue;
			if ($rule['gamename'] != '*' and $rule['gamename'] != $gamename) continue;
			if ($rule['mutator'] != '*' and stristr($qm_mutators, $rule['mutator']) === false) continue;
			$gid = $rule['gid'];
			break;
		}

		$qm_firstblood = small_query("SELECT col2 FROM uts_temp_$uid WHERE col1 = 'first_blood'");

		$firstblood = addslashes($qm_firstblood[col2]);

		$serverinfo = addslashes("Admin: $qm_serveran[col3]<br>Email: $qm_serverae[col3] <br><br>
		<u>MOTD</u><br>$qm_serverm1[col3]<br>$qm_serverm2[col3]<br>$qm_serverm3[col3]<br>$qm_serverm4[col3]");

		$gameinfo = addslashes(	add_info('Time Limit:', $qm_gameinfotl[col3]) .
										add_info('Frag Limit:', $qm_gameinfofl[col3]) .
										add_info('Goal Team Score:', $qm_gameinfogt[col3]) .
										add_info('Max Players:', $qm_gameinfomp[col3]) .
										add_info('Max Specs:', $qm_gameinfoms[col3]) .
										add_info('Game Speed:', $qm_gameinfogs[col3]) .
										add_info('Translocator:', $qm_gameinfout[col3]) .
										add_info('Friendly Fire:', $qm_gameinfoff[col3]) .
										add_info('Weapon Stay:', $qm_gameinfows[col3]) .
										add_info('UTStats Actor Version:', $actor_version));

		// Tidy Up The Info

		$mutators = substr("$qm_mutators", 0, -2);		// remove trailing ,
		$mutators = un_ut($mutators);				// Remove Class and BotPack. etc
		$mutators = addslashes($mutators);

		$gametime = utdate($gametime);

		// Get Teams Info
		$sql_tinfo = "SELECT col4 FROM uts_temp_$uid WHERE col1 = 'player' AND col2 = 'TeamName'  GROUP BY col4 ORDER BY col4 ASC";
		$q_tinfo = mysqli_query($GLOBALS["___mysqli_link"], $sql_tinfo) or die(mysqli_error($GLOBALS["___mysqli_link"]));

		$t0info = 0;
		$t1info = 0;
		$t2info = 0;
		$t3info = 0;

		while ($r_tinfo = mysqli_fetch_array($q_tinfo)) {
      if ($r_tinfo[col4] == "Red") { $t0info = 1; }
      if ($r_tinfo[col4] == "Blue") { $t1info = 1; }
      if ($r_tinfo[col4] == "Green") { $t2info = 1; }
      if ($r_tinfo[col4] == "Gold") { $t3info = 1; }
		}

		// Get Teamscores
		$sql_tscore = "SELECT col2 AS team, col3 AS score FROM uts_temp_$uid WHERE col1 = 'teamscore'";
		$q_tscore = mysqli_query($GLOBALS["___mysqli_link"], $sql_tscore) or die(mysqli_error($GLOBALS["___mysqli_link"]));

		$t0score = 0;
		$t1score = 0;
		$t2score = 0;
		$t3score = 0;

		while ($r_tscore = mysqli_fetch_array($q_tscore)) {
			if ($r_tscore['team'] == "0") $t0score = $r_tscore['score'];
			if ($r_tscore['team'] == "1") $t1score = $r_tscore['score'];
			if ($r_tscore['team'] == "2") $t2score = $r_tscore['score'];
			if ($r_tscore['team'] == "3") $t3score = $r_tscore['score'];
		}

// Insert Server Info Into Database
$sql_serverinfo = "INSERT INTO uts_match (time, servername, serverip, gamename, gid, gametime, mutators, insta, tournament, teamgame, mapname, mapfile, serverinfo, gameinfo, frags, kills, suicides, teamkills, deaths,
t0, t1, t2, t3, t0score, t1score, t2score, t3score)
VALUES ('$gametime', '$servername', '$serverip:$serverport', '$gamename', '$gid', '$servergametime', '$mutators', '$gameinsta', '$tournament',
'$teamgame', '$mapname', '$mapfile', '$serverinfo', '$gameinfo', '$s_frags', '$s_kills', '$s_suicides', '$s_teamkills', '$s_deaths',
$t0info, $t1info, $t2info, $t3info, $t0score, $t1score, $t2score, $t3score);";

		$q_serverinfo = mysqli_query($GLOBALS["___mysqli_link"], $sql_serverinfo) or die(mysqli_error($GLOBALS["___mysqli_link"]));
		$matchid = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_link"]))) ? false : $___mysqli_res);			// Get our Match ID

		echo "Yes (ID: $matchid)\n";
		if ($html) echo '</td></tr>';

		// Process Player Stuff
		$playerid2pid = array();
		$ignored_players = array();
		$imported_players = array();

		if ($html) echo '<tr><td class="smheading" align="left" width="350">';
		echo "Importing Players: ";
		if ($html) echo '</td><td class="grey" align="left" width="200">';

		// Get List of Player IDs and Process What They Have Done
		$sql_player = "SELECT DISTINCT col4 FROM uts_temp_$uid WHERE col1 = 'player' AND col2 = 'rename' AND col4 <> ''";
		$q_player = mysqli_query($GLOBALS["___mysqli_link"], $sql_player) or die(mysqli_error($GLOBALS["___mysqli_link"]));

		while ($r_player = mysqli_fetch_array($q_player)) {
			$playerid = $r_player[col4];

			// Get players last name used
			$r_player2 = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'player' AND col2 = 'rename' AND col4 = $playerid ORDER BY id DESC LIMIT 0,1");
			$playername = addslashes($r_player2[col3]);

			// Are they a Bot
			$r_player1 = small_query("SELECT col4 FROM uts_temp_$uid WHERE col1 = 'player' AND col2 = 'IsABot' AND col3 = $playerid ORDER BY id DESC LIMIT 0,1");
			$playertype = $r_player1[col4];
			// This player is a bot
			if ($playertype == 'True' and $import_ignore_bots) {
				$ignored_players[] = $playername;
				// We do not want to know who killed and who was killed by this bot...
				mysqli_query($GLOBALS["___mysqli_link"], "DELETE FROM uts_temp_$uid WHERE (col1 = 'kill' OR col1 = 'teamkill') AND (col2 = '$playerid' OR col4 = '$playerid');") or die(mysqli_error($GLOBALS["___mysqli_link"]));
				if ($html) echo "<span style='text-decoration: line-through;'>";
				echo "Bot:$playername ";
				if ($html) echo "</span>";
				continue;
			}

			// Get players last team
			$r_player3 = small_query("SELECT col4 FROM uts_temp_$uid WHERE col1 = 'player' AND col2 = 'TeamChange' AND col3 = $playerid AND col4 != 255 ORDER BY id DESC LIMIT 0,1");
			$playerteam = $r_player3[col4];

			$qc_kills = small_query("SELECT col4 FROM uts_temp_$uid WHERE col1 = 'stat_player' AND col2 = 'kills'AND col3 = $playerid");
			$qc_teamkills = small_query("SELECT col4 FROM uts_temp_$uid WHERE col1 = 'stat_player' AND col2 = 'teamkills' AND col3 = $playerid");
			$qc_deaths = small_query("SELECT col4 FROM uts_temp_$uid WHERE col1 = 'stat_player' AND col2 = 'deaths' AND col3 = $playerid");

			// Player had no kills, deaths or teamkills => ignore
			IF ($qc_kills[col4] == 0 && $qc_deaths[col4] == 0 && $qc_teamkills[col4] ==0 && $gamename != "Bunny Track") {
				$ignored_players[] = $playername;
				continue;
			}

			// Process all the other player information
			include("import/import_playerstuff.php");

			if ($playerbanned) {
				// Banned players don't have a rank.
				mysqli_query($GLOBALS["___mysqli_link"], "DELETE FROM uts_rank WHERE pid = '$pid'");

				if ($import_ban_type == 2) {
					// We do not want to know who killed and who was killed by this banned player
					$ignored_players[] = $playername;
					mysqli_query($GLOBALS["___mysqli_link"], "DELETE FROM uts_temp_$uid WHERE (col1 = 'kill' OR col1 = 'teamkill') AND (col2 = '$playerid' OR col4 = '$playerid');") or die(mysqli_error($GLOBALS["___mysqli_link"]));
					if ($html) echo "<span style='text-decoration: line-through;'>";
					echo "Banned:$playername ";
					if ($html) echo "</span>";
					continue;
				}
			}

			// Get Gametype specific stuff done
			if ($gamename == "Assault" || $gamename == "Assault (insta)") { include("import/import_ass.php"); }
			if ($gamename == "Capture the Flag" || $gamename == "Capture the Flag (insta)") { include("import/import_ctf.php"); }
			if ($gamename == "Bunny Track") { include("import/import_bt.php"); }
			if ($gamename == "Domination" || $gamename == "Domination (insta)") { include("import/import_dom.php"); }
			if ($gamename == "Tournament Team Game" || $gamename == "Tournament Team Game (insta)") { include("import/import_tdm.php"); }
			if ($gamename == "JailBreak" || $gamename == "JailBreak (insta)") { include("import/import_jailbreak.php"); }
			if ($gamename == "Last Man Standing" || $gamename == "Last Man Standing (insta)") { include("import/import_lms.php"); }
			if ($gamename == "Extended Last Man Standing" || $gamename == "Extended Last Man Standing (insta)") { include("import/import_lms.php"); }
			if ($gamename == "Last Man Standing +" || $gamename == "Last Man Standing + (insta)") { include("import/import_lms.php"); }
			if ($gamename == "Last Man Standing++" || $gamename == "Last Man Standing++ (insta)") { include("import/import_lms.php"); }

			// Do the rankings
			include("import/import_ranking.php");

			if ($playerbanned) {
					if ($html) echo "<span style='font-style: italic;'>";
					echo "Banned:";
			}
			echo $playername.' ';
			if ($playerbanned and $html) echo "</span>";
			if ($html) echo "<br>";
			$imported_players[] = $playername;
		}
		if ($html) echo '</td></tr>';
		// Check if theres any players left, if none or one delete the match (its possible ...)
		$final_pcount = small_count("SELECT id FROM uts_player WHERE matchid = $matchid");

		if ($final_pcount == NULL || ($final_pcount == 1 && $gamename != "Bunny Track")) {
				echo'<tr>
				<td class="smheading" align="left" width="350">Deleting Match:</td>
				<td class="grey" align="left" width="200">0 or 1 Player Entries Left</td>
			</tr>';

			$sql_radjust = "SELECT pid, gid, rank FROM uts_player WHERE matchid = $matchid";
			$q_radjust = mysqli_query($GLOBALS["___mysqli_link"], $sql_radjust) or die(mysqli_error($GLOBALS["___mysqli_link"]));
			while ($r_radjust = mysqli_fetch_array($q_radjust)) {
				$pid = $r_radjust[pid];
				$gid = $r_radjust[gid];
				$rank = $r_radjust[rank];

				$sql_crank = small_query("SELECT id, rank, matches FROM uts_rank WHERE pid = $pid AND gid = $gid");
				if (!$sql_crank) continue;

				$rid = $sql_crank[id];
				$newrank = $sql_crank[rank]-$rank;
				$oldrank = $sql_crank[rank];
				$matchcount = $sql_crank[matches]-1;

				mysqli_query($GLOBALS["___mysqli_link"], "UPDATE uts_rank SET rank = $newrank, prevrank = $oldrank, matches = $matchcount WHERE id = $rid") or die(mysqli_error($GLOBALS["___mysqli_link"]));
			}
			mysqli_query($GLOBALS["___mysqli_link"], "DELETE FROM uts_rank WHERE matches = 0") or die(mysqli_error($GLOBALS["___mysqli_link"]));

			$rem_mrecord = "DELETE FROM uts_match WHERE id = $matchid";
			mysqli_query($GLOBALS["___mysqli_link"], $rem_mrecord);
			$rem_precord = "DELETE FROM uts_player WHERE matchid = $matchid";
			mysqli_query($GLOBALS["___mysqli_link"], $rem_precord);
			$rem_precord = "DELETE FROM uts_events WHERE matchid = $matchid";
			mysqli_query($GLOBALS["___mysqli_link"], $rem_precord);
		} else {
			// Make our weapons statistics
			echo "\n";
			if ($html) echo '<tr><td class="smheading" align="left" width="350">';
			echo "Importing weapon statistics: ";
			if ($html) echo '</td><td class="grey" align="left" width="200">';
			include("import/import_weapons.php");
			echo "Done\n";
			if ($html) echo '</td></tr>';

			// Make our kills matrix stuff ...
			if ($html) echo '<tr><td class="smheading" align="left" width="350">';
			echo "Building kills matrix: ";
			if ($html) echo '</td><td class="grey" align="left" width="200">';
			include("import/import_killsmatrix.php");
			echo "Done\n";


			if ($html) echo '</td></tr><tr><td class="smheading" align="left" width="350">';
			echo "Combining Duplicate Player Entries: ";
			if ($html) echo '</td><td class="grey" align="left" width="200">';

			// Combine duplicate player entries ... very intensive :(
			include("import/import_pcleanup.php");

			echo "Done\n";
			if ($html) echo "</td></tr>";

			include("import/import_renderer-preconfig.php");

			$updategameinfo = false;
			if (count($ignored_players) > 0) {
				// Maybe we imported the player and ignored another record of him?
				$ignored_players = array_unique($ignored_players);
				foreach($ignored_players as $t_id => $t_name) {
					if (in_array($t_name, $imported_players)) unset($ignored_players[$t_id]);
				}
				if (count($ignored_players) > 0) {
					$gameinfo .= addslashes(add_info('Ignored Players:', implode(', ', $ignored_players)));
					$updategameinfo = true;
				}
			}
			if ($updategameinfo) {
				mysqli_query($GLOBALS["___mysqli_link"], "UPDATE uts_match SET gameinfo = '$gameinfo' WHERE id = '$matchid'");
				$updategameinfo = false;
			}
			if ($gamename == "Domination" || $gamename == "Domination (insta)" ) {
				if ($html) echo '<tr><td class="smheading" align="left" width="350">';
				echo "Generating dom graphs: ";
				if ($html) echo '</td><td class="grey" align="left" width="200">';
				include("import/import_renderer-dom.php");
				echo "Done\n";
				if ($html) echo'</td></tr>';
			}
			else if ($gamename == "Tournament DeathMatch" || $gamename == "Tournament DeathMatch (insta)" || $gamename == "Tournament Team Game" || $gamename == "Tournament Team Game (insta)" ) {
				if ($html) echo '<tr><td class="smheading" align="left" width="350">';
				echo "Generating dm graphs: ";
				if ($html) echo '</td><td class="grey" align="left" width="200">';
				include("import/import_renderer-dm.php");
				echo "Done\n";
				if ($html) echo'</td></tr>';
			}
			else if ($gamename == "Capture the Flag" || $gamename ==  "Capture the Flag (insta)" ) {
				if ($html) echo '<tr><td class="smheading" align="left" width="350">';
				echo "Generating ctf graphs: ";
				if ($html) echo '</td><td class="grey" align="left" width="200">';
				include("import/import_renderer-ctf.php");
				echo "Done\n";
				if ($html) echo'</td></tr>';
			}
		}
	}

	// Delete Temp MySQL Table
	$droptable = "DROP TABLE uts_temp_$uid";
	mysqli_query($GLOBALS["___mysqli_link"], $droptable) or die(mysqli_error($GLOBALS["___mysqli_link"]));

	if ($html) echo'<tr><td class="smheading" align="left" width="350">';
	echo "Deleting Temp MySQL Table: ";
	if ($html) echo '</td><td class="grey" align="left" width="200">';
	echo "uts_temp_$uid\n";
	if ($html) echo '</td></tr></table><br>';
	echo "\n\n";

  // Clear variables

	$asscode = "";
	$assteam = "";
	$asswin = "";
	$avgping = "";
	$data = "";
	$domplayer = "";
	$droptable = "";
	$firstblood = "";
	$gameinfo = "";
	$gameinsta = "";
	$gamename = "";
	$gametime = "";
	$highping = "";
	$unknowngt = "";
	$lowping = "";
	$mapname = "";
	$mapfile = "";
	$matchid = "";
	$mutators = "";
	$num = "";
	$playerid = "";
	$playerfragscnt = "";
	$playername = "";
	$playerecordid = "";
	$playerteam = "";
	$qm_mutators = "";
	$row = 1;
	$servername = "";
	$serverinfo = "";
	$serverip = "";
	$serverport = "";
	$suicidecnt = "";
	$t0info = "";
	$t1info = "";
	$t2info = "";
	$t3info = "";
	$t0score = "";
	$t1score = "";
	$t2score = "";
	$t3score = "";
	$teamgame = "";
	$tournament = "";

// Delete log file
	unlink($filename);
}
closedir($logdir);

if ($html) echo '<br>';
echo "\n";

// Import stats
if ($files != 0) {
	$elapsed = $elapsed + time() - $start_time;
	if ($html) echo '<p class="pages">';
	echo "Processed $files ". ($files == 1 ? 'file' : 'files') ." in $elapsed ". ($elapsed == 1 ? 'second' : 'seconds') ." ";
	echo "(". get_dp($elapsed / $files) ." seconds/file)\n";
	if ($html) echo '</p><br>';
}

// Optimise database
if (rand(0, 5) == 0) {
	if ($html) echo '<p class="pages">';
	echo "Optimizing tables... ";
	mysqli_query($GLOBALS["___mysqli_link"], "OPTIMIZE TABLE uts_match, uts_player, uts_rank, uts_killsmatrix, uts_weaponstats, uts_pinfo;") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	echo "Done\n";
	if ($html) echo '</p>';
}

// Analyze Key distribution
if (rand(0, 10) == 0) {
	if ($html) echo '<p class="pages">';
	echo "Analyzing tables... ";
	mysqli_query($GLOBALS["___mysqli_link"], "ANALYZE TABLE uts_match, uts_player, uts_rank, uts_killsmatrix, uts_weaponstats, uts_pinfo;") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	echo "Done\n";
	if ($html) echo '</p>';
}


// Purge old logs
if ($purged = (purge_backups('logs/backup', $import_log_backups_purge_after))) {
	if ($html) echo '<p class="pages">';
	echo "Purged $purged old logfiles\n";
	if ($html) echo '</p>';
}

// Purge old utdc logs
if ($import_utdc_download_enable) {
	if ($purged = (purge_backups('logs/utdc', $import_utdc_log_purge_after))) {
		if ($html) echo '<p class="pages">';
		echo "Purged $purged old UTDC logfiles\n";
		if ($html) echo '</p>';
	}
}

// Purge old AnthChecker logs
if ($import_ac_download_enable) {
	if ($purged = (purge_backups('logs/ac', $import_ac_log_purge_after))) {
		if ($html) echo '<p class="pages">';
		echo "Purged $purged old AC logfiles\n";
		if ($html) echo '</p>';
	}
}

// Purge old ACE logs
if ($import_ace_download_enable) {
	if ($purged = (purge_backups('logs/ace', $import_ace_log_purge_after))) {
		if ($html) echo '<p class="pages">';
		echo "Purged $purged old ACE logfiles\n";
		if ($html) echo '</p>';
	}
}

echo "\n\n";
if ($html) echo '<br><table class = "box" border="0" cellpadding="1" cellspacing="2" width="720"><tr><td class="heading" align="center" colspan="2">';
echo "Import Script Completed\n";
if ($html) echo '</td></tr></table>';

if ($html) include("includes/footer.php");
?>
