<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/okregular/lib.php';

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();

$strplural = get_string("pluginnameplural", "local_okregular");
$PAGE->navbar->add($strplural);
$PAGE->navbar->add(get_string('sititon:apply','local_okregular'));
$PAGE->navbar->add(get_string('courseinfo','local_okregular'));
$PAGE->set_title($strplural);
$PAGE->set_heading(get_string('courseinfo','local_okregular'));

echo $OUTPUT->header();

$courseid = required_param('id', PARAM_INT);

$PAGE->set_url('/local/okregular/courseinfo.php', array('id' => $courseid));

$course = $DB->get_record_sql("SELECT co.id, co.fullname, co.summary, co.startdate, co.lang
        , lc.subject_id, lc.timestart, lc.timeend, lc.isnonformal, lc.gubun, lc.ohakkwa
        , u.id AS prof_userid, u.username AS prof_username, u.firstname AS prof_name, u.email AS prof_email
    FROM {course} co
    JOIN {lmsdata_class} lc ON co.id = lc.course
    JOIN {user} u ON u.id = lc.prof_userid
    WHERE co.id = :courseid", array('courseid'=>$courseid));
?>
<div class="table-filter-area">
    <button onclick="history.back();" style="cursor:pointer;"><?php echo get_string('return', 'local_okregular'); ?></button>
</div>
<table class="generaltable">
    <caption class="hidden-caption">강의정보</caption>
    <tbody>
    <tr>
        <th width="20%"><?php echo get_string('course:name','local_okregular');?></th>
        <td class="title"><?php echo $course->fullname; ?></td>
    </tr>
    <tr>
        <th><?php echo get_string('professor','local_okregular');?></th>
        <td class="title"><?php echo $course->prof_name; ?></td>
    </tr>
    <tr>
        <th><?php echo get_string('course:open','local_okregular');?></th>
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
             echo get_string('course:emptyinfo', 'local_okregular');
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
