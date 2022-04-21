<?php

require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once("$CFG->libdir/excellib.class.php");
require_once dirname(dirname (__FILE__)).'/lib.php';

$coursetype = optional_param('coursetype', 0, PARAM_INT); //0:교과, 1:비교과, 2:이러닝 
$year         = optional_param('year', 0, PARAM_INT);
$term         = optional_param('term', 0, PARAM_INT);
$hyear        = optional_param('hyear', '', PARAM_RAW); //
$search       = optional_param('search', 1, PARAM_INT);
$searchtext   = optional_param('searchtext', '', PARAM_TEXT);
$tag_searchtext   = optional_param('tag', '', PARAM_TEXT);
$cata1        = optional_param('cata1', 0, PARAM_INT);
$cata2        = optional_param('cata2', 0, PARAM_INT);
$cata3        = optional_param('cata3', 0, PARAM_INT); //3차분류는 과정과 동일

$term_arr =lmsdata_get_terms();

$sql_select  = "SELECT co.id as courseid, co.fullname, co.shortname, co.summary, 
                lc.year, lc.term, lc.subject_id, lc.kor_lec_name, lc.eng_lec_name, lc.timestart, lc.timeend,
                ur.firstname, ur.lastname, ur.username as prof_cd ";

$sql_from    = " FROM {course} co
                 JOIN {course_categories} ca ON ca.id = co.category 
                 JOIN {lmsdata_class} lc ON lc.course = co.id
                 JOIN {context} ctx on ctx.contextlevel = 50 and ctx.instanceid = co.id 
                 left join {role_assignments} ra on ra.contextid = ctx.id AND  ra.roleid = (SELECT id FROM {role} WHERE shortname = 'editingteacher') 
                 left join {user} ur on ur.id = ra.userid AND ur.deleted = 0 "; 

$sql_where   =  array();
$params = array();
        
$cata_path = '';
if($cata3) {
    $cata_path = $DB->get_field('course_categories', 'path', array('id'=>$cata3));
} else if($cata2) {
    $cata_path = $DB->get_field('course_categories', 'path', array('id'=>$cata2));
} else if($cata1) {
    $cata_path = $DB->get_field('course_categories', 'path', array('id'=>$cata1));
}
if(!empty($cata_path)) {
    $sql_where[]= $DB->sql_like('ca.path', ':category_path');
    $params['category_path'] = $cata_path.'%';;
}

//교과,비교과,이러닝 구분
$sql_where[] = "lc.isnonformal = :coursetype";
$params['coursetype'] = $coursetype;

if(!empty($year)) { 
    $sql_where[] = " lc.year = :year ";
    $params['year'] = $year;
}

if(!empty($term)) {
    $sql_where[] = " lc.term = :term ";
    $params['term'] = $term;
}

if(!empty($hyear)) {
    $sql_where[]= $DB->sql_like('lc.hyear', ':hyear');
    $params['hyear'] = '%'.$hyear.'%';
}

if(!empty($tag_searchtext)) {    
    $sql_where[]= $DB->sql_like('lc.tag', ':tag');
    $params['tag'] = '%'.$tag_searchtext.'%';
}

if(!empty($searchtext)) {
    switch($search) {
        case 0: // 전체
            $sql_where[] = '( ' . $DB->sql_like('lc.subject_id', ':subject_id') . ' or ' . $DB->sql_like('lc.kor_lec_name', ':kor_lec_name') . ' or ' . $DB->sql_like('ur.firstname', ':profname_kr') . ' or ' . $DB->sql_like('ur.lastname', ':profname_en') . ')';
            $params['subject_id'] = '%'.$searchtext.'%';
            $params['kor_lec_name'] = '%'.$searchtext.'%';
            $params['profname_kr'] = '%' . $searchtext . '%';
            $params['profname_en'] = '%' . $searchtext . '%';
            break;
        case 1: // 강의코드
            $sql_where[]= $DB->sql_like('lc.subject_id', ':subject_id');
            $params['subject_id'] = '%'.$searchtext.'%';
            break;
        case 2: // 강의명
            $sql_where[] = $DB->sql_like('lc.kor_lec_name', ':kor_lec_name');
            $params['kor_lec_name'] = '%' . $searchtext . '%';
            break;
        case 3: // 책임교수명
            $sql_where[] = '( ' . $DB->sql_like('ur.firstname', ':profname_kr') . ' or ' . $DB->sql_like('ur.lastname', ':profname_en') . ')';
            $params['profname_kr'] = '%' . $searchtext . '%';
            $params['profname_en'] = '%' . $searchtext . '%';
            break;
        default:
            break;
    }
}

$sql_orderby = " ORDER BY co.fullname, co.timecreated desc ";

if(!empty($sql_where)) {
    $sql_where = ' WHERE '.implode(' and ', $sql_where);
}else {
    $sql_where = '';
}

$courses = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $params);

if($coursetype==0){
    $fields = array(
    'Course Id',
     get_string('yearterm','local_lmsdata').'(*)',
     get_string('course_number','local_lmsdata').'(*)',
     get_string('course_name_ko','local_lmsdata').'(*)',
     get_string('course_name_en','local_lmsdata'),
     get_string('prof_name','local_lmsdata'),
     get_string('prof_number','local_lmsdata'),
    '강의시작일'.'(*)',
    get_string('course_period_start','local_lmsdata').'(*)',
    get_string('learning_objectives', 'local_lmsdata')
);
}else{
    $fields = array(
    'Course Id',
     get_string('course_number','local_lmsdata').'(*)',
     get_string('course_name_ko','local_lmsdata').'(*)',
     get_string('course_name_en','local_lmsdata'),
     get_string('prof_name','local_lmsdata'),
     get_string('prof_number','local_lmsdata'),
    '강의시작일'.'(*)',
    get_string('course_period_start','local_lmsdata').'(*)',
    get_string('learning_objectives', 'local_lmsdata')
);
}


$date = date('Y-m-d', time());
$filename = 'Courselist_'.$date.'.xls';

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

foreach($courses as $course) {
    $col = 0;
    $worksheet[0]->write($row, $col++, $course->courseid);  // 강의아이디
    if($coursetype==0) $worksheet[0]->write($row, $col++, get_string('year','local_lmsdata',$course->year).'/'.$term_arr[$course->term]);  // 년도학기
    $worksheet[0]->write($row, $col++, $course->subject_id); // 강의교유코드
    $worksheet[0]->write($row, $col++, $course->kor_lec_name); // 한글이름
    $worksheet[0]->write($row, $col++, $course->eng_lec_name); // 영어이름
    $worksheet[0]->write($row, $col++, $course->firstname.$course->lastname); // 교수자이름
    $worksheet[0]->write($row, $col++, $course->prof_cd); // 교수자 ID
    $worksheet[0]->write($row, $col++, userdate($course->timestart, '%Y-%0m-%0d')); // 시작시간
    $worksheet[0]->write($row, $col++, userdate($course->timeend, '%Y-%0m-%0d'));  // 종료시간
    $row++;
}

$workbook->close();
die;
