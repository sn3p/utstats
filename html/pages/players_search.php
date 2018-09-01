<?php

function InvertSort($curr_field, $filter, $sort) {
	if ($curr_field != $filter) return(($curr_field == "name") ? "ASC" : "DESC");
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
$playername = my_stripslashes($_REQUEST[name]);
$playersearch = my_addslashes($_REQUEST[name]);
$filter = my_addslashes($_GET[filter]);
$sort = my_addslashes($_GET[sort]);

IF (empty($filter) or (!in_array(strtolower($filter), array("name", "games", "gamescore", "frags", "kills", "deaths", "suicides", "eff", "accuracy", "ttl", "gametime")))) {
	$filter = "name";
}

if (empty($sort) or ($sort != 'ASC' and $sort != 'DESC')) $sort = ($filter == "name") ? "ASC" : "DESC";

echo'




<form NAME="playersearch" METHOD="post" ACTION="./?p=psearch">

<table class="zebra box" border="0" cellpadding="0" cellspacing="0">
  <tbody><tr>
    <th class="heading" colspan="11" align="center">Player Search List</td>
  </th>
  <tr>
    <th class ="smheading" colspan = "12" ALIGN="center">
      <div class="darksearch">
	  <span><input type="text" class="search square" placeholder="Search player..." name="name" value="'.htmlentities($playername).'"><input class="searchbutton" type="submit" value="Search"></span></div>
	  </th>

  </tr>
  <tr>
    <td class="smheading" align="center" width="150"><a class="smheading" href="./?p=psearch&amp;name='.urlencode($playername).'&amp;filter=name&amp;sort='.InvertSort('name', $filter, $sort).'">Player Name</a>'.SortPic('name', $filter, $sort).'</td>
    <td class="smheading" align="center" width="45"><a class="smheading" href="./?p=psearch&amp;name='.urlencode($playername).'&amp;filter=games&amp;sort='.InvertSort('games', $filter, $sort).'">Matches</a>'.SortPic('games', $filter, $sort).'</td>
    <td class="smheading" align="center" width="50"><a class="smheading" href="./?p=psearch&amp;name='.urlencode($playername).'&amp;filter=gamescore&amp;sort='.InvertSort('gamescore', $filter, $sort).'">Score</a>'.SortPic('gamescore', $filter, $sort).'</td>
    <td class="smheading" align="center" width="50"><a class="smheading" href="./?p=psearch&amp;name='.urlencode($playername).'&amp;filter=frags&amp;sort='.InvertSort('frags', $filter, $sort).'">Frags</a>'.SortPic('frags', $filter, $sort).'</td>
    <td class="smheading" align="center" width="50"><a class="smheading" href="./?p=psearch&amp;name='.urlencode($playername).'&amp;filter=kills&amp;sort='.InvertSort('kills', $filter, $sort).'">Kills</a>'.SortPic('kills', $filter, $sort).'</td>
    <td class="smheading" align="center" width="50"><a class="smheading" href="./?p=psearch&amp;name='.urlencode($playername).'&amp;filter=deaths&amp;sort='.InvertSort('deaths', $filter, $sort).'">Deaths</a>'.SortPic('deaths', $filter, $sort).'</td>
    <td class="smheading" align="center" width="50"><a class="smheading" href="./?p=psearch&amp;name='.urlencode($playername).'&amp;filter=suicides&amp;sort='.InvertSort('suicides', $filter, $sort).'">Suicides</a>'.SortPic('suicides', $filter, $sort).'</td>
    <td class="smheading" align="center" width="45"><a class="smheading" href="./?p=psearch&amp;name='.urlencode($playername).'&amp;filter=eff&amp;sort='.InvertSort('eff', $filter, $sort).'">Eff.</a>'.SortPic('eff', $filter, $sort).'</td>
    <td class="smheading" align="center" width="45"><a class="smheading" href="./?p=psearch&amp;name='.urlencode($playername).'&amp;filter=accuracy&amp;sort='.InvertSort('accuracy', $filter, $sort).'">Acc.</a>'.SortPic('accuracy', $filter, $sort).'</td>
    <td class="smheading" align="center" width="45"><a class="smheading" href="./?p=psearch&amp;name='.urlencode($playername).'&amp;filter=ttl&amp;sort='.InvertSort('ttl', $filter, $sort).'">TTL</a>'.SortPic('ttl', $filter, $sort).'</td>
    <td class="smheading" align="center" width="45"><a class="smheading" href="./?p=psearch&amp;name='.urlencode($playername).'&amp;filter=gametime&amp;sort='.InvertSort('gametime', $filter, $sort).'">Hours</a>'.SortPic('gametime', $filter, $sort).'</td>
  </tr>';

$sql_plist = "SELECT pi.name AS name, pi.country AS country, p.pid, COUNT(p.id) AS games, SUM(p.gamescore) as gamescore, SUM(p.frags) AS frags, SUM(p.kills) AS kills,
SUM(p.deaths) AS deaths, SUM(p.suicides) as suicides, AVG(p.eff) AS eff, AVG(p.accuracy) AS accuracy, AVG(p.ttl) AS ttl, SUM(gametime) as gametime
FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND pi.name LIKE '%".$playersearch."%' AND pi.banned <> 'Y' GROUP BY name ORDER BY $filter $sort";

$q_plist = mysqli_query($GLOBALS["___mysqli_link"], $sql_plist) or die(mysqli_error($GLOBALS["___mysqli_link"]));
while ($r_plist = mysqli_fetch_array($q_plist)) {

	  $gametime = sec2hour($r_plist[gametime]);
	  $eff = get_dp($r_plist[eff]);
	  $acc = get_dp($r_plist[accuracy]);
	  $ttl = GetMinutes($r_plist[ttl]);

	  echo'
	  <tr class="clickableRow" href="./?p=pinfo&amp;pid='.$r_plist['pid'].'">
		<td nowrap   align="left"><a href="./?p=pinfo&amp;pid='.$r_plist['pid'].'">'.FormatPlayerName($r_plist[country], $r_plist['pid'], $r_plist[name]).'</a></td>
		<td align="center">'.$r_plist[games].'</td>
		<td align="center">'.$r_plist[gamescore].'</td>
		<td align="center">'.$r_plist[frags].'</td>
		<td align="center">'.$r_plist[kills].'</td>
		<td align="center">'.$r_plist[deaths].'</td>
		<td align="center">'.$r_plist[suicides].'</td>
		<td align="center">'.$eff.'</td>
		<td align="center">'.$acc.'</td>
		<td align="center">'.$ttl.'</td>
		<td align="center">'.$gametime.'</td>
	  </tr>';
}
echo'
</tbody></table></form>';
?>
