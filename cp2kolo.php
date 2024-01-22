<?
	#*****************************************
	# prekopirovani zaznamu z 1.kola na 2.kolo
	# Gybon, PZK
	#*****************************************
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

	$whr = "prijat=0 AND id_ss=1"; // neprijati && 2. kolo na Gybon
	$res = dbSelect("uchazec0",$whr);
	while($polozka = mysql_fetch_row($res)) {
		$cols ="";
		while(list($index,$value) = each($polozka)) {
			# polozky 18-24 vynulovat
			if($index >= 18 && $index <= 24) $value = 0;
			# polozku 26 (ucast) -> 1
			if($index == 26) $value = 1;
			# polozku 29 (odvolani) -> 0
			if($index == 29) $value = 0;
			$type = mysql_field_type($res,$index);
//			echo "type --> $type<br>";
			if($type == "int" || $type == "real"){
				$cols .= "$value,";
			} else {
				$cols .= "'$value',";
			}
		}
//		echo $cols."<br>";
		dbInsert("uchazec1", substr($cols,0,-1));
	}
	$path = GetPath($REQUEST_URI)."/setkolo.php?atyp=1";
//	Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
    Header(get_location($path));
?>
