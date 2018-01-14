<?php

$matchinfo = small_query("SELECT m.time, m.servername, g.name AS gamename, m.gamename AS real_gamename, m.gid, m.mapname, m.mapfile, m.serverinfo, m.gameinfo, m.mutators, m.serverip FROM uts_match AS m, uts_games AS g WHERE m.gid = g.id AND m.id = $mid");
$matchdate = mdate($matchinfo[time]);
$gamename = $matchinfo[gamename];
$real_gamename = $matchinfo[real_gamename];
$gid = $matchinfo[gid];

$mapname = un_ut($matchinfo[mapfile]);
$mappic = getMapImageName($mapname);
$myurl = urlencode($mapname);

$mapnameToPrint = $matchinfo[mapname];
if ($mapnameToPrint == "Untitled") {
  $mapnameToPrint = $mapname;
}

echo'
<table width="700" cellpadding="0" cellspacing="0" class="box matchtop">
<tbody>
  <tr>
    <th class="heading"><center>Match Stats</center></th>
  </tr>
</tbody>
</table>

<div class="matchheader" style="background-image: url(\''.$mappic.'\');background-size: 100% 100%;"></div>

<table class="zebra box" border="0" cellpadding="0" cellspacing="0" width="700">
<tbody>
';

if ($r_info[t0score] > 0 || $r_info[t1score] > 0) {
  echo '
  <tr>
  <th colspan="2" class="red score" width="50%">'.$r_info[t0score].'</th>
  <th colspan="2" class="blue score" width="50%">'.$r_info[t1score].'</th>
  </tr>';

  if ($r_info[t2score] > 0 || $r_info[t3score] > 0) {
    echo'
    <tr>
    <th colspan="2" class="green score" width="50%">'.$r_info[t2score].'</th>
    <th colspan="2" class="yellow score" width="50%">'.$r_info[t3score].'</th>
    </tr>';
  }
}

echo '
<tr>
  <td class="smheading" align="center" width="auto">Match Date</td>
  <td class="grey" align="center">'.$matchdate.'</td>
  <td class="smheading" align="center">Server</td>
  <td class="grey" align="center"><a class="grey" href="./?p=sinfo&amp;serverip='.$matchinfo[serverip].'">'.$matchinfo[servername].'</a></td>
</tr>
<tr>
  <td class="smheading" align="center">Mutators</td>
  <td class="grey" align="center">'.$matchinfo[mutators].'</td>
  <td class="smheading" align="center">Map Name</td>
  <td class="grey" align="center"><a class="grey" href="./?p=minfo&amp;map='.$myurl.'">'.$mapnameToPrint.'</a></td>
</tr>
<tr>
  <td class="smheading" align="center">Server Info</td>
  <td class="grey" align="center">'.$matchinfo[serverinfo].'</td>
  <td class="smheading" align="center">Game Info</td>
  <td class="grey" align="center">'.$matchinfo[gameinfo].'</td>
</tr>

</tbody>
</table>
<br>';

?>
