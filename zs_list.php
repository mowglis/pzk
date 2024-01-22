<?
	######################
	# zs
	######################
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

    $pg_name = "Èíselník Z©";
	/*
    if (!isAdminUser()) {
		$pg = "Nejste autorizován pro vstup na tuto stránku!";
		h_page($pg,$pg_name,"pzk.css");
		exit;
	}
    */
	$pg = sprintf ("
<table border=\"1\" cellspacing=0 cellpadding=3 class=tblmain>
<tr>
<td class=\"tblhead\" colspan=\"1\" align=\"center\">&nbsp;&nbsp;&nbsp;<a href=\"zs_edit.php\" title=\"Pøidat novou ¹kolu\"><img src=\"%s\"</a></td>
<td class=\"tblhead\" colspan=\"5\">&nbsp;&nbsp;&nbsp;<a href=\"%s\" title=\"Rejstøík ¹kol\">Rejstøík ¹kol</a></td>
</tr>
<tr>
<td class=tblhead>Název</th>
<td class=tblhead>Ulice</th>
<td class=tblhead>PSÈ</th>
<td class=tblhead>Místo</th>
<td class=tblhead>IZO</th>
<td class=tblhead>E-mail</th>
</tr>", "imgs/new.png","https://profa.uiv.cz/rejskol/");
	$res = dbSelect('zs','','*','nazev');
	while($r = mysql_fetch_array($res)) {
		if ($r['id_zs'] == 0) continue;
        $report_izo = sprintf("<a href=\"report.php?whr=izo|%s&templ=zs\">%s</a>", $r["izo"], $r["izo"]);
		$pg .= sprintf("
<tr class=\"row_ucebna0\">		
<td>&nbsp;<a href=\"zs_edit.php?id_zs=%s\" title=\"Editovat ¹kolu\">%s</a></td><td>&nbsp;%s</td><td>&nbsp;%s</td><td>&nbsp;%s</td><td>%s</td><td>&nbsp;%s</td>
</tr>",$r['id_zs'],$r["nazev"],$r["ulice"],$r["psc"],$r["misto"],$report_izo,$r["email"]);
	}
	$pg .= sprintf("
</table>");
	h_page($pg,$pg_name,"pzk.css");
?>

