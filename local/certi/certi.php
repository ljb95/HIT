<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot.'/lib/filelib.php';
require_once $CFG->dirroot.'/lib/pdflib.php';
require_once $CFG->dirroot.'/local/certi/fpdi/fpdi.php';

setlocale(LC_ALL, "en_US.UTF-8");
$id = required_param('id', PARAM_INT); 
$certiform = required_param('certiform',PARAM_INT); 

$context = context_system::instance();

require_login();

//강의명 정보 가져오기
$course = $DB->get_record('lmsdata_class',array('course'=>$id));
if(!$course){
    print_error(get_string('certi:alert01','local_certi'));
    die();
}

//이수증 양식 가져오기
$cert = $DB->get_record('lmsdata_certificate',array('id'=>$certiform));
if(!$cert){
    print_error(get_string('certi:alert02','local_certi'));
    die();
}

//이수를 하였는지 확인
$timecompleted = $DB->get_field('course_completions','timecompleted',array('course'=>$id, 'userid'=>$USER->id));
if(!$timecompleted || $timecompleted == 0){
    print_error(get_string('certi:alert03','local_certi'));
    die();
}

//이수이력 확인
$history = $DB->get_record('lmsdata_certificate_history',array('certiid'=>$cert->id,'courseid'=>$course->course, 'userid'=>$USER->id, 'lang'=>$cert->lang));
$lmsdata_class = $DB->get_record('lmsdata_class',array('course'=>$id));
//print_object($history);die();

require_once $cert->lang == 'ko' ? 'certi_sample.php':'certi_sample_en.php';

//$vars = new stdClass();
//$vars->background = $cert->id.'/'.$cert->background;
//$vars->dojang = $cert->id.'/'.$cert->dojang;
//$vars->documentid = (!$history) ? $cert->prefix.'-'.date('Ymd').$USER->id.$course->course : $history->certinum;
//$vars->cname = $cert->name;
//$vars->name = fullname($USER);
////$vars->birthday = '1999.10.12';
//$vars->coursename = ($cert->lang == 'ko') ? $course->kor_lec_name : $course->eng_lec_name;
//$vars->issuedate = ($cert->lang == 'ko') ? date('Y-m-d',$timecompleted) : date('l jS \of F Y',$timecompleted);
//$vars->author = $cert->author;
//$vars->description = $cert->description;
$vars = new stdClass();
$vars->background = 'back.jpg';
$vars->dojang = 'snut.png';
$vars->dojang1 = 'future.jpg';
$vars->logo = 'schoollogo.jpg';
$vars->seal = 'seal.jpg';
//$vars->dojang = '<img src="' . $CFG->wwwroot . '/local/okirregular/pix/snut.png">';
//$vars->documentid = $cert->prefix.'-'.date('Ymd').$USER->id;
$vars->documentid = date('y').'-'.$course->subject_id.'-'.str_pad($USER->id,3,'0',STR_PAD_LEFT);
//$vars->cname = $cert->name;
$vars->cname = '수 료 증 서';
$vars->name = fullname($USER);
//$vars->birthday = '0000년 00월 00일생';
$timestamp = mktime('0', '0', '0', $birthday2[1], $birthday2[2], $birthday2[0]);
////$birthday = date('Y.n.j', $timestamp);
$vars->birthday = date('Y년 n월 j일생', $timestamp);
$vars->coursename = date(Y).'학년도'.'('.$course->kor_lec_name.')';
$vars->issuedate =  date('Y년m월d일') ;
//$vars->issuedate2 =  date('Y년m월d일') ;
//$vars->author = $cert->author;
$vars->author1 = '서울과학기술대학교 미래융합대학장 ';
$vars->author2 = '공학박사        김    성    곤  ';
$vars->author3 = '위의 인정에 의하여 증서를 수여함.';
$vars->author = '서 울 과 학 기 술 대 학 교';
$vars->author4 = '공학박사        김    종    호  ';
$vars->description = '위 사람은 서울과학기술대학교 미래융합대학에서';
$vars->description1 = '실시한 과정을 이수하였음을 인정함';

if($histroy){
    $history->issuecnt = $history->issuecnt + 1;
    $history->timemodified = time();
    $DB->update_record('lmsdata_certificate_history',$history);    
}else{
    $history = new stdClass();
    $history->courseid = $course->course;
    $history->certiid = $cert->id;
    $history->userid = $USER->id;
    $history->lang = $cert->lang;
    $history->certinum = $vars->documentid;
    $history->issuecnt = 0;
    $history->timecreated = time();
    $DB->insert_record('lmsdata_certificate_history',$history);
}

$pdf = new completionPDF($CFG->dirroot.'/local/certi/certi_img');
$pdf->Write($vars);
$pdf->Output('cert.pdf', 'I');


