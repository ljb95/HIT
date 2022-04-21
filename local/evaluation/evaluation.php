<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/evaluation/lib.php';
require_once $CFG->libdir . '/formslib.php';
require_once($CFG->dirroot . '/siteadmin/lib.php');

$courseid = optional_param('courseid', 0, PARAM_INT);
$type = optional_param('type', 2, PARAM_INT);
$currpage = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 20, PARAM_INT);
$year = optional_param('year', '', PARAM_RAW); 
$searchtext = optional_param('searchtext', '', PARAM_TEXT);

$context = context_system::instance();

require_login();

$PAGE->set_context($context);

$PAGE->set_url('/local/evaluation/evaluation.php');
$PAGE->set_pagelayout('standard');


$strplural = get_string("course_evaluation", "local_evaluation");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);
echo $OUTPUT->header();
$lcon = "";

$myusergroup = $DB->get_field('lmsdata_user', 'usergroup', array('userid' => $USER->id));

$rows = array (
    new tabobject('evaluation', "$CFG->wwwroot/local/evaluation/evaluation.php", get_string('course_evaluation', 'local_evaluation')),
    new tabobject('survey', "$CFG->wwwroot/local/evaluation/index.php", get_string('survey', 'local_evaluation')),
    );

$sql = "select c.id 
                from {course} c 
                join {context} ct on ct.contextlevel = 50 and ct.instanceid = c.id 
                join {role_assignments} ra on ra.contextid = ct.id 
                join {user} u on u.id = ra.userid and u.id = :userid  
            ";
$courses = $DB->get_records_sql($sql, array('userid' => $USER->id));
if($courses && !is_siteadmin()){
    $course_in = '(';
    foreach($courses as $course => $val){
        $course_in .=  $course.',';
    }
    $course_in = rtrim($course_in,',').')';
}


print_tabs(array($rows), 'evaluation');

?>
<!--<input type="text" id="input1" style="position: absolute; top:500px;left: 500px;" >-->
<table class="generaltable" summary="<?php if($year != 0){ echo $year. get_string('stats_years', 'local_lmsdata'); } else { echo ''; } ?> <?php echo get_string('survey_list', 'local_lmsdata') ?>">
    <caption class="hidden-caption"><?php echo get_string('course_evaluation', 'local_evaluation') ?></caption>
    <thead>
        <tr>
            <th scope="row" width="5%"><?php echo get_string('num', 'local_evaluation')?></th>
            <th scope="row" width="20%"><?php echo get_string('evaluationnm', 'local_evaluation')?></th>
            <th scope="row"><?php echo get_string('lecturenm', 'local_evaluation')?></th>
            <th scope="row" width="20%"><?php echo get_string('evaluationperiod', 'local_evaluation')?></th>
            <th scope="row" width="10%"><?php echo get_string('participation', 'local_evaluation')?></th>
        </tr>   
    </thead>
    <tbody>
    <?php
    $offset = ($currpage - 1) * $perpage;
    $sql_like = "";
    if (!empty($searchtext)) {
        $sql_like .= 'and f.title like :searchtxt ';
    }
    $params = array('type' => 1, 'searchtxt' => "%" . $searchtext . "%", 'time1' => time(), 'time2' => time());
    if (!is_siteadmin()) {
        $myusergroup = $DB->get_field('lmsdata_user', 'usergroup', array('userid' => $USER->id));
        $params['usergroup'] = '%' . $myusergroup . '%';
        if(!$course_in){
            $course_in = '(0)';
        }
        $sql_like .= ' and e.course in '.$course_in;
    } 
    $cnt2 = 0;
    $sql = 'select e.*,c.fullname, f.title '
            . 'from {lmsdata_evaluation} e '
            . 'join {course} c on c.id=e.course '
            . 'join {lmsdata_evaluation_forms} f on f.id = e.formid '
            . 'where e.timestart <= :time1 and e.timeend > :time2  and e.type = :type ' . $sql_like;
    $orderby = ' ORDER BY e.timestart DESC ';
    $evaluations = $DB->get_records_sql($sql . $orderby, $params, $offset, $perpage);
    $evaluations_cnt = $DB->count_records_sql('select count(*) from {lmsdata_evaluation} e where e.timestart <= :time1 and e.timeend > :time2  and e.type = :type' . $sql_like, $params);
    foreach ($evaluations as $evaluation) {
        ?>
            <tr>
                <td scope="col"><?php echo $evaluations_cnt--; ?></td>
                <td scope="col"><?php echo $evaluation->title; ?></td>
                <td scope="col" class="title"><?php echo $evaluation->fullname; ?></td>
                <td scope="col"><?php echo date("Y-m-d", $evaluation->timestart) . " ~ " . date("Y-m-d", $evaluation->timeend); ?></td>
        <?php
        $answers = $DB->get_records('lmsdata_evaluation_submits', array('evaluation' => $evaluation->id, 'userid' => $USER->id, 'completion' => 1));
        if (!($answers) && !is_siteadmin()) {
            ?>
                    <td scope="col"><input type="button" class="blue_btn_small" onclick="location.href = '<?php echo $CFG->wwwroot . "/local/evaluation/survey.php?id=" . $evaluation->id; ?>'" value="<?php echo get_string('participation', 'local_evaluation')?>"></td>
                <?php } else { ?>
                    <td scope="col"><input type="button" class="gray_btn_small" onclick="location.href = '<?php echo $CFG->wwwroot . "/local/evaluation/answers.php?id=" . $evaluation->id; ?>'" value="<?php echo get_string('viewresults', 'local_evaluation')?>"></td>
                <?php } ?>
            </tr>
                <?php
                $cnt2++;
            }
            if ($cnt2 <= 0) {
                ?>
            <tr>
                <td scope="col" colspan="5"><?php echo get_string('Explanation', 'local_evaluation')?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot></tfoot>
</table>
<div class="table-footer-area">

</div>
<?php
echo $OUTPUT->footer();
?>
