<?php
	$sql_killmatrix = "SELECT 	col2 AS killer, 
										col4 AS killed, 
										COUNT(*) AS kills 
							FROM 		uts_temp_$uid 
							WHERE 	col1 = 'kill' 
								OR		col1 = 'teamkill'
							GROUP BY col2, col4";
						
	$q_killmatrix = mysql_query($sql_killmatrix) or die(mysql_error());
	while ($r_killmatrix = mysql_fetch_array($q_killmatrix)) {
		
		$sql =	"	INSERT
						INTO		uts_killsmatrix
						SET		matchid 	=	'$matchid',
									killer	=	'". $r_killmatrix['killer'] ."',
									victim	=	'". $r_killmatrix['killed'] ."',
									kills		=	'". $r_killmatrix['kills'] ."';";
		mysql_query($sql) or die(mysql_error());

	}
?>
