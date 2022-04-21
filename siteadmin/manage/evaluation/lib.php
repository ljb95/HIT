<?php

function print_radio_form($answers, $question) {
    foreach ($answers as $answer => $value) {
        echo '&nbsp;<input type="radio" title="radio" name="question' . $question->id . '">';
        echo $value;
        if ($question->expression == 2) {    // 1가로 2세로
            echo '<br>';
        }
    }
    if($question->etc != 0){
        echo '&nbsp;<input type="radio" title="radio" name="question' . $question->id . '">';
        echo $question->etcname;
    }
}
function print_checkbox_form($answers, $question) {
    foreach ($answers as $answer => $value) {
        echo '&nbsp;<input type="checkbox" title="checkbox" name="question' . $question->id . '">';
        echo $value;
        if ($question->expression == 2) {    // 1가로 2세로
            echo '<br>';
        }
    }
    if($question->etc != 0){
        echo '&nbsp;<input type="checkbox" title="checkbox" name="question' . $question->id . '">';
        echo $question->etcname;
    }
}
function print_text_form($question){
     echo '&nbsp;&nbsp;&nbsp;<input type="text" title="question01" class="question_text" size="50" name="question' . $question->id . '">';
}
function print_textarea_form($question){
     echo '&nbsp;&nbsp;&nbsp;<textarea class="question_textarea" title="question02"  name="question' . $question->id . '"></textarea>';
}

function print_fiveselect_form($answers,$question){
    foreach ($answers as $answer => $value) {
        echo '&nbsp;<input type="radio" title="radio" name="question' . $question->id . '">';
        echo $value;
        if ($question->expression == 2) {    // 1가로 2세로
            echo '<br>';
        }
    }
}


function print_textint_form($question){
     echo '&nbsp;&nbsp;&nbsp;<input type="text" title="text" onkeypress="return event.charCode >= 48 && event.charCode <= 57" class="question_text" size="10" name="question' . $question->id . '">';
}