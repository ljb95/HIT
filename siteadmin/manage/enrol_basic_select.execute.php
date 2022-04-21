<?php 
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot.'/siteadmin/lib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/lib/coursecatlib.php');
require_once($CFG->dirroot.'/lib/accesslib.php');
require_once($CFG->dirroot.'/lib/enrollib.php');
require_once($CFG->dirroot.'/enrol/locallib.php');

    global $DB, $PAGE;

    $enrol_users = required_param_array('new_list', PARAM_INT);
    $courseid = required_param('course', PARAM_INT);
    
    $enrol = $DB->get_record('enrol', array('enrol'=>'apply', 'courseid'=>$courseid));
    
    $roleid = $DB->get_field('role', 'id', array('shortname'=>'student'));
    
    $context = context_course::instance($courseid);
    
    $course = $DB->get_record('course', array('id'=>$courseid));
    $lmsdata_course = $DB->get_record('lmsdata_class', array('course'=>$courseid));
    $manager = new course_enrolment_manager($PAGE, $course);

    $instances = $manager->get_enrolment_instances();
    $instance = $instances[$enrol->id];

    $plugins = $manager->get_enrolment_plugins();
    $plugin = $plugins[$instance->enrol];
    
    foreach($enrol_users as $userid) {
        if(is_enrolled($context, $userid)) {
                $timestart = $lmsdata_course->timestart;
                $plugin->update_user_enrol($instance, $userid ,0 , $timestart, 0);
            } else {
                $plugin->enrol_user($instance, $userid, $roleid, $course->startdate, 0, null);
            }
    }
    
    redirect($CFG->wwwroot.'/siteadmin/manage/enrol_basic_students.php?course='.$courseid);
?>


