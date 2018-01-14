<?php
echo'<textarea rows="50" name="cbrep" cols="75">';

$id = preg_replace('/\D/', '', $_GET[id]);

if (empty($id)) {
      die("No ID given");
}

function get_cbid($pid) {
  $cbid = preg_replace('/\D/', '', $_GET[$pid]);
  return $cbid;
}
$cbid = get_cbid;

function get_cbnick($badtext) {
	$badstuff = array("[","]");
	$goodtext = str_replace($badstuff, " ", $badtext);
	return $goodtext;
}
$cbnick = get_cbnick;

$warinfo = get_clans($wid);

$redteam = $_GET["red"];

IF($redteam == $warinfo[0][1]) {
	$teamaid = $warinfo[0][1];
	$teambid = $warinfo[1][1];
	$cbteama = $warinfo[0][2];
	$cbteamb = $warinfo[1][2];
	$redteam = 0;
	$blueteam = 1;

} else {
	$teamaid = $warinfo[1][1];
	$teambid = $warinfo[0][1];
	$cbteama = $warinfo[1][2];
	$cbteamb = $warinfo[0][2];
	$redteam = 1;
	$blueteam = 0;
}

$teama = str_replace("<img src=", "", $cbteama);
$teamb = str_replace("<img src=", "", $cbteamb);

list($teama, $teamacup) = split("'", $teama, 3);
list($teamb, $teambcup) = split("'", $teamb, 3);

$teama = '[cl]'.$teamaid.','.$teama.'[/cl]';
$teamb = '[cl]'.$teambid.','.$teamb.'[/cl]';

$sql_s = small_query("SELECT mapfile, gametime FROM uts_match WHERE id = '$id'");

$map = str_replace(".unr", "", "$sql_s[mapfile]");
$mappic = strtolower("assets/images/maps/".$map.".jpg");
$actgamelength = $sql_s[gametime];

// Lets work out what kind of game this was from the scores

$q_score = small_query("SELECT t0score, t1score FROM uts_match WHERE id = $id");
$t0score = $q_score[t0score];
$t1score = $q_score[t1score];

IF ($t0score > $t1score) {

	$twin = 0;
	$twin_team = $teama;
	$twin_score = $t0score;

	$tlose = 1;
	$tlose_team = $teamb;
	$tdraw = "0";

	$tdiff = $t0score+$t1score;

	IF ($tdiff <= 3) {
		$reptype = "low";
	} elseIF ($tdiff >= 9) {
		$reptype = "high";
	} else {
		$reptype = "medium";
	}

} elseIF ($t0score < $t1score) {
	$twin = 1;
	$twin_team = $teamb;
	$twin_score = $t1score;

	$tlose = 0;
	$tlose_team = $teama;
	$tdraw = "0";

	$tdiff = $t1score+$t0score;

	IF ($tdiff <= 2) {
		$reptype = "low";
	} elseIF ($tdiff >= 9) {
		$reptype = "high";
	} else {
		$reptype = "medium";
	}

} else {
	$twin = "0";
	$tlose = "1";
	$tdraw = "1";

	$tdiff = $t1score+$t0score;
	$twin_team = $teama;
	$tlose_team = $teamb;

	IF ($tdiff <= 4) {
		$reptype = "low";
	} elseIF ($tdiff >= 10) {
		$reptype = "high";
	} else {
		$reptype = "medium";
	}
}

// Queries used within the report

// First blood
$q_fblood = small_query("SELECT firstblood FROM uts_match WHERE id = $id");

// Player id of first blood
$t_fblood = small_query("SELECT name FROM uts_pinfo WHERE id = '".$q_fblood[firstblood]."'");
$t_fbloodid = small_query("SELECT id FROM uts_player WHERE pid = '".$q_fblood[firstblood]."' AND matchid = $id");
$r_fbloodid = $cbid($t_fbloodid[id]);
$r_fbloodname = $cbnick($t_fblood[name]);

