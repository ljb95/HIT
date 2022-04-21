<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';

$pid = required_param('id', PARAM_INT);

$returnvalue = new stdClass();

$catagories = $DB->get_records('course_categories', array('visible'=>1, 'parent'=>$pid), 'sortorder', 'id, idnumber, name');
if($catagories) {
    $returnvalue->status = 'success';
    $returnvalue->categories = $catagories;
} else {
    $returnvalue->status = 'error';
    $returnvalue->message = get_string('empty_case','local_lmsdata');
}


@header('Content-type: application/json; charset=utf-8');
echo json_encode($returnvalue);