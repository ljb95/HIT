<?php
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 0);

require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once("$CFG->libdir/excellib.class.php");
require_once dirname(dirname (__FILE__)).'/lib.php';

//년도가 없을 경우
if (!$year){
    $year = date('Y');
}

$searchtext = optional_param('searchtext', '', PARAM_RAW);
$syear = optional_param('syear', 0, PARAM_INT);
$sterm = optional_param('sterm', 0, PARAM_INT);
$process = optional_param('process', 0, PARAM_INT);
if(!$syear) {
    $syear = get_config('moodle', 'haxa_year'); 
}
if(!$sterm) {
    $sterm = get_config('moodle', 'haxa_term');
}

$sql_params = array(
        'year'=>$syear,
        'term'=>$sterm,
        'contextlevel1'=>CONTEXT_COURSE
    );

if (!empty($searchtext)) {
    $sql_conditions[] = ' (u.firstname like :searchtext1 or u.username like :searchtext2) or c.fullname like :searchtext3  ';
    $sql_params['searchtext1'] = '%'.$searchtext.'%';
    $sql_params['searchtext2'] = '%'.$searchtext.'%';
        $sql_params['searchtext3'] = '%'.$searchtext.'%';
}

    $sql_conditions[] = ' lc.isnonformal = :process ';
    $sql_params['process'] = $process;

if(!empty($sql_conditions)) {
    $sql_conditions = ' WHERE '.implode(' and ', $sql_conditions);
}else {
    $sql_conditions = '';
}

$select = "select CONCAT(c.id, u.id) as pkid, c.id, c.fullname,u.id as userid , lc.subject_id, lc.bunban ,u.firstname ,u.username , lc.ohakkwa, lc.year, lc.term, u.institution, u.department, 
            notefile.notefilesize, notefile.notefilecount, notefile.notefirstdate , notefile.notelastdate,
            moodlefile.moodlefilesize, moodlefile.moodlefilecount, moodlefile.moodlefirstdate , moodlefile.moodlelastdate
            ";
$from = " from {course} c 
        join {lmsdata_class} lc on lc.course = c.id and lc.year = :year and lc.term = :term
        join {context} con on con.instanceid = c.id and con.contextlevel = :contextlevel1
        join {role_assignments} ra on ra.contextid = con.id 
        join {role} r on r.id = ra.roleid and r.shortname = 'editingteacher' 
        join {user} u on u.id = ra.userid and u.deleted = 0
        left join (
        select cf.course,fi.userid, sum(fi.filesize) as notefilesize, count(fi.id) as notefilecount, min(fi.timemodified) as notefirstdate, max(fi.timemodified) as notelastdate 
            from {coursenote_forder} cf
            join {coursenote_file} fi on fi.forderid = cf.id
            group by cf.course
        ) notefile on notefile.course = c.id and notefile.userid = u.id
        left join (
                select r.course,f.userid , sum(f.filesize) as moodlefilesize, count(f.id) as moodlefilecount, min(f.timemodified) as moodlefirstdate, max(f.timemodified) as moodlelastdate 
            from {resource} r
            join {modules} m on m.name = 'resource'
            join {course_modules} cm on cm.instance = r.id and cm.module = m.id
            join {context} ctx on ctx.instanceid = cm.id and ctx.contextlevel = 70
            join {files} f on f.contextid = ctx.id and f.filename != '.' 
            group by r.course
        ) moodlefile on moodlefile.course = c.id  and moodlefile.userid = u.id  ";

$groupby = " group by c.id , u.id ";
$orderby = "order by lc.ohakkwa asc ,c.id asc , u.id asc  ";
$courses = $DB->get_records_sql($select.$from.$sql_conditions.$groupby.$orderby,$sql_params);
//$count_courses = $DB->count_records_sql('select count(distinct c.id) '.$from.$sql_conditions ,$sql_params);


$fields = array(
//    get_string('number', 'local_lmsdata'),
     '교과목명',
     '교과목코드',
    '분반',
     get_string('course_code', 'local_lmsdata'),
     '개설학과',
     get_string('teachername', 'local_lmsdata'),
     get_string('stats_alternation', 'local_lmsdata'),
    '소속학과', 
    '직종',
    '통입력',
    '공통',
    '1주차',
    '2주차',
    '3주차',
    '4주차',
    '5주차',
    '6주차',
    '7주차',
    '8주차',
    '9주차',
    '10주차',
    '11주차',
    '12주차',
    '13주차',
    '14주차',
    '15주차',
);

$date = date('Y-m-d', time());
$filename = 'CourseNotelist_'.$date.'.xls';

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

$coursearrays = array();

