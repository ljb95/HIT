<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once $CFG->libdir . '/excellib.class.php' ;
require_once $CFG->dirroot . '/local/online_attendance/lib.php';

$search = optional_param('search', '', PARAM_RAW);

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);
$context = context_course::instance($course->id);
$PAGE->set_context($context);
$search = optional_param('search', '', PARAM_RAW);

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

$users = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $params);

// user activity 별 출석 데이터
if(!empty($users)){
    $userlist = array();
    foreach($users as $user) {
        $userlist[] = $user->id;
    }
}
$userdata = local_onattendance_get_status($id, $userlist);
foreach($userdata as $data) {
    $attend[$data->userid][$data->cmid] = $data;
}

// 활성화 activity 목록
$mods = local_onattendance_get_cmset($id, $visible);

$totaltr = 3;
foreach($mods as $mod) {
    $modtitle = $DB->get_field_sql('SELECT name FROM {'.$mod->modname.'} WHERE id = :id', array('id'=>$mod->instance));
    $mod->modtitle = $modtitle;
} 

$date = date('Y-m-d', time());
$filename = 'Activity_progress_'.$date.'.xls';

$workbook = new MoodleExcelWorkbook('-');
$workbook->send($filename);

$worksheet = array();

$worksheet[0] = $workbook->add_worksheet('');

//주차 명
$worksheet[0]->write(1, 0, '');
$worksheet[0]->write(1, 1, '');
$worksheet[0]->write(1, 2, '');
$col = 3;
foreach($mods as $mod) {
    $worksheet[0]->write(0, $col, $mod->section.'주차');
    $col++;
}

//field name
$worksheet[0]->write(1, 0, '번호');
$worksheet[0]->write(1, 1, '이름');
$worksheet[0]->write(1, 2, '학번');
$col = 3;
foreach($mods as $mod) {
    $worksheet[0]->write(1, $col, $mod->modtitle);
    $col++;
}

$row = 2;
foreach($users as $user) {
    $col = 0;
    $worksheet[0]->write($row, $col++, $row);
    $worksheet[0]->write($row, $col++, $user->fullname);
    $worksheet[0]->write($row, $col++, $user->username);
     foreach($cmdata as $cmid => $modname) {
        $attenddata = $attend[$user->id][$cmid];
        $worksheet[0]->write($row, $col++, $attenddata->aprogress.'%');
    }
    $row++;
}

$workbook->close();
die;