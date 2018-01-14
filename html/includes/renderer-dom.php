<?php

require_once 'renderer-general-import.php';

/*
Helper function to create the temporary table used for dom stats
*/
function createEmptyTempTable($table_name) {
		
	$sqlCreateTable = "
		CREATE TEMPORARY TABLE `$table_name` (
	 `id` int(11) NOT NULL AUTO_INCREMENT,
	 `cp` varchar(254) NOT NULL,
	 `teamid` int(11) NOT NULL,
	 `playerid` int(11) NOT NULL,
	 `playername` varchar(254) NOT NULL,
	 `start` float NOT NULL,
	 `end` float NOT NULL,
	 `time` float NOT NULL,
	 `scoret0` float NOT NULL,
	 `scoret1` float NOT NULL,
	 `realTimeEnd` float NOT NULL,
	 PRIMARY KEY (`id`) 
	 ) ENGINE=MyISAM 
	";
	mysqli_query($GLOBALS["___mysqli_link"], $sqlCreateTable) or die(mysqli_error($GLOBALS["___mysqli_link"]));
}
	
/*
Create & populate the dom stats table
@return name of temporary table
*/
function generateTempTable($uid) {
	global $playernames;
	global $playerteams;
	global $time_gamestart;
	global $time_gameend;
	global $time_ratio_correction;

	$uid = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $uid);
	$tempTableName = "uts_tempidom_".$uid;
	
	createEmptyTempTable($tempTableName);
			
	// iterate over cp's. also capture game-end event as a quick hack to also do final iteration over final cp
	$q_logdom = mysqli_query($GLOBALS["___mysqli_link"], "SELECT * FROM uts_temp_$uid WHERE (col1='controlpoint_capture' OR col1='game_end') AND col0>".mysqli_real_escape_string($GLOBALS["___mysqli_link"], $time_gamestart)." ORDER BY col1, col2, id ASC")or die(mysqli_error($GLOBALS["___mysqli_link"]));
		
	$prev_time = 0;		
	
	// dynamicly create insert query to populate the table in 1 go, instead of multiple database calls
	$insertQuery = "INSERT INTO $tempTableName (cp, teamid, playerid, playername, start, end, time,scoret0,scoret1,realTimeEnd) VALUES";
	
	// basically loop over all capture events per CP. Each time calculate how long the previous owner had the point and translate this to dom points
	while($r_logdom = mysqli_fetch_array($q_logdom)) {
		$points[0] = 0;
		$points[1] = 0;
		$ticks[0] = 0;
		$ticks[1] = 0;
			
		$start_time = $prev_time;
		
		$r_cp = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $r_logdom['col2']);	
		$r_time = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $r_logdom['col0']);
		
		// skip first capture event - no dom points given at start of map since no-one has cp		
		if($prev_time > 0) {
		
			// if switching to other cp
			if($prev_cp != $r_cp) {	
				$end_time = $time_gameend; // take game end time to calculate time cp was taken, since it's the last time it's taken + extra tick for end time
				
			} else {			
				$end_time = $r_time;		
			}	

			$time_diff = ($end_time - $start_time);
			
			// point has to be yours for at least a second before it starts to add points
			if($time_diff >= 1) {
			
				// after this second, it will start to add points during each time the timer runs (= each second)
				// thus, first run of timer is ceil of start time
				// also, floor of end time since having the point after the timer ran, but not until the next tick = no points
				// timing in log does not account for gamespeed, typically 1.1 ratio, which is corrected in these times
				// test was done to save the actual tick times based upon the dom_points stats event (happens each 5 ticks), but this doesn't result in significant better results to be worth the effort
							
				// save amount of timer 'ticks' that generate a score of 0.2
				$ticks[$r_teamid] = (floor($end_time/$time_ratio_correction)-ceil($start_time/$time_ratio_correction));
				
			}
					
			// if no tick happened, insert the event with 0  points		
			if($ticks[$r_teamid]==0) {
				
				$realTimeEnd=($start_time-$time_gamestart)/$time_ratio_correction/60;	
				$insertQuery .= " ('$prev_cp', '$r_teamid', '$r_pid', '$r_pname', '$start_time', '$end_time', '$time_diff', '".$points[0]."', '".$points[1]."', '$realTimeEnd'),";
			
			// if ticks happened, insert each seperate tick as an event
			} else {
					
				for($i=1;($ticks[$r_teamid]+1-$i) > 0;$i++) {
				
					$realTimeEnd=($start_time+$time_diff/$ticks[$r_teamid]*$i-$time_gamestart)/$time_ratio_correction/60;		
					$points[$r_teamid] = 0.2;
					
					$insertQuery .= " ('$prev_cp', '$r_teamid', '$r_pid', '$r_pname', '$start_time', '$end_time', '$time_diff', '".$points[0]."', '".$points[1]."', '$realTimeEnd'),";
					
				}
			}
			
		} 	
		
		// save data for current capture event
		$prev_time = $r_time;
		$prev_cp = $r_cp;
		
		$r_event = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $r_logdom['col1']);
		$r_pid = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $r_logdom['col3']);
		$r_teamid = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $playerteams[$r_pid]);
		$r_pname = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $playernames[$r_pid]);
		
	} 
	
	// populate table
	$insertQuery = rtrim($insertQuery,",");
	mysqli_query($GLOBALS["___mysqli_link"], $insertQuery) or die(mysqli_error($GLOBALS["___mysqli_link"]));
	
	return $tempTableName;
	
}