foreach($courses as $course) {
    if(!empty($coursearrays[$course->id]['courseid'])) {
        $coursearrays[$course->id]['profusername'] .= ','.$course->username;
        $coursearrays[$course->id]['firstname'] .= ','.$course->firstname;
        $coursearrays[$course->id]['userid'] .= ','.$course->userid;
        
        if(empty($coursearrays[$course->id]['notefilecount'])) {
            $coursearrays[$course->id]['notefilecount'] = $course->notefilecount;
        }
        if(empty($coursearrays[$course->id]['notefilecount'])) {
            $coursearrays[$course->id]['notefilecount'] = $course->notefilecount;
        }
        if(empty($coursearrays[$course->id]['notefilesize'])) {
            $coursearrays[$course->id]['notefilesize'] =(round($course->notefilesize/1024));
        }
        if(empty($coursearrays[$course->id]['notefirstdate'])) {
            $coursearrays[$course->id]['notefirstdate'] = $course->notefirstdate;
        }
        if(empty($coursearrays[$course->id]['notelastdate'])) {
            $coursearrays[$course->id]['notelastdate'] = $course->notelastdate;
        }
        if(empty($coursearrays[$course->id]['moodlefilecount'])) {
            $coursearrays[$course->id]['moodlefilecount'] = $course->moodlefilecount;
        }
        if(empty($coursearrays[$course->id]['moodlefilesize'])) {
            $coursearrays[$course->id]['moodlefilesize'] = $course->moodlefilesize;
        }
        if(empty($coursearrays[$course->id]['moodlefirstdate'])) {
            $coursearrays[$course->id]['moodlefirstdate'] = $course->moodlefirstdate;
        }
        if(empty($coursearrays[$course->id]['moodlelastdate'])) {
            $coursearrays[$course->id]['moodlelastdate'] = $course->moodlelastdate;
        }
    } else {
        $subject_id = explode('-', $course->subject_id);
        $coursearrays[$course->id]['courseid'] =$course->id;
        $coursearrays[$course->id]['fullname'] =$course->fullname;
        $coursearrays[$course->id]['profusername'] = $course->username;
        $coursearrays[$course->id]['firstname'] = $course->firstname;
        $coursearrays[$course->id]['bunban'] = $course->bunban;
        $coursearrays[$course->id]['subject_id'] = $subject_id[0];
        $coursearrays[$course->id]['subjectid'] = $course->subject_id;
        $coursearrays[$course->id]['department'] = $course->department;
        $coursearrays[$course->id]['ohakkwa'] = $course->ohakkwa;
        $coursearrays[$course->id]['institution'] = $course->institution;          
        $coursearrays[$course->id]['notefilesize'] = round($course->notefilesize/1024);      
        $coursearrays[$course->id]['userid'] = $course->userid;      
    }
    
}
foreach($coursearrays as $coursearray) {
    $col = 0;
    $worksheet[0]->write($row, $col++, $coursearray['fullname']);  // 교과목명
    $worksheet[0]->write($row, $col++,$subject_id[0]);  // 교과목코드
    $worksheet[0]->write($row, $col++, $coursearray['bunban']);  // 분반
    $worksheet[0]->write($row, $col++, $coursearray['subject_id']);  // 강의코드
    $worksheet[0]->write($row, $col++, $coursearray['ohakkwa']);  // 개설학과
    $worksheet[0]->write($row, $col++, $coursearray['firstname']);  // 교수명
    $worksheet[0]->write($row, $col++, $coursearray['profusername']);  // 교번
    $worksheet[0]->write($row, $col++, $coursearray['department']);  // 소속학과
    $worksheet[0]->write($row, $col++, $coursearray['institution']);  // 직종
    $worksheet[0]->write($row, $col++, $coursearray['notefilesize']); // 통입력
    
    for($i = 0; $i<16; $i++){
        $select_section = " select r.course, sum(f.filesize) as filesize
            from {resource} r
            join {modules} m on m.name = 'resource'
            join {course_modules} cm on cm.instance = r.id and cm.module = m.id
            join {context} ctx on ctx.instanceid = cm.id and ctx.contextlevel = 70
            join {files} f on f.contextid = ctx.id and f.filename != '.' 
            join {course_sections} cs on cs.course = r.course and cs.section = :count and cs.id = cm.section
            where r.course = :courseid and f.userid in (:userid)  
            group by r.course,cs.section, f.userid ";
        
        $courses_section = $DB->get_record_sql($select_section,array('count'=>$i,'courseid'=>$coursearray['courseid'],'userid'=>$coursearray['userid']));
        if(empty($courses_section)){
            $courses_section->filesize = '-';
        }
        $worksheet[0]->write($row, $col++, round($courses_section->filesize/1024));
    }
    $row++;
}
$workbook->close();
die();