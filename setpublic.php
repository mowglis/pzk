<?
	#********************************************************
	# nastaveni pristupnosti/nepristupnosti 'public' stranek
	# Gybon, PZK
	#********************************************************
    require "libpzk/common.inc.php";
    require "libpzk/sql.inc.php";
    extract($_REQUEST);

   if(!isset($kolo) || $kolo == "") $kolo = "1";
   $public = readCfg("public$kolo"."_$atyp");
   if($public) {
      writeCfg("public$kolo"."_$atyp","0");
   } else {
      writeCfg("public$kolo"."_$atyp","1");
   }
	$path = GetPath($REQUEST_URI)."/index.php?atyp=$atyp";
//	Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
    Header(get_location($path));
?>