// Belt count
$q_t0bcount = small_query("SELECT SUM(pu_belt) AS bcount FROM uts_player WHERE matchid = $id AND team = 0 AND pu_belt >0");
$q_t1bcount = small_query("SELECT SUM(pu_belt) AS bcount FROM uts_player WHERE matchid = $id AND team = 1 AND pu_belt >0");

// Amp count
$q_t0acount = small_query("SELECT SUM(pu_amp) AS acount FROM uts_player WHERE matchid = $id AND team = 0 AND pu_amp >0");
$q_t1acount = small_query("SELECT SUM(pu_amp) AS acount FROM uts_player WHERE matchid = $id AND team = 1 AND pu_amp >0");

// Cap count
$q_capsa = small_query("SELECT pi.name, SUM(p.flag_capture) AS flag_caps FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = 0 AND flag_capture >= 1 GROUP BY name");
$q_capsb = small_query("SELECT pi.name, SUM(p.flag_capture) AS flag_caps FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = 1 AND flag_capture >= 1 GROUP BY name");

// Most Flag Grabs (W)
$q_topgrab = small_query("SELECT p.id, pi.name, p.flag_taken FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = $twin ORDER BY flag_taken DESC LIMIT 0,1");
$r_topgrabid = $cbid($q_topgrab[id]);
$r_topgrabname = $cbnick($q_topgrab[name]);

// Most Flag Grabs (L)
$q_topgrabl = small_query("SELECT p.id, pi.name, p.flag_taken FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = $tlose ORDER BY flag_taken DESC LIMIT 0,1");
$r_topgrabidl = $cbid($q_topgrabl[id]);
$r_topgrabnamel = $cbnick($q_topgrabl[name]);

// Most Flag Grabs (D)
$q_topgrabd = small_query("SELECT p.id, pi.name, p.flag_taken, team FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id ORDER BY flag_taken DESC LIMIT 0,1");
$r_topgrabidd = $cbid($q_topgrabd[id]);
$r_topgrabnamed = $cbnick($q_topgrabd[name]);

IF ($redteam == $q_topgrabd[team]) {
	$r_topgrabteamd = $warinfo[0][2];
	$r_topgrabteamdid = $warinfo[0][1];
} else {
	$r_topgrabteamd = $warinfo[1][2];
	$r_topgrabteamdid = $warinfo[1][1];
}


// Most Flag Covers (W)
$q_topcover = small_query("SELECT p.id, pi.name, p.flag_cover FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = $twin ORDER BY flag_cover DESC LIMIT 0,1");
$r_topcoverid = $cbid($q_topcover[id]);
$r_topcovername = $cbnick($q_topcover[name]);

// Most Flag Covers (L)
$q_topcoverl = small_query("SELECT p.id, pi.name, p.flag_cover FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = $tlose ORDER BY flag_cover DESC LIMIT 0,1");
$r_topcoverlid = $cbid($q_topcoverl[id]);
$r_topcoverlname = $cbnick($q_topcoverl[name]);

// Most Flag Covers (R)
$q_topcoverr = small_query("SELECT p.id, pi.name, p.flag_cover FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = 0 ORDER BY flag_cover DESC LIMIT 0,1");
$r_topcoverrid = $cbid($q_topcoverr[id]);
$r_topcoverrname = $cbnick($q_topcoverr[name]);

// Most Flag Covers (B)
$q_topcoverb = small_query("SELECT p.id, pi.name, p.flag_cover FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = 1 ORDER BY flag_cover DESC LIMIT 0,1");
$r_topcoverbid = $cbid($q_topcoverb[id]);
$r_topcoverbname = $cbnick($q_topcoverb[name]);

// Most Flag Assists (D)
$q_topassistd = small_query("SELECT p.id, pi.name, p.flag_assist, team FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id ORDER BY flag_cover DESC LIMIT 0,1");
$r_topassistdid = $cbid($q_topassistd[id]);
$r_topassistdname = $cbnick($q_topassistd[name]);

IF ($q_topassistd[team] == $redteam) {
   $r_topassistdteam = $teamb;
} else {
   $r_topassistdteam = $teama;
}

