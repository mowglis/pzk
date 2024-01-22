<?
	########################################
	# statistika uspesnosti jednotlivych ZS
	########################################
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

    $ucast = "(ucast=1 OR ucast=3)";
	$img_ok = sprintf(
"&nbsp;<img src=\"%s\" alt=\"%s\">&nbsp;","imgs/apply.gif","ok");
	$img_bad = sprintf(
"&nbsp;<img src=\"%s\" alt=\"%s\">&nbsp;","imgs/help.gif","failure");
	$res = dbSelect(uchazec().",studium",uchazec().".id_studium=studium.id_studium AND $ucast","COUNT(*) as pocet,prefix,".uchazec().".id_studium as id_studium",uchazec().".id_studium",uchazec().".id_studium");
### souhrnna tabulka	
	$pg = sprintf(
"<table class=tblhead cellspacing=0 cellpadding=1>
<tr><td>
<table border=1 cellspacing=0 cellpadding=3 class=tblmain>
<tr class=nadpis1 align=center>
<td class=tblhead>Studium</td>
<td class=tblhead>Poèet<br>uchazeèù</td>
<td class=tblhead>Poèet<br>uèeben</td>
<td class=tblhead>Kapacita<br>uèeben</td>
<td class=tblhead>&nbsp;</td>
</tr>");
	while($r = mysql_fetch_array($res)) {
		$res2 = dbSelect("ucebna","id_studium=".$r["id_studium"],"COUNT(*) as ucebny,SUM(kapacita) as s_kapacita");
		$r2 = mysql_fetch_array($res2);
		if($r["pocet"] <= $r2["s_kapacita"]) {
			$ok = $img_ok;
		} else {
			$ok = $img_bad;
		}
		$rowcls = "rowcls".($r["id_studium"] % 2);
		$url = sprintf("report.php?whr=id_studium|%s*ucast|1&templ=ucebna",$r["id_studium"]);
		$pg .= sprintf(
"<tr align=right class=%s>
<td align=center class=polozka2>%s</td><td class=polozka2>%s</td><td class=polozka2>%s</td><td class=polozka2>%s</td><td align=center>%s</td>
</tr>",$rowcls,h_href($url,$r["prefix"],"Seznam uèeben - tisk (PDF)"),$r["pocet"],$r2["ucebny"],$r2["s_kapacita"],$ok);
	}
	$pg .= sprintf("
</table>
</td></tr>
</table>");
### vypis jednotlivych ZS
	$pg .= sprintf(
"<br>
<table border=1 cellspacing=0 cellpadding=3 class=tblmain>
<tr>
<th class=tblhead>Z©</th>
<th class=tblhead>IZO</th>
<th class=tblhead>uchazeèù</th>
<th class=tblhead>pøijato</th>
<th class=tblhead>%%</th>
<th class=tblhead>M &empty;</th>
<th class=tblhead>Èj &empty;</th>
</tr>");
	$uchazec = uchazec();
	$res = dbSelect("$uchazec,zs,studium","$ucast and $uchazec.izo_zs=zs.izo and $uchazec.id_studium=studium.id_studium","izo_zs, zs.nazev as zs, studium.prefix as studium,count(*) as pocet, sum(prijat) as prijato, sum(prijat)/count(*)*100 as procent,sum(m)/count(*) as matika, sum(cj)/count(*) as cestina","prijato DESC","izo_zs, $uchazec.id_studium");
	$old_zs = ""; $i=0;
	while($r = mysql_fetch_array($res)) {
		$pg .= sprintf(
"<tr align=center class=%s>
<td align=\"left\">%s</td><td>%s</td><td>%s</td><td>%s</td><td align=right>%s</td><td align=right>%s</td><td align=right>%s</td></tr>"
,$rowcls,$r["zs"],$r["izo_zs"],$r["pocet"],$r["prijato"],round($r["procent"],2)."%",round($r["matika"],2),round($r["cestina"],2));
		}
	$pg .= "</table>";
	h_page($pg,"PZK, Statistika Z©","pzk.css");
?>