/*
Generate times when amp was taken
@return $amps[$team][$start_time,$end_time, $pid]
*/
function generateAmpTimes($uid) {
	global $playernames;
	global $playerteams;
	global $time_gamestart;
	global $time_gameend;
	global $time_ratio_correction;
	
	$amps = array();
	$prev_time = array();
	
	$uid = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $uid);
	
	// Get activate & deactivate times & players
	$q_amps = mysqli_query($GLOBALS["___mysqli_link"], "SELECT col0,col1,col3 FROM uts_temp_$uid WHERE col2='Damage Amplifier' AND (col1='item_activate' OR col1='item_deactivate') ORDER BY id ASC");
	while($r_amps = mysqli_fetch_array($q_amps)) {
		$time = ($r_amps[0]-$time_gamestart)/$time_ratio_correction/60;
		$event = $r_amps[1];
		$pid = $r_amps[2];
		
		// If amp is deactivated, calculate the time it was used
		if($event == "item_deactivate") {			
			$amps[$playerteams[$pid]][] = array($prev_time[$pid], $time,$pid);
			$prev_time[$pid] = 0;
			
		// If amp is activated & none yet acquired, save the time of the start of this amp run
		} else if($prev_time[$pid] == 0) {
			$prev_time[$pid] = $time;
		}
	}
	
	return $amps;
}

