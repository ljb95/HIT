<?php
require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . "/siteadmin/lib.php");
require_once($CFG->dirroot . "/course/lib.php");
require_once($CFG->dirroot . "/lib/deprecatedlib.php");
require_once($CFG->dirroot . "/enrol/locallib.php");

global $PAGE, $SESSION;

$user = $DB->get_record('lmsdata_user', array('userid'=>$USER->id));
if(!is_siteadmin($USER) && ($user->usergroup != 'pr') && ($user->usergroup != 'sa')){
    redirect($CFG->wwwroot); 
}

$restore_id   = required_param('data', PARAM_INT);
$standard_id = $DB->get_field('lmsdata_class_drive_log','standard_id', array('id' => $restore_id));
$courses = $DB->get_records('lmsdata_class_drive_log', array('standard_id' => $standard_id, 'type' => LMSDATA_CLASS_MERGE_EXECUTE));

//강의 삭제
$standard_context= (context_course::instance($standard_id));
$standard_course = $DB->get_record('course', array('id' => $standard_id));
$coursecontext = context_course::instance($standard_course->id);
delete_course($standard_course);
add_class_drive_log($standard_course->id, $standard_course->id, LMSDATA_CLASS_DELETE_EXECUTE);

//강의활성화
foreach($courses as $course) {
    $subject_course = new stdClass;
    $subject_course->id = $course->subject_id;
    $subject_course->visible = 1;
    $subject_course->visibleold = $subject_course->visible;
    update_course($subject_course);
    
    $restore_log = new stdClass();
    $restore_log->id = $course->id;
    $restore_log->restore_flag = 1;
    $restore_log->restore_user_id = $USER->id;
    $restore_log->timerestore = time();
    $DB->update_record('lmsdata_class_drive_log', $restore_log);
}

