<?php
	include_once("includes/geoip.inc");
	$gi = geoip_open("GeoIP.dat",GEOIP_STANDARD);

	// Get the unique ID of this player.
	// Create a new one if he has none yet.
	$r_pid = small_query("SELECT id, country, banned FROM uts_pinfo WHERE name = '$playername'");
	if ($r_pid) {
		$pid = $r_pid['id'];
		$pid_country = $r_pid['country'];
		$playerbanned = ($r_pid['banned'] == 'Y') ? true : false;
	} else {
		mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_pinfo SET name = '$playername'") or die(mysqli_error($GLOBALS["___mysqli_link"]));
		$pid = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_link"]))) ? false : $___mysqli_res);
		$pid_country = false;
		$playerbanned = false;
	}
	$playerid2pid[$playerid] = $pid;

	// Do we import banned players?
	if ($playerbanned and $import_ban_type == 2) return;


	// Did the player do first blood?
	IF($playerid == $firstblood) {
		$upd_firstblood = "UPDATE uts_match SET firstblood = '$pid' WHERE id = $matchid";
		mysqli_query($GLOBALS["___mysqli_link"], $upd_firstblood) or die(mysqli_error($GLOBALS["___mysqli_link"]));
	}

	// Get player's IP
	$q_playerip = small_query("SELECT INET_ATON(col4) AS ip FROM uts_temp_$uid WHERE col1 = 'player' AND col2 = 'IP' and col3 = '$playerid' ORDER BY id ASC LIMIT 0,1");
	$playerip = ($q_playerip) ? $q_playerip['ip'] : 0;
	if (empty($playerip)) $playerip = 0;

	// Map the IP to a country
	$playercountry = strtolower(geoip_country_code_by_addr($gi,long2ip($playerip)));

	if ($playercountry != $pid_country)
	{
		mysqli_query($GLOBALS["___mysqli_link"], "UPDATE uts_pinfo SET country = '$playercountry' WHERE id = '$pid'") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	}


	// Get Sprees
	$q_spree_dbl = small_count("SELECT id FROM uts_temp_$uid WHERE col1 = 'spree' AND col2 = 'spree_dbl' AND col3 = '$playerid'");
	$q_spree_mult = small_count("SELECT id FROM uts_temp_$uid WHERE col1 = 'spree' AND col2 = 'spree_mult' AND col3 = '$playerid'");
	$q_spree_ult = small_count("SELECT id FROM uts_temp_$uid WHERE col1 = 'spree' AND col2 = 'spree_ult' AND col3 = '$playerid'");
	$q_spree_mon = small_count("SELECT id FROM uts_temp_$uid WHERE col1 = 'spree' AND col2 = 'spree_mon' AND col3 = '$playerid'");

	$q_spree_kill = small_count("SELECT id FROM uts_temp_$uid WHERE col1 = 'spree' AND col2 = 'spree_kill' AND col3 = '$playerid'");
	$q_spree_rampage = small_count("SELECT id FROM uts_temp_$uid WHERE col1 = 'spree' AND col2 = 'spree_rampage' AND col3 = '$playerid'");
	$q_spree_dom = small_count("SELECT id FROM uts_temp_$uid WHERE col1 = 'spree' AND col2 = 'spree_dom' AND col3 = '$playerid'");
	$q_spree_uns = small_count("SELECT id FROM uts_temp_$uid WHERE col1 = 'spree' AND col2 = 'spree_uns' AND col3 = '$playerid'");
	$q_spree_god = small_count("SELECT id FROM uts_temp_$uid WHERE col1 = 'spree' AND col2 = 'spree_god' AND col3 = '$playerid'");


	// Get Count of Pickups
	$sql_player7 = "SELECT col2, COUNT(col2) AS pu_count FROM uts_temp_$uid WHERE col1 = 'item_get' AND col3 = $playerid GROUP BY col2";
	$q_player7 = mysqli_query($GLOBALS["___mysqli_link"], $sql_player7);

	$pu_pads = 0;
	$pu_armour = 0;
	$pu_keg = 0;
	$pu_belt = 0;
	$pu_amp = 0;
	$pu_invis = 0;

	while ($r_player7 = mysqli_fetch_array($q_player7)) {
		// Cycle through pickups and see what the player got
		IF ($r_player7[col2] == "Thigh Pads") { $pu_pads = $r_player7[pu_count]; }
		IF ($r_player7[col2] == "Body Armor") { $pu_armour = $r_player7[pu_count]; }
		IF ($r_player7[col2] == "Super Health Pack") { $pu_keg = $r_player7[pu_count]; }
		IF ($r_player7[col2] == "ShieldBelt") { $pu_belt = $r_player7[pu_count]; }
		IF ($r_player7[col2] == "Damage Amplifier") { $pu_amp = $r_player7[pu_count]; }
		IF ($r_player7[col2] == "Invisibility") { $pu_invis = $r_player7[pu_count]; }
	}

	// Get ping information
	$r_player9 = small_query("SELECT MIN(col4 * 1) AS lowping, MAX(col4 * 1) AS highping, AVG(col4 * 1) AS avgping FROM uts_temp_$uid WHERE col1 = 'Player' AND col2 = 'Ping' AND col3 = $playerid AND col4 > 0");
	$lowping = $r_player9[lowping];
	$highping = $r_player9[highping];
	$avgping = (int)$r_player9[avgping];

	// People who join at the end error the import, this stops it
	IF ($lowping == NULL) { $lowping = 0; }
	IF ($highping == NULL) { $highping = 0; }
	IF ($avgping == NULL) { $avgping = 0; }

	// Get accuracy, ttl etc
	$r_acc = 0;
	$r_deaths = 0;
	$r_efficiency = 0;
	$r_frags = 0;
	$r_kills = 0;
	$r_teamkills = 0;
	$r_suicides = 0;
	$r_tos = 0;
	$r_ttl = 0;

	$q_acc = small_query("SELECT col4 FROM uts_temp_$uid WHERE col1 = 'stat_player' AND col2 = 'accuracy' AND col3 = $playerid");
	$q_deaths = small_query("SELECT col4 FROM uts_temp_$uid WHERE col1 = 'stat_player' AND col2 = 'deaths' AND col3 = $playerid");
	$q_kills = small_query("SELECT col4 FROM uts_temp_$uid WHERE col1 = 'stat_player' AND col2 = 'kills' AND col3 = $playerid");
	$q_teamkills = small_query("SELECT col4 FROM uts_temp_$uid WHERE col1 = 'stat_player' AND col2 = 'teamkills' AND col3 = $playerid");
	$q_efficiency = small_query("SELECT col4 FROM uts_temp_$uid WHERE col1 = 'stat_player' AND col2 = 'efficiency' AND col3 = $playerid");
	$q_suicides = small_query("SELECT col4 FROM uts_temp_$uid WHERE col1 = 'stat_player' AND col2 = 'suicides' AND col3 = $playerid");
	$q_tos = small_query("SELECT col4 FROM uts_temp_$uid WHERE col1 = 'stat_player' AND col2 = 'time_on_server' AND col3 = $playerid");
	$q_ttl = small_query("SELECT col4 FROM uts_temp_$uid WHERE col1 = 'stat_player' AND col2 = 'ttl' AND col3 = $playerid");
	$q_score = small_query("SELECT col4 FROM uts_temp_$uid WHERE col1 = 'stat_player' AND col2 = 'score' AND col3 = $playerid");

	IF ($teamgame == "True") {
		$r_kills = $q_kills[col4];
		$r_teamkills = $q_teamkills[col4];
	}
	IF ($teamgame == "False") {
		$r_kills = $q_kills[col4] + $q_teamkills[col4];
		$r_teamkills = 0;
	}

	$r_acc = get_dp($q_acc[col4]);
	$r_efficiency = get_dp($q_efficiency[col4]);
	$r_deaths = $q_deaths[col4];
	$r_suicides = $q_suicides[col4];
	$r_frags = $r_kills-$r_suicides-$r_teamkills;

	$r_tos = get_dp($q_tos[col4]);
	// time on server can't be longer then the server game time!
	if ($r_tos > $servergametime) {
		$r_tos = get_dp($servergametime);
	}
	$r_ttl = get_dp($q_ttl[col4]);
	$r_score = $q_score[col4];

	if (!$playerteam) {
		$playerteam = 0;
	}

	// Generate player record
	$sql_playerid = "	INSERT
							INTO		uts_player
							SET		matchid = '$matchid',
										playerid = '$playerid',
										pid = '$pid',
										team = '$playerteam',
										gid = '$gid',
										insta = '$gameinsta',
										country = '$playercountry',
										ip = '$playerip',


										spree_double = '$q_spree_dbl',
										spree_multi = '$q_spree_mult',
										spree_ultra = '$q_spree_ult',
										spree_monster = '$q_spree_mon',
										spree_kill = '$q_spree_kill',
										spree_rampage = '$q_spree_rampage',
										spree_dom = '$q_spree_dom',
										spree_uns = '$q_spree_uns',
										spree_god = '$q_spree_god',

										pu_pads = '$pu_pads',
										pu_armour = '$pu_armour',
										pu_keg = '$pu_keg',
										pu_belt = '$pu_belt',
										pu_amp = '$pu_amp',
										pu_invis = '$pu_invis',

										lowping = '$lowping',
										highping = '$highping',
										avgping = '$avgping',

										accuracy = '$r_acc',
										frags = '$r_frags',
										deaths = '$r_deaths',
										kills = '$r_kills',
										suicides = '$r_suicides',
										teamkills = '$r_teamkills',
										eff = '$r_efficiency',
										gametime = '$r_tos',
										ttl = '$r_ttl',
										gamescore= '$r_score'";

	$q_playerid = mysqli_query($GLOBALS["___mysqli_link"], $sql_playerid) or die(mysqli_error($GLOBALS["___mysqli_link"]));
	$playerecordid = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_link"]))) ? false : $___mysqli_res);


?>
