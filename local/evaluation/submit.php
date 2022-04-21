<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/evaluation/lib.php';

$id = optional_param('id', 0, PARAM_INT);
$temp = optional_param('temp', 1, PARAM_INT);
$evaluation = optional_param('evaluation', 0, PARAM_INT);

$evaluation = $DB->get_record('lmsdata_evaluation', array('id' => $evaluation));

$submit_history = $DB->get_record('lmsdata_evaluation_submits',array('evaluation'=>$evaluation->id,'userid'=>$USER->id));

$submit = new stdClass();
$submit->evaluation = $evaluation->id;
$submit->questions = serialize($_POST);
$submit->completion = ($temp ==1)?0:1;
$submit->userid = $USER->id;
$submit->timemodified = time();
if(empty($submit_history)){
    $submit->timecreated = time();
    $DB->insert_record('lmsdata_evaluation_submits',$submit);
    $history = new stdClass();
    $history->evaluation = $evaluation->id;
    $history->userid = $USER->id;
    $history->timecreated = time();
    $history->timemodified = time();
    $DB->insert_record('lmsdata_evaluation_history',$history);
} else {
    $submit->id = $submit_history->id;
    $DB->update_record('lmsdata_evaluation_submits',$submit);
    $DB->delete_records('lmsdata_evaluation_answers',array('evaluation' => $evaluation->id, 'userid' => $USER->id,'course'=>$id));
}
unset($_POST['temp']);

$data = new stdClass();
$data->course = $id;
$data->evaluation = $evaluation->id;
$data->userid = $USER->id;
$data->timecreated = time();
$data->timemodified = time();
foreach ($_POST as $key => $val) {
    if(!preg_match('/etc/',$key)){
        $qid = explode('question',$key);
    } else {
        $qid = explode('question_etc',$key);    
    }
    $answer = "";
    $data->question = $qid[1];
    if (is_array($val)) {
        foreach ($val as $va => $v) {
            if($v == ETCVALUE)continue;
            if (preg_match('/question_etc/', $key)) {
                $data->etcanswers = $v;
                $data->answers = null;
            } else {
                $answer .= $v.",";
                $data->etcanswers = null;
            }
           
        } 
         $data->answers = rtrim($answer,',');
         if(!empty($data->answers) || !empty($data->etcanswers)){
            $DB->insert_record('lmsdata_evaluation_answers',$data);
         }
    } else {
        if($val == ETCVALUE)continue;
        if (preg_match('/question_etc/', $key)) {
            $data->etcanswers = $val;
            $data->answers = null;
        } else {
            $data->answers = $val;
            $data->etcanswers = null;
        }
        if(!empty($data->answers) || !empty($data->etcanswers)){
            $DB->insert_record('lmsdata_evaluation_answers',$data);
        }
    } 
}
if($evaluation->type==1){
    redirect('evaluation.php');
}else{
    redirect('index.php');
}