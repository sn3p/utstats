<?php
echo'<form NAME="report" METHOD="get" ACTION="./?p=report">
	<input type="hidden" name="p" value="report" size="0">
	<input type="hidden" name="rtype" value="clanbase" size="0">
	<input type="hidden" name="id" value="'.$id.'" size="0">
	<input type="hidden" name="stage" value="1" size="0">
  <table CLASS="searchformb">
	<tr>
	  <td class="heading" colspan="2" ALIGN="center" width="480">Enter the Clanbase WarID</td>
	</tr>
	<tr>
	  <td ALIGN="left" class="grey">
	  <input TYPE="text" NAME="wid" MAXLENGTH="20" SIZE="20" value="'.(preg_replace('/\D/', '', $_GET['wid'])).'" CLASS="searchform">
	  <input TYPE="submit" VALUE="Submit" CLASS="searchformb"></td>
	</tr>
  </table>
</form>';

echo'<table>
<tr>
	<td width="480" align="center" class="heading" colspan="2">
	Team BreakDown of Match</td>
</tr>
<tr>
<td width="240" align="center" class="smheading">Red Team</td>
<td width="240" align="center" class="smheading">Blue Team</td>
<tr>
	<td width="50%" class="grey">';

$sql_rteam = "SELECT p.id, pi.name FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = 0 ORDER BY pi.name ASC";
$q_rteam = mysqli_query($GLOBALS["___mysqli_link"], $sql_rteam);
while ($r_rteam = mysqli_fetch_array($q_rteam)) {
	echo''.$r_rteam[name].'<br />';
	}
echo'</td>
<td width="50%" class="grey">';

$sql_bteam = "SELECT p.id, pi.name FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = 1 ORDER BY pi.name ASC";
$q_bteam = mysqli_query($GLOBALS["___mysqli_link"], $sql_bteam);
while ($r_bteam = mysqli_fetch_array($q_bteam)) {
	echo''.$r_bteam[name].'<br />';
	}
echo'</td></tr></table><br>';

IF ($wid == "") {
}

IF ($stage >= "1") {
   include("includes/clanbase.php");
   $warinfo = get_clans($wid);
}

IF ($stage == "1") {
	$claninfo0 = get_players($warinfo[0][1]);
	$claninfo1 = get_players($warinfo[1][1]);

	echo'<form action="" method="get">
	<input type="hidden" name="p" value="report" size="0">
	<input type="hidden" name="rtype" value="clanbase" size="0">
	<input type="hidden" name="id" value="'.$id.'" size="0">
	<input type="hidden" name="wid" value="'.$wid.'" size="0">
	<input type="hidden" name="stage" value="2" size="0">
	<table class="searchformb">

	<tr>
		<td width="480" align="center" class="heading" colspan="2">
		Who Played As Red Team?</td>
	</tr>
	<tr>
		<td width="100%" class="grey" colspan="2">
		<select size="1" name="red" CLASS="searchform">
		<option value="'.$warinfo[0][1].'">'.$warinfo[0][2].'</option>
		<option value="'.$warinfo[1][1].'">'.$warinfo[1][2].'</option>
		</select>
		<input type=submit CLASS="searchformb" value="Assign"></td>
	</tr>
	</table>
	</form>';
}

IF ($stage == "2") {
	$redteam = $_GET["red"];

	IF($redteam == $warinfo[0][1]) {
		$redcid = $warinfo[0][1];
		$redname = $warinfo[0][2];
		$redinfo = get_players($warinfo[0][1]);
		$bluecid = $warinfo[1][1];
		$bluename = $warinfo[1][2];
		$blueinfo = get_players($warinfo[1][1]);

	} else {
		$redcid = $warinfo[1][1];
		$redname = $warinfo[1][2];
		$redinfo = get_players($warinfo[1][1]);
		$bluecid = $warinfo[0][1];
		$bluename = $warinfo[0][2];
		$blueinfo = get_players($warinfo[0][1]);
	}

	echo'<form action="" method="get">
			<input type="hidden" name="p" value="report" size="0">
			<input type="hidden" name="rtype" value="clanbase" size="0">
			<input type="hidden" name="stage" value="generate" size="0">
			<input type="hidden" name="id" value="'.$id.'" size="0">
			<input type="hidden" name="wid" value="'.$wid.'" size="0">
			<input type="hidden" name="red" value="'.htmlspecialchars($redteam, ENT_QUOTES).'" size="0">
			<table class="searchformb">
			<tr>
				<td width="480" colspan="2" align="center" class="heading">
				Clan and Player Info for Clanbase War ID '.$wid.'</td>
			</tr>
			<tr>
				<td colspan="2" align="center" class="smheading">
				<a href="http://www.clanbase.com/claninfo.php?cid='.$redcid.'" target="_blank">'.$redname.'</a></td>
			</tr>';

			$redcount = count($redinfo);
			$bluecount = count($blueinfo);

			$sql_rteam = "SELECT p.id, pi.name FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = 0 ORDER BY pi.name ASC";

			$q_rteam = mysqli_query($GLOBALS["___mysqli_link"], $sql_rteam);
			while($r_rteam = mysqli_fetch_array($q_rteam)) {

			echo'<tr>
				<td class="grey">'.$r_rteam[name].'</td>
				<td class="grey">
				<select CLASS="searchform" name="'.$r_rteam[id].'">
				<option selected CLASS="searchform" value="">Use Me To Assign a CB Player to Stats</option>';

			for ($i = 0; $i < $redcount; $i++) {
					echo'<option CLASS="searchform" value="'.$redinfo[$i][0].'">'.$redinfo[$i][1].'</option>';
				}
				echo'</select></td>
				</tr>';
		}

			echo'<td width="100%" align="center" class="smheading" colspan="2">
				<a href="http://www.clanbase.com/claninfo.php?cid='.$bluecid.'" target="_blank">'.$bluename.'</a></td>';


			$sql_bteam = "SELECT p.id, pi.name FROM uts_player AS p, uts_pinfo AS pi WHERE p.pid = pi.id AND matchid = $id AND team = 1 ORDER BY pi.name ASC";

			$q_bteam = mysqli_query($GLOBALS["___mysqli_link"], $sql_bteam);
			while($r_bteam = mysqli_fetch_array($q_bteam)) {

			echo'<tr>
				<td class="grey">'.$r_bteam[name].'</td>
				<td class="grey">
				<select CLASS="searchform" name="'.$r_bteam[id].'">
				<option selected CLASS="searchform" value="">Use Me To Assign a CB Player to Stats</option>';

			for ($i = 0; $i < $bluecount; $i++) {
					echo'<option CLASS="searchform" value="'.$blueinfo[$i][0].'">'.$blueinfo[$i][1].'</option>';
				}
				echo'</select></td>
				</tr>';
		}

	echo'<tr>
		<td class="grey" colspan="2" align="center">
		<input type=submit CLASS="searchformb" value="Generate Report">
		</td>
	</tr>
	</table></form>';
}
?>