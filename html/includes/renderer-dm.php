<?php

require_once 'renderer-general-import.php';

/**
Render team DM graph for 2 teams scenario
*/
function renderFragBarsTeams($datafragsteam,$derivfragsteam,$topTeams,$counter) {
	global $matchid;
	
	$teamLabels = generateTeamLabels();

	if($playernumberofteams == 2) {
		// Net frags for first 2 teams
		for($i=0;$i<($counter-1);$i++) {
			$netfragsteam[0][$i] = $derivfragsteam[0][$i]-$derivfragsteam[1][$i];
			$netfragsteam[1][$i] = $derivfragsteam[1][$i]-$derivfragsteam[0][$i];
			
			$netfragsteam[0][$i] = $netfragsteam[0][$i]>0 ? $netfragsteam[0][$i] : 0;
			$netfragsteam[1][$i] = $netfragsteam[1][$i]>0 ? $netfragsteam[1][$i] : 0;
		}
				
		// Save net frags in db for first 2 teams
		mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_chartdata (mid,chartid,data,labels) VALUES (".$matchid.", ".RENDERER_CHART_FRAGS_TEAMDERIV.", 
		'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize(array($netfragsteam,$datafragsteam))))."', 
		'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($teamLabels)))."')") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	
	} else {
		
		// Save team score over team for teams
		mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_chartdata (mid,chartid,data,labels) VALUES (".$matchid.", ".RENDERER_CHART_FRAGS_TEAMSCORE.", 
		'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($datafragsteam)))."', 
		'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($teamLabels)))."')") or die(mysqli_error($GLOBALS["___mysqli_link"]));

	}
	
	// Generate normalized data (vs #2) & store it
	$normalfragsteam = normalizeDMdata($datafragsteam,$topTeams,$counter);
	
	mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_chartdata (mid,chartid,data,labels) VALUES (".$matchid.", ".RENDERER_CHART_FRAGS_TEAMNORMAL.", 
	'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($normalfragsteam)))."', 
	'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($teamLabels)))."')") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	
}


/**
Basically generates all the different graphs wrt frags over time
*/
function renderFragBars($datafrags, $derivfrags, $topFraggers,$counter) {
	global $matchid;
			
	// base frags based on amount of players
	$countPlayers = count($topFraggers);
		
	$topFraggersLabels = array_chunk(generateLabelsFraggers($topFraggers),4);	
	$datafragschunks = array_chunk(sortDMdata($datafrags,$topFraggers),4);
		
	// Only do something if there are frags & players
	if($countPlayers > 1 && count($datafrags[$topFraggers[0]])>0 && count($derivfrags[$topFraggers[0]])>0) {
		
		// In duel, show bar chart like DOM
		if($countPlayers == 2) {
				
			// Net frags for first 2 players
			for($i=0;$i<($counter-1);$i++) {
				$netfrags[0][$i] = $derivfrags[$topFraggers[0]][$i]-$derivfrags[$topFraggers[1]][$i];
				$netfrags[1][$i] = $derivfrags[$topFraggers[1]][$i]-$derivfrags[$topFraggers[0]][$i];
				
				$netfrags[0][$i] = $netfrags[0][$i]>0 ? $netfrags[0][$i] : 0;
				$netfrags[1][$i] = $netfrags[1][$i]>0 ? $netfrags[1][$i] : 0;
			}
			
			mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_chartdata (mid,chartid,data,labels) VALUES (".$matchid.", ".RENDERER_CHART_FRAGS_PLAYERDERIV.", 
			'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize(array($netfrags,$datafragschunks[0]))))."', 
			'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($topFraggersLabels[0])))."')") or die(mysqli_error($GLOBALS["___mysqli_link"]));	
		
		} else {
				
			// Generate graph for player 1 to 4
			mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_chartdata (mid,chartid,data,labels) VALUES (".$matchid.", ".RENDERER_CHART_FRAGS_PLAYERSCORE.", 
			'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($datafragschunks[0])))."', 
			'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($topFraggersLabels[0])))."')") or die(mysqli_error($GLOBALS["___mysqli_link"]));
			
		}
		
		// Normalize frags
		$normalfrags = normalizeDMdata($datafrags,$topFraggers,$counter);
		$normalfragschunks = array_chunk(sortDMdata($normalfrags,$topFraggers),4);
		
		mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_chartdata (mid,chartid,data,labels) VALUES (".$matchid.", ".RENDERER_CHART_FRAGS_PLAYERNORMAL.", 
		'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($normalfragschunks[0])))."', '".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($topFraggersLabels[0])))."')") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	
		// If at least 8 players, also great second graph for 5 to 8
		if($countPlayers >= 8) {
			mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_chartdata (mid,chartid,data,labels) VALUES (".$matchid.", ".RENDERER_CHART_FRAGS_PLAYERSCORE5.", 
			'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($datafragschunks[1])))."', 
			'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($topFraggersLabels[1])))."')") or die(mysqli_error($GLOBALS["___mysqli_link"]));
		
			mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_chartdata (mid,chartid,data,labels) VALUES (".$matchid.", ".RENDERER_CHART_FRAGS_PLAYERNORMAL5.", 
			'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($normalfragschunks[1])))."', 
			'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($topFraggersLabels[1])))."')") or die(mysqli_error($GLOBALS["___mysqli_link"]));		
		}
		
		
	}
}


