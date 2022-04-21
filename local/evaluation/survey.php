<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/evaluation/lib.php';

$evaluation = optional_param('id', 0, PARAM_INT);

$context = context_system::instance();

require_login();

$PAGE->set_context($context);

$PAGE->set_url('/local/evaluation/course_evaluation.php');
$PAGE->set_pagelayout('standard');

$evaluation = $DB->get_record('lmsdata_evaluation', array('id' => $evaluation));
$evaluation_form = $DB->get_record('lmsdata_evaluation_forms', array('id' => $evaluation->formid));

$strplural = $evaluation_form->type==1? get_string("course_evaluation", "local_evaluation"):get_string("survey", "local_evaluation");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);

echo $OUTPUT->header();

$row[] = new tabobject('evaluation', "$CFG->wwwroot/local/evaluation/index.php?type=1", get_string('course_evaluation', 'local_evaluation'));
$row[] = new tabobject('survey', "$CFG->wwwroot/local/evaluation/index.php?type=2", get_string('survey', 'local_evaluation'));
$rows[] = $row;

$selected = ($evaluation->type == 1 ) ? "evaluation" : "survey";

if($answers = $DB->get_records('lmsdata_evaluation_submits', array('evaluation' => $evaluation->id, 'userid' => $USER->id, 'completion' => 1))){ 
    $url = 'index.php?type='.$evaluation->type;
    redirect($url,'ERROR : 이미 설문에 참여하였습니다.');
}

print_tabs($rows, $selected);
?>
<div id="evaluation_course_description"><h3><?php echo $evaluation_form->title; ?></h3></div>
<div id="evaluation_header"><?php echo nl2br($evaluation_form->contents); ?></div>
<form method="post" id="myform" action="submit.php?id=0&evaluation=<?php echo $evaluation->id; ?>"> 
<?php
$qustions_cnt = $DB->count_records('lmsdata_evaluation_questions', array('formid' => $evaluation->formid, 'category' => 0));
$questions = $DB->get_records('lmsdata_evaluation_questions', array('formid' => $evaluation->formid, 'category' => 0), 'sortorder asc', '*');
foreach ($questions as $question) {
    ?>
    <div class="question_border">
        <div class="question_header">
            <h5>
                <?php
                echo $question->title;
                if ($question->required == 1){
                    echo '(<span class="red">*</span>)';
                }
                ?>
            </h5>
            <div class="question_header_text"><h6><?php echo nl2br($question->contents); ?></h6></div>
        </div>
        <?php
        $sql = 'select ans.answers,etc.etcanswers from {lmsdata_evaluation_answers} ans '
                . 'join {lmsdata_evaluation_questions} que on que.id = ans.question '
                . 'left join {lmsdata_evaluation_answers} etc on etc.evaluation = ans.evaluation and etc.question =  ans.question and etc.userid = ans.userid and etc.etcanswers is not null '
                . 'where ans.evaluation= :evaluation and ans.userid = :userid and ans.question = :question  and ans.answers is not null';
        $ans_val = $DB->get_record_sql($sql,array('evaluation'=>$evaluation->id,'question'=>$question->id,'userid'=>$USER->id));
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
                . 'left join {lmsdata_evaluation_answers} etc on etc.evaluation = ans.evaluation and  etc.question =  ans.question and etc.userid = ans.userid and etc.etcanswers is not null '
                . 'where ans.evaluation= :evaluation and ans.userid = :userid  and ans.question = :question  and (ans.answers is not null or etc.etcanswers is not null )';
        $ans_val = $DB->get_record_sql($sql,array('evaluation'=>$evaluation->id,'question'=>$question->id,'userid'=>$USER->id));
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
    <button type="submit" class="red_btn" onclick="return survey_form_chk();" name="temp" value="0" style="margin-right: 10px;">제출</button> 
</div>
    </form>

<script type="text/javascript">
    function survey_form_chk(){        
        var chk = 0;        
        var checkbox = [];
        var checkbox_true = [];        
        
        $('#myform input[type=text][data-required=1]').each(function(index,element){
            if($(this).attr('data-use') != '0' && !$(this).val() && chk == 0){
                alert('텍스트박스 필수입력값을 모두 입력해 주세요');
                $(this).focus();
                chk = 1;
                return false;
            }
        });
        
        $('#myform textarea[data-required=1]').each(function(index,element){
            if((!$(this).val() || $(this).val() == ' ') && chk == 0){
                alert('텍스트에어리어 필수입력값을 모두 입력해 주세요');
                $(this).focus();
                chk = 1;
                return false;
            }
        });
        
        $('#myform input[type=radio][data-required=1]').each(function(index,element){
            if(!$('input[name='+$(this).attr('name')+']:checked').val() && chk == 0){
                alert('라디오버튼 필수입력값을 모두 선택해 주세요');
                chk = 1;
                return false;
            }
        });
        
        $('#myform input[type=checkbox][data-required=1]').each(function(index,element){
            if(checkbox_true.indexOf($(this).attr('data-id')) == -1 && checkbox.indexOf($(this).attr('data-id')) == -1 && chk == 0){
                var dataid = $(this).attr('data-id');
                checkbox.push(dataid);
                $('#myform input[type=checkbox][data-id='+dataid+']').each(function(index,element){
                    if($(this).is(':checked') && checkbox_true.indexOf($(this).attr('data-id')) == -1){
                        checkbox_true.push($(this).attr('data-id'));
                    }
                });   
                if(checkbox_true.indexOf(dataid) == -1){
                    chk = 1;
                    alert('체크박스 필수입력값을 모두 입력해 주세요');
                    return false;
                }
            }
        });
        
        if(chk == 1){
            return false;
        }
    }
    //var form = document.getElementById('myform'); // form has to have ID: <form id="formID">
    
//form.noValidate = true;
//form.addEventListener('submit', function(event) { // listen for form submitting
//        if (!event.target.checkValidity()) {
//            event.preventDefault(); // dismiss the default functionality
//            alert('<?php echo get_string('required_fil','local_evaluation'); ?>'); // error message 
//        }
//    }, false);
    function etc_on(questionid){
        if($('#etc_'+questionid).prop('checked')){
            $('.etc_'+questionid).show();
            $('input[name=question_etc'+questionid+']').attr('data-use', '1');
            $('input[name=question_etc'+questionid+']').prop('disabled', false);
        } else {
            $('.etc_'+questionid).hide();
            $('input[name=question_etc'+questionid+']').attr('data-use', '0');
            $('input[name=question_etc'+questionid+']').prop('disabled', true);
        }
    }
</script>
<?php
echo $OUTPUT->footer();
?>
