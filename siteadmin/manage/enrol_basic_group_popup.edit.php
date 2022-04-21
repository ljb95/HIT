<?php 
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot.'/siteadmin/lib.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->dirroot.'/lib/accesslib.php');
require_once($CFG->dirroot.'/lib/enrollib.php');
require_once($CFG->dirroot.'/enrol/locallib.php');

global $DB, $PAGE;

$new_list = optional_param_array('new_list',array(), PARAM_INT);
$mgroupid = required_param('mgroupid', PARAM_INT); 
$courseid = required_param('course', PARAM_INT); 
$shortname = required_param('shortname', PARAM_RAW); 
$context = context_course::instance($courseid);
$PAGE->set_context($context);

if($shortname == 'tutor') {
    $enrol = $DB->get_record('enrol', array('enrol'=>'apply', 'courseid'=>$courseid));
    
    $roleid = $DB->get_field('role', 'id', array('shortname'=>'editingteacher01'));
    
    
    $course = $DB->get_record('course', array('id'=>$courseid));
    $lmsdata_course = $DB->get_record('lmsdata_class', array('course'=>$courseid));
    $manager = new course_enrolment_manager($PAGE, $course);

    $instances = $manager->get_enrolment_instances();
    $instance = $instances[$enrol->id];

    $plugins = $manager->get_enrolment_plugins();
    $plugin = enrol_get_plugin($instance->enrol);
    
    $et_sql = ' SELECT gm.userid FROM {groups_members} gm
                JOIN {role_assignments} ra ON ra.userid = gm.userid
                JOIN {role} ro ON ro.id = ra.roleid 
                WHERE gm.groupid = :groupid and ra.contextid = :contextid and ro.shortname = :shortname ';
    $et_params = array(
                    'groupid'=> $mgroupid,
                    'contextid'=> $context->id,
                    'shortname'=> 'editingteacher01'
                    );
    
    $et_members = $DB->get_records_sql($et_sql, $et_params);
    
    // unenrol_user() 에서 groups_members 값도 삭제함
    foreach($et_members as $et_member) {
        if(is_enrolled($context,  $et_member->userid)) {
            $plugin->unenrol_user($instance, $et_member->userid);
        }
    }
    
    foreach($new_list as $userid) {
        if(is_enrolled($context, $userid)) {
                $timestart = $lmsdata_course->timestart;
                $plugin->update_user_enrol($instance, $userid ,0 , $timestart, 0);
            } else {
                $plugin->enrol_user($instance, $userid, $roleid, $course->startdate, 0, null);
            }
        
        groups_add_member($mgroupid, $userid);
    }
    
} else if($shortname == 'student') {
    
    $sql = ' SELECT gm.userid FROM {groups_members} gm
                JOIN {role_assignments} ra ON ra.userid = gm.userid
                JOIN {role} ro ON ro.id = ra.roleid 
                WHERE gm.groupid = :groupid and ra.contextid = :contextid and ro.shortname = :shortname ';
    $params = array(
                    'groupid'=> $mgroupid,
                    'contextid'=> $context->id,
                    'shortname'=> 'student'
                    );
    
    $members = $DB->get_records_sql($sql, $params);
    
    foreach($members as $member) {
        groups_remove_member($mgroupid, $member->userid);
    }

    if(!empty($new_list)) {
        foreach($new_list as $userid) {
            groups_add_member($mgroupid, $userid);
        }
    }

}

echo '<script type="text/javascript">
      document.location.href="/siteadmin/manage/enrol_basic_group.php?course='.$courseid.'"
      </script>';