<?
	#------------------------------
	# odes�l�n� datov�ch zpr�v
	#------------------------------
	require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    require_once 'Michelf/Markdown.inc.php';

use Michelf\Markdown;
$upload_dir = readCfg('root_dir').readCfg('upload_dir');

function mk_img($img) 
{
  return sprintf("<img src=\"%s\">", "imgs/".$img);
}

function h_chbx_array($name, $value, $class)
{
    $t = sprintf ("<input type=\"checkbox\" name=\"%s[]\" value=\"%s\" class=\"%s\">", $name, $value, $class);
    return $t;
}

extract($_REQUEST);
$pg_name = "Datov� zpr�vy";
is_admin($pg_name);

$res = dbi_select(uchazec(), "datovka!=''");
$pg = "";

if ($res->num_rows<1) 
{
    $pg .= "<h3>Nelze pou��t datov� zpr�vy</h3>";
	h_page($pg,$pg_name,"pzk.css");
    exit;
}
$rr = dbi_select('dm_batch');
$_img = "<img src=\"imgs/%s\">";

$import_file = "<form method=\"post\" action=\"import_file_dm.php\" enctype=\"multipart/form-data\" onSubmit=\"return validate()\"><input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"8000000\"><input type=\"file\" name=\"i_file\" size=\"50\" accept=\"%s\">%s</form>";

$import_file_pdf = sprintf($import_file, ".pdf", h_btn("push_pdf","Upload souboru PDF"));
$import_file_p12 = sprintf($import_file, ".p12", h_btn("push_p12","Upload e-podpisu"));
$select_all_dm = sprintf("<input type=\"checkbox\" name=\"%s\" value=\"1\" onclick=\"%s\">", 'select_all_dm', "ToggleAll(this, 'toggle_dm');");

$pg .= sprintf(
"<table class=\"prihlaska\">
<tr><th colspan=\"3\">Upload soubor� PDF na server</th></td>
<tr><td colspan=\"3\">%s</td></tr>
<tr><th colspan=\"3\">Kvalifikovan� certifik�t</th></tr>
<tr><td colspan=\"3\">
<form  method=\"post\" action=\"%s\" enctype=\"multipart/form-data\" onSubmit=\"return validate()\">
    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"8000000\">
    <input type=\"file\" name=\"i_file\" size=\"50\" accept=\".p12\"><br>
    <label for=\"pass\">Heslo pro kvalifikovan� certifik�t:</label>
    <input type=\"password\" id=\"pass\" name=\"password\" placeholder=\"Zadej heslo pro certifik�t\">
</td></tr>
<tr>
<th>%s</th>
<th colspan=\"2\">D�vky pro sestaven� datov� zpr�vy</th></tr>
", $import_file_pdf, 'dm_send.php', $select_all_dm);    
	while ($r = $rr->fetch_assoc()) 
    {
        $select_one = h_chbx_array('dm_id', $r['id'], 'toggle_dm');
        $f_note = ""; $ff = array();
        if ($r['filename'] != '') {
            foreach (explode(';', $r['filename']) as $file) {
                if (file_exists($upload_dir.$file)) {
                    $ff[] = sprintf("<a href=\"%s\">%s</a>", readCfg('upload_dir').$file, $file);
                } else {
                    $ff[] = $file;
                }
            }                
            $f_note = sprintf("<b>Dal�� p��lohy:</b><br>%s", implode(", ", $ff));
        }
        $note = Markdown::defaultTransform($r['note']);
		$pg .= sprintf(
"<tr>
<td>%s</td>
<td>%s</td>
<td><b>%s</b><br>%s%s</td>
</tr>", $select_one, sprintf($_img, $r["image"]), $r["name"], $note, $f_note);
	}

	$pg .= sprintf("
</table>");

//$select_all = h_chbx('select_all');
$select_all = sprintf("<input type=\"checkbox\" name=\"%s\" value=\"1\" onclick=\"%s\">", 'select_all', "ToggleAll(this, 'toggle_id');");

$pg .= sprintf(
"<br>
<table class=\"prihlaska\">
<tr><th colspan=\"4\">P��jemci datov� zpr�vy</th></tr>
<tr>
<th>%s</th>
<th>P��jmen� a jm�no</th>
<th>Z�konn� z�stupce</th>
<th>Datovka</th>
</tr>", $select_all);
	while ($r = $res->fetch_assoc()) 
    {
        $select_one = h_chbx_array('select', $r['id'], 'toggle_id');
		$pg .= sprintf(
"<tr>
<td>%s</td>
<td>%s %s</td>
<td>%s %s</td>
<td>%s</td>
</tr>", $select_one, $r["prijmeni"], $r["jmeno"], $r['zast_prijmeni'], $r['zast_jmeno'], $r["datovka"]);
	}
	$pg .= sprintf("
</table>");
$pg .= sprintf("
<br>
%s
</form>", h_btn("odeslat", "Poslat datovou zpr�vu"));
	h_page($pg,$pg_name,"pzk.css",'','libpzk/scio.ins.js');
?>

