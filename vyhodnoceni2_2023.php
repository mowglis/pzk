<?
	#--------------------------
	# vyhodnoceni PZK - zápis
	#--------------------------
    # v roce 2023
    # - přidáno hodnocení cizinců: cizinci jsou do celkového pořadí zařazení s pořadím jaké získají při hodnocení PZK bez ČJ
    # - redukované pořadí - pořadí s vynecháním zkoušky z Čj
    #

	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

    function poradi_redukovane()
    {
        # --- redukované pořadí  - vynechá se Čj ---
        # - vyplní položky:
        #           poradi_od_cizinec 
        #           poradi_do_cizinec
        
        global $id_studium, $ucast;
        # -- kritéria pro redukované pořadí --
        $whr   = "id_studium=$id_studium AND $ucast AND splnil=1 AND uchazec0.id=vysledek.id";
    	$ordby = "celkem-cj DESC, m DESC, kriterium DESC";
		$grpby = "celkem-cj, m, kriterium";
		$cols  = "celkem-cj as celkem, count(*) as pocet, m, kriterium";
		$res2  = dbi_select(uchazec().", vysledek", $whr, $cols, $ordby, $grpby);
		$poradi = 1; 
	    
		while (list($celkem, $pocet, $m, $kriterium) = $res2->fetch_array()) 
        {
            #printf("pocet ve skupine: --%s-- --%s--  <br>", $pocet, $kriterium);
			$poradi_do = $poradi + $pocet - 1;
			$whr = "uchazec0.id=vysledek.id AND id_studium=$id_studium AND $ucast AND m=$m AND kriterium='$kriterium'";
			
            $cols = array(
				"poradi_od_cizinec" => $poradi,
				"poradi_do_cizinec" => $poradi_do,
			);
            $r3 = dbi_select(uchazec().", vysledek", $whr, "uchazec0.id", $ordby);
            #printf("sql-whr: %s<br>", $whr); 
                
            while (list($id) = $r3->fetch_array())
            {
				dbi_update(uchazec(), $cols, "id=$id");
                #printf("id: %s, celkem: %s, kriterium: %s, poradi (%s) : %s - %s<br>", $id, $celkem, $kriterium, $pocet, $poradi, $poradi_do);
            }                    
			$poradi += $pocet;
		}
        # vrátí seznam cizinců: id, poradi_od_cizinec (redukované pořadí)
        $cizinci = array();
        $whr = "id_studium=$id_studium AND $ucast AND splnil=1 AND cizinec=1";
        $cols = "id, poradi_od_cizinec, poradi_do_cizinec";
        $res = dbi_select(uchazec(), $whr, $cols);
        while (list($id, $poradi, $poradi_do) = $res->fetch_array()){
            $cizinci[$poradi] = $id;
        }
        return $cizinci;
    }

    function kontrola_poradi_cizince($poradi, $cizinci)
    {
        global $id_studium, $_prijeti; 
        # !! zjistit zda $poradi koresponduje s pořadím nějakého cizince 
        # - pokud nějaký cizinec má redukované pořadí ($poradi_od_cizinec) shodné s $poradi
        # bude na $poradi cizinec
        # - pokud na $poradi nepatří cizinec, tak se $poradi nezmění

        while (array_key_exists($poradi, $cizinci)) 
        {
            # na $poradi přijde cizinec s $id
            $cols = array(
			    "poradi_od" => $poradi,
			    "poradi_do" => $poradi,
		    );
            $prijat = setPrijeti($id_studium, 0, $poradi);
            if ($_prijeti) $cols["prijat"] = $prijat;
            $id = $cizinci[$poradi];
	        dbi_update(uchazec(), $cols, "id=$id");
            $poradi += 1;
        }
        return $poradi;
    }

    # ---- začátek vyhodnocení ---
    if (!isset($_prijeti)) $_prijeti="0";
	
    # -- vymazani vyhodnoceni --
	$cols = array(
		"poradi_od" => 0,
		"poradi_do" => 0,
        "poradi_od_cizinec" => 0,
        "poradi_do_cizinec" => 0
	);
	
    # vymazání přijetí
    if ($_prijeti) $cols["prijat"] = 0;
	dbi_update(uchazec(),$cols);
	
    if ($push!="Vymazat") 
    {
		$ucast = "(ucast=1 OR ucast=3)";
		
        # -- nastaveni prijeti pro ty bez PZK - 17.3.06 -- asi se už nepoužívá
		if ($_prijeti) 
        {
			$cols = array("prijat" => 1);
			$whr = "ucast = 4";
			dbi_update(uchazec(),$cols,$whr);
		}
        
        # -- nastavit --splnil/nesplnil--  zkoušku podle minima bodů - 28.3.2018  --
        $min_body_zk = readCfg("min_body_zk"); 
        $cols = array("splnil" => 0);
        dbi_update(uchazec(), $cols);
        $cols = array("splnil" => 1);
        dbi_update(uchazec(), $cols, "m>=$min_body_zk and cj>=$min_body_zk");
        # cizinec - vynechat kontrolu na Čj
        dbi_update(uchazec(), $cols, "m>=$min_body_zk and cizinec=1");

		# -- cyklus pres jednotlive typy studia --
		$res1 = dbi_select("studium","id_studium != 0","id_studium","id_studium");
		
        while (list($id_studium) = $res1->fetch_array())
        {
            $pocet_bez_PZK = 0;
            
            # redukované pořadí - přidáno v roce 2023 - vyhodnocení s vynecháním Čj
            $cizinci = poradi_redukovane();

            # -- kritéria pro vyhodnocení pořadí - pořadí bez cizinců --
            $whr   = "id_studium=$id_studium AND $ucast AND splnil=1 AND uchazec0.id=vysledek.id AND cizinec=0"; # bez cizinců
			$ordby = "celkem DESC, m+cj+osp DESC, m DESC, kriterium DESC";
			$grpby = "celkem, m+cj+osp, m, kriterium";
			$cols  = "celkem, m+cj+osp as pisemka, count(*) as pocet, m, kriterium";
			$res2  = dbi_select(uchazec().", vysledek", $whr, $cols, $ordby, $grpby);
			//$poradi = $pocet_bez_PZK + 1; // pricist pocet prijatych bez PZK -- zrušeno
			$poradi = 1;
	
            # -- cyklus pres skupiny podle celkovych bodu --
			while (list($celkem, $pisemka , $pocet, $m, $kriterium) = $res2->fetch_array()) 
            {
                #print($celkem);
                $poradi = kontrola_poradi_cizince($poradi, $cizinci); # kontrola, zda na $poradi nepatří cizinec podle redukovaného pořadí

				$poradi_do = $poradi + $pocet - 1;
                #printf("poradi: %s-%s -- pocet ve skupine: --%s-- --%s--  <br>", $poradi, $poradi_do, $pocet, $kriterium);
				$prijat = setPrijeti($id_studium, $celkem, $poradi_do);
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
                    #printf("\nid: %s, celkem: %s, kriterium: %s, poradi (%s) : %s-%s<br>", $id, $celkem, $kriterium, $pocet, $poradi, $poradi_do);
				 
                    dbi_update(uchazec(), $cols, "id=$id");
                }                    
				$poradi += $pocet;
			}

            # -- nastavení poradi=999 pro splnil=0 --
   			$cols = array(
					"poradi_od" => 999,
					"poradi_do" => 999,
					"poradi_od_cizinec" => 999,
					"poradi_do_cizinec" => 999,
			);
            dbi_update(uchazec(), $cols, "id_studium=$id_studium AND $ucast AND splnil=0");
		}
	}
    //exit;
	$path = GetPath($REQUEST_URI)."/vyhodnoceni.php?atyp=$atyp&_prijeti=$_prijeti";
//	Header("Location: http://$SERVER_NAME:$SERVER_PORT$path");
    Header(get_location($path));
?>
