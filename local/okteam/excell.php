<?php 
require('../../config.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->libdir.'/phpexcel/PHPExcel.php');
require_once($CFG->libdir.'/phpexcel/PHPExcel/IOFactory.php');

$courseid = required_param('course', PARAM_INT);

$context = context_course::instance($courseid);
$course = $DB->get_record('course', array('id'=>$courseid));


$filename = $_FILES['excell']['name'];
$filepath = $_FILES['excell']['tmp_name'];

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

$subject_arr = array();
$team_arr = array();
$message = '';
for ($i = 2; $i <= $maxRow; $i++) {
    $team = new Stdclass();
    $team->name = $objWorksheet->getCell('A' . $i)->getValue();   //팀명
    $team->idnumber   = $objWorksheet->getCell('B' . $i)->getValue(); //팀 식별번호
    $team->description   = $objWorksheet->getCell('C' . $i)->getValue(); //팀설명
    $team->enrolmentkey   = $objWorksheet->getCell('D' . $i)->getValue(); //등록키
    $team->courseid = $courseid;
    $team->username   = $objWorksheet->getCell('E' . $i)->getValue(); //사용자번호
    if(empty($team->idnumber)){
        $team->idnumber = '';
    }
    if(!empty($team->name)){
        $id = groups_create_group($team);
    } else {
        $sql = "select u.* from {role_assignments} ra "
        . "join {user} u on u.id = ra.userid and u.username = :username "
        . "join {context} c on c.contextlevel = :contextlevel and c.id = ra.contextid "
        . "where ra.contextid = :contextid ";
         $param = array('contextid' => $context->id, 'contextlevel' => CONTEXT_COURSE , 'username'=>$team->username);
         $user = $DB->get_record_sql($sql,$param);
         if(!$user && $team->username!=null && $team->username!=''){
             $message .= $i.'번 라인 '.$team->username.' 사용자는 강좌에 등록되어있지 않습니다.<br>';
         }else if($team->username==null || $team->username==''){
             
         }else{
             $DB->insert_record('groups_members',array('groupid'=>$id,'userid'=>$user->id,'timeadded'=>time(),'itemid'=>0));
         }
    }
}
 $returnurl = $CFG->wwwroot.'/local/okteam/index.php?id='.$course->id;
 redirect($returnurl,$message);