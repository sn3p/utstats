<?php
//require_once('config.php');

DeBugMessage("Execute: includes/ftp.php\nUse ftp_type: $ftp_type");

if ($ftp_type == 'php') {
  require(dirname(__FILE__) . '/ftp_class_native.php');
} else {
  require(dirname(__FILE__) . '/ftp_class.php');
}

$g_ftp_error = false;

function ftp_error($message) {
  global $html, $g_ftp_error, $ftp;

  $g_ftp_error = true;

  tablerow('ERROR:', $message, true);

  while(($err = $ftp->PopError()) !== false) {
    $fctname = $err['fctname'];
    $msg = $err['msg'];
    $desc = $err['desc'];

    if ($desc) $tmp=' ('.$desc.')'; else $tmp='';

    if (strpos($msg, 'socket set') === 0) {
      $tmp .= "\nTry disabling the usage of sockets (set \$ftp_type = 'pure'; in config.php)";
    }

    tablerow('Error details:', $fctname.': '.$msg.$tmp, true);
  }
}

function tablerow($left, $right, $error=false) {
  global $html, $ftp_debug;

  if ($ftp_debug) return;

  $space = ($html) ? '&nbsp;' : ' ';

  $left = (empty($left)) ? $space : (($html) ? htmlentities($left) : $left);
  $right = (empty($right)) ? $space : (($html) ? nl2br(htmlentities($right)) : $right);

  $style = ($error) ? 'style="background-color: red;"' : '';

  if ($html) {
    echo '<tr>';
    echo '<td class="smheading" '. $style .' align="left" width="170">'. $left .'</td>';
    echo '<td class="grey" '. $style .' align="left" width="380">'. $right .'</td>';
    echo '</tr>';
  } else {
    if (strlen($left) < 30) $left .= str_repeat(" ", 30 - strlen($left));

    echo "$left $right\n";
  }

  flush();
}

