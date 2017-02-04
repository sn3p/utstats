<?php

/*
$options['title'] = 'Test';
//$options['requireconfirmation'] = false;


$options['vars'][$i]['name'] = 'var_game';
$options['vars'][$i]['type'] = 'game';
$options['vars'][$i]['prompt'] = 'Choose game:';
$options['vars'][$i]['caption'] = 'Game:';
//$options['vars'][$i]['extraoption'] = 'extra';


$options['vars'][$i]['name'] = 'var_srv';
$options['vars'][$i]['type'] = 'server';
$options['vars'][$i]['prompt'] = 'Choose server:';
$options['vars'][$i]['caption'] = 'Server:';
//$options['vars'][$i]['wheregid'] = 'var_game';


$options['vars'][$i]['name'] = 'var_match';
$options['vars'][$i]['type'] = 'match';
$options['vars'][$i]['prompt'] = 'Choose Match:';
$options['vars'][$i]['caption'] = 'Match:';
//$options['vars'][$i]['whereserver'] = 'var_srv';
//$options['vars'][$i]['wheregid'] = 'var_game';
//$options['vars'][$i]['whereplayer'] = 'var_player';
$options['vars'][$i]['exclude'] = 'var_match';


$options['vars'][$i]['name'] = 'var_plr';
$options['vars'][$i]['type'] = 'player';
$options['vars'][$i]['prompt'] = 'Choose player';
$options['vars'][$i]['caption'] = 'Player:';
//$options['vars'][$i]['wherematch'] = 'var_match';
//$options['vars'][$i]['whereserver'] = 'var_server';
//$options['vars'][$i]['wheregid'] = 'var_game';


$options['vars'][$i]['name'] = 'sure';
$options['vars'][$i]['type'] = 'static';
$options['vars'][$i]['options'] = 'No|Yes';
$options['vars'][$i]['exitif'] = 'No';
$options['vars'][$i]['prompt'] = 'Are you sure?';
$options['vars'][$i]['caption'] = 'Sure:';


$options['vars'][$i]['name'] = 'ip_from';
$options['vars'][$i]['type'] = 'text';
$options['vars'][$i]['prompt'] = 'Enter the IP you want to search from:';
$options['vars'][$i]['caption'] = 'IP from:';
$options['vars'][$i]['initialvalue'] = 'ip_from';

*/


