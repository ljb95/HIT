<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot.'/lib/filelib.php';
require_once $CFG->dirroot.'/lib/pdflib.php';
require_once $CFG->dirroot.'/local/certi/fpdi/fpdi.php';

setlocale(LC_ALL, "en_US.UTF-8");
$id = optional_param('id',0 ,PARAM_INT); 
$mod = optional_param('mod','',PARAM_RAW);
$class =optional_param('class',0,PARAM_INT);
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/local/certi/certi_preview.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);


if($class == 0 && $mod == 'adminpreview'){
    $cert = $DB->get_record('lmsdata_certificate', array('id'=>$id), '*', MUST_EXIST);
}else{
    $course = $DB->get_record('lmsdata_class',array('course'=>$class));
    $int = $course->certificateid;
    $cert = $DB->get_record('lmsdata_certificate', array('id'=>$int));
    
    $certihistory = $DB->get_record('lmsdata_certificate_history',array('courseid'=>$course->course,'certiid'=>$int,'userid'=>$USER->id));
    $cernumsql = "select count(id) from {lmsdata_certificate_history} where courseid = :courseid and certiid = :certiid and userid <> :userid";
    $certinum = $DB->count_records_sql($cernumsql,array('courseid'=>$course->course, 'certiid'=>$int, 'userid'=>$USER->id));
    $cercount = $certinum+1;
    
    $data = new stdClass();
if($certihistory){
    $data->id = $certihistory->id;
    $data->issuecnt = $certihistory->issuecnt + 1;
    $data->timemodified = time();
    $data->certinum = $cercount;
    $DB->update_record('lmsdata_certificate_history',$data);
}else{
    $data->courseid = $course->course;
    $data->userid = $USER->id;
    $data->certiid = $int;
    $data->issuecnt = 1;
    $data->certinum = $cercount;
    $data->lang = 'ko';
    $data->timecreated = time();
    $data->timemodified = time();
    $DB->insert_record('lmsdata_certificate_history',$data);
}
    
    
}



$usersql = "select u.id,u.username,lu.psosok,lu.usergroup from {user} u "
        . "join {lmsdata_user} lu on u.id = lu.userid and u.id = :userid";
$user = $DB->get_record_sql($usersql,array('userid'=>$USER->id));


$coursename = ($cert->lang == 'ko') ? $course->kor_lec_name : $course->eng_lec_name;

require_once $cert->lang == 'ko' ? 'certi_sample.php':'certi_sample_en.php';

$vars = new stdClass();//????????? ?????? sample ????????? ?????? ?????????

if($user->usergroup=='rs'){//????????????
    $vars->desc1 = '???      ??? : ';
    $vars->desc2 = '???????????? : ';
    $vars->desc3 = $user->username;
    $vars->desc4 = $coursename;
    
}else{//??????
    $vars->desc1 = '???????????? : ';
    $vars->desc2 = '???????????? : ';
    $vars->desc3 = $coursename;
    if($course->learningtime == '' || $course->learningtime == null || $course->learningtime == 0){
        $vars->desc4 = '?????? ?????? ??????';
    }else{
    $vars->desc4 = $course->learningtime.' ??????';
    }
}
$vars->coursename = $coursename;
$vars->background = $cert->id.'/'.$cert->background;
$vars->dojang = $cert->id.'/'.$cert->dojang;
$vars->documentid = $cert->prefix.'-'.date('Ymd').$USER->id;
$vars->cname = $cert->name;
$vars->name = fullname($USER);
$vars->sosok = $user->psosok;
//$vars_ko->birthday = '1999.10.12';
//$vars->coursename = 'Course Name Sample';
$vars->issuedate = ($cert->lang == 'ko') ? date('Y???   m???  d???',time()) : date('l jS \of F Y',time());
$vars->author = $cert->author;
$vars->description = '??? ????????? ??????????????????????????? ???????????? "'.$coursename.'" ??? ????????? ??? ??????????????? ?????????????????? ??? ????????? ????????????.';

$pdf = new completionPDF($CFG->dirroot.'/siteadmin/manage/certi_imgs');
$pdf->Write($vars);
$pdf->Output('cert.pdf', 'I');
 

