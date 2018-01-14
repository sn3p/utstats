<?php
$serverip = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $_GET[serverip]);
 
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
 
$cpage = mysqli_real_escape_string($GLOBALS["___mysqli_link"], $_GET["page"]);
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
<table class = "zebra box" border="0" cellpadding="0" cellspacing="0" width="700">
  <tbody><tr>
    <th class="heading" align="center" colspan="2">'.$serverinfo[servername].'</th>
  </tr>
  <tr>
    <th align="center" width="110">Last Match</th>
    <td align="center" width="305">'.$matchdate.'</td>
  </tr>
  <tr>
    <th align="center">Server Info</th>
    <td align="center">'.$serverinfo[serverinfo].'</td>
  </tr>
  <tr>
    <th align="center">Mutators</td>
    <td align="center">'.$serverinfo[mutators].'</th>
  </tr>
  <tr>
    <th align="center">Game Info</td>
    <td align="center">'.$serverinfo[gameinfo].'</td>
  </tr>
</tbody></table>
<br>';
 
// Do graph stuff
$bgwhere = "serverip = '$serverip'";
include("pages/graph_mbreakdown.php");
 
echo'<div class="pages">Page ['.$tfpage.'/'.$tlpage.'] Selection: '.$fpageurl.' / '.$ppageurl.' / '.$npageurl.' / '.$lpageurl.'</div>
<table class="zebra box" border="0" cellpadding="0" cellspacing="0">
  <tbody><tr>
    <th class="heading" colspan="4" align="center">Unreal Tournament Match List</th>
  </tr>
  <tr>
    <th class="smheading" align="center" width="220">Date/Time</th>
    <th class="smheading" align="center" width="140">Match Type</th>
    <th class="smheading" align="center">Map</td>
    <th class="smheading" align="center" width="40">Time</th>
  </tr>';
 
$sql_recent = "SELECT m.id, m.time, g.name AS gamename, m.mapfile, m.gametime FROM uts_match AS m, uts_games AS g  WHERE g.id = m.gid AND m.serverip = '$serverip' ORDER BY m.time DESC LIMIT $qpage,25";
$q_recent = mysqli_query($GLOBALS["___mysqli_link"], $sql_recent) or die(mysqli_error($GLOBALS["___mysqli_link"]));
while ($r_recent = mysqli_fetch_array($q_recent)) {
 
          $r_time = mdate($r_recent[time]);
          $r_mapfile = un_ut($r_recent[mapfile]);
          $r_gametime = sec2min($r_recent[gametime]);
          $myurl = urlencode($r_mapfile);
 
          echo'
          <tr class="clickableRow" href="./?p=match&amp;mid='.$r_recent[id].'">
                <td align="center"><a href="./?p=match&amp;mid='.$r_recent[id].'">'.$r_time.'</a></td>
                <td align="center">'.$r_recent[gamename].'</td>
                <td align="center"><a href="./?p=minfo&amp;map='.$myurl.'">'.$r_mapfile.'</a></td>
                <td align="center">'.$r_gametime.'</td>
          </tr>';
}
 
echo'
</tbody></table>
<div class="pages">Page ['.$tfpage.'/'.$tlpage.'] Selection: '.$fpageurl.' / '.$ppageurl.' / '.$npageurl.' / '.$lpageurl.'</div>';
?>