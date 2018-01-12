<?php
$r_info = small_query("SELECT teamgame, t0, t1, t2, t3, t0score, t1score, t2score, t3score FROM uts_match WHERE id = '$mid'");
if (!$r_info) die("Match not found");
$teamgame = ($r_info['teamgame'] == 'True') ? true : false;

include("pages/match_info_server.php");

$GLOBALS['gid'] = $gid;
$_GLOBALS['gid'] = $gid;
$GLOBALS['gamename'] = $gamename;
$_GLOBALS['gamename'] = $gamename;

include("includes/teamstats.php");
include("pages/match_info_charts.php");

switch($real_gamename) {
  case "Assault":
  case "Assault (insta)":
    include("pages/match_info_ass.php");
    break;

  case "Capture the Flag":
  case "Capture the Flag (insta)":
    include("pages/match_info_ctf.php");
    teamstats($mid, 'Match Summary');
      break;

  case "Domination":
  case "Domination (insta)":
    teamstats($mid, 'Match Summary', 'dom_cp', 'Dom Pts');
    break;

  case "JailBreak":
  case "JailBreak (insta)":
    teamstats($mid, 'Match Summary', 'ass_obj', 'Team Releases');
    break;

  case "Bunny Track":
    include("pages/match_info_bt.php");
    break;

  case "Tournament DeathMatch":
  case "Tournament Team Game":
  case "Tournament DeathMatch (insta)":
  case "Tournament Team Game (insta)":
    teamstats($mid, 'Match Summary');
    break;

  case "Extended Last Man Standing":
  case "Extended Last Man Standing (insta)":
  case "Last Man Standing":
  case "Last Man Standing (insta)":
  case "Last Man Standing +":
  case "Last Man Standing + (insta)":
  case "Last Man Standing++":
  case "Last Man Standing++ (insta)":
    include("pages/match_info_lms.php");
    break;

  default:
    if ($teamgame) {
      teamstats($mid, 'Match Summary');
     } else {
      teamstats($mid, 'Player Summary');
    }
}

if ($real_gamename == "Assault" or $real_gamename == "Assault (insta)") {
  include("pages/match_info_other2.php");
}
else if ($real_gamename != "Bunny Track") {
  include("pages/match_info_other.php");
}

if ($real_gamename == "Capture the Flag" or $real_gamename == "Capture the Flag (insta)") {
  include("pages/match_report.php");
}

?>
