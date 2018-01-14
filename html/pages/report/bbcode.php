<?php
echo'<textarea rows="50" name="cbrep" cols="75">';

$id = preg_replace('/\D/', '', $_GET[id]);

if (empty($id)) {
      die("No ID given");
}

$sql_s = small_query("SELECT mapfile, gametime FROM uts_match WHERE id = '$id'");

$map = str_replace(".unr", "", "$sql_s[mapfile]");
$mappic = strtolower("assets/images/maps/".$map.".jpg");
$actgamelength = $sql_s[gametime];

// Lets work out what kind of game this was from the scores

$q_score = small_query("SELECT t0score, t1score FROM uts_match WHERE id = $id");
$t0score = $q_score[t0score];
$t1score = $q_score[t1score];

$teama = "Red Team";
$teamb = "Blue Team";

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
	$twin_team = $teama;;
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
$r_fbloodid = $t_fbloodid[id];
$r_fbloodname = $t_fblood[name];

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
$r_topgrabid = $q_topgrab[id];
$r_topgrabname = $q_topgrab[name];

// Most Flag Grabs (L)
$q_topgrabl = small_query("SELECT p.id, pi.name, p.flag_taken FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = $tlose ORDER BY flag_taken DESC LIMIT 0,1");
$r_topgrabidl = $q_topgrabl[id];
$r_topgrabnamel = $q_topgrabl[name];

// Most Flag Grabs (D)
$q_topgrabd = small_query("SELECT p.id, pi.name, p.flag_taken, team FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id ORDER BY flag_taken DESC LIMIT 0,1");
$r_topgrabidd = $q_topgrabd[id];
$r_topgrabnamed = $q_topgrabd[name];

IF ($redteam == $q_topgrabd[team]) {
	$r_topgrabteamd = $warinfo[0][2];
	$r_topgrabteamdid = $warinfo[0][1];
} else {
	$r_topgrabteamd = $warinfo[1][2];
	$r_topgrabteamdid = $warinfo[1][1];
}


// Most Flag Covers (W)
$q_topcover = small_query("SELECT p.id, pi.name, p.flag_cover FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = $twin ORDER BY flag_cover DESC LIMIT 0,1");
$r_topcoverid = $q_topcover[id];
$r_topcovername = $q_topcover[name];

// Most Flag Covers (L)
$q_topcoverl = small_query("SELECT p.id, pi.name, p.flag_cover FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = $tlose ORDER BY flag_cover DESC LIMIT 0,1");
$r_topcoverlid = $q_topcoverl[id];
$r_topcoverlname = $q_topcoverl[name];

// Most Flag Covers (R)
$q_topcoverr = small_query("SELECT p.id, pi.name, p.flag_cover FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = 0 ORDER BY flag_cover DESC LIMIT 0,1");
$r_topcoverrid = $q_topcoverr[id];
$r_topcoverrname = $q_topcoverr[name];

// Most Flag Covers (B)
$q_topcoverb = small_query("SELECT p.id, pi.name, p.flag_cover FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = 1 ORDER BY flag_cover DESC LIMIT 0,1");
$r_topcoverbid = $q_topcoverb[id];
$r_topcoverbname = $q_topcoverb[name];

// Most Flag Assists (D)
$q_topassistd = small_query("SELECT p.id, pi.name, p.flag_assist, team FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id ORDER BY flag_cover DESC LIMIT 0,1");
$r_topassistdid = $q_topassistd[id];
$r_topassistdname = $q_topassistd[name];

IF ($q_topassistd[team] == $redteam) {
   $r_topassistdteam = $teamb;
} else {
   $r_topassistdteam = $teama;
}

// Most Flag Seals (W)
$q_topseal = small_query("SELECT p.id, pi.name, p.flag_seal FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = $twin ORDER BY flag_seal DESC LIMIT 0,1");
$r_topsealid = $q_topseal[id];
$r_topsealname = $q_topseal[name];

// Most Flag Seals (L)
$q_topseall = small_query("SELECT p.id, pi.name, p.flag_seal FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = $tlose ORDER BY flag_seal DESC LIMIT 0,1");
$r_topseallid = $q_topseall[id];
$r_topseallname = $q_topseall[name];

// Top Capper (W)
$q_topcap = small_query("SELECT p.id, pi.name, p.flag_capture FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = $twin ORDER BY flag_capture DESC LIMIT 0,1");
$r_topcapid = $q_topcap[id];
$r_topcapname = $q_topcap[name];

// Top Capper (D)
$q_topcapd = small_query("SELECT p.id, pi.name, p.flag_capture FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id ORDER BY flag_capture DESC LIMIT 0,1");
$r_topcapdid = $q_topcap[id];
$r_topcapdname = $q_topcap[name];

