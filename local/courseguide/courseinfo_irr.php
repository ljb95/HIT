<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/okirregular/lib.php';

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();

$PAGE->set_url('/local/courseguide/courseinfo_irr.php');

$strplural = get_string("pluginnameplural", "local_okirregular");
$PAGE->navbar->add($strplural);
$PAGE->navbar->add(get_string('course:apply','local_okirregular'));
$PAGE->navbar->add(get_string('courseinfo','local_okirregular'));
$PAGE->set_title($strplural);
$PAGE->set_heading(get_string('courseinfo','local_okirregular'));

echo $OUTPUT->header();

$courseid = required_param('id', PARAM_INT);

$course = $DB->get_record_sql("SELECT co.id, co.fullname, co.summary, co.startdate, co.lang
        , lc.subject_id, lc.timestart, lc.timeend, lc.isnonformal, lc.gubun, lc.ohakkwa
        , u.id AS prof_userid, u.username AS prof_username, u.firstname AS prof_name, u.email AS prof_email
    FROM {course} co
    JOIN {lmsdata_class} lc ON co.id = lc.course
    LEFT JOIN {user} u ON u.id = lc.prof_userid
    WHERE co.id = :courseid", array('courseid'=>$courseid));
?>
<div class="table-filter-area">
    <button onclick="history.back();" style="cursor:pointer;"><?php echo get_string('return', 'local_okirregular'); ?></button>
</div>
<table class="generaltable">
    <tbody>
    <tr>
        <th width="20%"><?php echo get_string('course:name','local_okirregular');?></th>
        <td class="title"><?php echo $course->fullname; ?></td>
    </tr>
    <tr>
        <th><?php echo get_string('professor','local_okirregular');?></th>
        <td class="title"><?php echo $course->prof_name; ?></td>
    </tr>
    <tr>
        <th><?php echo get_string('course:open','local_okirregular');?></th>
        <td class="title">
        <?php 
        $timestart = strftime('%Y-%m-%d', $course->timestart); 
        $timeend = strftime ('%Y-%m-%d', $course->timeend);
        echo $timestart.' ~ '.$timeend;
        ?>
        </td>
    </tr>
    <tr>
        <td class="" colspan="2">
        <?php if(empty(trim($course->summary))){
            echo get_string('course:emptyinfo', 'local_okirregular');
        }else{
            echo $course->summary;
        }
        ?>
        </td>
    </tr>
    </tbody>
</table>

<?php
echo $OUTPUT->footer();
?>
