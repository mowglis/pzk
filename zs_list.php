<?
	######################
	# zs
	######################
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

    $pg_name = "��seln�k Z�";
	/*
    if (!isAdminUser()) {
		$pg = "Nejste autorizov�n pro vstup na tuto str�nku!";
		h_page($pg,$pg_name,"pzk.css");
		exit;
	}
    */
	$pg = sprintf ("
<table border=\"1\" cellspacing=0 cellpadding=3 class=tblmain>
<tr>
<td class=\"tblhead\" colspan=\"1\" align=\"center\">&nbsp;&nbsp;&nbsp;<a href=\"zs_edit.php\" title=\"P�idat novou �kolu\"><img src=\"%s\"</a></td>
<td class=\"tblhead\" colspan=\"5\">&nbsp;&nbsp;&nbsp;<a href=\"%s\" title=\"Rejst��k �kol\">Rejst��k �kol</a></td>
</tr>
<tr>
<td class=tblhead>N�zev</th>
<td class=tblhead>Ulice</th>
<td class=tblhead>PS�</th>
<td class=tblhead>M�sto</th>
<td class=tblhead>IZO</th>
<td class=tblhead>E-mail</th>
</tr>", "imgs/new.png","https://profa.uiv.cz/rejskol/");
	$res = dbSelect('zs','','*','nazev');
	while($r = mysql_fetch_array($res)) {
		if ($r['id_zs'] == 0) continue;
        $report_izo = sprintf("<a href=\"report.php?whr=izo|%s&templ=zs\">%s</a>", $r["izo"], $r["izo"]);
		$pg .= sprintf("
<tr class=\"row_ucebna0\">		
<td>&nbsp;<a href=\"zs_edit.php?id_zs=%s\" title=\"Editovat �kolu\">%s</a></td><td>&nbsp;%s</td><td>&nbsp;%s</td><td>&nbsp;%s</td><td>%s</td><td>&nbsp;%s</td>
</tr>",$r['id_zs'],$r["nazev"],$r["ulice"],$r["psc"],$r["misto"],$report_izo,$r["email"]);
	}
	$pg .= sprintf("
</table>");
	h_page($pg,$pg_name,"pzk.css");
?>