// Most Flag Seals (W)
$q_topseal = small_query("SELECT p.id, pi.name, p.flag_seal FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = $twin ORDER BY flag_seal DESC LIMIT 0,1");
$r_topsealid = $cbid($q_topseal[id]);
$r_topsealname = $cbnick($q_topseal[name]);

// Most Flag Seals (L)
$q_topseall = small_query("SELECT p.id, pi.name, p.flag_seal FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = $tlose ORDER BY flag_seal DESC LIMIT 0,1");
$r_topseallid = $cbid($q_topseall[id]);
$r_topseallname = $cbnick($q_topseall[name]);

// Top Capper (W)
$q_topcap = small_query("SELECT p.id, pi.name, p.flag_capture FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = $twin ORDER BY flag_capture DESC LIMIT 0,1");
$r_topcapid = $cbid($q_topcap[id]);
$r_topcapname = $cbnick($q_topcap[name]);

// Top Capper (D)
$q_topcapd = small_query("SELECT p.id, pi.name, p.flag_capture FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id ORDER BY flag_capture DESC LIMIT 0,1");
$r_topcapdid = $cbid($q_topcap[id]);
$r_topcapdname = $cbnick($q_topcap[name]);

// Most Flag Kills (L)
$q_topdefl = small_query("SELECT p.id, pi.name, p.flag_kill FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND  matchid = $id AND team = $tlose ORDER BY flag_kill DESC LIMIT 0,1");
$r_topdeflid = $cbid($q_topdefl[id]);
$r_topdeflname = $cbnick($q_topdefl[name]);

// Most Flag Kills (W)
$q_topfkill = small_query("SELECT p.id, pi.name, p.flag_kill FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND  matchid = $id AND team = $twin ORDER BY flag_kill DESC LIMIT 0,1");
$r_topfkillid = $cbid($q_topfkill[id]);
$r_topfkillname = $cbnick($q_topfkill[name]);

// 2nd Most Flag Kills (W)
$q_topfkill2 = small_query("SELECT p.id, pi.name, p.flag_kill FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = $twin ORDER BY flag_kill DESC LIMIT 1,1");
$r_topfkillid2 = $cbid($q_topfkill2[id]);
$r_topfkillname2 = $cbnick($q_topfkill2[name]);

// Most Frags (D)
$q_topfrag = small_query("SELECT p.id, pi.name, p.frags FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id ORDER BY frags DESC LIMIT 0,1");
$r_topfragid = $cbid($q_topfrag[id]);
$r_topfragname = $cbnick($q_topfrag[name]);

// Most Frags (W)
$q_topfragw = small_query("SELECT p.id, pi.name, p.frags FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = $twin ORDER BY frags DESC LIMIT 0,1");
$r_topfragwid = $cbid($q_topfkill2[id]);
$r_topfragwname = $cbnick($q_topfkill2[name]);

// Most Deaths (L)
$q_topdeath = small_query("SELECT p.id, pi.name, p.deaths FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = $tlose ORDER BY deaths DESC LIMIT 0,1");
$r_topdeathid = $cbid($q_topdeath[id]);
$r_topdeathname = $cbnick($q_topdeath[name]);

// Player of the Match
$q_topstats = small_query("SELECT p.id, pi.name, p.rank AS prank FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id GROUP BY id, name ORDER BY prank DESC LIMIT 0,1");
$r_topstatsid = $cbid($q_topstats[id]);
$r_topstatsname = $cbnick($q_topstats[name]);


// Write the top of the report (non game specific)

//Player List for Red Team
echo'_______________________________________________________________________<br />
'.$teama.' Lineup: <br />
';

$sql_rplayer = "SELECT p.id, pi.name FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = 0 ORDER BY pi.name ASC";
$q_rplayer = mysqli_query($GLOBALS["___mysqli_link"], $sql_rplayer);
while ($r_rplayer = mysqli_fetch_array($q_rplayer)) {

	echo'[pl]'.$cbid($r_rplayer[id]).','.$cbnick($r_rplayer[name]).'[/pl] ';
}

