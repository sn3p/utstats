<?php

// Firstly we need to work out First Last Next Prev pages
$scount = small_count("SELECT servername, serverip FROM uts_match GROUP BY servername, serverip");
$ecount = $scount/25;
$ecount2 = number_format($ecount, 0, '.', '');

if ($ecount > $ecount2) {
  $ecount2 = $ecount2+1;
}

$fpage = 0;
if($ecount < 1) { $lpage = 0; }
else { $lpage = $ecount2-1; }

$cpage = preg_replace('/\D/', '', $_GET["page"]);
if ($cpage == "") { $cpage = "0"; }
$qpage = $cpage*25;

$tfpage = $cpage+1;
$tlpage = $lpage+1;

$ppage = $cpage-1;
$ppageurl = "<a class=\"pages\" href=\"./?p=servers&amp;page=$ppage\">[Previous]</a>";
if ($ppage < "0") { $ppageurl = "[Previous]"; }

$npage = $cpage+1;
$npageurl = "<a class=\"pages\" href=\"./?p=servers&amp;page=$npage\">[Next]</a>";
if ($npage >= "$ecount") { $npageurl = "[Next]"; }

$fpageurl = "<a class=\"pages\" href=\"./?p=servers&amp;page=$fpage\">[First]</a>";
if ($cpage == "0") { $fpageurl = "[First]"; }

$lpageurl = "<a class=\"pages\" href=\"./?p=servers&amp;page=$lpage\">[Last]</a>";
if ($cpage == "$lpage") { $lpageurl = "[Last]"; }

echo '
<div class="pages">Page ['.$tfpage.'/'.$tlpage.'] Selection: '.$fpageurl.' / '.$ppageurl.' / '.$npageurl.' / '.$lpageurl.'</div>
<table class="zebra box" border="0" cellpadding="0" cellspacing="0">
<tbody>
  <tr>
		<th class="heading" colspan="6" align="center">Unreal Tournament Server List</th>
  </tr>
  <tr>
    <td class="smheading" align="center" width="250">Server Name</td>
    <td class="smheading" align="center" width="50">Status</td>
    <td class="smheading" align="center" width="100">Matches</td>
    <td class="smheading" align="center">Frags</td>
    <td class="smheading" align="center" width="100">TeamScore</td>
    <td class="smheading" align="center" width="100">Hours</td>
  </tr>';

$sql_servers = "SELECT servername, serverip, COUNT(*) AS matchcount, SUM(frags) AS frags, SUM(t0score+t1score+t2score+t3score) AS matchscore, SUM(gametime) AS gametime
  FROM uts_match GROUP BY servername, serverip ORDER BY servername ASC LIMIT $qpage,25";
$q_servers = mysqli_query($GLOBALS["___mysqli_link"], $sql_servers) or die(mysqli_error($GLOBALS["___mysqli_link"]));

while ($r_servers = mysqli_fetch_array($q_servers)) {
    $r_gametime = sec2hour($r_servers[gametime]);

    echo '
    <tr>
      <td align="center">
				<a href="./?p=sinfo&amp;serverip='.$r_servers[serverip].'">'.$r_servers[servername].'</a>
			</td>
      <td align="center">
				<a href="./?p=squery&amp;serverip='.$r_servers[serverip].'">
					<img border="0" alt="Server Status" src="assets/images/search.png">
				</a>
			</td>
      <td align="center">'.$r_servers[matchcount].'</td>
      <td align="center">'.$r_servers[frags].'</td>
      <td align="center">'.$r_servers[matchscore].'</td>
      <td align="center">'.$r_gametime.'</td>
    </tr>';
}

echo '
</tbody>
</table>

<div class="pages">Page ['.$tfpage.'/'.$tlpage.'] Selection: '.$fpageurl.' / '.$ppageurl.' / '.$npageurl.' / '.$lpageurl.'</div>';
?>