// Most Flag Kills (L)
$q_topdefl = small_query("SELECT p.id, pi.name, p.flag_kill FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND  matchid = $id AND team = $tlose ORDER BY flag_kill DESC LIMIT 0,1");
$r_topdeflid = $q_topdefl[id];
$r_topdeflname = $q_topdefl[name];

// Most Flag Kills (W)
$q_topfkill = small_query("SELECT p.id, pi.name, p.flag_kill FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND  matchid = $id AND team = $twin ORDER BY flag_kill DESC LIMIT 0,1");
$r_topfkillid = $q_topfkill[id];
$r_topfkillname = $q_topfkill[name];

// 2nd Most Flag Kills (W)
$q_topfkill2 = small_query("SELECT p.id, pi.name, p.flag_kill FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = $twin ORDER BY flag_kill DESC LIMIT 1,1");
$r_topfkillid2 = $q_topfkill2[id];
$r_topfkillname2 = $q_topfkill2[name];

// Most Frags (D)
$q_topfrag = small_query("SELECT p.id, pi.name, p.frags FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id ORDER BY frags DESC LIMIT 0,1");
$r_topfragid = $q_topfrag[id];
$r_topfragname = $q_topfrag[name];

// Most Frags (W)
$q_topfragw = small_query("SELECT p.id, pi.name, p.frags FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = $twin ORDER BY frags DESC LIMIT 0,1");
$r_topfragwid = $q_topfkill2[id];
$r_topfragwname = $q_topfkill2[name];

// Most Deaths (L)
$q_topdeath = small_query("SELECT p.id, pi.name, p.deaths FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = $tlose ORDER BY deaths DESC LIMIT 0,1");
$r_topdeathid = $q_topdeath[id];
$r_topdeathname = $q_topdeath[name];

// Player of the Match
$q_topstats = small_query("SELECT p.id, pi.name, p.rank AS prank FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id GROUP BY id, name ORDER BY prank DESC LIMIT 0,1");
$r_topstatsid = $q_topstats[id];
$r_topstatsname = $q_topstats[name];


// Write the top of the report (non game specific)

//Player List for Red Team
echo'[b]'.$teama.' Lineup[/b]
';

$sql_rplayer = "SELECT p.id, pi.name FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = 0 ORDER BY pi.name ASC";
$q_rplayer = mysqli_query($GLOBALS["___mysqli_link"], $sql_rplayer);
while ($r_rplayer = mysqli_fetch_array($q_rplayer)) {

	echo''.$r_rplayer[name].'
';
}

//Player List for Blue Team

echo'
[b]'.$teamb.' Lineup[/b]
';

$sql_bplayer = "SELECT p.id, pi.name FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = 1 ORDER BY pi.name ASC";
$q_bplayer = mysqli_query($GLOBALS["___mysqli_link"], $sql_bplayer);
while ($r_bplayer = mysqli_fetch_array($q_bplayer)) {

	echo''.$r_bplayer[name].'
';
}

// Firstblood and Pickup stats

echo '
[b]Statistics[/b]
First Blood: '.$r_fbloodname.'
'.$teama.' Belts: '.$q_t0bcount[bcount].'
'.$teamb.' Belts: '.$q_t1bcount[bcount].'
'.$teama.' Amps: '.$q_t0acount[acount].'
'.$teamb.' Amps: '.$q_t1acount[acount].'

[b]Match Report[/b]
';


// Low Scores and a Draw
IF ($reptype == "low" && $tdraw == 1) {
echo'Neither team were better this map, scorline is proof of this.
[cl]'.$r_topgrabteamdid.','.$r_topgrabteamd.'[/cl] had the most chances to cap with '.$r_topgrabnamed.' taking the flag '.$q_topgrabd[flag_taken].' times, but unfortuantely couldnt cap as many as he took.

Big credit to both teams defences; ';

IF ($q_topdefl[flag_kill] >= $q_topfkill[flag_kill]) {
   echo ''.$r_topdeflname.' defended the flag and got '.$q_topdefl[flag_kill].' flagkills while '.$r_topfkillname.' also had a pretty nice defensive game killing '.$q_topfkill[flag_kill].' flag carriers.
   ';
} else {
   echo ''.$r_topfkillname.' defended the flag and got '.$q_topfkill[flag_kill].' flagkills while '.$r_topdeflname.' also had a pretty nice defensive game killing '.$q_topdefl[flag_kill].' flag carriers.
   ';
}

echo'Seems the attackers had a frustrating game on this map, credit to ';

IF ($q_topcover[flag_cover] >= $q_topcoverl[flag_cover]) {
   echo ''.$r_topcovername.' for covering his flag carrier '.$q_topcover[flag_cover].' times and also '.$r_topcoverlname.' for covering '.$q_topcoverl[flag_cover].' times.
   ';
} else {
   echo ''.$r_topcoverlname.' for covering his flag carrier '.$q_topcoverl[flag_cover].' times and also '.$r_topcovername.' for covering '.$q_topcover[flag_cover].' times.
   ';
}

echo'Well done '.$r_topfragname.' for ensuring a tight scoreline with '.$q_topfrag[frags].' frags.
If this map would be played again between these two clans, we might see a change of strategy in the attacking play, but the defenders were strong on both sides this time.

Good game.
Stats player of the map was '.$r_topstatsname.' with '.$q_topstats[prank].' stat points.
Well played both teams.';
}

