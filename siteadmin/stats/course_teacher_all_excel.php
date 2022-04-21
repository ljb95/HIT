<?php 
require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname(dirname (__FILE__)).'/lib.php';

require_once("$CFG->libdir/excellib.class.php");
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/stats/course_teacher_all.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$currpage     = optional_param('page', 1, PARAM_INT);
$perpage      = optional_param('perpage', 20, PARAM_INT);
$year         = optional_param('year', 0, PARAM_INT);
$term         = optional_param('term', 0, PARAM_INT);
$course_name =  optional_param('course_name', '', PARAM_TEXT);
$prof_name =  optional_param('prof_name', '', PARAM_TEXT);
$subject_id =  optional_param('subject_id', 0, PARAM_INT);
$category =  optional_param('category', '', PARAM_TEXT);

// 현재 년도, 학기
if(!$year) {
    $year = get_config('moodle', 'haxa_year'); 
}
if(!$term) {
    $term = get_config('moodle', 'haxa_term');
}

$sql_select  = "SELECT mc.id, mc.fullname, mc.shortname
     , yc.subject_id, yc.isnonformal
     , mu.firstname AS prof_name
     , mu.USERNAME
     , yu.univ
     , yu.major
     , yc.year
     , yc.term
     , yc.ohakkwa as ohakkwa
     , ca.name as category_name
     ,(SELECT COUNT(*) 
       FROM {role_assignments} ra
        JOIN {role} ro ON ra.roleid = ro.id
        JOIN {context} ctx ON ra.contextid = ctx.id
        JOIN {course} co ON ctx.instanceid = co.id AND contextlevel = :contextlevel1
        WHERE co.id = mc.id
        AND ro.id = 3) as editingteacher
     ,(SELECT COUNT(*) 
        FROM {role_assignments} ra
        JOIN {role} ro ON ra.roleid = ro.id
        JOIN {context} ctx ON ra.contextid = ctx.id
        JOIN {course} co ON ctx.instanceid = co.id AND contextlevel = :contextlevel2
        WHERE co.id = mc.id
        AND ro.id = 5) as student
       ,(SELECT COUNT(*) 
        FROM {role_assignments} ra
        JOIN {role} ro ON ra.roleid = ro.id
        JOIN {context} ctx ON ra.contextid = ctx.id
        JOIN {course} co ON ctx.instanceid = co.id AND contextlevel = :contextlevel3
        WHERE co.id = mc.id
        AND ro.id = 4) as teacher
        ,(SELECT COUNT(*) 
        FROM {role_assignments} ra 
        JOIN {role} ro ON ra.roleid = ro.id 
        JOIN {context} ctx ON ra.contextid = ctx.id 
        JOIN {course} co ON ctx.instanceid = co.id AND contextlevel = :contextlevel4 
        WHERE co.id = mc.id 
        AND ro.id = 41) as auditor 
  ,(SELECT count(DISTINCT jc.id)
        from m_jinotechboard_contents jc
        left JOIN m_user u ON u.id = jc.userid
        left JOIN {role_assignments} ra ON ra.USERID = u.id 
        left JOIN {role} ro ON ra.roleid = ro.id 
        WHERE jc.course = mc.id and ro.id=3) as editingteacher_content
   ,(SELECT count(DISTINCT jc.id)
        from m_jinotechboard_contents jc
        left JOIN m_user u ON u.id = jc.userid
        left JOIN {role_assignments} ra ON ra.USERID = u.id 
        left JOIN {role} ro ON ra.roleid = ro.id 
        WHERE jc.course = mc.id and ro.id=4) as teacher_content
   ,(SELECT count(DISTINCT jc.id)
        from m_jinotechboard_contents jc
        left JOIN m_user u ON u.id = jc.userid
        left JOIN {role_assignments} ra ON ra.USERID = u.id 
        left JOIN {role} ro ON ra.roleid = ro.id 
        WHERE jc.course = mc.id and ro.id=5) as student_content
   ,(SELECT count(DISTINCT jc.id)
        from {jinotechboard_contents} jc
        left JOIN {user} u ON u.id = jc.userid
        left JOIN {role_assignments} ra ON ra.USERID = u.id 
        left JOIN {role} ro ON ra.roleid = ro.id 
        WHERE jc.course = mc.id and ro.id=41) as auditor_content "; 
$sql_from    = " FROM {course} mc 
JOIN {lmsdata_class} yc ON yc.course = mc.id 
JOIN {course_categories} ca ON ca.id = mc.category 
LEFT JOIN {user} mu ON mu.id = yc.prof_userid AND mu.deleted = 0
LEFT JOIN {lmsdata_user} yu ON yu.userid = mu.id ";
$sql_where   = " WHERE ((yc.year = :year 
  AND yc.term = :term ) or (yc.year= 9999)) ";
//$sql_orderby = " ORDER BY mc.fullname";

