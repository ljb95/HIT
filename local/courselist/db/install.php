<?php

function xmldb_local_courselist_install() {
	
    $newcategory = new stdClass();
    $newcategory->name = "교과과정";
    $newcategory->description = " ";
    $newcategory->idnumber = "oklass_regular";
    $newcategory->sortorder = 999;

    $regular = coursecat::create($newcategory);
    
    
    $newcategory = new stdClass();
    $newcategory->name = "비교과과정";
    $newcategory->description = " ";
    $newcategory->idnumber = "oklass_irregular";
    $newcategory->sortorder = 999;

    $irregular = coursecat::create($newcategory);
    
    $newcategory = new stdClass();
    $newcategory->name = "MOOC";
    $newcategory->description = " ";
    $newcategory->idnumber = "oklass_mooc";
    $newcategory->sortorder = 999;

    $mooc = coursecat::create($newcategory);
    
    $newcategory = new stdClass();
    $newcategory->name = "o-Class";
    $newcategory->description = " ";
    $newcategory->idnumber = "oklass_oclass";
    $newcategory->sortorder = 999;

    $oclass = coursecat::create($newcategory);
    

    $newcategory2 = new stdClass();
    $newcategory2->name = "OKlass 자체과목";
    $newcategory2->description = " ";
    $newcategory2->idnumber = "oklass_selfcourse";
    $newcategory2->parent = $irregular->id; 
    $newcategory2->sortorder = 999;

    coursecat::create($newcategory2);
    
    
}
