<?php
    #----------------------------
	# import souboru pro dm
	#----------------------------

    require "libpzk/common.inc.php";
	require "libpzk/sql.inc.php";
    extract($_REQUEST);

    is_admin(); 
    upload_file('i_file');
    $path = GetPath($REQUEST_URI)."/datovka.php";
    Header(get_location($path));
?>
