<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once $CFG->libdir . '/excellib.class.php' ;

$search = optional_param('search', '', PARAM_RAW);

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);
$context = context_course::instance($course->id);
$PAGE->set_context($context);

$roleobjs = $DB->get_records('role', array('archetype' => 'student'));
$roles = array_keys($roleobjs);
list($sql_in, $sql_params) = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'roleid');

$sql_select = "SELECT  ur.*,r.archetype ";
$sql_from = " FROM {user} ur 
              JOIN (
                SELECT userid , roleid 
                FROM {role_assignments} 
                WHERE contextid = :contextid AND roleid $sql_in
                GROUP BY userid 
                ) ra ON ra.userid = ur.id  "
        . "JOIN {role} r on r.id = ra.roleid  ";

$sql_conditions = array('ur.deleted = :deleted');
$sql_params['contextid'] = $context->id;
$sql_params['deleted'] = 0;

//검색어
if (!empty($search)) {
    $like_fullname = $DB->sql_like('CONCAT(ur.firstname,ur.lastname)', ':fullname');
    $like_name = $DB->sql_like('ur.username', ':username');
    $sql_conditions[] = '(' . $like_fullname . ' or ' . $like_name . ')';
    $sql_params['fullname'] = '%' . $search . '%';
    $sql_params['username'] = '%' . $search . '%';
}

$sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
$sql_orderby = ' ORDER BY firstname, lastname ASC ';

$totalcount = $DB->count_records_sql('SELECT COUNT(*) '.$sql_from.$sql_where, $sql_params);
$users = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $sql_params);

$userids = array_keys($users);
list($sql, $params) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'userid');
$sql_select = ' SELECT * FROM {local_off_attendance_status} ';
$sql_where = ' WHERE userid '. $sql . ' and courseid = :courseid';
$params['courseid'] = $id;
$attendance_books = $DB->get_records_sql($sql_select.$sql_where, $params);


$dates = array();
$books = array();
foreach($attendance_books as $atttendance_book) {
    $userid = $atttendance_book->userid;
    $user_timedate = $atttendance_book->timedate;
    $dates[$user_timedate] = $user_timedate; 
    $books[$userid][$user_timedate] = $atttendance_book->status;
}

$filename = 'offline_attendance('.$course->fullname.').xls';

$fields = array(
            get_string('manage:major', 'local_offline_attendance'),
            get_string('manage:username', 'local_offline_attendance'),
            get_string('manage:role', 'local_offline_attendance'),
            get_string('manage:name', 'local_offline_attendance')
);

foreach($dates as $date) {
   $fields[] = date('Y-m-d', $date);
}

$date = date('Y-m-d', time());
$filename = '오프라인출석부_'.$date.'.xls';

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
$status = array(
            1 =>'○',
            2 => 'X',
            3 =>'△',
            4 =>'□'
        );
foreach($users as $user) {
    $lmsdata = $DB->get_record('lmsdata_user',array('userid'=>$user->id));
    $col = 0;
    $worksheet[0]->write($row, $col++, $lmsdata->major);
    $worksheet[0]->write($row, $col++, $user->username);
    $worksheet[0]->write($row, $col++, get_string('defaultcourse'.$user->archetype));
    $worksheet[0]->write($row, $col++, fullname($user));
    foreach($books[$user->id] as  $st){
        $worksheet[0]->write($row, $col++, $status[$st]);
    }
    $row++;
}

$workbook->close();
die;