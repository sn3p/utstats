<?php
	$sql_killmatrix = "SELECT 	col2 AS killer, 
										col4 AS killed, 
										COUNT(*) AS kills 
							FROM 		uts_temp_$uid 
							WHERE 	col1 = 'kill' 
								OR		col1 = 'teamkill'
							GROUP BY col2, col4";
						
	$q_killmatrix = mysqli_query($GLOBALS["___mysqli_link"], $sql_killmatrix) or die(mysqli_error($GLOBALS["___mysqli_link"]));
	while ($r_killmatrix = mysqli_fetch_array($q_killmatrix)) {
		
		$sql =	"	INSERT
						INTO		uts_killsmatrix
						SET		matchid 	=	'$matchid',
									killer	=	'". $r_killmatrix['killer'] ."',
									victim	=	'". $r_killmatrix['killed'] ."',
									kills		=	'". $r_killmatrix['kills'] ."';";
		mysqli_query($GLOBALS["___mysqli_link"], $sql) or die(mysqli_error($GLOBALS["___mysqli_link"]));

	}
?>