/*
Function to render the chart with amp runs
*/
function renderAmpBars($uid,$tempTableName) {
	global $playernames;
	global $ampTimes;
	global $matchid;
	global $renderer_color;
	
	$uid = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $uid);
	
	// Iterate over amp runs
	for($i=0;$i<2;$i++) {
		foreach ($ampTimes[$i] as $ampTaken) {
			$ampStart = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $ampTaken[0]);
			$ampEnd = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $ampTaken[1]);
			$pid = $ampTaken[2];
			
			// Only save amp runs longer than 20seconds
			if($ampEnd > ($ampStart+.33)) {
			
				// Get scores during amprun
				$q_scoresDuringAmp = mysqli_query($GLOBALS["___mysqli_link"], "SELECT SUM(scoret0),SUM(scoret1) FROM $tempTableName WHERE realTimeEnd > $ampStart AND realTimeEnd < $ampEnd");
				$r_scoresDuringAmps = mysqli_fetch_array($q_scoresDuringAmp);
												
				$netPoints = $r_scoresDuringAmps[$i] - $r_scoresDuringAmps[1-$i];
				
				// Process time for readable label
				$totalTime = $ampEnd-$ampStart;
				$totalTimeMinutes = floor($totalTime);
				$totalTimeSeconds = round(($totalTime-$totalTimeMinutes)*60,0);
				
				$timeLabel = "";
				if($totalTimeMinutes>0) { $timeLabel .= $totalTimeMinutes."m"; }
				if($totalTimeSeconds>0) { $timeLabel .= $totalTimeSeconds."s"; }
				
				$playerName = $playernames[$pid];
								
				$ampRun['start'] = $ampStart;
				$ampRun['label'] = substr($playerName,0,18)." (".$timeLabel.")";
				$ampRun['points'] = round($netPoints,0);
				$ampRun['team'] = $i;
				
				$ampRuns[] = $ampRun;
			}
		}
	}
	
	if(count($ampRuns)>0) {
		
		// Sort ampruns not on teams, but on start time, to make sure they are plotted chronocologicstic
		$ampRuns = array_sort($ampRuns,'start');
		
		$data = array();
		$labels = array("Net Points");
		$categories = array();
		
		// Process the ampruns to map it to bar plots
		foreach ($ampRuns as $ampRun) {
			$categories[] = $ampRun['label'];
			$data[] = array("y" => $ampRun['points'], "color" => $renderer_color['team'][$ampRun['team']][0]);
		}
				
		// Save team score over team for teams
		mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_chartdata (mid,chartid,data,labels,categories) VALUES (".$matchid.", ".RENDERER_CHART_ITEMS_AMPRUNS.", 
			'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize(array($data))))."', 
			'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($labels)))."', 
			'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($categories)))."')") or die(mysqli_error($GLOBALS["___mysqli_link"]));
				
	}
	
	
}

/*
Render dom points over time & net rate of change per minute over all cp's
*/
function renderDataTotal($uid,$tempTableName) {
	$query = "SELECT t.realTimeEnd,@t0sum := @t0sum + t.scoret0 AS cumulScoret0,@t1sum := @t1sum + t.scoret1 AS cumulScoret1 FROM $tempTableName t JOIN (SELECT @t0sum := 0) r JOIN (SELECT @t1sum := 0) s ORDER BY realTimeEnd ASC";
	$appex = "total";
	
	renderData($uid,$tempTableName,$query,$appex,"Total score","");
}

/*
Render dom points over time & net rate of change per minute per cp
*/
function renderDataCPs($uid,$tempTableName) {
	$q_cps = mysqli_query($GLOBALS["___mysqli_link"], "SELECT DISTINCT(cp) FROM $tempTableName");
	
	$i=0;
	while($r_cps = mysqli_fetch_array($q_cps)) {
		$r_cp = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $r_cps[0]);
		$query = "SELECT t.realTimeEnd,@t0sum := @t0sum + t.scoret0 AS cumulScoret0,@t1sum := @t1sum + t.scoret1 AS cumulScoret1 FROM $tempTableName t JOIN (SELECT @t0sum := 0) r JOIN (SELECT @t1sum := 0) s WHERE t.CP = '$r_cp' ORDER BY realTimeEnd ASC";
		$appex = "cp".$i++;
		renderData($uid,$tempTableName,$query,$appex,"Score CP ".$r_cps[0],$r_cps[0]);
		
	}	
}

/*
Render dom points over time & net rate of change per minute
*/
function renderData($uid,$tempTableName,$query,$appex,$title,$cp) {
	global $matchid;
		
	// Use helper function to parse the data into different datasets for charts
	list($dompoints,$derivdompoints) = parseData($query);	
	$title = "Total";
		
	if(count($dompoints) > 0 && count($dompoints[0]) > 0 && count($dompoints[1]) > 0) {
		
				
		// If this is a cp plot, use player names as label. Otherwise simply teamname
		if(strlen($cp) > 0) {			
			$labels = renderNamesCP($tempTableName,$cp);
			$title = $cp;
		} else {
			$labels = generateTeamLabels();
			
		}
				
		// Deriv Dom points over time per team
		mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_chartdata (mid,chartid,title,data,labels) VALUES (".$matchid.", ".RENDERER_CHART_DOM_SCOREDERIV.", 
			'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], $title)."', 
			'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize(array($derivdompoints,$dompoints))))."', 
			'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($labels)))."')") or die(mysqli_error($GLOBALS["___mysqli_link"]));
		
	}
}

