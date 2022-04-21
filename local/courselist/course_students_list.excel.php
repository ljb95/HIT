<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once("$CFG->libdir/excellib.class.php");

$id     = optional_param('id', 0, PARAM_INT);
$searchtext = optional_param('searchtext', '', PARAM_RAW);
$status = optional_param('status', 0, PARAM_INT);
$complete = optional_param('complete', 0, PARAM_INT);

$context = context_course::instance($id);
$user = $DB->get_record('lmsdata_user', array('userid'=>$USER->id));

//if($user->usergroup != 'pr' || !is_siteadmin()) {
//    return false; 
//}

//수강생 목록
$sql_select = "SELECT ue.id
      , en.enrol
      , ue.userid
      , ur.username
      , CONCAT(ur.firstname,ur.lastname) as fullname
      , ur.email
      , lu.major
      , ue.status as approval
      , CASE WHEN ra.id IS NULL THEN 0 ELSE 1 END AS assignment
      , cic.complete
      , cic.timecreated
      , cic.timemodified, ra.roleid ";
$sql_from = " FROM {user_enrolments} ue 
JOIN {enrol} en ON en.id = ue.enrolid
JOIN {user} ur ON ur.id = ue.userid
LEFT JOIN {lmsdata_user} lu ON lu.userid = ue.userid
LEFT JOIN {role_assignments} ra ON ra.userid = ue.userid
LEFT JOIN {course_irregular_complete} cic ON cic.courseid = en.courseid and cic.userid = ue.userid ";

$page_params = array();
$params = array(
    'courseid' => $id,
    'enabled' => 0,
    'contextlevel'=>CONTEXT_COURSE,
    'contextid' => $context->id,
    'roleid' => 5,
);

$sql_where = array();
$sql_where[]   = " en.courseid = :courseid and en.status = :enabled and ra.contextid = :contextid and ra.roleid = :roleid ";

//수강여부
if(!empty($status)) {
    $sql_where[] = ' ue.status = :status' ;
    if($status == 1) {
        $params['status'] = 0;
    } else if($status == 2) {
        $params['status'] = 1;
    }
}

//이수여부
if(!empty($complete)) {
    if($complete == 1) {
        $sql_where[] = ' cic.complete = :complete ' ;
        $params['complete'] = 1;
    } else {
        $sql_where[] = ' (cic.complete = :complete or cic.complete is null) ' ;
        $params['complete'] = 0;
    }
}

//검색어
if(!empty($searchtext)) {
    $like_name = $DB->sql_like('ur.firstname || ur.lastname', ':fullname');
    $like_hakbun = $DB->sql_like('ur.username', ':username');
    $sql_where[] = '('.$like_name.' or '.$like_hakbun.')';
    $params['fullname'] = '%'.$searchtext.'%';
    $params['username'] = '%'.$searchtext.'%';
}


$sql_where = ' WHERE '.implode(' AND ', $sql_where);
$sql_sort = ' order by ur.firstname || lastname asc ';

$users = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_sort, $params);

$fields = array(
            get_string('user:number', 'local_courselist'),
            get_string('user:name', 'local_courselist'),
            get_string('user:major', 'local_courselist'),
            get_string('user:email', 'local_courselist'),
            get_string('course:enrol', 'local_courselist'),
            get_string('user:complete', 'local_courselist')
);

$course_name = $DB->get_field('course', 'fullname', array('id'=>$id));
$date = date('Y-m-d', time());
$filename = $course_name.'_'.$date.'.xls';

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

foreach($users as $user) {
    $col = 0;
    $worksheet[0]->write($row, $col++, $user->username);
    $worksheet[0]->write($row, $col++, $user->fullname);
    $worksheet[0]->write($row, $col++, $user->major);
    $worksheet[0]->write($row, $col++, $user->email);
    
    $user_enrol = '';
    if($user->approval == 0 ) {
        $user_enrol = get_string('course:registered', 'local_courselist');
    } else if($user->approval == 1) {
       $user_enrol = get_string('course:wait', 'local_courselist');
    };
    
    $worksheet[0]->write($row, $col++, $user_enrol);
    
    if(!empty($user->timemodified)) {
        $user_complete = date('Y-m-d', $user->timemodified);
    } else {
        if($user->approval == 0 || $user->assignment == 1) {
            $user_complete = get_string('user:incomplete', 'local_courselist');
        } else if($user->approval == 1) {
            $user_complete = '-';
        }; 

    }; 
    
    $worksheet[0]->write($row, $col++, $user_complete);
    
    $row++;
}
$workbook->close();
die;