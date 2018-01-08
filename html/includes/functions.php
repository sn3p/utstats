<?php
// Connect to database
mysql_connect($hostname, $uname, $upass);
mysql_select_db($dbname);

// Error reporting
// error_reporting(E_ALL & ~E_NOTICE);
// error_reporting(-1); // Turn on all errors
error_reporting(0); // Turn off all errors
@ini_set('track_errors', '1');

// Image Rotation Code
$charimages[] = "char1.jpg";
$charimages[] = "char2.jpg";
$charimages[] = "char3.jpg";
$charimages[] = "char4.jpg";
$charimages[] = "char5.jpg";
$charimages[] = "char6.jpg";
$charimages[] = "char7.jpg";
$charimages[] = "char8.jpg";
$charimages[] = "char9.jpg";
$charimages[] = "char10.jpg";
$charimages[] = "char11.jpg";
$charimages[] = "char12.jpg";
$charimages[] = "char13.jpg";
$charimages[] = "char14.jpg";

srand(microtime() * 1000);
$charimg = $charimages[rand(0, count($charimages)-1)];

// Two letter codes and their corresponding country names
require_once(dirname(__FILE__) .'/countries.php');

// Addslashes if magic_quotes are off
function my_addslashes($data) {
	if (!get_magic_quotes_gpc()) {
	  $data = addslashes($data);
	}
	return $data;
}

function my_stripslashes($data) {
	if (!get_magic_quotes_gpc()) {
	  $data = $data;
	} else {
	  $data = stripslashes($data);
	}
	return $data;
}

function my_fgets($fp, $length = -1, $compression = 'none') {
	static $use_fgets = NULL;

	if ($use_fgets === NULL) $use_fgets = (version_compare(phpversion(), "4.3.0", ">=")) ? true : false;

	if ($use_fgets and $compression == 'none') {
		if ($length == -1) {
			return(fgets($fp));
		} else {
			return(fgets($fp, $length));
		}
	}

	$buffer = '';
	$i = 0;
	while(!feof($fp)) {
		if ($length != -1 and $i >= $length) break;
		$i++;
		switch($compression) {
			case 'bz2':		$char = bzread($fp, 1); break;
			case 'zlib':	$char = gzread($fp, 1); break;
			default:			$char = fread($fp, 1);
		}
		$buffer .= $char;
		if ($char == "\n") break;
	}
	if (empty($buffer) and feof($fp)) return(false);
	return($buffer);
}

function my_fopen($filename, $mode, &$compression) {
	if ($compression === NULL) {
		$compression = 'none';
		if (substr($filename, -4) == '.bz2') {
			if (check_extension('bz2')) {
			 	$compression = 'bz2';
			} else {
			 	return(false);
			}
		}
		if (substr($filename, -3) == '.gz') {
			if (check_extension('zlib')) {
			 	$compression = 'zlib';
			} else {
			 	return(false);
			}
		}
	}

	switch($compression) {
		case 'bz2':		$fp = @bzopen($filename, $mode); break;
		case 'zlib': 	$fp = @gzopen($filename, $mode); break;
		default:			$fp = @fopen($filename, $mode); break;
	}
	return($fp);
}

function my_fclose($fp, $compression) {
	switch($compression) {
		case 'bz2':		return(@bzclose($fp));
		case 'zlib': 	return(@gzclose($fp));
		default:			return(@fclose($fp));
	}
}

// Small query
function small_query($query) {
	$sql_small = "$query";
	$q_small = mysql_query($sql_small) or die(mysql_error());
	$r_small = mysql_fetch_array($q_small);
	return $r_small;
}

// Small query count
function small_count($query) {
	$sql_small = "$query";
	$q_small = mysql_query($sql_small) or die(mysql_error());
	$r_small = mysql_num_rows($q_small);
	return $r_small;
}

// uid generator
function str_rand($length = 8, $seeds = 'abcdefghijklmnopqrstuvwxyz0123456789') {
  $str = '';
  $seeds_count = strlen($seeds);

  // Seed
  list($usec, $sec) = explode(' ', microtime());
  $seed = (float) $sec + ((float) $usec * 100000);
  mt_srand($seed);

  // Generate
  for ($i = 0; $length > $i; $i++) {
      $str .= $seeds{mt_rand(0, $seeds_count - 1)};
  }

  return $str;
}

