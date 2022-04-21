<?php 
require (dirname(dirname(dirname(__FILE__))) . '/config.php');

$courses = required_param_array('course', PARAM_INT);  
$mode = optional_param('mode', null, PARAM_RAW);

$context = context_system::instance();
$PAGE->set_context($context);

if(!is_siteadmin($USER)){
    redirect($CFG->wwwroot);
}
if(empty($mode)) {

    $sql_select = "SELECT lc.* ";
    $sql_from = " FROM {lmsdata_class} lc ";
    list($sql, $params) = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED, 'course');
    $sql_in = ' WHERE lc.course '. $sql;
    $sql_order = " ORDER BY lc.sbb";
    
    $lmsdata_courses = $DB->get_records_sql($sql_select.$sql_from.$sql_in.$sql_order, $params);
 ?>   

<div class="popup_content" id="course_drive">
    <form id="frm_course_certificate" name="frm_course_standard" onsubmit="return false;" style="padding-left: 5px;">
        <?php
            $count = 0;
            foreach($lmsdata_courses as $course) {
                echo '<input type="radio" name="course_standard" value="'.$course->course.'" style="margin-right: 10px;">['.$course->subject_id.'] '.$course->kor_lec_name.'</br>';
            }
        ?>
    </form>
    <div style="border: 1px dotted #7f7f7f;padding: 5px 5px 5px 5px;">
        <input type="radio" name="flag" value="0" checked/><?php echo get_string('msg1','local_lmsdata'); ?>
        </br>
        <input type="radio" name="flag" value="1" /><?php echo get_string('msg3','local_lmsdata'); ?>
    </div>
</div>
<?php
}
?>