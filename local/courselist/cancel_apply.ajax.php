<?php

/*************return type************************
 *  loginpage   - 로긴이 되어있지 않음
 *  geust       - 게스트 유저임
 *  enrolled    - 이미 등록되어있는 강의
 *  progress    - 청강 진행중인 상태(이미 청강 신청한 강의)
 *  success     - 청강 신청이 정상적으로 완료 됨
*/

define('AJAX_SCRIPT', true);

require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once($CFG->dirroot."/enrol/apply/lib.php");
require_once($CFG->dirroot."/lib/accesslib.php");


$id = required_param('id', PARAM_INT); // course->id

$retrunval = new stdClass();
 
if (!confirm_sesskey()) {
    $retrunval->status = 'failed';
    $retrunval->message = 'sesskey' + get_string('sesskey', 'error');
    
    echo $OUTPUT->header();
    echo json_encode($retrunval);
    
    die();
}

if (!isloggedin()) {
    $retrunval->status = 'failed';
    $retrunval->message = 'a ' + get_string("sititon:loginpage", "local_courselist");
} else if(isguestuser()) {
    $retrunval->status = 'failed';
    $retrunval->message = 'b ' + get_string("sititon:guest", "local_courselist");
} else {
    $context = context_course::instance($id, MUST_EXIST);
    $ra = $DB->get_record('role_assignments', array('contextid'=>$context->id, 'userid'=>$USER->id));
    //대형 수정
    //$ra = $DB->get_record('role_assignments', array('contextid'=>$context->id, 'userid'=>$USER->id, 'component'=>'enrol_apply'));
    if($ra) {
        $DB->delete_records('role_assignments',array('id'=>$ra->id));
    }
    
    
    $ue = $DB->get_record_sql("SELECT ue.*
FROM {user_enrolments} ue
JOIN {enrol} en ON en.id = ue.enrolid
WHERE ue.status = 1
  AND ue.userid = :userid
  AND en.courseid = :courseid
  AND en.enrol = 'apply'", array('userid'=>$USER->id, 'courseid'=>$id));
  
    

    if($ue) {
        $info = getRelatedInfo($ue->id);
        if($DB->delete_records('user_enrolments',array('id'=>$ue->id))){
          sendCancelMail($info);
	    }

        $retrunval->status = 'success';
        $retrunval->message = get_string('completedrequestcancel', 'local_courselist');
    } else {
        $retrunval->status = 'failed';
        $retrunval->message = 'c ' + get_string('couldnotfindenrolment', 'local_courselist');
    }
}

echo $OUTPUT->header();
echo json_encode($retrunval);

die();