function zero_out($data) {
	if (!is_array($data)) return($data);
	foreach($data as $key => $value) {
		if ($value == '0') $data[$key] = '';
	}
	return($data);
}

function get_dp($number) {
	$dp = number_format($number, 2, '.', '');
	return ($dp);
}

function sec2min($number) {
	$dp = $number/60;
	$dp = number_format($dp, 2, '.', '');
	return ($dp);
}

function sec2hour($number) {
	$dp = $number/3600;
	$dp = number_format($dp, 2, '.', '');
	return ($dp);
}

function un_ut($name) {
	$gname = str_replace("Botpack.", "", "$name");
	$gname = str_replace("Class ", "", "$gname");
	$gname = str_replace("CTFGame", "Capture The Flag", "$gname");
	$gname = str_replace(".unr", "", "$gname");
	return ($gname);
}

function mtimestamp($date) {
	$hour = substr($date, 8, 2);
	$minute = substr($date, 10, 2);
	$second = 00;
	$day = substr($date, 6, 2);
	$month = substr($date, 4, 2);
	$year = substr($date, 0, 4);

	return(mktime($hour,$minute,$second,$month,$day,$year));
}

function mdate($date) {
	$ourdate = date('D, M j Y \a\t g:i a', mtimestamp($date));
	return ($ourdate);
}

function mdate2($date) {
	$hour = substr("$date", 8, 2);
	$minute = substr("$date", 10, 2);
	$second = "00";
	$day = substr("$date", 6, 2);
	$month = substr("$date", 4, 2);
	$year = substr("$date", 0, 4);

	$ourdate = mktime($hour,$minute,$second,$month,$day,$year);

	$ourdate = date('Y-m-d g:i a', $ourdate);
	return ($ourdate);
}

function utdate($gametime) {
	$year = substr("$gametime", 0, 4);
	$month = substr("$gametime", 5, 2);
	$day = substr("$gametime", 8, 2);
	$hour = substr("$gametime", 11, 2);
	$minute = substr("$gametime", 14, 2);
	$second = substr("$gametime", 17, 2);

	$gametime = $year . $month . $day . $hour . $minute . $second;
	return ($gametime);
}

function btcaptime($time) {
	if (empty($time)) {
		return;
	}
	if ($time < 0) {
		return "-:--";
  }
	$minutes = intval($time / 60);
	$seconds = intval($time % 60);
	$hundreds = substr($time, -2, 2);
	if (substr($hundreds, 0, 1) == '.') {
		$hundreds = substr($hundreds, 1, 1) . 0;
	}
	if ((substr($hundreds, 1, 1) == '.') or (!preg_match("/\./", $time))) {
		$hundreds = "00";
	}
	if ($seconds < 10) {
		$seconds = "0" . $seconds;
  }
	return $minutes . ":" . $seconds . ":" . $hundreds;
}

// UT Server Query Functions
function GetItemInfo ($itemname, $itemchunks) {
  $retval = "N/A";
  for ($i = 0; $i < count($itemchunks); $i++) {
    //Found this item
    if (strcasecmp($itemchunks[$i], $itemname) == 0) {
      $retval = $itemchunks[$i+1];
    }
  }
  return  $retval;
}

function GetMinutes($seconds) {
	$timemins = intval($seconds / 60);
	$timesecs = ($seconds % 60);

	$Reqlength = 2; //Amount of digits we need
	if ($Reqlength-strlen($timemins) > 0) $timemins = str_repeat("0",($Reqlength-strlen($timemins))) . $timemins;
	if ($Reqlength-strlen($timesecs) > 0) $timesecs = str_repeat("0",($Reqlength-strlen($timesecs))) . $timesecs;
	return $timemins . ":" . $timesecs;
}

