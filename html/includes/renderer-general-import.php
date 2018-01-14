<?php

/*
Retrieve player names & player teams
Pretty much utstats code re-used
@return $playernames, $playerteams (arrays based on pid)
*/
function getPlayerTeam() {
	global $matchid;

	$playernames = array();
	$playerteams = array();

	$uid = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $uid);

	// Get List of Player IDs and Process What They Have Done
	$sql_player = "SELECT p.playerid,p.team,i.name FROM uts_player p JOIN uts_pinfo i ON p.pid = i.id WHERE p.matchid=$matchid";
	$q_player = mysqli_query($GLOBALS["___mysqli_link"], $sql_player) or die(mysqli_error($GLOBALS["___mysqli_link"]));

	while ($r_player = mysqli_fetch_array($q_player)) {
		$playerid = $r_player['playerid'];
		$playername = $r_player['name'];
		$playerteam = $r_player['team'];

		$playernames[$playerid] = $playername;
		$playerteams[$playerid] = $playerteam;
	}

	$r_team = small_query("SELECT COUNT(DISTINCT team) FROM uts_player WHERE matchid=$matchid");
	$teams = $r_team[0];

	return array($playernames,$playerteams,$teams);
}

/*
Get time game starts, game ends, and ratio compared to real time
@return $time_gamestart, $time_gameend, $time_ratio_correction (difference in ut time & real time, typically 110%)
*/
function getGameStartEndRatio($uid) {
	// gather game start & end time
	$result = mysqli_query($GLOBALS["___mysqli_link"], "SELECT col0 FROM uts_temp_$uid WHERE col1='game_start' OR col1='game_end' ORDER BY id ASC")or die(mysqli_error($GLOBALS["___mysqli_link"]));

	$time_gamestart = mysqli_fetch_row($result)[0];
	$time_gameend = mysqli_fetch_row($result)[0];

	//$time_ratio_correction = ($time_gameend-$time_gamestart)/1200;
	$time_ratio_correction = TIMERATIO; // based on hardcore mode

	return array($time_gamestart, $time_gameend, $time_ratio_correction);
}

function generateTeamLabels() {
	return array('Red Team', 'Blue Team', 'Green Team', 'Gold Team');
}

/*
Generate the chart with pickups
*/
function renderDataPickups($uid,$team=true,$playerRedWins=true,$topFraggers) {
	global $matchid;
	global $renderer_color;
	global $renderer_folder;
	global $renderer_width;
	global $renderer_heigth;

	$uid = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $uid);

	$q_pickups = mysqli_query($GLOBALS["___mysqli_link"], "SELECT SUM(pu_belt), SUM(pu_keg), SUM(pu_pads), SUM(pu_armour), SUM(pu_amp) FROM uts_player as p WHERE matchid = ".mysqli_real_escape_string($GLOBALS["___mysqli_link"], $matchid)." GROUP BY team") or die(mysqli_error($GLOBALS["___mysqli_link"]));

	while($r_pickups = mysqli_fetch_row($q_pickups)) {
		$preData[] = $r_pickups;
	}

	$pickupitems = array('belt','keg','pads','armour','amp');
	$itemsPickedUp = array();

	if($team || $playerRedWins) {
		$teamOneId = 0;
		$teamTwoId = 1;
	} else {
		$teamOneId = 1;
		$teamTwoId = 0;
	}

	// Process data to convert these to percentages
	// Normal numbers don't plot nicely (fe. pads getting much higher pickups due to lower spawn time
	for($i=0;$i<count($pickupitems);$i++) {
		if($preData[0][$i]>0) {
			$percValue = round($preData[0][$i]/($preData[0][$i]+$preData[1][$i])*100,0);

			$data[$teamOneId][] = $percValue;
			$data[$teamTwoId][] = 100-$percValue;
			$itemsPickedUp[] = $pickupitems[$i];

		} else if($preData[1][$i]>0) {
			$data[$teamOneId][] = 0;
			$data[$teamTwoId][] = 100;
			$itemsPickedUp[] = $pickupitems[$i];
		}
	}

	if(count($itemsPickedUp)>2) {
		if($team)
			$labels = generateTeamLabels();
		else
			$labels = generateLabelsFraggers($topFraggers);

		$charttype = $team?RENDERER_CHART_ITEMS_TEAMPICKUPS:RENDERER_CHART_ITEMS_PLAYERPICKUPS;

		// Save team score over team for teams
		mysqli_query($GLOBALS["___mysqli_link"], "INSERT INTO uts_chartdata (mid,chartid,data,labels,categories) VALUES (".$matchid.", ".$charttype.",
			'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($data)))."',
			'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($labels)))."',
			'".mysqli_real_escape_string($GLOBALS["___mysqli_link"], gzencode(serialize($itemsPickedUp)))."')") or die(mysqli_error($GLOBALS["___mysqli_link"]));
	}
}

/*
Generate labels for the fraggers
*/
function generateLabelsFraggers($topFraggers) {
	global $playernames;

	$labels = array();

	foreach($topFraggers as $fragger) {
		$labels[] = substr($playernames[$fragger],0,18);
	}

	return $labels;
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
