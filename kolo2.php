<?
	#*****************************
	# nastaveni 2.kola
	# Gybon, PZK
	#****************************
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

	if(readCfg("kolo") < 2) {
		$pg .= sprintf(
"<a href=\"%s\">Naplnit datab�zi pro 2. kolo</a>","cp2kolo.php");
	}
	h_page($pg,"P�ij�mac� zkou�ky - Gybon","pzk.css");
?>
