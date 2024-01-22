<?
   #****************************#
   #  Editace zaznamu uchazece  #
   #****************************#
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);
	
	# *** Texty tlacitek *** 
	$btnSaveCont  = "Ulo¾it & dal¹í";
	$btnSave   = "Ulo¾it";
	$btnCancel = "Cancel";
    $btnDelete = "Vymazat!";
	
	# *** implicitni hodnoty poli ***
	# -- zakony zastupce --
	$ini["zast_prijmeni"] = "";
	$ini["zast_jmeno"] = "";
	$ini["zast_pohlavi"] = 1;
    $ini["zast_datnar"] =  "";
    $ini["datovka"] = "";
	# -- uchazec --
	$ini["prijmeni"] = "";
	$ini["jmeno"] = "";
	$ini["pohlavi"] = 1;
	$ini["datnar"] = "";
	$ini["zps"] = 0;
	$ini["id_zs"] = 0;
	$ini["id_ss"] = 0;
	$ini["ulice"] = "";
	$ini["misto"] = "Hradec Králové";
	$ini["psc"] = "";
	$ini["p1"] = "0.0";
	$ini["p2"] = "0.0";
	$ini["p3"] = "0.0";
	$ini["bonifikace"] = "0";
    $ini["prumer"] = "0";
	$ini["body"] = "0";
	$ini["vstup"] = "0";
	$ini["cj"] = "0";
	$ini["m"] = "0";
	$ini["jazyk"] = "0";
	$ini["celkem"] = "0";
	$ini["prijat"] = "0";
	$ini["ucast"] = "1";
	$ini["odvolani"] = "0";
	$ini["cj0"] = "---nevyplòovat---";
	$ini["aj"] = "0";
	$ini["pz"] = "0";
	$ini["oz"] = "0";
	$ini["termin"] = 'datum_pzk_1';
	$ini["aid"] = '0';
	$ini["listek"] = 0;
    $ini["splnil"] = 0;
    # Cermat
    $ini["misto_nar"] = "Hradec Králové";
    $ini["izo_zs"] = "";
    $ini["poradi_zajmu"] = 1;
    $ini["e_mail0"] = "";
    $ini["e_mail1"] = "";
    $ini["poslat_pozvanku"] = 1;
    $ini["ulice_cp"] = "";
    $ini["bydliste_kraj"] = "---nevyplòovat---";
    $ini["cermat_export"] = 0;
    $ini["ulice_doruc"] = "";
    $ini["ulice_cp_doruc"] = "";
    $ini["mesto_doruc"] = "";
    $ini["psc_doruc"] = "";
    $ini["rc"] = "";
    $ini["cizinec"] = 0;
    $ini["prevzal"] = 0;

    # doruceni prihlasky
    $ini["doruceno"] = (new DateTime())->format("Y-m-d");
    $ini["zpusob_doruceni"] = 0;
    $ini["pocet_priloh"] = 0;

	$slashFields = array("prijmeni","jmeno","ulice","misto","poznamka","zast_jmeno","zast_prijmeni","zast_pohlavi","cj0","misto_nar","ulice_cp","bydliste_kraj","ulice_doruc","ulice_cp_doruc","mesto_doruc","psc_doruc","rc","datovka");
	if (isset($odeslano) && $odeslano) {
		if($push == $btnCancel || $push == $btnDelete) {
			// navrat bez ulozeni dat 	
			$path = GetPath($SCRIPT_NAME)."/index.php?&atyp=$atyp";
			Header(get_location($path));
        }
		if($push == $btnSave || $push == $btnSaveCont) {
		  	# kontroly
//         SaveFormData($fields);
			$vars = array("prijmeni","jmeno","datnar","id_studium","izo_zs","p1","p2","cj0","misto_nar","ulice_cp","bydliste_kraj","zast_datnar");
#            print("kontrola dat");
#			$valid = CheckData($ini,$vars);
            
            $valid = true;
			if($valid) {
				# Mame udaje - jdeme zapisovat do db
				$status = true;
//				echo "*** status - before: $status<BR>";
				doCalc($id_studium); // prepocitej bodove zisky
				AddSlashesFields($slashFields);
                if ($rc) $datnar = rc2datnar($rc);    
				# oprava ciselnych udaju
				$vars = array("id_studium","p1","p2","p3","prumer","body","vstup","cj","m","jazyk","bonifikace","celkem","zps","ucast","prijat","id_ucebna","poradi_od","poradi_do","odvolani","zast_pohlavi","prevzal","aj","pz","oz","listek","poradi_zajmu","poslat_pozvanku","cizinec","zpusob_doruceni","pocet_priloh","poradi_od_cizinec","poradi_do_cizinec");
                if (empty($poslat_pozvanku)) $poslat_pozvanku=0;
				validateNumData($vars);
				$datnar = DMR2RMD($datnar);
				$doruceno = DMR2RMD($doruceno);
				$zast_datnar = DMR2RMD($zast_datnar);
                $p3 = 0;
                # 11.2.2021 -  datum pzk je podle poradi_zajmu 
                $_termin = array('1' => 'datum_pzk_1', '2' => 'datum_pzk_2');
                $termin = $_termin[$poradi_zajmu];
				$cols = "$id, '$jmeno', '$prijmeni', $pohlavi, '$datnar', $zps, $id_zs, $id_ss, '$ulice', '$misto', '$psc', $id_studium, $p1, $p2, $p3, $prumer, $body, $vstup, $cj, $m, $jazyk, $celkem, $poradi_od, $poradi_do, $prijat, $bonifikace, $ucast, $id_ucebna, '$poznamka', $odvolani, '$zast_jmeno', '$zast_prijmeni', $zast_pohlavi, $prevzal, '$cj0', '$aj', '$pz', '$oz', '$termin', $aid, '$listek', '$izo_zs', '$misto_nar', $poradi_zajmu, '$e_mail0', '$e_mail1', $poslat_pozvanku, '$ulice_cp', '$bydliste_kraj', $cermat_export, '$ulice_doruc', '$ulice_cp_doruc', '$mesto_doruc', '$psc_doruc', '$rc',$cizinec, '$doruceno', $zpusob_doruceni, $pocet_priloh, '$zast_datnar', '$datovka', $splnil, $poradi_od_cizinec, $poradi_do_cizinec";
                
//				echo "*** cols: $cols<BR>\n";
				$res = dbi_insert(uchazec(),$cols, True);
				if (!$res) $status = false;
				//echo "*** status - after: $status";
				# *** OK -> pøesmìrování ***
                //exit;
				if($push == $btnSaveCont) {
					$path = GetPath($REQUEST_URI)."/edit.php?atyp=$atyp";
				} else {
					$path = GetPath($REQUEST_URI)."/index.php?atyp=$atyp";
				}
                if ($_GET['ret'] == 'prihlas') {
                    $path = GetPath($REQUEST_URI)."/prihlasky.php";
                }                    
//                echo "Redirect - path: $path";
//                echo "Redirect - get_location: ".get_location($path);
				Header(get_location($path));
			} else {
				# opakovat vstup dat
				if (!$valid) $info .= "<BR>Vypòte v¹echny potøebné údaje!";
			}
		}
	} else {
		# *** prvni pruchod - neodeslano ***
		if(isset($id) && $id!="") {
			# update - veta jiz existuje
			$type = "update";
			$tbl = uchazec();
			$cols = "*";
			$whr = "id=$id";
			$res =  dbi_select($tbl, $whr, $cols);
			$r = $res->fetch_array();
//         echo "db.id_studium:".$r["id_studium"]."id_studium:$id_studium<br>";
			setVars($r);
//         echo "db.id_studium:".$r["id_studium"]."id_studium:$id_studium<br>";
			StripSlashesFields($slashFields);
		} else {
			# new - zapis nove vety
			$type = "new";
			setVars($ini);
			# nove id
			$id = freeID($atyp);
			$id_studium = $atyp;
		}
	};
	### tvorba vystupni stranky
	if($datnar!="") $datnar = RMD2DMR($datnar);
	if($doruceno!="") $doruceno = RMD2DMR($doruceno);
	if($zast_datnar!="") $zast_datnar = RMD2DMR($zast_datnar);
    $title = "Editace záznamu uchazeèe";
	$pg = sprintf("
<FORM  METHOD=POST NAME=setdata onSubmit=\"return validate(this)\">
<DIV ALIGN=CENTER>");
	# dodatky formulare
    if (empty($jazyk)) $jazyk = 0;
	if (empty($poradi_od)) $poradi_od = 0;
	if (empty($poradi_od)) $poradi_do = 0;
	if (empty($poradi_od_cizinec)) $poradi_od_cizinec = 0;
	if (empty($poradi_od_cizinec)) $poradi_do_cizinec = 0;
if (empty($prijat)) $prijat = 0;
	if (empty($poznamka)) $poznamka = "";
	if (empty($odvolani)) $odvolani = 0;
	if (empty($id_ucebna)) $id_ucebna = 0;

    $pg .= h_hidden("odeslano","true");
	$pg .= h_hidden("atyp",$atyp);
	$pg .= h_hidden("id",$id);
	$pg .= h_hidden("prumer",$prumer);
//	$pg .= h_hidden("body",$body);
//	$pg .= h_hidden("vstup",$vstup);
//	$pg .= h_hidden("celkem",$celkem);
	$pg .= h_hidden("cj",$cj);
	$pg .= h_hidden("m",$m);
	$pg .= h_hidden("jazyk",$jazyk);
	$pg .= h_hidden("poradi_od",$poradi_od);
	$pg .= h_hidden("poradi_do",$poradi_do);
	$pg .= h_hidden("poradi_od_cizinec",$poradi_od_cizinec);
	$pg .= h_hidden("poradi_do_cizinec",$poradi_do_cizinec);
	$pg .= h_hidden("prijat",$prijat);
	$pg .= h_hidden("ucast",$ucast);
	$pg .= h_hidden("id_ucebna",$id_ucebna);
	$pg .= h_hidden("poznamka",$poznamka);
	$pg .= h_hidden("odvolani",$odvolani);
	$pg .= h_hidden("aid",$aid);
#	$pg .= h_hidden("poslat_pozvanku",$poslat_pozvanku);
#	$pg .= h_hidden("termin",$termin);
	$pg .= h_hidden("id_studium",$id_studium);
	$pg .= h_hidden("id_zs",$id_zs);
	$pg .= h_hidden("id_ss",$id_ss);
	$pg .= h_hidden("cermat_export",$cermat_export);
    $pg .= h_hidden("cj0",$cj0);
    $pg .= h_hidden("bydliste_kraj",$bydliste_kraj);
    $pg .= h_hidden("splnil",$splnil);
    $pg .= h_hidden("p3",$p3);
    $pg .= h_hidden("prevzal",$prevzal);
#    $pg .= h_hidden("datnar",$datnar);

    $tbl_line_items ="<tr><td class='polozka'>%s</td><td>%s</td><td class='polozka'>%s</td><td>%s</td></tr>\n"; 
    $tbl_line_hdr = "<tr><td colspan=4 align=left class='nadpis1'>%s</td></tr>\n";

	$pg .= sprintf("
	<table cellspacing=1 class=tblborder border=0>
	<tr><td>
	<table border=0 cellspacing=0 cellpadding=5 class=tblmain>
	");
    if (empty($info)) $info = "";
	$pg .= sprintf("<tr><td colspan=4 align=center class=info>%s<br>%s</td></tr>",
		"Údaje oznaèené hvìzdièkou je nutno vyplnit",$info);

    # --- doruèení pøihlá¹ky
    $__val = array("0","1","2");
	$pg .= sprintf($tbl_line_hdr,"Doruèení pøihlá¹ky");
	$_disp=array("osobnì","po¹tou","datovka");
	$_zpusob_doruceni = h_rb("zpusob_doruceni",$__val,$_disp);
	$pg .= sprintf($tbl_line_items,"Datum*",h_input("doruceno","10","text","onBlur=\"validDate(this.form.doruceno)\""),"Doruèeno",$_zpusob_doruceni);
	$pg .= sprintf($tbl_line_items,"Pøílohy",h_input("pocet_priloh","2"),"","");

    # --- uchazec
	$pg .= sprintf($tbl_line_hdr,"Základní údaje uchazeèe - ID: $id");
	$pg .= sprintf($tbl_line_items,"Pøíjmení*",h_input("prijmeni"),"Jméno*",h_input("jmeno"));
	$_val = array("1","2"); $_disp=array("mu¾","¾ena");
	$pol2 = h_rb("pohlavi",$_val,$_disp);
	$rc_naroz = h_input("rc","10","text","onBlur=\"validateRC(this.form)\"")."&nbsp;&nbsp;".h_input("datnar","10","text","onBlur=\"validDate(this.form.datnar)\"");
	$pg .= sprintf($tbl_line_items,"RÈ/Narození*",$rc_naroz,"Pohlaví",$pol2);
	$pg .= sprintf($tbl_line_items,"IZO Z©*",h_input("izo_zs","10","text","onBlur=\"validateIZO(this.form.izo_zs)\""),"E-mail",h_input("e_mail0"));
    $__val = array("0","1");
	$__disp=array("ne","ano");
    $_cizinec = h_rb("cizinec", $__val, $__disp);
	$pg .= sprintf($tbl_line_items,"Místo narození*",h_input("misto_nar"),"Cizinec", $_cizinec."&nbsp;&nbsp;&nbsp;PUP ".mk_pup("zps"));

    # --- trvalé bydli¹tì uchazeèe
	$pg .= sprintf($tbl_line_hdr,"Trvalé bydli¹tì uchazeèe");
	$pg .= sprintf($tbl_line_items,"Ulice*",h_input("ulice","14")." Èp/Èo ".h_input("ulice_cp","6"),"Mìsto/obec*",h_input("misto"));
	$pg .= sprintf($tbl_line_items,"PSÈ*",h_input("psc","4","text","onBlur=\"validPSC(this.form.psc)\""),"","");

    # --- Doruèovací adresa
	$pg .= sprintf($tbl_line_hdr,"Doruèovací adresa");
	$pg .= sprintf($tbl_line_items,"Ulice",h_input("ulice_doruc","14")." Èp/Èo ".h_input("ulice_cp_doruc","6"),"Mìsto/obec",h_input("mesto_doruc"));
	$pg .= sprintf($tbl_line_items,"PSÈ",h_input("psc_doruc","4","text"),"Datovka",h_input("datovka"));

    # --- zákonný zástupce
	$pg .= sprintf($tbl_line_hdr,"Zákonný zástupce uchazeèe");
	$pg .= sprintf($tbl_line_items,"Pøíjmení",h_input("zast_prijmeni"),"Jméno",h_input("zast_jmeno"));
	$pg .= sprintf($tbl_line_items,"Narození", h_input("zast_datnar","10","text","onBlur=\"validDate(this.form.zast_datnar)\""),"E-mail",h_input("e_mail1"));
	$pohl = h_rb("zast_pohlavi",$_val,$_disp);
	$pg .= sprintf($tbl_line_items,"Pohlaví",$pohl,"","");

    # --- Pøijímací zkou¹ka - vstup
	$pg .= sprintf($tbl_line_hdr,"Pøijímací zkou¹ka - základní vstupní údaje");
	$_disp=array("1","2");
    
   	$pg .= sprintf($tbl_line_items,"Prùmìr Z© 1*",h_input("p1","10","text","onBlur=\"isValidFNum(this.form.p1); doCalc2(this.form)\""),"Prùmìr - body",h_input("body","10","text","disabled"));
	$pg .= sprintf($tbl_line_items, "Prùmìr Z© 2*",h_input("p2","10","text","onBlur=\"isValidFNum(this.form.p2); doCalc2(this.form)\""),"Vstup celkem",h_input("vstup","10","text","disabled"));
    $pg .= sprintf($tbl_line_items,"Bonifikace",h_input("bonifikace","10","text","onBlur=\"doCalc2(this.form)\""),"Stav",mk_stav("ucast"));
    $pg .= sprintf($tbl_line_items,"&nbsp;", "&nbsp;", "Poøadí zájmu",h_rb("poradi_zajmu",$_val,$_disp)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pozvánka: ".h_chbx("poslat_pozvanku"));
 
#   $pg .= sprintf($tbl_line_items,"Termín",mk_termin("termin"),"Stav",mk_stav("ucast"));
#    $pg .= sprintf($tbl_line_items,"È. jednací",h_input("cj0","10"),"Poøadí zájmu",h_rb("poradi_zajmu",$_val,$_disp)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pozvánka: ".h_chbx("poslat_pozvanku"));
 
 # --  tlacitka ---
	$pg .= "<!-- ### zacatek tlacitek ### -->\n";
    $js_confirm_del = sprintf("onClick=\"confirmDel(%s,%s,'%s')\"", 1,$id,$prijmeni);
	$pg .= sprintf("<tr><td colspan=4 align=center><br>%s&nbsp;&nbsp;%s&nbsp;&nbsp;%s&nbsp;&nbsp;%s<br><br>  </td></tr>
	</table>
	</td></tr>
	</table>
	</form >",
		h_btn("push",$btnSaveCont),h_btn("push",$btnSave),h_btn("push",$btnCancel,"onClick=\"self.history.back();\""),h_btn("push",$btnDelete,$js_confirm_del));
	h_page($pg,$title,"pzk.css","setdata.doruceno","libpzk/edit.ins.js","calc");
?>
