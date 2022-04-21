<?php

require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/offline_attendance/lib.php';

global $USER;

$id       = required_param('id', PARAM_INT);    // course id
$code = required_param('code', PARAM_NUMBER); // 인증번호

$sql_select = ' SELECT * FROM {local_off_attendance_section} ';
$sql_where = ' WHERE courseid = :courseid and timestart <= :time1 and timeend >= :time2 ';
$params['courseid'] = $id;
$params['time1'] = time();
$params['time2'] = time();
$current_section = $DB->get_record_sql($sql_select.$sql_where, $params);

$returnvalue = new stdClass();

if($current_section->code == $code) {
    $count = $DB->count_records('local_off_attendance_section', array('timedate' => $current_section->timedate, 'courseid' => $id));
    
    $oldstatus = $DB->get_record('local_off_attendance_status', array('courseid'=>$id, 'userid'=>$USER->id, 'timedate'=> $current_section->timedate));
    
    if($count > 1) {
        $newstatus = new stdClass();
        $newstatus->id = $oldstatus->id;
        $newstatus->lastcode = $code;
        
        //이전 시간 출석 체크가 결석 이었을 경우 : 지각처리
        if($oldstatus->status == 2) {
            $newstatus->status = 3;     //지각처리
        } else {
            $newstatus->status = $oldstatus->status;     // 그 외 기존 상태 유지
        }
        
        $newstatus->timemodified = time();     
        $DB->update_record('local_off_attendance_status', $newstatus);
    
     //첫번째 출석 체크 일 경우 : 코드생성 시 기본값(결석) -> 출석
    } else if($count == 1){
        $newstatus = new stdClass();
        $newstatus->id = $oldstatus->id;
        $newstatus->status = 1;     //출석처리
        $newstatus->lastcode = $code;     
        $newstatus->timemodified = time();     
        $DB->update_record('local_off_attendance_status', $newstatus);
        
        
    } 
    
    local_offline_attendance_log($newstatus->id, $USER->id, $oldstatus->status, $newstatus->status);
    $returnvalue->status = 'success';
    $returnvalue->text = get_string('check:alert1', 'local_offline_attendance', $returnvalue);
    
} else {
    $returnvalue->status = 'fail';
    $returnvalue->text = get_string('check:alert2', 'local_offline_attendance', $returnvalue); // 인증번호 맞지 않음
}


@header('Content-type: application/json; charset=utf-8');
echo json_encode($returnvalue);