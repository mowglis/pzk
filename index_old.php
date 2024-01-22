<?
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

    $btnTempl = "<a href=\"%s\" title=\"%s\"><img src=\"%s\" border=0></a>";
	$btnTemplDel = "<a href=\"javascript:confirmDel(%s,%s,'%s')\" title=\"%s\"><img src=\"%s\" border=0></a>";
	$empty_cell = '--';
	
	# implicitni hodnoty pro vyber
	if(!isset($atyp) || $atyp=="") $atyp = "3";
	if(!isset($prijeti) || $prijeti=="") $prijeti = "2";
	if(!isset($aucast) || $aucast=="") $aucast = "1";
	if(!isset($termin) || $termin=="") $termin = "0";
	
	# sestaveni podminky vyberu
	$uchazec = uchazec();
	$whr ="$uchazec.id_zs=zs.id_zs and $uchazec.id_studium=studium.id_studium and $uchazec.id_ucebna=ucebna.id_ucebna and";
	if($atyp!="" && $atyp!="0") $whr .= " $uchazec.id_studium=$atyp and";
	if($pr!= "") $whr .= " prijmeni like '$pr%' and";
	if($prijeti!="" && $prijeti!="2") $whr .= " prijat=$prijeti and";
	if($aucast!="9") $whr .= " ucast=$aucast and";
	if($termin!="0") $whr .= " termin='$termin' and";
	$whr = substr($whr,0,-4);	
	$tbl = "$uchazec,zs,studium,ucebna";
//   echo "whr: $whr";
	$res = dbSelect($tbl,$whr,"$uchazec.*,zs.id_zs,zs.nazev,studium.*,ucebna.ucebna","prijmeni,jmeno");
//	$res = dbSelect($tbl,$whr,"$uchazec.*,zs.id_zs,zs.nazev,studium.*,ucebna.ucebna","id");
	# dialog pro nastaveni  kriteria vyberu
	$prijeti_v = array("2","0","1"); $prijeti_d = array("-- neuvedeno --","nepøijatí","pøijatí");
	$pg .= 
