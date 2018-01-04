<?php
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_scatter.php'; 
require_once 'jpgraph/jpgraph_mgraph.php'; 
require_once 'jpgraph/jpgraph_bar.php';
require_once 'jpgraph/jpgraph_line.php';
require_once 'jpgraph/jpgraph_radar.php';

/*
Retrieve player names & player teams
Pretty much utstats code re-used
@return $playernames, $playerteams (arrays based on pid)
*/
function getPlayerTeam($uid) {
	
	$playernames = array();
	$playerteams = array();
	
	// Get List of Player IDs and Process What They Have Done
	$sql_player = "SELECT DISTINCT col4 FROM uts_temp_$uid WHERE col1 = 'player' AND col2 = 'rename' AND col4 <> ''";
	$q_player = mysql_query($sql_player) or die(mysql_error());
	
	while ($r_player = mysql_fetch_array($q_player)) {
		$playerid = $r_player[col4];

		// Get players last name used
		$r_player2 = small_query("SELECT col3 FROM uts_temp_$uid WHERE col1 = 'player' AND col2 = 'rename' AND col4 = $playerid ORDER BY id DESC LIMIT 0,1");
		$playername = addslashes($r_player2[col3]);

		// Get players last team
		$r_player3 = small_query("SELECT col4 FROM uts_temp_$uid WHERE col1 = 'player' AND col2 = 'TeamChange' AND col3 = $playerid AND col4 != 255 ORDER BY id DESC LIMIT 0,1");
		$playerteam = $r_player3[col4];

		$playernames[$playerid] = $playername;
		$playerteams[$playerid] = $playerteam;		
	}
	
	return array($playernames,$playerteams);

}

