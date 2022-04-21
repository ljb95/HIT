<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot.'/local/courselist/lib.php';
require_once $CFG->dirroot.'/my/lib.php';
require_once $CFG->dirroot.'/lib/coursecatlib.php';
require_once $CFG->dirroot.'/blocks/course_overview/locallib.php';
require_once $CFG->dirroot.'/blocks/course_overview/renderer.php';

global $OUTPUT;


require_login();

$userid = $USER->id;  // Owner of the page
$context = context_user::instance($USER->id);
$PAGE->set_blocks_editing_capability('moodle/my:manageblocks');
$strmymoodle = get_string('course_notice','local_courselist');
$header = "$SITE->shortname: $strmymoodle";

// Get the My Moodle page info.  Should always return something unless the database is broken.

$PAGE->set_context($context);

$PAGE->set_url('/local/courselist/courseoverview.php');
$PAGE->set_pagelayout('standard');
/*
$PAGE->blocks->add_region('content');
$PAGE->set_blocks_editing_capability('moodle/my:manageblocks');

$strplural = get_string("course:overview", "local_courselist");
$PAGE->navbar->ignore_active();
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);
$PAGE->set_pagetype('local-courselist-courseoverview');
$PAGE->blocks->add_region('content');
*/
echo $OUTPUT->header();

$courses = enrol_get_my_courses();
if(!empty($courses)) {
    $overview = block_course_overview_get_overviews($courses);

    //$overviewclass = new theme_creativeband_block_course_overview_renderer($PAGE, null);
    $overviewclass = new block_course_overview_renderer($PAGE, null);

    echo $overviewclass->course_overview_jm($courses, $overview);
}else {
    echo get_string('course:empty', 'local_courselist');
}

echo $OUTPUT->footer();