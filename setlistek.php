<?
	#*******************************
	# prehozeni zapisoveho listku ano/ne
	# PZK Gybon
	#*******************************
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

	$ucast = "(ucast=1 OR ucast=3)";
	if(!isset($atyp) || !isset($listek) || !isset($id)) exit;
	if($listek == "1") $listek=0;
		else $listek=1;
	$cols = array(	"listek" => $listek);
	dbUpdate(uchazec(),$cols,"id=$id");
	$path = GetPath($REQUEST_URI)."/vyhodnoceni.php?atyp=$atyp";
//	Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
    Header(get_location($path));
?>
