<?php

require_once 'renderer-general-import.php';

/**
Render graph with scores & grabs
*/
function renderScoreGraph($uid) {
	global $matchid;
	global $time_gamestart;
	global $time_gameend;
	global $time_ratio_correction;
	global $playernumberofteams;
	global $playerteams;
	
	$uid = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $uid);
	
	// Get all frags related events + start & end
	$q_frags = mysqli_query($GLOBALS["___mysqli_link"], "SELECT * FROM `uts_temp_$uid` WHERE col0>=".mysqli_real_escape_string($GLOBALS["___mysqli_link"], $time_gamestart)." ORDER BY id ASC") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	
	// init caps/grabs array
	$caps = array();
	$grabs = array();
	$lastCapTeam = array();
	$overTimeCap = false;
	
	$teamLabels = generateTeamLabels();
	$realTimeEnd = ($time_gameend-$time_gamestart)/$time_ratio_correction/60;
	
	// init vars & ensure datapoint at origin
	for($i=0;$i<$playernumberofteams;$i++) {
		$caps[$i][0] = array(0,0);	
		$prevcaps[$i] = 0;
		$prevgrabs[$i] = 0;
				
		$flagmid[$i] = 0;
		$flagbase[$i] = 0;
		$flagclose[$i] = 0;
		$flagnmybase[$i] = 0;
		$flagcap[$i] = 0;
	}
	
	$counter = 1;
	while($data = mysqli_fetch_array($q_frags)) {
	
		// Collect data from utstats own table
		$r_id = $data[0];
		$r_time = round(($data[1]-$time_gamestart)/$time_ratio_correction/60,2);	// Transform UT time to real time		
		$r_type = $data[2];
		$r_capper = $data[3];
		$r_team = $playerteams[$r_capper];
		
		// Try to get team directly from data if possible, in case not found in playerteam db
		if(!($r_team >=0 && strlen($r_team)>0)) {
			if($data[4] == 0 || $data[4] == 20)
				$r_team = 1;
			if($data[4] == 1 || $data[4] == 14)
				$r_team = 0;
		}

		// Only collect team stats if team was found - sometimes this is bugged
		if($r_team >=0 && strlen($r_team)>0) {
			
			// If flag is captured, save this datapoint
			if($r_type == "flag_captured") {		
				$prevcaps[$r_team]++;		
				$flagcap[$r_team]++;	
				$flagnmybase[$r_team]--;	
				
				// Analyse cap for tooltip data
				$capdata = analyseCap($uid,$data[1],$r_capper,$r_team,$r_id);
				
				$caps[$r_team][] = array("x" => $r_time,"y" => $prevcaps[$r_team], "tooltipdata" => $capdata);				
				$lastCapTeam = array($r_time,$r_team);
				
			// If grab is done, simply save this event	
			} else if($r_type == "flag_taken") {				
				$prevgrabs[$r_team]++;			
				$flagnmybase[$r_team]++;	
			} else if($r_type == "flag_return_mid") {				
				$flagmid[$r_team]++;			
				$flagnmybase[$r_team]--;	
			} else if($r_type == "flag_return_enemybase") {				
				$flagbase[$r_team]++;					
				$flagnmybase[$r_team]--;	
			} else if($r_type == "flag_return_closesave") {				
				$flagclose[$r_team]++;				
				$flagnmybase[$r_team]--;	
				
			}
			
		// If game_end event & game ended cause timelimit reached, round the time down to prevent UT gay timing to screw the graph
		} if($r_type == "game_end") {
		
			// If it's a regular timelimit, floor the time to get it nicely rounded (ref gay UT timing)\
			$overTimeCap = (($r_time-$lastCapTeam[0])<=0.01);
			
			if($r_capper == "timelimit" && !$overTimeCap)
				$r_time = round($r_time);
				
			// Repeat final CTF score at the end
			for($i=0;$i<$playernumberofteams;$i++) {
				if(!$overTimeCap || $i != $lastCapTeam[1])
					$caps[$i][] = array($r_time,$prevcaps[$i]);
			}
		}
		
		// Each minute, save amount of grabs. Don't do it for last minute if this one was less than half a minute
		if(($r_time>=$counter && ($realTimeEnd-$r_time)>0.49) || $r_type == "game_end") {
		
			// Save grabs last min and reset
			for($i=0;$i<$playernumberofteams;$i++) {
				$grabs[$i][] = $prevgrabs[$i];
				$prevgrabs[$i] = 0;
			}
			
			$counter++;
		}
	}
		
	// Ensure flagNmyBase is at least 0
	for($i=0;$i<$playernumberofteams;$i++) {
		if($flagnmybase[$i]<0)
			$flagnmybase[$i] = 0;
	}
	
	// Save team score over team for teams
	mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_chartdata (mid,chartid,data,labels) VALUES (".$matchid.", ".RENDERER_CHART_CTF_TEAMSCORE.", 
		'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize(array($grabs,$caps))))."', 
		'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($teamLabels)))."')") or die(mysqli_error($GLOBALS["___mysqli_link"]));

	$labelsBreakdown = array('caps','close','base','mid','enemy base');
		
	// Save team score over team for teams
	mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_chartdata (mid,chartid,data,labels) VALUES (".$matchid.", ".RENDERER_CHART_CTF_GRABBREAKDOWN.", 
		'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize(array($flagcap,$flagclose,$flagbase,$flagmid,$flagnmybase))))."', 
		'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($labelsBreakdown)))."')") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	
}