"<div align=center>
<table border=0 cellpadding=2 cellspacing=0>
<tr><td align=center>\n";

	$pg .= sprintf(
"<form name=\"setselect\">
<table class=nadpis1 cellspacing=0 cellpadding=1>
<tr><td>
<table border=0 class=tblmain>
<tr><td colspan=4 class=nadpis1 align=center>Zadejj výbìr</td></tr>
<tr><td>Typ studia</td><td>%s</td><td>Pøijetí</td><td>%s</td></tr>
<tr><td>Termín</td><td>%s</td><td>Stav</td><td>%s</td></tr>
<tr><td>Pøíjmení</td><td colspan=3>%s</td></tr>
<tr><td colspan=4 align=center>%s</td></td>
</table>
</td></tr>
</table>
</form>",
	mk_typ("atyp"),h_sb("prijeti",$prijeti_v,$prijeti_d,$prijeti),mk_termin('termin'),mk_stav("aucast"),h_input("pr","36"),h_btn("push","Vyhledat"));

	$i=0;
	if(mysql_num_rows($res) > 0) {
		# tabuka pro vypis vyhledanych zaznamu
		$pg .= sprintf(
"<table class=nadpis1 cellspacing=0 cellpadding=1>
<tr><td>
<table class=tblmain cellspacing=0 cellpadding=3>
<tr>
<td class=tblhead>Typ</td>
<td class=tblhead>Uchazeè [id] (è.j.)<br>Zákonný zástupce<br>Bydli¹tì</td>
<td class=tblhead>©kola<br>prùmìry</td>
<td class=tblhead>Bon|Pr|Vst|Celkem<br>M|Èj|OSP|Aj|Pz|Oz</td>
<td class=tblhead valign=\"bottom\">záznamù: %s</td>
</tr>",mysql_num_rows($res));
		while($r = mysql_fetch_array($res)) {
			$i++;
			$rowclass = "rowcls".$i%2;
			$prumery = sprintf(
"<table  border=1 cellpadding=2 cellspacing=0>
<tr><td>%s</td><td>%s</td><td class=\"minicool\">%s</td></tr>
</table>",$r["p1"],$r["p2"],$r["body"]);
			if($r["id_studium"] == 1 || $r["id_studium"] == 3) {
				$czj = $aj = $pz = $oz = $empty_cell;
			} else {
				$czj= $r["jazyk"];
				$aj = $r["aj"];
				$pz = $r["pz"];
				$oz = $r["oz"];
			}
            # 2015 - bez OSP
            $r['osp'] = $empty_cell;
			$vysledky = sprintf(
"<table border=1 cellspacing=0 cellpadding=2>
<tr align=\"right\"><td>%s</td><td>%s</td><td class=\"minicool\">%s</td><td colspan=\"3\" class=\"cool\" align=\"center\">%s</td></tr>
<tr align=\"right\"><td><b>%s</b></td><td><b>%s</b></td><td><b>%s</b></td><td><b>%s</b></td><td><b>%s</b></td><td><b>%s</b></td></tr>
</table>",$r["bonifikace"],$r["body"],$r["vstup"],$r["celkem"],$r["m"],$r["cj"],$r["osp"],$aj,$pz,$oz);
			$btnEdit = sprintf($btnTempl,"edit2.php?editid=".$r["id"]."&atyp=$atyp","Editace výsledkù uchazeèe","imgs/edit.gif");
//			if($r["id_studium"] == 2) $templ = "pozv_h";
//				else $templ = "pozv_ps";
			$templ = "pozv";
			$btnPDF = sprintf($btnTempl,"report.php?whr=id|".$r["id"]."&atyp=".$r["id_studium"]."&templ=$templ","Pozvánka pro uchazeèe (PDF)","imgs/pdf.gif");
			if($r['prijat']) { 
				$btnPDF2 = sprintf($btnTempl,"report.php?whr=id|".$r["id"]."&atyp=".$r["id_studium"]."&templ=rozhodnuti2","Rozhodnutí po odvolání (PDF)","imgs/pdf.gif");
			} else { $btnPDF2 = "&nbsp;";}
			$btnDelete = sprintf($btnTemplDel,$r["id_studium"],$r["id"],$r["prijmeni"]." ".$r["jmeno"],"Vymazání uchazeèe z databáze","imgs/delete.gif");
			$akce = sprintf(
"<table border=0 cellspacing=0 cellpadding=2>
<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>
</table>
",$btnPDF,$btnPDF2,$btnEdit,$btnDelete);
			if($r["ucast"] % 2 == "0") {
				$neucast = " -- neúèast--";
				$clsjmeno = "bjmenonot";
			} else {
				$neucast = "";
				$clsjmeno = "bjmeno";
			}
			# prijati bez PZK
			if($r["ucast"]  == "4") {
				$neucast = " -- bez PZK --";
				$clsjmeno = "bjmenonot";
			}; 			
			if($r["zps"]){
				$zps = "*";
			} else {
				$zps = "";
			}
			$pg .= sprintf(
"<tr class=%s><td valign=top class=bjmeno>%s - <small>%s</small><br>%s<br><small>%s</small></td><td valign=top><span class=%s>%s</span> [<a href=\"edit.php?id=%s&atyp=%s\" title=\"plná editace uchazeèe\" border=0>%s</a>] (%s)%s<br>%s<br>%s</td><td valign=top>%s<br>%s</td><td>%s</td><td>%s</td></tr>",$rowclass,$r["prefix"],prevzal($r["id"],$r["prevzal"],$r["pohlavi"]), isPrijat($r["id"],$r["prijat"],$r["pohlavi"]),odvolani($r["id"],$r["odvolani"],$r["prijat"]),$clsjmeno,$r["prijmeni"]." ".$r["jmeno"].$zps,$r["id"],$atyp,$r["id"],$r['cj0'],$neucast,$r['zast_prijmeni'].' '.$r['zast_jmeno'],$r["ulice"]."<br>".$r["psc"].", ".$r["misto"],$r["nazev"],$prumery,$vysledky,$akce);
		}
		$pg .= sprintf(
"</table>
</td></tr>
</table>");
	} else {
		$pg .= "<h2 align=center>Nebyly nalezeny ¾ádné záznamy</h2>";
	}
	$pg .= sprintf(
"</td></tr>
</table>
</div>");
	h_page($pg,"Pøijímací zkou¹ky - Gybon","pzk.css","setselect.pr","libpzk/index.ins.js");
?>	
