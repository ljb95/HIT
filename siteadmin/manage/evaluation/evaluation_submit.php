<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib/paging.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib.php';
require_once($CFG->libdir . '/phpexcel/PHPExcel.php');
require_once($CFG->libdir . '/phpexcel/PHPExcel/IOFactory.php');
require_once($CFG->libdir . '/excellib.class.php');

$PAGE->set_url($CFG->wwwroot.'/siteadmin/manage/evaluation/evaluation_form.php');

$mode = required_param('mode', PARAM_RAW);
$formid = optional_param('formid', '0', PARAM_INT);
$userid = required_param('userid', PARAM_INT);
$title = required_param('title', PARAM_RAW);
$type = optional_param('type', 2, PARAM_INT);
$allow_category = required_param('allow_category', PARAM_INT);
$contents = optional_param('contents','', PARAM_RAW);
$excell = optional_param('excell', '0', PARAM_INT);

if ($mode == 'add') {
    $data = new stdClass();
    $data->type = $type;
    $data->title = $title;
    $data->contents = $contents;
    $data->userid = $userid;
    $data->allow_category = $allow_category;
    $data->timecreated = time();
    $data->timemodified = time();

    $formid = $DB->insert_record('lmsdata_evaluation_forms', $data);
    $categoryid = array();
    if ($excell == 1) {
        $errmsg = "";
        if (isset($_FILES['excell_file'])) {
            if ($_FILES["excell_file"]["error"] > 0) {
                $errmsg = get_string('err1','local_lmsdata');
            } else {
                $filename = $_FILES['excell_file']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (!in_array($ext, array('xlsx', 'xls'))) {
                    $errmsg = get_string('err2','local_lmsdata',implode(', ', array('xlsx', 'xls')));
                } else {
                    $filepath = $_FILES['excell_file']['tmp_name'];

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
                        $category_no = $objWorksheet->getCell('A' . $i)->getValue();
                        $category_name = $objWorksheet->getCell('B' . $i)->getValue();

                        if (!empty($category_no) && !empty($category_name)) {
                            $data = new stdClass();
                            $data->name = trim($category_name);
                            
                            $data->formid = trim($formid);
                            $data->sortorder = trim($category_no);
                            if(!is_numeric($category_no)){
                                echo get_string('err3','local_lmsdata');
                                die();
                            }
                            $orders = $DB->get_records('lmsdata_evaluation_category', array('formid' => $formid), 'sortorder asc', 'sortorder');
                            $found = false;
                            foreach ($orders as $key => $element) {
                                if ($element->sortorder == $data->sortorder) {
                                    $found = true;
                                }
                            }
                            if ($found) {
                                echo get_string('used_order','local_lmsdata');
                                die();
                            } else {
                                $categoryid[$category_no] = $DB->insert_record('lmsdata_evaluation_category', $data);
                            }
                        }

                        $question_no = $objWorksheet->getCell('C' . $i)->getValue();
                        $question_type = $objWorksheet->getCell('D' . $i)->getValue();
                        $question_name = $objWorksheet->getCell('E' . $i)->getValue();
                        echo "-".$question_name."-";
                        $question_header = $objWorksheet->getCell('F' . $i)->getValue();
                        $question_expression = $objWorksheet->getCell('G' . $i)->getValue();
                        $question_required = $objWorksheet->getCell('H' . $i)->getValue();

                        $answer_content = $objWorksheet->getCell('I' . $i)->getValue();


                        $answers_etc = $objWorksheet->getCell('J' . $i)->getValue();

                        if (!empty($question_no) && !empty($question_type)) {
                            $data = new stdClass();
                            $data->formid = $formid;
                            if(!empty($categoryid[$category_no])){
                            $data->category = $categoryid[$category_no];
                            } else {
                                $data->category = 0;
                            }
                            $data->qtype = trim($question_type);
                            $data->expression = trim($question_expression);
                            if(!is_numeric($data->expression)){
                                echo get_string('err4','local_lmsdata');
                                die();
                            }
                            $data->required = trim($question_required);
                            if(!is_numeric($data->required)){
                                echo get_string('err5','local_lmsdata');
                                die();
                            }
                            $data->title = trim($question_name);
                            $data->contents = trim($question_header);
                            $data->answers = "";
                            $data->answers = trim($answer_content);
                            $data->etcname = trim($answers_etc);
                            if (!empty($data->etcname)) {
                                $data->etc = 1;
                            } else {
                                $data->etc = 0;
                            }
                            $data->sortorder = $question_no;
                            if(!is_numeric($data->sortorder)){
                                echo get_string('err6','local_lmsdata');
                                die();
                            }
                            $orders = $DB->get_records('lmsdata_evaluation_questions', array('formid' => $formid, 'category' => $data->category), 'sortorder asc', 'sortorder');
                            $found = false;
                            foreach ($orders as $key => $element) {
                                if ($element->sortorder == $data->sortorder) {
                                    $found = true;
                                }
                            }
                            if ($found) {
                                echo get_string('used_order','local_lmsdata');
                                die();
                            } else {
                                $new_form = $DB->insert_record('lmsdata_evaluation_questions', $data);
                            }
                        }
                    }
                }
            }
        }
    }
} else if ($mode == 'modify') {
    if ($formid == 0) {
        redirect('./evaluation_form.php', get_string('err7','local_lmsdata'), 2);
    }

    $data = new stdClass();
    $data->id = $formid;
    $data->type = $type;
    $data->title = $title;
    $data->contents = $contents;
    $data->timemodified = time();

    $new_form = $DB->update_record('lmsdata_evaluation_forms', $data);
}

if($type == 2){
    redirect('./survey_form.php');
} else {
    redirect('./evaluation_form.php');
}
