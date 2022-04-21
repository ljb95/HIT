<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

$id   = required_param('id', PARAM_INT);
$approval   = required_param('approval', PARAM_BOOL);
$user_list  = required_param_array('user_list', PARAM_INT);

if($approval) {
    $status = 0;
} else {
    $status = 1;
}

$enrol_id = $DB->get_field('enrol', 'id', array('courseid' => $id, 'enrol' => 'manual'));

list($sql, $params) = $DB->get_in_or_equal($user_list, SQL_PARAMS_NAMED, 'userid');
$sql_in = ' userid '. $sql;
$sql_in .= ' and enrolid = :enrolid ';
$params['enrolid'] = $enrol_id;

$DB->set_field_select('user_enrolments', 'status', $status, $sql_in, $params);