function adminselect(&$options) {
	$i = !empty($_REQUEST['step']) ? $_REQUEST['step'] : 0;
	if (isset($_REQUEST['back'])) {
		if (isset($_REQUEST['cur_var'])) unset($_REQUEST[$_REQUEST['cur_var']]);
		$i -= 2;
	}
	if (isset($_REQUEST['noop'])) {
		if (isset($_REQUEST['cur_var'])) unset($_REQUEST[$_REQUEST['cur_var']]);
		$i -= 1;
	}
	if (!isset($_REQUEST['noop'])) {
		if (isset($_REQUEST['playerfilter'])) unset($_REQUEST['playerfilter']);
	}
	$step = $i + 1;
	$maxsteps = count($options['vars']);
	if (!isset($options['requireconfirmation']) or $options['requireconfirmation']) $maxsteps++;
	if (!isset($_REQUEST['values']) or empty($_REQUEST['values'])) {
		$values = array();
	} else {
		$valtmp = explode(',', $_REQUEST['values']);
		foreach($valtmp as $valtmp2) {
			$valtmp3 = explode('=>', $valtmp2);
			$values[$valtmp3[0]] = $valtmp3[1];
		}
	}
	if (isset($_REQUEST['submit']) and isset($_REQUEST['cur_var'])) {
		$values[$_REQUEST['cur_var']] = $_REQUEST[$_REQUEST['cur_var']];
		unset($_REQUEST[$_REQUEST['cur_var']]);
		if (isset($options['vars'][$i - 1]['exitif']) and $options['vars'][$i - 1]['exitif'] == $values[$_REQUEST['cur_var']]) $i = $maxsteps;
	}
	if ($i == $maxsteps) return($values);
	echo '<table border="0" cellpadding="1" cellspacing="0" width="716">
			<tbody>
			<tr><td class="heading">'.htmlentities($options['title']).'</td></tr>
			<tr><td class="smheading">Step '.$step.' of '.$maxsteps.'</td></tr>
			</tbody></table><br>';
	if ($step != $maxsteps and !isset($options['vars'][$i])) die("Something went wrong :(");
	
	echo '<form action="'. $_SERVER['PHP_SELF'] .'" method="POST">';

	echo '<table border="0" cellpadding="1" cellspacing="2" width="600">';
	if ($step == $maxsteps) {
		echo '<tr><td colspan="2" class="medheading">Please Confirm!</td></tr>';
	}

	foreach($options['vars'] as $num => $var) {	
		if ((!isset($values[$var['name']]) and $num != $i) or $num > $i) continue;
		echo '<tr><td class="smheading" width="150">';
		
		if ($num == $i or !isset($var['caption'])) {
			echo htmlentities($var['prompt']);
		} else {
			echo htmlentities($var['caption']);
		}
		
		echo '</td>';
		
		echo '<td class="grey" width="400">';
		if ($num != $i) {
			if (isset($var['extraoption']) and $values[$var['name']] == $var['extraoption']) {
				echo htmlentities($values[$var['name']]);
			} else {
				switch($var['type']) {
					case 'game':
						$r_game = small_query("SELECT gamename, name FROM uts_games WHERE id = '". $values[$var['name']] ."'");
						echo htmlentities($r_game['name']) .' ('. htmlentities($r_game['gamename']) .')';
						break;
					case 'server':
						$r_server = small_query("SELECT servername, serverip FROM uts_match WHERE id = '". $values[$var['name']] ."'");
						echo htmlentities($r_server['servername']) .' ('. $r_server['serverip'] .')';
						break;
					case 'player':
						$r_player = small_query("SELECT name FROM uts_pinfo WHERE id = '". $values[$var['name']] ."'");
						echo htmlentities($r_player['name']);
						break;
					case 'match':
						$r_match = small_query("SELECT id, time, serverip, mapfile FROM uts_match WHERE id = '". $values[$var['name']] ."'");
						echo htmlentities($r_match['id'].': '.mdate2($r_match['time']).' ('.un_ut($r_match['mapfile']).' on '.$r_match['serverip'].')');
						break;
					case 'static':
					case 'text':
						echo htmlentities($values[$var['name']]);
						break;
					default:
						echo 'Show: Don\'tknow what to do with type '. $var['type'];
				}
			}
		} else {
			if (isset($var['initialvalue']) and isset($values[$var['initialvalue']])) $values[$var['name']] = $values[$var['initialvalue']];
			echo '<input type="hidden" name="cur_var" value="'.$var['name'].'">';
			switch($var['type']) {
				case 'game':
					echo '<select class="searchform" name="'. $var['name'] .'">';
					if (isset($var['extraoption'])) {
						if (isset($var['exclude']) and $var['extraoption'] == $values[$var['exclude']]) {
						} else {
							echo '<option value="'.$var['extraoption'].'">'.$var['extraoption'].'</option>';
						}
					}
					
					$sql_game = "SELECT id, gamename, name FROM uts_games ORDER BY name ASC";
					$q_game = mysql_query($sql_game) or die(mysql_error());
					while ($r_game = mysql_fetch_array($q_game)) {
						if (isset($var['exclude']) and $r_game['id'] == $values[$var['exclude']]) continue;
						$selected = (isset($values[$var['name']]) and $r_game['id'] == $values[$var['name']]) ? 'selected' : '';
						echo '<option '.$selected.' value="'.$r_game['id'].'">'. htmlentities($r_game['name'] .' ('. $r_game['gamename'] .')') .'</option>';
					}
					echo '</select>';
					break;

				
				case 'server':
					echo '<select class="searchform" name="'. $var['name'] .'">';
					if (isset($var['extraoption'])) {
						if (isset($var['exclude']) and $var['extraoption'] == $values[$var['exclude']]) {
						} else {
							echo '<option value="'.$var['extraoption'].'">'.$var['extraoption'].'</option>';
						}
					}
					
					$sql_server = "SELECT id, servername, serverip FROM uts_match GROUP BY servername, serverip ORDER BY servername ASC";
					if (isset($var['wheregid'])) {
						$sql_server = "SELECT id, servername, serverip FROM uts_match WHERE gid = '". $values[$var['wheregid']] ."' GROUP BY servername, serverip ORDER BY servername ASC";
					}
					$q_server = mysql_query($sql_server) or die(mysql_error());
					while ($r_server = mysql_fetch_array($q_server)) {
						if (isset($var['exclude']) and $r_server['id'] == $values[$var['exclude']]) continue;
						$selected = (isset($values[$var['name']]) and $r_server['id'] == $values[$var['name']]) ? 'selected' : '';
						echo '<option '.$selected.' value="'.$r_server['id'].'">'. htmlentities($r_server['servername'] .' ('. $r_server['serverip'] .')').'</option>';
					}
					echo '</select>';
					break;
				
				case 'player':
					echo '<select class="searchform" name="'. $var['name'] .'">';
					if (isset($var['extraoption'])) {
						if (isset($var['exclude']) and $var['extraoption'] == $values[$var['exclude']]) {
						} else {
							echo '<option value="'.$var['extraoption'].'">'.$var['extraoption'].'</option>';
						}
					}
					
					$where_extra = '';
					if (isset($var['whereisbanned'])) {
						$where_extra .= " AND pi.banned = '". $var['whereisbanned'] ."' ";
					}
					if (!empty($_REQUEST['playerfilter'])) {
						$where_extra .= " AND pi.name LIKE '%". my_addslashes($_REQUEST['playerfilter']) ."%' ";
					}

					$sql_player = "SELECT pi.id, pi.name FROM uts_pinfo pi WHERE 1 $where_extra ORDER BY pi.name ASC";
					if (isset($var['wherematch'])) {
						$sql_player = "SELECT pi.id, pi.name FROM uts_player p, uts_pinfo pi WHERE p.pid = pi.id AND p.matchid = '". $values[$var['wherematch']] ."' $where_extra GROUP BY p.id ORDER BY pi.name ASC";
					}
					if (isset($var['whereserver'])) {
						$r_server = small_query("SELECT servername, serverip FROM uts_match WHERE id = '". $values[$var['whereserver']] ."'");
						$sql_player = "SELECT DISTINCT pi.id, pi.name FROM uts_match m, uts_player p, uts_pinfo pi WHERE m.serverip = '".$r_server['serverip']."' AND  p.matchid = m.id AND  p.pid = pi.id $where_extra GROUP BY p.id ORDER BY pi.name ASC";
					}
					if (isset($var['wheregid'])) {
						$sql_player = "SELECT pi.id, pi.name FROM uts_player p, uts_pinfo pi WHERE p.pid = pi.id AND p.gid = '". $values[$var['wheregid']] ."' $where_extra GROUP BY p.id ORDER BY pi.name ASC";
					}
					$q_player = mysql_query($sql_player) or die(mysql_error());
					while ($r_player = mysql_fetch_array($q_player)) {
						if (isset($var['exclude']) and $r_player['id'] == $values[$var['exclude']]) continue;
						$selected = (isset($values[$var['name']]) and $r_player['id'] == $values[$var['name']]) ? 'selected' : '';
						echo '<option '.$selected.' value="'.$r_player['id'].'">'. htmlentities($r_player['name']) .'</option>';
					}
					echo '</select>';
					echo '&nbsp;&nbsp;&nbsp;&nbsp;';
					echo 'Filter: <input class="searchform" type="text" name="playerfilter" value="'. (empty($_REQUEST['playerfilter']) ? '' : $_REQUEST['playerfilter']) .'" size="6">';
					echo '  <input class="searchform" type="Submit" name="noop" value="apply">';
					break;
				
				case 'match':
					echo '<select class="searchform" name="'. $var['name'] .'">';
					if (isset($var['extraoption'])) {
						if (isset($var['exclude']) and $var['extraoption'] == $values[$var['exclude']]) {
						} else {
							echo '<option value="'.$var['extraoption'].'">'.$var['extraoption'].'</option>';
						}
					}
					
					$sql_match = "SELECT id, time, serverip, mapfile FROM uts_match ORDER BY time DESC";
					if (isset($var['whereserver'])) {
						$r_server = small_query("SELECT servername, serverip FROM uts_match WHERE id = '". $values[$var['whereserver']] ."'");
						$sql_match = "SELECT id, time, serverip, mapfile FROM uts_match WHERE serverip = '".$r_server['serverip']."' ORDER BY time DESC";
					}
					if (isset($var['wheregid'])) {
						$sql_match = "SELECT id, time, serverip, mapfile FROM uts_match WHERE gid = '". $values[$var['wheregid']] ."' ORDER BY time DESC";
					}
					if (isset($var['whereplayer'])) {
						$sql_match = "SELECT m.id AS id, m.time AS time, m.serverip AS serverip, m.mapfile AS mapfile FROM uts_match m, uts_player p WHERE pid = '". $values[$var['whereplayer']] ."' AND p.matchid = m.id ORDER BY time DESC";
					}
					$q_match = mysql_query($sql_match) or die(mysql_error());
					while ($r_match = mysql_fetch_array($q_match)) {
						if (isset($var['exclude']) and $r_match['id'] == $values[$var['exclude']]) continue;
						$selected = (isset($values[$var['name']]) and $r_match['id'] == $values[$var['name']]) ? 'selected' : '';
						echo '<option '.$selected.' value="'.$r_match['id'].'">'. htmlentities($r_match['id'].': '.mdate2($r_match['time']).' ('.un_ut($r_match['mapfile']).' on '.$r_match['serverip'].')').'</option>';
					}
					echo '</select>';
					break;
					
				case 'static':
					echo '<select class="searchform" name="'. $var['name'] .'">';
					if (isset($var['extraoption'])) {
						if (isset($var['exclude']) and $var['extraoption'] == $values[$var['exclude']]) {
						} else {
							echo '<option value="'.$var['extraoption'].'">'.$var['extraoption'].'</option>';
						}
					}
					
					$sopts = explode('|', $var['options']);
					foreach($sopts as $sval) {
						$selected = (isset($values[$var['name']]) and $sval == $values[$var['name']]) ? 'selected' : '';
						echo '<option '.$selected.' value="'.$sval.'">'. htmlentities($sval) .'</option>';
					}
					echo '</select>';
					break;
					
				case 'text':
					$pval = (isset($values[$var['name']])) ? $values[$var['name']] : '';
					echo '<input type="text" class="searchform" name="'. $var['name'] .'" value="'. $pval .'">';
					break;
					
					
				default:
					echo 'Select: Don\'tknow what to do with type '. $var['type'];
			}
		}
		echo '</td></tr>';
	}
	
	$valstr = '';
	foreach($values as $key => $value) {
		if (empty($key)) continue;
		if (!empty($valstr)) $valstr .= ',';
		$valstr .= "$key=>$value";
	}
	
	
	$_REQUEST['step'] = '';
	$_REQUEST['values'] = '';
	foreach($_REQUEST as $key => $value) {
		if (isset($_COOKIE[$key])) continue;
		switch($key){
			case 'step':
				$value = $step; break;
			case 'values':
				$value = $valstr; break;
			case 'submit':
			case 'back':
			case 'cur_var':
			case 'playerfilter':
			case 'noop':
				continue 2;
		}
		echo '<input type="hidden" name="'.$key.'" value="'.$value.'">';
	}
	
	echo '<tr><td>';
	if ($step != 1) echo '<input class="searchformb" type="submit" name="back" value="&lt;&lt; Back">';
	echo '</td>';
	$caption = ($step == $maxsteps) ? 'Finish' : 'Next &gt;&gt;';
	echo '<td align="right"><input class="searchformb" type="submit" name="submit" value="'.$caption.'"></td></tr>';
	
	echo '</table>';

	
	echo '</form>';
	require('includes/footer.php');
	exit;
}


?>