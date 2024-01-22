<?
#------------------------------
# rozmisteni do budoucích tøíd 
#------------------------------
require "libpzk/common.inc.php";
require "libpzk/sql.inc.php";
extract($_REQUEST);

$pg = "";
$csv_file = "tridy.csv";
$button = array('button_save' => 'Ulo¾it zmìny', 
                'button_csv' => 'Stáhnout CSV',
                'button_delete' => 'Vymazat rozmístìní');

$_tridy = array('X', 'A', 'B', 'C');
$_t = array('A' => array(), 'B' => array(), 'C' => array(),  'X' => array());
$_id = array('A' => array(), 'B' => array(), 'C' => array(),  'X' => array());


function outputCSV($data, $file_name = 'file.csv') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=$file_name");
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    $output = fopen("php://output", "w");                                                                
    fputcsv($output, ["A", "B", "C"]);
    foreach ($data as $row) {
        fputcsv($output, $row); 
    }
    fclose($output);
    exit;
}

function get_data() {
    foreach (['A', 'B', 'C']  as $_trida) {
        list($_t[$_trida], $_id[$_trida]) = create_array($_trida);
    }
    $data = array();
    $imax = max(count($_t['A']), count($_t['B']), count($_t['C']));
    for ($i=0; $i<$imax; $i++) 
    {
        $line = array();
        foreach (['A', 'B', 'C'] as $_trida) {
            if (isset($_t[$_trida][$i])) {
                $line[] = $_t[$_trida][$i];
            }
        }    
        $data[] = $line;
    }      
    return $data;
}

function get_rb($id, $trida='')
{
    $display = array('A', 'B', 'C');
    $check = ''; $t = '';
    foreach ($display as $d) {
        $check = ($d == $trida ? " checked" : "");
        $v = $id.":".$d;
        $t .= sprintf("&nbsp;<input type=\"radio\" name=\"u_%s\" value=\"%s\" %s>%s", $id, $v, $check, $d);
    }
    return $t;
}

function create_array($trida='X')
{
    $x = array();
    $y = array();
    if ($trida == 'X') { 
        $res = dbi_select("uchazec0", "prijat and id not in (select id_uchazec from trida)", "*", "prijmeni, jmeno");
        #$res = dbi_select("uchazec0", "prijat", "*", "prijmeni, jmeno");
    } else {        
        $res = dbi_select("trida, uchazec0", "trida.id_uchazec=uchazec0.id and paralelka='$trida'", "*", "prijmeni, jmeno");
   }        
    while ($r = $res->fetch_assoc())
    {
        $x[] = $r['prijmeni']." ".$r['jmeno'];
        $y[] = $r['id'];
    }        
    return array($x, $y);
}

if (isset($_POST['push'])) {

    switch ($_POST['push']) {
        case $button['button_save']:
            foreach ($_POST as $var=>$value) {
                if(strpos($value, ':') !== false) {
                    list($id, $tr) = split(":", $value);
                    $cols = array('id_uchazec' => $id, 'paralelka' => $tr); 
                    if (!dbi_insert_cols('trida', $cols, true)) dbi_update('trida', $cols, "id_uchazec=$id");
                }            
            }
            break;            
        
        case $button['button_csv']:
            $data = get_data();
            outputCSV($data, $csv_file);
            break;

        case $button['button_delete']:
            dbi_delete_all('trida');
            break;
    }
}

$pg_name = "Rozmístìní pøijatých do tøíd";

if (!isAdminUser()) {
    $pg = "Nejste autorizován pro vstup na tuto stránku!";
    h_page($pg,$pg_name,"pzk.css");
    exit;
}

$pg .= sprintf("
<form method=\"post\" onSubmit=\"return validate()\">
%s %s
<br><br>
<table class=\"prihlaska\">
<tr>
  <th>nezaøazení</th>
  <th>tøída A</th>
  <th>tøída B</th>
  <th>tøída C</th>
</tr>", h_btn('push', $button['button_csv']), h_btn('push', $button['button_delete']));

foreach ($_tridy as $_trida) {
    list($_t[$_trida], $_id[$_trida]) = create_array($_trida);
}

$imax = max(count($_t['A']), count($_t['B']), count($_t['C']), count($_t['X']));
$inner_table = "<table style=\"border-collapse:collapse; border:%spx solid; width:350px;\"><tr>
    <td style=\"text-align:right; width:20px;   \">%s</td><td style=\"text-align:left;\">%s</td><td style=\"text-align:right;\">%s</td
    </tr></table>\n";
$td = "<td>".$inner_table."</td>\n"; 

for ($i=0; $i<$imax; $i++) 
{
    $row = '';
    foreach ($_tridy as $_trida) {
        $X = '';
        $X_index = '';
        $X_rb = '';
        $X_border = 0;

        if (isset($_t[$_trida][$i])) {
            $X = $_t[$_trida][$i];
            $X_index = $i+1;
            $X_rb = get_rb($_id[$_trida][$i], $_trida);
            $X_border = 1;
        }            
        $row .= sprintf($td, $X_border, $X_index, $X, $X_rb);
    }      
    $pg .= "<tr>".$row."</tr>";
}
$pg .= "</table><br>";
$pg .= h_btn('push', $button['button_save']);
$pg .= "</form>";
h_page($pg, $pg_name, "pzk.css",'','libpzk/scio.ins.js');
?>

