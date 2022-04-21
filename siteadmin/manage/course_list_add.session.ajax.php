<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';

if(!is_siteadmin($USER)){
    redirect($CFG->wwwroot);
}

global $SESSION;
$course_list    = optional_param_array('data', array(), PARAM_INT);

$courses =  $SESSION->split_course;
if(empty($courses)) {
    if(is_array($course_list)) {
       $SESSION->split_course = $course_list;
    }
} else {
    foreach ($course_list as $course) {
        if(!in_array($course, $courses)) {
            array_push($courses, $course);
        }
    }
    $SESSION->split_course = $courses;
}