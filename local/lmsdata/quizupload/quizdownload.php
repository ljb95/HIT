<?php
require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->libdir.'/excellib.class.php');

$idnumber = required_param('idnumber', PARAM_RAW);

$sql = "SELECT qa.id, qu.name, 
        DENSE_RANK()OVER(PARTITION BY qu.name ORDER BY qa.id ASC) as answernumber, '객관식' AS qtype,
        qu.questiontext, qu.generalfeedback, qa.answer, qa.fraction
        FROM {course} co
        JOIN {context} ctx ON ctx.instanceid = co.id and ctx.contextlevel = 50
        JOIN {question_categories} qc ON qc.contextid = ctx.id and qc.name = co.shortname
        JOIN {question} qu ON qu.category = qc.id
        JOIN {question_answers} qa ON qa.question = qu.id
        JOIN {qtype_multichoice_options} qmo ON qmo.questionid = qu.id ";

$where = '  WHERE co.shortname = :idnumber ';
$orderby = " ORDER BY qa.id ASC ";
$params = array('idnumber' => $idnumber);


$questions = $DB->get_records_sql($sql.$where.$orderby, $params);

$fields = array(
    '컨텐츠코드',
    '퀴즈 코드',
    '문제번호',
    '문제분류',
    '문제',
    '문제해설',
    '보기1',
    '보기2',
    '보기3',
    '보기4',
    '정답'
);

$date = date('Y-m-d', time());
$filename = $idnumber.'.xls';

$workbook = new MoodleExcelWorkbook('-');
$workbook->send($filename);

$worksheet = array();

$worksheet[0] = $workbook->add_worksheet('');
$col = 0;
foreach ($fields as $fieldname) {
    $worksheet[0]->write(0, $col, $fieldname);
    $col++;
}

$row = 0;
$qname = null;
$right = 0;
foreach($questions as $question) {
    
    $answer = preg_replace('/<\/{0,1}p>/', '', $question->answer);
    
    if($qname == null || $qname != $question->name) {
        $row++;
        $col = 0;
        $qname = $question->name;
        $quizcode = substr($question->name, 1);
        $quiznumber = substr($question->name, strlen($question->name) - 1);
        
        $worksheet[0]->write($row, $col++, $idnumber);
        $worksheet[0]->write($row, $col++, $quizcode);
        $worksheet[0]->write($row, $col++, $quiznumber);
        $worksheet[0]->write($row, $col++, $question->qtype);
        $worksheet[0]->write($row, $col++, $question->questiontext);
        $worksheet[0]->write($row, $col++, $question->generalfeedback);
        $worksheet[0]->write($row, $col++, $answer);
        
    }else{
        $worksheet[0]->write($row, $col++, $answer);
    }
        
    if(!empty($question->fraction)) {
        $worksheet[0]->write($row, 10, $question->answernumber);
    }
}
$workbook->close();
die;
