<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . "/ysadmin/lib.php");
require_once($CFG->dirroot . "/lib/deprecatedlib.php");
require_once($CFG->dirroot . "/enrol/locallib.php");
require_once($CFG->dirroot . "/course/lib.php");

global $PAGE, $DB, $USER;

$usergroup = $DB->get_field('lmsdata_user', 'usergroup', array('userid' => $USER->id));

if(!is_siteadmin($USER) && ($usergroup != 'pr') && ($usergroup != 'sa')){
    die;
}

$standard   = optional_param('standard', 0, PARAM_INT);
$split_list     = optional_param_array('split_list', array(), PARAM_INT);

$standard_context= (context_course::instance($standard));
if(($key = array_search($standard, $split_list)) !== false) {
    unset($split_list[$key]);
}

$standard_course = $DB->get_record('course', array('id' => $standard));

//이미 등록 되어 있는 사용자
$users = get_courses_role_assignments(array($standard));
$standard_users = array();
foreach ($users as $user){
    if(isset($standard_users[$user->userid])) {
        $standard_users[$user->userid] = ($user->roleid > $standard_users[$user->userid]) ? ($user->roleid) : ($standard_users[$user->userid]);
    } else {
        $standard_users[$user->userid] = $user->roleid;
    }
}

// 등록할 user
$assign_users = get_courses_role_assignments($split_list);

$role_editingteacher = $DB->get_record('role', array('shortname' => 'editingteacher'), 'id, shortname');
$role_editingteacher01 = $DB->get_record('role', array('shortname' => 'editingteacher01'), 'id, shortname');

$assign_count = 0;
foreach ($assign_users as $assign_user) {
    if(!isset($standard_users[$assign_user->userid])){
        set_assign_user($standard_course, $assign_user);
        $standard_users[$assign_user->userid] = $assign_user->roleid;
        $assign_count++;
    }
}

foreach ($split_list as $courseid) {
    if($standard != $courseid){
        $course = new stdClass;
        $course->id = $courseid;
        $course->visible = 0;
        $course->visibleold = $course->visible;
        update_course($course);
    }
}

foreach($split_list as $sid) {
    add_class_drive_log($standard, $sid, LMSDATA_CLASS_DRIVE_EXECUTE);
}

echo $assign_count;

