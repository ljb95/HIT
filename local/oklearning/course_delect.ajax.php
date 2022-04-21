<?php

/*************return type************************
 *  loginpage   - 로긴이 되어있지 않음
 *  geust       - 게스트 유저임
 *  enrolled    - 이미 등록되어있는 강의
 *  progress    - 청강 진행중인 상태(이미 청강 신청한 강의)
 *  success     - 청강 신청이 정상적으로 완료 됨
*/


require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once $CFG->dirroot . '/local/oklearning/lib.php';

$enrolid = required_param('enrolid', PARAM_INT); // course->id
$status = required_param('status', PARAM_INT); // 삭제, 엔롤 status
$courseid = optional_param('courseid', 0, PARAM_INT); // 삭제, 엔롤 status

$retrunval = new stdClass();
 
if (!isloggedin()) {
    $retrunval->status = 'failed';
    $retrunval->message = get_string("sititon:loginpage", "local_oklearning");
} else if(isguestuser()) {
    $retrunval->status = 'failed';
    $retrunval->message = get_string("sititon:guest", "local_oklearning");
} else if($status == 2) {
        $result = delete_enrol_user($enrolid, $USER->id);
        if($result==true){
            $retrunval->status = 'success';
            $retrunval->message = get_string("completedrequestenrol", "local_oklearning");
        }else{
            $retrunval->status = 'failed';
            $retrunval->message = get_string("couldnotrequestenrol", "local_oklearning");
        }
}else if($status == 1){
    if($courseid){
        $result = delete_course($courseid, FALSE);
        $retrunval->status = 'success';        
    }else{
        $retrunval->status = 'failed';
        $retrunval->message = get_string("couldnotrequestenrol", "local_oklearning");
    }     
}

@header('Content-type: application/json; charset=utf-8');
echo json_encode($retrunval);