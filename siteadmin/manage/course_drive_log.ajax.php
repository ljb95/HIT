<?php 
require (dirname(dirname(dirname(__FILE__))) . '/config.php');

$course = required_param('course', PARAM_INT);  

$context = context_system::instance();
$PAGE->set_context($context);

if(!is_siteadmin($USER)){
    redirect($CFG->wwwroot);
}

$standard_sql = ' SELECT * FROM {lmsdata_class} WHERE course = :courseid ';
$subject_sql = ' SELECT lc.* 
                 FROM {lmsdata_class_drive_log} dl
                 JOIN {lmsdata_class} lc ON lc.course = dl.subject_id 
                 WHERE dl.standard_id = :courseid ';

$params = array('courseid'=>$course);

$sdcourse = $DB->get_record_sql($standard_sql, $params);
$subject_courses = $DB->get_records_sql($subject_sql, $params);
 ?>   

<div class="popup_content" id="course_drive">
    <form id="frm_course_certificate" name="frm_course_standard" onsubmit="return false;" style="padding-left: 5px;">
        <?php
            echo '['.$sdcourse->subject_id.'] '.$sdcourse->kor_lec_name.' ('.get_stirng('standard','local_lmsdata').')</br>';
            foreach($subject_courses as $subcourse) {
                echo '['.$subcourse->subject_id.'] '.$subcourse->kor_lec_name.'</br>';
                echo '<input type="hidden" class="subcourse" value="'.$subcourse->course.'"/>';
            }
        ?>
    </form>
    <div style="border: 1px dotted #7f7f7f;padding: 5px 5px 5px 5px;margin-top: 10px;">
        <?php echo get_string('msg1','local_lmsdata'); ?>
    </div>
</div>