<?php
require(dirname(dirname(__FILE__)) . '/config.php');
require_once($CFG->libdir . '/phpexcel/PHPExcel.php');
require_once($CFG->libdir . '/phpexcel/PHPExcel/IOFactory.php');
require_once($CFG->libdir . '/excellib.class.php');
/* 
 * 메뉴 DB insert excel (시트 1,2,3 으로 나뉘어져 있음)
 */
$menu_excel = 'setting/menu.xlsx';
ini_set("max_execution_time", 0);
$objReader = PHPExcel_IOFactory::createReaderForFile($menu_excel);
$objReader->setReadDataOnly(true);
$objExcel = $objReader->load($menu_excel);
/* 1번시트 메뉴 */
$objExcel->setActiveSheetIndex(0);
$objWorksheet = $objExcel->getActiveSheet();
$rowIterator = $objWorksheet->getRowIterator();
foreach ($rowIterator as $row) { // 모든 행에 대해서
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
}
$maxRow = $objWorksheet->getHighestRow();
$menus = array(); // 예전 ID 맵핑을 위한 배열 생성
for ($i = 2; $i <= $maxRow; $i++) {
     $orgid = (string)$objWorksheet->getCell('A' . $i)->getValue();
     $menus[$orgid] = array();  
     $main_menu = new stdClass();
     $main_menu->depth = (string)$objWorksheet->getCell('B' . $i)->getValue();
     $main_menu->step = (string)$objWorksheet->getCell('C' . $i)->getValue();
     $main_menu->parent = (string)$objWorksheet->getCell('D' . $i)->getValue();
     $main_menu->ispopup = (string)$objWorksheet->getCell('E' . $i)->getValue();
     $main_menu->url = (string)$objWorksheet->getCell('F' . $i)->getValue();
     $main_menu->type = (string)$objWorksheet->getCell('G' . $i)->getValue();
     $main_menu->icon = (string)$objWorksheet->getCell('H' . $i)->getValue();
     $main_menu->isused = (string)$objWorksheet->getCell('J' . $i)->getValue();
     $main_menu->userid = $USER->id;
     $main_menu->required = 1;
     $main_menu->timecreated = time();
     $main_menu->timemodified = time();
     $main_menu->edituserid = $USER->id;
     $newid = $DB->insert_record('main_menu',$main_menu);
     $menus[$orgid][$newid] = $main_menu;
}
foreach($menus as $orgid => $menu){
    $newid = array_keys($menu)[0];
    $oldparent = $menu[$newid]->parent;
    $newidparentid = array_keys($menus[$oldparent])[0];   
    $change_parent = new stdClass();
    $change_parent->id = $newid;
    $change_parent->parent = $newidparentid;
    $DB->update_record('main_menu',$change_parent);
}
/* 2번시트 권한등록 */
$objExcel->setActiveSheetIndex(1);
$objWorksheet = $objExcel->getActiveSheet();
$rowIterator = $objWorksheet->getRowIterator();
$maxRow = $objWorksheet->getHighestRow();
for ($i = 2; $i <= $maxRow; $i++) {
    $new_apply = new stdClass();
    $new_apply->usergroup = (string)$objWorksheet->getCell('A' . $i)->getValue();
    $oldid = (string)$objWorksheet->getCell('B' . $i)->getValue();
    $new_apply->menuid = get_newid_byoldid($oldid,$menus);
    $new_apply->timecreated = time();
    $new_apply->timemodified = time();
    $DB->insert_record('main_menu_apply',$new_apply);
}
/* 3번시트  이름등록 */
$objExcel->setActiveSheetIndex(2);
$objWorksheet = $objExcel->getActiveSheet();
$rowIterator = $objWorksheet->getRowIterator();
$maxRow = $objWorksheet->getHighestRow();
for ($i = 2; $i <= $maxRow; $i++) {
    $new_name = new stdClass();
    $new_name->lang = (string)$objWorksheet->getCell('A' . $i)->getValue();
    $new_name->name = (string)$objWorksheet->getCell('B' . $i)->getValue();
    $oldid = (string)$objWorksheet->getCell('C' . $i)->getValue();
    $new_name->menuid = get_newid_byoldid($oldid,$menus);
    $new_name->timecreated = time();
    $new_name->timemodified = time();
    $DB->insert_record('main_menu_name',$new_name);
}
/**
 * 매칭을 위해 구 아이디를 새로 등록된 아이디로 변환
 * @param type int $oldid
 * @param type array $menus
 * @return type
 */
function get_newid_byoldid($oldid,$menus){
    $newid = array_keys($menus[$oldid])[0];
    return $newid;
}