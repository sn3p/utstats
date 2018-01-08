<?php
/*
  This function is to get the players from a clan.
  It will return an array with all the information.
*/

function get_players($cid) {
  //Load the page where all players are listed into a var.

  $url = 'http://www.clanbase.com/claninfo.php?cid='.$cid.'&frame=1';
  $file = implode('', file($url));


  //Use an expression to get the pid + name from each player

  preg_match_all("/personinfo\.php\?pid+=(.*)(<.a>|&nbsp;)/U", $file, $out, PREG_SET_ORDER);


  //for each player split the information and put all info in one array

  for ($x=0;$x<count($out);$x++) {
    $player[$x]=explode('" class="slink">',$out[$x][1]);
  }

  //return the array

  return ($player);

  //All players are listed as:
  //$player[0]
  //$player[1]
  //$player[2]
  //etc.

  //the info for each player is listed as:
  //$player[0][0] = pid;
  //$player[0][1] = name;
}


/*
  This function is to get the clans from a war.
  It will return an array with all the information.
*/

function get_clans($wid) {
  //Load the page where all players are listed into a var.

  $url = 'http://www.clanbase.com/warinfo.php?wid='.$wid.'&frame=1';
  $file = implode('', file($url));

  //Use an expression to get the tags from each clan

  preg_match_all("/pagetitle'>Match (.*)vs (.*)<.div>/U", $file, $out, PREG_SET_ORDER);

  //Add the information to one array

  $clan[0][0]=$out[0][1];
  $clan[1][0]=$out[0][2];

  //Use an expression to get the cid + full name from each clan

  preg_match_all("/claninfo\.php\?cid+=(.*)<.a>/U", $file, $out, PREG_SET_ORDER);

  //Add the information to one array

  $temp = explode('" class="slink">', $out[0][1]);
  $clan[0][1]=$temp[0];
  $clan[0][2]=$temp[1];

  $temp = explode('" class="slink">', $out[1][1]);
  $clan[1][1]=$temp[0];
  $clan[1][2]=$temp[1];

  //return the array

  return ($clan);


  //The clans are listed as:
  //$clan[0]
  //$clan[1]

  //the info for each clan is listed as:
  //$clan[0][0] = tag
  //$clan[0][1] = cid
  //$clan[0][2] = full name

}
?>