<?php

define("ETCVALUE",'[e!s@c#a$p%e]e!t@v#a$l%u^e');

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function print_checkbox_form($answers, $question , $val) {
    if(!is_object($val)){
        $val = new stdClass();
        $val->answers = '';
        $val->etcanswers = '';
    }
    $required = ($question->required == 1)?'required':'';
    $allow_required = ($question->required == 1)?'data-required="1"':'data-required="0"';
    $chcked_val = preg_split('/,/',$val->answers);
    foreach ($answers as $answer => $value) {
        $checked = '';
        foreach($chcked_val as $key => $v){
            if($v == $value) $checked = 'checked';
            
        }
        echo '&nbsp;<label class="inline"><input type="checkbox" '.$checked.' '.$allow_required.' class="'.$required.'" data-id="'.$question->id.'" value="'.$value.'" name="question' . $question->id . '[]">';
        echo $value."</label>";
        if ($question->expression == 2) {    // 1가로 2세로
            echo '<br>';
        }
    }
    if($question->etc != 0){
        $display = (empty($val->etcanswers))?'style="display:none"':'';
        $checked = (empty($val->etcanswers))?'':'checked';
        echo '&nbsp;<input type="checkbox" '.$allow_required.' class="'.$required.'" data-id="'.$question->id.'" id="etc_'.$question->id.'" '.$checked.' onclick="etc_on('.$question->id.')" value="'.ETCVALUE.'" name="question' . $question->id . '[]"> '.$question->etcname;
        echo '<span class="etc_'.$question->id.'" '.$display.'>&nbsp;<input type="text" data-use="0" placeholder="etc" value="'.$val->etcanswers.'" name="question_etc' . $question->id . '"><span>';    }
}
function print_radio_form($answers, $question , $val) {
    if(!is_object($val)){
        $val = new stdClass();
        $val->answers = '';
        $val->etcanswers = '';
    }
    $required = ($question->required == 1)?'required':'';
    $allow_required = ($question->required == 1)?'data-required="1"':'data-required="0"';
    foreach ($answers as $answer => $value) {
        $checked = ($val->answers == $value)?'checked':'';
        echo '&nbsp;<label class="inline"><input type="radio" '.$allow_required.' class="'.$required.'" '.$checked.' value="'.$value.'"  onclick="etc_on('.$question->id.')" name="question' . $question->id . '">';
        echo $value."</label>";
        if ($question->expression == 2) {    // 1가로 2세로
            echo '<br>';
        }
    }
        if($question->etc != 0){
        $display = (empty($val->etcanswers))?'style="display:none"':'';
        $checked = (empty($val->etcanswers))?'':'checked';
        echo '&nbsp;<input type="radio" class="'.$required.'" id="etc_'.$question->id.'" onclick="etc_on('.$question->id.')" '.$allow_required.' '.$checked.' value="'.ETCVALUE.'" name="question' . $question->id . '"> '.$question->etcname;
        echo '<span class="etc_'.$question->id.'" '.$display.' >&nbsp;<input type="text" data-use="0" placeholder="etc" value="'.$val->etcanswers.'" name="question_etc' . $question->id . '"><span>';
    }
}
function print_text_form($question , $val){
    if(!is_object($val)){
        $val = new stdClass();
        $val->answers = '';
        $val->etcanswers = '';
    }
    $required = ($question->required == 1)?'required':'';
    $allow_required = ($question->required == 1)?'data-required="1"':'data-required="0"';
     echo '&nbsp;&nbsp;&nbsp;<input type="text" class="question_text '.$required.'" '.$allow_required.' value="'.$val->answers.'" size="50" name="question' . $question->id . '">';
}
function print_textarea_form($question  , $val){
    if(!is_object($val)){
        $val = new stdClass();
        $val->answers = '';
        $val->etcanswers = '';
    }
    $required = ($question->required == 1)?'required':'';
    $allow_required = ($question->required == 1)?'data-required="1"':'data-required="0"';
     echo '&nbsp;&nbsp;&nbsp;<textarea class="question_textarea '.$required.'" '.$allow_required.' name="question' . $question->id . '">'.$val->answers.' </textarea>';
}

function print_fiveselect_form($answers,$question  , $val){
    if(!is_object($val)){
        $val = new stdClass();
        $val->answers = '';
        $val->etcanswers = '';
    }
    $required = ($question->required == 1)?'required':'';
    $allow_required = ($question->required == 1)?'data-required="1"':'data-required="0"';
    $i = 5;
    foreach ($answers as $answer => $value) {
        $checked = ($val->answers == $i)?'checked':'';
        echo '&nbsp;<label class="inline"><input type="radio" class="'.$required.'" value="'.$i.'" '.$allow_required.' '.$checked.' name="question' . $question->id . '">';
        echo $value."</label>";
        if ($question->expression == 2) {    // 1가로 2세로
            echo '<br>';
        }
        $i--;
    }
}


function print_textint_form($question , $val){
    if(!is_object($val)){
        $val = new stdClass();
        $val->answers = '';
        $val->etcanswers = '';
    }
    $required = ($question->required == 1)?'required':'';
    $allow_required = ($question->required == 1)?'data-required="1"':'data-required="0"';
     echo '&nbsp;&nbsp;&nbsp;<input type="text" onkeypress="return event.charCode >= 48 && event.charCode <= 57" value="'.$val->answers.'"  class="question_text '.$required.'" size="10" '.$allow_required.' name="question' . $question->id . '">';
}

    function get_questions($formid){
        global $DB;
        $fiveselect_question_sql = "select * from {lmsdata_evaluation_questions} where formid = :formid and qtype = 5 order by sortorder asc";
        $fiveselects = $DB->get_records_sql($fiveselect_question_sql, array('formid' => $formid));
        $question_sql = "select * from {lmsdata_evaluation_questions} where formid = :formid and qtype != 5 and category = 0 order by sortorder asc";
        $questions = $DB->get_records_sql($question_sql, array('formid' => $formid));
        return array($fiveselects,$questions);
    }