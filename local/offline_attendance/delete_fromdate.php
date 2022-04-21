<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$unixtime = required_param('unixtime', PARAM_RAW);


$DB->delete_records('local_off_attendance_section',array('timedate'=>$unixtime,'courseid'=>$id));
$DB->delete_records('local_off_attendance_status',array('timedate'=>$unixtime,'courseid'=>$id));


redirect('index.php?id='.$id);