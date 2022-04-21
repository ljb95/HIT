<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->libdir . '/formslib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once($CFG->dirroot.'/local/online_attendance/classes/autoloader.php');
require_once $CFG->dirroot . '/local/online_attendance/lib.php';

$id = required_param('id', PARAM_INT); // course id
$type = optional_param('type', 0, PARAM_INT); 

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);
$context = context_course::instance($course->id);
$PAGE->set_context($context);

$url = new moodle_url('/local/online_attendance/index.php', array('id' => $id, 'type' => $type));
$PAGE->set_url($url);

$PAGE->set_pagelayout('incourse');
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');
$PAGE->requires->css('/local/online_attendance/style.css');
$PAGE->requires->js('/local/online_attendance/online_attendance.js');

if($type == 2) {
    $PAGE->requires->js('/local/online_attendance/js/clockpicker.js');
    $PAGE->requires->js('/siteadmin/js/lib/jquery.ui.datepicker_lang.js');
    $PAGE->requires->css('/local/online_attendance/js/bootstrap-clockpicker.min.css');
    $PAGE->requires->css('/local/online_attendance/js/clockpicker.css');
}

$strplural = get_string("pluginnameplural", "local_online_attendance");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);

echo $OUTPUT->header();
if(has_capability('local/online_attendance:attendance_edit', $context)) {
    //tab
    $row = array();
    $row[] = new tabobject('0', new moodle_url('/local/online_attendance/index.php', array('id' => $id, 'type' => 0)), get_string('attendance:progress', 'local_online_attendance'));
    $row[] = new tabobject('1', new moodle_url('/local/online_attendance/index.php', array('id' => $id, 'type' => 1)), get_string('attendance:attendance', 'local_online_attendance'));
    $row[] = new tabobject('2', new moodle_url('/local/online_attendance/index.php', array('id' => $id, 'type' => 2)), get_string('attendance:setup', 'local_online_attendance'));
    $rows[] = $row;
    print_tabs($rows, $type);
    
    $filename = array(
                    '0' => 'manage',
                    '1' => 'attendance_book',
                    '2' => 'attendance_setup'
                );
} else {
    $filename = array(
                    '0' => 'result'
                );
}

if(array_key_exists($type, $filename)) {
    include $CFG->dirroot.'/local/online_attendance/'.$filename[$type].'.php';
} else {
    redirect($CFG->wwwroot);
}
?>
