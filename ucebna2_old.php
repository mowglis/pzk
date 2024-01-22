<?
	#--------------------------------------
    # rozmisteni studentu do uceben - zápis
	#--------------------------------------
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

#	$typ_studia = "1,3"; # 4-lete a 6-lete
	$typ_studia = "3"; # pouze 6-lete
	# vymazani dosavadniho rozmisteni
	$cols = array(
		"id_ucebna" => 0
	);
	dbi_update(uchazec(),$cols);
	# -- vymazani vyuziti v tabulce ucebna
	$cols = array(
		"vyuziti_1" => 0,
		"vyuziti_2" => 0,
		"id_studium_1" => 0,
		"id_studium_2" => 0
	);
	dbi_update("ucebna",$cols);
//	echo "vymazani rozmisteni...<br>";
	# -- cyklus pres terminy
 for ($id_termin=1; $id_termin<=2; $id_termin++) {
	$res1 = dbi_select("studium","id_studium in ($typ_studia)","id_studium","id_studium");
	$last_id_skupina=0;
	# -- cyklus pres jednotlive typy studia
	while (list($id_studium) = $res1->fetch_array())
    {
//		echo "studium: $id_studium<br>";
    foreach (array (2,1,0) as $zps )
    {   # -- ZPS studenti se zarazuji do samostatnych uceben  
		$offset = 0;
        
        # klasické třídění
	    $res2 = dbi_select("ucebna","skupina>$last_id_skupina","id_ucebna,kapacita,skupina",'skupina');
        # lexiografické třídění podle skupiny
        //$res2 = dbSelect_debug("ucebna","CONVERT(skupina,CHAR(3))>'$last_id_skupina'","id_ucebna,kapacita,skupina",'CONVERT(skupina,CHAR(3))');
        /*
        if ($zps == 1) {
            $zps_cond = "zps > 0";
        } else {
            $zps_cond = "zps = 0";
        } */           
		$whr = "id_studium=$id_studium AND ucast=1 AND termin='datum_pzk_$id_termin' AND zps=$zps AND poslat_pozvanku=1";
		$res_pocet = dbi_select(uchazec(),$whr,'COUNT(*)');
		list($pocet) = $res_pocet->fetch_array();
        if ($pocet < 1) continue;
		
        # -- cyklus pres jednotlive  ucebny
		while (list($id_ucebna,$kapacita,$skupina) = $res2->fetch_array()) 
        {
//			echo "ucebna, kapacita: $id_ucebna,$kapacita<br>";
			$res3 = dbi_select(uchazec(),$whr,"id","prijmeni,jmeno LIMIT $offset,$kapacita");
            # - třídění podle id - abecedně
            //$res3 = dbSelect(uchazec(),$whr,"id","CONVERT(id,CHAR(3)) LIMIT $offset,$kapacita");
			### cyklus pres studenty zarazovane do tridy
			while (list($id) = $res3->fetch_array())
            {
				$cols = array("id_ucebna" => $id_ucebna);
				dbi_update(uchazec(),$cols,"id=$id");
			}
			### zapsat typ studia do ucebny
			$cols = array(
				"id_studium_$id_termin" => $id_studium
			);
			if ($kapacita > 0) dbi_update(ucebna,$cols,"id_ucebna=$id_ucebna");
			$offset += $kapacita;
	 		$last_id_skupina = $skupina;
			if ($res3->num_rows < $kapacita || $offset >= $pocet) break;
		}
	  }	
	}
	# --- naplneni vyuziti jednotlivych trid
	$res = dbi_select(uchazec(),"termin='datum_pzk_$id_termin'","id_ucebna,count(*) AS pocet","","id_ucebna");
	while (list($id_ucebna,$pocet) = $res->fetch_array()) 
    {
		$cols = array("vyuziti_$id_termin" => $pocet);
		dbi_update("ucebna",$cols,"id_ucebna=$id_ucebna");
	}
 }
	$path = GetPath($REQUEST_URI)."/ucebna.php";
    Header(get_location($path));
?>
