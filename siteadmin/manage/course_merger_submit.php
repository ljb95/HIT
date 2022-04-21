<?php
require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot . "/siteadmin/lib.php");

$merger_id    = optional_param('merger_course_id_submit', 0, PARAM_INT);
$criterion_id = optional_param('criterion_course_id_submit', 0, PARAM_INT);
$ismerger    = optional_param('ismerger_submit', 1, PARAM_INT);

$merger = $DB->get_record('course', array('id' => $merger_id), '*', MUST_EXIST);
$criterion = $DB->get_record('course', array('id'=>$criterion_id));

$usergroup = $DB->get_field('lmsdata_user', 'usergroup', array('userid' => $USER->id));
if(!is_siteadmin($USER) && ($usergroup != get_string('teacher', 'local_lmsdata'))){
    die;  
}
if($ismerger){
    $sql_select = "SELECT mu.*, lu.userid, ra.roleid ";
    $sql_from = "FROM {context} ct
                JOIN {role_assignments} ra on ra.contextid = ct.id AND (ra.roleid = :roleid OR ra.roleid = :roleid2)
                JOIN {user} mu on mu.id = ra.userid
                JOIN {lmsdata_user} lu on lu.userid = ra.userid 
                JOIN {role} mr on mr.id = ra.roleid ";
    $sql_where = "WHERE ct.contextlevel = :contextlevel AND ct.instanceid = :instanceid";
    $users = $DB->get_records_sql($sql_select.$sql_from.$sql_where, array('contextlevel'=>CONTEXT_COURSE, 'instanceid'=>$merger_id, 'roleid'=>5, 'roleid2'=>9));
    
    foreach($users as $user){
        set_assign_user($criterion, $user);
    }
}
delete_course($merger);

redirect($CFG->wwwroot . '/siteadmin/manage/course_list.php?coursetype=1');

?>