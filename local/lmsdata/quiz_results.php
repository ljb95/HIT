<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');

require_once $CFG->dirroot.'/local/lmsdata/lib.php';

$courseid = required_param('id', PARAM_INT);

$quizid = optional_param('quizid', 0, PARAM_INT);
$searchtext = optional_param('searchtext', '', PARAM_TEXT);
$hyear = optional_param('hyear', 0, PARAM_INT);
$hakkwa = optional_param('hakkwa', '', PARAM_RAW);
$juya = optional_param('juya', 0, PARAM_INT);
$grade = optional_param('grade', 60, PARAM_INT);

$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);

$page_params = array();
$page_params['id'] = $courseid;
$page_params['quizid'] = $quizid;
$page_params['searchtext'] = $searchtext;
$page_params['perpage'] = $perpage;
$page_params['hyear'] = $hyear;
$page_params['hakkwa'] = $hakkwa;
$page_params['juya'] = $juya;
$page_params['grade'] = $grade;


if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}

$context = context_course::instance($course->id);

$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');

$PAGE->navbar->add($course->fullname, $CFG->wwwroot . '/course/view.php?id=' . $course->id);
$PAGE->navbar->add("성적/출석");
$PAGE->navbar->add($course->fullname . " 평가결과 확인");
$PAGE->set_heading($course->fullname . " 평가결과 확인");
$PAGE->set_title($course->fullname . " 평가결과 확인");
$PAGE->set_url('/local/lmsdata/quiz_results.php', array('id' => $courseid));

require_login($course);

if (!(has_capability('moodle/course:update', $context) || is_siteadmin())) {
    redirect($CFG->wwwroot.'/view/course.php?id='.$course->id,'페이지에 접근 할 권한이 없습니다');
}

$offset = ($page - 1) * $perpage;

$quizs = $DB->get_records('quiz', array('course' => $courseid));
if ($quizid) {
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

    $cquery = "select count(u.id)
from {role_assignments} ra 
join {user} u on u.id = ra.userid 
join {lmsdata_user} lu on u.id = lu.userid 
join {context} c on c.contextlevel = 50 and c.id = ra.contextid 
join {quiz} q on q.id = :quizid 
";

    $users = $DB->get_records_sql($query . $where, $params, ($currpage - 1) * $perpage, $perpage);

    $total = $DB->count_records_sql($cquery . $where, $params);
}


echo $OUTPUT->header();

$htmlwriter = new html_writer();
?>
<form class="table-search-option" method="post">
    <p class="form-row">
        <select name="quizid">
            <option value="">평가선택</option>
<?php
foreach ($quizs as $quiz) {
    $options = array('value' => $quiz->id);
    if ($quiz->id == $quizid) {
        $options['selected'] = 'selected';
    }
    echo $htmlwriter->tag('option', $quiz->name, $options);
}
?>
        </select>
        <select name="hakkwa">
            <option value="">학과전체</option>
<?php
$parentcategory = $DB->get_record('course_categories', array('idnumber' => 'HIT'));
$hakkwas = $DB->get_records('course_categories', array('parent' => $parentcategory->id), '', 'id, name');
foreach ($hakkwas as $hakkwakey => $hakkwaval) {
    $selected = "";
    if ($hakkwaval->id == $hakkwa) {
        $selected = "selected";
    }
    if ($hakkwaval->name != null && trim($hakkwaval->name) != '') {
        echo '<option value="' . $hakkwaval->id . '"  ' . $selected . '>' . $hakkwaval->name . '</option>';
    }
}
?>
        </select>
        <select name="hyear">
            <option value="">학년전체</option>
            <option value="1" <?php if ($hyear == 1) echo 'selected'; ?>>1학년</option>
            <option value="2" <?php if ($hyear == 2) echo 'selected'; ?>>2학년</option>
            <option value="3" <?php if ($hyear == 3) echo 'selected'; ?>>3학년</option>
            <option value="4" <?php if ($hyear == 4) echo 'selected'; ?>>4학년</option>
        </select>
        <select name="juya">
            <option value="">주야</option>
            <option value="10" <?php if ($juya == 10) echo 'selected'; ?>>주간</option>
            <option value="20" <?php if ($juya == 20) echo 'selected'; ?>>야간</option>
        </select>
    </p>
    <p class="form-row">
        <input type="text" class="w200px" name="searchtext" value="<?php echo $searchtext; ?>" placeholder="이름/학번 검색" />
        <input type="text" class="w100px" name="grade" value="<?php echo $grade; ?>" placeholder="평가점수" /> 점 이상&nbsp;
        <input type="submit" class="btn" value="검색" />
    </p>
