<?php
// Fix for UTF-8 required for local use
require_once('encoding.php');
use \ForceUTF8\Encoding;

function renderChart($target,$layout,$color,$title,$data,$labels,$categories,$width,$height,$charttype) {

	// Add categories if required
	if(!empty($categories) && count($categories)>0) {
		$categoriesString = "categories: ".json_encode($categories).", ";
		$categoriesStringPos = strpos($layout,"{",strpos($layout,"xAxis"))+1;
		if($categoriesStringPos !== false)
			$layout = substr_replace($layout,$categoriesString,$categoriesStringPos,0);
	}

	// Replace title if required
	if(!empty($title) && strlen($title)>0) {
		$titleStringPosStart = strpos($layout,":",strpos($layout,"text"))+1;
		$titleStringPosEnd = strpos($layout,"}",strpos($layout,"text"))-1;

		if($categoriesStringPos !== false)
			$layout = substr_replace($layout,json_encode($title),$titleStringPosStart,$titleStringPosEnd-$titleStringPosStart);
	}

	$chart = "
		<div id='$target' style='width: ".$width."px; height: ".$height."px; margin: 0em'></div>
		<script type='text/javascript'>

		var chart = new Highcharts.Chart({
			chart: {
				renderTo: '$target'	";

	if($charttype == RENDERER_CHARTTYPE_RADAR)
		$chart .= ", polar: true, type: 'area'";

	// Encoding::toUTF8() required for $data & $labels in offline use
	$chart .= "}, ".Encoding::toUTF8($layout)." ,series: [
				".jsonEncodeSeries(Encoding::toUTF8($data),Encoding::toUTF8($labels),$color,$charttype)."
			]
		});
	</script>";

	return $chart;
}

function jsonEncodeMultiSeries($data,$labels,$color,$charttype) {
	$countSeries = count($data);

	for($i=0;$i<$countSeries;$i++) {

		// Reset vars per serie
		$layoutSerie = '';
		$colorTransparant = false;
		$charttypeSingle = $charttype;

		// Check if this is the last one
		if($i == ($countSeries-1)) {

			if($charttype ==  RENDERER_CHARTTYPE_LINESTEPCOLUMN)
				$charttypeSingle = RENDERER_CHARTTYPE_LINESTEP;

		} else {

			if($charttype == RENDERER_CHARTTYPE_LINECOLUMN || $charttype ==  RENDERER_CHARTTYPE_LINESTEPCOLUMN) {
				$colorTransparant = true;
				$charttypeSingle = RENDERER_CHARTTYPE_COLUMN;


				$layoutSerie .= "
							showInLegend: false,
							yAxis: 1,
							";
			}

		}

		$jsonData .= jsonEncodeSingleSerie($data[$i],$labels,$color,$charttypeSingle,$layoutSerie,$colorTransparant);

		if($i<($countSeries-1))
				$jsonData .=  ",";

	}

	return $jsonData;
}



function jsonEncodeSingleSerie($data,$labels,$color,$charttype,$layoutSerie='',$colorTransparant=false) {
	global $renderer_color;
	global $renderer_color_transparancy;

	$countSeries = count($data);
	$jsonData = "";

	if($countSeries>0) {
		for($i=0;$i<$countSeries;$i++) {

			$colorSerie = $renderer_color[$color][$i][0];
			if($colorTransparant)
				$colorSerie = "rgba(".hex2rgb($colorSerie).",".$renderer_color_transparancy.")";

			$jsonData .= " { ";

			if($charttype == RENDERER_CHARTTYPE_COLUMN)
				$jsonData .= " type: 'column',";

			if($charttype == RENDERER_CHARTTYPE_BAR)
				$jsonData .= " type: 'bar',";

			if($charttype == RENDERER_CHARTTYPE_LINESTEP)
				$jsonData .= " step: 'left',";

			$jsonData .= $layoutSerie;

			$jsonData .= "
				color: '".$colorSerie."',
				name: ".json_encode($labels[$i]).",
				data: ".json_encode($data[$i])." }";

			if($i<($countSeries-1))
				$jsonData .=  ",";

		}

	} else if(countSeries==1) {
		$jsonData .=  "data: { ".$layoutSerie.json_encode($data[0])." }";
	}

	return $jsonData;
}

function jsonEncodeRadarSerie($data,$labels,$color,$charttype) {
	return jsonEncodeSingleSerie($data,$labels,$color,$charttype,'',true);
}


function jsonEncodeSeries($data,$labels,$color,$charttype) {

	if($charttype == RENDERER_CHARTTYPE_LINECOLUMN || $charttype ==  RENDERER_CHARTTYPE_LINESTEPCOLUMN)
		return jsonEncodeMultiSeries($data,$labels,$color,$charttype);

	else
		return jsonEncodeSingleSerie($data,$labels,$color,$charttype);
}


/**
Functions to render block in which the graphs are shown
*/
function renderHeaderBlock($category) {

	return '
		<table class="box" border="0" cellpadding="0" cellspacing="0">
		  <thead>
			<tr>
			  <td class="heading" align="center" colspan="2" style="min-width: 300px"><a href="#_" onclick="toggle_visibility(\''.$category.'\');">'.$category.'</a></td>
			</tr>
			 </thead>
			<tbody id="'.$category.'">
			<tr>
			  <td>
	';
}

function renderFootBlock() {
	return '
				</td>
			</tr>
		  </tbody>
		</table><br>
	';
}

/*
Helper function to get rgb value for hex color, based on solution from the interwebs
*/
function hex2rgb($hex) {
	$hex = str_replace("#", "", $hex);

   if(strlen($hex) == 3) {
      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
   } else {
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));
   }
   $rgb = array($r, $g, $b);
   return implode(",", $rgb); // returns the rgb values separated by commas

}

?>
