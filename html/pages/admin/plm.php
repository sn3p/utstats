<?php 
if (empty($import_adminkey) or isset($_REQUEST['import_adminkey']) or $import_adminkey != $adminkey) die('bla');

$debug = 0; // This enables debug messages

/*
** This function merges two playerss
** @var $mplayer1 : The player to merge to
** @var $mplayer2 : The player who gets merged into mplayer 1
** @return null
*/
function merge_players($mplayer1, $mplayer2) {
    $mp1name = small_query("SELECT name FROM uts_pinfo WHERE id = $mplayer1");
    $mp2name = small_query("SELECT name FROM uts_pinfo WHERE id = $mplayer2");
    
    mysql_query("DELETE FROM uts_pinfo WHERE id = $mplayer2") or die(mysql_error());
    mysql_query("UPDATE uts_player SET pid = $mplayer1  WHERE pid = $mplayer2") or die(mysql_error());
    mysql_query("UPDATE uts_weaponstats SET pid = $mplayer1  WHERE pid = $mplayer2") or die(mysql_error());
    mysql_query("DELETE FROM uts_weaponstats WHERE pid = $mplayer2") or die(mysql_error());
    mysql_query("DELETE FROM uts_weaponstats WHERE matchid='0' AND pid = '$mplayer1'") or die(mysql_error());
    
    $q_weaponstats = mysql_query("SELECT weapon, SUM(kills) AS kills, SUM(shots) AS shots, SUM(hits) as hits, SUM(damage) as damage, AVG(acc) AS acc FROM uts_weaponstats WHERE pid = '$mplayer1'  GROUP BY weapon") or die(mysql_error());
    while ($r_weaponstats = mysql_fetch_array($q_weaponstats)) {
        mysql_query("INSERT INTO uts_weaponstats SET matchid='0', pid='$mplayer1',  weapon='${r_weaponstats['weapon']}', kills='${r_weaponstats['kills']}', shots='${r_weaponstats['shots']}', hits='${r_weaponstats['hits']}', damage='${r_weaponstats['damage']}', acc='${r_weaponstats['acc']}'") or die(mysql_error());
    }
    mysql_query("UPDATE uts_match SET firstblood = $mplayer1  WHERE firstblood = $mplayer2") or die(mysql_error());
    mysql_query("UPDATE uts_rank SET pid = $mplayer2 WHERE pid= $mplayer1") or die(mysql_error());
    $sql_nrank = "SELECT SUM(time) AS time, pid, gid, AVG(rank) AS rank, AVG(prevrank) AS prevrank, SUM(matches) AS matches FROM uts_rank WHERE pid = $mplayer2 GROUP BY pid, gid";
    $q_nrank = mysql_query($sql_nrank) or die(mysql_error());
    while ($r_nrank = mysql_fetch_array($q_nrank)) {
    
        mysql_query("INSERT INTO uts_rank SET time = '$r_nrank[time]', pid = $mplayer1, gid = $r_nrank[gid], rank = '$r_nrank[rank]', prevrank = '$r_nrank[prevrank]', matches = $r_nrank[matches]") or die(mysql_error());
    }
    mysql_query("DELETE FROM uts_rank WHERE pid = $mplayer2") or die(mysql_error());
}

// If debugmode is on, get start time
if($debug) {
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$begintime = $time;
}

// Print header & links
echo '<P><B>Player merging tool</B></P>';
echo "<P><A href=admin.php?key=".$adminkey."&action=plm>Merge ip's</A> - <A href=admin.php?key=".$adminkey."&action=plm&onlyrange=true>Merge ip's, limited on range</A> - <A href=admin.php?key=".$adminkey."&action=plm&manignore=true>Manage ignored ip's</A> - <A href=admin.php?key=".$adminkey."&action=plm&manignore=true&onlyrange=true>Manage ignored ip's - only range</A><BR><BR></P>";

