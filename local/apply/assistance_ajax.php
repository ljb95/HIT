<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/theme/remui/classes/my_render.php');

$id = required_param('id', PARAM_INT);


$course = get_course($id);
$PAGE->set_course($course);

course_create_sections_if_missing($course, 0);

$modinfo = get_fast_modinfo($course);
$course = course_get_format($course)->get_course();

$context = context_course::instance($course->id);
// Title with completion help icon.
$completioninfo = new completion_info($course);

$course_render = new preview_course_renderer($PAGE);

foreach ($modinfo->get_section_info_all() as $section => $thissection) {
    $sec_name = get_section_name($course,$section);
    echo '<h4 class="preview_h4">'.$sec_name.'</h4>';
    echo $course_render->course_section_cm_list($course, $thissection, $displaysection);
}