// Medium Scores and a Draw
IF ($reptype == "medium" && $tdraw == 1) {
echo'An excitingly close game with neither side having the upper hand.
Both clans teamplay was about even and the result shows that it could have easily gone either way.

Capping opportunities came along time after time, with [cl]'.$r_topgrabid.','.$r_topgrabname.'[/cl] taking the flag '.$q_topgrab[flag_taken].' times and at the other end [cl]'.$r_topgrabidl.','.$r_topgrabnamel.'[/cl] taking the flag '.$q_topgrabl[flag_taken].' times.

Help was at hand for the flag runners with '.$teama.'\'s '.$r_topcoverrname.' covering '.$q_topcoverr[flag_cover].' times and '.$r_topcoverbname.' covering '.$q_topcoverb[flag_cover].' times for '.$teamb.'.

Big credit to both teams defences; ';

IF ($q_topdefl[flag_kill] >= $q_topfkill[flag_kill]) {
   echo ''.$r_topdeflname.' defended the flag and got '.$q_topdefl[flag_kill].' flagkills while '.$r_topfkillname.' also had a pretty nice defensive game killing '.$q_topfkill[flag_kill].' flag carriers.
   ';
} else {
   echo ''.$r_topfkillname.' defended the flag and got '.$q_topfkill[flag_kill].' flagkills while '.$r_topdeflname.' also had a pretty nice defensive game killing '.$q_topdefl[flag_kill].' flag carriers.
   ';
}

echo'Both teams showed great teamplay and skill and the scoreline represents this.
Two even lineups for this map.

Well done '.$r_topfragname.' who had '.$q_topfrag[frags].' frags.

Good game.
Stats player of the map was '.$r_topstatsname.' with '.$q_topstats[prank].' stat points.
Well played both teams.';
}

// High Scores and a Draw
IF ($reptype == "high" && $tdraw == 1) {
echo'A very very exciting, close CTF game Im sure both clans will agree.
This high scoring draw proves that '.$teama.' and '.$teamb.' were evenly matched on this map.
Teamplay and strategy enabled both teams to cap quite a few times.
Well done to both teams attackers especially '.$r_topcapdname.' who capped '.$q_topcapd[flag_capture].' times.

Capping opportunities came along time after time, with [cl]'.$r_topgrabid.','.$r_topgrabname.'[/cl] taking the flag '.$q_topgrab[flag_taken].' times and at the other end [cl]'.$r_topgrabidl.','.$r_topgrabnamel.'[/cl] taking the flag '.$q_topgrabl[flag_taken].' times.

Help was at hand for the flag runners with '.$teama.'\'s '.$r_topcoverrname.' covering '.$q_topcoverr[flag_cover].' times and '.$r_topcoverbname.' covering '.$q_topcoverb[flag_cover].' times for '.$teamb.'.

Although the defenders probably didnt have as good a game as the attackers on this map, credit should be awarded to ';

IF ($q_topdefl[flag_kill] >= $q_topfkill[flag_kill]) {
   echo ''.$r_topdeflname.' for killing '.$q_topdefl[flag_kill].' flag carriers and also to ';
} else {
   echo ''.$r_topfkillname.' for killing '.$q_topfkill[flag_kill].' flag carriers and also to ';
}

IF ($q_topseall[flag_seal] >= $q_topseal[flag_seal]) {
   echo ''.$r_topseallname.' for sealing the base '.$q_topseall[flag_seal].' times for his flag carrier to cap.
   <br >';
} else {
   echo ''.$r_topsealname.' for sealing the base '.$q_topseal[flag_seal].' times for his flag carrier to cap.
   <br >';
}

echo' '.$r_topassistdname.' helped '.$r_topassistdteam.' get as many caps as they did by assisting '.$q_topassistd[flag_assist].' times in their caps.
This shows great teamplay and cover, well played.

Both teams showed great teamplay and skill and the scoreline represents this.
Well done '.$r_topfragname.' who had '.$q_topfrag[frags].' frags.

Good game.
Stats player of the map was '.$r_topstatsname.' with '.$q_topstats[prank].' stat points.
Well played both teams.';
}

