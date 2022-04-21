<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/evaluation/lib.php';

$id = optional_param('id', 0, PARAM_INT); // courseid 
$evaluation = optional_param('evaluation', 0, PARAM_INT);
$profid = optional_param('profid', 0, PARAM_INT);

$context = context_system::instance();

require_login();

$PAGE->set_context($context);

$PAGE->set_url('/local/evaluation/course_evaluation.php?id='.$id.'&evaluation='.$evaluation.'&profid='.$profid);
$PAGE->set_pagelayout('standard');

$strplural = get_string("pluginnameplural", "local_evaluation");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);

$PAGE->requires->jquery();
$PAGE->requires->js('/local/evaluation/jquery.validation.min.js');

$course = $DB->get_record('course', array('id' => $id));
$course_lmsdata = $DB->get_record('lmsdata_course', array('course' => $id));
$evaluation = $DB->get_record('lmsdata_evaluation', array('id' => $evaluation));
$evaluation_form = $DB->get_record('lmsdata_evaluation_forms', array('id' => $evaluation->formid));

if($profid){
   $name =  $DB->get_field('user','firstname',array('id'=>$profid));
   $profname = ' - '.$name;
}

echo $OUTPUT->header();

$row[] = new tabobject('evaluation', "$CFG->wwwroot/local/evaluation/index.php?type=1", get_string('course_evaluation', 'local_evaluation'));
$row[] = new tabobject('survey', "$CFG->wwwroot/local/evaluation/index.php?type=2", get_string('survey', 'local_evaluation'));
$rows[] = $row;

$selected = ($evaluation->type == 1 ) ? "evaluation" : "survey";