//Player List for Blue Team

echo'<br />_______________________________________________________________________<br />
'.$teamb.' Lineup: <br />
';

$sql_bplayer = "SELECT p.id, pi.name FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = 1 ORDER BY pi.name ASC";
$q_bplayer = mysqli_query($GLOBALS["___mysqli_link"], $sql_bplayer);
while ($r_bplayer = mysqli_fetch_array($q_bplayer)) {

	echo'[pl]'.$cbid($r_bplayer[id]).','.$cbnick($r_bplayer[name]).'[/pl] ';
}
echo '_______________________________________________________________________<br />
<br />';

// Firstblood and Pickup stats

echo 'First Blood: [pl]'.$r_fbloodid.','.$r_fbloodname.'[/pl]<br />
'.$teama.' Belts: '.$q_t0bcount[bcount].'<br />
'.$teamb.' Belts: '.$q_t1bcount[bcount].'<br />
'.$teama.' Amps: '.$q_t0acount[acount].'<br />
'.$teamb.' Amps: '.$q_t1acount[acount].'<br />
';

echo '_______________________________________________________________________<br />
<br />
';

// Low Scores and a Draw
IF ($reptype == "low" && $tdraw == 1) {
	echo'Neither team were better this map, scorline is proof of this. <br />
	[cl]'.$r_topgrabteamdid.','.$r_topgrabteamd.'[/cl] had the most chances to cap with [pl]'.$r_topgrabidd.','.$r_topgrabnamed.'[/pl]
	taking the flag '.$q_topgrabd[flag_taken].' times, but unfortuantely couldnt cap as many as he took.<br />
	<br />
	Big credit to both teams defences; ';

	IF ($q_topdefl[flag_kill] >= $q_topfkill[flag_kill]) {
	   echo '[pl]'.$r_topdeflid.','.$r_topdeflname.'[/pl] defended the flag
	   and got '.$q_topdefl[flag_kill].' flagkills while [pl]'.$r_topfkillid.','.$r_topfkillname.'[/pl]
	   also had a pretty nice defensive game killing '.$q_topfkill[flag_kill].' flag carriers.<br />
	   <br />';
	} else {
	   echo '[pl]'.$r_topfkillid.','.$r_topfkillname.'[/pl] defended the flag
	   and got '.$q_topfkill[flag_kill].' flagkills while [pl]'.$r_topdeflid.','.$r_topdeflname.'[/pl]
	   also had a pretty nice defensive game killing '.$q_topdefl[flag_kill].' flag carriers.<br />
	   <br />';
	}

	echo'Seems the attackers had a frustrating game on this map, credit to ';

 	IF ($q_topcover[flag_cover] >= $q_topcoverl[flag_cover]) {
	   echo '[pl]'.$r_topcoverid.','.$r_topcovername.'[/pl] for covering his flag carrier
	   '.$q_topcover[flag_cover].' times and also [pl]'.$r_topcoverlid.','.$r_topcoverlname.'[/pl]
	   for covering '.$q_topcoverl[flag_cover].' times. <br />
	   <br />';
	} else {
	   echo '[pl]'.$r_topcoverlid.','.$r_topcoverlname.'[/pl] for covering his flag carrier
	   '.$q_topcoverl[flag_cover].' times and also [pl]'.$r_topcoverid.','.$r_topcovername.'[/pl]
	   for covering '.$q_topcover[flag_cover].' times. <br />
	   <br />';
	}

	echo'Well done [pl]'.$r_topfragid.','.$r_topfragname.'[/pl] for ensuring a tight scoreline with
	'.$q_topfrag[frags].' frags.<br />
	<br />
	If this map would be played again between these two clans, we might see a change of strategy
	in the attacking play, but the defenders were strong on both sides this time.<br />
	<br />
	Good game.<br />
	Stats player of the map was [pl]'.$r_topstatsid.','.$r_topstatsname.'[/pl] with '.$q_topstats[prank].'
	stat points.<br />
	Well played both teams.';
}