/*
Get time game starts, game ends, and ratio compared to real time
@return $time_gamestart, $time_gameend, $time_ratio_correction (difference in ut time & real time, typically 110%)
*/
function getGameStartEndRatio($uid) {

	// gather game start & end time
	$q_logdom = mysql_query("SELECT col0 FROM uts_temp_$uid WHERE col1='game_start' OR col1='game_end' ORDER BY id ASC")or die(mysql_error());

	$time_gamestart = mysql_result($q_logdom,0);
	$time_gameend = mysql_result($q_logdom,1);
	$time_ratio_correction = ($time_gameend-$time_gamestart)/1200;
			
	return array($time_gamestart, $time_gameend, $time_ratio_correction);
}

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
	mysql_query($sqlCreateTable) or die(mysql_error());
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

	$tempTableName = "uts_tempidom_".$uid;
	
	createEmptyTempTable($tempTableName);
			
	// iterate over cp's. also capture game-end event as a quick hack to also do final iteration over final cp
	$q_logdom = mysql_query("SELECT * FROM uts_temp_$uid WHERE col1='controlpoint_capture' OR col1='game_end' ORDER BY col1, col2, id ASC")or die(mysql_error());
		
	$prev_time = 0;		
	
	// dynamicly create insert query to populate the table in 1 go, instead of multiple database calls
	$insertQuery = "INSERT INTO $tempTableName (cp, teamid, playerid, playername, start, end, time,scoret0,scoret1,realTimeEnd) VALUES";
	
	// basically loop over all capture events per CP. Each time calculate how long the previous owner had the point and translate this to dom points
	while($r_logdom = mysql_fetch_array($q_logdom)) {
		$points[0] = 0;
		$points[1] = 0;
		$ticks[0] = 0;
		$ticks[1] = 0;
			
		$start_time = $prev_time;
		
		$r_cp = $r_logdom['col2'];	
		$r_time = $r_logdom['col0'];
		
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
		
		$r_event = $r_logdom['col1'];
		$r_pid = $r_logdom['col3'];
		$r_teamid = mysql_real_escape_string($playerteams[$r_pid]);
		$r_pname = mysql_real_escape_string($playernames[$r_pid]);
		
	} 
	
	// populate table
	$insertQuery = rtrim($insertQuery,",");
	mysql_query($insertQuery) or die(mysql_error());
	
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
	
	// Get activate & deactivate times & players
	$q_amps = mysql_query("SELECT col0,col1,col3 FROM uts_temp_$uid WHERE col2='Damage Amplifier' AND (col1='item_activate' OR col1='item_deactivate') ORDER BY id ASC");
	while($r_amps = mysql_fetch_array($q_amps)) {
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
	global $domimage_color;
	global $domimages_folder;
	global $domimage_width;
	global $domimage_heigth;
	
	// Iterate over amp runs
	for($i=0;$i<2;$i++) {
		foreach ($ampTimes[$i] as $ampTaken) {
			$ampStart = $ampTaken[0];
			$ampEnd = $ampTaken[1];
			$pid = $ampTaken[2];
			
			// Only save amp runs longer than 20seconds
			if($ampEnd > ($ampStart+.33)) {
			
				// Get scores during amprun
				$q_scoresDuringAmp = mysql_query("SELECT SUM(scoret0),SUM(scoret1) FROM $tempTableName WHERE realTimeEnd > $ampStart AND realTimeEnd < $ampEnd");
				$r_scoresDuringAmps = mysql_fetch_array($q_scoresDuringAmp);
												
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
				$ampRun['label'] = substr($playerName,0,15)." (".$timeLabel.")";
				$ampRun['points'] = $netPoints;
				$ampRun['team'] = $i;
				
				$data[] = $ampRun;
			}
		}
	}
	
	if(count($data)>0) {
		
		// Sort ampruns not on teams, but on start time, to make sure they are plotted chronocologicstic
		$data = array_sort($data,'start');
		
		$maxValue=0;
		// Process the ampruns to map it to 2 differnt bar plots per team
		foreach ($data as $ampRun) {
			$labels[] = $ampRun['label'];
			$values[$ampRun['team']][] = $ampRun['points'];
			
			$posValue = $ampRun['points']>0? $ampRun['points'] : $ampRun['points']*-1;
			
			if($posValue>$maxValue)
				$maxValue = $posValue;
			
			// Add 0 value for other team to ensure only 1 bar is plotted each time (either team 0 or team 1)
			$values[1-$ampRun['team']][] = 0;
		}
		
		$tickInterval = $maxValue>15?10:5;
		$maxValue = $tickInterval*ceil($maxValue/$tickInterval);
				
		// Create the graph
		// Some commands to enable horizontal bars & moved axis for proper formatting
		$graph = new Graph($domimage_width+8,$domimage_heigth+8);
		$graph->SetScale('textlin',$maxValue*-1,$maxValue);	  
		$graph->SetTickDensity(TICKD_SPARSE);
		$graph->Set90AndMargin('20','15','30','30');
		$graph->SetMarginColor($domimage_color['background']); 
		$graph->SetColor($domimage_color['background']); 
		$graph->SetFrame(true,$domimage_color['background'],0);
		$graph->ygrid->SetFill(true,$domimage_color['band'][0],$domimage_color['band'][1]); 
		$graph->ygrid->SetColor($domimage_color['background']); 
		
		$graph->title->Set('Net Score Amp Runs');
		$graph->title->SetFont(FF_VERDANA,FS_BOLD);
		$graph->title->SetColor($domimage_color['heading']);
		
		$graph->xaxis->SetPos('min');
		$graph->xaxis->SetTickLabels($labels);
		$graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,7);
		$graph->xaxis->SetColor($domimage_color['font']);
		$graph->xaxis->SetLabelAlign('left','center');
		$graph->xaxis->SetColor($domimage_color['background'],$domimage_color['font']);
		
		$graph->yaxis->SetPos('max');
		$graph->yaxis->SetLabelAlign('center','top');
		$graph->yaxis->SetLabelSide(SIDE_RIGHT);
		$graph->yaxis->SetTickSide(SIDE_LEFT);
		$graph->yaxis->scale->ticks->set($tickInterval);
		$graph->yaxis->SetColor($domimage_color['background'],$domimage_color['font']);
				
		if(count($values[0])>0) { 
			$b1plot = new BarPlot($values[0]); 
			$graph->Add($b1plot);
			$b1plot->SetFillGradient($domimage_color['team'][0][0],$domimage_color['team'][0][1],GRAD_HOR);
			$b1plot->SetColor($domimage_color['team'][0][0]);
			
		} if(count($values[1])>0) { 
			$b2plot = new BarPlot($values[1]); 
			$graph->Add($b2plot);		
			$b2plot->SetFillGradient($domimage_color['team'][1][0],$domimage_color['team'][1][1],GRAD_HOR);
			$b2plot->SetColor($domimage_color['team'][1][0]);	
		}		
		
		$graph->Stroke($domimages_folder."/".$matchid."-ampruns.png");		
	}
}

