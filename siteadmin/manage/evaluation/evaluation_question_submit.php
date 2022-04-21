<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib/paging.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib.php';

$mode = required_param('mode', PARAM_RAW);
$questionid = optional_param('questionid', '0', PARAM_INT);
$title = optional_param('title'," ", PARAM_RAW);
$formid = required_param('formid', PARAM_INT);
$category = required_param('categoryid', PARAM_INT);
$qtype = required_param('qtype', PARAM_INT);
$required = required_param('required', PARAM_INT);
$contents = optional_param('contents','', PARAM_RAW);
$answers = optional_param_array('answers', array(), PARAM_RAW);
$answers_etc = optional_param('answers_etc', "", PARAM_RAW);
$expression = optional_param('expression', 0, PARAM_INT);
$sortorder = required_param('sortorder', PARAM_INT);


if ($mode == 'add') {
    $data = new stdClass();
    $data->formid = $formid;
    $data->category = $category;
    $data->qtype = $qtype;
    $data->expression = $expression;
    $data->required = $required;
    $data->title = $title;
    $data->contents = $contents;
    $data->answers = "";
    foreach ($answers as $answer => $key) {
        $data->answers .= $key . "\n";
    }
    $data->answers = rtrim($data->answers, '\n');
    $data->etcname = $answers_etc;
    if (!empty($data->etcname)) {
        $data->etc = 1;
    } else {
        $data->etc = 0;
    }
    $data->sortorder = $sortorder;
    $orders = $DB->get_records('lmsdata_evaluation_questions', array('formid' => $formid, 'category' => $category), 'sortorder asc', 'sortorder');
    $found = false;
    foreach ($orders as $key => $element) {
        if ($element->sortorder == $data->sortorder) {
            $found = true;
        }
    }
    if ($found) {
        redirect('./evaluation_question_add.php?formid=' . $formid . '&categoryid=' . $category, get_string('used_order','local_lmsdata'), 2);
    } else {
        $new_form = $DB->insert_record('lmsdata_evaluation_questions', $data);
        redirect('./evaluation_categories.php?formid=' . $formid);
    }
} else if ($mode == 'modify') {
    
    $question = $DB->get_record('lmsdata_evaluation_questions', array('id' => $questionid));
    $data = new stdClass();
    $data->id = $question->id;
    $data->qtype = $qtype;
    $data->expression = $expression;
    $data->required = $required;
    $data->title = $title;
    $data->contents = $contents;
    $data->answers = "";
    foreach ($answers as $answer => $key) {
        $data->answers .= $key . "\n";
    }   
    $data->answers = rtrim($data->answers, '\n');
    $data->etcname = $answers_etc;
    if (!empty($data->etcname)) {
        $data->etc = 1;
    } else {
        $data->etc = 0;
    }
    $data->sortorder = $sortorder;
    $orders = $DB->get_records('lmsdata_evaluation_questions', array('formid' => $formid, 'category' => $category), 'sortorder asc', 'sortorder');
    $found = false;
    foreach ($orders as $key => $element) {
        if ($element->sortorder == $data->sortorder) {
            $found = true;
        }
    }
    if($data->sortorder == $question->sortorder)$found = false; 
    if ($found) {
        redirect('./evaluation_question_modify.php?formid=' . $formid . '&categoryid=' . $category.'&questionid='.$question->id, get_string('used_order','local_lmsdata'), 2);
    } else {
        $new_form = $DB->update_record('lmsdata_evaluation_questions', $data);
        redirect('./evaluation_categories.php?formid=' . $formid);
    }
}