// Medium Scores and a Draw
IF ($reptype == "medium" && $tdraw == 1) {
	echo'An excitingly close game with neither side having the upper hand.<br />
	Both clans teamplay was about even and the result shows that it could have easily gone either way.<br />
	<br />
	Capping opportunities came along time after time, with [cl]'.$r_topgrabid.','.$r_topgrabname.'[/cl]
	taking the flag '.$q_topgrab[flag_taken].' times and at the other end [cl]'.$r_topgrabidl.','.$r_topgrabnamel.'[/cl]
	taking the flag '.$q_topgrabl[flag_taken].' times.

	Help was at hand for the flag runners with '.$teama.'\'s [pl]'.$r_topcoverrid.','.$r_topcoverrname.'[/pl]
	covering '.$q_topcoverr[flag_cover].' times and [pl]'.$r_topcoverbid.','.$r_topcoverbname.'[/pl]
	covering '.$q_topcoverb[flag_cover].' times for '.$teamb.'.<br />
	<br />
	Big credit to both teams defences; ';

	IF ($q_topdefl[flag_kill] >= $q_topfkill[flag_kill]) {
	   echo '[pl]'.$r_topdeflid.','.$r_topdeflname.'[/pl] defended the flag
	   and got '.$q_topdefl[flag_kill].' flagkills while [pl]'.$r_topfkillid.','.$r_topfkillname.'[/pl]
	   also had a pretty nice defensive game killing '.$q_topfkill[flag_kill].' flag carriers.<br />
	   <br />';
	} else {
	   echo '[pl]'.$r_topfkillid.','.$r_topfkillname.'[/pl] defended the flag
	   and got '.$q_topfkill[flag_kill].' flagkills while [pl]'.$r_topdeflid.','.$r_topdeflname.'[/pl]
	   also had a pretty nice defensive game killing '.$q_topdefl[flag_kill].' flag carriers.<br />
	   <br />';
	}

	echo'Both teams showed great teamplay and skill and the scoreline represents this.
	Two even lineups for this map.<br />
	<br />
	Well done [pl]'.$r_topfragid.','.$r_topfragname.'[/pl] who had '.$q_topfrag[frags].' frags.<br />
	<br />
	Good game.<br />
	Stats player of the map was [pl]'.$r_topstatsid.','.$r_topstatsname.'[/pl] with '.$q_topstats[prank].'
	stat points.<br />
	Well played both teams.';
}

// High Scores and a Draw
IF ($reptype == "high" && $tdraw == 1) {
echo' A very very exciting, close CTF game Im sure both clans will agree.<br />
This high scoring draw proves that '.$teama.' and '.$teamb.' were evenly matched on this map.<br />
Teamplay and strategy enabled both teams to cap quite a few times.<br />
Well done to both teams attackers especially [pl]'.$r_topcapdid.','.$r_topcapdname.'[/pl]
who capped '.$q_topcapd[flag_capture].' times.<br />
<br />
	Capping opportunities came along time after time, with [cl]'.$r_topgrabid.','.$r_topgrabname.'[/cl]
	taking the flag '.$q_topgrab[flag_taken].' times and at the other end [cl]'.$r_topgrabidl.','.$r_topgrabnamel.'[/cl]
	taking the flag '.$q_topgrabl[flag_taken].' times.<br />
	<br />
	Help was at hand for the flag runners with '.$teama.'\'s [pl]'.$r_topcoverrid.','.$r_topcoverrname.'[/pl]
	covering '.$q_topcoverr[flag_cover].' times and [pl]'.$r_topcoverbid.','.$r_topcoverbname.'[/pl]
	covering '.$q_topcoverb[flag_cover].' times for '.$teamb.'.<br />
	<br />
	Although the defenders probably didnt have as good a game as the attackers on this map,
	credit should be awarded to ';

	IF ($q_topdefl[flag_kill] >= $q_topfkill[flag_kill]) {
	   echo '[pl]'.$r_topdeflid.','.$r_topdeflname.'[/pl] for killing '.$q_topdefl[flag_kill].'
	   flag carriers and also to ';
	} else {
	   echo '[pl]'.$r_topfkillid.','.$r_topfkillname.'[/pl] for killing '.$q_topfkill[flag_kill].'
	   flag carriers and also to ';
	}

	IF ($q_topseall[flag_seal] >= $q_topseal[flag_seal]) {
	   echo '[pl]'.$r_topseallid.','.$r_topseallname.'[/pl] for sealing the base '.$q_topseall[flag_seal].'
	   times for his flag carrier to cap.<br />
	   <br >';
	} else {
	   echo '[pl]'.$r_topsealid.','.$r_topsealname.'[/pl] for sealing the base '.$q_topseal[flag_seal].'
	   times for his flag carrier to cap.<br />
	   <br >';
	}

	echo' [pl]'.$r_topassistdid.','.$r_topassistdname.'[/pl] helped '.$r_topassistdteam.' get as many
	caps as they did by assisting '.$q_topassistd[flag_assist].' times in their caps.<br />
	This shows great teamplay and cover, well played.<br />
	<br />
	Both teams showed great teamplay and skill and the scoreline represents this.<br />
	<br />
	Well done [pl]'.$r_topfragid.','.$r_topfragname.'[/pl] who had '.$q_topfrag[frags].' frags.<br />
	<br />
	Good game.<br />
	Stats player of the map was [pl]'.$r_topstatsid.','.$r_topstatsname.'[/pl] with '.$q_topstats[prank].'
	stat points.<br />
	Well played both teams.';
}