function ftpupdate() {
  global  $html, $ftp, $ftp_uname, $ftp_upass, $ftp_hostname, $ftp_port, $g_ftp_error, $ftp_debug,

  $ftp_delete, $ftp_movedir, $ftp_dir, $ftp_passive, $import_log_start, $import_log_extension,

  $import_utdc_download_enable, $import_utdc_log_start, $import_utdc_log_extension, $import_utdc_screenshot_start, $import_utdc_screenshot_extension,
  $import_ac_download_enable, $import_ac_log_start, $import_ac_log_extension,
  $import_ace_download_enable, $import_ace_log_start, $import_ace_log_extension, $import_ace_screenshot_start, $import_ace_screenshot_extension;

  if (!$ftp_debug) {
    if ($html) echo'<table class="zebra box" border="0" cellpadding="0" cellspacing="0" style="table-layout:fixed"><tr><td class="smheading" align="center" height="25" width="550" colspan="2">';

    echo "FTP Transferring Log Files...\n";

    if ($html) echo '</td></tr>';
  }

  // Update, from here on were going to be doing multiple FTP sessions.
  for ($i = 0; $i < count($ftp_hostname); $i++) {
    if ($i != 0) {
      if ($html and !$ftp_debug) echo '<tr><td align="center" height="25" width="550" colspan="2"></td></tr>';

      echo "\n";
    }

    tablerow('Connecting to server:', $ftp_hostname[$i] .':'. $ftp_port[$i]);

    if (!$ftp->SetServer($ftp_hostname[$i], $ftp_port[$i])) {
      ftp_error("Unable to set server: ". $ftp->lastmsg); $ftp->quit(true); continue;
    }

    if (!$ftp->connect()) {
      ftp_error("Unable to connect to server: ". $ftp->lastmsg); $ftp->quit(true); continue;
    }

    tablerow('', "Connected, now logging in...");

    if (!$ftp->login($ftp_uname[$i], $ftp_upass[$i])) {
      ftp_error("Login failed!\nBad username/password?"); $ftp->quit(true); continue;
    }

    tablerow('', "Logged in!");

    if (!$ftp->SetType(FTP_BINARY)) {
      ftp_error("Could not set type: ". $ftp->lastmsg); $ftp->quit(true); continue;
    }

    if (!isset($ftp_passive[$i]) or $ftp_passive[$i]) {
      tablerow("", "Setting passive mode");

      if (!$ftp->Passive(true)) {
        ftp_error("Could not set passive mode: ". $ftp->lastmsg); $ftp->quit(true); continue;
      }
    } else {
      tablerow("", "Setting active mode");

      if (!$ftp->Passive(false)) {
        ftp_error("Could not set active mode: ". $ftp->lastmsg); $ftp->quit(true); continue;
      }
    }

    if (($pwd = $ftp->pwd()) === false) {
      ftp_error("Unable to retrieve current working directory"); $ftp->quit(true); continue;
    }

    tablerow("Current directory is:", $pwd);

    $dl_start = time();
    $dl_files = 0;
    $dl_bytes = 0;
    $error = false;

    foreach($ftp_dir[$i] as $dir) {
      if (!empty($dir)) {
        if (!$ftp->chdir($dir)) {
          ftp_error("Unable to change directory to: $dir"); $ftp->quit(true); continue;
        }

        tablerow('', "Changing directory to: $dir");

        if (($pwd = $ftp->pwd()) === false) {
          ftp_error("Unable to retrieve current working directory"); $ftp->quit(true); continue;
        }

        tablerow("New directory is:", $pwd);
      }

      if (($filelist = $ftp->nlist()) === false) {
        ftp_error("Unable to retrieve file list"); continue;
      }

      tablerow("Directory contains:", count($filelist) ." ". ((count($filelist) == 1) ? 'file' : 'files'));

      if (count($filelist) == 0) {
        continue;
      }

      foreach ($filelist as $filename) {
        if (((substr($filename, 0, strlen($import_log_start)) == $import_log_start) and (substr($filename, strlen($filename) - strlen($import_log_extension)) == $import_log_extension))
        or ($import_utdc_download_enable and (substr($filename, 0, strlen($import_utdc_log_start)) == $import_utdc_log_start) and (substr($filename, strlen($filename) - strlen($import_utdc_log_extension)) == $import_utdc_log_extension))
        or ($import_utdc_download_enable and (substr($filename, 0, strlen($import_utdc_screenshot_start)) == $import_utdc_screenshot_start) and (substr($filename, strlen($filename) - strlen($import_utdc_screenshot_extension)) == $import_utdc_screenshot_extension))
        or ($import_ac_download_enable and (substr($filename, 0, strlen($import_ac_log_start)) == $import_ac_log_start) and (substr($filename, strlen($filename) - strlen($import_ac_log_extension)) == $import_ac_log_extension))
        or ($import_ace_download_enable and (substr($filename, 0, strlen($import_ace_log_start)) == $import_ace_log_start) and (substr($filename, strlen($filename) - strlen($import_ace_log_extension)) == $import_ace_log_extension))
        or ($import_ace_download_enable and (substr($filename, 0, strlen($import_ace_screenshot_start)) == $import_ace_screenshot_start) and (substr($filename, strlen($filename) - strlen($import_ace_screenshot_extension)) == $import_ace_screenshot_extension))) {
        } else {
          continue;
        }

        $size = $ftp->get($filename, 'logs/' . $filename);

        if ($size === FALSE) {
          $result = 'ERROR!';
          $error = true;

          if (file_exists('logs/' . $filename)) {
            unlink('logs/' . $filename);
          }
        } else {
          $result = "OK (". number_format(round(($size / 1024), 0)) ." KB)";
          $dl_files++;
          $dl_bytes += $size;
        }

        tablerow(($dl_files == 1) ? 'Downloading...' : '', "$filename -> $result");

        if ((!isset($ftp_delete[$i]) or $ftp_delete[$i]) and !$error) {
          $ftp->delete($filename);
        } else {
          // rename the file to prevent reimporting
          $ftp->rename($filename, "~" . $filename);
        }
      }
    }

    $dl_kb = number_format(round(($dl_bytes / 1024), 0));
    $dl_time = time() - $dl_start;

    tablerow("Downloaded:", "$dl_files ". ((count($filelist) == 1) ? 'file' : 'files') ." ($dl_kb KB) in $dl_time seconds");

    if ($error) {
      ftp_error('There were errors when downoading (some) files!');
    }

    tablerow("Disconnecting...", "done!");

    $ftp->quit(true);
  }

  if (!$ftp_debug and $html) echo '</table><br />';

  echo "\n\n";

  //update timestamp
  if (!$g_ftp_error) {
    $file = fopen('includes/ftptimestamp.php', 'wb+', 1);
    fwrite($file, time());
    fclose($file);
  }
}

$fname = 'includes/ftptimestamp.php';
$timestamp = 0;

if (file_exists($fname)) {
  $file = fopen($fname, 'rb');
  $timestamp = trim(my_fgets($file));
  fclose($file);
}

if (!$timestamp || (time() - $timestamp) > $ftp_interval*60) {
  if ($timestamp) {
    if ($html) echo '<p class="pages">';

    echo "Last FTP update more than $ftp_interval minutes ago, starting update ($ftp_type): \n";

    if ($html) echo '</p>';
  }

  if ($ftp_debug) {
    if ($html) echo '<table class="box" border="0"><tr><td class="smheading" width="550">';

    echo "FTP Debugging Output:\n";

    if ($html) echo '</td></tr><tr><td width="550" align="left"><pre>';
  }

  $ftp = new ftp($ftp_debug, $ftp_debug);
  ftpupdate();

  if ($ftp_debug and $html) echo '</pre></td></tr></table><br />';
} else {
  if ($html) echo '<p class="pages">';

  echo "Last FTP update was ". round(((time() - $timestamp) / 60), 0) ." minutes ago, no update necessary\n";

  if ($html) echo '</p>';
}

?>
