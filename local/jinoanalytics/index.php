<?php
require_once '../../config.php';
require_once('./locallib.php');
$pluginname = 'local_jinoanalytics';

$config = get_config('local_analytics');
if (!empty($config->modules)) $display_modules = explode(',',$config->modules);
else $display_modules = array('assign', 'forum', 'quiz', 'wiki', 'feedback', 'url', 'book', 'resource');

$id = required_param('id', PARAM_INT);
$tab = optional_param('tab','tab1', PARAM_RAW);

// 파라메터
$param = array();
$param['id'] = $id;
$param['tab'] = $tab;

// TAB 정보
$tabs = array();
$tabs['tab1'] = new tabobject('tab1', new moodle_url('/local/jinoanalytics/index.php', array('id'=>$id, 'tab'=>'tab1')), get_string('tab1', 'local_jinoanalytics'));
$tabs['tab2'] = new tabobject('tab2', new moodle_url('/local/jinoanalytics/index.php', array('id'=>$id, 'tab'=>'tab2')), get_string('tab2', 'local_jinoanalytics'));
// $tabs['tab3'] = new tabobject('tab3', new moodle_url('/local/jinoanalytics/index.php', array('id'=>$id, 'tab'=>'tab3')), get_string('tab3', 'local_jinoanalytics'));
$tabs['tab4'] = new tabobject('tab4', new moodle_url('/local/jinoanalytics/index.php', array('id'=>$id, 'tab'=>'tab4')), get_string('tab4', 'local_jinoanalytics'));
$title = $tabs[$tab]->title;

// 
if (! $course = $DB->get_record("course", array("id" => $id))) {
    print_error('coursemisconf');
}
context_helper::preload_course($course->id);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);



$PAGE->set_pagelayout('course');
$PAGE->set_url('/local/jinoanalytics/', $param);
$returnurl = base64_encode($PAGE->url->out());
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_pagetype('local_jinoanalytics');
$PAGE->navbar->add(get_string('pluginname', $pluginname), new moodle_url('/local/jinoanalytics/index.php',array('id'=>$id,'tab'=>'tab1')));
$PAGE->navbar->add($title, $PAGE->url->out());
$current_language = current_language();
$PAGE->requires->js_init_call('M.local_jinoanalytics.index');
$PAGE->requires->js('/local/jinoanalytics/javascript/chart/highcharts.js');
$PAGE->requires->js('/local/jinoanalytics/javascript/chart/highcharts-3d.js');
$PAGE->requires->js('/local/jinoanalytics/javascript/chart/modules/exporting.js');
$PAGE->requires->js('/local/jinoanalytics/javascript/chart/themes/grid-light.js');
/*
<script src="/local/jinoanalytics/javascript/chart/highcharts.js"></script>
<script src="/local/jinoanalytics/javascript/chart/highcharts-3d.js"></script>
<script src="/local/jinoanalytics/javascript/chart/modules/exporting.js"></script>
<script src="/local/jinoanalytics/javascript/chart/themes/grid-light.js"></script>
*/
echo $OUTPUT->header();
echo html_writer::start_div('jinoanalytics jinoanalytics-list');
echo $OUTPUT->heading($title, 2, 'pagetitle');
echo $OUTPUT->tabtree($tabs, $tab);
include('./include/analytics_'.$tab.'.php');
echo html_writer::end_div();
echo $OUTPUT->footer();
