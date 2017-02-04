<?php
$serverip = $_GET[serverip];

// Firstly we need to work out First Last Next Prev pages
$scount = small_count("SELECT id FROM uts_match WHERE serverip = '$serverip'");

$ecount = $scount/25;
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
$ppageurl = "<a class=\"pages\" href=\"./?p=sinfo&amp;serverip=$serverip&amp;page=$ppage\">[Previous]</a>";
IF ($ppage < "0") { $ppageurl = "[Previous]"; }

$npage = $cpage+1;
$npageurl = "<a class=\"pages\" href=\"./?p=sinfo&amp;serverip=$serverip&amp;page=$npage\">[Next]</a>";
IF ($npage >= "$ecount") { $npageurl = "[Next]"; }

$fpageurl = "<a class=\"pages\" href=\"./?p=sinfo&amp;serverip=$serverip&amp;page=$fpage\">[First]</a>";
IF ($cpage == "0") { $fpageurl = "[First]"; }

$lpageurl = "<a class=\"pages\" href=\"./?p=sinfo&amp;serverip=$serverip&amp;page=$lpage\">[Last]</a>";
IF ($cpage == "$lpage") { $lpageurl = "[Last]"; }


// Get the last match entry for this server

$serverinfo = small_query("SELECT time, servername, serverinfo, gameinfo, mutators FROM uts_match WHERE serverip = '$serverip' ORDER BY time DESC LIMIT 0,1");
$matchdate = mdate($serverinfo[time]);

echo'
<table class = "box" border="0" cellpadding="1" cellspacing="2" width="720">
  <tbody><tr>
    <td class="heading" align="center" colspan="4">'.$serverinfo[servername].'</td>
  </tr>
  <tr>
    <td class="dark" align="center" width="110">Last Match</td>
    <td class="grey" align="center" width="305">'.$matchdate.'</td>
    <td class="dark" align="center" width="305">Game Info</td>
  </tr>
  <tr>
    <td class="dark" align="center">Server Info</td>
    <td class="grey" align="center">'.$serverinfo[serverinfo].'</td>
    <td class="grey" align="center" rowspan="2">'.$serverinfo[gameinfo].'</td>
  </tr>
  <tr>
    <td class="dark" align="center">Mutators</td>
    <td class="grey" align="center">'.$serverinfo[mutators].'</td>
  </tr>
</tbody></table>
<br>';

// Do graph stuff
$bgwhere = "serverip = '$serverip'";
include("pages/graph_mbreakdown.php");

echo'<div class="pages"><b>Page ['.$tfpage.'/'.$tlpage.'] Selection: '.$fpageurl.' / '.$ppageurl.' / '.$npageurl.' / '.$lpageurl.'</b></div>
<table class="box" border="0" cellpadding="1" cellspacing="1">
  <tbody><tr>
    <td class="heading" colspan="4" align="center">Unreal Tournament Match List</td>
  </tr>
  <tr>
    <td class="smheading" align="center" width="220">Date/Time</td>
    <td class="smheading" align="center" width="140">Match Type</td>
    <td class="smheading" align="center">Map</td>
    <td class="smheading" align="center" width="40">Time</td>
  </tr>';

$sql_recent = "SELECT m.id, m.time, g.name AS gamename, m.mapfile, m.gametime FROM uts_match AS m, uts_games AS g  WHERE g.id = m.gid AND m.serverip = '$serverip' ORDER BY m.time DESC LIMIT $qpage,25";
$q_recent = mysql_query($sql_recent) or die(mysql_error());
while ($r_recent = mysql_fetch_array($q_recent)) {

	  $r_time = mdate($r_recent[time]);
	  $r_mapfile = un_ut($r_recent[mapfile]);
	  $r_gametime = sec2min($r_recent[gametime]);
	  $myurl = urlencode($r_mapfile);

	  echo'
	  <tr>
		<td class="dark" align="center"><a class="darkhuman" href="./?p=match&amp;mid='.$r_recent[id].'">'.$r_time.'</a></td>
		<td class="grey" align="center">'.$r_recent[gamename].'</td>
		<td class="grey" align="center"><a class="grey" href="./?p=minfo&amp;map='.$myurl.'">'.$r_mapfile.'</a></td>
		<td class="grey" align="center">'.$r_gametime.'</td>
	  </tr>';
}

echo'
</tbody></table>
<div class="pages"><b>Page ['.$tfpage.'/'.$tlpage.'] Selection: '.$fpageurl.' / '.$ppageurl.' / '.$npageurl.' / '.$lpageurl.'</b></div>';
?>