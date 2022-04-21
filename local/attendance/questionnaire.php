<?php
require_once("../../config.php");

require_once $CFG->dirroot . '/course/report/statistics/lib.php';
require_once $CFG->libdir . '/formslib.php';
require_once $CFG->dirroot . '/local/attendance/lib.php';
require_once("$CFG->libdir/excellib.class.php");

$id = required_param('id', PARAM_INT); // course id
$search = optional_param('search', '', PARAM_CLEAN);
$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);

$activity = optional_param('activity', 0, PARAM_INT);
$unreport = optional_param('unreport', '', PARAM_CLEAN);

$offset = ($page - 1) * $perpage;

require_login();

$context = context_course::instance($id);
$PAGE->set_context($context);

$course = get_course($id);

$section_cnt = $DB->get_record('course_format_options', array('courseid' => $id, 'name' => 'numsections', 'format' => $course->format));

$PAGE->set_url('/local/attendance/index.php?id=' . $id);
$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$strplural = get_string('pluginname', 'local_attendance');

$PAGE->navbar->add(get_string("pluginname", "local_attendance"));
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);

$PAGE->requires->jquery();

$usersearch = '';
$params = array('courseid' => $id);
if ($search) {
    $usersearch = 'and (u.username like :search1 or concat(u.firstname,u.lastname) like :search2)';
    $params['search1'] = $params['search2'] = '%' . $search . '%';
}
$user_select = "select u.id ,c.id as course ,(ascnt.cnt - ur.cnt) as nullcnt ,  ur.cnt as cnt ";  

for ($i = 1; $i <= $section_cnt->value; $i++) {
    if (date('Ymd', $course->startdate + (1 * 60 * 60 * 24 * 7 * ($i))) > date('Ymd')) {
        $params['sec'] = $i;
        break;
    }
}

if (!isset($params['sec'])) {
    $params['sec'] = 9999;
}

if($activity){
$ajoin = " 
    join( select qam.userid,a.id from  {questionnaire} a 
                        join {questionnaire_attempts} qam on qam.qid = a.id where a.id = :id ) a on a.userid = u.id";
                        $params['id'] = $activity;
                        $usersearch .= $ajoin;
}
//if($unreport && !$activity){
    $ujoin = " 
    join (select count(id) as cnt ,course  from {questionnaire} where course = :cid2) ascnt on ascnt.course = c.id   
    join (select qam.userid,count(qam.id) as cnt , a.course  from {questionnaire} a 
                        join {questionnaire_attempts} qam on qam.qid = a.id and a.course = :cid and userid != 0 
                        group by userid )  ur on ur.userid = u.id and ur.course = c.id  and (ascnt.cnt - ur.cnt) >= :unreport ";
     $params['cid'] = $id;
     $params['cid2'] = $id;
     $params['unreport'] = $unreport;
      $usersearch .= $ujoin;
//}

$user_query = " from {course} c
    join {context} ctx on ctx.contextlevel = 50 and ctx.instanceid = c.id 
    join {role_assignments} ra on ra.contextid = ctx.id 
    join {role} r on r.id = ra.roleid and r.shortname = 'student' 
    join {user} u on u.id = ra.userid $usersearch 
where c.id = :courseid 
   ";


$order = ' order by u.firstname asc , u.lastname asc , u.username desc';
// 강의에 등록된 유저가져오기
$users = $DB->get_records_sql($user_select . $user_query . $order, $params, $offset, $perpage);
$users_cnt = $DB->get_records_sql($user_select . $user_query . $having, $params);
$users_cnt = count($users_cnt);
// 헤더출력
echo $OUTPUT->header();
//echo '<br>'.$user_select.str_replace('}','',str_replace('{', 'm_', $user_query)); DEBUG
$row[] = new tabobject('attend', "$CFG->wwwroot/local/attendance/index.php?id=" . $id, '출석현황');
$row[] = new tabobject('assign', "$CFG->wwwroot/local/attendance/assign.php?id=" . $id, get_string('pluginname', 'assign'));
$row[] = new tabobject('quiz', "$CFG->wwwroot/local/attendance/quiz.php?id=" . $id, get_string('pluginname', 'quiz'));
$row[] = new tabobject('questionnaire', "$CFG->wwwroot/local/attendance/questionnaire.php?id=" . $id, get_string('pluginname', 'questionnaire'));
$rows[] = $row;

print_tabs($rows, 'questionnaire');

