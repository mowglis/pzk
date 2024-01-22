<?php
	#####################################
	# import výsledkù ze SCIO
	#####################################
	#
	# struktura vety ze SCIO
	#
	#	verejneID; anonymniID; prijmeni; jmeno; CJ7 [skóre]; CJ7 [percentil]; CJ7 [skóre harm.]; CJ7 [percentil harm.]; MA7 [skóre]; MA7 [percentil]; MA7 [skóre harm.]; MA7 [percentil harm.]; OSP7 [skóre]; OSP7 [percentil]; OSP7 [skóre harm.]; OSP7 [percentil harm.]
	#
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

	function zpracuj_vetu($aid,$scio_cj,$scio_m) {
		global $m, $cj, $vstup, $celkem;
		# m - vynasobit 4/3 - uz neplati
		# 2014: celkem_bodovy_zisk = scio_m + 3/4*scio_cj + 1/2*scio_osp
		# pracujeme s 'hskore' - harmonizovane skore
		$res = dbSelect(uchazec(),"aid=$aid",'vstup,termin');
		list($vstup,$termin) = mysql_fetch_array($res);
//		echo "$aid: ".$scio_cj." -- ".$scio_m."\n<br>";
		$id_termin=substr($termin,10,1);
//		$m   = myRound(str_replace(",",".",$scio_m[$id_termin])*4/3,2);  // !!! matiku nasobit 4/3
//		$m   = str_replace(",",".",$scio_m[$id_termin]);  
//		$cj  = str_replace(",",".",$scio_cj[$id_termin]);
//		$cj   = myRound(str_replace(",",".",$scio_cj[$id_termin])*3/4,2);   // Cj  -> 3/4
//		$osp  = myRound(str_replace(",",".",$scio_osp[$id_termin])*1/2,2);  // OSP -> 1/2
		$m  = myRound(str_replace(",",".",$scio_m)*4/3,2);                  // M -> 4/3
		$cj = str_replace(",",".",$scio_cj);
//		echo "$aid: ".$cj." -- ".$m." -- $vstup\n<br>";
      
		# 2014 - body jsou vzdy v jednom sloupci
//		$m   = str_replace(",",".",$scio_m);  
//		$cj   = myRound(str_replace(",",".",$scio_cj)*3/4,2);   // Cj  -> 3/4
//		$osp  = myRound(str_replace(",",".",$scio_osp)*1/2,2);  // OSP -> 1/2
        $osp = 0;
		doSum(); // prepocitej bodove zisky
		$cols = array (
					"osp"		=> $osp,
					"cj" 		=> $cj,
					"m" 		=> $m,
					"celkem"	=> $celkem
		);
//		echo "zápis - $aid: $vstup + $cj + $m = $celkem\n<br>";
		#validateNumData($vars);
//		echo "*** cols: $cols<BR>\n";
		$res = dbUpdate (uchazec(),$cols,"aid=$aid");
		if (!$res) {
			$pg .= "ERROR - aid: $aid\n<br>";
		}
	}

	$uploaddir = '/tmp/';
	$title = "Import souboru";
	if (!isAdminUser()) {
		$pg = "Nejste autorizován pro vstup na tuto stránku!";
		h_page($pg,$title,"pzk.css");
		exit;
	}
	$pg .= "FILE: ".$_FILES['i_file']['name']."<br>";
	$pg .= "FILE: ".$_FILES['i_file']['tmp_name']."<br>";
	$pg .= "FILE: ".$_FILES['i_file']['size']."<br>";
	$pg .= "FILE: ".$_FILES['i_file']['type']."<br>";
	$pg .= "FILE: ".$_FILES['i_file']['error']."<br>";
	$uploadfile = $uploaddir . basename($_FILES['i_file']['name']);
	if(!move_uploaded_file($_FILES['i_file']['tmp_name'], $uploadfile)) {
		$pg .= "Import soubor skonèil chybou";
		exit;
	};
	$pg .= "uploadfile: ".$uploadfile."<br>";
//	print_r($_FILES);
 	
	$handle = fopen($uploadfile, "r");
	$rec = 0;
	while (!feof($handle)) {
		$rec += 1;
//        print "vìta: $rec<br>";
		$buffer = rtrim(fgets($handle));
//        var_dump ($buffer);
        if ($rec == '1') continue;
//		$cj = array(); $m = array(); $osp = array();
//		list($id,$aid,$prijmeni,$jmeno,$cj[1],$cj[2],$m[1],$m[2],$osp[1],$osp[2]) = split(';',$buffer);  - do roku 2013
	   #
	   # vstupni veta: id,aid,prijmeni,jmeno,cj,m
	   #
//		list($id,$aid,$prijmeni,$jmeno,$cj,$m,$osp) = split(';',$buffer);
		list($id,$aid,$prijmeni,$jmeno,$cj,$m) = split(';',$buffer);
//		if ($id == 'verejneID') continue;
//		if ($id == '') continue;
#		$prijmeni = iconv('cp1250','ISO-8859-2',$prijmeni);
#		$jmeno = iconv('cp1250','ISO-8859-2',$jmeno);
#		$pg .= "$aid --> $prijmeni<br>";
		# zpracovani vety
		zpracuj_vetu($aid,$cj,$m);
	}
	fclose($handle);
	$pg = sprintf("
<table class=tblhead cellspacing=0 cellpadding=1>
<tr><td>
  <table border=1 cellspacing=0 cellpadding=3 class=tblmain>
  <tr><td>Jméno souboru:</td><td>%s</td></tr>
  <tr><td>velikost souboru:</td><td>%s B</td></tr>
  <tr><td>zpracováno záznamù:</td><td>%s</td></tr>
  </table>
</table>",$_FILES['i_file']['name'],$_FILES['i_file']['size'],$rec);
	$pg .= sprintf("
<br><form action=\"scio.php\">%s</form>",h_btn("push",'Zpìt na SCIO'));
	h_page($pg,$title,"pzk.css");
?>
