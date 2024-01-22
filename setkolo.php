<?
	#*****************************
	# nastaveni kola pzk
	# Gybon, PZK
	#****************************
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

	$kolo = readCfg("kolo");
	if($kolo > 1) {
		writeCfg("kolo","1");
	} else {
		writeCfg("kolo","2");
	}
	$path = GetPath($REQUEST_URI)."/index.php?atyp=$atyp";
//	Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
    Header(get_location($path));
?>
