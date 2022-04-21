<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->libdir . '/formslib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once($CFG->dirroot.'/lib/grade/grade_grade.php');
require_once($CFG->dirroot.'/local/online_attendance/classes/autoloader.php');
require_once $CFG->dirroot . '/local/online_attendance/lib.php';

$id = required_param('id', PARAM_INT);    // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);
$context = context_course::instance($course->id);
$PAGE->set_context($context);

$fullname = $DB->sql_fullname('ur.firstname', 'ur.lastname');
$sql_select = "SELECT  ur.id,
                       ur.username, ".$DB->sql_fullname('ur.firstname', 'ur.lastname')." AS fullname ";
$sql_from = " FROM {context} co 
              JOIN {role_assignments} ra ON ra.contextid = co.id and roleid = :roleid
              JOIN {user} ur ON ur.id = ra.userid ";

$sql_conditions[] = ' co.id = :contextid ';
$sql_conditions[] = ' ur.deleted = :deleted ';

$params['contextid'] = $context->id;
$params['roleid'] = $DB->get_field('role', 'id', array('archetype' => 'student'));
$params['deleted'] = 0;


$sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
$sql_order_by = ' ORDER BY loc.section, loc.cmid ASC ';

$users = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $params);

// user의 주차별 출석 데이터
if(!empty($users)){
    $userlist = array();
    foreach($users as $user) {
        $userlist[] = $user->id;
    }
}

$weeks_status = local_onattendance_week_status($id, $userlist);
$weektstatus = array();
foreach($weeks_status as $wst) {
    $weektstatus[$wst->userid][$wst->sec] = $wst->status; 
}
unset($weeks_status); 
$sections = $DB->get_records_sql_menu('SELECT section FROM {local_onattend_cm_set} WHERE approval = 1 GROUP BY section order BY section', array('approval'=>1));
$sections = array_keys($sections);

//강의 출석 설정 정보
$setupdata = new online_attendance($id);

$count = 0;
foreach($users as $user) {
    $status_count = array();
    foreach($sections as $sect) {
        $weekdata = $weektstatus[$user->id][$sect];
        $status_count[$weekdata]++;
    }
    $absent = 0;
    $absent += $status_count[0];
    $absent += floor($status_count[2] / $setupdata->late);
    $grade = floor($setupdata->maxscore) + ($absent * $setupdata->absent);
    $minscore = floor($setupdata->minscore);
    if($grade < $minscore) {
        $grade = $minscore;
    }
    
    $userid = $user->id;
    $gradegrade = grade_grade::fetch(array('itemid' => $setupdata->itemid, 'userid' => $userid));
    if(empty($gradegrade)) {
        $gradegrade = new grade_grade();
        $gradegrade->itemid = $setupdata->itemid;
        $gradegrade->userid = $userid;
        $gradegrade->finalgrade = $grade;
        $gradegrade->rawgrademax = floor($setupdata->maxscore);
        $gradegrade->rawgrademin = floor ($setupdata->minscore);
        $gradegrade->timecreated = time();
        $gradegrade->timemodified = time();
        $gradegrade->insert();
    }else {
        $gradegrade->finalgrade = $grade;
        $gradegrade->update();
    }
        $count++;
}

$returnvalue = new stdClass();
$returnvalue->status = 'success';
$returnvalue->count = $count;
$returnvalue->text = get_string('book:alert2', 'local_offline_attendance', $returnvalue);

@header('Content-type: application/json; charset=utf-8');
echo json_encode($returnvalue);