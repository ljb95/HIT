<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/courselist/lib.php';

require_login();

$courseid = required_param('id', PARAM_INT);

$course = $DB->get_record_sql("SELECT co.id, co.fullname, co.summary, co.startdate, co.lang
        , lc.subject_id, lc.timestart, lc.timeend, lc.isnonformal, lc.gubun, lc.ohakkwa
        , u.id AS prof_userid, u.username AS prof_username, u.firstname AS prof_name, u.email AS prof_email
    FROM {course} co
    JOIN {lmsdata_class} lc ON co.id = lc.course
    JOIN {user} u ON u.id = lc.prof_userid
    WHERE co.id = :courseid", array('courseid'=>$courseid));
?>
<table style="width: 600px">
    <tr>
        <th><?php echo get_string('lecture','local_courselist');?></th>
        <td><?php echo $course->fullname; ?></td>
    </tr>
    <tr>
        <th><?php echo get_string('course:professo','local_courselist');?></th>
        <td><?php echo $course->prof_name; ?></td>
    </tr>
    <tr>
        <th><?php echo get_string('course:star','local_courselist');?></th>
        <td><?php echo strftime ('%Y-%m-%d', $course->timestart); ?></td>
    </tr>
    <tr>
        <th><?php echo get_string('course:end','local_courselist');?></th>
        <td><?php echo strftime ('%Y-%m-%d', $course->timeend); ?></td>
    </tr>
    <tr>
        <th><?php echo get_string('lang','local_courselist');?></th>
        <td><?php echo $course->gubun == 2 ? '영문': '국문'; ?></td>
    </tr>
</table>
