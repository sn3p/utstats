<?php
$map = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $_GET[map]);
$bugmap = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $_GET[map]);
$realmap = $bugmap.".unr";

$map_matches = small_query("SELECT COUNT(id) as matchcount, SUM(t0score+t1score+t2score+t3score) AS gamescore,
SUM(gametime) AS gametime, SUM(kills) AS kills, SUM(deaths) AS deaths, SUM(suicides) AS suicides, SUM(teamkills) AS teamkills FROM uts_match WHERE mapfile = '$realmap' OR mapfile = '$bugmap'");
$map_last = small_query("SELECT time FROM uts_match WHERE mapfile = '$realmap' OR mapfile = '$bugmap' ORDER BY time DESC LIMIT 0,1");

$map_tottime = GetMinutes($map_matches[gametime]);
$map_lastmatch = mdate($map_last[time]);

if($map_matches[gametime]<= 0) {

	echo "map not found";

} else {

	// Map pic code
	$mappic = getMapImageName($map);

	echo '
	<table class="zebra box" border="0" cellpadding="0" cellspacing="0" width="700">
  <tbody>
		<tr>
			<th class="heading" align="center" colspan="4">Statistics for '.htmlentities($map).'</th>
	  </tr>
		<tr>
			<th colspan="4" class="mapheader">
				<img border="0" alt="'.$map.'" src="'.$mappic.'" width=698>
			</th>
		</tr>
	  <tr>
			<th align="center">Matches</th>
			<td align="center">'.$map_matches[matchcount].'</td>
	  </tr>
	  <tr>
			<th align="center">Total Time</th>
			<td align="center">'.$map_tottime.' minutes</td>
	  </tr>';
	// Show some gametype specific stuff
	if ((strtolower(substr($map, 0, 7)) == "ctf-bt-") or (strtolower(substr($map, 0, 3)) == "bt-")) {
		// Bunny Track
		$record = small_query("SELECT pi.id, pi.name AS name, pi.country, e.col3 AS time, e.col4 AS date FROM uts_events AS e, uts_pinfo AS pi, uts_player AS p, uts_match AS m WHERE m.id = e.matchid AND m.id = p.matchid AND p.playerid = e.playerid AND pi.id = p.pid AND (m.mapfile = '$realmap' OR m.mapfile = '$bugmap') AND e.col1 = 'btcap' GROUP BY pi.id ORDER BY (0 + e.col3) ASC, e.col4 ASC LIMIT 0,1");
		if (!empty($record['time'])) {
			echo '
			  <tr>
					<th align="center">Fastest Capture</th>
					<td align="center"><a href="?p=pinfo&amp;pid='.$record[id].'">'.FormatPlayerName($record['country'], $record['id'], $record['name']).'</a><br>' . btcaptime($record['time']) . ' minutes<BR>'.gmdate('d-m-Y h:i a', $record['date']).'</td>
			  </tr>';
		}
		else {
			echo '
			  <tr>
					<th align="center">Fastest Capture</th>
					<td align="center">No record set!</td>
			  </tr>';
		}
		echo '
			  <tr>
					<th align="center">Total Flags Captured</th>
					<td align="center">'.$map_matches[gamescore].'</td>
			  </tr>
			  <tr>
					<th align="center">Total Suicides</th>
					<td align="center">'.$map_matches[suicides].'</td>
			  </tr>';
	}
	else if (strtolower(substr($map, 0, 4)) == "ctf-") {
		// Capture the Flag
		$totals = small_query("SELECT SUM(p.flag_taken) as flag_taken, SUM(p.flag_return) AS flag_return, SUM(p.flag_cover) AS flag_cover FROM uts_player AS p, uts_match AS m WHERE m.id = p.matchid AND (m.mapfile = '$realmap' OR m.mapfile = '$bugmap')");
		echo '
			  <tr>
					<th align="center">Total Flags Captured</th>
					<td align="center">'.$map_matches[gamescore].'</td>
			  </tr>
			  <tr>
					<th align="center">Total Flags Taken</th>
					<td align="center">'.$totals['flag_taken'].'</td>
			  </tr>
			  <tr>
					<th align="center">Total Covers</th>
					<td align="center">'.$totals['flag_cover'].'</td>
			  </tr>
			  <tr>
					<th align="center">Total Flags Returned</th>
					<td align="center">'.$totals['flag_return'].'</td>
			  </tr>
			  <tr>
					<th align="center">Total Kills</th>
					<td align="center">'.$map_matches[kills].'</td>
			  </tr>
			  <tr>
					<th align="center">Total Suicides</th>
					<td align="center">'.$map_matches[suicides].'</td>
			  </tr>';
	}
	else if (strtolower(substr($map, 0, 3)) == "as-") {
		// Assault
		$totals = small_query("SELECT SUM(p.ass_obj) as  ass_obj FROM uts_player AS p, uts_match AS m WHERE m.id = p.matchid AND (m.mapfile = '$realmap' OR m.mapfile = '$bugmap')");
		echo '
			  <tr>
					<th align="center">Total Objectives Achieved</th>
					<td align="center">'.$totals[ass_obj].'</td>
			  </tr>
			  <tr>
					<th align="center">Total Kills</th>
					<td align="center">'.$map_matches[kills].'</td>
			  </tr>
			  <tr>
					<th align="center">Total Suicides</th>
					<td align="center">'.$map_matches[suicides].'</td>
			  </tr>';
	}
	else if (strtolower(substr($map, 0, 3)) == "jb-") {
		// Assault
		$totals = small_query("SELECT SUM(p.ass_obj) as  ass_obj FROM uts_player AS p, uts_match AS m WHERE m.id = p.matchid AND (m.mapfile = '$realmap' OR m.mapfile = '$bugmap')");
		echo '
			  <tr>
					<th align="center">Team Releases</th>
					<td align="center">'.$totals[ass_obj].'</td>
			  </tr>
			  <tr>
					<th align="center">Total Kills</th>
					<td align="center">'.$map_matches[kills].'</td>
			  </tr>
			  <tr>
					<th align="center">Total Suicides</th>
					<td align="center">'.$map_matches[suicides].'</td>
			  </tr>';
	}
	else if (strtolower(substr($map, 0, 4)) == "dom-") {
		// Assault
		$totals = small_query("SELECT SUM(p.dom_cp) as  dom_cp FROM uts_player AS p, uts_match AS m WHERE m.id = p.matchid AND (m.mapfile = '$realmap' OR m.mapfile = '$bugmap')");
		echo '
			  <tr>
					<th align="center">Total Control Points Captured</th>
					<td align="center">'.$totals['dom_cp'].'</td>
			  </tr>
			  <tr>
					<th align="center">Total Kills</th>
					<td align="center">'.$map_matches[kills].'</td>
			  </tr>
			  <tr>
					<th align="center">Total Suicides</th>
					<td  align="center">'.$map_matches[suicides].'</td>
			  </tr>';
	}
	else {
		echo '
			  <tr>
					<th align="center">Total Score</th>
					<td align="center">'.$map_matches[gamescore].'</td>
			  </tr>
			  <tr>
					<th align="center">Total Kills</th>
					<td align="center">'.$map_matches[kills].'</td>
			  </tr>
			  <tr>
					<th align="center">Total Teamkills</th>
					<td align="center">'.$map_matches[teamkills].'</td>
			  </tr>
			  <tr>
					<th align="center">Total Suicides</th>
					<td align="center">'.$map_matches[suicides].'</td>
			  </tr>';
	}
	echo '
	  <tr>
			<th align="center">Last Match</th>
			<td align="center">'.$map_lastmatch.'</td>
	  </tr>
	</tbody></table>
	<br>';

	// Show a list of recent matches
	$mcount = small_count("SELECT id FROM uts_match WHERE mapfile = '$realmap' OR mapfile = '$bugmap' GROUP BY id");

	$ecount = $mcount/25;
	$ecount2 = number_format($ecount, 0, '.', '');

	if ($ecount > $ecount2) {
		$ecount2 = $ecount2+1;
	}

	$fpage = 0;
	if ($ecount < 1) { $lpage = 0; }
	else { $lpage = $ecount2-1; }

	$cpage = preg_replace('/\D/', '', $_GET["page"]);
	$qpage = $cpage*25;

	if ($cpage == "") { $cpage = "0"; }

	$tfpage = $cpage+1;
	$tlpage = $lpage+1;

	$ppage = $cpage-1;
	$ppageurl = "<a class=\"pages\" href=\"./?p=minfo&amp;map=".htmlentities($map)."&amp;page=$ppage\">[Previous]</a>";
	if ($ppage < "0") { $ppageurl = "[Previous]"; }

	$npage = $cpage+1;
	$npageurl = "<a class=\"pages\" href=\"./?p=minfo&amp;map=".htmlentities($map)."&amp;page=$npage\">[Next]</a>";
	if ($npage >= "$ecount") { $npageurl = "[Next]"; }

	$fpageurl = "<a class=\"pages\" href=\"./?p=minfo&amp;map=".htmlentities($map)."&amp;page=$fpage\">[First]</a>";
	if ($cpage == "0") { $fpageurl = "[First]"; }

	$lpageurl = "<a class=\"pages\" href=\"./?p=minfo&amp;map=".htmlentities($map)."&amp;page=$lpage\">[Last]</a>";
	if ($cpage == "$lpage") { $lpageurl = "[Last]"; }

	// list recent matches
	echo '
	<div class="pages">Page ['.$tfpage.'/'.$tlpage.'] Selection: '.$fpageurl.' / '.$ppageurl.' / '.$npageurl.' / '.$lpageurl.'</div>
	<table class="zebra box" border="0" cellpadding="0" cellspacing="0" width=700>
  <tbody>
		<tr>
			<th class="heading" colspan="5" align="center">Recent Matches</th>
	  </tr>
	  <tr>
			<th class="smheading" align="center" width="250">Date</th>
			<th class="smheading" align="center" width="100">Match Type</th>
			<th class="smheading" align="center">Player Count</th>
			<th class="smheading" align="center" width="100">Time</th>
	  </tr>';

	$sql_maps = "SELECT m.id, m.time, g.name AS gamename, m.gametime
		FROM uts_match AS m, uts_games AS g WHERE (m.mapfile = '$realmap' OR m.mapfile = '$bugmap') AND m.gid = g.id ORDER BY time DESC LIMIT $qpage,25";
	$q_maps = mysqli_query($GLOBALS["___mysqli_link"], $sql_maps) or die(mysqli_error($GLOBALS["___mysqli_link"]));

	while ($r_maps = mysqli_fetch_array($q_maps)) {
	  $r_mapfile = un_ut($r_maps[mapfile]);
	  $r_matchtime = mdate($r_maps[time]);
	  $r_gametime = GetMinutes($r_maps[gametime]);

	  $map_pcount = small_count("SELECT id FROM uts_player WHERE matchid = $r_maps[id]");

	  echo '
	  <tr class="clickableRow" href="./?p=match&amp;mid='.$r_maps[id].'">
			<td align="center"><a href="./?p=match&amp;mid='.$r_maps[id].'">'.$r_matchtime.'</a></td>
			<td align="center">'.$r_maps[gamename].'</td>
			<td align="center">'.$map_pcount.'</td>
			<td align="center">'.$r_gametime.'</td>
	  </tr>';
	}

	echo '
	</tbody>
	</table>
	<div class="pages">Page ['.$tfpage.'/'.$tlpage.'] Selection: '.$fpageurl.' / '.$ppageurl.' / '.$npageurl.' / '.$lpageurl.'</div>';

	// Do graph stuff
	$bgwhere = "(mapfile = '$realmap' or mapfile = '$bugmap')";
	include("pages/graph_mbreakdown.php");

	// Show a list of BT records
	if ((strtolower(substr($map, 0, 7)) == "ctf-bt-") or (strtolower(substr($map, 0, 3)) == "bt-")) {
		$mcount = small_count("SELECT pi.id FROM uts_events AS e, uts_pinfo AS pi, uts_player AS p, uts_match AS m WHERE m.id = e.matchid AND m.id = p.matchid AND p.playerid = e.playerid AND pi.id = p.pid AND (m.mapfile = '$realmap' OR m.mapfile = '$bugmap') GROUP BY pi.id");

		$ecount = $mcount/25;
		$ecount2 = number_format($ecount, 0, '.', '');

		if ($ecount > $ecount2) {
			$ecount2 = $ecount2+1;
		}

		$fpage = 0;
		if ($ecount < 1) { $lpage = 0; }
		else { $lpage = $ecount2-1; }

		$cpage = preg_replace('/\D/', '', $_GET["rpage"]);
		$qpage = $cpage*25;

		if ($cpage == "") { $cpage = "0"; }

		$tfpage = $cpage+1;
		$tlpage = $lpage+1;

		$ppage = $cpage-1;
		$ppageurl = "<a class=\"pages\" href=\"./?p=minfo&amp;map=".htmlentities($map)."&amp;rpage=$ppage\">[Previous]</a>";
		if ($ppage < "0") { $ppageurl = "[Previous]"; }

		$npage = $cpage+1;
		$npageurl = "<a class=\"pages\" href=\"./?p=minfo&amp;map=".htmlentities($map)."&amp;rpage=$npage\">[Next]</a>";
		if ($npage >= "$ecount") { $npageurl = "[Next]"; }

		$fpageurl = "<a class=\"pages\" href=\"./?p=minfo&amp;map=".htmlentities($map)."&amp;rpage=$fpage\">[First]</a>";
		if ($cpage == "0") { $fpageurl = "[First]"; }

		$lpageurl = "<a class=\"pages\" href=\"./?p=minfo&amp;map=".htmlentities($map)."&amp;rpage=$lpage\">[Last]</a>";
		if ($cpage == "$lpage") { $lpageurl = "[Last]"; }

		$sql_btrecords = "SELECT pi.id, pi.name AS name, pi.country, e.col3 AS time, e.col4 AS date FROM uts_events AS e, uts_pinfo AS pi, uts_player AS p, uts_match AS m WHERE m.id = e.matchid AND m.id = p.matchid AND p.playerid = e.playerid AND pi.id = p.pid AND (m.mapfile = '$realmap' OR m.mapfile = '$bugmap') AND e.col1 = 'btcap' GROUP BY pi.id ORDER BY (0 + e.col3) ASC, e.col4 ASC LIMIT $qpage,25";
		$q_btrecords = mysqli_query($GLOBALS["___mysqli_link"], $sql_btrecords) or die (mysqli_error($GLOBALS["___mysqli_link"]));

		if (mysqli_num_rows($q_btrecords) > 0) {
			echo '
			<div class="pages">Page ['.$tfpage.'/'.$tlpage.'] Selection: '.$fpageurl.' / '.$ppageurl.' / '.$npageurl.' / '.$lpageurl.'</div>
			<table class="zebra box" border="0" cellpadding="0" cellspacing="0" width="700">
		  <tbody>
			  <tr>
					<th class="heading" colspan="4" align="center">Fastest captures</th>
			  </tr>
			  <tr>
					<th class="smheading" align="center" width="40">N&deg;</th>
					<th class="smheading" align="center" width="200">Name</th>
					<th class="smheading" align="center" width="60">Time</th>
					<th class="smheading" align="center" width="180">Date</th>
				</tr>';

			$i = $qpage;
			$lasttime = -1;

			while ($r_btrecords = mysqli_fetch_array($q_btrecords)) {
				$i++;
				$class = ($i%2) ? 'grey' : 'grey2';
				echo '
					<tr><td class = "'.$class.'" align = "right">'.($lasttime == $r_btrecords['time'] ? '' : $i).'&nbsp;</td>
						<td class="'.$class.'" align="center"><a href="?p=pinfo&amp;pid='.$r_btrecords[id].'">', FormatPlayerName($r_btrecords['country'], $r_btrecords['id'], $r_btrecords['name']), '</a></td>
						<td class="'.$class.'" align="center">', btcaptime($r_btrecords['time']), '</td>
						<td class="'.$class.'" align="center">', gmdate('d-m-Y h:i a', $r_btrecords['date']), '</td></tr>';
				$lasttime = $r_btrecords['time'];
			}

			echo '</tbody>
				</table>
			<div class="pages">Page ['.$tfpage.'/'.$tlpage.'] Selection: '.$fpageurl.' / '.$ppageurl.' / '.$npageurl.' / '.$lpageurl.'</div><br>';
		}
	}
}

?>
