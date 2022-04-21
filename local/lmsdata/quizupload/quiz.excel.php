<?php

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->libdir . '/phpexcel/PHPExcel.php');
require_once($CFG->libdir . '/phpexcel/PHPExcel/IOFactory.php');

if (!is_siteadmin($USER)) {
    redirect($CFG->wwwroot);
}

echo $OUTPUT->header();

$filename = $_FILES['quizupload']['name'];
$filepath = $_FILES['quizupload']['tmp_name'];

$objReader = PHPExcel_IOFactory::createReaderForFile($filepath);
$objReader->setReadDataOnly(true);
$objExcel = $objReader->load($filepath);

$objExcel->setActiveSheetIndex(0);
$objWorksheet = $objExcel->getActiveSheet();
$rowIterator = $objWorksheet->getRowIterator();

foreach ($rowIterator as $row) { // 모든 행에 대해서
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
}

$maxRow = $objWorksheet->getHighestRow();

$exist_course = new Stdclass();

for ($i = 2; $i <= $maxRow; $i++) {
    $categorycode = trim($objWorksheet->getCell('A' . $i)->getValue());   //퀴즈 카테고리
    
    $categoryname_arr = explode('/', $categorycode);
    
    foreach($categoryname_arr as $categoryname) {
        $categoryname = trim($categoryname);
        if(empty($exist_course) || ($exist_course->coursecd != $categoryname)) {
            $exist_sql = 'SELECT co.id, co.shortname, cx.id as contextid
                          FROM {course} co 
                          JOIN {lmsdata_class} lco on lco.course = co.id 
                          JOIN {context} cx ON cx.instanceid = co.id and cx.contextlevel = :contextlevel
                          WHERE lco.kor_lec_name = :shortname ';
            $exist_course = $DB->get_record_sql($exist_sql, array('shortname'=>$categoryname, 'contextlevel' => 50));
        }
         
        if (!empty($exist_course)) { 
            $data = new stdClass();
            $data->name = 'Q' . trim($objWorksheet->getCell('C' . $i)->getValue()); //퀴즈번호
            $data->questiontext = trim($objWorksheet->getCell('E' . $i)->getValue()); //문제
            $data->generalfeedback = trim($objWorksheet->getCell('F' . $i)->getValue()); //문제해설
            $answer1 = trim($objWorksheet->getCell('G' . $i)->getValue()); //보기1
            $answer2 = trim($objWorksheet->getCell('H' . $i)->getValue()); //보기2
            $answer3 = trim($objWorksheet->getCell('I' . $i)->getValue()); //보기3
            $answer4 = trim($objWorksheet->getCell('J' . $i)->getValue()); //보기4
            $answer5 = trim($objWorksheet->getCell('K' . $i)->getValue()); //보기5
            $answer6 = trim($objWorksheet->getCell('L' . $i)->getValue()); //보기6
            $correct = trim($objWorksheet->getCell('M' . $i)->getValue()); //정답

            if ($category = $DB->get_field_sql('SELECT id FROM {question_categories} WHERE contextid = :contextid and name = :name ', array('contextid' => $exist_course->contextid, 'name' => $categoryname))) {
                $data->category = $category;
            } else {
                $category = new stdClass();
                $category->name = $categoryname;
                $category->contextid = $exist_course->contextid;
                $category->info = ' ';
                $category->infoformat = 0;
                $category->stamp = make_unique_id_code();
                $category->parent = 0;
                $category->sortorder = 999;
                $data->category = $DB->insert_record('question_categories', $category);
                echo '<p>' . $i . '행 : 범주(' . $categoryname . ')을 생성하였습니다.</p>';
            }

            if (!$question = $DB->get_record('question', array('name' => $data->name, 'category' => $data->category))) {

                $data->parent = 0;
                $data->questiontextformat = 1;
                $data->generalfeedbackformat = 1;
                $data->defaultmark = 1;
                $data->penalty = 0.3333333;
                $data->qtype = 'multichoice';
                $data->length = 1;
                $data->stamp = make_unique_id_code();
                $data->version = make_unique_id_code();
                $data->hidden = 0;
                $data->timecreated = time();
                $data->timemodified = time();
                $data->createdby = $USER->id;
                $data->modifiedby = $USER->id;
                $data->newid = $DB->insert_record('question', $data);
                echo '<p>' . $i . '행 : [' . $categoryname . '] 퀴즈정보(' . $data->name . ')을 생성하였습니다.</p>';

                //보기 저장
                for ($j = 1; $j <= 6; $j++) {
                    if(!empty(${'answer' . $j})){
                    $answer = new stdClass();
                    $answer->question = $data->newid;
                    $answer->answer = '<p>' . ${'answer' . $j} . '</p>';
                    $answer->answerformat = 1;
                    $answer->fraction = ($correct == $j) ? 1 : 0;
                    $answer->feedback = ' ';
                    $answer->feedbackformat = 1;
                    $data->newanswerid = $DB->insert_record('question_answers', $answer);
                    echo '<p>' . $i . '행 : [' . $categoryname . '] ' . $data->name . ' 보기문항(' . ${'answer' . $j} . ')을 생성하였습니다.</p>';
                    }
                }

                //multichoice 옵션 등록
                $option = new stdClass();
                $option->questionid = $data->newid;
                $option->layout = 0;
                $option->single = 1;
                $option->shuffleanswers = 0;
                $option->correctfeedback = '';
                $option->correctfeedbackformat = 1;
                $option->partiallycorrectfeedback = '';
                $option->partiallycorrectfeedbackformat = 1;
                $option->incorrectfeedback = '';
                $option->incorrectfeedbackformat = 1;
                $option->answernumbering = '123';
                $option->shownumcorrect = 1;
                $data->newoptionid = $DB->insert_record('qtype_multichoice_options', $option);
                echo '<p>' . $i . '행 : [' . $categoryname . '] ' . $data->name . ' 옵션 정보를 생성하였습니다.</p>';

            }else {
                //이미 퀴즈가 있을 경우 업데이트 처리
                echo '<p>' . $i . '행 : [' . $categoryname . '] 퀴즈정보(' . $data->name . ')가 이미 존재합니다.</p>';

                $question->name = $data->name;
                $question->questiontext = $data->questiontext;
                $question->generalfeedback = $data->generalfeedback;
                $question->timemodified = time();
                $question->modifiedby = $USER->id;
                $DB->update_record('question', $question);
                echo '<p>' . $i . '행 : [' . $categoryname . '] 퀴즈정보(' . $data->name . ')을 업데이트하였습니다.</p>';

                //보기 저장
                $DB->delete_records('question_answers', array('question'=>$question->id));
                for ($j = 1; $j <= 6; $j++) {
                    if(!empty(${'answer' . $j})){
                    $answer = new stdClass();
                    $answer->question = $question->id;
                    $answer->answer = '<p>' . ${'answer' . $j} . '</p>';
                    $answer->answerformat = 1;
                    $answer->fraction = ($correct == $j) ? 1 : 0;
                    $answer->feedback = ' ';
                    $answer->feedbackformat = 1;
                    $data->newanswerid = $DB->insert_record('question_answers', $answer);
                    echo '<p>' . $i . '행 : [' . $categoryname . '] ' . $data->name . ' 보기문항(' . ${'answer' . $j} . ')을 생성하였습니다.</p>';
                    }
                }

            }
        }else{
            echo '<p>' . $i . '행 : 코드가 존재하지 않아 생성하지 못했습니다. </p>';        
        } 
    }
}

echo 'complete!!';
echo '<script type="text/javascript">window.scrollTo(0, document.body.scrollHeight);</script>';

echo $OUTPUT->footer();
