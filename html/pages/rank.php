<?php
$sql_rgame = "SELECT DISTINCT(p.gid), g.name FROM uts_player AS p, uts_games AS g WHERE p.gid = g.id ORDER BY g.name ASC";
$q_rgame = mysql_query($sql_rgame) or die(mysql_error());
while ($r_rgame = mysql_fetch_array($q_rgame)) {

	  echo'
	  <table class="box" border="0" cellpadding="1" cellspacing="1">
	  <tbody>
	  <tr>
		<td class="heading" colspan="4" align="center">Top 10 '.$r_rgame['name'].' Players</td>
	  </tr>
	  <tr>
		<td class="smheading" align="center" width="75">N&deg;</td>
		<td class="smheading" align="center" width="150">Player Name</td>
		<td class="smheading" align="center" width="75">Rank</td>
		<td class="smheadingx" align="center" width="75">Matches</td>
	  </tr>
	  ';

	$ranking = 0;

	$sql_rplayer = "SELECT pi.id AS pid, pi.name, pi.country, r.rank, r.prevrank, r.matches FROM uts_rank AS r, uts_pinfo AS pi WHERE r.pid = pi.id AND r.gid =  '$r_rgame[gid]' AND pi.banned <> 'Y' ORDER BY r.rank DESC LIMIT 0,10";
	$q_rplayer = mysql_query($sql_rplayer) or die(mysql_error());
	if (mysql_num_rows($q_rplayer) == 0) {
		echo '
		  <tr>
			<td class="grey" align="center" colspan = "4">No players entered the rankings yet.</td>
		  </tr>
		  <tr>
			<td class="smheading" align="center" colspan="4">&nbsp;</td>
		  </tr>
		  </tbody></table><br>';

	}
	else {
		while ($r_rplayer = mysql_fetch_array($q_rplayer)) {

			$ranking++;
			$myurl = urlencode($r_rplayer[name]);

		  echo'
		  <tr>
			<td class="grey" align="center">'.$ranking.'</td>
			<td nowrap class="dark" align="left"><a class="darkhuman" href="./?p=pinfo&amp;pid='.$r_rplayer[pid].'">'.FlagImage($r_rplayer[country]).' '.htmlspecialchars($r_rplayer[name], ENT_QUOTES) .' '. RankMovement($r_rplayer['rank'] - $r_rplayer['prevrank']).'</a></td>
			<td class="dark" align="center">'.get_dp($r_rplayer[rank]).'</td>
			<td class="grey" align="center">'.$r_rplayer[matches].'</td>
		  </tr>';
		}
		echo'
		  <tr>
			<td class="smheading" align="center" colspan="4"><a href="./?p=ext_rank&amp;gid='.$r_rgame[gid].'">Click Here To See All The Rankings</a></td>
		  </tr>
		  </tbody></table><br>';
	}
}
?>