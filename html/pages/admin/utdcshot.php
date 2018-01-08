<?php
$filename = (!empty($_REQUEST['filename'])) ? stripslashes($_REQUEST['filename']) : '';
$filename = str_replace(array('..', '/', '\\', '<', ':'), array(), $filename);


if (!empty($filename)) {
	if (!file_exists('../../logs/utdc/'. $filename) or !is_file('../../logs/utdc/'. $filename)) die('bla ' . $filename);

	header("Content-type: image/jpg");
 
	$fp_in = fopen('../../logs/utdc/'. $filename, 'rb') or die("Can't open file");
	$blocksize = 1024;
	while (!feof($fp_in)) {
		$buffer .= @fread($fp_in, $blocksize);
		if ($buffer === false) return(false);
		if ($bytes === false) return(false);
	}

	$buffer = preg_replace('[\x00]', '', $buffer);
	echo base64_decode($buffer);
}
?>