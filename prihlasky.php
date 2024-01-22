<?
#------------------------------
# seznam skenovaných pøihlá¹ek
#------------------------------
require "libpzk/common.inc.php";
require "libpzk/sql.inc.php";

function mk_img($img) 
{
  return sprintf("<img src=\"%s\">", "imgs/".$img);
}

function a_scan_file($file) 
{
   $qr_files_dir = "QR/scan_files/"; 
   return sprintf("<a href=\"%s\">%s</a>", $qr_files_dir.$file, $file);
}   

extract($_REQUEST);

$pg_name = "Skenované pøihlá¹ky";
	/*
    if (!isAdminUser()) {
		$pg = "Nejste autorizován pro vstup na tuto stránku!";
		h_page($pg,$pg_name,"pzk.css");
		exit;
	}
    */
    $nezpracovane=0;
    if (array_key_exists('zobraz', $_GET) && $_GET['zobraz'] == 'vyber') {
        if (array_key_exists('nezpracovane', $_GET)) $nezpracovane = $_GET['nezpracovane'];
    } else {
        $nezpracovane = 1;
    }
    $whr = "";
    if ($nezpracovane) {
        $whr = "zpracovano=0";
    }
	$res = dbi_select("prihlaska", $whr);
    $zobrazit = h_chbx('nezpracovane');
    $tl = h_btn('zobraz', 'vyber'); 
    $pg = sprintf("
<form>
%s Pouze nezpracované &nbsp;&nbsp;&nbsp;%s
</form>", $zobrazit, $tl);

if ($res->num_rows<1) 
{
    $pg .= "<h3>V¹echny pøihlá¹ky jsou zpracované</h3>";
	h_page($pg,$pg_name,"pzk.css");
    exit;
}

$img_done = sprintf("<img src=\"%s\">", "imgs/done.png");
$img_done = sprintf("<img src=\"%s\">", "imgs/done.png");
$img_prihlas = sprintf("<img src=\"%s\">", "imgs/prihlas.png");
setlocale(LC_TIME, "Czech");
$date_fmt = "j.n.Y H:i:s";

$pg .= sprintf(
"
<table class=\"prihlaska\">
<tr>
<th colspan=\"2\">Pøíjmení a jméno<br>Z©</th>
<th>Rodné è.</th>
<th>Datum<br>skenování</th>
<th>Datum<br>zpracování</th>
</tr>");
	while ($r = $res->fetch_assoc()) 
    {
        if ($r['zpracovano'] == 0) {
            $operace = sprintf("<a href=\"zpracuj_prihlasku.php?id=%s\" title=\"zpracovat pøihlá¹ku\">%s</a>", $r['id'], mk_img('prihlas.png'));
        } else {
            $operace = mk_img('done.png');
        }            
		$pg .= sprintf(
"<tr>
<td>%s</td>
<td><b>%s %s</b><br>%s</td>
<td>%s</td>
<td><div class=\"tooltip\">%s<span class=\"tooltiptext\">%s</td>
<td>%s</td>
</tr>", $operace, $r["prijmeni"], $r["jmeno"], get_zs($r['izo_zs']), $r['rc'], date($date_fmt,strtotime($r['scan_date'])), a_scan_file($r['image_file']), $r['accept_date']);
	}
	$pg .= sprintf("
</table>");

	h_page($pg,$pg_name,"pzk.css");
?>

