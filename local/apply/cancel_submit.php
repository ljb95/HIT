<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/enrol/locallib.php');

$id =  required_param('id',PARAM_INT);
$unapprove_reason =  required_param('unapprove_reason',PARAM_TEXT);

$apply = $DB->get_record('approval_reason',array('id'=>$id));

$apply->approval_status = 3;
$apply->unapprove_reason = $unapprove_reason;

$courseid = $apply->courseid;
$course = $DB->get_record('course', array('id' => $courseid));

$manager = new course_enrolment_manager($PAGE, $course);

switch($apply->application_type){
    case 'assistant':$rolename = 'tutor';break;
    case 'auditor':$rolename='auditor'; break;
}
        
$roleid = $DB->get_field('role','id',array('shortname'=>$rolename));
$manager->unassign_role_from_user($apply->userid, $roleid);

$DB->update_record('approval_reason',$apply);

redirect('allow.php?id='.$apply->courseid);