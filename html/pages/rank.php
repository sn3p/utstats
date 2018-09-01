<?php
$sql_rgame = "SELECT DISTINCT(p.gid), g.name FROM uts_player AS p, uts_games AS g WHERE p.gid = g.id ORDER BY g.name ASC";
$q_rgame = mysqli_query($GLOBALS["___mysqli_link"], $sql_rgame) or die(mysqli_error($GLOBALS["___mysqli_link"]));

while ($r_rgame = mysqli_fetch_array($q_rgame)) {

    echo'
    <table class="box zebra" border="0" cellpadding="0" cellspacing="0" width="700">
	    <tbody>
	    <tr>
		    <th class="heading" colspan="4" align="center">Top 10 '.$r_rgame['name'].' Players</th>
	    </tr>
	    <tr>
		    <th align="center" width="50">N&deg;</th>
		    <th align="center" width="150">Player Name</th>
		    <th align="center" width="75">Rank</th>
		    <th align="center" width="75">Matches</th>
	    </tr>
    ';

  $ranking = 0;

  $sql_rplayer = "SELECT pi.id AS pid, pi.name, pi.country, r.rank, r.prevrank, r.matches FROM uts_rank AS r, uts_pinfo AS pi WHERE r.pid = pi.id AND r.gid =  '$r_rgame[gid]' AND pi.banned <> 'Y' ORDER BY r.rank DESC LIMIT 0,10";
  $q_rplayer = mysqli_query($GLOBALS["___mysqli_link"], $sql_rplayer) or die(mysqli_error($GLOBALS["___mysqli_link"]));

  if (mysqli_num_rows($q_rplayer) == 0) {
    echo '
    <tr>
	    <th align="center" colspan = "4">No players entered the rankings yet.</th>
    </tr>
    <tr>
	    <th align="center" colspan="4">&nbsp;</th>
    </tr>
    </tbody></table>
		<br>';
  }
  else {
    while ($r_rplayer = mysqli_fetch_array($q_rplayer)) {

      $ranking++;
      $myurl = urlencode($r_rplayer[name]);

      echo'
      <tr class="clickableRow" href="./?p=pinfo&amp;pid='.$r_rplayer[pid].'">
	      <td align="center">'.$ranking.'</td>
	      <td nowrap align="left">
					<a href="./?p=pinfo&amp;pid='.$r_rplayer[pid].'">'.FlagImage($r_rplayer[country]).' '.htmlspecialchars($r_rplayer[name], ENT_QUOTES) .' '. RankMovement($r_rplayer['rank'] - $r_rplayer['prevrank']).'</a>
				</td>
	      <td align="center">'.get_dp($r_rplayer[rank]).'</td>
	      <td align="center">'.$r_rplayer[matches].'</td>
      </tr>';
    }
    echo'
    <tr>
	    <td class="totals" align="center" colspan="4">
				<a href="./?p=ext_rank&amp;gid='.$r_rgame[gid].'">Click here to see all the rankings</a>
			</td>
    </tr>
    </tbody></table>
		<br>';
  }
}
?>
