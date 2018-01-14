
<?php	
	include_once('includes/renderer-dm.php');

		
	// Only run script if at least 5 minutes were played
	if(($time_gameend-$time_gamestart)>300 && count(array_unique($playernames))>=2) {

		try {
			$safe_uid = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $uid);
			
			// Get relevant data
			list($datafrags, $derivfrags, $topFraggers,$counter,$datafragsteam,$derivfragsteam,$topTeams) = parseDMdata($safe_uid);
			
			if($playernumberofteams > 1 && count(array_unique($playernames)) > 3 &&($gamename == "Tournament Team Game" || $gamename == "Tournament Team Game (insta)" || $gamename == "2v2v2v2 iTDM"))
				renderFragBarsTeams($datafragsteam,$derivfragsteam,$topTeams,$counter);
				
			renderFragBars($datafrags, $derivfrags, $topFraggers,$counter);
						
			if(count($playernames) == 2)			
				renderDataPickups($safe_uid,false,($playerteams[$topFraggers[0]]==0),$topFraggers);
			else if($playernumberofteams == 2)
				renderDataPickups($safe_uid);			
			
		} catch (Exception $e) {
			
			// Do nothing, but simply do not block import

		}
	}
?>