<?php
// Start buffering
ob_start();

// Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

// always modified
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

// HTTP/1.0
header("Pragma: no-cache");

if (isset($_COOKIE['uts_lastvisit'])) {
	if (isset($_COOKIE['utss_lastvisit'])) {
		$s_lastvisit = $_COOKIE['utss_lastvisit'];
	} else {
		setcookie('utss_lastvisit', $_COOKIE['uts_lastvisit'], 0);
		$s_lastvisit = $_COOKIE['uts_lastvisit'];
	}
} else {
	$s_lastvisit = time();
}
setcookie('uts_lastvisit', time(), time()+60*60*24*30*365);


echo'
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title>Unreal Tournament Stats - Powered by UTStats</title>
  <meta http-equiv="Content-Type" content="text/html; CHARSET=iso-8859-1">
  <link rel="icon" href="images/favicon.ico" type="image/ico">
  <link rel="stylesheet" href="style.css">
  <script type="text/javascript">
  <!--
    var ol_fgclass="dark"; var ol_bgclass="darkbox"; var ol_textfontclass="dark"; var ol_captionfontclass="hlheading";
  -->
  </script>
  <script type="text/javascript" src="includes/overlib_mini.js"><!-- overLIB (c) Erik Bosrup --></script>
</head>
<body>

<table border="0" cellpadding="0" cellspacing="0">
<tbody>
<tr>
';

include("includes/sidebar.php");

echo'
<table align="center" border="0" cellpadding="0" cellspacing="0">
  <tbody><tr>
    <td align="center" width="720">
      <img src="images/statslogo.gif" title="UTStats Logo" alt="UTStats Logo" border="0">
    </td>
  </tr>
</tbody></table>
<center>
<br>
';
