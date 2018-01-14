<?php
//Return if no variables are set (exit)
if (isset($_GET['serverip']) != 1) return;

// Get server IP info
list($serverip, $serverport) = split(":", addslashes($_GET[serverip]));
$queryport = preg_replace('/\D/', '', $serverport)+1;

// check for valid ip address, no ipv6 support
if (!preg_match('/(\w{1,3}\.){3}\w{1,3}/', $serverip)) {
  die('bla');
}

//If there is no IP, return (exit)
if (strlen($serverip) == 0) return;

//Open UDP socket to server
$sock = fsockopen("udp://" . $serverip, $queryport, $errno, $errstr,4);

//Check if we have a socket open, if not, display error message
if (!$sock) {
	echo "$errstr ($errno)<br>\n";
	exit;
}

fputs($sock,"\\status\\\player_property\Health\\\game_property\ElapsedTime\\\game_property\RemainingTime\\");

$gotfinal = False;
$data = "";

//Set starttime, for possible loop expiration, so the server doesn't get too much work.
$starttime = Time();

//Loop until final packet has been received.
while(!($gotfinal == True || feof($sock))) {

	//Get data
	if(($buf = fgetc($sock)) == FALSE) {
		usleep(100); // wait for additional data? :S whatever
	}

	//Add to databuffer
	$data .= $buf;

	//Check if final item (queryid) has been received
	if (strpos($data,"final\\") != False) {
		$gotfinal = True;
	}

	//Protect webserver against massive loop.
	if ((Time() - $starttime) > 5) {
		echo "Data receiving took too long. Cancelled.<P>";
		$gotfinal = True;
	}
}

//Close socket
fclose ($sock);


//Split chunks by \
$chunks = split('[\]', $data);

$mappic = strtolower("assets/images/maps/".$map.".jpg");

if (file_exists($mappic)) {
} else {
   $mappic = ("assets/images/maps/blank.jpg");
}

$mapname = getiteminfo("mapname",$chunks);
$mappic = strtolower("assets/images/maps/".$mapname.".jpg");
if (file_exists($mappic)) {
	} else {
	$mappic = "assets/images/maps/blank.jpg";
}

$r_hostname = getiteminfo("hostname",$chunks);
$r_gametype = getiteminfo("gametype",$chunks);

$r_adminname = getiteminfo("adminname",$chunks);
$r_adminemail = getiteminfo("adminemail",$chunks);

$r_password = getiteminfo("password",$chunks);
$r_timelimit = getiteminfo("timelimit",$chunks);
$r_remainingtime = GetMinutes(getiteminfo("RemainingTime",$chunks))  . " mins";
$r_elapsedtime = GetMinutes(getiteminfo("ElapsedTime",$chunks)) . " mins";

$r_goalteamscore = getiteminfo("goalteamscore",$chunks);
$r_numplayers = getiteminfo("numplayers",$chunks);
$r_minplayers = getiteminfo("minplayers",$chunks);
$r_maxplayers = getiteminfo("maxplayers",$chunks);
$r_maxteams = getiteminfo("maxteams",$chunks);

$r_balanceteams = getiteminfo("balanceteams",$chunks);
$r_tournament = getiteminfo("tournament",$chunks);
$r_friendlyfire = getiteminfo("friendlyfire",$chunks);
$r_gamestyle = getiteminfo("gamestyle",$chunks);

echo'
<table class = "box" border="0" cellpadding="0" cellspacing="0" width="720">
  <tbody><tr>
    <td class="heading" colspan="4" align="center">Server Status for '.$r_hostname.'</td>
  </tr>
  <tr>
    <td class="dark" align="center" width="110">Server IP</td>
    <td class="grey" align="center" width="350"><a class="grey" href="unreal://'.$serverip.':'.$serverport.'">'.$serverip.':'.$serverport.'</a></td>
    <td class="tooltip" align="center" rowspan="5" colspan="2"><img border="0" alt="'.$mapname.'" title="'.$mapname.'" src="'.$mappic.'"></td>
  </tr>
  <tr>
    <td class="dark" align="center">Map Name</td>
    <td class="grey" align="center">'.$mapname.'</td>
  </tr>
  <tr>
    <td class="dark" align="center" width="110">Match Type</td>
    <td class="grey" align="center">'.$r_gametype.'</td>
  </tr>
  <tr>
    <td class="dark" align="center">Server Info</td>
    <td class="grey" align="center">Admin: '.$r_adminname.'<br />Email: '.$r_adminemail.'</td>
  </tr>
  <tr>
    <td class="dark" align="center">Game Info</td>
    <td class="grey" align="center">
	Goal Team Score: '.$r_goalteamscore.'<br />
	Min Players: '.$r_minplayers.'<br />
	Max Players: '.$r_maxplayers.'<br />
	Max Teams: '.$r_maxteams.'<br />
	Balanced Teams: '.$r_balanceteams.'<br />
	Tournament Mode: '.$r_tournament.'<br />
	Friendly Fire: '.$r_friendlyfire.'<br />
	Game Style: '.$r_gamestyle.'<br /><br />
    Time Limit: '.$r_timelimit.'<br />
    Time Remaining: '.$r_remainingtime.'<br />
    Time Elapsed: '.$r_elapsedtime.'<br />
    Password Required: '.$r_password.'<br />
	</td>
  </tr>
</tbody></table>
<br>

<table class = "box" border="0" cellpadding="0" cellspacing="0" width="720">
  <tbody>
  <tr>
    <td class="heading" colspan="5" align="center">Player Information</td>
  </tr>
  <tr>
    <td class="smheading" align="center" width="270">Player</td>
    <td class="smheading" align="center">Frags</td>
    <td class="smheading" align="center">Team</td>
    <td class="smheading" align="center">Ping</td>
    <td class="smheading" align="center">Health</td>
  </tr>';

//Loop through all players
for ($i = 0; $i < $r_numplayers; $i++) {
	$actualid = $i;
	$itemx = $i+1;

	$r_playername = getiteminfo("player_" . $actualid,$chunks);
	$r_playerfrags = getiteminfo("frags_" . $actualid,$chunks);
	$r_playerteam = getiteminfo("team_" . $actualid,$chunks);
	$r_playerping = getiteminfo("ping_" . $actualid,$chunks);
	$r_playerhealth = getiteminfo("Health_" . $itemx,$chunks);

	echo'<tr>
		<td class="grey" align="center">'.$r_playername.'</td>
		<td class="grey" align="center">'.$r_playerfrags.'</td>
		<td class="grey" align="center">'.$r_playerteam.'</td>
		<td class="grey" align="center">'.$r_playerping.'</td>
		<td class="grey" align="center">'.$r_playerhealth.'</td>
	  </tr>';
}

echo'</tbody></table>';
?>
