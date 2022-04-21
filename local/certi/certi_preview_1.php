<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot.'/lib/filelib.php';
require_once $CFG->dirroot.'/lib/pdflib.php';
require_once $CFG->dirroot.'/local/certi/fpdi/fpdi.php';

setlocale(LC_ALL, "en_US.UTF-8");
$id = required_param('id', PARAM_INT); 
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/local/certi/certi_preview.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$cert = $DB->get_record('lmsdata_certificate', array('id'=>$id), '*', MUST_EXIST);

require_once $cert->lang == 'ko' ? 'certi_sample.php':'certi_sample_en.php';

$vars = new stdClass();
$vars->background = $cert->id.'/'.$cert->background;
$vars->dojang = $cert->id.'/'.$cert->dojang;
$vars->documentid = $cert->prefix.'-'.date('Ymd').$USER->id;
$vars->cname = $cert->name;
$vars->name = fullname($USER);
//$vars_ko->birthday = '1999.10.12';
$vars->coursename = 'Course Name Sample';
$vars->issuedate = ($cert->lang == 'ko') ? date('Y-m-d',time()) : date('l jS \of F Y',time());
$vars->author = $cert->author;
$vars->description = $cert->description;

$pdf = new completionPDF($CFG->dirroot.'/siteadmin/manage/certi_imgs');
$pdf->Write($vars);
$pdf->Output('cert.pdf', 'I');


