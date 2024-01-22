<?
    #------------------------------------
	# nastaveni kriteria pro vyhodnoceni
	#------------------------------------
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

	if (!isset($atyp) || $atyp=="") exit;
	#  nastaveni/prehoceni vyhodnocovaciho kriteria
    $info = "";
	if (isset($kriterium) && $kriterium=="1") 
    {
		$kriterium = readCfg("vyhodnoceni");
        #var_dump($kriterium);
		if ($kriterium == "minbody"){
			writeCfg("vyhodnoceni","maxporadi");
		} else {
			writeCfg("vyhodnoceni","minbody");
		}
        //exit;
		$path = GetPath($REQUEST_URI)."/vyhodnoceni.php?atyp=$atyp";
//		Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
        Header(get_location($path));
    }
	# *** Texty tlacitek *** 
	$btnSave   = "Ulo¾it";
	$btnCancel = "Cancel";
	if (isset($odeslano) && $odeslano) {
		if($push == $btnCancel) {
			// navrat bez ulozeni dat 	
			$path = GetPath($SCRIPT_NAME)."/vyhodnoceni.php?atyp=$atyp";
//			Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
            Header(get_location($path));
    }
		if($push == $btnSave || $push == $btnSaveCont) {
		  	# kontroly
			$valid = true;
			if($valid) {
				# Mame udaje - jdeme zapisovat do db
				$status = true;
				$vars = array("minbody".$atyp,"maxporadi".$atyp);
				validateNumData($vars);
				writeCfg("minbody".$atyp,$minbody);
				writeCfg("maxporadi".$atyp,$maxporadi);
				if (!$res) $status = false;
				# *** OK -> pøesmìrování ***
				$path = GetPath($REQUEST_URI)."/vyhodnoceni.php?atyp=$atyp";
//	    Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
                Header(get_location($path));

} else {
				# opakovat vstup dat
				if (!$valid) $info .= "<BR>Vypòte v¹echny potøebné údaje!";
			}
		}
	} else {
		# *** prvni pruchod - neodeslano ***
		$minbody   = readCfg("minbody".$atyp);
		$maxporadi = readCfg("maxporadi".$atyp);
	}
	### tvorba vystupni stranky
	$res = dbSelect("studium","id_studium=$atyp","prefix");
	list($studium) = mysql_fetch_array($res);
	$title = "Editace kritéria pro pøijetí (studium $studium)";
	$pg = sprintf("
<FORM  METHOD=POST NAME=setdata onSubmit=\"return validate(this)\">
<DIV ALIGN=CENTER>");
	# dodatky formulare - HIDDEN
	$pg .= h_hidden("odeslano","true");
	$pg .= h_hidden("atyp",$atyp);
	$pg .= sprintf("
<table cellspacing=1 class=tblborder border=0>
<tr><td>
<table border=0 cellspacing=0 cellpadding=5 class=tblmain>
	");
	$pg .= sprintf("<tr><td colspan=2 align=center class=rowcls1>%s<br>%s</td></tr>",$title,$info);
	$pg .= sprintf(
"<tr class=\"polozka\">
<td class=\"polozka\">minmální celkový bodový zisk</td><td>%s</td>
</tr><tr>
<td class=\"polozka\">maximální dosa¾ené poøadí</td><td>%s</td>
</tr>",h_input("minbody","3","text"),h_input("maxporadi","3","text"));
	# *** tlacitka ***
	$pg .= "<!-- ### zacatek tlacitek ### -->\n";
	$pg .= sprintf("<tr><td class=rowcls1 colspan=2 align=center>%s&nbsp;&nbsp;%s</td></tr>",
	h_btn("push",$btnSave),h_btn("push",$btnCancel,"onClick=\"self.history.back()\""));
	$pg .= sprintf(
"</table>
</form></td></tr></table>");
	h_page($pg,$title,"pzk.css","setdata.minbody","libpzk/edit2.ins.js");
?>	