// CASE: MANAGE IGNORED IPS
if($_GET['manignore'] == "true") {
	if($_POST['submit'] == "Remove from ignored list") {
    	echo "<P><B>Stopped ignore ip's</B></P>";
        foreach($_POST as $key=>$value) {
            if($key !='submit') {
                $key = mysql_real_escape_string(str_replace("_",".",$key));
                $query = "DELETE FROM uts_ignoreips WHERE ip = (INET_ATON('".$key."'))";
                mysql_query($query) or die(mysql_error());
                echo "<br>$key";
            }
        }
	} else if($_GET['onlyrange'] == "true") {
    	if($_POST['submit'] == "Confirm") {
			echo '<P><B>Ignored ip\'s in range '.htmlentities($_POST['from']).' to '.htmlentities($_POST['to']).'</B><br><I>If you want to stop ignoring some ip\'s, because for example you accidently ignored these, check these and press the button at the lower end to confirm this</I></P>';
			echo '<FORM METHOD="POST" ACTION="admin.php?key='.$adminkey.'&action=plm&manignore=true" target="_blank">';    		
			
			$from = mysql_real_escape_string($_POST['from']);
    		$to = mysql_real_escape_string($_POST['to']);
    		
    	    $ignore_ips = mysql_query("SELECT ip FROM uts_ignoreips WHERE ip >= INET_ATON('$from') AND ip <= INET_ATON('$to') ORDER BY ip ASC");
	
	        if(mysql_num_rows($ignore_ips) > 0) {
		        while ($r_pipcheck = mysql_fetch_array($ignore_ips)) {
		            
		            $playerip = $r_pipcheck[ip];
		            $trueplayerip = long2ip($playerip);
		            $pidcount = $r_pipcheck[pidcount];
		            
		                echo "<br><input type=checkbox name=$trueplayerip> <b>$trueplayerip</b>";
		                
		                // Query for player names and ids associated to that ip during the cycle
		                $sql_pcheck = "SELECT pi.id, pi.name, pi.country, p.pid, p.ip FROM uts_pinfo AS pi, uts_player AS p WHERE pi.id = p.pid AND p.ip = $playerip GROUP BY pi.id, pi.name, p.pid, p.ip, pi.country";
		                $q_pcheck = mysql_query($sql_pcheck) or die(mysql_error());
		                while ($r_pcheck = mysql_fetch_array($q_pcheck)) {
		                    echo '<br><a class="darkhuman" href="admin.php?key='.$adminkey.'&amp;action=pinfo&amp;pid='.$r_pcheck[pid].'">'.FormatPlayerName($r_pcheck[country], $r_pcheck['pid'], $r_pcheck['name']).'</a> ';
		                }
		                echo '<br />';
		        }
		        echo '<BR><INPUT TYPE="SUBMIT" VALUE="Remove from ignored list" NAME="submit"></FORM>';
		    } else {
		    	echo "<BR><P>No ignored ip's found</P>";
		    }
    	} else {
    		echo "<P><B>Ignored ip's in range: Enter range to filter on</B></P>";
        	echo '<FORM METHOD="POST" ACTION="admin.php?key='.$adminkey.'&action=plm&manignore=true&onlyrange=true">';
        	echo '<BR>Ranging from <INPUT TYPE="TEXT" NAME="from" SIZE="20" VALUE="0.0.0.0"> to <INPUT TYPE="TEXT" NAME="to" SIZE="20" VALUE="255.255.255.255">';
    		echo '<BR><BR><INPUT TYPE="SUBMIT" NAME="submit" VALUE="Confirm"></FORM>';
    	}		
	} else {
		echo '<P><B>Ignored ip\'s</B><br><I>If you want to stop ignoring some ip\'s, because for example you accidently ignored these, check these and press the button at the lower end to confirm this</I></P>';
		echo '<FORM METHOD="POST" ACTION="admin.php?key='.$adminkey.'&action=plm&manignore=true" target="_blank">';
		
        $ignore_ips = mysql_query("SELECT ip FROM uts_ignoreips ORDER BY ip ASC");
        if(mysql_num_rows($ignore_ips) > 0) {
	        while ($r_pipcheck = mysql_fetch_array($ignore_ips)) {
	            
	            $playerip = $r_pipcheck[ip];
	            $trueplayerip = long2ip($playerip);
	            $pidcount = $r_pipcheck[pidcount];
	            
	                echo "<br><input type=checkbox name=$trueplayerip> <b>$trueplayerip</b>";
	                
	                // Query for player names and ids associated to that ip during the cycle
	                $sql_pcheck = "SELECT pi.id, pi.name, pi.country, p.pid, p.ip FROM uts_pinfo AS pi, uts_player AS p WHERE pi.id = p.pid AND p.ip = $playerip GROUP BY pi.id, pi.name, p.pid, p.ip, pi.country";
	                $q_pcheck = mysql_query($sql_pcheck) or die(mysql_error());
	                while ($r_pcheck = mysql_fetch_array($q_pcheck)) {
	                    echo '<br><a class="darkhuman" href="admin.php?key='.$adminkey.'&amp;action=pinfo&amp;pid='.$r_pcheck[pid].'">'.FormatPlayerName($r_pcheck[country], $r_pcheck['pid'], $r_pcheck['name']).'</a> ';
	                }
	                echo '<br />';
	        }
	        echo '<BR><INPUT TYPE="SUBMIT" VALUE="Remove from ignored list" NAME="submit"></FORM>';
	    } else {
	    	echo "<BR><P>No ignored ip's found</P>";
	    }
    }
    
// CASE: IGNORE SUBMITTED IP'S
} else if($_POST['submit'] == "ignore") {
	echo "<P><B>Ignored ips</B></P>";
    foreach($_POST as $key=>$value) {
        if($key !='submit') {
            $key = mysql_real_escape_string(str_replace("_",".",$key));
            $query = "INSERT INTO uts_ignoreips (ip) VALUES (INET_ATON('".$key."'))";
            mysql_query($query) or die(mysql_error());
            echo "<br>$key";
        }
    }
    echo "<br>";
    
// CASE: SHOW NICKS WITH GIVEN SHARED IP   
} else if(substr($_POST['submit'],0,8) == "merge - ") {
	echo "<P><B>Merge nicks with shared ip</B></P>";
	
    $ip = mysql_real_escape_string(str_replace("_",".",substr($_POST['submit'],8)));
    $sql_pipcheck = "SELECT ip, COUNT(DISTINCT pid) AS pidcount FROM uts_player WHERE ip = INET_ATON('$ip') GROUP BY ip ORDER BY ip ASC";
    $q_pipcheck = mysql_query($sql_pipcheck) or die(mysql_error());
    while ($r_pipcheck = mysql_fetch_array($q_pipcheck)) {
        
        $playerip = $r_pipcheck[ip];
        $trueplayerip = long2ip($playerip);
        $pidcount = $r_pipcheck[pidcount];
        
        // If there is more than one pid associated to an IP ...
        IF ($pidcount > 1 ) {
            echo '<FORM METHOD="POST" ACTION="admin.php?key='.$adminkey.'&action=plm">';
            echo "<br><b>$trueplayerip</b>";
            
            // Query for player names and ids associated to that ip during the cycle
            $sql_pcheck = "SELECT pi.id, pi.name, pi.country, p.pid, p.ip FROM uts_pinfo AS pi, uts_player AS p WHERE pi.id = p.pid AND p.ip = $playerip GROUP BY pi.id, pi.name, p.pid, p.ip, pi.country";
            
            $q_pcheck = mysql_query($sql_pcheck) or die(mysql_error());
            while ($r_pcheck = mysql_fetch_array($q_pcheck)) {
                echo '<br><a class="darkhuman" href="./?p=pinfo&amp;pid='.$r_pcheck[pid].'">'.FormatPlayerName($r_pcheck[country], $r_pcheck['pid'], $r_pcheck['name']).'</a> ';
                $options .= '<OPTION value="'.$r_pcheck[pid].'">'.$r_pcheck['name'].'</OPTION>';
            }
            echo '<br><br>Merge to: <SELECT NAME="merge_to">'.$options.'</SELECT>';
            echo "<br><INPUT TYPE=\"hidden\" NAME=\"ip\" VALUE=\"$ip\"><INPUT TYPE=\"SUBMIT\" VALUE=\"Player merge\" NAME=\"submit\"></FORM>";
            echo '<br />';
        }
    }
        
// CASE: MERGE NICKS WITH SHARED IP              
} else if($_POST['submit'] == "Player merge") {
	echo "<P><B>Merging nicks with shared ip</B></P>";
	
    $ip = mysql_real_escape_string(str_replace("_",".",$_POST['ip']));
    $merge_to_pid = mysql_real_escape_string($_POST['merge_to']);
    $sql_pipcheck = "SELECT ip, COUNT(DISTINCT pid) AS pidcount FROM uts_player WHERE ip = INET_ATON('$ip') GROUP BY ip ORDER BY ip ASC";
    $q_pipcheck = mysql_query($sql_pipcheck) or die(mysql_error());
    while ($r_pipcheck = mysql_fetch_array($q_pipcheck)) {
        
        $playerip = $r_pipcheck[ip];
        $trueplayerip = long2ip($playerip);
        $pidcount = $r_pipcheck[pidcount];
        
        // If there is more than one pid associated to an IP ...
        IF ($pidcount > 1 ) {
            echo "<b>$trueplayerip</b><br><br>merge:<br>";
            
            // Query for player names and ids associated to that ip during the cycle
            $sql_pcheck = "SELECT p.pid FROM uts_pinfo AS pi, uts_player AS p WHERE pi.id = p.pid AND p.ip = $playerip GROUP BY pi.id, pi.name, p.pid, p.ip, pi.country";
            
            $q_pcheck = mysql_query($sql_pcheck) or die(mysql_error());
            $i=0;
            while ($r_pcheck = mysql_fetch_array($q_pcheck)) {
                if($r_pcheck['pid'] != $merge_to_pid) {
                    $pid_from[$i] = $r_pcheck['pid'];
                    echo $pid_from[$i].'<br>';
                    $i++;
                }
            }
            echo '<br />merge to: '.$merge_to_pid;
        }
    }
    if($debug) echo "<br> -- started merging";
    for($j=0;$j<count($pid_from);$j++) {
        merge_players($merge_to_pid, $pid_from[$j]);
    }
    if($debug) echo "<br> -- merging ended";
    
// CASE: SHOW ALL IP'S LINKED TO MORE THAN ONE NICK - ONLY RANGE    
} else if($_GET['onlyrange'] == "true") {
	if($_POST['submit'] == "Confirm") {
		echo "<P><B>Showing all ip's in range ".htmlentities($_POST['from'])." to ".htmlentities($_POST['to'])."</B><br><I>If you want to ignore some ip's, because for example different but unrelated nicks are associated with it, check these and press the button at the lower end to confirm this</I></P>";
		$from = mysql_real_escape_string($_POST['from']);
		$to = mysql_real_escape_string($_POST['to']);
		
	    $ignore_ips = mysql_query("SELECT ip FROM uts_ignoreips WHERE ip >= INET_ATON('$from') AND ip <= INET_ATON('$to')");
	    $extended_query = "WHERE ip >= INET_ATON('$from') AND ip <= INET_ATON('$to')";
	    $i=0;
	    while($ignore_ips_array = mysql_fetch_array($ignore_ips)) {
	        $ip = $ignore_ips_array[0];
	        $extended_query .= " AND ";
	        $extended_query .= "ip <> '".$ip."'";
	        $i++;
	    }
	    
	    echo '<FORM METHOD="POST" ACTION="admin.php?key='.$adminkey.'&action=plm" target="_blank">';
	    
	                
	    // Query for list of unique ips and player ids
	    $sql_pipcheck = "SELECT ip, COUNT(DISTINCT pid) AS pidcount FROM uts_player ".$extended_query." GROUP BY ip ORDER BY ip ASC";
	    $q_pipcheck = mysql_query($sql_pipcheck) or die(mysql_error());
	    if(mysql_num_rows($q_pipcheck) > 0) {
		    while ($r_pipcheck = mysql_fetch_array($q_pipcheck)) {
		        
		        $playerip = $r_pipcheck[ip];
		        $trueplayerip = long2ip($playerip);
		        $pidcount = $r_pipcheck[pidcount];
		        
		        // If there is more than one pid associated to an IP ...
		        IF ($pidcount > 1 ) {
		            
		            echo "<br><input type=checkbox name=$trueplayerip> <b>$trueplayerip</b>";
		            
		            // Query for player names and ids associated to that ip during the cycle
		            $sql_pcheck = "SELECT pi.id, pi.name, pi.country, p.pid, p.ip FROM uts_pinfo AS pi, uts_player AS p WHERE pi.id = p.pid AND p.ip = $playerip GROUP BY pi.id, pi.name, p.pid, p.ip, pi.country";
		            $q_pcheck = mysql_query($sql_pcheck) or die(mysql_error());
		            while ($r_pcheck = mysql_fetch_array($q_pcheck)) {
		                echo '<br><a class="darkhuman" href="admin.php?key='.$adminkey.'&amp;action=pinfo&amp;pid='.$r_pcheck[pid].'">'.FormatPlayerName($r_pcheck[country], $r_pcheck['pid'], $r_pcheck['name']).'</a> ';
		            }	
		            echo "<br><INPUT TYPE=\"SUBMIT\" VALUE=\"merge - $trueplayerip\" NAME=\"submit\">";
		            echo '<br />';
		        }
		    }
		    
		    
		    echo '<BR><INPUT TYPE="SUBMIT" VALUE="ignore" NAME="submit"></FORM>';	
	    } else {
			echo "<P><BR>No ip's found with more than one nick linked to it and which are not ignored and are in this range</P>";	
		}
	    	
	} else {
		echo "<P><B>Showing all ip's in range - Enter range to filter on</B></P>";
    	echo '<FORM METHOD="POST" ACTION="admin.php?key='.$adminkey.'&action=plm&onlyrange=true">';
    	echo '<BR>Ranging from <INPUT TYPE="TEXT" NAME="from" SIZE="20" VALUE="0.0.0.0"> to <INPUT TYPE="TEXT" NAME="to" SIZE="20" VALUE="255.255.255.255">';
		echo '<BR><BR><INPUT TYPE="SUBMIT" NAME="submit" VALUE="Confirm"></FORM>';
	}	

// CASE: SHOW ALL IP'S LINKED TO MORE THAN ONE NICK
} else {
	echo "<P><B>IP's linked with more than one nick</B><br><I>If you want to ignore some ip's, because for example different but unrelated nicks are associated with it, check these and press the button at the lower end to confirm this</I></P>";
	
    $ignore_ips = mysql_query("SELECT ip FROM uts_ignoreips");
    $extended_query = "";
    $i=0;
    while($ignore_ips_array = mysql_fetch_array($ignore_ips)) {
        $ip = $ignore_ips_array[0];
        if($i==0)
            $extended_query = " WHERE ";
        else
            $extended_query .= " AND ";
        $extended_query .= "ip <> '".$ip."'";
        $i++;
    }
    
    echo '<FORM METHOD="POST" ACTION="admin.php?key='.$adminkey.'&action=plm" target="_blank">';
    
                
    // Query for list of unique ips and player ids
    $sql_pipcheck = "SELECT ip, COUNT(DISTINCT pid) AS pidcount FROM uts_player ".$extended_query." GROUP BY ip ORDER BY ip ASC";
    $q_pipcheck = mysql_query($sql_pipcheck) or die(mysql_error());
    if(mysql_num_rows($q_pipcheck) > 0) {
	    while ($r_pipcheck = mysql_fetch_array($q_pipcheck)) {
	        
	        $playerip = $r_pipcheck[ip];
	        $trueplayerip = long2ip($playerip);
	        $pidcount = $r_pipcheck[pidcount];
	        
	        // If there is more than one pid associated to an IP ...
	        IF ($pidcount > 1 ) {
	            
	            echo "<br><input type=checkbox name=$trueplayerip> <b>$trueplayerip</b>";
	            
	            // Query for player names and ids associated to that ip during the cycle
	            $sql_pcheck = "SELECT pi.id, pi.name, pi.country, p.pid, p.ip FROM uts_pinfo AS pi, uts_player AS p WHERE pi.id = p.pid AND p.ip = $playerip GROUP BY pi.id, pi.name, p.pid, p.ip, pi.country";
	            $q_pcheck = mysql_query($sql_pcheck) or die(mysql_error());
	            while ($r_pcheck = mysql_fetch_array($q_pcheck)) {
	                echo '<br><a class="darkhuman" href="admin.php?key='.$adminkey.'&amp;action=pinfo&amp;pid='.$r_pcheck[pid].'">'.FormatPlayerName($r_pcheck[country], $r_pcheck['pid'], $r_pcheck['name']).'</a> ';
	            }	
	            echo "<br><INPUT TYPE=\"SUBMIT\" VALUE=\"merge - $trueplayerip\" NAME=\"submit\">";
	            echo '<br />';
	        }
	    }
    	echo '<BR><INPUT TYPE="SUBMIT" VALUE="ignore" NAME="submit"></FORM>';
    } else {
    	echo "<P><BR>No ip's found with more than one nick linked to it and which are not ignored</P>";	
    }
}

// If debugmode is on, determine end time & output execution time
if($debug) {
	$time = microtime();
	$time = explode(" ", $time);
	$time = $time[1] + $time[0];
	$endtime = $time;
	$totaltime = ($endtime - $begintime);
	echo "<br>execution time: $totaltime";
}
?>