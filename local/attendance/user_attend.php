<?php
require_once("../../config.php");

require_once $CFG->dirroot . '/course/report/statistics/lib.php';
require_once $CFG->libdir . '/formslib.php';
require_once $CFG->dirroot . '/local/attendance/lib.php';

$search = optional_param('search', '', PARAM_CLEAN);
$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 5, PARAM_INT);
$nullcnt = optional_param('nullcnt', 0, PARAM_INT);

$offset = ($page - 1) * $perpage;

require_login();

$context = context_system::instance();
$PAGE->set_context($context);




$section_cnt = 15;

$PAGE->set_url('/local/attendance/index.php?id=' . $id);
$PAGE->set_pagelayout('standard');
$strplural = get_string('Attendance_Status', 'local_lmsdata');

//$PAGE->navbar->add(get_string("pluginname", "coursereport_statistics"));
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);

$PAGE->requires->jquery();

$USER_select = "select c.id as courseid,u.id,c.fullname";
$USER_query = " from {course} c 
    join {context} ctx on ctx.contextlevel = 50 and ctx.instanceid = c.id 
    join {role_assignments} ra on ra.contextid = ctx.id 
    join {role} r on r.id = ra.roleid and r.shortname = 'student' 
    join {user} u on u.id = ra.userid and u.id = :userid ";
$params = array('userid' => $USER->id);

$USER_courses = $DB->get_records_sql($USER_select . $USER_query, $params);

echo $OUTPUT->header();
?>
<table class="generaltable">
    <thead>
        <tr>
            <th>번호</th>
            <th>강좌명</th>
            <?php for ($i = 1; $i <= 15; $i++) { ?>
                <th><?php echo $i; ?>주차</th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $cnt = 1;
        foreach ($USER_courses as $USER_course) {


            $activities = array();  // 주차별 액티비티명이 들어갈 배열
            $sec_colspan = array(); // 섹션 콜스판(액티비티의 갯수) 를 담기위한 배열
            for ($i = 1; $i <= 15; $i++) {
                $section_activities = $DB->get_records_sql('select * from v_attend_mod where course = :courseid and section = :section', array('courseid' => $USER_course->courseid, 'section' => $i));
                $sec_colspan[$i] = 0; // 기본 콜스판 선언
                foreach ($section_activities as $section_activity) {
                    $activities[$i][$section_activity->id] = $section_activity->act; // 생성된 뷰테이블의 액티비티명  tbl , lcms , off (오프라인출석부) 정보
                    $sec_colspan[$i] ++; // 액티비티가 존재함으로 콜스판 증가
                }
                if (!$section_activities) { // 등록된 액티비티가 없을경우 콜스판을 1로 늘리고 - 표시함
                    $activities[$i][0] = '-';
                }
            }
            echo "<tr>";
            echo "<td>" . $cnt++ . "</td>";
            echo "<td>" . $USER_course->fullname . "</td>";
            for ($i = 1; $i <= 15; $i++) {
                if ($sec_colspan[$i] == 0) {
                    echo "<td>-</td>";
                } else {
                    $total = $sec_colspan[$i];
                    $zero = 0;
                    $success = 0;
                    $fail = 0;
                    $lateness = 0;
                    foreach ($activities[$i] as $activityid => $activity) {
                        $att = '';
                        switch ($activity) {
                            case 'off':
                                $query = "select * from {local_off_attendance_section} oas
                        join {local_off_attendance_status} att on att.lastcode = oas.code 
                        where oas.id = :id and att.userid = :userid";
                                $params = array('id' => $activityid, 'userid' => $USER->id, 'courseid' => $USER_course->courseid, 'section' => $i);
                                $attend = $DB->get_record_sql($query, $params);
                                switch ($attend->status) {
                                    case '0':
                                    case '2':
                                        $att = 'X';
                                        break;
                                    case '1':
                                        $att = 'O';
                                        break;
                                    case '4':
                                        $att = '□';
                                        break;
                                    case '3':
                                        $att = '△';
                                        break;
                                    default:
                                        $att = 'X';
                                        break;
                                }
                                break;
                            case 'tbl':
                                $attend = $DB->get_record('tbl_attend', array('tblid' => $activityid, 'userid' => $USER->id));
                                switch ($attend->finalstatus) {
                                    case '0':
                                    case '2':
                                        $att = 'X';
                                        break;
                                    case '1':
                                        $att = 'O';
                                        break;
                                    case '4':
                                        $att = '□';
                                    case '3':
                                        $att = '△';
                                        break;
                                    default:
                                        $att = 'X';
                                        break;
                                }
                                break;
                            case 'lcms':
                                $attend = $DB->get_record('lcms_track', array('lcms' => $activityid, 'userid' => $USER->id));
                                if ($attend->progress == 100) {
                                    $att = 'O';
                                } else if ($attend->progress) {
                                    $att = '△';
                                } else {
                                    $att = 'X';
                                }
                                break;
                        }
                        if ($att == 'X') {
                            $zero++;
                        }
                        if ($att == 'O') {
                            $success++;
                        }
                        if ($att == '△') {
                            $fail++;
                        }
                        if ($att == '□') {
                            $lateness++;
                        }
                    }
                    if ($lateness == $total) {
                        echo "<td>□</td>";
                    } else if ($zero == $total) {
                        echo "<td>X</td>";
                    } else if ($success == $total) {
                        echo "<td>O</td>";
                    } else if ($success + $fail > 0) {
                        echo "<td>△</td>";
                    } else {
                        echo "<td>-</td>";
                    }
                }
            }
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
<?php
echo $OUTPUT->footer();
