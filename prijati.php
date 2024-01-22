<?
	########################################
	# statistika - seznam prijatych zaku
	########################################
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

    $ucast = "(ucast=1 OR ucast=3 OR ucast=4)";
	$res = dbSelect(uchazec().",studium",uchazec().".id_studium=studium.id_studium AND $ucast","COUNT(*) as pocet,prefix,SUM(prijat) as prijato,".uchazec().".id_studium as id_studium, SUM(prijat)/COUNT(*) * 100 as procent",uchazec().".id_studium",uchazec().".id_studium");
### souhrnna tabulka	
	$pg = sprintf(
"<table class=tblhead cellspacing=0 cellpadding=1>
<tr><td>
<table border=1 cellspacing=0 cellpadding=3 class=tblmain>
<tr class=nadpis1 align=center>
<td class=tblhead>Studium</td>
<td class=tblhead>Poèet<br>uchazeèù</td>
<td class=tblhead>Poèet<br>pøijatých</td>
<td class=tblhead>Procent</td>
</tr>");
	while($r = mysql_fetch_array($res)) {
		$rowcls = "rowcls".($r["id_studium"] % 2);
		$pg .= sprintf(
"<tr align=right class=%s>
<td align=center class=polozka2>%s</td><td class=polozka2>%s</td><td class=polozka2>%s</td><td class=polozka2>%s</td></tr>",$rowcls,$r["prefix"],$r["pocet"],$r["prijato"],$r["procent"]."%");
	}
	$pg .= sprintf("
</table>
</td></tr>
</table>");
### vypis jednotlivych ZS
	$tbl_head = sprintf(
"<table border=1 cellspacing=0 cellpadding=3 class=tblmain>
<tr>
<td class=tblhead>Pøíjmení</td>
<td class=tblhead>Jméno</td>
<td class=tblhead>Z©</td>
<td class=tblhead>Adresa</td>
</tr>");
	
	$uchazec = uchazec();
	$res = dbSelect("$uchazec,zs,studium","$ucast and $uchazec.izo_zs=zs.izo and $uchazec.id_studium=studium.id_studium and $uchazec.prijat=1","prijmeni, jmeno, zs.nazev as zs, $uchazec.id_studium, $uchazec.ulice, $uchazec.misto, $uchazec.psc, studium.studium_long","$uchazec.id_studium,$uchazec.prijmeni,$uchazec.jmeno");
	$id_studium_prev = ""; $i=0;
	while(list($prijmeni,$jmeno,$zs,$id_studium,$ulice,$misto,$psc,$studium) = mysql_fetch_array($res)) {
		if($id_studium_prev != $id_studium) {
			$id_studium_prev = $id_studium;
			if($i != 0) {
				$pg .= sprintf("</table><br>");
			}
			$pg .= sprintf("
<h3>Studenti %s</h3>",$studium);
			$pg .= $tbl_head;
			$i++;
		}
		$rowcls = "rowcls0";
		$pg .= sprintf(
"<tr align=left class=%s>
<td>%s</td><td>%s</td><td>%s</td><td>%s, %s, %s</td>
</tr>"
,$rowcls,$prijmeni,$jmeno,$zs,$ulice,$misto,$psc);
	}
	$pg .= "</table>";
	h_page($pg,"PZK, Pøijatí studenti","pzk.css");
?>
