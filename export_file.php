<?php
	#####################################
	# export uchazeèù pro SCIO
	#####################################
    extract($_REQUEST);
	if (!isset($filename) || $filename=='') exit;
	if (!isset($id_studium) || $id_studium=='') exit;
	if (!isset($idt) || $idt=='') exit;
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
	$title = "Export souboru";
	if (!isAdminUser()) {
		$pg = "Nejste autorizován pro vstup na tuto stránku!";
		h_page($pg,$title,"pzk.css");
		exit;
	}
	header("Content-Disposition: attachment; filename=\"$filename\"");
	header("Content-Type: text/csv");
	#header('Content-Length: ' . $content_len);
	$pred_datnar = '';
	$r = dbSelect(uchazec(),"id_studium=$id_studium AND ucast=1 AND termin='datum_pzk_$idt'",'id,aid,prijmeni,jmeno,datnar','datnar');
	while (list($id,$aid,$prijmeni,$jmeno,$datnar) = mysql_fetch_array($r)) {
	   # RC
#      $datnar = preg_replace("/-/","",$datnar);$datnar = substr($datnar,2);
#		if ($pred_datnar == $datnar) { 
#			$add_num++; $datnar.=$add_num;
#		} else {
#			$add_num = 0;
#			$pred_datnar = $datnar;
#		}
		# pouze datum narozeni - 19.3.2013
		list($rr,$mm,$dd) = split('-',$datnar);
		$datnar = "$dd.$mm.$rr";
		# !! konverze do cp1250
//		print ("$id;$aid;".iconv('ISO-8859-2','cp1250',$prijmeni).";".iconv('ISO-8859-2','cp1250',$jmeno).";$datnar\n");
	   # 18.3.2014 - zmìna importu
		print ("$id;;$aid;;$idt;;;".iconv('ISO-8859-2','cp1250',$prijmeni).";".iconv('ISO-8859-2','cp1250',$jmeno).";$datnar\n");
	}
	exit();
?>
