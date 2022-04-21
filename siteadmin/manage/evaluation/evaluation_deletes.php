<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib/paging.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib.php';

// Check for valid admin user - no guest autologin

require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/manage/evaluation/evaluation_add.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$target = required_param('target', PARAM_RAW);
$instanceid = required_param('instanceid', PARAM_INT);

switch($target){
    case 'evaluation':
        // 어떤식으로 진행할지 <?php echo get_string('okay','local_lmsdata');
        break;
    case 'form';
        $DB->delete_records('lmsdata_evaluation_questions',array('formid'=>$instanceid));
        $DB->delete_records('lmsdata_evaluation_category',array('formid'=>$instanceid));
        $DB->delete_records('lmsdata_evaluation',array('formid'=>$instanceid));
        $DB->delete_records('lmsdata_evaluation_forms',array('id'=>$instanceid));
        redirect('./evaluation_form.php');
    case 'category':
        $category = $DB->get_record('lmsdata_evaluation_category',array('id'=>$instanceid));
        $DB->delete_records('lmsdata_evaluation_category',array('id'=>$instanceid));
        $DB->delete_records('lmsdata_evaluation_questions',array('category'=>$instanceid));
        redirect('./evaluation_categories.php?formid='.$category->formid);
        break;
    case 'question':
        $question = $DB->get_record('lmsdata_evaluation_questions',array('id'=>$instanceid));
        $DB->delete_records('lmsdata_evaluation_questions',array('id'=>$instanceid));
        redirect('./evaluation_categories.php?formid='.$question->formid);
        break;
}

?>