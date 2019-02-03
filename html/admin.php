<?php
if (isset($_REQUEST['rememberkey'])) {
	setcookie('uts_adminkey', $_REQUEST['key'], time() + 60 * 60 * 24 * 30 * 365);
}
if (isset($_COOKIE['uts_adminkey'])) {
	$adminkey = $_REQUEST['uts_adminkey'];
}

require 'includes/config.php';
require 'includes/functions.php';
require 'includes/functions_admin.php';
require 'includes/header.php';

// Get key from web browser
if (isset($_REQUEST['key'])) {
	$adminkey = $_REQUEST['key'];
}

if (!isset($adminkey)) {
	$adminkey = '';
}

$action = (!empty($_REQUEST['action'])) ? $_REQUEST['action'] : 'main';

echo '
          <table class = "box" border="0" cellpadding="1" cellspacing="2" width="100%">
            <tr>
	          <td class="heading text-center" colspan="2">UTStats Administration</td>
            </tr>';

if (empty($import_adminkey)) {
	echo '
	        <tr>
	          <td class="smheading" align="left" width="150">Error:</td>
	          <td class="grey" align="left">No key set in config.php</td>
	        </tr>
	      </table>';
	include 'includes/footer.php';
	return;
}


if (!empty($adminkey) and $adminkey != $import_adminkey) {
	echo '
	        <tr>
	          <td class="smheading" align="left" width="150">Error:</td>
	          <td class="grey" align="left">Keys do not match</td>
	        </tr>';
	$adminkey = '';
}

if (empty($adminkey)) {
	echo '
	        <tr>
		      <td class="smheading" align="left" width="150">Enter Admin key:</td>
		      <td class="grey" align="left">
		        <form NAME="adminkey" ACTION="admin.php">
		          <input TYPE="text" NAME="key" MAXLENGTH="35" SIZE="20" CLASS="searchform">
		          <input TYPE="submit" VALUE="Submit" CLASS="searchformb">
		          <input TYPE="checkbox" NAME="rememberkey"> Remember the key
		        </form>
		      </td>
	        </tr>
	      </table>';
	include 'includes/footer.php';
	return;
}

$action = str_replace(array('.', '/', '<', ':'), array(), $action);
$fn = "pages/admin/$action.php";

if (!file_exists($fn) or !is_file($fn)) {
	die('bla');
}

require $fn;

echo '</table>
 <br>';

require 'includes/footer.php';

?>
