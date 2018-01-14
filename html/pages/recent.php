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

if ($ecount > $ecount2) {
  $ecount2 = $ecount2+1;
}

$fpage = 0;
if ($ecount < 1) { $lpage = 0; }
else { $lpage = $ecount2-1; }

$cpage = mysqli_real_escape_string($GLOBALS["___mysqli_link"], preg_replace('/\D/', '', $_REQUEST["page"]));
if ($cpage == "") { $cpage = "0"; }
$qpage = $cpage*25;

$tfpage = $cpage+1;
$tlpage = $lpage+1;

$ppage = $cpage-1;
$ppageurl = "<a class=\"pages\" href=\"./?p=recent&amp;year=$year&amp;month=$month&amp;day=$day&amp;gid=$gid&amp;page=$ppage\">[Previous]</a>";
if ($ppage < "0") { $ppageurl = "[Previous]"; }

$npage = $cpage+1;
$npageurl = "<a class=\"pages\" href=\"./?p=recent&amp;year=$year&amp;month=$month&amp;day=$day&amp;gid=$gid&amp;page=$npage\">[Next]</a>";
if ($npage >= "$ecount") { $npageurl = "[Next]"; }

$fpageurl = "<a class=\"pages\" href=\"./?p=recent&amp;year=$year&amp;month=$month&amp;day=$day&amp;gid=$gid&amp;page=$fpage\">[First]</a>";
if ($cpage == "0") { $fpageurl = "[First]"; }

$lpageurl = "<a class=\"pages\" href=\"./?p=recent&amp;year=$year&amp;month=$month&amp;day=$day&amp;gid=$gid&amp;page=$lpage\">[Last]</a>";
if ($cpage == "$lpage") { $lpageurl = "[Last]"; }

echo '
<form action="'.$_SERVER['PHP_SELF'].'" method="GET">
<div class="pages spacer">Page ['.$tfpage.'/'.$tlpage.'] Selection: '.$fpageurl.' / '.$ppageurl.' / '.$npageurl.' / '.$lpageurl.'</div>

<table width="900" class="zebra box" border="0" cellpadding="0" cellspacing="0">
  <tbody><tr>
    <th class="heading" colspan="7" align="center">Unreal Tournament Match List</th>
  </tr>
  <tr>
    <th colspan="7" align="center">';
echo '<input type="hidden" name="p" value="'.htmlentities($_REQUEST['p']).'">';
echo '<table width="900" class="smheading" border="0" cellpadding="0" cellspacing="0">';
echo '<tr><th class="noborders">Filter:</th>';
//echo '<td>Date:</td>';
echo '<th class="noborders"><select class="searchform" name="year">';
echo '<option value="0">*</option>';

for ($i = date('Y');$i >= date("Y") - 5; $i--) {
  $selected = ($year == $i) ? 'selected' : '';
  echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
}

echo '</select>';
echo '&nbsp;';
echo '<select class="searchform" name="month">';
echo '<option value="0">*</option>';

$monthname = array('', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
for ($i = 1;$i <= 12; $i++) {
  $selected = ($month == $i) ? 'selected' : '';
  echo '<option '.$selected.' value="'.$i.'">'.$monthname[$i].'</option>';
}

echo '</select>';
echo '&nbsp;';
echo '<select class="searchform" name="day">';
echo '<option value="0">*</option>';

for ($i = 1;$i <= 31; $i++) {
  $selected = ($day == $i) ? 'selected' : '';
  echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
}

echo '</select></th>';
echo '<th class="noborders">Gametype:</th>';
echo '<th class="noborders"><select class="searchform" name="gid">';
echo '<option value="0">*</option>';

$sql_game = "SELECT DISTINCT(p.gid), g.name FROM uts_player AS p, uts_games AS g WHERE p.gid = g.id ORDER BY g.name ASC";
$q_game = mysqli_query($GLOBALS["___mysqli_link"], $sql_game) or die(mysqli_error($GLOBALS["___mysqli_link"]));

while ($r_game = mysqli_fetch_array($q_game)) {
  $selected = ($r_game['gid'] == $gid) ? 'selected' : '';
  echo '<option '.$selected.' value="'.$r_game['gid'].'">'. $r_game['name'] .'</option>';
}

echo '</select></th>';
echo '<th class="noborders"><input class="searchform" type="Submit" name="filter" value="Apply"></th>';
echo '</tr></table>';
echo '</td></tr>';
echo '
  <tr>
    <th class="smheading" align="center" width="40">ID</th>
    <th class="smheading" align="center" width="220">Date/Time</th>
    <th class="smheading" align="center" width="140">Match Type</th>
    <th class="smheading" align="center">Map</th>
	  <th class="smheading" align="center" width="200">Scores</th>
  </tr>';

$sql_recent = "SELECT m.id, m.time, g.name AS gamename, m.mapfile, m.gametime, t0score, t1score, t2score, t3score, (SELECT count(p.id) FROM uts_player AS p WHERE m.id = p.matchid) as players FROM uts_match AS m, uts_games AS g WHERE g.id = m.gid $where ORDER BY m.time DESC LIMIT ".mysqli_real_escape_string($GLOBALS["___mysqli_link"], $qpage).",50";
$q_recent = mysqli_query($GLOBALS["___mysqli_link"], $sql_recent) or die(mysqli_error($GLOBALS["___mysqli_link"]));

while ($r_recent = mysqli_fetch_array($q_recent)) {
  $r_time = mdate($r_recent[time]);
  $r_mapfile = un_ut($r_recent[mapfile]);
  $r_gametime = GetMinutes($r_recent[gametime]);
  $winner = max($r_recent[t0score], $r_recent[t1score], $r_recent[t2score], $r_recent[t3score]);
  $moreThan2Teams = ($r_recent[t2score]!=0);

  if ($winner == $r_recent[t0score]) {
    $winnercolor = "red";
    $winmsg = "Red is the winner!";
  }
  elseif ($winner == $r_recent[t1score]) {
    $winnercolor = "blue";
    $winmsg = "Blue is the winner!";
  }
  elseif ($winner == $r_recent[t2score]) {
    $winnercolor = "green";
    $winmsg = "Green is the winner!";
  }
  else {
    $winnercolor = "gold";
    $winmsg = "Gold is the winner!";
  };

  echo '
  <tr class="clickableRow" href="./?p=match&amp;mid='.$r_recent[id].'">
    <td align="center">'.$r_recent[id].'</td>
    <td nowrap align="center"><a href="./?p=match&amp;mid='.$r_recent[id].'">'.$r_time.'</a></td>
    <td nowrap align="center">'.$r_recent[gamename].'</td>
    <td align="center">'.$r_mapfile.'</td>
    <td class="tooltip" title="'.$winmsg.'" align="center"><span class="redbox">'.$r_recent[t0score].'</span><span class="bluebox">'.$r_recent[t1score].'</span>';

		if  ($moreThan2Teams) {
			echo '<span class="greenbox">'.$r_recent[t2score].' </span><span class="goldbox">  '.$r_recent[t3score].' </span>';
		}

    '</td>
  </tr>';
}

echo '
</tbody></table>
<div class="pages">Page ['.$tfpage.'/'.$tlpage.'] Selection: '.$fpageurl.' / '.$ppageurl.' / '.$npageurl.' / '.$lpageurl.'</div>
</form>';
?>