/**
Analyse cap to show in tooltip
*/
function analyseCap($uid, $capTime, $capPlayer,$capTeam, $capId) {
	global $playernames;
	global $playerteams;
	global $time_ratio_correction;

	// Initialise variables
	$tooltip = '';
	$cherrypick = '';		
	$solocap = '';
	$standoff = '';
	$hero = '';
	$event = array();
	$cherrypickDone = false;

	$event[0]['flag_taken'] = array('id' => 0, 'time' => 0, 'player' => '');
	$event[0]['flag_returned'] = array('id' => 0, 'time' => 0, 'player' => '');
	$event[0]['flag_captured'] = array('id' => 0, 'time' => 0, 'player' => '');
	$event[1]['flag_taken'] = array('id' => 0, 'time' => 0, 'player' => '');
	$event[1]['flag_returned'] = array('id' => 0, 'time' => 0, 'player' => '');
	$event[1]['flag_captured'] = array('id' => 0, 'time' => 0, 'player' => '');
			
	// Get grab time for both teams related to this cap. Query of the month award.
	$q_grabs = mysqli_query($GLOBALS["___mysqli_link"], "SELECT f.id AS id, f.col0 AS gtime, f.col1 AS event, f.col2 AS player, f.col3 AS team 
	FROM (	
		SELECT col3, col1, MAX( col0 ) AS xgtime FROM `uts_temp_$uid` WHERE (col1 = 'flag_taken' OR col1 = 'flag_returned' OR col1 = 'flag_captured') AND id<".mysqli_real_escape_string($GLOBALS["___mysqli_link"], $capId)." GROUP BY col3,col1 ORDER BY col3 DESC	 
	) AS x INNER JOIN `uts_temp_$uid` AS f 
	on f.col3 = x.col3 AND f.col1 = x.col1 AND f.col0 = x.xgtime") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	
	// Only continue if you have at least one event (should be at least flag_taken for this cap)
	if(mysqli_num_rows($q_grabs) >0) {
	
		while($r_grabs = mysqli_fetch_array($q_grabs)) {		
			// Ugly code to fix bug where sometimes 20 or 14 is imported instead of 0 and 1 respectively
			$fixedteam = ($r_grabs['team']==20)?0:$r_grabs['team'];
			$fixedteam = ($fixedteam==14)?1:$fixedteam;
			
			// Swap team around since ut logs which flag has been taken, thus opposite team actually took it
			$event[1-$fixedteam][$r_grabs['event']] = array('id' => $r_grabs['id'],'time' => $r_grabs['gtime'],'player' => $r_grabs['player']);
		}			
				
		// Determine how long flags were holded
		$flagHolded[$capTeam] = $capTime - $event[$capTeam]['flag_taken']['time'];
		$flagHolded[1-$capTeam] = $event[1-$capTeam]['flag_returned']['time'] - $event[1-$capTeam]['flag_taken']['time'];
			
		// Determine if this cap resulted from a cherrypick
		// For this the flag shoudl be taken within 4 seconds after return
		// Only register as cherry pick if previous run was at least 10 sec, otherwise it's not really a hcerry pick but just a subsequent grab
		if($event[$capTeam]['flag_returned']['time']>0 && ($event[$capTeam]['flag_taken']['time']-$event[$capTeam]['flag_returned']['time'])<4) {
				
			$prevRunTime = small_query("SELECT MAX( col0 ) as gtime FROM `uts_temp_$uid` WHERE id<".mysqli_real_escape_string($GLOBALS["___mysqli_link"], $event[$capTeam]['flag_returned']['id'])." AND col1='flag_taken' AND col3='".(1-$capTeam)."'") or die(mysqli_error($GLOBALS["___mysqli_link"]));
				
			if(($event[$capTeam]['flag_returned']['time'] - $prevRunTime[0]) > 10)
				$cherrypickDone = true;
		}
		
		if($event[$capTeam]['flag_captured']['time']>0 && ($event[$capTeam]['flag_taken']['time']-$event[$capTeam]['flag_captured']['time'])<4) 
			$cherrypickDone = true;
			
		if($cherrypickDone)
			$cherrypick = "<br><span style=\"fontSize: '7px';  font-style: italic;\">Cherrypick by ".renderPlayernameForTooltip($event[$capTeam]['flag_taken']['player'])."</span>";	
		
		// Determine if solo cap
		// First check if person who grabbed is same person as capper
		if($event[$capTeam]['flag_taken']['player'] == $capPlayer) {
		
			// Second check is to see no pickups were done during this run
			if(small_count("SELECT * FROM `uts_temp_$uid` WHERE col1 = 'flag_pickedup' AND col3 = '".(1-$capTeam)."' AND id<".mysqli_real_escape_string($GLOBALS["___mysqli_link"], $capId)." AND id>".mysqli_real_escape_string($GLOBALS["___mysqli_link"], $event[$capTeam]['flag_taken']['id'])) == 0) {
			
				$solocap = "<br>Solo cap";
								
			}
		
		}		
		
		// If taken for more than 30 seconds for both teams, this is a long standoff
		// Add who did the return
		if($flagHolded[$capTeam] > 30 && $flagHolded[1-$capTeam] > 30 && $event[1-$capTeam]['flag_returned']['player'] >= 0) 
			$standoff = "<br>Standoff return by ".renderPlayernameForTooltip($event[1-$capTeam]['flag_returned']['player']);		
		
		$tooltip = "Capped by : ".renderPlayernameForTooltip($capPlayer)." (".renderTimeForTooltip($flagHolded[$capTeam]/$time_ratio_correction).")";		
		$tooltip .= $cherrypick;
		$tooltip .= $solocap;
		$tooltip .= $standoff;
		
	}		
		
	return $tooltip;
}

/**
Prep temporary database for CTF parsing
*/
function prepCTFdata($safe_uid) {
	
	// Replace time-out return by normal return
	mysqli_query($GLOBALS["___mysqli_link"], "UPDATE `uts_temp_$safe_uid` SET col1='flag_returned', col2=(@temp:=col2),col2='-1',col3=@temp WHERE col1='flag_returned_timeout'") or die(mysqli_error($GLOBALS["___mysqli_link"]));

}

function renderPlayernameForTooltip($pid) {
	global $playernames;

	return substr($playernames[$pid],0,25);
}

function renderTimeForTooltip($time) {

	$minutes = floor($time/60);	
	if($minutes > 0) {
		$strtime = $minutes."min ";
		$time = $time - $minutes*60;
	}
	
	$seconds = round($time,0);
	if($seconds >= 1)
		$strtime .= $seconds."sec";
	
	return $strtime;
}

?>