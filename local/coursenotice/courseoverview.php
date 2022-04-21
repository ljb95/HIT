<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot.'/local/courselist/lib.php';
//require_once $CFG->dirroot.'/lib/coursecatlib.php';
require_once $CFG->dirroot.'/blocks/course_overview/locallib.php';
require_once $CFG->dirroot.'/blocks/course_overview/renderer.php';
require_once $CFG->dirroot.'/local/courselist/renderer.php';
 
global $OUTPUT;

/**
 * Display overview for courses
 *
 * @param array $courses courses for which overview needs to be shown
 * @return array html overview
 */
function block_course_overview_get_overviews_local($courses) {
    $htmlarray = array();
    if ($modules = get_plugin_list_with_function('mod', 'print_overview')) {
        // Split courses list into batches with no more than MAX_MODINFO_CACHE_SIZE courses in one batch.
        // Otherwise we exceed the cache limit in get_fast_modinfo() and rebuild it too often.
        if (defined('MAX_MODINFO_CACHE_SIZE') && MAX_MODINFO_CACHE_SIZE > 0 && count($courses) > MAX_MODINFO_CACHE_SIZE) {
            $batches = array_chunk($courses, MAX_MODINFO_CACHE_SIZE, true);
        } else {
            $batches = array($courses);
        }
        foreach ($batches as $courses) {
            foreach ($modules as $fname) {
                $fname($courses, $htmlarray);
            }
        }
    }
    return $htmlarray;
}
require_login();

$userid = $USER->id;  // Owner of the page
$context = context_user::instance($USER->id);
$PAGE->set_blocks_editing_capability('moodle/my:manageblocks'); 
$strmymoodle = get_string('course_notice','local_courselist');
$header = "$SITE->shortname: $strmymoodle";

// Get the My Moodle page info.  Should always return something unless the database is broken.

$PAGE->set_context($context);
$PAGE->navbar->add(get_string("mypage", "local_courselist"), new moodle_url($CFG->wwwroot.'/local/courselist/course_manage.php'));
$PAGE->navbar->add($strmymoodle);
$PAGE->set_url('/local/coursenotice/courseoverview.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_title($strmymoodle);
$PAGE->set_heading($strmymoodle);

echo $OUTPUT->header();


$courses = enrol_get_my_courses(NULL,'timemodified DESC,sortorder ASC');
if(!empty($courses)) {
    $overview = block_course_overview_get_overviews($courses);

    $overviewclass = new local_courselist_block_course_overview_renderer($PAGE, null);

    echo $overviewclass->course_overview($courses, $overview);
}else {
    echo get_string('course:empty', 'local_courselist');
}

echo $OUTPUT->footer();