/**
Sort data based on ranking of players
*/
function sortDMdata($datafrags,$topFraggers) {
	$sortedArray = array();
	
	foreach($topFraggers as $fragger) {
		$sortedArray[] = $datafrags[$fragger];
	}
	
	return $sortedArray;
}

/**
Normalize data vs #2 in rank to show gap over time
*/
function normalizeDMdata($datafrags, $topFraggers,$counter) {
	// base frags based on amount of players
	$countPlayers = count($topFraggers);
	$player3hasData = (count($datafrags[$topFraggers[2]])>0);
	$player4hasData = (count($datafrags[$topFraggers[3]])>0);
	
	// Generate normalized data for players
	for($i=0;$i<$counter;$i++) {
				
		// Normalize data vs #2 for first 4 players	
		$normalfrags[$topFraggers[0]][$i] = $datafrags[$topFraggers[0]][$i] - $datafrags[$topFraggers[1]][$i];
		$normalfrags[$topFraggers[1]][$i] = 0;
		
		if($countPlayers >=3 && $player3hasData)
			$normalfrags[$topFraggers[2]][$i] = $datafrags[$topFraggers[2]][$i] - $datafrags[$topFraggers[1]][$i];
		if($countPlayers >=4 && $player4hasData)
			$normalfrags[$topFraggers[3]][$i] = $datafrags[$topFraggers[3]][$i] - $datafrags[$topFraggers[1]][$i];
					
		// Also do this vs 6 for player 5 to 8 if we have at least 8 players
		if($countPlayers >= 8) {
			$normalfrags[$topFraggers[4]][$i] = $datafrags[$topFraggers[4]][$i] - $datafrags[$topFraggers[5]][$i];
			$normalfrags[$topFraggers[6]][$i] = $datafrags[$topFraggers[6]][$i] - $datafrags[$topFraggers[5]][$i];
			$normalfrags[$topFraggers[7]][$i] = $datafrags[$topFraggers[7]][$i] - $datafrags[$topFraggers[5]][$i];
			$normalfrags[$topFraggers[5]][$i] = 0;
		}
			
	}		

	return $normalfrags;
}

/**
Get data wrt frags over time
*/
function parseDMdata($uid) {
	global $time_gamestart;
	global $time_gameend;
	global $time_ratio_correction;
	global $playernumberofteams;
	global $playerteams;
	
	$uid = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $uid);
	
	// Get all frags related events + start & end
	$q_frags = mysqli_query($GLOBALS["___mysqli_link"], "SELECT * FROM `uts_temp_$uid` WHERE col0>=".mysqli_real_escape_string($GLOBALS["___mysqli_link"], $time_gamestart)." ORDER BY id ASC") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	
	// Get all unique player id's
	$q_ids = mysqli_query($GLOBALS["___mysqli_link"], "SELECT DISTINCT col2 FROM `uts_temp_$uid` WHERE col1='kill'");
	while($data = mysqli_fetch_array($q_ids)) {
		$ids[] = $data[0];
	}
		
	// Iterate over frags
	$prevx = 0;
	$prevfrags = array();
	$prevfragsteam = array();
	$counter = 1;
	$firstrun = true;
	$realTimeEnd = ($time_gameend-$time_gamestart)/$time_ratio_correction/60;
	
	while($data = mysqli_fetch_array($q_frags)) {
	
		// Collect data from utstats own table
		$r_time = ($data[1]-$time_gamestart)/$time_ratio_correction/60;	// Transform UT time to real time
		$r_type = $data[2];
		$r_killer = $data[3];
		
		// If game_end event & game ended cause timelimit reached, round the time down to prevent UT gay timing to screw the graph
		if($r_type == "game_end" && $r_capper == "timelimit")
			$r_time = floor($r_time);
		
		// Add frag if kill event, deduct if suicide
		if($r_type == "kill") {
			$frags[$r_killer]++;
			$fragsteam[$playerteams[$r_killer]]++;
		} else if($r_type == "suicide" || $r_type == "teamkill") {
			$frags[$r_killer]--;
			$fragsteam[$playerteams[$r_killer]]--;
		}
		
		// Only save the data each minute. Added game_start & game_end for first & last tick
		// Add 0.05 sec to counter for gay UT timing rounding error
		if($r_type == "game_start" || ($r_time>$counter && ($realTimeEnd-$r_time)>0.4) || $r_type == "game_end") {
							
			foreach($ids as $id) { 
				// On first run, everyone has score 0
				if($firstrun)
					$frags[$id] = 0;
				else
					$derivfrags[$id][] = $frags[$id] - $prevfrags[$id];
									
				$datafrags[$id][] = $frags[$id];					
			}
			
			$prevfrags = $frags;
			
			for($i=0;$i<$playernumberofteams;$i++) {
				// On first run, everyone has score 0
				if($firstrun)
					$fragsteam[$i] = 0;
				else
					$derivfragsteam[$i][] = $fragsteam[$i] - $prevfragsteam[$i];
									
				$datafragsteam[$i][] = $fragsteam[$i];	
			
			}
			
			$prevfragsteam = $fragsteam;
			
			if(!$firstrun)
				$counter++;
			else
				$firstrun = false;
		}					
		
	}
		
	// Get top 8 - no time for the noobies
	arsort($frags);
	arsort($fragsteam);
	$topFraggers = array_slice(array_keys($frags),0,8);
	$topTeams = array_slice(array_keys($fragsteam),0,$playernumberofteams);
	
	return array($datafrags, $derivfrags, $topFraggers,$counter,$datafragsteam,$derivfragsteam,$topTeams);
}

?>