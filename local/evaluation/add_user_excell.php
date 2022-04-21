<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->libdir . '/phpexcel/PHPExcel.php');
require_once($CFG->libdir . '/phpexcel/PHPExcel/IOFactory.php');
require_once($CFG->libdir . '/excellib.class.php');


$filepath = 'students.xlsx';

ini_set("max_execution_time", 3000000);

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

    $user = new stdClass();
    
    $user->auth = 'manual';
    $user->confirmed = 1;
    $user->policyagreed = 0;
    $user->deleted =0;
    $user->suspended = 0;
    $user->mnethostid = 1;
    $user->emailstop = 0;
    $user->lang = 'ko';
    $user->calendartype = 'gregorian';
    $user->timezone = 99;
    $user->descriptionformat = 1;
    $user->mailformat =1;
    $user->maildisplay =2;
    $user->autosubscribe =1;
    $user->trackforums =0;
    $user->trustbitmask =0;
    
    
    $user->username = (string)$objWorksheet->getCell('A' . $i)->getValue();
    $user->password = hash_internal_user_password((string)$objWorksheet->getCell('A' . $i)->getValue());
    
    $user->email = $objWorksheet->getCell('C' . $i)->getValue();
    $user->firstname = mb_strimwidth($objWorksheet->getCell('D' . $i)->getValue(),0,2,'','utf-8');
    $user->lastname = mb_strimwidth($objWorksheet->getCell('D' . $i)->getValue(),1,15,'','utf-8');
    
    $user->timemodified = time();
    $user->timecreated = time();
    if(!$DB->get_record('user',array('username'=>$user->username))){
    $user->id = user_create_user($user, false, false);
    
    $lmsdata_user = new stdClass();
    $lmsdata_user->userid = $user->id;
    $lmsdata_user->end_name = $user->username;
    $lmsdata_user->usergroup = $objWorksheet->getCell('E' . $i)->getValue();
    $DB->insert_record('lmsdata_user',$lmsdata_user);
    
    echo "추가완료 ".$user->id."<br><br>";
    } else {
        echo "이미 추가된 사용자".$user->username;
    }
}
