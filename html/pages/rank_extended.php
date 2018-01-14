<?php
$gid = my_addslashes($_GET['gid']);

$r_gamename = small_query("SELECT name FROM uts_games WHERE id = '$gid'");
$gamename = $r_gamename['name'];

$r_pcount = small_query("SELECT COUNT(*) as pcount FROM uts_rank WHERE gid= '$gid'");
$pcount = $r_pcount['pcount'];

$ecount = $pcount/25;
$ecount2 = number_format($ecount, 0, '.', '');

IF($ecount > $ecount2) {
	$ecount2 = $ecount2+1;
}

$fpage = 0;
IF($ecount < 1) { $lpage = 0; }
else { $lpage = $ecount2-1; }

$cpage = $_GET["page"];
IF ($cpage == "") { $cpage = "0"; }
$qpage = $cpage*25;

$tfpage = $cpage+1;
$tlpage = $lpage+1;

$ppage = $cpage-1;
$ppageurl = "<a class=\"pages\" href=\"./?p=ext_rank&amp;gid=$gid&amp;page=$ppage\">[Previous]</a>";
IF ($ppage < "0") { $ppageurl = "[Previous]"; }

$npage = $cpage+1;
$npageurl = "<a class=\"pages\" href=\"./?p=ext_rank&amp;gid=$gid&amp;page=$npage\">[Next]</a>";
IF ($npage >= "$ecount") { $npageurl = "[Next]"; }

$fpageurl = "<a class=\"pages\" href=\"./?p=ext_rank&amp;gid=$gid&amp;page=$fpage\">[First]</a>";
IF ($cpage == "0") { $fpageurl = "[First]"; }

$lpageurl = "<a class=\"pages\" href=\"./?p=ext_rank&amp;gid=$gid&amp;page=$lpage\">[Last]</a>";
IF ($cpage == "$lpage") { $lpageurl = "[Last]"; }

echo'
<div class="pages">Page ['.$tfpage.'/'.$tlpage.'] Selection: '.$fpageurl.' / '.$ppageurl.' / '.$npageurl.' / '.$lpageurl.'</div>
<table class="zebra box" border="0" cellpadding="0" cellspacing="0" width="700">
  <tbody>
  <tr>
	<th class="heading" colspan="4" align="center">'.$gamename.' Ranking Players</th>
  </tr>
  <tr>
	<th class="smheading" align="center" width="75">N&deg;</th>
	<th class="smheading" align="center" width="150">Player Name</th>
	<th class="smheading" align="center" width="75">Rank</th>
	<th class="smheading" align="center" width="75">Matches</th>
  </tr>';

	$ranking = $qpage;

	$sql_rplayer = "SELECT pi.name, pi.country, r.rank, r.prevrank, r.matches, r.pid FROM uts_rank AS r, uts_pinfo AS pi WHERE r.pid = pi.id AND r.gid = '$gid' AND pi.banned <> 'Y' ORDER BY rank DESC LIMIT $qpage,25";
	$q_rplayer = mysqli_query($GLOBALS["___mysqli_link"], $sql_rplayer) or die(mysqli_error($GLOBALS["___mysqli_link"]));
	while ($r_rplayer = mysqli_fetch_array($q_rplayer)) {

		$ranking++;
	  echo'
	  <tr class="clickableRow" href="./?p=pinfo&amp;pid='.$r_rplayer['pid'].'">
		<td   align="center">'.$ranking.'</td>
		<td nowrap   align="left"><a href="./?p=pinfo&amp;pid='.$r_rplayer['pid'].'">'.FlagImage($r_rplayer[country]).' '.htmlspecialchars($r_rplayer[name]) .' '. RankMovement($r_rplayer['rank'] - $r_rplayer['prevrank']) .'</a></td>
		<td   align="center">'.get_dp($r_rplayer[rank]).'</td>
		<td   align="center">'.$r_rplayer[matches].'</td>
	  </tr>';

}
echo'
</tbody></table>
<div class="pages">Page ['.$tfpage.'/'.$tlpage.'] Selection: '.$fpageurl.' / '.$ppageurl.' / '.$npageurl.' / '.$lpageurl.'</div>';
?>