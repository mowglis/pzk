<?php
    #----------------------------
	# import výsledkù z CERMATu
	#----------------------------

    require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);
    $rec_null = 0;
    $rec_write = 0;
    $rec_valid = 0;

    function zpracuj_vysledky($id, $vysledky_2)
    {
        global $odeslano;
        $cols = [];
        $cols['id'] = $id;
        foreach ($vysledky_2 as $klic => $hodnota) {
            $cols[$klic] = floatval($hodnota);
        }
        $cols['kriterium'] = "'".sprintf("%04d%04d%04d%04d%04d%04d%04d%04d", $cols['MA_ou']*100, $cols['CJ_ou']*100, $cols['MA_d']*100, $cols['MA_b']*100, $cols['MA_c']*100, $cols['CJ_c']*100, $cols['CJ_a']*100, $cols['CJ_b']*100)."'";
        $t = "";
        foreach ($cols as $i => $h) {
            $t .= sprintf("%s:%s ", $i, $h);
        }
        //echo "$t <br>\n";
        if ($odeslano) {
            $res = dbInsert_cols('vysledek', $cols);           
        }
        return;
    }       

	function zpracuj_vetu($id, $vysledky) {
        global $m, $cj, $vstup, $celkem, $rec_null,$rec_write,$odeslano;
//        echo "veta:",$id," MA:",$vysledky["MA"]," CJ:",$vysledky["CJ"],"<br>\n";
	    $res = dbSelect(uchazec(),"id=$id",'vstup');
        if ($res && mysql_num_rows($res) > 0) {
            list($vstup) = mysql_fetch_array($res);
        } else {
            return false;
        }
        if (empty($vysledky["MA"]) && empty($vysledky["CJ"])) {
            # nastavit neúèast!
            $rec_null += 1;
            return true;
        }
        $m  = $vysledky["MA"];
        $cj = $vysledky["CJ"];
        doSum(); // prepocitej bodove zisky
        $cols = array (
            "cj"        => $cj,
            "m"         => $m,
            "celkem"    => $celkem
        );
        if (isset($odeslano)) {
//            echo "$id - Zápis dat!!<br>\n";
            $res = dbUpdate(uchazec(),$cols,"id=$id");
            if (!$res) {
                return false;
            }
            $rec_write += 1;
        }
        return true;
    }

    # --- main ---
   	if (!isAdminUser()) {
		$pg = "Nejste autorizován pro vstup na tuto stránku!";
		h_page($pg,$title,"pzk.css");
		exit;
	}
   
	$title = "Import výsledkù - CERMAT";
    if (!isset($uploadfile)) {
      $uploaddir = '/tmp/';
	  $uploadfile = $uploaddir . basename($_FILES['i_file']['name']);
      if(!move_uploaded_file($_FILES['i_file']['tmp_name'], $uploadfile)) {
	      $pg = "Import soubor skonèil chybou";
		  exit;
	  };
    }
    # vymazani starych vysledku v tbl 'vysledek'
    if ($odeslano) {
        #$expr = "DELETE FROM vysledek";
        $result = dbi_delete('vysledek');
    }        
   
    $xml = simplexml_load_file($uploadfile);
    $text = "";
    $rec = 0;
    $rec_error = 0;
    foreach ($xml->uchazec as $uchazec) {
        $rec += 1;
        $text .= sprintf("%-3s %-25s\n",l2($uchazec->ev_cislo),l2($uchazec->prijmeni)." ".l2($uchazec->jmeno));
        unset($vysledky);
        unset($vysledky_2);
        foreach ($uchazec->vysledky->zkouska as $zk) {
           $predmet = (string) $zk->predmet;
           $vysledky[(string) $zk->predmet] = (string) $zk->body;
           $text .= sprintf(" %-3s: %-3s",$zk->predmet,$zk->body);
           # -- pridane vysledky --
           $vysledky_2[$predmet.'_ou'] =  $zk->uspesnost_ou;
           $vysledky_2[$predmet.'_a']  =  $zk->uspesnost_a;
           $vysledky_2[$predmet.'_b']  =  $zk->uspesnost_b;
           $vysledky_2[$predmet.'_c']  =  $zk->uspesnost_c;
           $vysledky_2[$predmet.'_d']  =  $zk->uspesnost_d;
           $text .= sprintf("  --   ou:%-5s a:%-5s b:%-5s c:%-5s d:%-5s", $zk->uspesnost_ou, $zk->uspesnost_a, $zk->uspesnost_b, $zk->uspesnost_c, $zk->uspesnost_d);
           $text .= "\n";
        }
        $id = (int) $uchazec->ev_cislo;
    #    var_dump($id);
    #    var_dump($vysledky);
		if (zpracuj_vetu($id, $vysledky)) {
            $rec_valid += 1;
            zpracuj_vysledky($id, $vysledky_2);
        } else {
            $rec_error += 1; 
        } 
	}

	$pg = sprintf("
<table class=tblhead cellspacing=0 cellpadding=1>
<tr><td>
  <table border=1 cellspacing=0 cellpadding=3 class=tblmain>
  <tr><td>Jméno souboru:</td><td>%s</td></tr>
  <tr><td>velikost souboru:</td><td>%s B</td></tr>
  <tr><td>celkem záznamù:</td><td>%s</td></tr>
  <tr><td>- nespárováno:</td><td>%s</td></tr>
  <tr><td>- pøipraveno k zápisu:</td><td>%s</td></tr>
  <tr><td>-- prázdné výsledky:</td><td>%s</td></tr>
  <tr><td>poèet zápisù do db:</td><td>%s</td></tr>
  </table>
</table>",$uploadfile,filesize($uploadfile),$rec,$rec_error,$rec_valid,$rec_null,$rec_write);

    $pg .= sprintf("<br><textarea readonly rows=\"25\" cols=\"60\">%s</textarea><br>", $text);
	$pg .= sprintf("
<br><form method=\"POST\">%s %s %s</form>",
    h_btn("write",'Provést zápis dat'),
    h_hidden("odeslano","1"),
    h_hidden("uploadfile",$uploadfile)
    );

	$pg .= sprintf("
<br><form action=\"cermat.php\">%s</form>",h_btn("push",'Zpìt'));
	h_page($pg,$title,"pzk.css");
?>
