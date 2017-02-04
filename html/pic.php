<?php
require ("includes/config.php");
require ("includes/config_pic.php");
require ("includes/functions.php");

if (!isset($pic_enable) or !$pic_enable) pic_error('err_disabled');


function pic_error($name) {
	header("Content-type: image/png");
	readfile("images/templates/${name}.png");
	exit;
}

function place_text(&$im, $size, $angle, $x, $to_x, $y, $to_y, $color, $font, $align, $text) {
	$cp = allocate_color($im, $color);
	$box = imagettfbbox($size, $angle, $font, $text);
	$twidth = $box[4] - $box[0];

	switch($align) {
		case 'center':
			$p_x = ($to_x - $x) / 2 - ceil($twidth/2); break;
		case 'right':
			$p_x = $to_x - $twidth; break;
		default: 
			$p_x = $x;
	}
	imagettftext($im, $size, $angle, $p_x, $y, $cp, $font, $text);
}

function image_create($filename, &$load) {
	if (!file_exists($filename)) return(false);
	$infos = getimagesize($filename);
	if (!$infos) return(false);
	switch($infos[2]) {
		case 1:
			$im = @imagecreatefromgif($filename);
			break;
		case 2: 
			$im = @imagecreatefromjpeg($filename);
			break;
		case 3:
			$im = @imagecreatefrompng($filename);
			break;
		default:
			die("Unsupported image type");
	}
	if (!$im) die("Unable to load image template");
	if (!$load['recreate']) return($im);
	
	$in = imagecreatetruecolor(imagesx($im), imagesy($im));
	if (!empty($load['bgcolor'])) {
		$bg = allocate_color($in, $load['bgcolor']);
		imagefill($in, 0, 0, $bg);
	}
	if ($load['bgtransparent']) imagecolortransparent($in, $bg);
	imagecopy($in, $im, 0, 0, 0, 0, imagesx($im), imagesy($im)) or die("Unable to copy image");	
	imagedestroy($im);
	
	return($in);
}


function allocate_color(&$im, $colstring) {
	static $cache = array();
	
	if (isset($cache[$colstring])) return($cache[$colstring]);
	
	$col = explode(':', substr(chunk_split($colstring, 2, ':'), 0, -1));
	$r = hexdec($col[0]);
	$g = hexdec($col[1]);
	$b = hexdec($col[2]);
	if (isset($col[3])) {
		$alpha = hexdec($col[3]);
		$cp = imagecolorallocatealpha($im, $r, $g, $b, $alpha);	
	} else {
		$cp = imagecolorallocate($im, $r, $g, $b);	
	}
	$cache[$colstring] = $cp;
	return($cp);	
}

function output_image(&$im, &$options) {
	switch($options['type']) {
		case 'jpg':
			header("Content-type: image/jpeg");
			imagejpeg($im);
			break;
		
		case 'gif':
			header("Content-type: image/gif");
			imagegif($im);
			break;
			
		default:
			header("Content-type: image/png");
			imagepng($im);
	}
}


function replace_vars($text, &$searchrepl) {
	static $search = NULL;
	static $replace = NULL;
	
	if ($search === NULL) {
		$search = array();
		$replace = array();
		foreach($searchrepl as $key => $value) {
			$search[] = $key;
			$replace[] = $value;
		}
	}

	$text = str_replace($search, $replace, $text);

	if (!empty($searchrepl['%GID%'])) {
		$rankingtext = RankImageOrText($searchrepl['%PID%'], $searchrepl['%PLAYERNAME%'], 0, $searchrepl['%GID%'], $searchrepl['%GAMENAME%'], false, $text, NULL);
		if (!empty($rankingtext)) $text = $rankingtext;
	}
	return($text);
}



