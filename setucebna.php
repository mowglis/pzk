<?
	#####################################
	# editace ucebny
	#####################################
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

	$title = "Editace parametrù uèebny";
    //is_admin();

	function new_id_ucebna() {
		$r = dbi_select('ucebna','','id_ucebna','id_ucebna DESC LIMIT 1');
		list($last_id) = $r->fetch_array();
		$last_id+=1;
		return $last_id;
	}

	function new_skupina() {
		$r = dbi_select('ucebna','','skupina','skupina DESC LIMIT 1');
		list($last_skupina) = $r->fetch_array();
		$last_skupina+=1;
		return $last_skupina;
	}

	# kontrola
    $info = '';
    //print_r($id_ucebna);
	if (!isset($id_ucebna) || $id_ucebna=="") exit;
	# *** Texty tlacitek *** 
	$btnSave   = "Ulo¾it";
	$btnCancel = "Cancel";
	if (isset($odeslano) && $odeslano) 
    {
		if ($push == $btnCancel) 
        {
			// navrat bez ulozeni dat 	
			$path = GetPath($SCRIPT_NAME)."/ucebna.php";
//			Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
            Header(get_location($path));
        }
		if ($push == $btnSave || $push == $btnSaveCont) 
        {
		  	# kontroly
			$valid = true;
			if ($valid) 
            {
				$status = true;
				$cols = array (
					"skupina" => $skupina,
					"ucebna" => $ucebna,
					"popis" => $popis,
					"kapacita" => $kapacita
				);
				$vars = array("kapacita");
				validateNumData($vars);
				if ($id_ucebna == 9999) {
					# -- vlozeni noveho zaznamu
					$cols['id_ucebna'] = new_id_ucebna();
					$res = dbi_insert_cols('ucebna',$cols);
				} else {
					# -- update zaznamu
					$res = dbi_update("ucebna",$cols,"id_ucebna=$id_ucebna");
                }
				if (!$res) $status = false;
				# *** OK -> pøesmìrování ***
				$path = GetPath($REQUEST_URI)."/ucebna.php";
//				Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
                Header(get_location($path));
			} else {
				# -- opakovat vstup dat
				if (!$valid) $info .= "<BR>Vypòte v¹echny potøebné údaje!";
			}
		}
	} else {
		# *** prvni pruchod - neodeslano ***
        //print_r($id_ucebna);
		if ($id_ucebna == 9999 ) {
			## nová ucebna
			$skupina = new_skupina();
			$iniVars = array(	
				'skupina' => $skupina,
				'ucebna' => 'XX',
				'popis' => 'Popis uèebny',
				'kapacita' => 0
			);
			setVars($iniVars);
		} else {
			$res = dbi_select("ucebna","id_ucebna=$id_ucebna","skupina,ucebna,popis,kapacita");
			if ($res->num_rows < 1) 
            {
				$path = GetPath($REQUEST_URI)."/ucebna.php";
//				Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
                Header(get_location($path));
			}
			$r = $res->fetch_array();
			setVars($r);
		}
	}
	### tvorba vystupni stranky
	$pg = sprintf("
<FORM  METHOD=POST NAME=setdata onSubmit=\"return validate(this)\">
<DIV ALIGN=CENTER>");
	# dodatky formulare - HIDDEN
	$pg .= h_hidden("odeslano","true");
	$pg .= h_hidden("id_ucebna",$id_ucebna);
	$pg .= sprintf("
<table cellspacing=1 class=tblborder border=0>
<tr><td>
<table border=0 cellspacing=0 cellpadding=5 class=tblmain>
	");
	$pg .= sprintf("<tr><td colspan=2 align=center class=rowcls1>%s<br>%s</td></tr>",
		"Editace údajù uèebny",$info);
	$pg .= sprintf(
"<tr><td class=\"polozka\">Skupina</td><td>%s</td></tr>
<tr><td class=\"polozka\">Uèebna</td><td>%s</td></tr>
<tr><td class=\"polozka\">Popis</td><td>%s</td></tr>
<tr><td class=\"polozka\">Kapacita</td><td>%s</td></tr>",
h_input("skupina","3","text","onBlur=\"isValidNum(this.form.skupina)\""),h_input("ucebna","3","text"),h_input("popis","40","text"),h_input("kapacita","3","text","onBlur=\"isValidNum(this.form.skupina)\""));
	# *** tlacitka ***
	$pg .= "<!-- ### zacatek tlacitek ### -->\n";
	$pg .= sprintf("<tr><td class=rowcls1 colspan=2 align=center>%s&nbsp;&nbsp;%s</td></tr>",
	h_btn("push",$btnSave),h_btn("push",$btnCancel,"onClick=\"self.history.back()\""));
	$pg .= sprintf(
"</table>
</form></td></tr></table>");
	h_page($pg,$title,"pzk.css","setdata.skupina","libpzk/edit2.ins.js");
?>	
