<?php
// Firstly we need to work out First Last Next Prev pages
$where = ' ';
$year = !empty($_REQUEST['year']) ? my_addslashes(sprintf("%04d", $_REQUEST['year'])) : 0;
$month = !empty($_REQUEST['month']) ? my_addslashes(sprintf("%02d", $_REQUEST['month'])) : 0;
$day = !empty($_REQUEST['day']) ? my_addslashes(sprintf("%02d", $_REQUEST['day'])) : 0;
$gid  = !empty($_REQUEST['gid']) ?  my_addslashes($_REQUEST['gid']) : 0;

if (!empty($year) and empty($month) and empty($day)) $where .= " AND m.time LIKE '$year%'";
if (!empty($year) and !empty($month) and empty($day)) $where .= " AND m.time LIKE '$year$month%'";
if (!empty($year) and !empty($month) and !empty($day)) $where .= " AND m.time LIKE '$year$month$day%'";
if (!empty($gid)) $where .= " AND m.gid = '$gid'";
$r_mcount = small_query("SELECT COUNT(*) AS result FROM uts_match m WHERE 1 $where");
$mcount = $r_mcount['result'];

$ecount = $mcount/25;
$ecount2 = number_format($ecount, 0, '.', '');

IF($ecount > $ecount2) {
	$ecount2 = $ecount2+1;
}

$fpage = 0;
IF($ecount < 1) { $lpage = 0; }
else { $lpage = $ecount2-1; }

$cpage = preg_replace('/\D/', '', $_REQUEST["page"]);
IF ($cpage == "") { $cpage = "0"; }
$qpage = $cpage*25;

$tfpage = $cpage+1;
$tlpage = $lpage+1;

$ppage = $cpage-1;
$ppageurl = "<a class=\"pages\" href=\"./?p=recent&amp;year=$year&amp;month=$month&amp;day=$day&amp;gid=$gid&amp;page=$ppage\">[Previous]</a>";
IF ($ppage < "0") { $ppageurl = "[Previous]"; }

$npage = $cpage+1;
$npageurl = "<a class=\"pages\" href=\"./?p=recent&amp;year=$year&amp;month=$month&amp;day=$day&amp;gid=$gid&amp;page=$npage\">[Next]</a>";
IF ($npage >= "$ecount") { $npageurl = "[Next]"; }

$fpageurl = "<a class=\"pages\" href=\"./?p=recent&amp;year=$year&amp;month=$month&amp;day=$day&amp;gid=$gid&amp;page=$fpage\">[First]</a>";
IF ($cpage == "0") { $fpageurl = "[First]"; }

$lpageurl = "<a class=\"pages\" href=\"./?p=recent&amp;year=$year&amp;month=$month&amp;day=$day&amp;gid=$gid&amp;page=$lpage\">[Last]</a>";
IF ($cpage == "$lpage") { $lpageurl = "[Last]"; }

echo '
<form action="'.$_SERVER['PHP_SELF'].'" method="GET">
<div class="pages"><b>Page ['.$tfpage.'/'.$tlpage.'] Selection: '.$fpageurl.' / '.$ppageurl.' / '.$npageurl.' / '.$lpageurl.'</b></div>
<table width="720" class="box" border="0" cellpadding="1" cellspacing="1">
  <tbody><tr>
    <td class="heading" colspan="7" align="center">Unreal Tournament Match List</td>
  </tr>';

echo '
  <tr>
    <td class = "smheading" colspan = "7" align = "center">';
echo '<input type="hidden" name="p" value="'.$_REQUEST['p'].'">';
echo '<table width="700" class="searchform" border="0" cellpadding="1" cellspacing="1">';
echo '<tr><td><strong>Filter:</strong></td>';
//echo '<td>Date:</td>';
echo '<td><select class="searchform" name="year">';
echo '<option value="0">*</option>';
for($i = date('Y');$i >= date("Y") - 5; $i--) {
	$selected = ($year == $i) ? 'selected' : '';
	echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
}
echo '</select>';
echo '&nbsp;';
echo '<select class="searchform" name="month">';
echo '<option value="0">*</option>';
$monthname = array('', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
for($i = 1;$i <= 12; $i++) {
	$selected = ($month == $i) ? 'selected' : '';
	echo '<option '.$selected.' value="'.$i.'">'.$monthname[$i].'</option>';
}
echo '</select>';
echo '&nbsp;';
echo '<select class="searchform" name="day">';
echo '<option value="0">*</option>';
for($i = 1;$i <= 31; $i++) {
	$selected = ($day == $i) ? 'selected' : '';
	echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
}
echo '</select></td>';

echo '<td>Gametype:</td>';
echo '<td><select class="searchform" name="gid">';
echo '<option value="0">*</option>';
$sql_game = "SELECT DISTINCT(p.gid), g.name FROM uts_player AS p, uts_games AS g WHERE p.gid = g.id ORDER BY g.name ASC";
$q_game = mysql_query($sql_game) or die(mysql_error());
while ($r_game = mysql_fetch_array($q_game)) {
	$selected = ($r_game['gid'] == $gid) ? 'selected' : '';
	echo '<option '.$selected.' value="'.$r_game['gid'].'">'. $r_game['name'] .'</option>';
}
echo '</select></td>';
echo '<td><input class="searchform" type="Submit" name="filter" value="Apply"></td>';
echo '</tr></table>';
echo '</td></tr>';

echo '
  <tr>
    <td class="smheading" align="center" width="80">Match ID</td>
    <td class="smheading" align="center" width="220">Date/Time</td>
    <td class="smheading" align="center" width="140">Match Type</td>
    <td class="smheading" align="center">Map</td>
    <td class="smheading" align="center" width="60">Time</td>
    <td class="smheading" align="center" width="60">Players</td>
  </tr>';

$sql_recent = "SELECT m.id, m.time, g.name AS gamename, m.mapfile, m.gametime, (SELECT count(p.id) FROM uts_player AS p WHERE m.id = p.matchid) as players FROM uts_match AS m, uts_games AS g WHERE g.id = m.gid $where ORDER BY m.time DESC LIMIT $qpage,25";
$q_recent = mysql_query($sql_recent) or die(mysql_error());
while ($r_recent = mysql_fetch_array($q_recent)) {

	  $r_time = mdate($r_recent[time]);
	  $r_mapfile = un_ut($r_recent[mapfile]);
	  $r_gametime = GetMinutes($r_recent[gametime]);

	  echo'
	  <tr>
		<td class="dark" align="center"><a class="darkid" href="./?p=match&amp;mid='.$r_recent[id].'">'.$r_recent[id].'</a></td>
		<td nowrap class="dark" align="center"><a class="darkhuman" href="./?p=match&amp;mid='.$r_recent[id].'">'.$r_time.'</a></td>
		<td nowrap class="grey" align="center">'.$r_recent[gamename].'</td>
		<td class="grey" align="center">'.$r_mapfile.'</td>
		<td class="grey" align="center">'.$r_gametime.'</td>
		<td class="grey" align="center">'.$r_recent[players].'</td>
	  </tr>';
}



echo '
</tbody></table>
<div class="pages"><b>Page ['.$tfpage.'/'.$tlpage.'] Selection: '.$fpageurl.' / '.$ppageurl.' / '.$npageurl.' / '.$lpageurl.'</b></div>
</form>';
?>