function FlagImage($country, $mini = true) {
	global $a_countries;
	$width = ($mini) ? 20 : 20;
	$height = ($mini) ? 14 : 14;
	if (empty($country)) return('');
	if (!file_exists("images/flags/$country.png")) return(''); //18*12
	$countryname = (isset($a_countries[$country])) ? $a_countries[$country] : '';
	return('<img src="images/flags/'. $country .'.png" width="'.$width.'" height="'.$height.'" style="border:0;" alt="'. $country .'" title="'. $countryname .'">');
}

function RankMovement($diff) {
	$diff = round($diff, 2);
	if ($diff == 0) {
		$chimg = 'same';
		$chtext = "ranking not affected";
	}
	if ($diff > 0) {
		$chimg = 'up';
		$chtext = "gained ". get_dp($diff) ." ranking points";
	}
	if ($diff < 0) {
		$chimg = 'down';
		$chtext = "lost ". get_dp($diff * -1) ." ranking points";
	}
	$moveimg = '';
	if (file_exists("images/ranks/$chimg.png")) {
		$infos = getimagesize("images/ranks/$chimg.png");
		$width = $infos[0];
		$height = $infos[1];
		$moveimg = '<img src="images/ranks/'. $chimg .'.png" width="'.$width.'" height="'.$height.'" style="border:0;" alt="" title="'. $chtext .'">';
	}
	return($moveimg);
}

function ordinal($number) {
  // when fed a number, adds the English ordinal suffix. Works for any number, even negatives
  if ($number % 100 > 10 && $number %100 < 14) {
    $suffix = "th";
	} else {
    switch($number % 10) {
      case 0:
        $suffix = "th";
        break;

      case 1:
        $suffix = "st";
        break;

      case 2:
        $suffix = "nd";
        break;

      case 3:
        $suffix = "rd";
        break;

      default:
        $suffix = "th";
        break;
    }
  }

  return $suffix;
}

function RankImageOrText($pid, $name, $rank, $gid, $gamename, $mini = true, $format = NULL, $rankchange = NULL) {
	$points = 0;

	if (empty($rank)) {
		$r_rank = small_query("SELECT rank FROM uts_rank WHERE pid = '$pid' AND gid= '$gid';");
		if (!$r_rank) return('');
		$points = get_dp($r_rank['rank']);
		$r_no = small_query("SELECT (COUNT(*) + 1) AS no FROM uts_rank WHERE gid = '$gid' and rank > ${points}9");
		$rank = $r_no['no'];
	}

	$ranktext = $rank.ordinal($rank);
	if (file_exists("images/ranks/$rank.png")) {
		$width = ($mini) ? 15 : 15;
		$height = ($mini) ? 12 : 12;
		$img = '<img class="tooltip" src="images/ranks/'. $rank .'.png" width="17" height="17" style="border:0; margin-bottom: -4px;" alt="'. $rank .'" title="'. $ranktext .' in '. $gamename .'">';
	} else {
		$img = '';
	}

	$moveimg = '';
	if ($rankchange !== NULL) {
		$moveimg =  ' '. RankMovement($rankchange);
	}

	if (empty($format)) {
		if ($img) {
			return($img.$moveimg);
		} else {
			return('<span class="rangtext">('.$ranktext.$moveimg.')</span>');
		}
	}

	$imageortext = ($img) ? $img : $ranktext;
	$search  = array('%RT%', 		'%RN%',	'%RP%',	'%RI%',	'%GN%',		'%PN%',	'%IT%');
	$replace = array($ranktext,	$rank,	$points,	$img,		$gamename,	$name,	$imageortext);
	return(str_replace($search, $replace, $format));
}

function FormatPlayerName($country, $pid, $name, $gid = NULL, $gamename = NULL, $mini = true, $rankchange = NULL) {
	static $cache = array();

	if (isset($cache[$pid])) return($cache[$pid]);

	$ranktext = false;
	if (!empty($gamename) and $pid !== NULL) {
		$ranktext = RankImageOrText($pid, $name, 0, $gid, $gamename, $mini, NULL, $rankchange);
	}
	$ret = '';
	if (!empty($country)) $ret .= FlagImage($country, $mini) ." ";
	$ret .= htmlentities($name);
	if ($ranktext) $ret .= " " . $ranktext;
	$cache[$pid] = $ret;
	return($ret);
}

