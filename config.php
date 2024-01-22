<?
	#################################################
	# konfigurace - nastavení konfiguraèních hodnot 
	#################################################
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

	if (!isAdminUser()) {
		$pg = "Nejste autorizován pro vstup na tuto stránku!";
		h_page($pg,"Nastavení konfigurace","pzk.css");
		exit;
	}
	if (isset($_POST['write']) && $_POST["write"] == true) {
		foreach ($_POST as $ident => $value)
        {
			$cols = array("value" => "$value");
			$whr = "ident='$ident'";
			dbi_update('config', $cols, $whr);
		}
	}
	$img_ok = sprintf(
"&nbsp;<img src=\"%s\" alt=\"%s\">&nbsp;","imgs/apply.gif","ok");
	$img_bad = sprintf(
"&nbsp;<img src=\"%s\" alt=\"%s\">&nbsp;","imgs/help.gif","failure");
	$pg = "Pracujete s konfigurací jako u¾ivatel: ".readUser('long_name');
	$res = dbSelect('config','visible=1');
	$pg .= sprintf(
	"<form method=\"post\" action=\"%s\"","config.php");
	$pg .= sprintf(
"<table class=tblhead cellspacing=0 cellpadding=1>
<tr><td>
<table border=1 cellspacing=0 cellpadding=3 class=tblmain>
<tr class=nadpis1 align=center>
<td class=tblhead>Název polo¾ky</td>
<td class=tblhead>Hodnota</td>
<td class=tblhead>Popis polo¾ky</td>
</tr>");
	while(list($id_cfg,$ident,$value,$descript) = mysql_fetch_array($res)) {
		if(empty($descript)) { $descript='&nbsp;'; }
		$pg .= sprintf(
"<tr>
<td class=polozka2>%s</td>
<td class=polozka2><input type=\"text\" name=\"%s\" value=\"%s\" size=\"40\"></td>
<td class=polozka2>%s</td>
</tr>",$ident,$ident,$value,$descript);
	}
	$pg .= sprintf("
<tr><td align=\"center\" colspan=\"3\" class=polozka2>%s</td></tr>
</table>
</td></tr>
</table>
<input type=\"hidden\" name=\"write\" value=\"true\">
</form>",h_btn("btn","Zapi¹ konfiguraci"));
	h_page($pg,"Nastavení konfigurace","pzk.css");
?>

