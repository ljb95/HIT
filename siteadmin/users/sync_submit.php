<?php

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once($CFG->libdir . '/phpexcel/PHPExcel.php');
require_once($CFG->libdir . '/phpexcel/PHPExcel/IOFactory.php');
require_once($CFG->dirroot . '/enrol/locallib.php');

if (!empty($_FILES['user']['name'])) {
    $filename = $_FILES['user']['name'];
    $filepath = $_FILES['user']['tmp_name'];

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
        $update = $objWorksheet->getCell('A' . $i)->getValue(); // 변경 여부
        if ($update != 1) {
            continue;
        }
        $username = $objWorksheet->getCell('B' . $i)->getValue(); // 임시번호(로그인ID)
        $password = $objWorksheet->getCell('C' . $i)->getValue();  //  비밀번호
        $name = $objWorksheet->getCell('D' . $i)->getValue();  //  이름
        $firstname = mb_substr($name, 0, 1, 'UTF-8'); // 이름의 첫글자 (성)
        $lastname = mb_substr($name, 1, null, 'UTF-8'); // 이름의 나머지 (이름)

        $phone = $objWorksheet->getCell('E' . $i)->getValue(); // 전화번호
        $email = $objWorksheet->getCell('F' . $i)->getValue(); //  이메일
        $usergroup = $objWorksheet->getCell('G' . $i)->getValue(); // 역할
        $univ = $objWorksheet->getCell('H' . $i)->getValue(); // 대학
        $major = $objWorksheet->getCell('I' . $i)->getValue(); // 전공                 
        
        $startdate = $objWorksheet->getCell('J' . $i)->getValue(); // 사용기간 시작
        $enddate = $objWorksheet->getCell('K' . $i)->getValue(); // 사용기간 종료
              
        $user = new stdClass();
        $user->auth = 'manual';
        $user->confirmed = 1;
        $user->mnethostid = 1;
        $user->lang = 'ko';
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        if (empty($email)) {
            $email = ' ';
        }
        $user->username = $username;
        $user->email = $email;
        $user->phone1 = $phone;
        $user->phone2 = $phone;
        $user->timezone = 99;
        $user->timecreated = time();
        $user->timemodified = time();
        $user->description = "Excell 유저 추가";

        $lmsdata_user = new stdClass();
        $lmsdata_user->eng_name =$name; 
        $lmsdata_user->usergroup = $usergroup;
        $lmsdata_user->b_temp = 1;
        $lmsdata_user->b_mobile = 0;
        $lmsdata_user->b_email = 0;
        $lmsdata_user->b_tel = 0;
        $lmsdata_user->univ = $univ; //  학교
        $lmsdata_user->major = $major; // 전공
        $lmsdata_user->b_univ = 0;
        $lmsdata_user->b_major = 0;
        $lmsdata_user->ehks = null;
        $lmsdata_user->edhs = null;
        $lmsdata_user->domain = null;
        $lmsdata_user->hyhg = null;
        $lmsdata_user->persg = null;
        $lmsdata_user->psosok = null;
        $lmsdata_user->sex = '';
        if (!$org_user = $DB->get_record('user', array('username' => $username))) {
            $user->password = hash_internal_user_password($password, TRUE);
            $userid = $DB->insert_record('user', $user);
            $user->id = $userid;

            /* if you want change password for first login, this sourse enable
            $change_password = array();
            $change_password['userid'] = $user->id;
            $change_password['name'] = 'auth_forcepasswordchange';
            if (!$cp = $DB->get_record('user_preferences', $change_password)) {
                $change_password['value'] = 1;
                $DB->insert_record('user_preferences', $change_password);
            }
            */ 
            $lmsdata_user->userid = $userid;
            $record = new stdClass();
            $record->contextlevel = CONTEXT_USER;
            $record->instanceid = $userid;
            $record->depth = 0;
            $record->path = null; //not known before insert

            if (!$id = $DB->get_record('context', array('contextlevel' => CONTEXT_USER, 'instanceid' => $user->id))) {
                $record->id = $DB->insert_record('context', $record);
            } else {
                $record->id = $id->id;
            }
            if (!is_null('/' . SYSCONTEXTID)) {
                $record->path = '/1/' . $record->id;
                $record->depth = substr_count($record->path, '/');
                $DB->update_record('context', $record);
            }


            $DB->insert_record('lmsdata_user', $lmsdata_user);
        } else {

            $user->id = $org_user->id;
            $user->password = hash_internal_user_password($password, TRUE);
            $userid = $DB->update_record('user', $user);
            $lmsdata_user->userid = $user->id;
            $record = new stdClass();
            $record->contextlevel = CONTEXT_USER;
            $record->instanceid = $user->id;
            $record->depth = 0;
            $record->path = null; //not known before insert
            $org_lmsdata_user = $DB->get_record('lmsdata_user', array('userid' => $user->id));
            $lmsdata_user->id = $org_lmsdata_user->id;

            $luser = $DB->update_record('lmsdata_user', $lmsdata_user);
            if (!$id = $DB->get_record('context', array('contextlevel' => CONTEXT_USER, 'instanceid' => $user->id))) {
                $record->id = $DB->insert_record('context', $record);
            } else {
                $record->id = $id->id;
            }
            if (!is_null('/' . SYSCONTEXTID)) {
                $record->path = '/' . $record->id;
                $record->depth = substr_count($record->path, '/');
                $DB->update_record('context', $record);
            }
        }
        
        $old_period = $DB->get_record('excel_user_period',array('userid'=>$user->id));
        if(!$old_period){
            $period = new stdClass();
            $period->userid = $user->id;
            $period->adminid = $USER->id;
            $period->startdate = strtotime($startdate);
            $period->enddate = strtotime($enddate);
            $period->timecreated = time();
            $DB->insert_record('excel_user_period',$period);
        } else {
            $period = new stdClass();
            $period->id = $old_period->id;
            $period->adminid = $USER->id;
            $period->startdate = strtotime($startdate);
            $period->enddate = strtotime($enddate);
            $period->timecreated = time();
            $DB->update_record('excel_user_period',$period);
        }
    }
}


redirect($CFG->wwwroot . '/siteadmin/users/info.php');
