<?
	$menu = "menu.txt";
	$mid = new XLayersMenu();
	$mid->setMenuStructureFile($menu);				//Struktura menu v souboru
//	$mid->setVerticalMenuTpl("layersmenu-vertical_menu.ihtml");	
	$mid->setHorizontalMenuTpl("layersmenu-horizontal_menu.ihtml");	//V�stavba hlavn�ho menu horizont�ln�
//	$mid->setHorizontalMenuTpl("templates/layersmenu-horizontal_menu-red.ihtml");
	
	$mid->setDownArrow("&nbsp;&gt;&gt;");
//	$mid->setForwardArrowImg("forward-nautilus.png");
	$mid->setForwardArrow("&nbsp;&gt;&gt;");
	$mid->parseMenuStructure("menu");
	
	$mid->setSubMenuTpl("layersmenu-sub_menu-black.ihtml");		//V�stavba rozv�jec�ho menu
	//$mid->newPHPTreeMenu("vermenu");
	//$mid->setTreeMenuDefaultExpansion("82");
	//$mid->newTreeMenu("vermenu");
//	$mid->newVerticalMenu("menu");
	$mid->newHorizontalMenu("menu");
	$mid->printHeader();
	$mid->printMenu("menu");
?>
