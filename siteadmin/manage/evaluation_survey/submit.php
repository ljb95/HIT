<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib/paging.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib.php';


$mode = required_param('mode', PARAM_RAW);
$id = optional_param('id',0, PARAM_INT);

$formid = optional_param('formid', '0', PARAM_INT);
$userid = required_param('userid', PARAM_INT);

$compulsion = optional_param('compulsion',0, PARAM_INT);
$targets = optional_param_array('targets',array(), PARAM_INT);
$formid = optional_param('formid',0, PARAM_INT);

$timestart = strtotime(required_param('starttime', PARAM_RAW));
$timeend = strtotime(required_param('endtime', PARAM_RAW));

if ($mode == 'add') {
    $data = new stdClass();
    $data->type = 2;
    $data->formid = $formid;
    $data->course = 0;
    
    $data->compulsion = $compulsion;
    
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
    $targets = array_filter($targets);
    if(!empty($targets)){
        $data->targets = '';
        foreach ($targets as $target => $val) {
            $data->targets .= $val . ",";
        }
        $data->targets = rtrim($data->targets,",");
    }
    
    $data->compulsion = $compulsion;

    $data->timestart = $timestart;
    $data->timeend = $timeend; 

    $data->timemodified = time();
    
    $new_form = $DB->update_record('lmsdata_evaluation', $data);
}
redirect('survey_list.php');