function get_values($date_from, $date_to, $pid, $gid, $prefix, &$searchrepl) {
	$sql_time = (empty($date_from)) ? '' : "AND	m.time >= '".date("YmdHis", $week_start)."' and m.time <= '".date("YmdHis", $week_end);
	$sql_gid = (empty($gid)) ? '' : "AND m.gid = '$gid'";
	$sql_order = ($prefix != 'LM') ? '' : 'ORDER BY m.time DESC LIMIT 0,1';
	$sql_groupby = ($prefix != 'LM') ? 'p.pid' : '1';
	$sql = "	SELECT	m.time AS gamedate,
							COUNT(*) AS games, 
							SUM(p.gamescore) as gamescore, 
							SUM(p.frags) AS frags, 
							SUM(p.kills) AS kills,
							SUM(p.deaths) AS deaths, 
							SUM(p.suicides) as suicides, 
							AVG(p.eff) AS eff, 
							AVG(p.accuracy) AS acc, 
							AVG(p.ttl) AS ttl, 
							SUM(p.gametime) as gametime,
							SUM(p.flag_capture) as flag_capture,
							SUM(p.flag_cover) as flag_cover,
							SUM(p.flag_seal) as flag_cover,
							SUM(p.flag_assist) as flag_assist,
							SUM(p.flag_kill) as flag_kill,
							SUM(p.flag_pickedup) as flag_pickedup,
							SUM(p.dom_cp) as dom_cp,
							SUM(p.ass_obj) as ass_obj,
							SUM(p.spree_double) as spree_double,
							SUM(p.spree_triple) as spree_triple,
							SUM(p.spree_multi) as spree_multi,
							SUM(p.spree_mega) as spree_mega,
							SUM(p.spree_ultra) as spree_ultra,
							SUM(p.spree_monster) as spree_monster,
							SUM(p.spree_kill) as spree_kill,
							SUM(p.spree_rampage) as spree_rampage,
							SUM(p.spree_dom) as spree_dom,
							SUM(p.spree_uns) as spree_uns,
							SUM(p.spree_god) as spree_god,
							SUM(p.pu_pads) as pu_pads,
							SUM(p.pu_armour) as pu_armour,
							SUM(p.pu_keg) as pu_keg,
							SUM(p.pu_invis) as pu_invis,
							SUM(p.pu_belt) as pu_belt,
							SUM(p.pu_amp) as pu_amp,
							SUM(p.rank) as rankmovement
				FROM 		uts_match AS m,
							uts_player AS p
				WHERE 	m.id = p.matchid
							$sql_time
							$sql_gid
					AND   p.pid = '$pid'
				GROUP BY $sql_groupby
							$sql_order";
	$result = small_query($sql);	
	if (!$result) return;
	foreach($result as $name => $value) {
		$name = strtoupper($name);
		switch($name) {
			case 'EFF': $value = get_dp($value); break;
			case 'ACC': $value = get_dp($value); break;
			case 'TTL': $value = GetMinutes($value); break;
			case 'GAMETIME': $value = sec2hour($value); break;
			case 'GAMEDATE': $value = date("Y-m-d H:i", mtimestamp($value)); break;
			case 'RANKMOVEMENT': $value = ($value >= 0) ? '+'.get_dp($value) : get_dp($value); break;
		}
		$searchrepl["%${prefix}_${name}%"] = $value;
	}
}




?>
<?php



if (!function_exists("gd_info")) {
	if (!check_extension('gd2')) pic_error('err_no_gd');
}

$gd_info = gd_info();
if (!$gd_info['FreeType Support']) pic_error('err_no_ft');


$num = isset($_GET['num']) ? $_GET['num'] : 0;
$pid = isset($_GET['pid']) ? my_addslashes($_GET['pid']) : 0;
$gid = isset($_GET['gid']) ? my_addslashes($_GET['gid']) : 0;


if ($num == 0 and $pid == 0 and $gid == 0 and !empty($_SERVER['PATH_INFO'])) {
	$pi = explode('/', $_SERVER['PATH_INFO']);
	list($void, $num, $pid, $gid) = $pi;
}

if (!isset($pic[$num]) or !$pic[$num]['enabled']) pic_error('err_na');
$load = &$pic[$num]['load'];
$std = &$pic[$num]['default'];
$output = &$pic[$num]['output'];




if (empty($pid)) die("No pid supplied");
$r_pinfo = small_query("SELECT name, country, banned FROM uts_pinfo WHERE id = '$pid'");
if (!$r_pinfo) die("Unable to fetch player record");
if ($r_pinfo['banned'] == 'Y') pic_error('err_banned');
$playername = $r_pinfo['name'];
$playercountry = $r_pinfo['country'];


$gamename = '(unknown)';
if (!empty($gid)) {
	$r_gameinfo = small_query("SELECT name FROM uts_games WHERE id = '$gid'");
	if (!$r_gameinfo) die("Unable to fetch game record");
	$gamename = $r_gameinfo['name'];
}


$searchrepl = array(	'%GID%'				=>	$gid,
							'%PID%'				=> $pid,
							'%GAMENAME%'		=> $gamename,
							'%PLAYERNAME%'		=> $playername,
							'%PLAYERCOUNTRY%'	=> strtoupper($playercountry)
						);
//$search  = array('%RT%', 		'%RN%',	'%RP%',	'%RI%',	'%GN%',		'%PN%',	'%IT%');
//$replace = array($ranktext,	$rank,	$points,	$img,		$gamename,	$name,	$imageortext);


