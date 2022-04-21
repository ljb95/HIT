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

for ($i = 2; $i <= $maxRow; $i++) {
    $categoryname = trim($objWorksheet->getCell('A' . $i)->getValue());   //퀴즈 카테고리
    if ($categoryname) {
        $data = new stdClass();
        $data->name = 'Q' . trim($objWorksheet->getCell('B' . $i)->getValue()); //퀴즈번호
        $data->questiontext = trim($objWorksheet->getCell('E' . $i)->getValue()); //문제
        $data->generalfeedback = trim($objWorksheet->getCell('F' . $i)->getValue()); //문제해설
        $answer1 = trim($objWorksheet->getCell('G' . $i)->getValue()); //보기1
        $answer2 = trim($objWorksheet->getCell('H' . $i)->getValue()); //보기2
        $answer3 = trim($objWorksheet->getCell('I' . $i)->getValue()); //보기3
        $answer4 = trim($objWorksheet->getCell('J' . $i)->getValue()); //보기4
        $correct = trim($objWorksheet->getCell('K' . $i)->getValue()); //정답

        if ($category = $DB->get_field('question_categories', 'id', array('name' => $categoryname, 'contextid' => 1))) {
            $data->category = $category;
        } else {
            $category = new stdClass();
            $category->name = $categoryname;
            $category->contextid = 1;
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
            for ($j = 1; $j <= 4; $j++) {
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
            $option->correctfeedback = $data->generalfeedback;
            $option->correctfeedbackformat = 1;
            $option->partiallycorrectfeedback = $data->generalfeedback;
            $option->partiallycorrectfeedbackformat = 1;
            $option->incorrectfeedback = $data->generalfeedback;
            $option->incorrectfeedbackformat = 1;
            $option->answernumbering = '123';
            $option->shownumcorrect = 1;
            $data->newoptionid = $DB->insert_record('qtype_multichoice_options', $option);
            echo '<p>' . $i . '행 : [' . $categoryname . '] ' . $data->name . ' 옵션 정보를 생성하였습니다.</p>';
        }else {
            echo '<p>' . $i . '행 : [' . $categoryname . '] 퀴즈정보(' . $data->name . ')가 이미 존재합니다.</p>';
        }
    }else{
        echo '<p>' . $i . '행 : 코드가 존재하지 않아 생성하지 못했습니다. </p>';        
    } 
   // break;
}

echo 'compete!!';
echo '<script type="text/javascript">window.scrollTo(0, document.body.scrollHeight);</script>';

echo $OUTPUT->footer();
