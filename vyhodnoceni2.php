<?
	#--------------------------
	# vyhodnoceni PZK - zápis
	#--------------------------
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

   #$_prijeti = 1; #---- chyba opravit ve vyhodnoceni.php
    if (!isset($_prijeti)) $_prijeti="0";
    var_dump($_prijeti);
	# vymazani dosavadniho vyhodnoceni
	$cols = array(
		"poradi_od" => 0,
		"poradi_do" => 0
	);
	if($_prijeti) $cols["prijat"] = 0;
	dbi_update(uchazec(),$cols);
//	echo "vymazani vyhodnoceni...<br>";
	if ($push!="Vymazat") 
    {
//	echo "vyhodnoceni....<br>";
		$ucast = "(ucast=1 OR ucast=3)";
		
        # -- nastaveni prijeti pro ty bez PZK - 17.3.06 --
		if ($_prijeti) {
			$cols = array("prijat" => 1);
			$whr = "ucast = 4";
			dbi_update(uchazec(),$cols,$whr);
		}
        
        # -- nastavit splnil/nesplil zkoušku podle minima bodů - 28.3.2018  --
        $min_body_zk = readCfg("min_body_zk"); 
        $cols = array("splnil" => 0);
        dbi_update(uchazec(), $cols);
        $cols = array("splnil" => 1);
        dbi_update(uchazec(), $cols, "m>=$min_body_zk and cj>=$min_body_zk");

		# -- cyklus pres jednotlive typy studia --
		$res1 = dbi_select("studium","id_studium != 0","id_studium","id_studium");
		while (list($id_studium) = $res1->fetch_array())
        {
            # -- pocet bez PZK pro dane studium - 17.3.06
			$whr = "id_studium=$id_studium AND prijat=1 AND ucast=4";
			$cols = "count(*) as pocet";
			$bezpzk = dbi_select(uchazec(),$whr,$cols);
			list($pocet_bez_PZK) = $bezpzk->fetch_array();

//            $whr   = "id_studium=$id_studium AND $ucast AND splnil=1";
            # 16.6.2020 -- přidána další kritéria
            $whr   = "id_studium=$id_studium AND $ucast AND splnil=1 AND uchazec0.id=vysledek.id";

			$ordby = "celkem DESC, m+cj+osp DESC, m DESC, kriterium DESC";
//			$grpby = "celkem,zps";  //oprava 12.2.2003 - nehodnotit s ohledem na ZPS
//			$grpby = "celkem";      // oprava 13.3.08 - uprednostnit M+Cj
			$grpby = "celkem, m+cj+osp, m, kriterium";
			$cols  = "celkem, m+cj+osp as pisemka, count(*) as pocet, m, kriterium";
			$res2  = dbi_select(uchazec().", vysledek", $whr, $cols, $ordby, $grpby);
			$poradi = $pocet_bez_PZK + 1; // pricist pocet prijatych bez PZK
	
            # -- cyklus pres skupiny podle celkovych bodu --
			while (list($celkem, $pisemka , $pocet, $m, $kriterium) = $res2->fetch_array()) 
            {
                #printf("pocet ve skupine: --%s-- --%s--  <br>", $pocet, $kriterium);
				$poradi_do = $poradi+$pocet-1;
				$prijat = setPrijeti($id_studium,$celkem,$poradi_do,$pocet_bez_PZK);
//				$whr = "id_studium=$id_studium AND $ucast AND celkem=$celkem AND zps=$zps"; oprava 12.4.2003 - nehodnotit s ohledem na zps
				$whr = "uchazec0.id=vysledek.id AND id_studium=$id_studium AND $ucast AND celkem=$celkem AND m+cj+osp=$pisemka AND m=$m AND kriterium='$kriterium'";
				$cols = array(
					"poradi_od" => $poradi,
					"poradi_do" => $poradi_do,
				);
   			    if ($_prijeti)	{ $cols["prijat"] = $prijat; }
                $r3 = dbi_select(uchazec().", vysledek", $whr, "uchazec0.id", $ordby);
                #printf("sql-whr: %s<br>", $whr); 
                while (list($id) = $r3->fetch_array())
                {
				    dbi_update(uchazec(), $cols, "id=$id");
                    #printf("id: %s, celkem: %s, kriterium: %s, poradi (%s) : %s - %s<br>", $id, $celkem, $kriterium, $pocet, $poradi, $poradi_do);
                }                    
				$poradi += $pocet;
			}

            # -- nastavení poradi=999 pro splnil=0 --
   			$cols = array(
					"poradi_od" => 999,
					"poradi_do" => 999,
			);
            dbi_update(uchazec(), $cols, "id_studium=$id_studium AND $ucast AND splnil=0");
		}
	}
    //exit;
	$path = GetPath($REQUEST_URI)."/vyhodnoceni.php?atyp=$atyp&_prijeti=$_prijeti";
//	Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
    Header(get_location($path));
?>
