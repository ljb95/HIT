<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once $CFG->libdir . '/excellib.class.php' ;
require_once $CFG->dirroot . '/local/online_attendance/lib.php';
require_once($CFG->dirroot.'/local/online_attendance/classes/autoloader.php');
        
$search = optional_param('search', '', PARAM_RAW);

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);
$context = context_course::instance($course->id);
$PAGE->set_context($context);


//등록된 학생 목록
$fullname = $DB->sql_fullname('ur.firstname', 'ur.lastname');
$sql_select = "SELECT  ur.id,
                       ur.username, ".$DB->sql_fullname('ur.firstname', 'ur.lastname')." AS fullname ";
$sql_from = " FROM {context} co 
              JOIN {role_assignments} ra ON ra.contextid = co.id and roleid = :roleid
              JOIN {user} ur ON ur.id = ra.userid ";

$sql_conditions[] = ' co.id = :contextid ';
$sql_conditions[] = ' ur.deleted = :deleted ';

$params['contextid'] = $context->id;
$params['roleid'] = $DB->get_field('role', 'id', array('archetype' => 'student'));
$params['deleted'] = 0;

//검색어
if (!empty($search)) {
    $like_fullname = $DB->sql_like($fullname, ':fullname');
    $like_name = $DB->sql_like('ur.username', ':username');
    $sql_conditions[] = '(' . $like_fullname . ' or ' . $like_name . ')';
    $params['fullname'] = '%' . $search . '%';
    $params['username'] = '%' . $search . '%';
}

$sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
$sql_order_by = ' ORDER BY loc.section, loc.cmid ASC ';

$totalcount = $DB->count_records_sql('SELECT COUNT(*) '.$sql_from.$sql_where, $params);
$users = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $params, ($page-1)*$perpage, $perpage);

// user의 주차별 출석 데이터
if(!empty($users)){
    $userlist = array();
    foreach($users as $user) {
        $userlist[] = $user->id;
    }
}

$weeks_status = local_onattendance_week_status($id, $userlist);
$weektstatus = array();
foreach($weeks_status as $wst) {
    $weektstatus[$wst->userid][$wst->sec] = $wst->status; 
}
unset($weeks_status); 
$sections = $DB->get_records_sql_menu('SELECT section FROM {local_onattend_cm_set} WHERE approval = 1 GROUP BY section order BY section', array('approval'=>1));
$sections = array_keys($sections);

//강의 출석 설정 정보
$setupdata = new online_attendance($id);

$filename = 'offline_attendance('.$course->fullname.').xls';

foreach($dates as $date) {
   $fields[] = date('Y-m-d', $date);
}

$date = date('Y-m-d', time());
$filename = 'Attendance_book'.$date.'.xls';

$workbook = new MoodleExcelWorkbook('-');
$workbook->send($filename);

$worksheet = array();

$col = 0;
$worksheet[0] = $workbook->add_worksheet('');
$worksheet[0]->write(0, $col++, '번호');
$worksheet[0]->write(0, $col++, '이름');
$worksheet[0]->write(0, $col++, '학번');
foreach($sections as $section) {
    $worksheet[0]->write(0, $col, $section.'주차');
    $col++;
}
$worksheet[0]->write(0, $col++, '출석');
$worksheet[0]->write(0, $col++, '지각');
$worksheet[0]->write(0, $col++, '결석');
$worksheet[0]->write(0, $col++, '점수');
$row = 1;
$status = array(
            0 =>'결석',
            1 =>'출석',
            2 =>'지각'
        );
foreach($users as $user) {
    $status_count = array();
    $col = 0;
    $worksheet[0]->write($row, $col++, $row);
    $worksheet[0]->write($row, $col++, $user->fullname);
    $worksheet[0]->write($row, $col++, $user->username);
    foreach($sections as $sect) {
        $weekdata = $weektstatus[$user->id][$sect];
        if(empty($weekdata)) {
            $weekdata = 0;
        }
        $worksheet[0]->write($row, $col++, $status[$weekdata]);
        $status_count[$weekdata]++;
    }
    
    $absent = 0;
    $absent += $status_count[0];
    $absent += floor($status_count[2] / $setupdata->late);
    $grade = floor($setupdata->maxscore) + ($absent * $setupdata->absent);
    $minscore = floor($setupdata->minscore);
    if($grade < $minscore) {
        $grade = $minscore;
    }
    $worksheet[0]->write($row, $col++, $status_count[1] ? $status_count[1] : 0);
    $worksheet[0]->write($row, $col++, $status_count[2] ? $status_count[2] : 0);
    $worksheet[0]->write($row, $col++, $status_count[0] ? $status_count[0] : 0);
    $worksheet[0]->write($row, $col++, $grade);
    
    $row++;
}

$workbook->close();
die;