// Low Scores and not a Draw
IF ($reptype == "low" && $tdraw == 0) {
echo 'Not very many caps in this close game, but '.$twin_team.' emerge victorious.

Big credit to both teams defences; ';

IF ($q_topdefl[flag_kill] >= $q_topfkill[flag_kill]) {
   echo ''.$r_topdeflname.' defended the flag and got '.$q_topdefl[flag_kill].' flagkills while '.$r_topfkillname.' also had a pretty nice defensive game killing '.$q_topfkill[flag_kill].' flag carriers.
   ';
} else {
   echo ''.$r_topfkillname.' defended the flag and got '.$q_topfkill[flag_kill].' flagkills while '.$r_topdeflname.' also had a pretty nice defensive game killing '.$q_topdefl[flag_kill].' flag carriers.
   ';
}

echo 'Seems the attackers had a frustrating game on this map, especially [cl]'.$r_topgrabidl.','.$r_topgrabnamel.'[/cl] who took the flag '.$q_topgrabl[flag_taken].' and still lost the game :(

Suportive play was made by '.$r_topcoverlname.' having '.$q_topcoverl[flag_cover].'  covers, but even with covering play, the majority of the flags got returned and the game ended with '.$tlose_team.' losing out.

Well done to '.$r_topcapname.' for capping '.$q_topcap[flag_capture].'.
On other days the outcome could have been different.

Good game and congratulations '.$twin_team.'.
Stats player of the map was '.$r_topstatsname.' with '.$q_topstats[prank].' stat points.
Well played both teams.';
}

// Medium Scores and not a Draw
IF ($reptype == "medium" && $tdraw == 0) {
echo 'A nice game of Capture the Flag took place this map with both teams trying hard to get flags back but only occasionally succeeding.

'.$twin_team.' however managed to succesfully cap more than '.$tlose_team.' with big help from '.$r_topcapname.' who capped '.$q_topcap[flag_capture].'.
'.$r_topgrabname.' made it hard for the '.$tlose_team.' defenders taking the flag a total of '.$q_topgrab[flag_taken].' times.
Not to say that the defence was bad at all, '.$r_topdeflname.' got '.$q_topdefl[flag_kill].' flag kills and this made attacking hard work for '.$twin_team.'.

Sealing off the base for '.$twin_team.' was some nice defence including '.$r_topsealname.' who sealed the base '.$q_topseal[flag_seal].' times.
Covering the flag carrier for '.$twin_team.' mostly was '.$r_topcovername.' with '.$q_topcover[flag_cover].' covers.

Attacking in vain at the other end of the map was '.$r_topgrabnamel.' who managed to take the '.$twin_team.' flag '.$q_topgrabl[flag_taken].' times, but unfortunately was unable to match the '.$twin_score.' caps made by '.$twin_team.'.
'.$r_topgrabnamel.' was also up against some heavy defence including '.$r_topfkillname.' with '.$q_topfkill[flag_kill].' flag kills and '.$r_topfkillname2.' with '.$q_topfkill2[flag_kill].' flag kills.

Good game and congratulations '.$twin_team.'.
Stats player of the map was '.$r_topstatsname.' with '.$q_topstats[prank].' stat points.
Well played both teams.';
}

// High Scores and not a Draw
IF ($reptype == "high" && $tdraw == 0) {
echo ''.$twin_team.' were victorious thanks to some nice capping by '.$r_topcapname.' who capped '.$q_topcap[flag_capture].' and '.$r_topgrabname.' who took the flag'.$q_topgrab[flag_taken].' times.

'.$r_topcapname.' probably could not have done it without the help of '.$r_topcovername.' who had '.$q_topcover[flag_cover].' covers and '.$r_topsealname.' for sealing the base off '.$q_topseal[flag_seal].' times.

Although the '.$tlose_team.' defenders played well, the '.$q_topdefl[flag_kill].' flag kills by '.$r_topdeflname.' was just not enough to stop the '.$twin_team.' attackers succeeding.
Extra Credit should be given to '.$r_topfragwname.' for getting '.$q_topwfrag[frags].' frags.
Some sympathy should be given to '.$r_topdeathname.' who died a whopping '.$q_topdeath[deaths].' times - unlucky!.

All in all, this was a great attacking game for '.$twin_team.', but all players deserve credit for playing well.

Good game and congratulations '.$twin_team.'.
Stats player of the map was '.$r_topstatsname.' with '.$q_topstats[prank].' stat points.';
}

echo'

This report was created with UTStats from the following game:
[url]http://'.$oururl.'?p=match&mid='.$id.'[/url]
</textarea>';
?>
