<?php

function xmldb_local_template_install() {
    global $DB;
    $context = context_system::instance();
	
    $newcategory = new stdClass();
    $newcategory->name = "Template";
    $newcategory->description = "For the course template";
    $newcategory->idnumber = "oklass_template";
    $newcategory->parent = 0; 
    $newcategory->sortorder = 999;

    if (!$newcategory->id = $DB->insert_record('course_categories', $newcategory)) {
        
    }
    
    $newcategory2 = new stdClass();
    $newcategory2->name = "Sample";
    $newcategory2->description = "For the course Sample";
    $newcategory2->idnumber = "oklass_sample";
    $newcategory2->parent = 0; 
    $newcategory2->sortorder = 999;

    if (!$newcategory2->id = $DB->insert_record('course_categories', $newcategory2)) {
        
    }

    $newcategory->context = get_context_instance(CONTEXT_COURSECAT, $newcategory->id);
    $newcategory2->context = get_context_instance(CONTEXT_COURSECAT, $newcategory2->id);
    
    $context->mark_dirty($newcategory->context->path);
    fix_course_sortorder(); 

    $context->mark_dirty($newcategory2->context->path);
    fix_course_sortorder(); 
    
    
}