// Low Scores and not a Draw
IF ($reptype == "low" && $tdraw == 0) {
	echo 'Not very many caps in this close game, but '.$twin_team.' emerge victorious.<br />
	<br />
	Big credit to both teams defences; ';

	IF ($q_topdefl[flag_kill] >= $q_topfkill[flag_kill]) {
	   echo '[pl]'.$r_topdeflid.','.$r_topdeflname.'[/pl] defended the flag
	   and got '.$q_topdefl[flag_kill].' flagkills while [pl]'.$r_topfkillid.','.$r_topfkillname.'[/pl]
	   also had a pretty nice defensive game killing '.$q_topfkill[flag_kill].' flag carriers.<br />
	   <br />';
	} else {
	   echo '[pl]'.$r_topfkillid.','.$r_topfkillname.'[/pl] defended the flag
	   and got '.$q_topfkill[flag_kill].' flagkills while [pl]'.$r_topdeflid.','.$r_topdeflname.'[/pl]
	   also had a pretty nice defensive game killing '.$q_topdefl[flag_kill].' flag carriers.<br />
	   <br />';
	}

	echo 'Seems the attackers had a frustrating game on this map,
	especially [cl]'.$r_topgrabidl.','.$r_topgrabnamel.'[/cl] who took the flag '.$q_topgrabl[flag_taken].'
	and still lost the game :(<br />
	<br />
	Suportive play was made by [pl]'.$r_topcoverlid.','.$r_topcoverlname.'[/pl] having
	'.$q_topcoverl[flag_cover].'  covers, but even with covering play,
	the majority of the flags got returned and the game ended with '.$tlose_team.' losing out.<br />
	<br />
	Well done to [pl]'.$r_topcapid.','.$r_topcapname.'[/pl] for capping '.$q_topcap[flag_capture].'.<br />
	On other days the outcome could have been different.<br />
	<br />
	Good game and congratualtions '.$twin_team.'.<br />
	Stats player of the map was [pl]'.$r_topstatsid.','.$r_topstatsname.'[/pl] with '.$q_topstats[prank].'
	stat points.<br />
	Well played both teams.';
}

// Medium Scores and not a Draw
IF ($reptype == "medium" && $tdraw == 0) {
	echo 'A nice game of Capture the Flag took place this map with both teams trying
	hard to get flags back but only occasionally succeeding.<br />
	<br />
	'.$twin_team.' however managed to succesfully cap more than '.$tlose_team.' with big help
	from [pl]'.$r_topcapid.','.$r_topcapname.'[/pl] who capped '.$q_topcap[flag_capture].'.
	[pl]'.$r_topgrabid.','.$r_topgrabname.'[/pl] made it hard for the '.$tlose_team.' defenders taking the
	flag a total of '.$q_topgrab[flag_taken].' times.<br />
	Not to say that the defence was bad at all, [pl]'.$r_topdeflid.','.$r_topdeflname.'[/pl]
	got '.$q_topdefl[flag_kill].' flag kills and this made attacking hard work for '.$twin_team.'. <br />
	<br />
	Sealing off the base for '.$twin_team.' was some nice defence including
	[pl]'.$r_topsealid.','.$r_topsealname.'[/pl] who sealed the base '.$q_topseal[flag_seal].' times.<br />
	Covering the flag carrier for '.$twin_team.' mostly was [pl]'.$r_topcoverid.','.$r_topcovername.'[/pl]
	with '.$q_topcover[flag_cover].' covers.<br />
	<br />
	Attacking in vain at the other end of the map was [pl]'.$r_topgrabidl.','.$r_topgrabnamel.'[/pl]
	who managed to take the '.$twin_team.' flag '.$q_topgrabl[flag_taken].' times,
	but unfortunately was unable to match the '.$twin_score.' caps made by '.$twin_team.'.<br />
	[pl]'.$r_topgrabidl.','.$r_topgrabnamel.'[/pl] was also up against some heavy defence including
	[pl]'.$r_topfkillid.','.$r_topfkillname.'[/pl] with '.$q_topfkill[flag_kill].' flag kills and
	[pl]'.$r_topfkillid2.','.$r_topfkillname2.'[/pl] with '.$q_topfkill2[flag_kill].' flag kills.<br />
	<br />
	Good game and congratualtions '.$twin_team.'.<br />
	Stats player of the map was [pl]'.$r_topstatsid.','.$r_topstatsname.'[/pl] with '.$q_topstats[prank].'
	stat points.<br />
	Well played both teams.';
}

// High Scores and not a Draw
IF ($reptype == "high" && $tdraw == 0) {
	echo ''.$twin_team.' were victorious thanks to some nice capping by
	[pl]'.$r_topcapid.','.$r_topcapname.'[/pl] who capped '.$q_topcap[flag_capture].' and
	[pl]'.$r_topgrabid.','.$r_topgrabname.'[/pl] who took the flag'.$q_topgrab[flag_taken].' times.<br />
	<br />
	[pl]'.$r_topcapid.','.$r_topcapname.'[/pl] probably could not have done it without the help of
	[pl]'.$r_topcoverid.','.$r_topcovername.'[/pl] who had '.$q_topcover[flag_cover].' covers and
	[pl]'.$r_topsealid.','.$r_topsealname.'[/pl] for sealing the base off
	'.$q_topseal[flag_seal].' times.<br />
	<br />
	Although the '.$tlose_team.' defenders played well, the '.$q_topdefl[flag_kill].' flag kills by
	[pl]'.$r_topdeflid.','.$r_topdeflname.'[/pl] was just not enough to stop the
	'.$twin_team.' attackers succeeding.  Extra Credit should be given to
	[pl]'.$r_topfragwid.','.$r_topfragwname.'[/pl] for getting '.$q_topwfrag[frags].' frags.<br />
	Some sympathy should be given to [pl]'.$r_topdeathid.','.$r_topdeathname.'[/pl]
	who died a whopping '.$q_topdeath[deaths].' times - unlucky!.<br />
	<br />
	All in all, this was a great attacking game for '.$twin_team.', but all players deserve credit for
	playing well.<br />
	<br />
	Good game and congratualtions '.$twin_team.'.<br />
	Stats player of the map was [pl]'.$r_topstatsid.','.$r_topstatsname.'[/pl] with '.$q_topstats[prank].'
	stat points.';

}

echo'<br />
	<br />
	This report was created with UTStats from the following game:<br />
	[el]http://'.$oururl.'?p=match&mid='.$id.',http://'.$oururl.'?p=match&mid='.$id.'[/el]
	</textarea>';
?>
