<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib/paging.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib.php';

$id     = required_param('courseid', PARAM_INT);

$sql = "select u.*
                from {course} c 
                join {context} ct on ct.contextlevel = 50 and ct.instanceid = c.id 
                join {role_assignments} ra on ra.contextid = ct.id 
                join {user} u on u.id = ra.userid  
                join {role} r on r.id = ra.roleid and r.shortname = 'editingteacher' 
                where c.id = :courseid";
$professors = $DB->get_records_sql($sql, array('courseid' => $id));
foreach($professors as $professor){
    echo fullname($professor).'<input type="checkbox" value="'.$professor->id.'" name="professor[]">';
}