<?
    require "libpzk/common.inc.php";
    require "libpzk/sql.inc.php";
    extract($_REQUEST);

   # MySQL
   $res = dbSelect("uchazec0","","*","prijmeni,jmeno");
   $pg = sprintf(
"<table border=1>
<tr>
<td>id</td><td>jméno</td><td>prùmìry</td><td>prùmìr (db)</td><td>prùmìr</td><td>prùmìr<br>(new)</td><td>body</td><td>bonifikace</td><td>celkem</td><td>celkem<br>(new)</td><td>info</td>
</tr>");
   while($r = mysql_fetch_array($res)){
		setVars($r);
      $prnewcelk = ($p1+$p2)/2;
      $prnew = myRound($prnewcelk,2);
      if($r['celkem'] != doCalc($id_studium)) {
         $info = "W";
			$prumer = $prnew;
      } else {
         $info = "&nbsp;";
      }
      $pg .= sprintf(
"<tr>
<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>
</tr>",$r["id"],$r["prijmeni"]." ".$r["jmeno"],$r["p1"].", ".$r["p2"],$r['prumer'],$prnewcelk,$prnew,$body,$r['bonifikace'],$r['celkem'],$celkem,$info);
		if($info == "W") {
			# update db
			$cols = array(
#				"prumer"=>$prumer,
				"body"=>$body,
				"vstup"=>$vstup,
				"celkem"=>$celkem);
#...... pro zapis nutno odkomentovat ........#				
#			dbUpdate("uchazec0",$cols,"id=$id");
		}
   }
   $pg .= "</table>";
   h_page($pg,"test - prumer","pzk.css"); 
?>
