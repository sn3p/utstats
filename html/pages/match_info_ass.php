<?php
// Get information about this match
$sql_assault = small_query("SELECT assaultid, ass_att, gametime, ass_win FROM uts_match WHERE id = $mid");
$ass_id = $sql_assault[assaultid];
$gametime = $sql_assault[gametime];

// Get information about the other match
$sql_assault2 = small_query("SELECT id, gametime, ass_win FROM uts_match WHERE assaultid = '$ass_id' AND id != $mid LIMIT 0,1");
$mid2 = $sql_assault2[id];
$gametime2 = $sql_assault2[gametime];

// Work out who was attacking which match
$ass_att = $sql_assault[ass_att];
IF($ass_att == 0) {
	$ass_att = "Red";
	$ass_att2 = "Blue";
} else {
	$ass_att = "Blue";
	$ass_att2 = "Red";
}

// Work out the end result for each match
$asswin = $sql_assault[ass_win];
$asswin2 = $sql_assault2[ass_win];
IF($asswin == 0) {
	$asswin = "$ass_att2 Successfully Defended";
} else {
	$asswin = "$ass_att Successfully Attacked";
}

IF($asswin2 == 0) {
	$asswin2 = "$ass_att Successfully Defended";
} else {
	$asswin2 = "$ass_att2 Successfully Attacked";
}

$gametime = sec2min($gametime);
$gametime2 = sec2min($gametime2);


teamstats($mid, 'Match Summary - '.$ass_att.' Team Attacking', 'ass_obj', 'Ass Obj');

echo'
<table class = "box" border="0" cellpadding="0" cellspacing="2" width="720">
  <tbody><tr>
    <td class="hlheading" colspan="15" align="center">'.$asswin.'</td>
  </tr>
</tbody></table>
<br>';

// The Other Game (if it happened)

IF($mid2 != NULL) {
	teamstats($mid2, 'Match Summary - '.$ass_att2.' Team Attacking', 'ass_obj', 'Ass Obj');
	
	echo'
	<table class = "box" border="0" cellpadding="0" cellspacing="2" width="720">
	<tbody><tr>
		<td class="hlheading" colspan="15" align="center">'.$asswin2.'</td>
	</tr>
	</tbody></table>
	<br>';
}
?>