/*
Helper function to parse the data for the main renderData
*/
function parseData($query) {
	$q_result = mysqli_query($GLOBALS["___mysqli_link"], $query) or die(mysqli_error($GLOBALS["___mysqli_link"])); 

	$smallcounter = 1/3;
	$counter = 1;
	$prevpoints = array(0,0);
	$prevtime = 0;
	
	$run = 0;
	$numRows = mysqli_num_rows($q_result);
	
	while($data = mysqli_fetch_array($q_result)) {	
		$r_time = round($data[0],2);
		if($run==($numRows-1))
			$r_time = $prevtime;
		
		$points[0] = round($data[1],0);
		$points[1] = round($data[2],0);	
			
		// Prep data for dom points over time
		// Only save the data each $smallcounter interval, to optimize rendering time
		if($r_time >= $smallcounter || $run==($numRows-1)) {
		
			for($i=0;$i<2;$i++) {
				$dompoints[$i][] = array($r_time,$points[$i]);
			}
			
			$smallcounter+=1/3;
		}
			
		// Prep data for net change over time
		// Only save the data each integer interval, since we want the value per minute
		if($r_time >= $counter || ($run==($numRows-1) && ($counter-$r_time)<(1/3))) {
		
			// Iterate over teams to get points last min
			for($i=0;$i<2;$i++) {
				$derivypre[$i] = $points[$i]-$prevpoints[$i];
			}
			
			// Iterate over teams again to determine net points
			for($i=0;$i<2;$i++) {
				$derivypost[$i] = $derivypre[$i]-$derivypre[1-$i];
				$derivdompoints[$i][] = $derivypost[$i]>0 ? $derivypost[$i] : 0;
			}
			
			$prevpoints = $points;
			
			$counter++;
		}

		$prevtime = $r_time;
		$run++;
	}
		
		
	return array($dompoints,$derivdompoints);
}

/*
Render the names of players that played the cp
*/
function renderNamesCP($tempTableName,$cp) {
	$labels = array();

	$q_namesPerCP = mysqli_query($GLOBALS["___mysqli_link"], "SELECT playername, COUNT( playername ) AS cplayer, MAX( teamid ) AS tid, AVG( realTimeEnd ) AS ati FROM  $tempTableName WHERE cp = '".mysqli_real_escape_string($GLOBALS["___mysqli_link"], $cp)."' GROUP BY playername ORDER BY tid,ati") or die(mysqli_error($GLOBALS["___mysqli_link"]));
				
	while($r_namesPerCP = mysqli_fetch_array($q_namesPerCP)) {
	
		$playerName = substr($r_namesPerCP[0],0,18);
		$timesTouched = $r_namesPerCP[1];
		$teamid = $r_namesPerCP[2];
		$avgTime = $r_namesPerCP[3];
	
		// Only plot the playername if he touched the cp at least 60 times
		if(isset($playerName) && !empty($playerName) && strlen($playerName)>0 && $timesTouched > 75) {
			if(strlen($labels[$teamid])>0)
				$labels[$teamid] .= " + ";
			
			$labels[$teamid] .= $playerName;					
		}
		
	}
	
	// Default value if empty
	if(!isset($labels[0]) || !strlen($labels[0])>1)
		$labels[0] = 'Red Team';
	if(!isset($labels[1]) || !strlen($labels[1])>1)
		$labels[1] = 'Blue Team';

	return $labels;
}


?>