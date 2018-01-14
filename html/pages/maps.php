<?php
function InvertSort($curr_field, $filter, $sort) {
	if ($curr_field != $filter) return(($curr_field == "mapfile") ? "ASC" : "DESC");
	if ($sort == 'ASC') return('DESC');
	return('ASC');
}

function SortPic($curr_field, $filter, $sort) {
	if ($curr_field != $filter) return;
	$fname = 'assets/images/s_'. strtolower($sort) .'.png';
	if (!file_exists($fname)) return;
	return('&nbsp;<img src="'. $fname .'" border="0" width="11" height="9" alt="" class="tooltip" title="('.strtolower($sort).'ending)">');
}

// Get filter and set sorting
$filter = my_addslashes($_GET[filter]);
$sort = my_addslashes($_GET[sort]);
$q = my_addslashes($_GET[q]);
$gid = preg_replace('/\D/', '', $_GET[gid]);

if (empty($filter) or (!in_array(strtolower($filter), array("mapfile", "matchcount", "frags", "matchscore", "gametime")))) {
	$filter = "mapfile";
}

if (empty($sort) or ($sort != 'ASC' and $sort != 'DESC')) $sort = ($filter == "mapfile") ? "ASC" : "DESC";

if (isset($gid)) {
	if ($gid != 0) {
		$url_condition .= "&amp;gid=".urlencode($gid);
		$sql_condition = " WHERE gid = $gid";
	}
}
else {
	$gid = 0;
}

if (isset($q)) {
	if ($gid != 0) {
		$sql_condition .= ' AND mapfile LIKE "%' . $q . '%" ';
	}
	else {
		$sql_condition .= ' WHERE mapfile LIKE "%' . $q . '%" ';
	}
	$url_condition .= "&amp;q=".urlencode($q);
}

// Firstly we need to work out First Last Next Prev pages
$mcount = small_count("SELECT mapfile FROM uts_match" . $sql_condition . " GROUP BY mapfile");
$ecount = $mcount / 25;
$ecount2 = number_format($ecount, 0, '.', '');

if ($ecount > $ecount2) {
	$ecount2 = $ecount2+1;
}

$fpage = 0;
if ($ecount < 1) { $lpage = 0; }
else { $lpage = $ecount2-1; }

$cpage = preg_replace('/\D/', '', $_GET["page"]);
if ($cpage == "") { $cpage = "0"; }
$qpage = $cpage*25;

$tfpage = $cpage+1;
$tlpage = $lpage+1;

$ppage = $cpage-1;
$ppageurl = "<a class=\"pages\" href=\"./?p=maps&amp;filter=$filter&amp;sort=$sort&amp;page=$ppage".$url_condition."\">[Previous]</a>";
if ($ppage < "0") { $ppageurl = "[Previous]"; }

$npage = $cpage+1;
$npageurl = "<a class=\"pages\" href=\"./?p=maps&amp;filter=$filter&amp;sort=$sort&amp;page=$npage".$url_condition."\">[Next]</a>";
if ($npage >= "$ecount") { $npageurl = "[Next]"; }

$fpageurl = "<a class=\"pages\" href=\"./?p=maps&amp;filter=$filter&amp;sort=$sort&amp;page=$fpage".$url_condition."\">[First]</a>";
if ($cpage == "0") { $fpageurl = "[First]"; }

$lpageurl = "<a class=\"pages\" href=\"./?p=maps&amp;filter=$filter&amp;sort=$sort&amp;page=$lpage".$url_condition."\">[Last]</a>";
if ($cpage == "$lpage") { $lpageurl = "[Last]"; }

