<?php
require_once '../../config.php';


$pluginname = 'local_jinoanalytics';
$title = get_string('pluginname', $pluginname);

$PAGE->set_url('/local/jinoanalytics/view.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title($title);
$PAGE->set_pagetype('local_jinoanalytics');
$PAGE->navbar->add(get_string('pluginname', $pluginname), $CFG->wwwroot.'/local/jinoanalytics/index.php');
$PAGE->navbar->add($title, $PAGE->url->out());

$PAGE->requires->js_init_call('M.local_jinoanalytics.view');

echo $OUTPUT->header();

echo $OUTPUT->footer();