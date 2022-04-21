<?php

require_once("../../config.php");

$newcategory2 = new stdClass();
$newcategory2->name = "Sample";
$newcategory2->description = "For the course Sample";
$newcategory2->idnumber = "jinotech_sample_2015_01_28_categorynumber";
$newcategory2->parent = 0;
$newcategory2->sortorder = 999;

if (!$newcategory2->id = $DB->insert_record('course_categories', $newcategory2)) {
    
}

mark_context_dirty($newcategory2->context->path);
fix_course_sortorder();
