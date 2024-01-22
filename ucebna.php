<?
	#----------------------
	# rozmisteni do uceben
	#----------------------
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

	$pg_name = "Rozmístìní uchazeèù do uèeben";
	if (!isAdminUser()) {
		$pg = "Nejste autorizován pro vstup na tuto stránku!";
		h_page($pg,$pg_name,"pzk.css");
		exit;
	}
    $prefix_studium=array(0=>'', 1=>'P', 3=>'S');
    $ucast = "(ucast=1 OR ucast=3)";
    $img_ok = sprintf(
"&nbsp;<img src=\"%s\" alt=\"%s\">&nbsp;","imgs/apply.gif","ok");
	$img_bad = sprintf(
"&nbsp;<img src=\"%s\" alt=\"%s\">&nbsp;","imgs/help.gif","failure");
    $res = dbi_select(uchazec().",studium",uchazec().".id_studium=studium.id_studium AND $ucast AND poslat_pozvanku=1","COUNT(*) as pocet,prefix,".uchazec().".id_studium as id_studium,termin","termin",'termin,'.uchazec().".id_studium");
    #---  souhrnna tabulka	
    $pg = sprintf(
"<table class=tblhead cellspacing=0 cellpadding=1>
<tr><td>
<table border=1 cellspacing=0 cellpadding=3 class=tblmain>
<tr class=nadpis1 align=center>
<td class=tblhead>Termín</td>
<td class=tblhead>Studium</td>
<td class=tblhead>Poèet<br>uchazeèù</td>
<td class=tblhead>Poèet<br>uèeben</td>
<td class=tblhead>Kapacita<br>uèeben</td>
<td class=tblhead>&nbsp;</td>
</tr>");
    while($r = $res->fetch_array()) 
    {
		$id_termin = substr($r['termin'],10,1);
		$res2 = dbi_select("ucebna","id_studium_$id_termin=".$r["id_studium"],"COUNT(*) as ucebny,SUM(kapacita) as s_kapacita");
		$r2 = $res2->fetch_array();
		if($r["pocet"] <= $r2["s_kapacita"]) {
			$ok = $img_ok;
		} else {
			$ok = $img_bad;
		}
		$rowcls = "row_ucebna".$r["id_studium"];
		$url = sprintf("report.php?whr=id_studium|%s*ucast|1&templ=ucebna",$r["id_studium"]);
		$pg .= sprintf(
"<tr align=right class=%s>
<td class=polozka2>%s</td>
<td align=center class=polozka2>%s</td><td class=polozka2>%s</td><td class=polozka2>%s</td><td class=polozka2>%s</td><td align=center>%s</td>
</tr>",$rowcls,readCFG($r["termin"]),$r["prefix"],$r["pocet"],$r2["ucebny"],$r2["s_kapacita"],$ok);
	}
    $pg .= sprintf("
</table>
</td></tr>
</table>");
    # -- tlaciltko pro rozmisteni
    $pg .= sprintf(
"<br><form action=\"%s\">%s</form>","ucebna2.php",h_btn("btn","Rozmísti do uèeben"));
### vypis jednotlivych uceben
    $pg .= sprintf(
"<table border=1 cellspacing=0 cellpadding=4 class=tblmain>
<tr><td colspan=6><a href=%s>Pøidat uèebnu</td></tr>
<tr>
<td class=tblhead>Skupina</td>
<td class=tblhead>Uèebna</td>
<td class=tblhead>Popis</td>
<td class=tblhead>Kapacita</td>
<td class=tblhead align=center>Vyu¾ití<br>%s</td>
<td class=tblhead align=center>Vyu¾ití<br>%s</td>
</tr>","setucebna.php?id_ucebna=9999",readCFG('datum_pzk_1'),readCFG('datum_pzk_2'));
    # abecední (lexikografické) tøídìní uèeben
    //$res = dbSelect("ucebna","","ucebna.*","CONVERT(skupina,char(3))");
    # -- klasické tøídìní
    $res = dbi_select("ucebna","","ucebna.*","skupina");
    $href_templ="<a href=\"%s\" title=\"%s\">%s</a>";
	while ($r = $res->fetch_array()) 
    {
		$id_ucebna = $r["id_ucebna"];
		if($r["vyuziti_1"] > 0 || $r["vyuziti_2"] > 0 ) {
			$href_ucebna = sprintf($href_templ,"listucebna.php?id_ucebna=$id_ucebna","Seznam uchazeèù v uèebnì",$r["skupina"]);
		} else {
			$href_ucebna = $r["skupina"];
		}
		if($r["vyuziti_1"] > 0 ) {
			$href_ucebna_1_pdf = sprintf($href_templ,"report.php?whr=id_ucebna|$id_ucebna&templ=ucebna2&idt=1","Seznam uchazeèù v uèebnì (PDF) - 1. termín",$r["vyuziti_1"]);
		} else {
			$href_ucebna_1_pdf = $r["vyuziti_1"]; 
		}
		if($r["vyuziti_2"] > 0 ) {
			$href_ucebna_2_pdf = sprintf($href_templ,"report.php?whr=id_ucebna|$id_ucebna&templ=ucebna2&idt=2","Seznam uchazeèù v uèebnì (PDF) - 2. termín",$r["vyuziti_2"]);
		} else {
			$href_ucebna_2_pdf = $r["vyuziti_2"]; 
		}
		$href_kapacita = sprintf($href_templ,"setucebna.php?id_ucebna=$id_ucebna","nastavit kapacitu uèebny",$r["kapacita"]);
		$rowcls_1 = "row_ucebna".$r["id_studium_1"];
		$rowcls_2 = "row_ucebna".$r["id_studium_2"];
		if($r["kapacita"] != $r["vyuziti_1"]) {
			$clsTD_1 = "cool";
		} else {
			$clsTD_1 = "";
		}
		if($r["kapacita"] != $r["vyuziti_2"]) {
			$clsTD_2 = "cool";
		} else {
			$clsTD_2 = "";
		}
		$rowcls = "row_ucebna1";
		if ($r['kapacita'] == 0 ) {
			$rowcls = "row_grey";	
		} 
		if($r["id_ucebna"] != 0) {
			$pg .= sprintf(
"<tr align=center class=%s>
<td>%s</td>
<td>%s</td>
<td align=left>%s</td>
<td>%s</td>
<td class=%s>%s</td>
<td class=%s>%s</td>
</tr>", $rowcls,$href_ucebna,$r['ucebna'],$r["popis"],$href_kapacita,$clsTD_1,$href_ucebna_1_pdf,
    $clsTD_2,$href_ucebna_2_pdf);
		}
	}
    $pg .= "</table>";
    h_page($pg,$pg_name,"pzk.css");
?>

