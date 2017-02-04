<?php
if (empty($import_adminkey) or isset($_REQUEST['import_adminkey']) or $import_adminkey != $adminkey) die('bla');
	
$options['title'] = 'Recalculate Rankings';
$options['requireconfirmation'] = false;
$i = 0;
$options['vars'][$i]['name'] = 'start';
$options['vars'][$i]['type'] = 'static';
$options['vars'][$i]['options'] = 'No|Yes';
$options['vars'][$i]['exitif'] = 'No';
$options['vars'][$i]['prompt'] = 'Are you sure';
$options['vars'][$i]['caption'] = 'Sure:';
$i++;

$results = adminselect($options);

if ($results['start'] != 'Yes') {
	include('pages/admin/main.php');
	exit;
}
@ignore_user_abort(true);
@set_time_limit(0);


echo'<br><table border="0" cellpadding="1" cellspacing="2" width="600">
<tr>
	<td class="smheading" align="center" colspan="2">Recalculating Rankings</td>
</tr>';

echo'<tr>
	<td class="smheading" align="left" width="200">Deleting rankings</td>';

	mysql_query("TRUNCATE uts_rank") or die(mysql_error());	

	echo'<td class="grey" align="left" width="400">Done</td>';

echo'</tr>
<tr>
	<td class="smheading" align="left">Recalculating Rankings:</td>';
	echo'<td class="grey" align="left">';
	$playerbanned = false;
	$q_pm = mysql_query(	"	SELECT 	p.id, 
												p.matchid, 
												p.pid, 
												p.gid,
												m.gamename
									FROM 		uts_player p, 
												uts_pinfo pi,
												uts_match m
									WHERE 	pi.id = p.pid 
										AND 	pi.banned <> 'Y' 
										AND	m.id = p.matchid
									ORDER BY p.matchid ASC, 
												p.playerid ASC");
	$i = 0;
	while ($r_pm = mysql_fetch_array($q_pm)) {
		$i++;
		if ($i%50 == 0) {
			echo '. ';
			flush();
		}
		$playerecordid = $r_pm['id'];
		$matchid = $r_pm['matchid'];
		$pid = $r_pm['pid'];
		$gid = $r_pm['gid'];
		$gamename = $r_pm['gamename'];
//		echo "$pid|$gid|$matchid<br>";
		include('import/import_ranking.php');
	}
	echo 'Done</td>
</tr>

<tr>
	<td class="smheading" align="center" colspan="2">Rankings recalculated - <a href="./admin.php?key='.$_REQUEST[key].'">Go Back To Admin Page</a></td>
</tr></table>';

?>
