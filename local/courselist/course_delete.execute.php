<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot . "/siteadmin/lib.php");

global $DB;

$courseid = required_param('id', PARAM_INT);

$usergroup = $DB->get_field('lmsdata_user', 'usergroup', array('userid' => $USER->id));

if(!is_siteadmin($USER) && ($usergroup != 'pr') && ($usergroup != 'sa')){
    die; 
}


$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$coursecontext = context_course::instance($course->id);

delete_course($course);

add_class_drive_log($course->id, $course->id, LMSDATA_CLASS_DELETE_EXECUTE);