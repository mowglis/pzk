<?
	#*******************************
	# prehozeni prijat/neprijat
	# PZK Gybon
	#*******************************
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

	$ucast = "(ucast=1 OR ucast=3)";
	if(!isset($atyp) || !isset($prijat) || !isset($id)) exit;
	if($prijat == "1") $prijat=0;
		else $prijat=1;
	$cols = array(	"prijat" => $prijat);
	dbUpdate(uchazec(),$cols,"id=$id");
	$path = GetPath($REQUEST_URI)."/vyhodnoceni.php?atyp=$atyp";
//	Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
    Header(get_location($path));
?>
