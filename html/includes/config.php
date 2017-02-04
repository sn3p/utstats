<?php
// Database connection details
$dbname = "";
$hostname = "";
$uname = "";
$upass = "";

$dbconnect = mysql_connect($hostname,$uname,$upass);
$dbconnect2 = mysql_select_db($dbname);

// The key needed to run the import script
$import_adminkey = 'fietsbel';

// When runnning from the command-line (cron jobs):
// The absolute path to UTStats's home directory.
// Only needed if you're starting the importer from another directory
// Leave emtpy if unsure
$import_homedir = '';

// Use the MySQL temporary tables feature?
// Available since MySQL 3.23 - requires CREATE TEMPORARY TABLE privilege since 4.0.2
$import_use_temporary_tables = true;		// set to true if available

// Use temporary heap tables?
// This will (at any cost) keep the entire table in RAM and may speed and/or fuck things up
$import_use_heap_tables = false;				// DON'T USE IF YOU DON'T KNOW WHAT YOU'RE DOING!

// Log files start with...
$import_log_start = "Unreal.ngLog";

// Log files end with...
$import_log_extension = ".log";

// How to backup logfiles?
// Possible values: yes      - move logfiles to the backup directory
//                  no       - don't make a backup. The file will be lost after it was imported
//                  compress - will compress the logfile and move it to the backup directory
//                             It'll first try bzip2 compression, then gzip (your php must be
//                             compiled to support these)
//                             If both fail, it will backup the uncompressed log
//                  gzip     - same as compress but will only try to gzip the file
$import_log_backup = "yes";

// Purge old logs after x days. 0 to disable.
$import_log_backups_purge_after = 0;


// After how many seconds should we reload the import page?
// This is to prevent the 'maximum execution time exeeded' error. It will reload
// the page after the amount of seconds you specify in order to bypass php's time limit.
// Set to 0 to disable (f.e. if your php does not run in safe mode)
$import_reload_after = 22;

// Ignore bots and bot kills/deaths?
$import_ignore_bots = true;

// How to deal with banned players?
// 1 - (recommended) import the player and display him/her on matchpages (without values :D)
//     but don't include him/her in rankings and don't allow to show individual player stats
//     You may unban a player banned with this setting and all stuff will display again
// 2 - don't import at all
//     will lead to 'strange' results on matchpages because kills of and against this player
//     won't be shown; efficiency etc. will be calculated including these kills though.
$import_ban_type = 1;

// Try to import logs from previous versions of UTStats
// Set this to true and you'll probably some strange results - You've been warned ;)
$import_incompatible_logs = false;

// Don't import if the gametime was less than x minutes. Set to 0 to import all logs.
$import_ignore_if_gametime_less_than = 0;


// UTStats can download and manage your UTDC logs
// Enable downloading of UTDC logs?
$import_utdc_download_enable = false;

// Log files start with...
$import_utdc_log_start = "[UTDC]";

// Log files end with...
$import_utdc_log_extension = ".log";

// Screenshot files start with...
$import_utdc_screenshot_start = "[UTDC]";

// Screenshot files end with...
$import_utdc_screenshot_extension = ".enc";

// Compress UTDC logfiles after download? [compress/gzip/no]
// (see $import_log_backup for available options)
$import_utdc_log_compress = "compress";

// Purge old UTDC logs after x days. 0 to disable.
$import_utdc_log_purge_after = 0;

// UTStats can download and manage your AnthChecker logs
// Enable downloading of AC logs?
$import_ac_download_enable = true;

// Log files start with...
$import_ac_log_start = "[AC]";

// Log files end with...
$import_ac_log_extension = ".log";

// Compress AnthChecker logfiles after download? [compress/gzip/no]
// (see $import_log_backup for available options)
$import_ac_log_compress = "compress";

// Purge old AnthChecker logs after x days. 0 to disable.
$import_ac_log_purge_after = 0;

// UTStats can download and manage your ACE logs
// Enable downloading of ACE logs?
$import_ace_download_enable = true;

// Log files start with...
$import_ace_log_start = "[ACE]";

// Log files end with...
$import_ace_log_extension = ".log";

// Log files start with...
$import_ace_screenshot_start = "[ACE]";

// Log files end with...
$import_ace_screenshot_extension = ".jpg";

// Compress ACE logfiles after download? [compress/gzip/no]
// (see $import_log_backup for available options)
$import_ace_log_compress = "compress";

// Purge old ACE logs after x days. 0 to disable.
$import_ace_log_purge_after = 0;

// Enable the creation of pictures? (Signature pictures for users where they can see their current ranking and stuff)
// Requires GD- and FreeType support.
// see config_pic.php for picture configuration options
$pic_enable = true;


// FTP Connection Details
$ftp_use = false;			      	// Whether to auto get the log files
$ftp_interval = 0;			    	// How often in minutes to allow stats update
$ftp_type = 'sockets';				// Which FTP module do you want to use?
											// sockets - (recommended)
											//           Use PHP's socket extension to connect to the FTP server
											//           will fallback to 'pure' if no sockets available
											// pure    - Use fsockopen() to connnect to the FTP server
											//           should work with any php version
											// php     - Use PHP's FTP extension (must be compiled in)
											//           Debugging will not be available with this module and
											//           error handling may not be as good as with the other modules

$ftp_debug = false;					// Debugging output that may help you to resolve ftp problems


// UT Server 1
$i = 0;
$ftp_hostname[$i]	= '';				// FTP server location here
$ftp_port[$i] 		= 21;				// FTP Port - do not remove this even if you do not use ftp
											// Do not add '' around the port either
$ftp_uname[$i] 	= '';				// FTP Username
$ftp_upass[$i] 	= '';				// FTP Password
$ftp_dir[$i][] 	= '/';			// Directory of the log files - MUST NOT end with a /
//$ftp_dir[$i][] 	= '/';			// You may repeat this entry as often as you wish but please remember
//$ftp_dir[$i][] 	= '/';			// to enter the directory relative to the last one
//$ftp_dir[$i][] 	= '/';			// (or use an absolute path)
$ftp_passive[$i] 	= true;			// Use passive transfer mode for this connection?
$ftp_delete[$i] 	= true; 			// Delete logs after download?

/*
// UT Server 2 --- Uncomment this one if needed, or copy/paste the block above if more servers are needed.
$i++;
$ftp_hostname[$i]	= '';				// FTP server location here
$ftp_port[$i] 		= 21;				// FTP Port - do not remove this even if you do not use ftp
											// Do not add '' around the port either
$ftp_uname[$i] 	= '';				// FTP Username
$ftp_upass[$i] 	= '';				// FTP Password
$ftp_dir[$i][] 	= '/';			// Directory of the log files - MUST NOT end with a /
//$ftp_dir[$i][] 	= '/';			// You may repeat this entry as often as you wish but please remember
//$ftp_dir[$i][] 	= '/';			// to enter the directory relative to the last one
//$ftp_dir[$i][] 	= '/';			// (or use an absolute path)
$ftp_passive[$i] 	= true;			// Use passive transfer mode for this connection?
$ftp_delete[$i] 	= true; 			// Delete logs after download?
*/
?>