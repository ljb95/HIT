<?php

require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/offline_attendance/lib.php';

$id       = required_param('id', PARAM_INT);    // course id
$unixtime = required_param('unixtime', PARAM_NUMBER); // unixtime
$value = required_param('value', PARAM_INT);

$context = context_course::instance($id);
$PAGE->set_context($context);

$roleobjs = $DB->get_records('role', array('archetype' => 'student'));
$roles = array_keys($roleobjs);

// section을 만들어 줌
$section = $DB->get_record_sql('SELECT * FROM {local_off_attendance_section} WHERE courseid = :courseid and timedate = :timedate ORDER BY timeend DESC ', array('courseid'=>$id, 'timedate'=>$unixtime));
if(empty($section->id)) {
    $timedates = $DB->get_records_sql('SELECT code  FROM {local_off_attendance_section} ', array('courseid' => $id));
    do{
        $code = mt_rand(10000, 99999);    
    }while(!empty($timedates[$code]));

    $section = new stdClass();
    $section->courseid = $id;
    $section->userid = $USER->id;
    $section->code = $code;
    $section->timestart = time();
    $section->timeend = time();
    $section->timedate = $unixtime;
    $section->id = $DB->insert_record('local_off_attendance_section', $section);
    
// status 테이블에 값이 없을 경우(unixtime : 날짜 값이 최초인 경우) status 테이블에 status 필드 2 값으로 초기화 값 넣어줌
    $sql_select = "SELECT  ur.*, loa.status  ";
    $sql_from = " FROM {user} ur
                  JOIN (
                    SELECT userid 
                    FROM {role_assignments} 
                    WHERE contextid = :contextid AND roleid $sql_in
                    GROUP BY userid 
                    ) ra ON ra.userid = ur.id
                  LEFT JOIN {local_off_attendance_status} loa ON ur.id = loa.userid and loa.timedate = :timedate and loa.courseid = :courseid ";

    $sql_conditions = array('ur.deleted = :deleted');
    
    list($sql_in, $sql_params) = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'roleid');
    
    $sql_params['contextid'] = $context->id;
    $sql_params['timedate'] = $unixtime;
    $sql_params['courseid'] = $id;
    $sql_params['deleted'] = 0;
    $sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
    $sql_orderby = ' ORDER BY ur.firstname, lastname ASC ';

    $users = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $sql_params);
    
    foreach($users as $user){
        if(is_null($user->status)) {
            $status = new stdClass();
            $status->courseid = $id;
            $status->userid = $user->id;
            $status->status = 2;
            $status->lastcode = $section->code;
            $status->timedate = $unixtime;
            $status->timecreated = time();
            $status->timemodified = time();
            
            $status->id = $DB->insert_record('local_off_attendance_status', $status);
        }
    }
} 

list($sql_in, $sql_params) = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'roleid');
$sql_params['timedate'] = $unixtime;
$sql_params['courseid'] = $id;
$sql_params['contextid'] = $context->id;
$sql2 = "SELECT loa.*
        FROM {user} ur
        JOIN (
            SELECT userid 
            FROM {role_assignments} 
            WHERE contextid = :contextid AND roleid $sql_in
            GROUP BY userid 
        ) ra ON ra.userid = ur.id
        JOIN {local_off_attendance_status} loa ON ur.id = loa.userid and loa.timedate = :timedate and loa.courseid = :courseid";

$users = $DB->get_records_sql($sql2, $sql_params);

$count = 0;
foreach($users as $user) {
    $oldstatus = $user->status;
    $user->status = $value;
    $user->lastcode = $section->code;
    $user->timemodified = time();
    $DB->update_record('local_off_attendance_status', $user);
    $count++;
    
    local_offline_attendance_log($user->id, $USER->id, $oldstatus, $value);
}

$returnvalue = new stdClass();
$returnvalue->status = 'success';
$returnvalue->count = $count;
$returnvalue->status = get_string('manage:status'.$value, 'local_offline_attendance');
$returnvalue->text = get_string('manage:alert3', 'local_offline_attendance', $returnvalue);

@header('Content-type: application/json; charset=utf-8');
echo json_encode($returnvalue);