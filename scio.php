<?
	#####################################
	# editace ucebny
	#####################################
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

	$title = "PZK - SCIO";
	if (!isAdminUser()) {
		$pg = "Nejste autorizov�n pro vstup na tuto str�nku!";
		h_page($pg,$title,"pzk.css");
		exit;
	}
	$exp_file_1_1 = 'uchazeci_scio_4l_1.csv';
	$exp_file_1_2 = 'uchazeci_scio_4l_2.csv';
	$exp_file_3_1 = 'uchazeci_scio_6l_1.csv';
	$exp_file_3_2 = 'uchazeci_scio_6l_2.csv';
	$url_scio = 'http://prijimacky.scio.cz';
	# *** Texty tlacitek *** 
	$btnClear_AID = "Vymazat SCIO_ID";
	$btnSet_AID   = "Nastavit SCIO_ID";
	$btnExport_1_1 = "Export uchaze�� (4l) - 1. term�n";
	$btnExport_1_2 = "Export uchaze�� (4l) - 2. term�n";
	$btnExport_3_1 = "Export uchaze�� (6l) - 1. term�n";
	$btnExport_3_2 = "Export uchaze�� (6l) - 2. term�n";
	$btnImport_1 = "Import v�sledk�";
//	$btnImport_3 = "Import v�sledk� - studium S";
	if ($odeslano) {
		# akce
		switch ($push) {
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
			case $btnExport_1_1:
				$path = GetPath($REQUEST_URI)."/export_file.php?filename=$exp_file_1_1&id_studium=1&idt=1";
//				Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
                Header(get_location($path));
				break;
			case $btnExport_1_2:
				$path = GetPath($REQUEST_URI)."/export_file.php?filename=$exp_file_1_2&id_studium=1&idt=2";
//				Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
                Header(get_location($path));
				break;
			case $btnExport_3_1:
				$path = GetPath($REQUEST_URI)."/export_file.php?filename=$exp_file_3_1&id_studium=3&idt=1";
//				Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
                Header(get_location($path));
				break;
			case $btnExport_3_2:
				$path = GetPath($REQUEST_URI)."/export_file.php?filename=$exp_file_3_2&id_studium=3&idt=2";
//				Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
                Header(get_location($path));
				break;
			case $btnImport_1:
				break;
		}
	}

	$pg = sprintf(" 
<FORM  METHOD=POST NAME=setdata onSubmit=\"return validate()\"> 
<DIV ALIGN=CENTER>");

	$import_file_form = "<br>
	<form method=\"post\" action=\"import_file.php\" enctype=\"multipart/form-data\" onSubmit=\"return validate()\">
		<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"8000000\">
		<input type=\"file\" name=\"i_file\" size=\"50\">
		<br><br>%s<br>
	</form>";

	$import_file_1 = sprintf($import_file_form,h_btn("push",$btnImport_1));
//	$import_file_3 = sprintf($import_file_form,h_btn("push",$btnImport_3));

	$pg .= h_hidden("odeslano","true");
	$pg .= sprintf("
<table class=tblhead cellspacing=0 cellpadding=1>
<tr><td>
  <table border=1 cellspacing=0 cellpadding=3 class=tblmain>
  <tr><th colspan=2>Nastaven� anonymn�ho ID</th></tr>
  <tr><td align=center>%s</td><td>%s</td></tr>
  <tr><td align=center>%s</td><td>%s</td></tr>
  <tr><th colspan=2>Export uchaze�� pro SCIO</th></tr>
  <tr><td align=center>%s</td><td>%s</td></tr>
  <tr><td align=center>%s</td><td>%s</td></tr>
  <tr><td align=center>%s</td><td>%s</td></tr>
  <tr><td align=center>%s</td><td>%s</td></tr>
  </form>
  <tr><th colspan=2>Import v�sledk� ze SCIO</th></tr>
  <tr><td align=center colspan=2>%s</td></tr>
	",
	h_btn("push",$btnClear_AID),"Vymaz�n� anonymn�ho ID (SCIO_ID) u v�ech uchaze��",
	h_btn("push",$btnSet_AID),"Nastaven� anonymn�ho ID (SCIO_ID) u v�ech uchaze��",
	h_btn("push",$btnExport_1_1),"Export uchaze�� pro SCIO - 4l - 1. term�n",
	h_btn("push",$btnExport_1_2),"Export uchaze�� pro SCIO - 4l - 2. term�n",
	h_btn("push",$btnExport_3_1),"Export uchaze�� pro SCIO - 6l - 1. term�n",
	h_btn("push",$btnExport_3_2),"Export uchaze�� pro SCIO - 6l - 2. term�n",
	$import_file_1
	);
	$pg .= sprintf("
	</table>	
</td></tr></table>	
	");
	$pg .= sprintf ("
<br>SCIO p�ij�ma�ky: <a href=\"%s\" target=\"_blank\">%s</a>", $url_scio,$url_scio);
	h_page($pg,$title,"pzk.css",'','libpzk/scio.ins.js');
?>
