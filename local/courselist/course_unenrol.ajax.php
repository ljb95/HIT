<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot."/enrol/apply/lib.php");

$id   = required_param('id', PARAM_INT);
$user_list  = required_param_array('user_list', PARAM_INT);

$enrolinstance = $DB->get_record('enrol', array('courseid'=>$id, 'status'=>ENROL_INSTANCE_ENABLED, 'enrol' => 'apply'));
$enrol_apply = new enrol_apply_plugin();

$count = 0;
foreach ($user_list as $userid) {
    $enrol_apply->unenrol_user($enrolinstance, $userid);
    $count++;
}

echo $count;