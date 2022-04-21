<?php

/*************return type************************
 *  loginpage   - 로긴이 되어있지 않음
 *  geust       - 게스트 유저임
 *  enrolled    - 이미 등록되어있는 강의
 *  progress    - 청강 진행중인 상태(이미 청강 신청한 강의)
 *  success     - 청강 신청이 정상적으로 완료 됨
*/


require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once $CFG->dirroot . '/local/okirregular/lib.php';

$courseid = required_param('id', PARAM_INT); // course->id
$type = required_param('type', PARAM_INT);

$retrunval = new stdClass();
 
if (!isloggedin()) {
    $retrunval->status = 'failed';
    $retrunval->message = get_string("sititon:loginpage", "local_okirregular");
} else if(isguestuser()) {
    $retrunval->status = 'failed';
    $retrunval->message = get_string("sititon:guest", "local_okirregular");
} else {
    if($type==1){
        $result = local_okirregular_set_assign_user($courseid, $USER->id, 'student');
        if($result==true){
            $retrunval->status = 'success';
            $retrunval->message = get_string("completedrequestenrol", "local_okirregular");
        }else{
            $retrunval->status = 'failed';
            $retrunval->message = get_string("couldnotrequestenrol", "local_okirregular");
        }
    }else{
        $result = local_okirregular_set_unassign_user($courseid, $USER->id);
        if($result==true){
            $retrunval->status = 'success';
            $retrunval->message = get_string("completedrequestcancel", "local_okirregular");
        }else{
            $retrunval->status = 'failed';
            $retrunval->message = get_string("couldnotrequestenrol", "local_okirregular");
        }
    }
}

@header('Content-type: application/json; charset=utf-8');
echo json_encode($retrunval);