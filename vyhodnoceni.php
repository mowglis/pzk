<?
	#------------------------
	# vyhodnoceni PZK
	#------------------------
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

    function list_table($atyp, $res)
    {
    $p = "";
    if ($atyp == "5") {
		$p .= sprintf(
"<table class=tblmain border=0 cellspacing=0 cellpadding=3>
<tr class=nadpis1>
<td class=tblhead>Poøadí</td>
<td class=tblhead>Id</td>
<td class=tblhead>Pøíjmení</td>
<td class=tblhead>Jméno</td>
<td class=tblhead>Z©</td>
<td class=tblhead>Vstup</td>
<td class=tblhead>M</td>
<td class=tblhead>Èj</td>
<td class=tblhead>Aj</td>
<td class=tblhead>Czj</td>
<td class=tblhead>Pz</td>
<td class=tblhead>Oz</td>
<td class=tblhead>Celkem</td>
<td class=tblhead>Pøijetí</td>
</tr>");
	} else {
		$p .= sprintf(
"<table border=1 cellspacing=0 cellpadding=4 class=tblmain>
<tr class=nadpis1>
<td class=tblhead width=\"40\" align=\"center\">ID</td>
<td class=tblhead width=\"40\" align=\"center\">AID</td>
<td class=tblhead width=\"125\" align=\"center\">Pøíjmení</td>
<td class=tblhead width=\"120\" align=\"center\">Jméno</td>
<td class=tblhead width=\"200\" align=\"center\">Z©</td>
<td class=tblhead width=\"50\"  align=\"center\">Vstup</td>
<td class=tblhead width=\"50\"  align=\"center\">M</td>
<td class=tblhead width=\"50\"  align=\"center\">Èj</td>
<td class=tblhead width=\"50\" align=\"center\">Celkem</td>
<td class=tblhead width=\"40\" align=\"center\">Poøadí</td>
<td class=tblhead width=\"40\" align=\"center\">Poøadí<br>redu</td>
<td class=tblhead width=\"50\" align=\"center\">Celkem<br>redu</td>
<td class=tblhead width=\"40\" align=\"center\">Pøijetí</td>
<td class=tblhead width=\"40\" align=\"center\">Lístek</td>
</tr>");
	}
	while ($r = $res->fetch_array()) 
    {
		if($atyp=="2"){
			$czj = sprintf("<td align=center>%s</td>",$r["jazyk"]);
		} else {
			$czj = "";
		}
//		$osp = sprintf("<td align=center>%s</td>",$r["osp"]);
        $osp = "";
		
		# prijeti
		$prij_templ = "<a href=\"setprijat.php?id=%s&prijat=%s&atyp=%s\" title=\"Nastavit pøijetí - ano/ne\">%s</a>";
		if($r["prijat"] == 1) {
			$prijat = sprintf($prij_templ,$r["id"],$r["prijat"],$atyp,"ano");
		} else {
			$prijat = sprintf($prij_templ,$r["id"],$r["prijat"],$atyp,"ne");
		}
		
		# zapisovy listek
		$listek_templ = "<a href=\"setlistek.php?id=%s&listek=%s&atyp=%s\" title=\"Nastavit zápisový lístek - ano/ne\">%s</a>";
		if($r["listek"] == 1) {
			$listek = sprintf($listek_templ,$r["id"],$r["listek"],$atyp,"ano");
		} else {
			$listek = sprintf($listek_templ,$r["id"],$r["listek"],$atyp,"ne");
		}
		
		# poøadí
		if($r["poradi_od"] != $r["poradi_do"]) {
			$poradi = $r["poradi_od"]."-".$r["poradi_do"];
		} else {
			$poradi = $r["poradi_od"];
		}
		
		# redukované poøadí
		if($r["poradi_od_cizinec"] != $r["poradi_do_cizinec"]) {
			$poradi_redukovane = $r["poradi_od_cizinec"]."-".$r["poradi_do_cizinec"];
		} else {
			$poradi_redukovane = $r["poradi_od_cizinec"];
		}
		
		$rowcls = "rowcls".(($r["prijat"]+1) % 2);
		if($r["ucast"] == "4") {
			$rowcls = "rowcls4";
			$poradi = '--';
		}
        
        if ($r["splnil"] == 0) {
            $poradi = "---";
            $poradi_redukovane = "---";
        }            
        
		if($r["ucast"] == "3") $rowcls .= "_n";
		
		if($r["zps"]){
			$zps = "* ";
		} else {
			$zps = "";
		}

		$prijmeni = $r['prijmeni'];
		if ($r['cizinec'] == 1){
			$rowcls = 'rowcls5';
			$prijmeni = sprintf("<div class=\"tooltip\">%s<span class=\"tooltiptext\">cizinec - %s</span></div>", $r['prijmeni'], $r['misto_nar']);
		}

		if ($atyp == "5") {
		$p .= sprintf(
"<tr align=left class=%s>
	<td align=center>%s</td>
	<td align=right>%s</td>
	<td>%s</td><td>%s</td>
	<td>%s</td>
	<td>%s</td>
	<td align=center>%s</td>
	<td align=center width=25>%s</td>
	<td align=center width=25>%s</td>
	<td align=center width=25>%s</td>
	<td align=center width=25>%s</td>
	<td align=center width=25>%s</td>
	<td align=center width=25>%s</td>
	<td align=center class=\"cool\">%s</td>
	<td align=center>%s</td>
</tr>", $rowcls, $poradi, $zps.$r["id"], $r["aid"], $prijmeni, $r["jmeno"], $r["izo_zs"], $r["vstup"], $r["m"], $r["cj"], $r["aj"], $r["osp"], $r["pz"], $r["oz"], $r["celkem"], $prijat);
		} else {
		$p .= sprintf(
"<tr align=left class=%s>
	<td align=center>%s</td>
	<td align=\"center\">%s</td>
	<td>%s</td>
	<td>%s</td>
	<td align=\"left\">%s</td>
	<td align=right style=\"background-color:MediumBlue;\">%s</td>
	<td align=right style=\"background-color:MediumBlue;\">%s</td>
	<td align=right style=\"background-color:MediumBlue;\">%s</td>
	<td align=right style=\"background-color:MidnightBlue;\">%s</td>
	<td align=center style=\"background-color:Maroon;\">%s</td>
	<td align=center style=\"background-color:DarkSlateGrey;\">%s</td>
	<td align=right style=\"background-color:DarkSlateGrey;\">%s</td>
	<td align=center>%s</td>
	<td align=center>%s</td>
</tr>", $rowcls, $zps.$r["id"], $r['aid'], $prijmeni, $r["jmeno"], get_zs($r["izo_zs"]), $r["vstup"], $r["m"], $r["cj"], $r["celkem"], $poradi, $poradi_redukovane,   number_format($r['celkem']-$r['cj'], 2), $prijat, $listek);

}
	}
    $p .= "</table>";
    return $p;
    } 
 
    # typ=3 - ¹estileté
	if(!isset($atyp) || $atyp=="") $atyp=3;
	$btnSearch = "Vyhledat";
	$btnMake = "Vyhodnotit PZK";
	$btnZero = "Vymazat";

	# uprava 17.3.06 - ucast=4 -> bez PZK
   	$ucast = "(ucast=1 OR ucast=3 OR ucast=4)";
	$img_ok = sprintf(
"&nbsp;<img src=\"%s\" alt=\"%s\" border=0>&nbsp;","imgs/apply.gif","ok");

	# -- statistika --
	$res = dbi_select(uchazec(),"id_studium=$atyp AND $ucast","COUNT(*)");
	list($c_pocet) = $res->fetch_array();
	$res = dbi_select(uchazec(),"id_studium=$atyp AND prijat=1 AND $ucast","COUNT(*)");
	list($c_prijato) = $res->fetch_array();
	$res = dbi_select(uchazec(),"id_studium=$atyp AND prijat=0 AND $ucast","COUNT(*)");
	list($c_neprijato) = $res->fetch_array();
	$res = dbi_select(uchazec(),"id_studium=$atyp AND listek=1 AND $ucast","COUNT(*)");
	list($c_listek) = $res->fetch_array();
    $res = dbi_select(uchazec(),"id_studium=$atyp AND splnil=0 AND $ucast","COUNT(*)");
	list($c_nesplnil) = $res->fetch_array();
	
    # -- kriteria vyhodnoceni --
	$minbody   = readCfg("minbody".$atyp);
	$maxporadi = readCfg("maxporadi".$atyp);
	$kriterium = readCfg("vyhodnoceni");
	$min_body_zk = readCfg("min_body_zk");
	$img_minbody = $img_maxporadi = "&nbsp;";
	$GLOBALS["img_".$kriterium] = h_href("setkriterium.php?atyp=$atyp&kriterium=1",$img_ok,"Pøehodit kritérium");
	if(isset($_zapsani) && $_zapsani) { $zapsani = "AND listek=1"; }
		else { $zapsani = '';}

	if($atyp=="2") 
    {
        $czj = "<td class=tblhead>Czj</td>";
    } else {
        $czj = $czj2 ="";
    }
    $pg = sprintf(
"<form>
<table class=nadpis1 cellspacing=0 cellpadding=1>
<tr><td>
<table border=0 class=tblmain width=%s cellpadding=4>
<tr><td colspan=2>Typ studia &nbsp;%s&nbsp;%s zapsaní&nbsp;&nbsp;%s</td></tr>
<tr><td>
<table class=nadpis1 cellspacing=0 cellpaddong=1>
<tr><td>
<table class=rowcls1 cellspacing=0 cellpadding=3>
<tr><td colspan=3 align=center class=tblhead>Kritéria vyhodnocení</td></tr>
<tr><td class=rowcls1>min. získaných bodù&nbsp;</td><td align=right class=bjmeno>%s</td><td>%s</td></tr>
<tr><td class=rowcls1>max. dosa¾ené poøadí&nbsp;</td><td class=bjmeno align=right>%s</td><td>%s</td></tr>
<tr><td class=rowcls1>min. bodù ke splnìní&nbsp;</td><td class=bjmeno align=right>%s</td><td>&nbsp;</td></tr>
</form>
<form action=vyhodnoceni2_2023.php>
<tr><td class=rowcls1>nastavit pøijetí</td><td align=right>%s</td><td>&nbsp;</td></tr>
</table>
</td></tr>
</table>
</td>
<td>
<table class=nadpis1 cellspacing=0 cellpadding=1>
<tr><td>
<table class=rowcls1 cellspacing=0 cellpadding=3>
<tr><td class=rowcls1>Celkem uchazeèù&nbsp;</td><td class=bjmeno align=right>%s</td></tr>
<tr><td class=rowcls1>z toho pøijato</td><td class=bjmeno align=right>%s</td></tr>
<tr><td class=rowcls1>z toho nepøijato</td><td class=bjmeno align=right>%s</td></tr>
<tr><td class=rowcls1>podalo záp. lístek</td><td class=bjmeno align=right>%s</td></tr>
<tr><td class=rowcls1>nesplnilo PZK</td><td class=bjmeno align=right>%s</td></tr>
</table>
</td></tr>
</table>
</td></tr>
</table>
</td></tr>
</table><p>
%s&nbsp;&nbsp;%s %s  </p>
</form>","100%",mk_typ("atyp"),h_chbx('_zapsani'),h_btn("push",$btnSearch),
	h_href("setkriterium.php?atyp=$atyp",$minbody,"Nastavení kritéria"),$img_minbody,h_href("setkriterium.php?atyp=$atyp",$maxporadi),$img_maxporadi,$min_body_zk, h_chbx('_prijeti'),
	$c_pocet,$c_prijato,$c_neprijato,$c_listek,$c_nesplnil,
	h_btn("push",$btnMake),h_btn("push",$btnZero),h_hidden("atyp",$atyp));

    # -- výpis pøijatých --
    $pg .= "<b>Výpis uchazeèù, kteøí splnili zkou¹ku</b><br>";
    $res = dbi_select(uchazec(),"id_studium=$atyp AND $ucast AND splnil=1 $zapsani","*","poradi_od, zps DESC, m+cj DESC, m DESC, prijmeni,jmeno");
	$pg .= list_table($atyp, $res);
    
    # -- výpis nepøijatých --
    $pg .= "<br><b>Výpis uchazeèù, kteøí nesplnili zkou¹ku</b><br>";
    $res = dbi_select(uchazec(),"id_studium=$atyp AND $ucast AND splnil=0 $zapsani","*","celkem DESC, zps DESC, m+cj DESC, m DESC, prijmeni, jmeno");
	$pg .= list_table($atyp, $res);
    
    $pg .= sprintf("
</table>
<br>
Pozn.: Je-li v polo¾ce <b>Id</b> znak <b>*</b>, uchazeè má ZPS");
	h_page($pg,"PZK, Vyhodnocení","pzk.css");
?>