function QuoteHintText($text) {
	$search = array('\\', '\'', '(', ')');
	$replace = array('\\\\', '\\\'', '\\(', '\\)');
	return(str_replace($search, $replace, $text));
}

function OverlibPrintHint($name, $text = NULL, $caption = NULL) {
	include(dirname(__FILE__) .'/hints.php');
	if (!isset($hint[$name]) and empty($text)) return('');
	if ($text === NULL) $text = $hint[$name]['text'];
	if ($caption === NULL and isset($hint[$name]['caption'])) $caption = $hint[$name]['caption'];
	$rv  = 'onmouseover="return overlib(\''. QuoteHintText($text) .'\'';
	if ($caption !== NULL) $rv .= ', CAPTION, \''. QuoteHintText($caption) .'\'';
	$rv .= ');" ';
	$rv .= 'onmouseout="return nd();"';
	return($rv);
}

function debug_output($desc, $data) {
	echo '<div align="left"><pre>';
	echo $desc .": ";

	$len = strlen($data);
	for ($i = 0; $i < $len; $i++) {
		echo substr($data, $i, 1) .'  ';
	}

	echo "\n";
	echo str_repeat(' ', (strlen($desc) + 2));

	for ($i = 0; $i < $len; $i++) {
		echo ord(substr($data, $i, 1)) .' ';
	}
	echo "</pre></div>";
}

function check_extension($name) {
	if (extension_loaded($name)) return(true);
	if( !(bool)ini_get("enable_dl") or (bool)ini_get( "safe_mode" )) return(false);
	$prefix = (PHP_SHLIB_SUFFIX == 'dll') ? 'php_' : '';
	return(@dl($prefix . $name . PHP_SHLIB_SUFFIX));
}

function compress_file($method, $in, $out, $stripx00) {
	if ((!file_exists($out) and !is_writeable(dirname($out))) or (file_exists($out) and !is_writable($out))) return(false);
	$blocksize = 8192;

	switch($method) {
		case 'bz2':		$suffix = '.bz2'; break;
		case 'zlib': 	$suffix = '.gz'; break;
		case 'none': 	$suffix = ''; break;
		default: return(false);
	}

	if (substr($out, strlen($out) - strlen($suffix)) != $suffix) $out .= $suffix;

	$fp_in = fopen($in, 'rb');
	if (!$fp_in) return(false);
	switch($method) {
		case 'bz2':		$fp_out = @bzopen($out, 'wb'); break;
		case 'zlib': 	$fp_out = @gzopen($out, 'wb6'); break;
		case 'none':	$fp_out = @fopen($out, 'wb'); break;
	}
	if (!$fp_out) return(false);

	while (!feof($fp_in)) {
		$buffer = @fread($fp_in, $blocksize);
		if ($buffer === false) return(false);
		if ($stripx00) $buffer = preg_replace('/[\x00]/', '', $buffer);
		switch($method) {
			case 'bz2':		$bytes = @bzwrite($fp_out, $buffer, strlen($buffer)); break;
			case 'zlib': 	$bytes = @gzwrite($fp_out, $buffer, strlen($buffer)); break;
			case 'none':	$bytes = @fwrite($fp_out, $buffer, strlen($buffer)); break;
		}
		if ($bytes === false) return(false);
	}

	@fclose($fp_in);

	switch($method) {
		case 'bz2':		@bzclose($fp_out); break;
		case 'zlib': 	@gzclose($fp_out); break;
		case 'none':	@fclose($fp_out);  break;
	}

	return(true);
}

function backup_logfile($method, $filename, $backupfilename, $stripx00) {
	switch ($method) {
		case 'compress':
			if (!check_extension('bz2') or !compress_file('bz2', $filename, $backupfilename, $stripx00)) {
				return(backup_logfile('gzip', $filename, $backupfilename, $stripx00));
			}
			return('Succeeded (bz2)');
			break;

		case 'gzip':
			if (!check_extension('zlib') or !compress_file('zlib', $filename, $backupfilename, $stripx00)) {
				return(backup_logfile('yes', $filename, $backupfilename, $stripx00));
			}
			return('Succeeded (gzip)');
			break;

		case 'no':
			return('NO (disabled in config)');
			break;

		default:
			if ($stripx00) {
				if (compress_file('none', $filename, $backupfilename, $stripx00)) {
					return('Succeeded (uncompressed)');
				} else {
					return('FAILED' . (!empty($php_errormsg) ? ': '. $php_errormsg : ''));
				}
			}
			if (@copy($filename, $backupfilename)) {
				return('Succeeded (uncompressed)');
			} else {
				return('FAILED' . (!empty($php_errormsg) ? ': '. $php_errormsg : ''));
			}
	}
}

