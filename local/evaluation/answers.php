<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/evaluation/lib.php';
require_once("$CFG->libdir/excellib.class.php");

$evaluation = optional_param('id', 0, PARAM_INT);
$excell = optional_param('excell', 0, PARAM_INT);

$context = context_system::instance();

require_login();

$PAGE->set_context($context);

$PAGE->set_url('/local/evaluation/course_evaluation.php');
$PAGE->set_pagelayout('standard');

$strplural = get_string("pluginnameplural", "local_evaluation");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);

$evaluation = $DB->get_record('lmsdata_evaluation', array('id' => $evaluation));
$evaluation_form = $DB->get_record('lmsdata_evaluation_forms', array('id' => $evaluation->formid));

$myusergroup = $DB->get_field('lmsdata_user','usergroup',array('userid'=>$USER->id));

if (!function_exists('stats_standard_deviation')) {

    function stats_standard_deviation(array $a, $sample = false) {
        $n = count($a);
        if ($n === 0) {
            trigger_error("The array has zero elements", E_USER_WARNING);
            return false;
        }
        if ($sample && $n === 1) {
            trigger_error("The array has only 1 element", E_USER_WARNING);
            return false;
        }
        $mean = array_sum($a) / $n;
        $carry = 0.0;
        foreach ($a as $val) {
            $d = ((double) $val) - $mean;
            $carry += $d * $d;
        };
        if ($sample) {
            --$n;
        }
        return sqrt($carry / $n);
    }

}


/* 질문가져오기 */
if ($myusergroup == 'pr' || $myusergroup == 'sa' || $myusergroup == 'ad' || is_siteadmin()) {
    list($fiveselects,$questions) = get_questions($evaluation->formid); // 오점척도와 질문을 별개로 가져옴.
} else {
    $questions = $DB->get_records('lmsdata_evaluation_questions', array('formid' => $evaluation->formid, 'category' => 0), 'sortorder asc', '*'); // 학생일경우 질문으로가져온다.
}
$history = $DB->count_records('lmsdata_evaluation_history', array('evaluation' => $evaluation->id)); // 총 응답수.