echo'
<form NAME="mapfilter" METHOD="get" ACTION="">
<div class="pages">Page ['.$tfpage.'/'.$tlpage.'] Selection: '.$fpageurl.' / '.$ppageurl.' / '.$npageurl.' / '.$lpageurl.'</div>
<table class="zebra box" border="0" cellpadding="0" cellspacing="0">
  <tbody><tr>
    <th class="heading" colspan="5" align="center">Unreal Tournament Maps List</th>
  </tr>
  <tr>
    <th class="smheading" align="center" width="100%" colspan="5">
			<input type = "hidden" name = "p" value = "maps">
			<input type = "hidden" name = "sort" value = "'.$sort.'">
			<input type = "hidden" name = "filter" value = "'.$filter.'">
			Filter:
			<select class="searchform" name="gid">
				<option value="0">*</option>';

				$sql_game = "SELECT DISTINCT(p.gid), g.name FROM uts_player AS p, uts_games AS g WHERE p.gid = g.id ORDER BY g.name ASC";
				$q_game = mysqli_query($GLOBALS["___mysqli_link"], $sql_game) or die(mysqli_error($GLOBALS["___mysqli_link"]));
				while ($r_game = mysqli_fetch_array($q_game)) {
					$selected = ($r_game['gid'] == $gid) ? 'selected' : '';
					echo '<option '.$selected.' value="'.$r_game['gid'].'">'. $r_game['name'] .'</option>';
				}

echo '</select>
			<div class="darksearch">
			 <span>
				 	<input type="text" class="search square" placeholder="Search maps..." name="q" value="'.htmlentities($q).'">
					<input class="searchbutton" type="submit" value="Search">
				</span>
			</div>
	  </th>
  </tr>
  <tr>
    <th class="smheading" align="center" width="250"><a class="smheading" href="./?p=maps&amp;filter=mapfile&amp;sort='.InvertSort('mapfile', $filter, $sort).$url_condition.'">Map Name</a>'.SortPic('mapfile', $filter, $sort).'</th>
    <th class="smheading" align="center" width="150"><a class="smheading" href="./?p=maps&amp;filter=matchcount&amp;sort='.InvertSort('matchcount', $filter, $sort).$url_condition.'">Matches</a>'.SortPic('matchcount', $filter, $sort).'</th>
    <th class="smheading" align="center"><a class="smheading" href="./?p=maps&amp;filter=frags&amp;sort='.InvertSort('frags', $filter, $sort).$url_condition.'">Avg. Frags</a>'.SortPic('frags', $filter, $sort).'</th>
    <th class="smheading" align="center" width="100"><a class="smheading" href="./?p=maps&amp;filter=matchscore&amp;sort='.InvertSort('matchscore', $filter, $sort).$url_condition.'">Avg. Score</a>'.SortPic('matchscore', $filter, $sort).'</th>
    <th class="smheading" align="center" width="100"><a class="smheading" href="./?p=maps&amp;filter=gametime&amp;sort='.InvertSort('gametime', $filter, $sort).$url_condition.'">Time</a>'.SortPic('gametime', $filter, $sort).'</th>
  </tr>';

$sql_maps = "SELECT IF(RIGHT(mapfile,4) LIKE '.unr', mapfile, CONCAT(mapfile, '.unr')) as mapfile, COUNT(id) AS matchcount, AVG(frags) AS frags, AVG(t0score+t1score+t2score+t3score) AS matchscore, SUM(gametime) AS gametime
	FROM uts_match" . $sql_condition . " GROUP BY mapfile ORDER BY $filter $sort LIMIT $qpage,25";
$q_maps = mysqli_query($GLOBALS["___mysqli_link"], $sql_maps) or die(mysqli_error($GLOBALS["___mysqli_link"]));

while ($r_maps = mysqli_fetch_array($q_maps)) {
	  $r_mapfile = un_ut($r_maps[mapfile]);
	  $myurl = urlencode($r_mapfile);
	  $r_gametime = GetMinutes($r_maps[gametime]);

	  echo '
	  <tr class="clickableRow" href="./?p=minfo&amp;map='.$myurl.'">
			<td align="center"><a href="./?p=minfo&amp;map='.$myurl.'">'.$r_mapfile.'</a></td>
			<td align="center">'.$r_maps[matchcount].'</td>
			<td align="center">'.get_dp($r_maps[frags]).'</td>
			<td align="center">'.get_dp($r_maps[matchscore]).'</td>
			<td align="center">'.$r_gametime.'</td>
	  </tr>';
}

echo '
</tbody></table>
<div class="pages">Page ['.$tfpage.'/'.$tlpage.'] Selection: '.$fpageurl.' / '.$ppageurl.' / '.$npageurl.' / '.$lpageurl.'</div>
</form>';
?>
