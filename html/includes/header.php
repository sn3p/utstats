<?php
// Start buffering
ob_start();

// Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

// Always modified
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Content-Type: text/html; charset=utf-8");

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
setcookie('uts_lastvisit', time(), time() + 60 * 60 * 24 * 30 * 365);

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>Unreal Tournament Stats - Powered by UTStats</title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <link rel="icon" href="assets/images/favicon.ico" type="image/ico">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/lib/tooltipster/tooltipster.css">
    <link rel="stylesheet" href="assets/style.css">
  </head>
  <body>';

include 'includes/navbar.php';
