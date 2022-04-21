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

$courseid = required_param('id', PARAM_INT); // course->id
$type = required_param('type', PARAM_INT);
$password = optional_param('pass', '', PARAM_RAW);

if($type == 1){
    $status = required_param('status', PARAM_INT);
}
$enrol = $DB->get_record('enrol', array('enrol'=>'self', 'courseid'=>$courseid));

$retrunval = new stdClass();
    
@header('Content-type: application/json; charset=utf-8');
if ($type == 1 && $status == 1 && $enrol->password != $password){
    $err_msg = get_string('wrong_password','local_oklearning');
}else{
    if($status == 2){
        $status = 1;
    }else{
        $status = 0;
    }
    
}
if (!empty($err_msg)) {
    $returnvalue = new stdClass();
    $returnvalue->status = 'error';
    $returnvalue->message = $err_msg;

    echo json_encode($returnvalue);
    die;
}
if (!isloggedin()) {
    $retrunval->status = 'failed';
    $retrunval->message = get_string("sititon:loginpage", "local_oklearning");
} else if(isguestuser()) {
    $retrunval->status = 'failed';
    $retrunval->message = get_string("sititon:guest", "local_oklearning");
} else {
    if($type==1){
        $result = local_oklearning_set_assign_user($courseid, $USER->id, $status, 'student');
        if($result==true){
            if(trim($password)){
                $retrunval->pass = true;
            }
            $retrunval->status = 'success';
            $retrunval->message = get_string("completedrequestenrol", "local_oklearning");
        }else{
            $retrunval->status = 'failed';
            $retrunval->message = get_string("couldnotrequestenrol", "local_oklearning");
        }
    }else{
        $result = local_oklearning_set_unassign_user($courseid, $USER->id);
        if($result==true){
            $retrunval->status = 'success';
            $retrunval->message = get_string("completedrequestcancel", "local_oklearning");
        }else{
            $retrunval->status = 'failed';
            $retrunval->message = get_string("couldnotrequestenrol", "local_oklearning");
        }
    }
}


echo json_encode($retrunval);