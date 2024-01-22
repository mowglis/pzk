<?
   #---------------------------------
   #  pøidání uchazeèe z pøihlá¹ky  
   #---------------------------------
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);
	
	if (empty($_GET['id']))  exit;

    $idp = $_GET['id'];
#    $r = mysql_fetch_assoc(dbSelect('prihlaska', "id=$idp"));
    $r = dbi_select('prihlaska', "id=$idp")->fetch_assoc();
    #var_dump($r);
   
    $id = freeID();

    # prihlaska => uchazec
    $ini = array(
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
	    "jazyk" => "0",
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
        "poznamka" => ""

    );
    $ini["doruceno"] = (new DateTime())->format("Y-m-d");

    #doCalc($id_studium);
    $datnar = rc2datnar($r["rc"]);    
    #var_dump($datnar);
    $datnar = DMR2RMD($datnar);

    $cols = sprintf(
    "%s, '%s', '%s', %s, '%s', %s, %s , %s, '%s', '%s', 
    '%s', %s, %s, %s, %s, %s, %s , %s, %s, %s,  
    %s, %s, %s, %s, %s, %s, %s , %s, '%s', %s,  
    '%s', '%s', %s, %s, '%s', %s, %s , %s, '%s', %s,  
    %s, '%s', '%s', %s, '%s', '%s', %s , '%s', '%s', %s,  
    '%s', '%s', '%s', '%s', '%s', %s, '%s' , %s, %s, '%s',
    '%s', %s",
    $id, $r['jmeno'], $r['prijmeni'], $r['pohlavi'], $datnar, $ini['zps'], $ini['id_zs'], $ini['id_ss'], $ini['ulice'], $ini['misto'], $ini['psc'], $ini['id_studium'], $r['p1'], $r['p2'], $r['p3'], $ini['prumer'], $ini['body'], $ini['vstup'],$ini['cj'], $ini['m'], $ini['jazyk'], $ini['celkem'], $ini['poradi_od'], $ini['poradi_do'], $ini['prijat'], $ini['bonifikace'], $ini['ucast'], $ini['id_ucebna'], $ini['poznamka'], $ini['odvolani'], $r['zast_jmeno'],$r['zast_prijmeni'], $r['zast_pohlavi'] ,$ini['prevzal'], $ini['cj0'], $ini['aj'], $ini['pz'], $ini['oz'], $ini['termin'], $ini['aid'], $ini['listek'], $r['izo_zs'], $r['misto_nar'], $r['poradi_zajmu'], $r['e_mail0'], $r['e_mail1'], $ini['poslat_pozvanku'], $ini['ulice_cp'], $ini['bydliste_kraj'], $ini['cermat_export'], $ini['ulice_doruc'], $ini['ulice_cp_doruc'], $ini['mesto_doruc'], $ini['psc_doruc'], $r['rc'], $r['cizinec'], $ini['doruceno'], $ini['zpusob_doruceni'], $ini['pocet_priloh'], $ini['zast_datnar'], $ini['datovka'] ,$ini['splnil']
    );
                
    $res = dbi_insert(uchazec(), $cols);
    if ($res) { 
        # zapsat zpracovaní pøihlá¹ky
        $zpracovano = (new DateTime())->format("Y-m-d H:i:s");
        $cols = array('accept_date' => "$zpracovano", 'zpracovano' => 1);
        dbi_update('prihlaska', $cols, "id=$idp"); 
        #var_dump($cols);
        $path = GetPath($REQUEST_URI)."/edit.php?atyp=$atyp&id=$id&ret=prihlas";
        Header(get_location($path));
    } else {        
        $status = false;
    }        
?>
