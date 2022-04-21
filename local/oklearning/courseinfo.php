<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/oklearning/lib.php';

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();

$PAGE->set_url('/local/oklearning/courseinfo.php');

$strplural = get_string("pluginnameplural", "local_oklearning");
$PAGE->navbar->add($strplural);
//$PAGE->navbar->add(get_string('course:apply','local_oklearning'));
$PAGE->navbar->add(get_string('courseinfo','local_oklearning'));
$PAGE->set_title($strplural);
$PAGE->set_heading(get_string('courseinfo','local_oklearning'));

echo $OUTPUT->header();

$courseid = required_param('id', PARAM_INT);

$course = $DB->get_record_sql("SELECT co.id, co.fullname, co.summary, co.startdate, co.lang
        , lc.subject_id, lc.timestart, lc.timeend, lc.isnonformal, lc.gubun, lc.ohakkwa
        , u.id AS prof_userid, u.username AS prof_username, CONCAT(u.firstname, u.lastname) AS prof_name, u.email AS prof_email
    FROM {course} co
    JOIN {lmsdata_class} lc ON co.id = lc.course
    JOIN {user} u ON u.id = lc.prof_userid
    WHERE co.id = :courseid", array('courseid'=>$courseid));
?>
<div class="table-filter-area">
    <button onclick="history.back();" style="cursor:pointer;"><?php echo get_string('return', 'local_oklearning'); ?></button>
</div>
<table class="generaltable">
    <tbody>
    <tr>
        <td width="20%"><?php echo get_string('course:name','local_oklearning');?></th>
        <td class="title"><?php echo $course->fullname; ?></td>
    </tr>
    <tr>
        <td><?php echo get_string('professor','local_okirregular');?></th>
        <td class="title"><?php echo $course->prof_name; ?></td>
    </tr>
    <tr>
        <td><?php echo get_string('course:open','local_okirregular');?></th>
        <td class="title">
        <?php 
        $timestart = strftime('%Y-%m-%d', $course->timestart); 
        $timeend = strftime ('%Y-%m-%d', $course->timeend);
        echo $timestart.' ~ '.$timeend;
        ?>
        </td>
    </tr>
    <tr>
        <td class="title" colspan="2">
        <?php if(empty(trim($course->summary))){
            echo get_string('course:emptyinfo', 'local_oklearning');
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