print_tabs($rows, $selected);
?>
<div id="evaluation_course_description"><h3><?php echo $course->fullname; if($evaluation->targets == 2)echo $profname; ?></h3><div class="subject_id"><?php echo $course_lmsdata->subject_id; ?></div></div>
<div id="evaluation_header"><?php echo nl2br($evaluation_form->contents); ?></div>
<form method="post" action="submit.php?id=<?php echo $id; ?>&evaluation=<?php echo $evaluation->id; ?>&profid=<?php echo $profid; ?>" onsubmit="return fonsubmit();">
<?php
$qustions_cnt = $DB->count_records('lmsdata_evaluation_questions', array('formid' => $evaluation->formid, 'category' => 0));
$questions = $DB->get_records('lmsdata_evaluation_questions', array('formid' => $evaluation->formid, 'category' => 0), 'sortorder asc', '*');
foreach ($questions as $question) {
    ?>
    <div class="question_border">
        <div class="question_header">
            <h5><?php
                echo $question->title;
                if ($question->required == 1)
                    echo '(<span class="red">*</span>)';
                ?></h5>
            <div class="question_header_text"><h6><?php echo nl2br($question->contents); ?></h6></div>
        </div>
        <?php
        $sql = 'select ans.answers,etc.etcanswers from {lmsdata_evaluation_answers} ans '
                . 'join {lmsdata_evaluation_questions} que on que.id = ans.question '
                . 'left join {lmsdata_evaluation_answers} etc on etc.evaluation = ans.evaluation and etc.prof_userid = ans.prof_userid and etc.question =  ans.question and etc.userid = ans.userid and etc.etcanswers is not null '
                . 'where ans.evaluation= :evaluation and ans.userid = :userid and ans.prof_userid = :prof_userid and ans.question = :question  and ans.answers is not null';
        $ans_val = $DB->get_record_sql($sql,array('evaluation'=>$evaluation->id,'question'=>$question->id,'userid'=>$USER->id,'prof_userid'=>$profid));
        switch ($question->qtype) {
            case '1':
                $answers = preg_split('/\n|\r\n?/', trim($question->answers));
                print_checkbox_form($answers, $question,$ans_val);
                break;
            case '2':
                $answers = preg_split('/\n|\r\n?/', trim($question->answers));
                print_radio_form($answers, $question,$ans_val);
                break;
            case '3':
                print_text_form($question,$ans_val);
                break;
            case '4':
                print_textarea_form($question,$ans_val);
                break;
            case '5':
                $answers = preg_split('/\n|\r\n?/', trim($question->answers));
                print_fiveselect_form($answers, $question,$ans_val);
                break;
            case '6':
                print_textint_form($question,$ans_val);
                break;
        }
        ?>
    </div> <!-- question end -->
    <?php
}
$categories_cnt = $DB->count_records('lmsdata_evaluation_category', array('formid' => $evaluation->formid));
$categories = $DB->get_records('lmsdata_evaluation_category', array('formid' => $evaluation->formid), 'sortorder asc', '*');
foreach ($categories as $category) {
    ?>
    <div class="category_border">
        <div><h3><?php echo $category->name; ?></h3></div>
        <?php
        $questions = $DB->get_records('lmsdata_evaluation_questions', array('formid' => $evaluation->formid, 'category' => $category->id), 'sortorder asc', '*');
        foreach ($questions as $question) {
            ?>
            <div class="question_border">
                <div class="question_header">
                    <h5><?php echo $question->title;
            if ($question->required == 1) echo '(<span class="red">*</span>)'; ?></h5>
                    <div class="question_header_text"><h6><?php echo nl2br($question->contents); ?></h6></div>
                </div>
                <?php
                $sql = 'select ans.answers,etc.etcanswers from {lmsdata_evaluation_answers} ans '
                . 'join {lmsdata_evaluation_questions} que on que.id = ans.question '
                . 'left join {lmsdata_evaluation_answers} etc on etc.evaluation = ans.evaluation and etc.prof_userid = ans.prof_userid and etc.question =  ans.question and etc.userid = ans.userid and etc.etcanswers is not null '
                . 'where ans.evaluation= :evaluation and ans.userid = :userid and ans.prof_userid = :prof_userid and ans.question = :question  and ans.answers is not null';
                 $ans_val = $DB->get_record_sql($sql,array('evaluation'=>$evaluation->id,'question'=>$question->id,'userid'=>$USER->id,'prof_userid'=>$profid));
                switch ($question->qtype) {
                    case '1':
                        $answers = preg_split('/\n|\r\n?/', trim($question->answers));
                        print_radio_form($answers, $question,$ans_val);
                        break;
                    case '2':
                        $answers = preg_split('/\n|\r\n?/', trim($question->answers));
                        print_checkbox_form($answers, $question,$ans_val);
                        break;
                    case '3':
                        print_text_form($question,$ans_val);
                        break;
                    case '4':
                        print_textarea_form($question,$ans_val);
                        break;
                    case '5':
                        $answers = preg_split('/\n|\r\n?/', trim($question->answers));
                        print_fiveselect_form($answers, $question,$ans_val);
                        break;
                    case '6':
                        print_textint_form($question,$ans_val);
                        break;
                }
                ?>
            </div> <!-- question end -->
            <?php
        }
        ?>
    </div>
    <?php
}
?>
<div class="table-footer-area">
    <button type="button" onclick="this.form.submit();" class="red_btn" name="temp" value="1" style="margin-right: 10px;">임시저장</button>
    <button type="submit" class="red_btn" name="temp" value="0" style="margin-right: 10px;">제출</button> 
    <input type="button" value="목록으로" onclick="location.href='index.php'">
</div>
    </form>
<script>
    $(document).ready(function(){
        $('form').validate();
    });
    function fonsubmit(){
        if($('.required:checkbox:checked').length <= 0 && $('.required:checkbox').length > 0){
            alert('하나이상의 문항을 체크하셔야합니다.');
            $('.required:checkbox').focus();
             return false;
        }
    }

    function etc_on(questionid){
        if($('#etc_'+questionid).prop('checked')){
            $('.etc_'+questionid).show();
           if($('#etc_'+questionid).prop("required") || $('#etc_'+questionid).hasClass('required')){
               $('input[name=question_etc'+questionid+']').prop('required',true);
           }
            $('input[name=question_etc'+questionid+']').prop('disabled', false);
        } else {
            $('.etc_'+questionid).hide();
            $('input[name=question_etc'+questionid+']').prop('required',false);
            $('input[name=question_etc'+questionid+']').prop('disabled', true);
        }
    }
</script>
<?php
echo $OUTPUT->footer();
?>