</form>
<div class="table-top-area">
    <form method="post">
<?php
foreach ($page_params as $key => $value) {
    $attributes = array('type' => 'hidden', 'name' => $key, 'value' => $value);
    echo $htmlwriter->empty_tag('input', $attributes) . "\n";
}
?>
        <select class="perpage f-l" name="perpage" onchange="this.form.submit();">
            <option value="10">10</option>
            <option value="20" <?php if ($perpage == 20) echo 'selected'; ?>>20</option>
            <option value="30" <?php if ($perpage == 30) echo 'selected'; ?>>30</option>
            <option value="50" <?php if ($perpage == 50) echo 'selected'; ?>>50</option>
            <option value="100" <?php if ($perpage == 100) echo 'selected'; ?>>100</option>
        </select>
    </form>
    <form method="post" action="quiz_results_excel.php">
<?php
foreach ($page_params as $key => $value) {
    $attributes = array('type' => 'hidden', 'name' => $key, 'value' => $value);
    echo $htmlwriter->empty_tag('input', $attributes) . "\n";
}
?>
    <input type="submit" class="btn f-r" value="엑셀다운로드" />
    </form>
</div>
<table class="table table-bordered generaltable">
    <colgroup>
        <col width="50px" />
        <col width="/" />
        <col width="100px" />
        <col width="100px" />
        <col width="/" />
        <col width="/" />
        <col width="/" />
        <col width="100px" />
        <col width="100px" />
    </colgroup>
    <thead>
        <tr>
            <th>번호</th>
            <th>학과</th>
            <th>학년</th>
            <th>주야</th>
            <th>학번</th>
            <th>이름</th>
            <th>평가성적</th>
            <th>결과</th>
        </tr>
    </thead>
    <tbody>
<?php
if (!$quizid) {
    $td = $htmlwriter->tag('td', '퀴즈를 선택해주세요.', array('colspan' => 8));
    echo $htmlwriter->tag('tr', $td);
} else {
    $num = $total - $offset;
    foreach ($users as $user) {
        if ($user->day_tm_cd == 10) {
            $daytm = '주간';
        } else if ($user->day_tm_cd == 20) {
            $daytm = '야간';
        } else {
            $daytm = '-';
        } 
        if ($user->sumgrades) {
            $sumgrade = number_format($user->sumgrades);
            if ($sumgrade >= $grade) {
                $pass = 'Pass';
            } else {
                $pass = 'Fail';
            }
        } else {
            $sumgrade = '-';
            $pass = '-';
        }
        $tds = $htmlwriter->tag('td', $num--);
        $tds .= $htmlwriter->tag('td', $user->hakkwa);
        $tds .= $htmlwriter->tag('td', $user->hyear);
        $tds .= $htmlwriter->tag('td', $daytm);
        $tds .= $htmlwriter->tag('td', $user->username);
        $tds .= $htmlwriter->tag('td', fullname($user));
        $tds .= $htmlwriter->tag('td', $sumgrade);
        $tds .= $htmlwriter->tag('td', $pass);
        echo $htmlwriter->tag('tr', $tds);
    }
    if (!$users) {
        $td = $htmlwriter->tag('td', '등록된 수강생이 없습니다.', array('colspan' => 8));
        echo $htmlwriter->tag('tr', $td);
    }
}
?>
    </tbody>
</table>
<div class="table-footer-area">
    <div class="board-breadcrumbs">
       <?php 
        echo my_print_paging_navbar($total, $page, $perpage, 'quiz_results.php', $page_params);
       ?>
    </div>
</div>
<?php
echo $OUTPUT->footer();
?>