// Add all texts that are used in this pic to one big string
// We'll use this string to determine which values the user wants
// and hence which we'll have to provide
$textstrings = '';
foreach ($pic[$num]['process'] as $process) {
	if ($process['type'] != 'text') continue;
	$textstrings .= $process['value'];
}


$ts = time();
if (strpos($textstrings, '%WEEK_') !== false) {
	$week_start = mktime(0,0,0, date('m', $ts), date('d', $ts) - date('w', $ts), date('Y', $ts));
	$week_end = mktime(23,59,59, date('m', $week_start), date('d', $week_start) + 6, date('Y', $ts));
	get_values($week_start, $week_end, $pid, $gid, 'WEEK', $searchrepl);
}

if (strpos($textstrings, '%LWEEK_') !== false) {
	$last_week_start = mktime(0,0,0, date('m', $ts), date('d', $ts) - date('w', $ts) - 7, date('Y', $ts));
	$last_week_end = mktime(23,59,59, date('m', $last_week_start), date('d', $last_week_start) + 6, date('Y', $ts));
	get_values($last_week_start, $last_week_end, $pid, $gid, 'LWEEK', $searchrepl);
}

if (strpos($textstrings, '%MONTH_') !== false) {
	$month_start = mktime(0,0,0, date('m', $ts), 1, date('Y', $ts));
	$month_end = mktime(23,59,59, date('m', $month_start) + 1, 0, date('Y', $month_start));
	get_values($month_start, $month_end, $pid, $gid, 'MONTH', $searchrepl);
}

if (strpos($textstrings, '%LMONTH_') !== false) {
	$last_month_start = mktime(0,0,0, date('m', $ts) - 1, 1, date('Y', $ts));
	$last_month_end = mktime(23,59,59, date('m', $last_month_start) + 1, 0, date('Y', $last_month_start));
	get_values($last_month_start, $last_month_end, $pid, $gid, 'LMONTH', $searchrepl);
}

if (strpos($textstrings, '%YEAR_') !== false) {
	$year_start = mktime(0,0,0, 1, 1, date('Y', $ts));
	$year_end = mktime(23,59,59, 12, 31, date('Y', $year_start));
	get_values($year_start, $year_end, $pid, $gid, 'YEAR', $searchrepl);
}

if (strpos($textstrings, '%LYEAR_') !== false) {
	$last_year_start = mktime(0,0,0, 1, 1, date('Y', $ts) - 1);
	$last_year_end = mktime(23,59,59, 12, 31, date('Y', $last_year_start));
	get_values($last_year_start, $last_year_end, $pid, $gid, 'LYEAR', $searchrepl);
}

if (strpos($textstrings, '%TOTAL_') !== false) {
	get_values(0, 0, $pid, $gid, 'TOTAL', $searchrepl);
}

if (strpos($textstrings, '%GTOTAL_') !== false) {
	get_values(0, 0, $pid, 0, 'GTOTAL', $searchrepl);
}

if (strpos($textstrings, '%LM_') !== false) {
	get_values(0, 0, $pid, $gid, 'LM', $searchrepl);
}

//echo "<pre>"; var_dump($searchrepl); echo "</pre>"; exit;


$im = image_create('images/templates/'. $load['template'], $load);

$img_width = imagesx($im);
$img_height = imagesy($im);

if (empty($std['align'])) $std['align'] = 'left';
if (empty($std['angle'])) $std['angle'] = 0;
if (empty($std['font'])) $std['font'] = 'microsbe.ttf';
if (empty($std['fontcolor'])) $std['fontcolor'] = 'FFFFFF';
if (empty($std['fontsize'])) $std['fontsize'] = 12;

foreach ($pic[$num]['process'] as $process) {
	switch($process['type']) {
		case 'text':
			if (empty($process['align'])) $process['align'] = $std['align'];
			if (empty($process['font'])) $process['font'] = $std['font'];
			if (empty($process['fontcolor'])) $process['fontcolor'] = $std['fontcolor'];
			if (empty($process['fontsize'])) $process['fontsize'] = $std['fontsize'];
			if (empty($process['angle'])) $process['angle'] = $std['angle'];
			if (empty($process['x_to'])) $process['x_to'] = $img_width;
			if (empty($process['y_to'])) $process['y_to'] = $process['y_from'];
			
			$text = replace_vars($process['value'], $searchrepl);
			place_text($im, $process['fontsize'], $process['angle'], $process['x_from'], $process['x_to'], $process['y_from'], $process['y_to'], $process['fontcolor'], 'images/fonts/'.$process['font'], $process['align'], $text);
			break;
			
		default:
			die("Don't know how to process: ". $process['type']);
	}

}

output_image($im, $output);

?>