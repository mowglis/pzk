<?
	require("libpzk/common.inc.php");
    extract($_REQUEST);

	$debug = 0;  # 1 - pro debug
	$basedir = "/home/rusek/pzk/";
	$file = 'labels';
	$pdffile = $file.".pdf";
	### zadani typu studia (-s)
	if(isset($atyp) && $atyp!=""){
		$param_s = "-s$atyp";
	} else {
		$param_s = "";
	}
	### zadani klauzule WHERE (-w)
	if(isset($whr) && $whr!="") {
		$trans = array(
			"|" => "=", 
			":" => "=", 
			"*" => " AND "
		);
		$whr = "-w\"".strtr($whr,$trans)."\"";
	}
	$command = $basedir."mklabels.pl $param_s $whr > /dev/null 2> /dev/null";
	if ($debug) {
		$command = $basedir."mklabels.pl $param_s $whr";
		print("$command<BR>");
		print("pdffile: $pdffile<BR>");
	}
	system($command);
//	$redirect = "Location: http://$SERVER_NAME:$SERVER_PORT".getPath($SCRIPT_NAME)."/pdf/$pdffile";
    $redirect = get_location("/pdf/$pdffile");
	if ($debug) {
		print($redirect);
	} else {
		header($redirect);
	}
?>
