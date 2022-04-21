<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot.'/lib/filelib.php';
require_once $CFG->dirroot.'/lib/pdflib.php';
require_once $CFG->dirroot.'/local/certi/fpdi/fpdi.php';

setlocale(LC_ALL, "en_US.UTF-8");
//$id = required_param('id', PARAM_INT); 
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/local/okirregular/certi_preview.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

//$cert = $DB->get_record('lmsdata_certificate', array('id'=>$id), '*', MUST_EXIST);

require_once 'certi_sample.php';

$vars = new stdClass();
$vars->background = 'back.jpg';
$vars->dojang = 'snut.png';
$vars->dojang1 = 'future.jpg';
$vars->logo = 'schoollogo.jpg';
$vars->seal = 'seal.jpg';
//$vars->dojang = '<img src="' . $CFG->wwwroot . '/local/okirregular/pix/snut.png">';
//$vars->documentid = $cert->prefix.'-'.date('Ymd').$USER->id;
$vars->documentid = $cert->prefix.date('y').'-'.'교과목코드'.'-'.'001';
//$vars->cname = $cert->name;
$vars->cname = '수 료 증 서';
$vars->name = fullname($USER);
$vars->birthday = '0000년 00월 00일생';
$vars->coursename = '0000학년도 00학기(course name)';
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
//$vars = new stdClass();
//$vars->background = $cert->id.'/'.$cert->background;
//$vars->dojang = $cert->id.'/'.$cert->dojang;
//$vars->documentid = $cert->prefix.'-'.date('Ymd').$USER->id;
//$vars->cname = $cert->name;
//$vars->name = fullname($USER);
//$vars_ko->birthday = '1999.10.12';
//$vars->coursename = 'Course Name Sample';
//$vars->issuedate =  date('Y년m월d일') ;
//$vars->issuedate2 =  date('Y년m월d일') ;
//$vars->author = $cert->author;
//$vars->author1 = $cert->author1;
//$vars->author2 = $cert->author2;
//$vars->author3 = $cert->author3;
//$vars->description = $cert->description;

//$pdf = new completionPDF($CFG->dirroot.'/siteadmin/manage/certi_imgs');
$pdf = new completionPDF($CFG->dirroot.'/local/okirregular/certi_img');
$pdf->Write($vars);
$pdf->Output('cert.pdf', 'I');


