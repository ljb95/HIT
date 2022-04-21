<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/courselist/lib.php';
require_once $CFG->dirroot . '/local/competence/lib.php';
require_once $CFG->dirroot . '/local/okirregular/lib.php';
require_once $CFG->dirroot . '/local/competence/classes/user_competencies.php';
require_once $CFG->dirroot . '/local/competence/classes/user_grades.php';
require_once($CFG->dirroot . '/siteadmin/lib.php');

$userid = optional_param('userid', $USER->id, PARAM_INT);
$complete = optional_param('complete', 0, PARAM_INT);
$searchtext = optional_param('searchtext', '', PARAM_RAW);
$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);

$context = context_system::instance();

require_login();

$PAGE->set_context($context);

$PAGE->set_url('/local/okirregular/complete.php');
$PAGE->set_pagelayout('standard');


$strplural = get_string("pluginnameplural", "local_okirregular");
$PAGE->navbar->add($strplural);
$PAGE->navbar->add(get_string('complete:list','local_okirregular'));
$PAGE->set_title($strplural);
$PAGE->set_heading(get_string('complete:list','local_okirregular'));
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');
$PAGE->requires->css('/siteadmin/css/loading.css');
//$PAGE->requires->js('/local/competence/local_competence.js');

echo $OUTPUT->header();

$sql_select = 'SELECT 
            co.id, co.id as courseid, co.fullname, lc.certiform, lc.certiform_en, 
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

$conditions = array('lc.isnonformal=1','co.enablecompletion=1','lc.certificate=1');

if($complete){
    $conditions[] = ($complete==1)? 'cc.timecompleted > 0':'cc.timecompleted is null';
}

if($searchtext){
    $conditions[] =  $DB->sql_like('co.fullname', ':course_name');
    $params['course_name'] = '%'.$searchtext.'%';
}

$sql_where = '';
if($conditions) $sql_where = ' WHERE '.implode(' AND ',$conditions);

$enrol_courses = $DB->get_records_sql($sql_select . $sql_from . $sql_where, $params, ($page - 1) * $perpage, $perpage);
$totalcount = $DB->count_records_sql('SELECT COUNT(*) ' . $sql_from . $sql_where, $params);
//등록된 강의 목록
$user_grade = new \local_competence\local_competence_user_grades($userid);
$enrol_list = $user_grade->get_user_enrol_courses();
//등록된 강의 성적
$user_grades = $user_grade->get_course_grades();
//LCMS 진도율 목록
$user_progress = local_competence_get_course_lcms_progress($USER->id, $enrol_list);
?>  
<form class="table-search-option">
    <select title="complete" name ="complete" title="complete">
        <option value="0" <?php echo $complete == 0 ? 'selected' : ''; ?>><?php print_string('complete:status', 'local_competence') ?></option>>
        <option value="1" <?php echo $complete == 1 ? 'selected' : ''; ?>><?php print_string('complete:pass', 'local_competence') ?></option>>
        <option value="2" <?php echo $complete == 2 ? 'selected' : ''; ?>><?php print_string('complete:fail', 'local_competence') ?></option>>
    </select>
    <input type="text" title="course" name="searchtext" placeholder="<?php print_string('search:coursename', 'local_okirregular'); ?>" value="<?php echo!empty($searchtext) ? $searchtext : ''; ?>"/>
    <input type="submit" value="<?php echo get_string('search');?>" class="gray_btn" />
</form>
<table class="generaltable">
    <caption class="hidden-caption">complete view</caption>
    <thead>
        <tr>
            <th scope="row"><?php echo get_string('no','local_okirregular');?></th>
            <th scope="row"><?php echo get_string('course:name','local_okirregular');?></th>
            <th scope="row"><?php echo get_string('user:progress','local_okirregular');?></th>
            <th scope="row"><?php echo get_string('user:grade','local_okirregular');?></th>
            <th scope="row"><?php echo get_string('user:activity','local_okirregular');?></th>
            <th scope="row"><?php echo get_string('user:complete_status','local_okirregular');?></th>
            <th scope="row"><?php echo get_string('user:complete_success_date','local_okirregular');?></th>
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
                $completed = get_string('user:incomplete','local_okirregular');
                $timecompleted = '-';
                if (!empty($course->timecompleted)) {
                    $completed = get_string('user:complete','local_okirregular');
                    $timecompleted = date('Y-m-d', $course->timecompleted);
//                    if($course->certiform) $completebtn .= '<input type="button" class="blue_btn_small" value="'.get_string('lang:ko','local_okirregular').'" onclick="location.href=\''.$CFG->wwwroot.'/local/certi/certi.php?id='.$course->id.'&certiform='.$course->certiform.'\'">';
                }
                ?>
                <tr>
                    <td scope="col"><?php echo $startnum--; ?></td>
                    <td scope="col" class="title"><a href="<?php echo $CFG->wwwroot . '/course/view.php?id=' . $course->id; ?>"><?php echo $course->fullname; ?></a></td>
                    <td scope="col"><?php echo $progress; ?></td>
                    <td scope="col"><?php echo $total_average . '(' . $finalgrade . '/' . $total_grade . ')'; ?></td>
                    <td scope="col"><?php echo '<button class="activity-view gray_btn_small" courseid="' . $course->id . '">click</button>'; ?></td>
                    <td scope="col"><?php echo $completed; ?></td>
                    <td scope="col"><?php echo $timecompleted; ?></td>
                </tr>
        <?php
    }
} else {
    ?>
            <tr>
                <td scope="col" colspan="7"><?php echo get_string('course:empty','local_okirregular');?></td>
            </tr>
    <?php
}
?>

    </tbody>
</table>
<div class="table-footer-area">
<?php
local_okirregular_print_paging_navbar_script($totalcount, $page, $perpage, 'javascript:goto_page(:page);', 10);
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
                url: '<?php echo $CFG->wwwroot . '/local/okirregular/activity_grade.ajax.php'; ?>',
                method: 'POST',
                data: {
                    courseid: courseid
                },
                success: function (data) {
                    tag.html(data).dialog({
                        title: '<?php echo get_string('course:list','local_okirregular');?>',
                        modal: true,
                        width: 800,
                        resizable: false,
                        height: 400,
                        close: function () {
                            $(this).dialog('destroy').remove()
                        }
                    }).dialog('open');
                }
            });
        })
    });
</script>