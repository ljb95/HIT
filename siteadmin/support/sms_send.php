<?php

// Written at Louisiana State University

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

 $CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1;
error_reporting($CFG->debug);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

require_once($CFG->dirroot . '/enrol/externallib.php');
require_once($CFG->dirroot . '/siteadmin/support/smslib.php');
require_once($CFG->dirroot . '/siteadmin/support/smsconfig.php');

//require_once 'send_form.php';
require_once $CFG->dirroot . '/course/report/statistics/lib.php';
require_once $CFG->dirroot . '/lib/formslib.php';
require_once $CFG->dirroot . '/lib/form/filemanager.php';
require_once($CFG->dirroot . '/siteadmin/support/smsconfig.php');
require_once $CFG->dirroot . '/local/attendance/lib.php';

global $CFG, $DB;

//require_login();
$smssend = new stdClass();
$smssend->contents = optional_param('contents', '', PARAM_TEXT);
//$sendd = optional_param('sendd', '', PARAM_ALPHANUMEXT);
//$sendh = optional_param('sendh', 0, PARAM_ALPHANUM);
//$sendm = optional_param('sendm', 0, PARAM_ALPHANUM);
$now_send = optional_param('now_send', 1, PARAM_INT);
$sphone = optional_param('sphone', '', PARAM_RAW);
$mailto_type = optional_param('mailto_type', 1, PARAM_INT);
$smssend->subject = optional_param('subject', '', PARAM_RAW);
$mailto = optional_param('mailto', '', PARAM_RAW);

//시간처리
//$sendds = explode('-', $sendd);
//$sy = $sendds[0];
//$sm = $sendds[1];
//$sd = $sendds[2];

if ($now_send) {
    $smssend->sendtime = time();
    $smssend->schedule_type = 0;
} else {
    //$smssend->sendtime = mktime($sendh, $sendm, 0, $sm, $sd, $sy);
    //$smssend->schedule_type = 1;
}

$smssend->sender = optional_param('fullname', '', PARAM_RAW);
$smssend->userid = $USER->id;
$smssend->callback = str_replace("-", "", $sphone);
$smssend->timecreated = time();
/*
$msg_no = set_smssend($smssend);
if(!$msg_no){
    echo 'Set Sms Not Working';
    die();
}
 * 
 */
$newemail = $DB->insert_record('lmsdata_sms', $smssend);

