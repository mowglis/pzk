<?
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);
	
    if(!isset($id) || $id=="") exit();
	dbDelete(uchazec(),"id=$id");
	$path = GetPath($REQUEST_URI)."/index.php?atyp=$atyp";
//	Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
    Header(get_location($path));
?>
