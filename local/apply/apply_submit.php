<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/enrol/locallib.php');

$id = required_param('id', PARAM_INT);

$apply = $DB->get_record('approval_reason', array('id' => $id));

$apply->approval_status = 1;

$courseid = $apply->courseid;
$course = $DB->get_record('course',array('id'=>$courseid));

$manager = new course_enrolment_manager($PAGE, $course);
$enrolid = $DB->get_field('enrol', 'id', array('courseid' => $courseid, 'enrol' => 'manual'));
$instances = $manager->get_enrolment_instances();
$plugins = $manager->get_enrolment_plugins(true); // Do not allow actions on disabled plugins.
$instance = $instances[$enrolid];

switch($apply->application_type){
    case 'assistant':$rolename = 'tutor';break;
    case 'auditor':$rolename='auditor'; break;
}
        
$roleid = $DB->get_field('role','id',array('shortname'=>$rolename));

$context = context_course::instance($course->id, MUST_EXIST);
$plugin = $plugins[$instance->enrol];
$plugin->enrol_user($instance, $apply->userid, $roleid);

$DB->update_record('approval_reason', $apply);

redirect('allow.php?id=' . $apply->courseid);