function purge_backups($dir, $maxage) {
	if (empty($maxage) or rand(0, 5) != 0) return(NULL);

	// $maxage is days but we need seconds
	$maxage = $maxage * 86400;
	$deleted = 0;

	$dh = opendir($dir);
	while (false !== ($filename = readdir($dh))) {
		if ($filename == '.htaccess' or $filename == 'index.htm') continue;
		$cna = $dir .'/'. $filename;
		if (@is_file($cna) and (@filemtime($cna) + $maxage) < time()) {
			unlink($cna);
			$deleted++;
		}
	}
	closedir($dh);

	return $deleted;
}

function file_size_info($filesize) {
	$bytes = array('KB', 'KB', 'MB', 'GB', 'TB'); # values are always displayed
	if ($filesize < 1024) $filesize = 1; # in at least kilobytes.
	for ($i = 0; $filesize > 1024; $i++) $filesize /= 1024;
	$file_size_info['size'] = ceil($filesize);
	$file_size_info['type'] = $bytes[$i];
	return $file_size_info;
}

function GetCurrentWatchlist() {
	if (!isset($_COOKIE['uts_watchlist'])) return(array());
	$watchlist = @explode(',', $_COOKIE['uts_watchlist']);
	if (!$watchlist or !is_array($watchlist)) return(array());
	foreach($watchlist as $key => $value) {
		$watchlist[$key] = addslashes($value);
	}
	return $watchlist;
}

function PlayerOnWatchlist($pid) {
	$watchlist = GetCurrentWatchlist();
	return in_array($pid, $watchlist);
}

function ToggleWatchStatus($pid) {
	$watchlist = GetCurrentWatchlist();

	if (in_array($pid, $watchlist)) {
		$key = array_search($pid, $watchlist);
		unset($watchlist[$key]);
		$status = 0;
	} else {
		$watchlist[] = $pid;
		$status = 1;
	}

	setcookie('uts_watchlist', implode(',', $watchlist), time() + 60*60*24*30*365*5);
	return $status;
}

function DeBugMessage($message) {
	global $debug, $html;

	if (!$debug) {
		return;
	}

	if ($html) echo '<table class="zebra box" border="0"><tr><th class="smheading" width="550">';
	echo "Debugging Output:\n";
	if ($html) echo '</th></tr><tr><td width="550" align="left"><pre>';
	echo $message . "\n";
	if ($html) echo '</pre></td></tr></table><br><br>';
}

function getMapImageName($mapname) {
	for ($i=0; $i<3; $i++) {
		// try substracting modname from map
		if ($i == 1) {
			if(($x_pos = strpos($mapname, '-')) !== false) {
				$mapname = substr($mapname, $x_pos + 1);
			}
		}

		// try also substracting league names from map
		if ($i==2) {
			$mapVersions = array('CB1','CB2','CB3','CB4','CB5','CB6','CB7','CB','LE13','LE14','LE15','LE16','LE17','LE18','LE19','LE','-DE13','-DE14','-DE15','-DE16','-DE17','-DE18','-DE19','v2','v3','v4','v5','-MLIG','-GU','-SDOM','-][ugn','-ugn','ALT2lightedit','-LE102','-','LE100','LE101','LE102','LE103','LE104','LE105','-v17','test3C','Fixed','XL','DM','MLTDM');
			$mapname = str_replace($mapVersions, "", $mapname);
		}

		$mappic = strtolower("images/maps/" . $mapname . "_large.jpg");

		if(file_exists(dirname(dirname(__FILE__)) . "/" . $mappic)) {
			return $mappic;
		}
	}

	return "images/maps/blank_large.jpg";
}

?>