$activities = array();  // 주차별 액티비티명이 들어갈 배열
$sec_colspan = array(); // 섹션 콜스판(액티비티의 갯수) 를 담기위한 배열
for ($i = 1; $i <= $section_cnt->value; $i++) {
     $query = "select a.id,cm.course ,a.name,cs.section from {course_modules} cm 
			join {course_sections} cs on cs.id = cm.section 
			join {modules} m on m.id = cm.module 
			join {questionnaire} a on a.id = cm.instance  
			where  m.name = 'questionnaire' and cs.section = :section and cs.course = :courseid;";
     $params = array('section'=>$i ,'courseid'=>$id);
    $section_activities = $DB->get_records_sql($query,$params);
    $sec_colspan[$i] = 0; // 기본 콜스판 선언
    foreach ($section_activities as $section_activity) {
        $activities[$i][$section_activity->id] = 'questionnaire'; // 생성된 뷰테이블의 액티비티명  tbl , lcms , off (오프라인출석부) 정보
        $sec_colspan[$i] ++; // 액티비티가 존재함으로 콜스판 증가
    }
    if (!$section_activities) { // 등록된 액티비티가 없을경우 콜스판을 1로 늘리고 - 표시함
        $activities[$i][0] = '-';
        $sec_colspan[$i] ++;
    }
}
if (has_capability('moodle/course:update', $context)) {
    ?>


    <form class="table-search-option stat_form" name="form_setup">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name = "page" value="1">

        <div class="options">
    <!--        <div class="title"><?php echo get_string('search', 'local_attendance'); ?></div>-->
            <input type="text" name="search" value="<?php echo $search; ?>" class="search-text" placeholder="<?php echo get_string('manage:placeholder', 'local_offline_attendance'); ?>">
            과제명
            <select name="activity" onchange="assign_switching()">
                 <option value="">전체</option>
                <?php
                $query = "select a.id,cm.course ,a.name,cs.section from {course_modules} cm 
			join {course_sections} cs on cs.id = cm.section 
			join {modules} m on m.id = cm.module 
			join {questionnaire} a on a.id = cm.instance 
                        join {questionnaire_attempts} qam on qam.qid = a.id 
			where  m.name = 'questionnaire' and cs.section = :section and cs.course = :courseid;";
                for ($i = 1; $i <= $section_cnt->value; $i++) {
                    $quizs = $DB->get_records_sql($query, array('section' => $i, 'courseid' => $id));
                    echo '<optgroup label="' . $i . '주차">';
                    foreach ($quizs as $quiz) {
                        echo '<option '; 
                        if($quiz->id == $activity){
                            echo 'selected';
                        }
                        echo ' value="' . $quiz->id . '">' . $quiz->name . '</option>';
                    }
                    if (!$quizs) {
                        echo '<option disabled>퀴즈가 없습니다.</option>';
                    }
                }
                ?>
            </select>
            <select name="reported" style="display: none;">
                <option value="">전체</option>
                <option value="1">제출</option>
                <option value="0">미제출</option>
            </select>
            <!--span class="grade" style="display: none;">점수 <input type="text"> 점 이상</span-->
            <span class="unreport">미제출횟수 <input type="text" value="<?php echo $unreport; ?>" name="unreport"> 회 이상</span>
            <input type="submit" value="<?php echo get_string('manage:search', 'local_offline_attendance'); ?>" class="board-search"/>
        </div>
    </form>
<script>
    function assign_switching(){
        if($('select[name=activity] option:selected').val() == ''){
            $('.grade').hide();
            $('.unreport').show();
            $('select[name=reported]').hide();
        } else {
            $('.grade').show();
            $('.unreport').hide();
            $('select[name=reported]').show();
        }
    }
</script>
    <form  name="form_setup_table">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name = "page" value="1">
        <input type="hidden" name="search" value="<?php echo $search; ?>">
        <input type="hidden" name = "nullcnt" value="<?php echo $nullcnt; ?>">
        <div class="options">

            <select class="select perpage" name="perpage" onchange="this.form.submit();">
                <?php
                $nums = array(10, 20, 50, 100);
                foreach ($nums as $num) {
                    $selected = '';
                    if ($num == $perpage) {
                        $selected = ' selected';
                    }
                    echo '<option value="' . $num . '"' . $selected . '>' . get_string('showperpage', 'local_courselist', $num) . '</option>';
                }
                ?>
            </select>
            <input type="button" class="btn_st01" value="<?php echo get_string('encouraging', 'local_attendance'); ?>" onclick="pop_up_post();"> 
        </div>
    </form>
<?php } ?>
<form method="post" id="send_user_frm" name="send_user_frm" target="_blank" action="send.php?id=<?php echo $id; ?>">
    <table class="generaltable">
        <thead>
            <tr>
                <?php if (has_capability('moodle/course:update', $context)) { ?>
                    <th style="width:5%" rowspan="3"><input type="checkbox" id="all_check"></th>
                <?php } if (has_capability('moodle/course:update', $context)) { ?>
                    <th style="width:5%" rowspan="3"><?php echo get_string('num', 'local_attendance'); ?></th>
                <?php } ?>
                <th style="width:10%" rowspan="3"><?php echo get_string('major', 'local_attendance'); ?></th>
                <th style="width:10%" rowspan="3"><?php echo get_string('haknum', 'local_attendance'); ?></th>
                <th style="width:10%" rowspan="3"><?php echo get_string('name', 'local_attendance'); ?></th>
                <th style="width:5%" rowspan="3">미제출횟수<br>(미제출/제출)</th>
                <?php
                for ($i = 1; $i <= $section_cnt->value; $i++) {
                    if (date('Ymd', $course->startdate + (1 * 60 * 60 * 24 * 7 * ($i))) > date('Ymd')) {
                        break;
                    }
                    echo '<th colspan="' . $sec_colspan[$i] . '">' . $i . get_string('week', 'local_attendance') . '</th>'; // 주차 출력
                }
                ?>
            </tr>
            <tr>
                <?php
                for ($i = 1; $i <= $section_cnt->value; $i++) {
                    if (date('Ymd', $course->startdate + (1 * 60 * 60 * 24 * 7 * ($i))) > date('Ymd')) {
                        break;
                    }
                    $sectionname = get_section_name($course, $i);
                    echo '<th colspan="' . $sec_colspan[$i] . '">' . $sectionname . '</th>'; // 섹션 이름 출력
                }
                ?>
            </tr>
            <tr>
                <?php
                for ($i = 1; $i <= $section_cnt->value; $i++) {
                    if (date('Ymd', $course->startdate + (1 * 60 * 60 * 24 * 7 * ($i))) > date('Ymd')) {
                        break;
                    }
                    foreach ($activities[$i] as $activity) {
                        if ($activity != 'off' && $activity != '-') {
                            $icon = "<img src=\"" . $OUTPUT->pix_url('icon', $activity) . "\" class=\"icon\" alt=\"\" />"; // 액티비티시 아이콘 출력
                        } else if ($activity == 'off') {
                            $icon = get_string('attend', 'local_attendance');  // 오프라인 출석부 
                        } else {
                            $icon = '-';  // 액티비티가 없을때
                        }
                        echo '<th>' . $icon . '</th>';
                    }
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            $cnt = $users_cnt - $offset;
            if (!has_capability('moodle/course:update', $context)) {
                $users = array($USER);
            }
            foreach ($users as $user) {
                $user_nullcnt = $user->nullcnt;
                $user_cnt = $user->cnt;
                $user = $DB->get_record('user', array('id' => $user->id));
                $lmsdata_user = $DB->get_record('lmsdata_user', array('userid' => $user->id));
                ?>
                <tr>
                    <?php if (has_capability('moodle/course:update', $context)) { ?>
                        <td><input type="checkbox" class="check_user" name="userid[<?php echo $user->id ?>]"></td>
                        <?php } ?>
                        <?php if (has_capability('moodle/course:update', $context)) { ?>
                        <td><?php echo $cnt--; ?></td>
                    <?php } ?>
                    <td><?php echo $lmsdata_user->major ?></td>
                    <td><?php echo $user->username; ?></td>
                    <td><a href="/user/profile.php?id=<?php echo $user->id; ?>"><?php echo fullname($user); ?></a></td>
                    <td><?php echo $user_nullcnt.'/'.$user_cnt; ?></td>
                    <?php
                    for ($i = 1; $i <= $section_cnt->value; $i++) {
                        if (date('Ymd', $course->startdate + (1 * 60 * 60 * 24 * 7 * ($i))) > date('Ymd')) {
                            break;
                        }

                        foreach ($activities[$i] as $activityid => $activity) {
                            $att = '';
                            if($activityid != 0){
                                $att = 'X';
                                if($act = $DB->get_record('questionnaire_attempts',array('qid'=>$activityid,'userid'=>$user->id))){
                                    $att = 'O';
                                }
                            } 
                            
                            echo '<td>' . $att .'</td>';
                        }
                    }
                    ?>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</form>
<script>
    $('#all_check').click(function () {
        if ($('#all_check').is(":checked")) {
            $(".check_user").each(function () {
                this.checked = true;
            });
        } else {
            $(".check_user").each(function () {
                this.checked = false;
            });
        }
    });

    function pop_up_post()
    {
        window.open("", "mypop", "width=850, height=650, scrollbars=yes");
        document.send_user_frm.action = "send.php?id=<?php echo $id; ?>";
        document.send_user_frm.target = "mypop";
        document.send_user_frm.submit();
    }

</script>
<?php
$total_page = ceil($users_cnt / $perpage);
$params = array('id' => $id, 'nullcnt' => $nullcnt);
if (has_capability('moodle/course:update', $context)) {
    echo '<div class="table-footer-area">';
    attend_get_paging_bar($CFG->wwwroot . '/local/attendance/index.php', $params, $total_page, $page);
    echo '</div>';
}
echo $OUTPUT->footer();

