<?
	######################
	# zs - edit
	######################
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

	$pg_name = "Èíselník Z©";
	$btnCancel='Cancel';
	$btnSave='Zapsat';
    $info = "";
	if (!isAdminUser()) {
		$pg = "Nejste autorizován pro vstup na tuto stránku!";
		h_page($pg,$pg_name,"pzk.css");
		exit;
	}
	$ini = array('nazev'=>'','ulice'=>'','psc'=>'','misto'=>'');
	if (isset($odeslano) and $odeslano) {
		// zapis do db
		if($push == $btnCancel) {
			// navrat bez ulozeni dat 	
			$path = GetPath($SCRIPT_NAME)."/zs_list.php";
//			Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
            Header(get_location($path));
      }
		if($push == $btnSave) {
		  	# kontroly
			$vars = array("nazev","ulice","psc","misto");
			$valid = CheckData($ini,$vars);
			if($valid) {
				# Mame udaje - jdeme zapisovat do db
				$status = true;
//				echo "*** status - before: $status<BR>";
				if (empty($id_zs)) $id_zs = 0;;
				$cols = array(
                "nazev" => $nazev,
                "ulice" => $ulice,
                "psc" => $psc,
                "misto" => $misto,
                "email" => $email,
                "izo" => $izo);
                if (empty($id_zs)) {
                    dbi_insert_cols('zs', $cols);
                } else {
                    dbi_update('zs',$cols,"id_zs=$id_zs");
                }                    
				if (!$res) $status = false;
//				echo "*** status - after: $status";
				# *** OK -> pøesmìrování ***
				$path = GetPath($REQUEST_URI)."/zs_list.php";
//				Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
                Header(get_location($path));
			} else {
				# opakovat vstup dat
				if (!$valid) $info .= "<BR>Vypòte v¹echny potøebné údaje!";
			}
		}
	}
	if (!empty($id_zs)) {
		$res = dbi_select('zs',"id_zs=$id_zs");
		$r = $res->fetch_array();
		setvars($r);
	}
	$odeslano = true;
	$pg = sprintf("
<form>
%s
<table  border=\"1\" cellspacing=0 cellpadding=3 class=tblmain>	
<tr><td>Název ¹koly</td><td>%s</td></tr>
<tr><td>Ulice</td><td>%s</td></tr>
<tr><td>Místo</td><td>%s</td></tr>
<tr><td>PSÈ</td><td>%s</td></tr>
<tr><td>E-mail</td><td>%s</td></tr>
<tr><td>IZO</td><td>%s</td></tr>
<tr align=\"center\"><td colspan=2>%s&nbsp;%s</td></tr>
</table>
%s %s
</form>",$info, h_input('nazev'),h_input('ulice'),h_input('misto'),h_input('psc'),h_input('email'),h_input('izo'),h_btn("push",$btnSave),h_btn("push",$btnCancel),h_hidden('id_zs'),h_hidden('odeslano'));
	h_page($pg,$pg_name,"pzk.css");
?>

