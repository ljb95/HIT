<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/courselist/lib.php';
require_once $CFG->dirroot . '/local/competence/lib.php';
require_once $CFG->dirroot . '/local/competence/classes/user_competencies.php';
require_once $CFG->dirroot . '/local/competence/classes/user_grades.php';
require_once($CFG->dirroot . '/siteadmin/lib.php');

$userid = optional_param('userid', $USER->id, PARAM_INT);
$year = optional_param('year', 0, PARAM_INT);
$complete = optional_param('complete', 0, PARAM_INT);
$searchtext = optional_param('searchtext', '', PARAM_RAW);
$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);

$context = context_system::instance();

require_login();

$PAGE->set_context($context);

$PAGE->set_url('/local/competence/index.php');
$PAGE->set_pagelayout('standard');


$strplural = get_string("pluginnameplural", "local_competence");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');
$PAGE->requires->css('/siteadmin/css/loading.css');
//$PAGE->requires->js('/local/competence/local_competence.js');

echo $OUTPUT->header();

$sql_select = 'SELECT 
            co.id, co.id as courseid, co.fullname,
            CASE WHEN cc.timecompleted IS NULL THEN 0 ELSE cc.timecompleted END AS timecompleted ';
$sql_from = 'FROM {course} co 
            JOIN {lmsdata_class} lc ON lc.course = co.id
            JOIN (
                SELECT  co.instanceid, co.instanceid as courseid, ra.userid 
                FROM {role_assignments} ra
                JOIN {role} ro ON ro.id = ra.roleid
                JOIN {context} co ON co.id = ra.contextid 
                WHERE ra.userid = :userid and ro.archetype = :type and co.contextlevel = :level 
              ) eco ON eco.courseid = co.id 
            LEFT JOIN {course_completions} cc on cc.course = co.id and cc.userid = eco.userid ';

$params = array(
    'userid' => $userid,
    'type' => 'student',
    'level' => CONTEXT_COURSE
);

$enrol_courses = $DB->get_records_sql($sql_select . $sql_from, $params, ($page - 1) * $perpage, $perpage);
$totalcount = $DB->count_records_sql('SELECT COUNT(*) ' . $sql_from . $sql_where, $params);
//등록된 강의 목록
$user_grade = new \local_competence\local_competence_user_grades($userid);
$enrol_list = $user_grade->get_user_enrol_courses();
//등록된 강의 성적
$user_grades = $user_grade->get_course_grades();

// 역량 목록
$user_competencies = new \local_competence\local_competence_user_competencies();
$competencies = $user_competencies->get_course_competencies();
//코스별 역량 성취율
$competencies_achieve = local_competence_user_competency_achieve($competencies);
//LCMS 진도율 목록
$user_progress = local_competence_get_course_lcms_progress($USER->id, $enrol_list);
?>
<h1 class="course_h1" ><?php echo $strplural; ?></h1>
<form class="table-search-option">
    <select name="year">
        <?php
        $selecte_years = lmsdata_get_years();
        $selected = ($year == 0) ? 'selected' : '';
        echo '<option value="0" ' . $selected . '>' . get_string('year:all', 'local_competence') . '</option>';
        foreach ($selecte_years as $syear) {
            $selected = ($year == $syear) ? 'selected' : '';
            echo '<option value="' . $syear . '" ' . $selected . '>' . $syear . '</option>';
        }
        ?>
    </select>
    <select name ="complete">
        <option value="0" <?php echo $complete == 0 ? 'selected' : ''; ?>><?php print_string('complete:status', 'local_competence') ?></option>>
        <option value="1" <?php echo $complete == 1 ? 'selected' : ''; ?>><?php print_string('complete:pass', 'local_competence') ?></option>>
        <option value="2" <?php echo $complete == 2 ? 'selected' : ''; ?>><?php print_string('complete:fail', 'local_competence') ?></option>>
    </select>
    <input type="text" name="searchtext" placeholder="<?php get_string('search:course', 'local_competence'); ?>" size="60" value="<?php echo!empty($searchtext) ? $searchtext : ''; ?>"/>
    <input type="submit" value="<?php echo get_string('search', 'local_competence') ?>" class="gray_btn" />
</form>
<div class="options text-right">       
    <input type="button" value="<?php echo get_string('achievement_by_competency', 'local_competence') ?>" onclick="document.location.href = '<?php echo $CFG->wwwroot . '/local/competence/competence_total.php'; ?>'" />
    <input type="button" value="<?php echo get_string('historyissuing', 'local_competence') ?>" onclick="document.location.href = '<?php echo $CFG->wwwroot . '/local/competence/competence_total.php'; ?>'" />
