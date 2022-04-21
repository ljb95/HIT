<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/okmanage/manage_form.php';

$id = optional_param('id', 0, PARAM_INT);  // Course ID
$search = optional_param('search', '', PARAM_CLEAN);  // Course ID

$context = get_context_instance(CONTEXT_COURSE, $id);

require_login();

$PAGE->set_context($context);
$PAGE->set_url('/local/okmanage/index.php?id=' . $id);
$PAGE->set_pagelayout('incourse');


$course = get_course($id);
$PAGE->set_course($course);


if (!has_capability('moodle/course:manageactivities', $context)) {
    return;
}

$strplural = get_string("pluginname", "local_okmanage");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);

$mform = new okmanage_form(null,array($course));
$courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
if ($mform->is_cancelled()) {
    redirect($courseurl);
} else if ($fromform = $mform->get_data()) {

    $class = $DB->get_record('lmsdata_class',array('course'=>$fromform->id));    
    
    $lmsdata_class = new stdClass();
    $lmsdata_class->id = $class->id;
    $lmsdata_class->notice = $fromform->notice;
    $lmsdata_class->useprogress = $fromform->useprogress;
    $lmsdata_class->onlineattendance = $fromform->onlineatt;
    $lmsdata_class->attendanceoption = $fromform->attstandard;
    $lmsdata_class->offlineattendance = $fromform->offatt;
    $DB->update_record('lmsdata_class',$lmsdata_class);
    
    update_course($fromform, $editoroptions);
        // Set the URL to take them too if they choose save and display.
    redirect($courseurl);
} else {

$lmsdata_class = $DB->get_record('lmsdata_class',array('course'=>$id));    

$course->notice = $lmsdata_class->notice;
$course->useprogress = $lmsdata_class->useprogress;
$course->onlineatt = $lmsdata_class->onlineattendance;
$course->attstandard = $lmsdata_class->attendanceoption;
$course->offatt = $lmsdata_class->offlineattendance;

$mform->set_data($course);

echo $OUTPUT->header();

echo $mform->display();

echo $OUTPUT->footer();

}