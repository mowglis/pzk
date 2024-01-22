<?
	#####################################
	# editace ucebny
	#####################################
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

	$title = "PZK - CERMAT";
	if (!isAdminUser()) {
		$pg = "Nejste autorizován pro vstup na tuto stránku!";
		h_page($pg,$title,"pzk.css");
		exit;
	}
	$exp_file = 'uchazeci_cermat_gybon.xml';
	$url_certis = 'https://certis.cermat.cz/';
	$url_cermat = 'http://www.cermat.cz/prijimaci-rizeni-sl-2016-1404035005.html';
    # *** Texty tlacitek *** 
	$btnClear_AID = "Vymazat AID";
	$btnSet_AID   = "Nastavit AID";
	$btnDelete_Data  = "Vymazat výsledky";
	$btnExport = "Export uchazeèù";
	$btnImport = "Import výsledkù";
	if (isset($odeslano) && $odeslano) {
		# akce
		switch ($push) {
            case $btnDelete_Data:
   				$r = dbSelect(uchazec(), '', 'id, vstup');
				while (list($id, $vstup) = mysql_fetch_array($r)) {
                    $cols = ['m'=>0, 'cj'=>0, 'celkem'=>$vstup];
					dbUpdate(uchazec(),$cols,"id=$id");
				}
                $expr = "DELETE FROM vysledek";
                $result = mysql_db_query($db_name,$expr,dbConnect(1));
                break;
			case $btnClear_AID:
				$cols = array('aid' => 0);
				dbUpdate(uchazec(),$cols);
				break;
			case $btnSet_AID:
				$aid_1 = readCFG('aid_prefix_1');
				$aid_3 = readCFG('aid_prefix_3');
				$r = dbSelect(uchazec(),'','id,id_studium','prijmeni,jmeno');
				while (list($id,$id_studium) = mysql_fetch_array($r)) {
					${'aid_'.$id_studium}+=1;
					$aid = ${'aid_'.$id_studium};
					$cols = array('aid' => $aid);
					dbUpdate(uchazec(),$cols,"id=$id");
				}
				break;
			case $btnExport:
				$path = GetPath($REQUEST_URI)."/export_file_cermat.php?filename=$exp_file&id_studium=3";
//				Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
                Header(get_location($path));
				break;
			case $btnImport:
				break;
		}
	}

	$pg = sprintf(" 
<FORM  METHOD=POST NAME=setdata onSubmit=\"return validate()\"> 
<DIV ALIGN=CENTER>");

	$import_file = sprintf("<form method=\"post\" action=\"import_file_cermat.php\" enctype=\"multipart/form-data\" onSubmit=\"return validate()\">
    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"8000000\">
    <input type=\"file\" name=\"i_file\" size=\"50\">
    <br><br>%s<br>
    </form>", h_btn("push",$btnImport));

	$pg .= h_hidden("odeslano","true");
	$pg .= sprintf("
<table class=\"tblhead\" cellspacing=\"0\" cellpadding=\"1\">
<tr><td>
  <table border=\"1\" cellpadding=\"5\" class=\"tblmain\">
  <tr><th colspan=2>Nastavení anonymního ID (AID)</th></tr>
  <tr><td align=center>%s</td><td>%s</td></tr>
  <tr><td align=center>%s</td><td>%s</td></tr>
  <tr><td align=center>%s</td><td>%s</td></tr>
  <tr><th colspan=2>Export uchazeèù pro CERMAT</th></tr>
  <tr><td align=center>%s</td><td>%s</td></tr>
  </FORM>
  <tr><th colspan=2>Import výsledkù z CERMATu</th></tr>
  <tr><td align=center colspan=2>
  %s
  </td></tr>",
	h_btn("push",$btnClear_AID),"Vymazání anonymního ID u v¹ech uchazeèù",
	h_btn("push",$btnSet_AID),"Nastavení anonymního ID v¹ech uchazeèù",
	h_btn("push",$btnDelete_Data),"Vymazání importovaných výledkù",
	h_btn("push",$btnExport),"Export uchazeèù pro CERMAT",$import_file);
	$pg .= sprintf("
	</table>	
</td></tr>
</table>");
	$pg .= sprintf ("
<br>CERMAT: <a href=\"%s\" target=\"_blank\">%s</a> 
<br>Certis: <a href=\"%s\" target=\"_blank\">%s</a>", 
$url_cermat,$url_cermat,$url_certis,$url_certis);
	h_page($pg,$title,"pzk.css",'','libpzk/scio.ins.js');
?>
