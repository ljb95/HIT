<?php
require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once("$CFG->libdir/excellib.class.php");

//강좌 아이디
$id    = optional_param('id', 0, PARAM_INT);

$coursename = $DB->get_field('course','fullname',array('id'=>$id));

$sql_select = "SELECT mu.*, lu.dept, lu.usergroup, lu.hyear, lu.day_tm_cd  ";
$sql_from = "FROM {context} ct
            JOIN {role_assignments} ra on ra.contextid = ct.id AND (ra.roleid = :roleid OR ra.roleid = :roleid2)
            JOIN {user} mu on mu.id = ra.userid
            JOIN {lmsdata_user} lu on lu.userid = ra.userid 
            JOIN {role} mr on mr.id = ra.roleid ";
$sql_where = "WHERE ct.contextlevel = :contextlevel AND ct.instanceid = :instanceid";

$users = $DB->get_records_sql($sql_select.$sql_from.$sql_where, array('contextlevel'=>CONTEXT_COURSE, 'instanceid'=>$id, 'roleid'=>5, 'roleid2'=>9));
$count_users = $DB->count_records_sql("SELECT COUNT(*) ".$sql_from.$sql_where, array('contextlevel'=>CONTEXT_COURSE, 'instanceid'=>$id, 'roleid'=>5, 'roleid2'=>9));

$role_array = array(
    'rs' => '학생',
    'ad' => '조교',
    'pr' => '교수'
);


$fields = array(
    get_string('number', 'local_lmsdata'),
    get_string('major', 'local_lmsdata'),
    get_string('class','local_lmsdata'),
    get_string('student_number','local_lmsdata'),
    get_string('user_role', 'local_lmsdata'),
    get_string('name', 'local_lmsdata'),
    get_string('dayandnight', 'local_lmsdata')
);


$filename = $coursename.' 수강생 목록.xls';

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

if(empty($users)) {
    $worksheet[0]->write($row, 0, get_string('empty_student','local_lmsdata'));
}else{
    foreach ($users as $user) {
        $usergroup = empty(trim($user->usergroup)) ? '-' : $role_array[$user->usergroup];
        $col = 0;
        $worksheet[0]->write($row, $col++, $count_users--);
        $worksheet[0]->write($row, $col++, $user->dept);
        $worksheet[0]->write($row, $col++, $user->hyear);
        $worksheet[0]->write($row, $col++, $user->username);
        $worksheet[0]->write($row, $col++, $usergroup);
        $worksheet[0]->write($row, $col++, fullname($user));
        if($user->day_tm_cd == '0' || $user->day_tm_cd == null || $user->day_tm_cd == ''){ $user->day_tm_cd = '-'; 
        }else if($user->day_tm_cd == '10'){$user->day_tm_cd = '주간';
        }else if($user->day_tm_cd == '20'){$user->day_tm_cd = '야간';}
        $worksheet[0]->write($row, $col++, $user->day_tm_cd );
        $row++;
    }
}

$workbook->close();
die;

?>
