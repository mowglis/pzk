<?
	require("libpzk/common.inc.php");
	require("libpzk/sql.inc.php");
    extract($_REQUEST);

	$title = "Export souboru - odvolání";
	$filename = 'odvolani.csv';
	$basedir = "/home/rusek/pzk/";
	if (!isAdminUser()) {
		$pg = "Nejste autorizován pro vstup na tuto stránku!";
		h_page($pg,$title,"pzk.css");
		exit;
	}
	header("Content-Disposition: attachment; filename=\"$filename\"");
	header("Content-Type: text/csv");
	$fields="jmeno, prijmeni, id, zast_jmeno, zast_prijmeni, ulice, misto, psc, datnar, ulice_cp";
	$orderby="id_studium, poradi_od, zps DESC, m+cj DESC, prijmeni, jmeno";
    $start_rozhod=readCfg("start_rozhod");
    $rok=readCfg("rok");
//	$sql = "select $FIELDS from ".uchazec()." WHERE odvolani=1 AND prijat=0 ORDER BY id_studium, poradi_od, zps DESC, m+cj DESC, prijmeni,jmeno";
	$r = dbSelect(uchazec(),"odvolani=1 AND prijat=0",$fields,$orderby);
    print(iconv('ISO-8859-2', 'cp1250', "Èíslo jednací;Uchazeè;Datum narození;Zákonný zástupce;Bydli¹tì\n"));
	while (list($jmeno,$prijmeni,$id,$zast_jmeno,$zast_prijmeni,$ulice,$misto,$psc, $datnar, $ulice_cp) = mysql_fetch_array($r)) {
		# !! konverze do cp1250
		#print (iconv('ISO-8859-2','cp1250',$jmeno).";".iconv('ISO-8859-2','cp1250',$prijmeni).";$id;".iconv('ISO-8859-2','cp1250',$zast_jmeno).";".iconv('ISO-8859-2','cp1250',$zast_prijmeni).";".iconv('ISO-8859-2','cp1250',$ulice).";".iconv('ISO-8859-2','cp1250',$misto).";$psc\n");
        $cj = $start_rozhod+$id;
        $cj = "PZK $cj/$rok";
		print ($cj.";".iconv('ISO-8859-2','cp1250',$jmeno)." ".iconv('ISO-8859-2','cp1250',$prijmeni).";$datnar;".iconv('ISO-8859-2','cp1250',$zast_jmeno)." ".iconv('ISO-8859-2','cp1250',$zast_prijmeni).";".iconv('ISO-8859-2','cp1250',$ulice)." $ulice_cp, $psc ".iconv('ISO-8859-2','cp1250',$misto)."\n");
	}

#	function mkdata ($outdir) {
#		global $basedir;
	//	$command = $basedir."data_odvolani.sh  $outdir ".readCfg('kolo')." > /dev/null";
#		$command = $basedir."data_odvolani.sh  $outdir ".readCfg('kolo');
#		system($command);
#	}
	
#	mkdata('/home/sterba/pzk');
#	mkdata('/home/rusek/pzk/odvolani');

# 	$path = GetPath($REQUEST_URI)."/index.php?atyp=$atyp";
#	Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
	exit();
?>

