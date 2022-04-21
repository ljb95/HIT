<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once dirname(dirname (__FILE__)).'/lib.php';
require_once($CFG->dirroot.'/lib/enrollib.php');
require_once($CFG->dirroot.'/enrol/locallib.php');

$user_list  = required_param_array('users', PARAM_INT);
$courseid = required_param('course', PARAM_INT);

$context = context_course::instance($courseid, MUST_EXIST);

$PAGE->set_context($context);

$enrol_course = $DB->get_record('course', array('id' => $courseid));
$manager = new course_enrolment_manager($PAGE, $enrol_course);

$instances = $manager->get_enrolment_instances();
$plugins = $manager->get_enrolment_plugins();

$sql_select = ' SELECT ue.id, ue.enrolid, ue.userid, en.enrol  
                FROM {enrol} en 
                JOIN {user_enrolments} ue ON ue.enrolid = en.id ';

list($sql, $params) = $DB->get_in_or_equal($user_list, SQL_PARAMS_NAMED, 'ueid');

$sql_in = ' WHERE ue.id '. $sql;

$enrols = $DB->get_records_sql($sql_select.$sql_in, $params);

foreach ($enrols as $enrol) {
     $instance = $instances[$enrol->enrolid];
     $plugin = $plugins[$enrol->enrol];
     $plugin->unenrol_user($instance, $enrol->userid);
}
