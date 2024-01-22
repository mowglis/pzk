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
	$pristup = array('uzavøeny','zpøístupnìny');
	$studium = array(0 => 'nic',1 => 'ètyøleté',3 => '¹estileté',5 => '¹estileté (tercie)');
    $dev_text = array(0 => '', 1 => 'VÝYVOJOVÁ VERZE PROGRAMU');

    printf ("<h3 align=center>Pøijímací zkou¹ky - <a href=\"%s\" title=\"%s\" border=0>%s. kolo</a> - veøejné stránky pro %s studium jsou <a href=\"%s\" title=\"%s\">%s</a> %s</h3>","setkolo.php?atyp=$atyp","Nastavení kola PZK",$kolo,$studium[$atyp],"setpublic.php?kolo=$kolo&atyp=$atyp",'Nastavení pøístupu na veøejné stránky',$pristup[readCfg("public$kolo"."_$atyp")], $dev_text[$db_devel]);
?>