/*
Generate the chart with pickups
*/
function renderDataPickups($uid) {
	global $matchid;
	global $domimage_color;
	global $domimages_folder;
	global $domimage_width;
	global $domimage_heigth;

	$q_pickups = mysql_query("SELECT SUM(pu_belt), SUM(pu_keg), SUM(pu_pads), SUM(pu_armour), SUM(pu_amp) FROM uts_player as p WHERE matchid = $matchid GROUP BY team") or die(mysql_error());
	
	while($r_pickups = mysql_fetch_row($q_pickups)) {
		$preData[] = $r_pickups;
	}
	
	$anyPickupDone = false;
	
	// Process data to convert these to percentages
	// Normal numbers don't plot nicely (fe. pads getting much higher pickups due to lower spawn time
	for($i=0;$i<5;$i++) {
		if($preData[0][$i]>0) {
			$data[0][$i] = round($preData[0][$i]/($preData[0][$i]+$preData[1][$i])*100,0);
			$data[1][$i] = 100-$data[0][$i];
			$anyPickupDone = true;			
		} else if($preData[1][$i]>0) {
			$data[0][$i] = 0;
			$data[1][$i] = 100;
			$anyPickupDone = true;
		} else {
			$data[0][$i] = 0;
			$data[1][$i] = 0;		
		}
	}
	
	if($anyPickupDone) {
		// Plot radargraph
		$titles = array('belt','keg','pads','armour','amp');
		$graph = new RadarGraph ($domimage_width+8,$domimage_heigth+8);
		$graph->SetScale('lin',0,100);
		$graph->yscale->ticks->Set(25,5);
		$graph->setMargin(30,30,30,30);
		$graph->SetMarginColor($domimage_color['background']); 
		$graph->SetColor($domimage_color['background']); 
		$graph->SetFrame(true,$domimage_color['background'],0);
		$graph->title->Set('Pickups %');
		$graph->title->SetFont(FF_VERDANA,FS_BOLD);
		$graph->title->SetColor($domimage_color['heading']);
		$graph->HideTickMarks();
		$graph->axis->HideLabels();
		
		$graph->SetTitles($titles);
		$graph->axis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
		$graph->axis->title->SetColor($domimage_color['font']);
		$graph->grid->Show(true,true);
		$graph->grid->SetLineStyle('dashed');	 	 
		
		// Seperate plot within radar per team
		$plot1 = new RadarPlot($data[0]);	 
		$plot2 = new RadarPlot($data[1]);	 
		$graph->Add($plot1);
		$graph->Add($plot2);
		
		$plot1->SetFillColor($domimage_color['team'][0][0].'@0.5');
		$plot1->SetColor($domimage_color['team'][0][0]);
		$plot1->SetLineWeight(2);
		$plot2->SetFillColor($domimage_color['team'][1][0].'@0.5');
		$plot2->SetColor($domimage_color['team'][1][0]);
		$plot2->SetLineWeight(2);
		
		$graph->Stroke($domimages_folder."/".$matchid."-pickups.png");
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
	$q_cps = mysql_query("SELECT DISTINCT(cp) FROM $tempTableName");
	
	$i=0;
	while($r_cps = mysql_fetch_array($q_cps)) {
		$r_cp = $r_cps[0];
		$query = "SELECT t.realTimeEnd,@t0sum := @t0sum + t.scoret0 AS cumulScoret0,@t1sum := @t1sum + t.scoret1 AS cumulScoret1 FROM $tempTableName t JOIN (SELECT @t0sum := 0) r JOIN (SELECT @t1sum := 0) s WHERE t.CP = '$r_cp' ORDER BY realTimeEnd ASC";
		$appex = "cp".$i++;
		renderData($uid,$tempTableName,$query,$appex,"Score CP ".$r_cp,$r_cp);
		
	}	
}

/*
Render dom points over time & net rate of change per minute
*/
function renderData($uid,$tempTableName,$query,$appex,$title,$cp) {
	global $matchid;
	global $domimage_color;
	global $domimages_folder;
	global $domimage_width;
	global $domimage_heigth;
	
	// Use helper function to parse the data into different datasets for charts
	list($datax,$datay1,$datay2,$derivx,$derivy1,$derivy2) = parseData($query);	
	
	// If this is a cp plot, ensure enough space for plotting the names underneath
	if(strlen($cp)>0) {
	
		$g1heigth = $domimage_heigth*.65;
		$g2heigth = $domimage_heigth*.35;
		$g2marginlow = 50;
	
	// Else, use all space
	} else {
	
		$g1heigth = $domimage_heigth*.75;
		$g2heigth = $domimage_heigth*.25;
		$g2marginlow = 10;
	
	}
	
	// Dom points over time per team
	$graph1 = createGraph($title,$domimage_width,$g1heigth,array(30,10,30,30),"linlin");
	$sp11 = generateScatterLink($datax, $datay1,$domimage_color['team'][0][0],'FCallbackRedAmp');
	$sp12 = generateScatterLink($datax, $datay2,$domimage_color['team'][1][0],'FCallbackBlueAmp');
	$graph1->Add($sp11);
	$graph1->Add($sp12);
	
	// Net rate of change per minute
	$graph2 = createGraph("",$domimage_width,$g2heigth,array(30,10,5,$g2marginlow),"linlin");
	$graph2->xaxis->HideLabels();
	
	$b1plot = new BarPlot($derivy1,$derivx);
	$b2plot = new BarPlot($derivy2,$derivx);		
	
	$graph2->Add($b1plot);
	$graph2->Add($b2plot);
	
	$b1plot->SetFillGradient($domimage_color['team'][0][0],$domimage_color['team'][0][1],GRAD_HOR);
	$b1plot->SetColor($domimage_color['team'][0][0]);
	$b1plot->SetWidth(0.85);
	$b2plot->SetFillGradient($domimage_color['team'][1][0],$domimage_color['team'][1][1],GRAD_HOR);
	$b2plot->SetColor($domimage_color['team'][1][0]);	
	$b2plot->SetWidth(0.85);
	
	// If this is a cp plot, render the names
	if(strlen($cp) > 0) {			
		renderNamesCP($tempTableName,$cp,$graph2,$domimage_width,$g2heigth);	
	}
		
	// Combine both graphs into one
	$mgraph = new MGraph();
	$xpos1=0;$ypos1=0;
	$xpos2=0;$ypos2=$g1heigth;
	$mgraph->Add($graph1,$xpos1,$ypos1);
	$mgraph->Add($graph2,$xpos2,$ypos2);
	$mgraph->SetFillColor($domimage_color['background']);
		
	$gdImgHandler = $mgraph->Stroke(_IMG_HANDLER);
	 
	$fileName = $domimages_folder."/".$matchid."-".$appex.".png";
	$mgraph->Stroke($fileName);	
}

/*
Helper function to parse the data for the main renderData
*/
function parseData($query) {
	$q_result = mysql_query($query) or die(mysql_error()); 

	$prevx = 0;
	$prevy1 = 0;
	$prevy2 = 0;
	$smallcounter = 1/3;
	$counter = 1;
	
	while($data = mysql_fetch_array($q_result)) {	
			
		// Prep data for dom points over time
		// Only save the data each $smallcounter interval, to optimize rendering time
		if($data[0]>=$smallcounter) {
		
			$datax[] = $data[0];
			$datay1[] = $data[1];
			$datay2[] = $data[2];	
			
			$smallcounter+=1/3;
		}
			
		// Prep data for net change over time
		// Only save the data each integer interval, since we want the value per minute
		// Start at 0.5, to ensure proper alignment with graph above (cheap hack)
		if($data[0]>=$counter) {
			$derivx[] = $counter-1;
		
			$derivy1pre = $data[1]-$prevy1;
			$derivy2pre = $data[2]-$prevy2;
			
			$derivy1post = $derivy1pre-$derivy2pre;
			$derivy2post = $derivy2pre-$derivy1pre;
			
			$derivy1[] = $derivy1post>0 ? $derivy1post : 0;
			$derivy2[] = $derivy2post>0 ? $derivy2post : 0;
			
			$prevy1 = $data[1];
			$prevy2 = $data[2];		
			
			$counter++;
		}					
	}
		
	return array($datax,$datay1,$datay2,$derivx,$derivy1,$derivy2);
}

/*
Render the names of players that played the cp
*/
function renderNamesCP($tempTableName,$appex,$graph,$width,$height) {
	global $domimage_color;
	
	$appex = mysql_real_escape_string($appex);

	$q_namesPerCP = mysql_query("SELECT playername, COUNT( playername ) AS cplayer, MAX( teamid ) AS tid, AVG( realTimeEnd ) AS ati FROM  $tempTableName WHERE cp = '$appex' GROUP BY playername ORDER BY tid,ati") or die(mysql_error());
		
	$yTeam[0][0] = 45;
	$yTeam[0][1] = 35;
	$yTeam[1][0] = 20;
	$yTeam[1][1] = 10;
	$prevTeam = -1;
		
	while($r_namesPerCP = mysql_fetch_array($q_namesPerCP)) {
		$playerName = substr($r_namesPerCP[0],0,20);
		$timesTouched = $r_namesPerCP[1];
		$teamid = $r_namesPerCP[2];
		$avgTime = $r_namesPerCP[3];
	
		// Only plot the playername if he touched the cp at least 60 times
		if(strlen($playerName)>0 && $timesTouched > 60) {
		
			$txt = new Text($playerName);	 
			
			// Names per team can be plotted on 2 different heights to ensure it doesn't become a mess on one line
			// This bit of code ensures height is flipped each time
			if($teamid == $prevTeam) {			
				$ypos = $height-$yTeam[$teamid][1];
				$prevTeam = -1;					
			} else {
				$ypos = $height-$yTeam[$teamid][0];
				$prevTeam = $teamid;
			}
			
			$color = $domimage_color['team'][$teamid][0];
			
			// Plot name at average time, minus 25 (hack assuming most names are around 50 pixels)
			$txt->SetPos($width/20*$avgTime-25,$ypos);
			
			$txt->SetColor($color);
			$txt->SetFont(FF_VERDANA,FS_NORMAL,8);
			$graph->AddText($txt); 					
		}
		
		$prevAvgTime = $avgTime;
	}

}

/*
Helper function to generate the graph object
*/
function createGraph($title,$width,$height,$margin,$scale) {
	global $domimage_color;

	$graph = new Graph($width,$height);
	
	$graph->SetScale($scale);	 
	$graph->SetMargin($margin[0],$margin[1],$margin[2],$margin[3]);        
	$graph->SetMarginColor($domimage_color['background']); 
	$graph->SetColor($domimage_color['background']); 
	$graph->SetFrame(true,$domimage_color['background'],0);
	
	$graph->ygrid->SetFill(true,$domimage_color['band'][0],$domimage_color['band'][1]); 
	$graph->ygrid->SetColor($domimage_color['background']); 
	
	$graph->yaxis->SetColor($domimage_color['background'],$domimage_color['font']);
	$graph->xaxis->SetColor($domimage_color['background'],$domimage_color['font']);
	
	if(strlen($title)>0) {
		$graph->title->Set($title);
		$graph->title->SetFont(FF_VERDANA,FS_BOLD);
		$graph->title->SetColor($domimage_color['heading']);
	}
	return $graph;
}

/*
Helper function to generate the scatter plots
*/
function generateScatterLink($datax, $datay, $color,$callback) {
	$sp = new ScatterPlot($datay,$datax);

	$sp->link->Show();
	$sp->link->SetStyle('solid');
	$sp->link->SetWeight(3);
	$sp->link->SetColor($color);
	
	if(strlen($callback) > 0)
		$sp->mark->SetCallbackYX($callback);
	
	$sp->mark->SetType(MARK_FILLEDCIRCLE);
	
	return $sp;
}

/*
Format functions for scatterplot to format based on team and whether amp was taken
*/
function FCallbackRedAmp($bVal,$aVal) {
	return FCallbackAmp($aVal,0);
}

function FCallbackBlueAmp($bVal,$aVal) {
	return FCallbackAmp($aVal,1);
}

function FCallbackAmp($aVal, $team) {
	global $ampTimes;
	global $domimage_color;
	
	$size = 1;
	$color = $domimage_color['team'][$team][0];
		
	foreach ($ampTimes[$team] as $ampTaken) {
	
		if($aVal > $ampTaken[0] && $aVal < $ampTaken[1]) {
		
			$color = $domimage_color['amp'];
			$size = 3;
			break;
		}			
	}	
	
    return array($size,$color,$color);
}

/*
Helper function to sort array on key, based on solution from the interwebs
*/
function array_sort($array, $on) {
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        asort($sortable_array);            

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}
?>