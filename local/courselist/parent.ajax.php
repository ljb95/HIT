<?php

require_once dirname(dirname(dirname (__FILE__))).'/config.php';

$parent = optional_param('parent', 0, PARAM_INT);

$currentlang = current_language();

$categories = array();
if($parent) {
    if($currentlang != 'ko') {
        $categories = $DB->get_records_sql('SELECT sortorder, id, name_eng AS name FROM {course_categories} WHERE parent = :parent ORDER BY sortorder', array('parent'=>$parent));
    } else { 
        $categories = $DB->get_records_sql('SELECT sortorder, id, name AS name FROM {course_categories} WHERE parent = :parent ORDER BY sortorder', array('parent'=>$parent));
        //$categories = $DB->get_records('course_categories', array('parent'=>$parent), 'name');
    }
}


@header('Content-type: application/json; charset=utf-8');
echo json_encode($categories);