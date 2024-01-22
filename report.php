<?
    require("libpzk/common.inc.php");
    extract($_REQUEST);

    $debug = 0; # 1 ==> pro ladìní

    $basedir = "/home/rusek/pzk/";
    if (!isset($templ) || $templ=="") exit;
//   $file = uniqid("");
	### osetreni sablony pro sestavu (-t)
	# template -- ucebna
//	if($templ == "ucebna"){
//		$order = "-b\"id_ucebna,prijmeni,jmeno\"";
//		$file = "ucebna";
//	}
	# template -- vyhodnoceni
//	if($templ == "vyhodnoceni"){
//		$order = "-b\"poradi_od,prijmeni,jmeno\"";
//		$file = "vyhodnoceni";
//	}
	# template -- zs
//	if($templ == "zs"){
//		$order = "-b\"poradi_od,prijmeni,jmeno\"";
//		$file = "zs";
//	}
	#--- zadani klauzule WHERE (-w)
	if (isset($whr) && $whr!="") {
		$trans = array(
			"|" => "=", 
			":" => "=", 
			"*" => " AND "
		);
		$whr = "-w\"".strtr($whr,$trans)."\"";
	}
	#--- zadani typu studia (-s)
	if(isset($atyp) && $atyp!="")
    {
		$param_s = "-s$atyp";
	} else {
		$param_s = "";
	}
	#--- mod 'secure' - bez jmen (-x)
	if (isset($sec) && $sec="1")
    {
		$sec = "-x";
	} else {
		$sec = "";
	}
  	#--- zadani termínu (-z)
	if(isset($idt) && $idt!=""){
		$param_z = "-z$idt";
	} else {
		$param_z = "";
	}
    #--- undef vars
    if (!isset($order)) $order = ""; 
    if (!isset($whr)) $whr = ""; 


    $file = $templ;
    $pattern = "/id=\d*/";
    preg_match($pattern, $whr, $items);
    ##print_r($items);
    if (isset($items) && !empty($items)) $file = $file.'_'.str_replace('id=', '', $items[0]);
    $pdffile = $file.".pdf";
    if ($debug) 
    {
        $command = $basedir."mkreport.pl -t $templ $param_s $param_z -n $file $whr $order $sec";
        print ("<b>podmínka:</b> $whr <BR>");
        print("<b>command:</b> $command <BR><br>");
    } else {
        $command = $basedir."mkreport.pl -t $templ $param_s $param_z -n $file $whr $order $sec > /dev/null 2> /dev/null";
    }
    system($command);
    $redirect = get_location("/pdf/$pdffile");
    if ($debug) {
        print("<br><br><b>pdffile:</b> $pdffile <BR>");
        print("<b>reditect:</b> $redirect");
    } else {
        header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
        header("Pragma: no-cache"); // HTTP 1.0.
        header("Expires: 0"); // Proxies.
        header($redirect);
    }
?>
