<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib/paging.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib.php';

$mode = required_param('mode', PARAM_RAW);

$id = optional_param('id',0, PARAM_INT);
$courseid = optional_param('courseid',0, PARAM_INT);
$formid = optional_param('formid', 0, PARAM_INT);
$userid = required_param('userid', PARAM_INT);

$targets = optional_param('targets','', PARAM_RAW);
$formid = optional_param('formid',0, PARAM_INT);

$timestart = strtotime(required_param('starttime', PARAM_RAW));
$timeend = strtotime(required_param('endtime', PARAM_RAW));

if ($mode == 'add') {
    $data = new stdClass();
    $data->type = 1;
    $data->formid = $formid;
    $data->course = $courseid;
    
    $data->targets = '';
    foreach ($targets as $target => $val) {
        $data->targets .= $val . ",";
    }
    $data->targets = rtrim($data->targets,",");
    
    $data->timestart = $timestart;
    $data->timeend = $timeend;
    $data->userid = $userid;

    
    $data->timecreated = time();
    $data->timemodified = time();
    
    $new_form = $DB->insert_record('lmsdata_evaluation', $data);
} else if ($mode == 'modify') {
    $data = new stdClass();
    $data->id = $id;
    if($formid != 0){
    $data->formid = $formid;
    }
    if($courseid != 0){
    $data->course = $courseid;
    }

    if(!empty($targets)){
        $data->targets = $targets;
    }
    
    
    $data->timestart = $timestart;
    $data->timeend = $timeend;

    $data->timemodified = time();

    $new_form = $DB->update_record('lmsdata_evaluation', $data);
}

redirect('evaluation_list.php');