//전체 대상일 경우와 지정일 경우 구분하여 발송자 리스트 처리
if ($mailto_type == 2) {
        //전체발송
        //전체 회원데이터를 얻는다.
        $query = 'select u.* from {user} u
            JOIN {lmsdata_user} ui on ui.userid = u.id and u.id !=1 and u.id !=2 and u.id !=3 and u.deleted != 1';
        
        //$user_arr = $DB->get_records_sql("select * from m_user where id In ($temp_users) and id !=1 and id !=2 and id !=3 and deleted != 1");
        
        $users = $DB->get_records_sql($query);
        $count = 0;
        push_sms_api($users, $smssend->subject, preg_replace('/\r\n|\r|\n/', '', html_to_text($smssend->contents)), 1);
        foreach ($users as $user) {
             $user->phone2 = trim(str_replace("-", "", $user->phone2));
            if(preg_match('/^[0-9]{10,11}$/', trim($user->phone2))){
                $smssend_user->sms = $newemail;
                $smssend_user->userid = $user->id;
                $smssend_user->phone = $user->phone2;
                $smssend_user->fullname = fullname($user);
                $smssend_user->timecreated = time();
                //send_sms($user, $smssend,$msg_no);
                $DB->insert_record('lmsdata_sms_data', $smssend_user);

                $count++;
            }
        }
} else {
    //발송대상 목록을 분해한다. 
    if ($mailto) {

        $maillists = json_decode($mailto, true);

        $count = 0;
        foreach ($maillists as $key => $mails) {

            $mail = explode(';', $mails);

            if ($mail[0] == 'user') {

                $query = 'select u.* from {user} u
                   JOIN {lmsdata_user} ui on ui.userid = u.id
                   where u.id=:id';
                $user = $DB->get_record_sql($query, array('id' => $mail[1]));
                $rcount = $DB->count_records_sql('select count(*) from {lmsdata_sms_data} where sms=:sms and userid=:userid', array('sms' => $newemail, 'userid' => $mail[1]));
                $user_arr = $DB->get_records_sql($query, array('id' => $mail[1]));
                //$user_arr = $DB->get_records_sql("select * from m_user where id In ($mail[1]) and id !=1 and id !=2 and id !=3 and deleted != 1");
                if ($rcount == 0) {
                    $user->phone2 = trim(str_replace("-", "", $user->phone2));
                    if(preg_match('/^[0-9]{10,11}$/', trim($user->phone2))){

                        $smssend_user = new stdClass();
                        $smssend_user->sms = $newemail;
                        $smssend_user->userid = $user->id;
                        $smssend_user->phone = $user->phone2;
                        $smssend_user->fullname = fullname($user);
                        $smssend_user->timecreated = time();


                        $now = date('Ymdhis', time());
                        $send_date = date('Ymdhis', $send_date);

                        $userinfo = $user->firstname . $user->lastname . '^' . $user->phone2;

                        //send_sms($user, $smssend,$msg_no);
                        push_sms_api($user_arr, $smssend->subject, preg_replace('/\r\n|\r|\n/', '', html_to_text($smssend->contents)), 1);
                        $DB->insert_record('lmsdata_sms_data', $smssend_user);
                        $count++;
                    }
                }
            } else if ($mail[0] == 'role') {

                if ($mail[1] == 'admin') {
                    $roles = $DB->get_records('role_assignments', array('roleid' => 1));
                } else if ($mail[1] == 'manager') {
                    $roles = $DB->get_records('role_assignments', array('roleid' => 2));
                } else if ($mail[1] == 'teacher') {
                    $roles = $DB->get_records('role_assignments', array('roleid' => 3));
                } else if ($mail[1] == 'student') {
                    $roles = $DB->get_records('role_assignments', array('roleid' => 5));
                }
                

                foreach ($roles as $role) {

                    $rcount = $DB->count_records_sql('select count(*) from {lmsdata_sms_data} where sms=:sms and userid=:userid', array('sms' => $newemail, 'userid' => $role->userid));

                    if ($rcount == 0) {

                        $query = 'select u.* from {user} u
                            JOIN {lmsdata_user} ui on ui.userid = u.id
                            where u.id=:id';
                        $user = $DB->get_record_sql($query, array('id' => $role->userid));
                        //$user_arr = $DB->get_records_sql($query, array('id' => $role->userid));
                        $user_arr = $DB->get_records_sql("select u.* from {user} u JOIN {lmsdata_user} ui on ui.userid = u.id where u.id In($role->userid) and deleted != 1");
                        push_sms_api($user_arr, $smssend->subject, preg_replace('/\r\n|\r|\n/', '', html_to_text($smssend->contents)), $type);
                         $user->phone2 = trim(str_replace("-", "", $user->phone2));
                         if(preg_match('/^[0-9]{10,11}$/', $user->phone2)){


                            $smssend_user->sms = $newemail;
                            $smssend_user->userid = $user->id;
                            $smssend_user->phone = $user->phone2;
                            $smssend_user->fullname = fullname($user);
                            $smssend_user->timecreated = time();
                            
                            //send_sms($user, $smssend,$msg_no);
                            
                            $DB->insert_record('lmsdata_sms_data', $smssend_user);
                            $count++;
                        }
                    }
                }
            } else if ($mail[0] == 'course') {
                //코스에 유저가 있어야 함..
                $params = array('instanceid' => $mail[1], 'contextlevel' => 50);

                $query = 'select mu.* 
                          from {context} mc
                          join {role_assignments} ra on ra.contextid = mc.id
                          join {user} mu on mu.id = ra.userid
                          join {lmsdata_user} lu on lu.userid = mu.id
                          where instanceid = :instanceid and contextlevel = :contextlevel';
                $users = $DB->get_records_sql($query, $params);
                push_sms_api($users, $smssend->subject, preg_replace('/\r\n|\r|\n/', '', html_to_text($smssend->contents)), $type);
                $count = 0;
                foreach ($users as $user) {

                    $rcount = $DB->count_records_sql('select count(*) from {lmsdata_sms_data} where sms=:sms and userid=:userid', array('sms' => $newemail, 'userid' => $user->id));

                    if ($rcount == 0) {
                         $user->phone2 = trim(str_replace("-", "", $user->phone2));
                         if(preg_match('/^[0-9]{10,11}$/', $user->phone2)){

                            $smssend_user->sms = $newemail;
                            $smssend_user->userid = $user->id;
                            $smssend_user->phone = $user->phone2;
                            $smssend_user->fullname = fullname($user);
                            $smssend_user->timecreated = time();

                            //send_sms($user, $smssend,$msg_no);
                            
                            $DB->insert_record('lmsdata_sms_data', $smssend_user);
                            $count++;
                        }
                    }
                }
            }
        }
    }
}

header('Location: sms_state.php?id=' . $newemail);
