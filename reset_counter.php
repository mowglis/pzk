<?
	#********************************************************
	# recet pocitadla stranek
	# Gybon, PZK
	#********************************************************
   require "libpzk/common.inc.php";
   require "libpzk/sql.inc.php";
   extract($_REQUEST);

   if (!isAdminUser()) {
		$pg = "Nejste autorizov�n pro vstup na tuto str�nku!";
		h_page($pg,"Nastaven� konfigurace","pzk.css");
		exit;
   }
   writeCfg("counter","0");
   $path = GetPath($REQUEST_URI)."/index.php?atyp=$atyp";
//   Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
   Header(get_location($path));
?>
