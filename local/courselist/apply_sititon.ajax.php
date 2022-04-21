<?php

/*************return type************************
 *  loginpage   - 로긴이 되어있지 않음
 *  geust       - 게스트 유저임
 *  enrolled    - 이미 등록되어있는 강의
 *  progress    - 청강 진행중인 상태(이미 청강 신청한 강의)
 *  success     - 청강 신청이 정상적으로 완료 됨
*/


require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once($CFG->dirroot."/enrol/apply/lib.php");

$courseids = required_param_array('id', PARAM_INT); // course->id

$role = $DB->get_record('role', array('shortname'=>'auditor'));

$retrunval = new stdClass();
 
if (!isloggedin()) {
    $retrunval->status = 'failed';
    $retrunval->message = get_string("sititon:loginpage", "local_courselist");
} else if(isguestuser()) {
    $retrunval->status = 'failed';
    $retrunval->message = get_string("sititon:guest", "local_courselist");
} else {
    $enrol_apply = new enrol_apply_plugin();
    $msg = array(
        get_string('completedrequestaudit', 'local_courselist'), ""
    );
    foreach($courseids as $courseid) {
        $enrolinstance = $DB->get_record('enrol', array('courseid'=>$courseid, 'status'=>ENROL_INSTANCE_ENABLED, 'enrol' => 'apply'));

        $context = context_course::instance($courseid, MUST_EXIST);
        $course  = $DB->get_record('course', array('id'=>$courseid));

        if (is_enrolled($context, $USER, '', true)) {
            $msg[] =  $course->fullname.': '.get_string("sititon:enrolled", "local_courselist");
        } else if ($DB->record_exists('user_enrolments', array('userid'=>$USER->id, 'enrolid'=>$enrolinstance->id))) {
            $msg[] =  $course->fullname.': '.get_string("sititon:progress", "local_courselist");
        } else {
            $timestart = $course->startdate;
            if ($enrolinstance->enrolperiod) {
                    $timeend = $timestart + $enrolinstance->enrolperiod;
            } else {
                    $timeend = 0;
            }

            $enrol_apply->enrol_user($enrolinstance, $USER->id, $role->id, $timestart, $timeend, 1);
            sendConfirmMailToTeachers($enrolinstance->courseid, $enrolinstance->id, '');
            sendConfirmMailToManagers($enrolinstance->courseid,'');

            $msg[] =  $course->fullname.': '.get_string("sititon:success", "local_courselist");
        }
    }
    
    $retrunval->status = 'success';
    $retrunval->message = implode("\n", $msg);
}

@header('Content-type: application/json; charset=utf-8');
echo json_encode($retrunval);