<?php 
require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/competence/classes/user_grades.php';

$courseid = required_param('courseid', PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);

$sql = ' SELECT cm.id,cm.course, cm.instance, 
            cs.section, mo.name, 
            gi.grademax, 
            gg.finalgrade
        FROM {course_modules} cm
        JOIN {course_sections} cs ON cs.id = cm.section
        JOIN {modules} mo ON mo.id = cm.module
        LEFT JOIN {grade_items} gi ON gi.courseid = cm.course AND gi.itemmodule = mo.name AND cm.instance = gi.iteminstance 
        LEFT JOIN {grade_grades} gg ON gg.itemid = gi.id AND gg.userid = :userid
        WHERE cm.course = :courseid  ORDER BY cs.section ASC ';
$param = array(
            'userid' => $USER->id,
            'courseid' => $courseid
        );

$activitys = $DB->get_records_sql($sql, $param);
?>

<div class="popup_content" id="course_prof">
    <form id="frm_course_certificate" name="frm_course_certificate" onsubmit="return false;">
        <table cellpadding="0" cellspacing="0" class="generaltable">
            <tbody>
            <tr>
                <th><?php echo get_string('weeks', 'local_competence')?></th>
                <th><?php echo get_string('activityname', 'local_competence')?></th>
                <th><?php echo get_string('score', 'local_competence')?></th>
                <th><?php echo get_string('fullmarks', 'local_competence')?></th>
            </tr>
            <?php
            if(count($activitys) > 0) {
                foreach($activitys as $activity) {
                    $sql = ' SELECT name FROM {'.$activity->name.'} WHERE id = :id '; 
                    $name = $DB->get_field_sql($sql, array('id' => $activity->instance));
                    $grade = round($activity->finalgrade, 1);
                    if(is_null($grade)) {
                        $grade = '-';
                    }
                    $grademax = round($activity->grademax, 1);
                    if(is_null($grademax)) {
                        $grademax = '-';
                    }
                    echo '<tr>';
                    echo '<td>'.$activity->section.'</td>';
                    echo '<td>'.$name.'</td>';
                    echo '<td>'.$grade.'</td>';
                    echo '<td>'.$grademax.'</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="6">'. get_string('noactivity', 'local_competence').'</td></tr>';
            }
            ?>
            </tbody>
        </table>
    </form>
</div>