$page_params = array();
$params = array(
    'year'=>$year,
    'term'=>$term,
    'contextlevel1'=>CONTEXT_COURSE,
    'contextlevel2'=>CONTEXT_COURSE,
    'contextlevel3'=>CONTEXT_COURSE,
    'contextlevel4'=>CONTEXT_COURSE
);

if($course_name) {
    $sql_where .= ' AND '.$DB->sql_like('mc.fullname', ':course_name');
    $params['course_name'] = '%'.$course_name.'%';
}
if($subject_id){
    $sql_where .= ' AND '.$DB->sql_like('yc.subject_id', ':subject_id');
    $params['subject_id'] = '%'.$subject_id.'%';
}
if($prof_name){
    $sql_where .= ' AND '.$DB->sql_like('mu.firstname', ':prof_name');
    $params['prof_name'] = '%'.$prof_name.'%';
}
if($category){
    $sql_where .= ' AND '.$DB->sql_like('ca.path', ':category');
    $params['category'] = $category.'%';;
}

//$cata_path = '';
//if($cata3) {
//    $cata_path = $DB->get_field('course_categories', 'path', array('id'=>$cata3));
//} else if($cata2) {
//    $cata_path = $DB->get_field('course_categories', 'path', array('id'=>$cata2));
//} else if($cata1) {
//    $cata_path = $DB->get_field('course_categories', 'path', array('id'=>$cata1));
//}
//if(!empty($cata_path)) {
//    $sql_where .= ' AND '.$DB->sql_like('ca.path', ':category');
//    $params['category'] = $cata_path.'%';;
//}

$courses = $DB->get_records_sql($sql_select.$sql_from.$sql_where, $params);

$fields = array(
    get_string('stats_alternation', 'local_lmsdata'),
    get_string('teachername', 'local_lmsdata'),
    get_string('stats_years', 'local_lmsdata'),
    get_string('stats_terms', 'local_lmsdata'),
    get_string('stats_curriculum', 'local_lmsdata'),
    'LEC_CD',
    get_string('course_code', 'local_lmsdata'),
    get_string('course_name', 'local_lmsdata'),
    get_string('stats_typelecture', 'local_lmsdata'),
    get_string('teacher', 'local_lmsdata'),
    get_string('stats_assistant', 'local_lmsdata'),
    get_string('stats_student', 'local_lmsdata'),
    get_string('stats_auditor', 'local_lmsdata'),
    get_string('stats_assignment', 'local_lmsdata'),
    get_string('stats_board1', 'local_lmsdata'),
    get_string('stats_board2', 'local_lmsdata'),
    get_string('stats_board3', 'local_lmsdata'),
    get_string('stats_board4', 'local_lmsdata'),
    get_string('stats_board5', 'local_lmsdata'),
    get_string('stats_board6', 'local_lmsdata')
);

$filename = get_string('stats_allcourselist', 'local_lmsdata').'.xls';

$workbook = new MoodleExcelWorkbook('-');
$workbook->send($filename);

$worksheet = array();

$worksheet[0] = $workbook->add_worksheet('');
$col = 0;
foreach ($fields as $fieldname) {
    $worksheet[0]->write(0, $col, $fieldname);
    $col++;
}
$row = 1;
foreach($courses as $ap) {
        $submitted_count = lmsdata_get_submission_assign_count($ap);
        $sub_exname = explode("-", $ap->subject_id);
        $sub_name = "";
        if(!empty($ap->subject_id)){
            if(!empty($sub_exname[0])) {
                $sub_name = $sub_exname[0];
            } else {
                 $sub_name = $ap->subject_id;
            }
        } else {
            $sub_name = "-";
        }
    $col = 0;
    $worksheet[0]->write($row, $col++, $ap->username);
    $worksheet[0]->write($row, $col++, $ap->prof_name);
    $worksheet[0]->write($row, $col++, $ap->year);
    $worksheet[0]->write($row, $col++, $ap->term);
    $worksheet[0]->write($row, $col++, $ap->category_name);
    $worksheet[0]->write($row, $col++, $ap->ohakkwa);
    $worksheet[0]->write($row, $col++, $ap->subject_id);
    $worksheet[0]->write($row, $col++, $ap->fullname);
    $worksheet[0]->write($row, $col++, $ap->isnonformal == 1 ? get_string('stats_irregular', 'local_lmsdata') : get_string('stats_regular', 'local_lmsdata'));
    $worksheet[0]->write($row, $col++, $ap->editingteacher);
    $worksheet[0]->write($row, $col++, $ap->teacher);
    $worksheet[0]->write($row, $col++, $ap->student);
    $worksheet[0]->write($row, $col++, $ap->auditor);
    $worksheet[0]->write($row, $col++, $submitted_count);
    $worksheet[0]->write($row, $col++, '-');
    $worksheet[0]->write($row, $col++, '-');
    $worksheet[0]->write($row, $col++, '-');
    $worksheet[0]->write($row, $col++, $ap->student_content);
    $worksheet[0]->write($row, $col++, $ap->teacher_content);
    $worksheet[0]->write($row, $col++, $ap->editingteacher_content);
    $row++;
}
$workbook->close();
die;
