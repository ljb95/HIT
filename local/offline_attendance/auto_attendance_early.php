<?php

require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/offline_attendance/lib.php';

$id       = required_param('id', PARAM_INT);    // course id
$unixtime = required_param('unixtime', PARAM_NUMBER); // unixtime
$timeend = required_param('timeend', PARAM_NUMBER); // timeend

$count = $DB->count_records('local_off_attendance_section', array('courseid' => $id, 'timedate' => $unixtime));

// 1교시가 아닐 경우
if($count >= 2) {
    $code = $DB->get_field('local_off_attendance_section','code', array('courseid' =>$id, 'timedate'=>$unixtime, 'timeend'=>$timeend));
    
    //기존 출석상태이며, 방금 끝난 출석체크를 하지 않은 사용자 
    $param = array(
                'courseid' => $id, 
                'timedate' => $unixtime, 
                'code' => $code, 
                'status' => 1
            );
    $sql_select = ' SELECT * FROM {local_off_attendance_status} ';
    $sql_where  = ' WHERE courseid = :courseid AND timedate = :timedate AND lastcode <> :code AND status = :status ';
    $users = $DB->get_records_sql($sql_select.$sql_where, $param);
    
    //조퇴 처리
    foreach($users as $user) {
        $user->status = 4;
        $user->lastcode = $code;
        $user->timemodified = time();
        $DB->update_record('local_off_attendance_status', $user);
        
        local_offline_attendance_log($user->id, 0, 1);
    }
}

$returnvalue = new stdClass();
$returnvalue->status = 'success';

@header('Content-type: application/json; charset=utf-8');
echo json_encode($returnvalue);