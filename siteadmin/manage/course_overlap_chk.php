<?php
require (dirname(dirname(dirname(__FILE__))) . '/config.php');
$course = optional_param('courseid', 0, PARAM_INT);
$course_code = optional_param('course_code', '', PARAM_RAW);
$bunban = optional_param('bunban', '', PARAM_RAW);

$sql = 'SELECT * FROM {lmsdata_class} where subject_id = :subject_id and bunban = :bunban and course != :course';
$chkcourse = $DB->get_records_sql($sql, array('subject_id'=>$course_code, 'bunban'=>$bunban, 'course'=>$course));

if($chkcourse){
    echo 'no';
} else {
    echo 'ok';
}