</div>
<table class="generaltable">
    <thead>
        <tr>
            <th class="centeralign header c0" scope="col"><?php echo get_string('no', 'local_competence') ?></th>
            <th class="centeralign header c1" scope="col"><?php echo get_string('Lecture', 'local_competence') ?></th>
            <th class="centeralign header c2" scope="col"><?php echo get_string('magnitude', 'local_competence') ?></th>
            <th class="centeralign header c3" scope="col"><?php echo get_string('competency', 'local_competence') ?></th>
            <th class="centeralign header c4 lastcol" scope="col"><?php echo get_string('score', 'local_competence') ?></th>
            <th class="centeralign header c5 lastcol" scope="col"><?php echo get_string('activity', 'local_competence') ?></th>
            <th class="centeralign header c6 lastcol" scope="col"><?php echo get_string('complete_status', 'local_competence') ?></th>
            <th class="centeralign header c7 lastcol" scope="col"><?php echo get_string('complete_success', 'local_competence') ?></th>
            <th class="centeralign header c8 lastcol" scope="col"><?php echo get_string('certificate_issue', 'local_competence') ?></th>
            <th class="centeralign header c9 lastcol" scope="col"><?php echo get_string('classroom', 'local_competence') ?></th>
        </tr>   
    </thead>
    <tbody>
        <?php
        $startnum = $totalcount - (($page - 1) * $perpage);
        if (!empty($totalcount)) {
            foreach ($enrol_courses as $course) {
                //lcms 진도율
                $lcmscount = $user_progress[$course->id]->lccount;
                $sumprogress = $user_progress[$course->id]->sumprogress;
                if (empty($lcmscount)) {
                    $progress = '-';
                } else {
                    $progress = round($sumprogress / $lcmscount, 1);
                }
                //역량 성취율
                $percent = $competencies_achieve[$course->id]->percent;
                if (empty($percent)) {
                    $percent = '-';
                }

                //성적
                $total_grade = $user_grades[$course->id]->totalmaxgrade;
                $total_grade = round($total_grade, 1);
                $finalgrade = $user_grades[$course->id]->finalgrade;
                $finalgrade = round($finalgrade, 1);
                if (empty($total_grade)) {
                    $total_average = 0;
                } else {
                    $total_average = round($finalgrade / $total_grade, 1) * 100;
                }

                //이수여부
                $completed = get_string('complete:pass', 'local_competence');
                $timecompleted = '-';
                if (!empty($course->timecompleted)) {
                    $completed = get_string('complete:fail', 'local_competence');
                    $timecompleted = date('Y-m-d', $course->timecompleted);
                }
                ?>
                <tr>
                    <td class="centeralign cell c0"><?php echo $startnum--; ?></td>
                    <td class="centeralign cell c1"><?php echo $course->fullname; ?></td>
                    <td class="centeralign cell c2"><?php echo $progress; ?></td>
                    <td class="centeralign cell c3"><?php echo '<a target="_blank" href ="' . $CFG->wwwroot . '/admin/tool/lp/coursecompetencies.php?courseid=' . $course->id . '">' . $percent; ?></td>
                    <td class="centeralign cell c4"><?php echo $total_average . '(' . $finalgrade . '/' . $total_grade . ')'; ?></td>
                    <td class="centeralign cell c5"><?php echo '<div class="activity-view" courseid="' . $course->id . '">click</div>'; ?></td>
                    <td class="centeralign cell c6"><?php echo $completed; ?></td>
                    <td class="centeralign cell c7"><?php echo $timecompleted; ?></td>
                    <td class="centeralign cell c8 lastcol"><input type="button" class="blue_btn" onclick="location.href = '<?php ?>'" value="<?php echo get_string('Issued', 'local_competence') ?>"></td>
                    <td class="centeralign cell c9 lastcol"><input type="button" class="blue_btn" onclick="location.href = '<?php echo $CFG->wwwroot . '/course/view.php?id=' . $course->id; ?>'" value="<?php echo get_string('entrance', 'local_competence') ?>"></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="10"><?php echo get_string('nolecture', 'local_competence') ?></td>
            </tr>
            <?php
        }
        ?>

    </tbody>
</table>
<div class="table-footer-area">
    <?php
    courselist_print_paging_navbar_script($totalcount, $page, $perpage, 'javascript:goto_page(:page);', 10);
    ?>
</div>
<?php
echo $OUTPUT->footer();
?>


<script type="text/javascript">
    $(document).ready(function () {
        $('.activity-view').click(function () {
            var tag = $("<div id='activity_grade'></div>");
            var courseid = $(this).attr('courseid');
            $.ajax({
                url: '<?php echo $CFG->wwwroot . '/local/competence/activity_grade.ajax.php'; ?>',
                method: 'POST',
                data: {
                    courseid: courseid
                },
                success: function (data) {
                    tag.html(data).dialog({
                        title: '<?php echo get_string('learning_activities', 'local_competence') ?>',
                        modal: true,
                        width: 800,
                        resizable: false,
                        height: 400,
                        buttons: [{id: 'close',
                                text: '닫기',
                                disable: true,
                                click: function () {
                                    $(this).dialog("close");
                                }}],
                        close: function () {
                            $(this).dialog('destroy').remove()
                        }
                    }).dialog('open');
                }
            });
        })
    });
</script>