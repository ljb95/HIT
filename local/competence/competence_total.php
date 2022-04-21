<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/courselist/lib.php';
require_once $CFG->dirroot . '/local/competence/lib.php';
require_once $CFG->dirroot . '/local/competence/classes/user_competencies.php';
require_once $CFG->dirroot . '/local/competence/classes/user_grades.php';
require_once($CFG->dirroot . '/siteadmin/lib.php');

$userid = optional_param('userid', $USER->id, PARAM_INT);
$frameid = optional_param('fid', 0, PARAM_INT);

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
$PAGE->requires->css('/local/competence/style.css');

echo $OUTPUT->header();
$sql_select = 'SELECT 
            co.id, co.id as courseid, co.fullname,
            CASE WHEN cc.timecompleted IS NULL THEN 0 ELSE cc.timecompleted END AS timecompleted ';
$sql_from ='FROM {course} co 
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

$enrol_courses = $DB->get_records_sql($sql_select.$sql_from, $params, ($page-1)*$perpage, $perpage);
$totalcount = $DB->count_records_sql('SELECT COUNT(*) '.$sql_from.$sql_where, $params);

// 역량 목록
$user_competencies = new \local_competence\local_competence_user_competencies();
$competencies = $user_competencies->get_course_competencies();

//프레임웍 목록
$frameworks = local_competence_get_framework_list($competencies);
//역량 현황
$statuses = local_competence_get_status($competencies, $frameid);
?>
<h1 class="course_h1" ><?php echo $strplural; ?></h1>
<div class="head-button" style="float: right">
    <input type="button" value="<?php echo get_string('goback', 'local_competence')?>" onclick="history.back()" />
    <input type="button" value="<?php echo get_string('mylearningstatus', 'local_competence')?>" onclick="document.location.href='<?php echo $CFG->wwwroot.'/local/competence/index.php' ;?>'" />
</div>
<div class="">       
    <form>
        <select name="fid" onchange="this.form.submit()">
            <?php
            $frameworks[0] = get_string('year:all', 'local_competence');
            ksort($frameworks);
            foreach ($frameworks as $id => $frameworkname) {
                $selected = ($frameid == $id) ? 'selected' : '';
                echo '<option value="' . $id . '" ' . $selected . '>' .$frameworkname . '</option>';
            }
            ?>
        </select>
    </form>
</div>
<?php 
    foreach($frameworks as $fid => $fname) {
        if($fid != 0) {
   ?>
    <h2 class="course_h2" ><?php echo $frameworks[$frameid]; ?></h2>
    <table class="generaltable competence_total">
        <thead>
            <tr>
                <th class="centeralign header c0" scope="col"><?php echo get_string('competencyname', 'local_competence')?></th>
                <th class="centeralign header c1" scope="col"><?php echo get_string('achievementcapability', 'local_competence')?></th>
            </tr>   
        </thead>
        <tbody>
            <?php 

                if(!empty($totalcount)) {
                    foreach($statuses as $status) {
                        $shortname = $status->shortname;
                        $depth = $status->depth;
                        if($depth != 1) {
                            $shortname = '> '.$shortname;
                        }
                        
                ?>
                    <tr>
                        <td class="centeralign cell c0 left <?php echo 'depth_'.$depth; ?>"><?php echo '<span>'.$shortname.'</sapn>'; ?></td>
                        <td class="centeralign cell c1"><?php echo $status->completecount.'/'.$status->setcount; ?></td>
                    </tr>
                <?php
                    }
                } else {
                ?>
                <tr>
                    <td colspan="2"><?php echo get_string('nocompetencies', 'local_competence')?></td>
                </tr>
                <?php   
                
                }
                ?>

        </tbody>
    </table>
    <?php 
        }
    } 
    ?>
    
<div class="table-footer-area">
</div>
    
<?php
echo $OUTPUT->footer();
?>

