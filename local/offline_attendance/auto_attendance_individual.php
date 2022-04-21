<?php

require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/offline_attendance/lib.php';

$id       = required_param('id', PARAM_INT);    // course id
$unixtime = required_param('unixtime', PARAM_NUMBER); // unixtime
$status = required_param_array('status', PARAM_RAW);

$context = context_course::instance($id);
$PAGE->set_context($context);

// section을 만들어 줌
$query = 'SELECT * FROM {local_off_attendance_section} WHERE courseid = :courseid and timedate = :timedate ORDER BY timeend DESC ';
$section = $DB->get_record_sql($query, array('courseid'=>$id, 'timedate'=>$unixtime));
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
    
// status 테이블에 값이 없을 경우(unixtime : 날짜 값이 최초인 경우) status 테이블에 status 필드 0 값으로 초기화 값 넣어줌
    $sql_select = "SELECT  ur.*,
                           loa.status,
                           CASE WHEN loa.id IS NULL THEN 0 ELSE loa.id END AS statusid ";
    $sql_from = " FROM {user} ur
                  JOIN (
                    SELECT userid 
                    FROM {role_assignments} 
                    WHERE contextid = :contextid 
                    GROUP BY userid 
                    ) ra ON ra.userid = ur.id
                  LEFT JOIN {local_off_attendance_status} loa ON ur.id = loa.userid and loa.timedate = :timedate and loa.courseid = :courseid ";

    $sql_conditions = array('ur.deleted = :deleted');
    
    $roleobjs = $DB->get_records('role', array('archetype' => 'student'));
    $roles = array_keys($roleobjs);
    list($sql_in, $sql_params) = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'roleid');
    
    $sql_params['contextid'] = $context->id;
    $sql_params['timedate'] = $unixtime;
    $sql_params['courseid'] = $id;
    $sql_params['deleted'] = 0;
    $sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
    $sql_orderby = ' ORDER BY ur.firstname, lastname ASC ';

    $users = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $sql_params);
    
    foreach($users as $user){
        if(is_null($user->status) && !is_siteadmin($user->id)) {
            $st = new stdClass();
            $st->courseid = $id;
            $st->userid = $user->id;
            $st->status = 2;
            $st->lastcode = $section->code;
            $st->timedate = $unixtime;
            $st->timecreated = time();
            $st->timemodified = time();
            $st->id = $DB->insert_record('local_off_attendance_status', $st);
                                          
           local_offline_attendance_log($user->statusid, $USER->id, 0, 2);
        }
    }
} 

    
$userids = array();
$user_status = array();
foreach($status as $userdata) {
   list($userid, $st) = explode('/', $userdata);
   $user_status[$userid] = $st;  
   $userids[$userid] = $userid;
}

list($sql, $params) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'userid');
$sql_select = ' SELECT * FROM {local_off_attendance_status} ';
$sql_where = ' WHERE userid '. $sql . ' and courseid = :courseid and timedate = :timedate';
$params['courseid'] = $id;
$params['timedate'] = $unixtime;
$users = $DB->get_records_sql($sql_select.$sql_where, $params);

$count = 0;
foreach($users as $user) {
    $oldstatus = $user->status;
    if($oldstatus != $user_status[$user->userid]) {
        $user->status = $user_status[$user->userid];
        $user->code = $section->code;
        $user->timemodified = time();
        $DB->update_record('local_off_attendance_status', $user);
        
        local_offline_attendance_log($user->id, $USER->id, $oldstatus, $user->status);
        
        $count++;
    }
}

$returnvalue = new stdClass();
$returnvalue->status = 'success';
$returnvalue->count = $count;
$returnvalue->text = get_string('manage:alert5', 'local_offline_attendance', $returnvalue);

@header('Content-type: application/json; charset=utf-8');
echo json_encode($returnvalue);