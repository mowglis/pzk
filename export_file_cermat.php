<?php
	#####################################
	# export uchazeèù pro CERMAT
	#####################################
    extract($_REQUEST);
    if (!isset($filename) || $filename=='') exit;
	if (!isset($id_studium) || $id_studium=='') exit;
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
	$title = "Export souboru";
	
    if (!isAdminUser()) {
		$pg = "Nejste autorizován pro vstup na tuto stránku!";
		h_page($pg,$title,"pzk.css");
		exit;
	}

    function write_xml_uchazec($u, $data) {
        $uchazec = $u->addChild('uchazec');
        foreach ($data as $tag => $value) {
            $uchazec->addChild($tag, $value);
        }
        return;
    }        
        
    $gybon_obor = "79-41-K/61";
    $cizinec_AN = array("Ne", "Ano");
    $uchazeci = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><uchazeci></uchazeci>");
	$res = dbSelect(uchazec(),"id_studium=$id_studium AND ucast=1");
	while ($r = mysql_fetch_array($res)) {
		list($rr,$mm,$dd) = split('-',$r["datnar"]);
		$datnar = "$dd.$mm.$rr";
        $bydliste = u8($r["misto"]).";".u8($r["ulice"]).";".$r["ulice_cp"];
        $_data = array(
            "ev_cislo" => $r["id"],
            "jmeno" => u8($r["jmeno"]),
            "prijmeni" => u8($r["prijmeni"]),
            "rodne_cislo" => $r["rc"],
            "datum_nar" => $datnar,
            "misto_nar" => u8($r["misto_nar"]),
            "trvale_bydliste" => $bydliste,
            "obor" => $gybon_obor,
            "poradi_zajmu" => $r["poradi_zajmu"],
            "izozs" => $r["izo_zs"],
            "kontakt_uchazec" => u8($r["e_mail0"]),
            "kontakt_zak_zastupce" => u8($r["e_mail1"]),
            "pup" => u8(get_pup($r["zps"])),
            "cizinec" => $cizinec_AN[$r["cizinec"]],
            "kona_jpz" => ""
        );            
        write_xml_uchazec($uchazeci, $_data);
}
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($uchazeci->asXML());
	header("Content-Disposition: attachment; filename=\"$filename\"");
	header("Content-Type: text/xml");
//    print($uchazeci->asXML());
    print($dom->saveXML());
	exit();
?>
