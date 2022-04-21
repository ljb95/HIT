<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once($CFG->dirroot . "/siteadmin/lib.php");
require_once($CFG->dirroot . "/lib/deprecatedlib.php");
require_once($CFG->dirroot . "/enrol/locallib.php");
require_once($CFG->dirroot.'/course/lib.php');

if(!is_siteadmin($USER)){
    redirect($CFG->wwwroot);
}

global $PAGE, $SESSION;

$standard   = optional_param('standard', 0, PARAM_INT);
$flag       = optional_param('flag', 0, PARAM_INT);
$list       = optional_param_array('list', array(), PARAM_INT);

$standard_context= (context_course::instance($standard));
if(($key = array_search($standard, $list)) !== false) {
    unset($list[$key]);
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
$assign_users = get_courses_role_assignments($list);

$assign_count = 0;

if(empty($assign_users)){
    
    $role_editingteacher01 = $DB->get_record('role', array('shortname' => 'editingteacher01'), 'id, shortname');
    $role_editingteacher = $DB->get_record('role', array('shortname' => 'editingteacher'), 'id, shortname');

    foreach ($assign_users as $assign_user) {
        if(!isset($standard_users[$assign_user->userid])){
            if($assign_user->roleid == $role_editingteacher->id) {
                if(!empty($role_editingteacher01)) {
                    $assign_user->roleid = $role_editingteacher01->id;
                } else {
                    $assign_user->roleid = $role_editingteacher->id;
                }
            }
            set_assign_user($standard_course, $assign_user);
            $standard_users[$assign_user->userid] = $assign_user->roleid;
            $assign_count++;
        }
    }
}

foreach ($list as $subjectid) {
    if(($key = array_search($subjectid, $SESSION->split_course)) !== false) {
        unset($SESSION->split_course[$key]);
    }
    if($flag) {
        delete_course($subjectid);
        add_class_drive_log($standard, $subjectid, LMSDATA_CLASS_DELETE_EXECUTE, 0);
    } else {
        course_change_visibility($subjectid, false);
        add_class_drive_log($standard, $subjectid, LMSDATA_CLASS_DRIVE_EXECUTE, 0);
    }
}

if(($key = array_search($standard, $SESSION->split_course)) !== false) {
    unset($SESSION->split_course[$key]);
}

echo $assign_count;