if ($excell != 1) {
    echo $OUTPUT->header();
?>
    <div id="evaluation_course_description"><h3><?php echo $evaluation_form->title; ?></h3></div>
    <div id="evaluation_header"><?php echo nl2br($evaluation_form->contents); ?> </div>
    <div class="right">
        <?php
            if ($myusergroup == 'pr' || $myusergroup == 'ad' || is_siteadmin()) {
                echo get_string('totalresponses','local_evaluation').':' . $history;
            }
        ?>
    </div>
    <?php if ($myusergroup == 'pr' || $myusergroup == 'ad' || $myusergroup == 'sa' || is_siteadmin()) {?>    
        <table class="generaltable">
            <caption class="hidden-caption"><?php echo get_string('evaluation', 'local_lmsdata')?></caption>
            <thead>
            <tr>
                <th scope="row"><?php echo get_string('question','local_evaluation') ?></th>
                <th scope="row" width="15%"><?php echo get_string('average_point','local_evaluation') ?></th>
                <th scope="row" width="15%"><?php echo get_string('standarddeviation','local_evaluation') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $answers_all = array();
            $answer_ary = array();
            foreach ($fiveselects as $fiveselect) {
                $answers = $DB->get_recordset_sql('select answers from {lmsdata_evaluation_answers} where evaluation= :evaluation and question = :question and answers is not null', array('evaluation' => $evaluation->id, 'question' => $fiveselect->id));
                foreach ($answers as $answer) {
                    $answers_all[] = (!empty($answer->answers)) ? $answer->answers : "";
                    $answer_ary[] = (!empty($answer->answers)) ? $answer->answers : "";
                }
                 $average = 0;  
                 $standard_deviation = 0;
                if(count($answer_ary) >0){  // 답변이있으면.
                    $average = number_format(array_sum($answer_ary) / count($answer_ary), 2);      // 평균
                    $standard_deviation = number_format(stats_standard_deviation($answer_ary), 2); // 표준편차
                } 
                 
                ?>
                <tr>
                    <td scope="col"><?php echo $fiveselect->title; ?></td>
                    <td scope="col"><?php echo $average; ?></td>
                    <td scope="col"><?php echo $standard_deviation; ?></td>
                </tr>
                <?php
            }
            if(count($answer_ary)>0){
                $total_average = number_format(array_sum($answer_ary) / count($answer_ary), 2);
            } else {
                $total_average = 0;
            }
            ?>
            <tr>
                <td scope="col"><?php echo get_string('average', 'local_lmsdata') ?></td>
                <td scope="col"><?php echo $total_average; ?></td>
                <td cope="col">-</td>
            </tr>
            </tbody>
        </table>
    <br/>
    <?php } ?>  <!-- 5점척도 -->
    <?php
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
            $split_questions = preg_split('/\n|\r\n?/', trim($question->answers));
            $answers_cnt = array('13' => 0, '12' => 0, '11' => 0, '10' => 0,'9' => 0, '8' => 0, '7' => 0, '6' => 0,'5' => 0, '4' => 0, '3' => 0, '2' => 0, '1' => 0);
            if( ($myusergroup == 'pr' || $myusergroup == 'ad' || $myusergroup == 'sa' || is_siteadmin() ) && $evaluation->type == 1) {
                $answers = $DB->get_recordset_sql('select answers from {lmsdata_evaluation_answers} where evaluation= :evaluation and question = :question and answers is not null', array('evaluation' => $evaluation->id, 'question' => $question->id));
                $answers_etc = $DB->get_records_sql('select id,etcanswers from {lmsdata_evaluation_answers} where evaluation= :evaluation and question = :question and etcanswers is not null', array('evaluation' => $evaluation->id, 'question' => $question->id));
                $answer_array = array();
                $answer_ary = array();
                foreach ($answers as $answer) {
                    if ($question->qtype == 5) {
                        $answer_ary[] = $answer->answers;
                        $answers_cnt[$answer->answers] += 1;
                    } else if ($question->qtype == 4) {
                        echo "<pre>" . $answer->answers . "</pre>";
                    } else if ($question->qtype == 3 || $question->qtype == 6) {
                        echo $answer->answers . "<br>";
                    } else if ($question->qtype == 2) {
                        $cnt = count(preg_split('/\n|\r\n?/', trim($question->answers)));
                        for ($i = 0; $i < $cnt; $i++) {
                            if ($split_questions[$i] == $answer->answers) {
                                $answers_cnt[$i + 1] += 1;
                            }
                        }
                    } else if ($question->qtype == 1) {
                        $cnt = count(preg_split('/\n|\r\n?/', trim($question->answers)));
                        for ($i = 0; $i < $cnt; $i++) {
                            $checkbox_vals = explode(',', $answer->answers);
                            foreach ($checkbox_vals as $checkbox_val => $val) {
                                if ($split_questions[$i] == $val) {
                                    $answers_cnt[$i + 1] += 1;
                                }
                            }
                        }
                    }
                    // 질문유형-선다형(checkbox):1,선다형(radio):2,단답형(input):3,에세이(textarea):4,5점척도(radio):5,점수(input):6)
                }
                if ($question->qtype == 5) {
                    $j = 5;
                    echo '<div style="float:left;">';
                    for ($i = 0; $i <= 4; $i++) {
                        echo $split_questions[$i] . ' : ' . $answers_cnt[$j];
                        echo "<br>";
                        $j--;
                    }
                    echo "</div>";
                    if(count($answer_ary) > 0){
                    echo '<div style="float:right; text-align:center; width:7%; margin-right:3%;">평균<br> ';
                    echo (int) array_sum($answer_ary) / count($answer_ary) . "</div>";
                    echo '<div style="float:right; text-align:center; width:7%;">표준편차<br> ';
                    echo stats_standard_deviation($answer_ary) . "</div>";
                    }
                } else if ($question->qtype == 2 || $question->qtype == 1) {
                    $cnt = count(preg_split('/\n|\r\n?/', trim($question->answers)));
                    for ($i = 0; $i < $cnt; $i++) {
                        echo $split_questions[$i] . ' : ' . $answers_cnt[$i + 1];
                        echo "<br>";
                    }
                }
                if (!empty($answers_etc)) {
                    echo "=== 기타응답 === <br>";
                    foreach ($answers_etc as $etc) {
                        if (!empty($etc->etcanswers)) {
                            echo $etc->etcanswers . "<br>";
                        }
                    }
                }
            } else if(is_siteadmin() && $evaluation->type == 2){
                 $answers = $DB->get_recordset_sql('select answers from {lmsdata_evaluation_answers} where evaluation= :evaluation and question = :question and answers is not null', array('evaluation' => $evaluation->id, 'question' => $question->id));
                $answers_etc = $DB->get_records_sql('select id,etcanswers from {lmsdata_evaluation_answers} where evaluation= :evaluation and question = :question and etcanswers is not null', array('evaluation' => $evaluation->id, 'question' => $question->id));
                $answer_array = array();
                $answer_ary = array();
                foreach ($answers as $answer) {
                    if ($question->qtype == 5) {
                        $answer_ary[] = $answer->answers;
                        $answers_cnt[$answer->answers] += 1;
                    } else if ($question->qtype == 4) {
                        echo "<pre>" . $answer->answers . "</pre>";
                    } else if ($question->qtype == 3 || $question->qtype == 6) {
                        echo $answer->answers . "<br>";
                    } else if ($question->qtype == 2) {
                        $cnt = count(preg_split('/\n|\r\n?/', trim($question->answers)));
                        for ($i = 0; $i < $cnt; $i++) {
                            if ($split_questions[$i] == $answer->answers) {
                                $answers_cnt[$i + 1] += 1;
                            }
                        }
                    } else if ($question->qtype == 1) {
                        $cnt = count(preg_split('/\n|\r\n?/', trim($question->answers)));
                        for ($i = 0; $i < $cnt; $i++) {
                            $checkbox_vals = explode(',', $answer->answers);
                            foreach ($checkbox_vals as $checkbox_val => $val) {
                                if ($split_questions[$i] == $val) {
                                    $answers_cnt[$i + 1] += 1;
                                }
                            }
                        }
                    }
                    // 질문유형-선다형(checkbox):1,선다형(radio):2,단답형(input):3,에세이(textarea):4,5점척도(radio):5,점수(input):6)
                }
                if ($question->qtype == 5) {
                    $j = 5;
                    echo '<div style="float:left;">';
                    for ($i = 0; $i <= 4; $i++) {
                        echo $split_questions[$i] . ' : ' . $answers_cnt[$j];
                        echo "<br>";
                        $j--;
                    }
                    echo "</div>";
                    if(count($answer_ary) > 0){
                    echo '<div style="float:right; text-align:center; width:7%; margin-right:3%;">평균<br> ';
                    echo (int) array_sum($answer_ary) / count($answer_ary) . "</div>";
                    echo '<div style="float:right; text-align:center; width:7%;">표준편차<br> ';
                    echo stats_standard_deviation($answer_ary) . "</div>";
                    }
                } else if ($question->qtype == 2 || $question->qtype == 1) {
                    $cnt = count(preg_split('/\n|\r\n?/', trim($question->answers)));
                    for ($i = 0; $i < $cnt; $i++) {
                        echo $split_questions[$i] . ' : ' . $answers_cnt[$i + 1];
                        echo "<br>";
                    }
                }
                if (!empty($answers_etc)) {
                    echo "=== 기타응답 === <br>";
                    foreach ($answers_etc as $etc) {
                        if (!empty($etc->etcanswers)) {
                            echo $etc->etcanswers . "<br>";
                        }
                    }
                }
            } else {
                
                $answer = $DB->get_record_sql('select answers from {lmsdata_evaluation_answers} where evaluation= :evaluation  and userid = :userid and question = :question and answers is not null', array('evaluation' => $evaluation->id, 'userid' => $USER->id, 'question' => $question->id));
                $answer_etc = $DB->get_record_sql('select etcanswers from {lmsdata_evaluation_answers} where evaluation= :evaluation and userid = :userid and question = :question and etcanswers is not null', array('evaluation' => $evaluation->id, 'userid' => $USER->id, 'question' => $question->id));
                if ($question->qtype != 5) {
                    if (!empty($answer))
                        echo $answer->answers;
                } else {
                    if (!empty($answer->answers)) {
                        $num = count($split_questions);
                        $text = array();
                        foreach($split_questions as $split_question => $value){
                            $text[$num--] = $value;
                        }
                        echo $text[$answer->answers];
                    }
                }
                if (!empty($answer_etc))
                    echo $answer_etc->etcanswers;
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
            if ($myusergroup == 'pr' || $myusergroup == 'ad' || is_siteadmin()) {
                $question_sql = "select * from {lmsdata_evaluation_questions} where formid = :formid and category = :category order by sortorder asc";
                $questions = $DB->get_records_sql($question_sql, array('formid' => $evaluation->formid, 'category' => $category->id));
            } else {
                $questions = $DB->get_records('lmsdata_evaluation_questions', array('formid' => $evaluation->formid, 'category' => $category->id), 'sortorder asc', '*');
            }
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
                    $split_questions = preg_split('/\n|\r\n?/', trim($question->answers));
                    $answers_cnt = array('13' => 0, '12' => 0, '11' => 0, '10' => 0,'9' => 0, '8' => 0, '7' => 0, '6' => 0,'5' => 0, '4' => 0, '3' => 0, '2' => 0, '1' => 0);
                    if (($myusergroup == 'pr' || $myusergroup == 'ad' || $myusergroup == 'ad' || is_siteadmin())  && $evaluation->type == 1) {
                        $answers = $DB->get_recordset_sql('select answers from {lmsdata_evaluation_answers} where evaluation= :evaluation and question = :question and answers is not null', array('evaluation' => $evaluation->id, 'question' => $question->id));
                        $answers_etc = $DB->get_records_sql('select id,etcanswers from {lmsdata_evaluation_answers} where evaluation= :evaluation and question = :question and etcanswers is not null', array('evaluation' => $evaluation->id, 'question' => $question->id));
                        $answer_array = array();
                        $answer_ary = array();
                        foreach ($answers as $answer) {
                            if ($question->qtype == 5) {
                                $answer_ary[] = $answer->answers;
                                $answers_cnt[$answer->answers] += 1;
                            } else if ($question->qtype == 4) {
                                echo "<pre>" . $answer->answers . "</pre>";
                            } else if ($question->qtype == 3 || $question->qtype == 6) {
                                echo $answer->answers . "<br>";
                            } else if ($question->qtype == 2) {
                                $cnt = count(preg_split('/\n|\r\n?/', trim($question->answers)));
                                for ($i = 0; $i < $cnt; $i++) {
                                    if ($split_questions[$i] == $answer->answers) {
                                        $answers_cnt[$i + 1] += 1;
                                    }
                                }
                            } else if ($question->qtype == 1) {
                                $cnt = count(preg_split('/\n|\r\n?/', trim($question->answers)));
                                for ($i = 0; $i < $cnt; $i++) {
                                    $checkbox_vals = explode(',', $answer->answers);
                                    foreach ($checkbox_vals as $checkbox_val => $val) {
                                        if ($split_questions[$i] == $val) {
                                            $answers_cnt[$i + 1] += 1;
                                        }
                                    }
                                }
                            }
                            // 질문유형-선다형(checkbox):1,선다형(radio):2,단답형(input):3,에세이(textarea):4,5점척도(radio):5,점수(input):6)
                        }
                        if ($question->qtype == 5) {
                            $j = 5;
                            echo '<div style="float:left;">';
                            for ($i = 0; $i <= 4; $i++) {
                                echo $split_questions[$i] . ' : ' . $answers_cnt[$j];
                                echo "<br>";
                                $j--;
                            }
                            echo "</div>";
                            if(count($answer_ary) >0){
                                echo '<div style="float:right; text-align:center; width:7%; margin-right:3%;">평균<br> ';
                                echo (int) array_sum($answer_ary) / count($answer_ary) . "</div>";
                                echo '<div style="float:right; text-align:center; width:7%;">표준편차<br> ';
                                echo stats_standard_deviation($answer_ary) . "</div>";
                            }
                        } else if ($question->qtype == 2 || $question->qtype == 1) {
                            $cnt = count(preg_split('/\n|\r\n?/', trim($question->answers)));
                            for ($i = 0; $i < $cnt; $i++) {
                                echo $split_questions[$i] . ' : ' . $answers_cnt[$i + 1];
                                echo "<br>";
                            }
                        }
                        if (!empty($answers_etc)) {
                            echo "=== ".get_string('etcanswer','local_evaluation')." === <br>";
                            foreach ($answers_etc as $etc) {
                                if (!empty($etc->etcanswers)) {
                                    echo $etc->etcanswers . "<br>";
                                }
                            }
                        }
                    } else if(is_siteadmin()  && $evaluation->type == 2){
                        $answers = $DB->get_recordset_sql('select answers from {lmsdata_evaluation_answers} where evaluation= :evaluation and question = :question and answers is not null', array('evaluation' => $evaluation->id, 'question' => $question->id));
                        $answers_etc = $DB->get_records_sql('select id,etcanswers from {lmsdata_evaluation_answers} where evaluation= :evaluation and question = :question and etcanswers is not null', array('evaluation' => $evaluation->id, 'question' => $question->id));
                        $answer_array = array();
                        $answer_ary = array();
                        foreach ($answers as $answer) {
                            if ($question->qtype == 5) {
                                $answer_ary[] = $answer->answers;
                                $answers_cnt[$answer->answers] += 1;
                            } else if ($question->qtype == 4) {
                                echo "<pre>" . $answer->answers . "</pre>";
                            } else if ($question->qtype == 3 || $question->qtype == 6) {
                                echo $answer->answers . "<br>";
                            } else if ($question->qtype == 2) {
                                $cnt = count(preg_split('/\n|\r\n?/', trim($question->answers)));
                                for ($i = 0; $i < $cnt; $i++) {
                                    if ($split_questions[$i] == $answer->answers) {
                                        $answers_cnt[$i + 1] += 1;
                                    }
                                }
                            } else if ($question->qtype == 1) {
                                $cnt = count(preg_split('/\n|\r\n?/', trim($question->answers)));
                                for ($i = 0; $i < $cnt; $i++) {
                                    $checkbox_vals = explode(',', $answer->answers);
                                    foreach ($checkbox_vals as $checkbox_val => $val) {
                                        if ($split_questions[$i] == $val) {
                                            $answers_cnt[$i + 1] += 1;
                                        }
                                    }
                                }
                            }
                            // 질문유형-선다형(checkbox):1,선다형(radio):2,단답형(input):3,에세이(textarea):4,5점척도(radio):5,점수(input):6)
                        }
                        if ($question->qtype == 5) {
                            $j = 5;
                            echo '<div style="float:left;">';
                            for ($i = 0; $i <= 4; $i++) {
                                echo $split_questions[$i] . ' : ' . $answers_cnt[$j];
                                echo "<br>";
                                $j--;
                            }
                            echo "</div>";
                            if(count($answer_ary) >0){
                                echo '<div style="float:right; text-align:center; width:7%; margin-right:3%;">'.get_string('average','local_evaluation').'<br> ';
                                echo (int) array_sum($answer_ary) / count($answer_ary) . "</div>";
                                echo '<div style="float:right; text-align:center; width:7%;">'.get_string('standarddeviation','local_evaluation').'<br> ';
                                echo stats_standard_deviation($answer_ary) . "</div>";
                            }
                        } else if ($question->qtype == 2 || $question->qtype == 1) {
                            $cnt = count(preg_split('/\n|\r\n?/', trim($question->answers)));
                            for ($i = 0; $i < $cnt; $i++) {
                                echo $split_questions[$i] . ' : ' . $answers_cnt[$i + 1];
                                echo "<br>";
                            }
                        }
                        if (!empty($answers_etc)) {
                            echo "=== ".get_string('etcanswer','local_evaluation')." === <br>";
                            foreach ($answers_etc as $etc) {
                                if (!empty($etc->etcanswers)) {
                                    echo $etc->etcanswers . "<br>";
                                }
                            }
                        }
                    } else {
                        $answer = $DB->get_record_sql('select answers from {lmsdata_evaluation_answers} where evaluation= :evaluation and userid = :userid and question = :question and answers is not null', array('evaluation' => $evaluation->id, 'userid' => $USER->id, 'question' => $question->id));
                        $answer_etc = $DB->get_record_sql('select etcanswers from {lmsdata_evaluation_answers} where evaluation= :evaluation and userid = :userid and question = :question and etcanswers is not null', array('evaluation' => $evaluation->id, 'userid' => $USER->id, 'question' => $question->id));
                        if ($question->qtype != 5) {
                            if (!empty($answer))
                                echo $answer->answers;
                        } else {
                            if (!empty($answer->answers)) {
                                echo $split_questions[$answer->answers - 1];
                            }
                        }
                        if (!empty($answer_etc))
                            echo $answer_etc->etcanswers;
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
        <input type="button" class="red_btn"  value="<?php echo get_string('back','local_evaluation'); ?>" onclick="location.href = '<?php echo $evaluation->type==1 ? 'evaluation.php':'index.php';?>';" /> 
        <?php if ($myusergroup == 'pr' || $myusergroup == 'ad' || $myusergroup == 'sa' || is_siteadmin()) { ?>
            <?php if ($evaluation->targets == 2) { ?>
                <!-- 교수자를 위한 기능이였음 -->
                <input type="button" class="red_btn" value="<?php echo get_string('graph','local_evaluation'); ?>" onclick="location.href = 'answers_chart.php?id=<?php echo $evaluation->id ?>&type=<?php echo $evaluation->type; ?>'" /> 
            <?php } ?>
            <input type="button" class="red_btn" style="margin-right: 10px;" value="<?php echo get_string('excell_print','local_evaluation'); ?>" onclick="location.href = '?id=<?php echo $evaluation->id; ?>&excell=1'" /> 
        <?php } ?>
    </div>
    <?php
    echo $OUTPUT->footer();
} else {
    /******************************************************************        엑셀 영역       **********************************************************************/
    $fields = array(
        get_string('name','local_evaluation')
    );
    $questions = $DB->get_records('lmsdata_evaluation_questions', array('formid' => $evaluation->formid), 'sortorder asc', '*');
    foreach ($questions as $question) {
        $fields[] = $question->title;
    }
    $filename = $evaluation_form->title . '.xls';

    $workbook = new MoodleExcelWorkbook('-');
    $workbook->send($filename);

    $worksheet = array();

    $worksheet[0] = $workbook->add_worksheet('');
    $col = 0;
    foreach ($fields as $fieldname) {
        $worksheet[0]->write(0, $col, $fieldname);
        $col++;
    }

    $row = 1;

    $histories = $DB->get_records('lmsdata_evaluation_history', array('evaluation' => $evaluation->id), 'userid asc');

    foreach ($histories as $history) {
        $user = $DB->get_record('user', array('id' => $history->userid));
        $col = 0;
        $sql = 'select ans.answers,etc.etcanswers from {lmsdata_evaluation_answers} ans '
                . 'join {lmsdata_evaluation_questions} que on que.id = ans.question '
                . 'left join {lmsdata_evaluation_answers} etc on etc.evaluation = ans.evaluation  and etc.question =  ans.question and etc.userid = ans.userid and etc.etcanswers is not null '
                . 'where ans.evaluation= :evaluation and ans.userid = :userid  and ans.answers is not null order by que.category asc, que.sortorder asc';
        $param = array('evaluation' => $evaluation->id, 'userid' => $history->userid);
        $answers_in_excell = $DB->get_records_sql($sql, $param);
        $worksheet[0]->write($row, $col++, fullname($user) . "(" . $user->username . ")");
        foreach ($answers_in_excell as $answer) {
            if (!empty($answer->etcanswers)) {
                $worksheet[0]->write($row, $col++, $answer->answers . $answer->etcanswers);
            } else {
                $worksheet[0]->write($row, $col++, $answer->answers);
            }
        }
        $row++;
    }
    $workbook->close();
    die;
}
?>
