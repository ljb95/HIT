<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/excellib.class.php');


$courseid = required_param('id', PARAM_INT);

$quizid = optional_param('quizid', 0, PARAM_INT);
$searchtext = optional_param('searchtext', '', PARAM_TEXT);
$hyear = optional_param('hyear', 0, PARAM_INT);
$hakkwa = optional_param('hakkwa', '', PARAM_RAW);
$juya = optional_param('juya', 0, PARAM_INT);
$grade = optional_param('grade', 60, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}

$context = context_course::instance($course->id);

require_login($course);

$params['quizid'] = $quizid;
$conditions[] = 'ra.contextid = c.id';
$conditions[] = ' c.id = :contextid ';
$params['contextid'] = $context->id;
if ($hakkwa != '') {
    $conditions[] = $DB->sql_like('lu.dept', ':hakkwa');
    $params['hakkwa'] = '%' . $hakkwa . '%';
}
if ($hyear != '') {
    $conditions[] = ' lu.hyear = :hyear ';
    $params['hyear'] = $hyear;
}
if ($juya != '') {
    $conditions[] = ' lu.day_tm_cd = :juya ';
    $params['juya'] = $juya;
}

if ($searchtext != '') {
    $conditions[] = '(' . $DB->sql_like('u.username', ':searchtext')
            . ' or ' . $DB->sql_like('u.firstname', ':searchtext2')
            . ' or ' . $DB->sql_like('u.lastname', ':searchtext3')
            . ' or ' . $DB->sql_like('CONCAT(u.firstname,u.lastname)', ':searchtext4') . ')';

    $params['searchtext'] = $params['searchtext2'] = $params['searchtext3'] = $params['searchtext4'] = '%' . $searchtext . '%';
}

if (!empty($conditions)) {
    $where = " WHERE " . implode(" AND ", $conditions);
} else {
    $where = "";
}

$sort = ' order by u.username desc';

$query = "select u.id,u.firstname,u.lastname,u.username, lu.dept, lu.hyear,  lu.day_tm_cd  , qatt.sumgrades
from {role_assignments} ra 
join {user} u on u.id = ra.userid 
join {lmsdata_user} lu on u.id = lu.userid 
join {context} c on c.contextlevel = 50 and c.id = ra.contextid 
join {quiz} q on q.id = :quizid 
left join {quiz_attempts} qatt on qatt.quiz = q.id 
";



$users = $DB->get_records_sql($query . $where, $params, ($currpage - 1) * $perpage, $perpage);


$fields = array(
    '학과',
    '학년',
    '주야',
    '학번',
    '이름',
    '평가성적',
    '결과',
);

$date = date('Y-m-d', time());
$filename = $course->fullname.'_평가결과.xls';

$workbook = new MoodleExcelWorkbook('-');
$workbook->send($filename);

$worksheet = array();

$worksheet[0] = $workbook->add_worksheet('');
$col = 0;
foreach ($fields as $fieldname) {
    $worksheet[0]->write(0, $col, $fieldname);
    $col++;
}

$row = 0;
foreach ($users as $user) {
    
    $row++;
    $col = 0;
    
    if ($user->day_tm_cd == 10) {
        $daytm = '주간';
    } else if ($user->day_tm_cd == 20) {
        $daytm = '야간';
    } else {
        $daytm = '-';
    }
    if ($user->sumgrades) {
        $sumgrade = $user->sumgrades;
        if ($sumgrade >= $grade) {
            $pass = 'Pass';
        } else {
            $pass = 'Fail';
        }
    } else {
        $sumgrade = '-';
        $pass = '-';
    }
    $worksheet[0]->write($row, $col++, $user->hakkwa);
    $worksheet[0]->write($row, $col++, $user->hyear);
    $worksheet[0]->write($row, $col++, $daytm);
    $worksheet[0]->write($row, $col++, $user->username);
    $worksheet[0]->write($row, $col++, fullname($user));
    $worksheet[0]->write($row, $col++, $sumgrade);
    $worksheet[0]->write($row, $col++, $pass);
 
    if (!$users) {
         $worksheet[0]->write($row, $col++, '등록된 수강생이 없습니다.');
    }
}
$workbook->close();
die;