<?
   #****************************#
   #  Editace zaznamu uchazece  #
   #****************************#
	# Pøijímací zkou¹ka - prùbìh
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

	# vstup: id, [rec, atyp]
	# rec=next -> nacteni dalsiho zaznamu
	# jestlize body [m,cj,osp] = -1 -> nastavit neucast

	function searchOffset($atyp,$editid){
		$res = dbi_select(uchazec(),"id_studium=$atyp","id","prijmeni,jmeno");
		$offset = 0;
		while (list($id) = $res->fetch_array()){
			if($id == $editid) {
//				echo "offset:$offset";
				return $offset;
			}
			$offset++;
		}
	}

	# *** Texty tlacitek *** 
	$btnSaveCont   = "Ulo¾it & dal¹í";
	$btnSave       = "Ulo¾it";
	$btnCancel     = "Cancel";

	if (!empty($odeslano) && $odeslano) {
		if($push == $btnCancel) {
			# navrat bez ulozeni dat 	
			$path = GetPath($SCRIPT_NAME)."/index.php?&atyp=$atyp";
            Header(get_location($path));
      }
		if($push == $btnSave || $push == $btnSaveCont) {
		  	# kontroly
            $osp = 0; // 2015
			$valid = true;
			if($valid) {
				# Mame udaje - jdeme zapisovat do db
				$status = true;
//				echo "*** status - before: $status<BR>";
				doSum(); // prepocitej bodove zisky
				$cols = array (
					"cj" 		=> $cj,
					"m" 		=> $m,
					"osp"   	=> $osp,
					"aj" 		=> $aj,
					"pz" 		=> $pz,
					"oz" 		=> $oz,
					"celkem"	=> $celkem);
				if($m*1 < 0  || $cj*1 < 0 || $osp*1 < 0) {
					$m = $cj = $osp = 0;
					$ucast -= 1;
					doSum(); // prepocitej bodove zisky
					$cols = array (
						"cj" 		=> $cj,
						"m" 		=> $m,
						"osp"		=> $osp,
						"aj" 		=> $aj,
						"pz" 		=> $pz,
						"oz" 		=> $oz,
						"celkem"	=> $celkem,
						"ucast"	=> $ucast);
				}
				#validateNumData($vars);
				//var_dump($cols);
				$res = dbi_update(uchazec(),$cols,"id=$id");
				if (!$res) $status = false;
				echo "*** status - after: $status";
				# *** OK -> pøesmìrování ***

                if($push == $btnSave) {
					$path = GetPath($REQUEST_URI)."/index.php?&atyp=$atyp";
				} else {
					$offset++;
					$path = GetPath($REQUEST_URI)."/edit2.php?atyp=$id_studium&offset=$offset";
				}
//				Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
   				Header(get_location($path));
             
			} else {
				# opakovat vstup dat
				if (!$valid) $info .= "<BR>Vypòte v¹echny potøebné údaje!";
			}
		}
	} else {
		# *** prvni pruchod - neodeslano ***
		if(!isset($offset) || $offset=="") $offset = 0;
		$tbl = uchazec();
		$cols = "*";
		if(isset($editid) && $editid!="") {
			$whr = "id=$editid";
		} else {
			$whr = "id_studium=$atyp";
		}
		$ordby = "prijmeni,jmeno limit $offset,1";
		$res =  dbi_select($tbl,$whr,$cols,$ordby);
		if($res->num_rows < 1) {
			$path = GetPath($REQUEST_URI)."/index.php?&atyp=$atyp";
//			Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
			Header(get_location($path));
		}
		$r = $res->fetch_array();
		setVars($r);
	}
	### tvorba vystupni stranky
	$title = "Vstup/oprava získaných bodù";
	$pg = sprintf("
<FORM  METHOD=POST NAME=\"setdata\" onSubmit=\"return validate(this)\">
<DIV ALIGN=CENTER>");
	# dodatky formulare - HIDDEN
	$pg .= h_hidden("odeslano","true");
	$pg .= h_hidden("atyp",$atyp);
	$pg .= h_hidden("id",$id);
	$pg .= h_hidden("id_studium",$id_studium);
	$pg .= h_hidden("vstup",$vstup);
	$pg .= h_hidden("ucast",$ucast);
#	if($id_studium!=2) $pg .= h_hidden("jazyk",0);
	if($id_studium != 5) {
#		$pg .= h_hidden("jazyk",0);
		$pg .= h_hidden("aj",0);
		$pg .= h_hidden("pz",0);
		$pg .= h_hidden("oz",0);
	}
	$pg .= sprintf("
<table cellspacing=1 class=tblborder border=0>
<tr><td>
<table border=0 cellspacing=0 cellpadding=5 class=tblmain>
	");
    if (empty($info)) $info = "";
	$pg .= sprintf("<tr><td colspan=4 align=center class=info>%s<br>%s</td></tr>",
		$title,$info);
	### uchazec ###
	if($ucast == 0 || $ucast == 2) {
		$cls = "nadpis1-shadow";
		$uc = " ** neúèast";
	} else {
		$cls = "nadpis1";
		$uc = "";
	}
	$pg .= sprintf("<tr><td class=\"%s\" colspan=2>%s</td><td class=%s colspan=2>Id: %s %s</td></tr>\n",$cls,$r["jmeno"]." ".$r["prijmeni"],$cls,$id,$uc);
//	$pg .= sprintf("<tr class=\"polozka\"><td class=\"polozka\">%s</td><td>%s</td><td class='polozka'>%s</td><td>%s</td></tr>\n",
//		"OSP",h_input("osp","10","text","onBlur=\"isValidNum(this.form.m);doSum(this.form,$vstup)\""),"Vstup",$vstup);
	$pg .= sprintf("<tr class=\"polozka\"><td class=\"polozka\">%s</td><td>%s</td><td class='polozka'>&nbsp;</td><td>&nbsp;</td></tr>\n",
		"Matematika",h_input("m","10","text","onBlur=\"isValidNum(this.form.m);doSum(this.form,$vstup)\""));
	$pg .= sprintf("<tr><td class='polozka'>%s</td><td>%s</td><td>%s</td><td>%s</td></td></tr>\n",
		"Èes. jazyk",h_input("cj","10","text","onBlur=\"isValidNum(this.form.cj);doSum(this.form,$vstup)\""),"Celkem",h_input("celkem","10","text","disabled"));
#	if($id_studium == "2") {
#		$pg .= sprintf("<tr><td class='polozka'>%s</td><td>%s</td><td colspan=2>&nbsp;</td></tr>",
#		"Ciz. jazyk",h_input("jazyk","10","text","onBlur=\"isValidNum(this.form.jazyk);doSum(this.form,$vstup)\""));
#	}
	if($id_studium == "5") {
		$pg .= sprintf("<tr><td class='polozka'>%s</td><td>%s</td><td colspan=2>&nbsp;</td></tr>\n",
		"Angl. jazyk",h_input("aj","10","text","onBlur=\"isValidNum(this.form.aj);doSum(this.form,$vstup)\""));
		$pg .= sprintf("<tr><td class='polozka'>%s</td><td>%s</td><td colspan=2>&nbsp;</td></tr>\n",
		"Ciz. jazyk",h_input("jazyk","10","text","onBlur=\"isValidNum(this.form.jazyk);doSum(this.form,$vstup)\""));
		$pg .= sprintf("<tr><td class='polozka'>%s</td><td>%s</td><td colspan=2>&nbsp;</td></tr>\n",
		"Pøír. zákl.",h_input("pz","10","text","onBlur=\"isValidNum(this.form.pz);doSum(this.form,$vstup)\""));
		$pg .= sprintf("<tr><td class='polozka'>%s</td><td>%s</td><td colspan=2>&nbsp;</td></tr>\n",
		"Obè. zákl.",h_input("oz","10","text","onBlur=\"isValidNum(this.form.oz);doSum(this.form,$vstup)\""));
	}
	# *** tlacitka ***
//	if(isset($editid)) $btn = $btnSave;
//		else $btn = $btnSaveCont;
	$btn = $btnSaveCont;
	$pg .= "<!-- ### zacatek tlacitek ### -->\n";
	$pg .= sprintf("<tr><td colspan=4 align=center>%s&nbsp;&nbsp;%s</td></tr>",
	h_btn("push",$btn),h_btn("push",$btnCancel));
	if(isset($editid) && $editid!="") {
		$offset = searchOffset($atyp,$editid);
	}
//	$nxtoff=$offset+1;
//	$nxt = "<a href=\"edit2.php?atyp=$id_studium&offset=".$nxtoff."\">dal¹í</a>";
	$pg .= h_hidden("offset",$offset);
	$pg .= sprintf(
"<tr><td colspan=4 align=center>%s</td></tr>",navigTab($atyp,$offset));
	$pg .= sprintf(
"</table>
</td></tr>
</table>
</form>");
	h_page($pg,$title,"pzk.css","setdata.m","libpzk/edit2.ins.js");
?>
