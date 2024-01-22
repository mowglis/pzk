<?
	#####################################
	# vypis seznamu studentu v ucebne
	#####################################
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

	$pg_name = "Seznam u�ebny";
	if (!isAdminUser()) {
		$pg = "Nejste autorizov�n pro vstup na tuto str�nku!";
		h_page($pg,$pg_name,"pzk.css");
		exit;
	}
# kontrola
	if(!isset($id_ucebna) || $id_ucebna=="") exit;
	$res1 = dbSelect("ucebna","id_ucebna=$id_ucebna","skupina,ucebna,popis,vyuziti_1,vyuziti_2");
	$res = dbSelect(uchazec().",zs",uchazec().".izo_zs=zs.izo AND id_ucebna=$id_ucebna","prijmeni,jmeno,zs.nazev,termin,id,aid","termin,prijmeni,jmeno");
	list($skupina,$ucebna,$popis,$vyuziti_1,$vyuziti_2) = mysql_fetch_array($res1);
	### tabulka - zahlavi tridy
	$pg = sprintf(
"<table border=1 cellspacing=0 cellpadding=3 class=rowcls1>
<tr>
<td class=tblhead>skupina</td><td class=rowcls1>%s</td>
</tr>
<tr>
<td class=tblhead>ub�ebna</td><td class=rowcls1>%s</td>
</tr>
<tr>
<td class=tblhead>popis</td><td class=rowcls1>%s</td>
</tr>
<tr>
<td class=tblhead>po�et/po�et</td><td class=rowcls1>%s / %s</td>
</tr></table>
<br>",$skupina,$ucebna,$popis,$vyuziti_1,$vyuziti_2);
	### tabulka studentu
	$pg .= sprintf(
"<table border=1 cellspacing=0 cellpadding=5>
<tr class=nadpis1>
<td class=tblhead>Term�n</td>
<td class=tblhead align=center>&nbsp;&nbsp;ID&nbsp;&nbsp;</td>
<td class=tblhead align=center>&nbsp;&nbsp;AID&nbsp;&nbsp;</td>
<td class=tblhead>P��jmen�</td>
<td class=tblhead>Jm�no</td>
<td class=tblhead>�kola</td>
</tr>");
	while(list($prijmeni,$jmeno,$skola,$termin,$id,$aid) = mysql_fetch_array($res)) {
		$rowcls = "rowcls".(substr($termin,10,1) % 2);
		$pg .= sprintf(
"<tr class=%s>
<td>%s</td><td align=right>%s</td><td align=center>%s</td><td>%s</td><td>%s</td><td>%s</td>
</tr>",$rowcls,readCFG($termin),$id,$aid,$prijmeni,$jmeno,$skola);
	}
	$pg .= sprintf("
</table>");
	### tlaciltko pro rozmisteni
	$pg .= sprintf(
"<br><form action=\"%s\">%s</form>","ucebna.php",h_btn("btn","&lt;&lt; Zp�t"));
	h_page($pg,$pg_name,"pzk.css");
?>
