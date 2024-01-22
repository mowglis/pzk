<?
#------------------------------
# odesílání datových zpráv
#------------------------------
require "libpzk/common.inc.php";
require "libpzk/sql.inc.php";

$upload_dir = readCfg('root_dir').readCfg('upload_dir');
$pdf_dir = readCfg('root_dir').readCfg('pdf_dir');
$ds_login = readCfg('ds_login');
$ds_password = readCfg('ds_password');
$cermat_dir = readCfg('root_dir').readCfg('cermat_dir');

function pozvanka_cermat($id)
{
    # -- pozvánky od Cermatu
    global $cermat_dir;
    $p_cermat = 'p_cermat_';
    return $cermat_dir.$p_cermat.$id.'.pdf';
}

function vysledky_cermat($id)
{
    # -- výsledky od Cermatu
    global $cermat_dir;
    $p_cermat = 'vysl_cermat_';
    return $cermat_dir.$p_cermat.$id.'.pdf';
}

function print_page()
{
    global $pg, $pg_name, $text;
    $pg .= sprintf("<textarea readonly class=\"textinfo\">%s</textarea>", $text);
    h_page($pg,$pg_name,"pzk.css");
    return;
}

function emergency_stop($t)
{
    global $text;
    $text .= sprintf("\n%s\n\n----- EMERGENCY STOP! -----", $t);
    print_page();
    exit;
}

function sign_pdf($pdf_file)
{
    global $upload_dir, $text, $p12_file;
    if (!empty($_POST['password']) && !empty($p12_file))
    {
        $sig_file = 'imgs/'.readUser('signature');
        $pdf_signed = basename($pdf_file,'.pdf').'_sign.pdf';
        $password = $_POST['password'];
        $PortableSigner = "/usr/local/bin/PortableSigner -n -b en -t %s -o %s -s %s -p %s -i %s";
        $cmd = sprintf($PortableSigner, $upload_dir.$pdf_file, $upload_dir.$pdf_signed, $p12_file, $password, $sig_file); 
        $text .= sprintf("Podepsání PDF dokumentu:\n%s\n", preg_replace("/$password/","***", $cmd));
        system($cmd);
        if (!file_exists($upload_dir.$pdf_signed))
        {
            emergency_stop(sprintf("Nepodaøilo se podepsat dokument: %s", $pdf_file));
        }            
            
    } else {
        $pdf_signed = "";
        emergency_stop("Nelze podepsat pdf soubor!");
    }        
    return $pdf_signed;        
}

function generate_pdf($template, $id)
{
    # -- generate pdf docs
    global $pdf_dir, $upload_dir, $text;
    $file = $template.'_'.$id; 
    $pdf_file = $file.'.pdf';
    $report = "/home/rusek/pzk/mkreport.pl -t %s -s3 -n %s -w\"id=%s\" > /dev/null 2> /dev/null";
    $cmd = sprintf($report, $template, $file, $id);
    $text .= sprintf("Vytvoøení PDF dokumentu:\n%s\n", $cmd);
    system($cmd);
    rename($pdf_dir.$pdf_file, $upload_dir.$pdf_file);
    if (!file_exists($upload_dir.$pdf_file)) emergency_stop(sprintf("Nepodaøilo se vytvoøit PDF: %s", $pdf_file));
    return $pdf_file;
}

function get_attachements($r, $id)
{
    # -- PDF attachements dm --
    global $upload_dir;
    if (isset($r['template']) && !empty($r['template']))
    {
        $pdf_file = generate_pdf($r['template'], $id);
        if (isset($r['signed']) && $r['signed'] == 1) 
        {
            $ff[] = $upload_dir.sign_pdf($pdf_file);
        } else {
            $ff[] = $upload_dir.$pdf_file; 
        }        
    }
    if (!empty($r['filename'])) {
        foreach (explode(';', $r['filename']) as $f)
        {
            if (substr($f, 0, 1) == '%') 
            {
                $fce_name = substr($f, 1);
                $ff[] = $fce_name($id);
            } else {       
                if (!file_exists($upload_dir.$f)) emergency_stop(sprintf("Nelze nalézt soubor: %s", $f));
                $ff[] = $upload_dir.$f;
            }                
        }
    }        
    return implode(';',$ff);
}

function send_dm($r)
{
    # -- send dm via datovka --
    global $text, $dm_id, $ds_login, $ds_password; 
    $subj = "Pøijímací øízení";
    $datovka_cli = "su - datovka -c \"xvfb-run -a -d datovka --login \\\"username='%s',password='%s'\\\" --send-msg \\\"dbIDRecipient='%s',dmAnnotation='%s',dmAttachment='%s'\\\"\"";
    # !!! -- only for debug !!!
    #$r['datovka'] = 'iz4z3qm'; # for dm test --> rusek

    foreach ($dm_id as $_id_dm)
    {
        $r2 = dbi_select('dm_batch', "id=$_id_dm");
        $rr = $r2->fetch_assoc();
        $subj = $subj." - ".strtolower($rr['name']);
        $text .= sprintf("Jméno: %s %s (%s), datovka: %s, dávka: %s\n\n", $r['prijmeni'], $r['jmeno'], $r['id'], $r['datovka'], $rr['name']);
        $cmd = sprintf($datovka_cli, $ds_login, $ds_password, $r['datovka'], $subj, get_attachements($rr, $r['id']));
        $text .= sprintf("Odeslání datové zprávy:\n%s\n\n", preg_replace("/$ds_password/", "***", $cmd));
        $text .= shell_exec(iconv("ISO-8859-2", "UTF-8",$cmd));
    }
    return;
}

extract($_REQUEST);
$pg = "<h2>Výpis prùbìhu zpracování datových zpráv</h2>";
$pg_name = "Odeslání datové zprávy";
$text = '';
is_admin($pg_name);

$p12_file = upload_file('i_file');
$text .= sprintf("Soubor s certifikátem: %s\n", $p12_file);
if (isset($_POST['password']) && $_POST['password']!='')
{
    $text .= "Heslo: === zadáno ===\n\n";
} else {
    $text .= "Heslo: --- NEZADÁNO ---\n\n";
}   

if (!isset($select_all)) $select_all = "";
if (!isset($select)) $select = [];
if (!isset($dm_id) || (empty($select) && empty($select_all))) 
{
    emergency_stop("Nebyla vybrána datová zpráva nebo její pøíjemce");
}

//var_dump($select_all);
//var_dump($select);
//var_dump($dm_id);

$whr = "datovka!='' and id=%s";
foreach ($select as $id)
{
    $res = dbi_select(uchazec(), sprintf($whr, $id));
    send_dm($res->fetch_assoc());
}        
print_page();
?>
