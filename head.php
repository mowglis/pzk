<?
	#***************************
	# zahlavi uvodni stranky
	# Gybon, PZK
	#***************************
    #require "libpzk/common.inc.php"; 
    extract($_REQUEST);
	$kolo = readCfg("kolo");
	if (array_key_exists('atyp', $GLOBALS)) {
        $atyp = $GLOBALS["atyp"];
    } else {        
	    $atyp = 3;
    }        
	$pristup = array('uzav�eny','zp��stupn�ny');
	$studium = array(0 => 'nic',1 => '�ty�let�',3 => '�estilet�',5 => '�estilet� (tercie)');
    $dev_text = array(0 => '', 1 => 'V�YVOJOV� VERZE PROGRAMU');

    printf ("<h3 align=center>P�ij�mac� zkou�ky - <a href=\"%s\" title=\"%s\" border=0>%s. kolo</a> - ve�ejn� str�nky pro %s studium jsou <a href=\"%s\" title=\"%s\">%s</a> %s</h3>","setkolo.php?atyp=$atyp","Nastaven� kola PZK",$kolo,$studium[$atyp],"setpublic.php?kolo=$kolo&atyp=$atyp",'Nastaven� p��stupu na ve�ejn� str�nky',$pristup[readCfg("public$kolo"."_$atyp")], $dev_text[$db_devel]);
?>
