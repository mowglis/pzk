<?
   #---------------------------------
   #  pøidání uchazeèe z pøihlá¹ky  
   #---------------------------------
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);
	
	if (empty($_GET['id']))  exit;

    $idp = $_GET['id'];
    $r = dbi_select('prihlaska', "id=$idp")->fetch_assoc();
   
    $id = freeID();

    # prihlaska => uchazec
    $cols = array(
        "zast_datnar" =>  "",
        "datovka" => "",
	    "zps" => 0,
	    "id_zs" => 0,
	    "id_ss" => 0,
	    "ulice" => "",
	    "misto" => "Hradec Králové",
	    "psc" => "",
	    "bonifikace" => "0",
        "prumer" => "0",
	    "body" => "0",
	    "vstup" => "0",
	    "cj" => "0",
	    "m" => "0",
	    "celkem" => "0",
	    "prijat" => "0",
	    "ucast" => "1",
	    "odvolani" => "0",
	    "cj0" => "---nevyplòovat---",
	    "aj" => "0",
	    "pz" => "0",
	    "oz" => "0",
	    "termin" => 'datum_pzk_1',
	    "aid" => '0',
	    "listek" => 0,
        "splnil" => 0,
        "poslat_pozvanku" => 1,
        "ulice_cp" => "",
        "bydliste_kraj" => "---nevyplòovat---",
        "cermat_export" => 0,
        "ulice_doruc" => "",
        "ulice_cp_doruc" => "",
        "mesto_doruc" => "",
        "psc_doruc" => "",
        "zpusob_doruceni" => 0,
        "pocet_priloh" => 0,
        "id_ucebna" => 0,
        "zast_datnar" => "",
        "id_studium" => 3,
        "poradi_od" => 0,
        "poradi_do" => 0,
        "prevzal" => 0,
        "poznamka" => "",
        "doruceno" => (new DateTime())->format("Y-m-d"),
        "datnar" => DMR2RMD(rc2datnar($r["rc"])),
        "id" => $id,
        "jmeno" => $r['jmeno'], 
        "prijmeni" => $r['prijmeni'], 
        "pohlavi" => $r['pohlavi'], 
        "p1" => $r['p1'], 
        "p2" => $r['p2'], 
        "p3" => $r['p3'], 
        "zast_jmeno" => $r['zast_jmeno'],
        "zast_prijmeni" => $r['zast_prijmeni'], 
        "zast_pohlavi" => $r['zast_pohlavi'],
        "izo_zs" => $r['izo_zs'],
        "misto_nar" => $r['misto_nar'],
        "poradi_zajmu" => $r['poradi_zajmu'],
        "e_mail0" => $r['e_mail0'],
        "e_mail1" => $r['e_mail1'],
        "rc" => $r['rc'], 
        "cizinec" => $r['cizinec'] 
    );
    if ($r['poradi_zajmu'] == "2") {
        $cols['termin'] = 'datum_pzk_2';
    }        
                
    $res = dbi_insert_cols(uchazec(), $cols);
    if ($res) { 
        
        # zapsat zpracovaní pøihlá¹ky
        $zpracovano = (new DateTime())->format("Y-m-d H:i:s");
        $cols = array('accept_date' => "$zpracovano", 'zpracovano' => 1);
        dbi_update('prihlaska', $cols, "id=$idp"); 
        #var_dump($cols);
        $path = GetPath($REQUEST_URI)."/edit.php?atyp=3&id=$id&ret=prihlas";
        Header(get_location($path));
    } else {        
        $status = false;
    }        
?>
