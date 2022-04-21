<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once($CFG->dirroot . "/siteadmin/lib.php");
require_once($CFG->dirroot . "/course/lib.php");
require_once($CFG->dirroot . "/lib/deprecatedlib.php");
require_once($CFG->dirroot . "/enrol/locallib.php");

if(!is_siteadmin($USER)){
    redirect($CFG->wwwroot);
}

global $PAGE, $SESSION;

$sdcourse   = required_param('sdcourse', PARAM_INT);
$subcourses = required_param_array('subcourse', PARAM_INT);

$standard_context = context_course::instance($sdcourse);
$standard_course  = $DB->get_record('course', array('id' => $sdcourse));
$sub_users   = get_courses_role_assignments($subcourses);

$manager = new course_enrolment_manager($PAGE, $standard_course);

$plugin = enrol_get_plugin('manual');
$instance = $DB->get_record('enrol', array('enrol'=>'manual', 'courseid'=> $sdcourse), '*', MUST_EXIST);

$role_editingteacher = $DB->get_record('role', array('shortname' => 'editingteacher'), 'id, shortname');
$role_editingteacher01 = $DB->get_record('role', array('shortname' => 'editingteacher01'), 'id, shortname');

$restore_count = 0;
foreach($sub_users as $user) {
    $roles = $manager->get_user_roles($user->userid);
    if(sizeof($roles) > 1) {
        if(!empty($role_editingteacher01) && ($role_editingteacher->id == $user->roleid)){
            $manager->unassign_role_from_user($user->userid, $role_editingteach01);
        } else {
            $manager->unassign_role_from_user($user->userid, $user->roleid);
        }
    } else {
      $plugin->unenrol_user($instance, $user->userid);	
    }
    $restore_count++;
}

foreach($subcourses as $subcourse) {
    course_change_visibility($subcourse, true);
    
}

list($sql_in, $subject_params) = $DB->get_in_or_equal($subcourses, SQL_PARAMS_NAMED, 'subject_id');
$sql_where = " WHERE subject_id ".$sql_in." and standard_id = :standard_id ";

$sql_select  = " SELECT * FROM {lmsdata_class_drive_log} ";

$subject_params['standard_id'] = $sdcourse;
    
$drive_logs = $DB->get_records_sql($sql_select.$sql_where, $subject_params);

foreach($drive_logs as $log) {
    $log->restore_flag = 1;
    $DB->update_record('lmsdata_class_drive_log', $log);
    
}
echo $restore_count;

