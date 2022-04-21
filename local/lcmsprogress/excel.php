<?php

require_once('../../config.php');
require_once($CFG->libdir.'/excellib.class.php');

// For this type of page this is the course id.
$id = required_param('id', PARAM_INT);
$name = optional_param('name', '', PARAM_RAW);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
require_login($course);

$PAGE->set_url('/local/lcmsprogress/excel.php', array('id' => $id));
$PAGE->set_pagelayout('incourse');
$PAGE->set_pagetype('mod-lcmsprogress');

// Print the header.
$strplural = get_string("modulenameplural", "lcmsprogress");
$strprogressname = get_string('lcmsprogressname', 'lcmsprogress');
$strdescription = get_string('description', 'lcmsprogress');

$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($course->fullname);

$context = context_course::instance($course->id);

//교수이거나 관리자일 경우 다운로드 가능
if (has_capability('local/lcmsprogress:professor', $context) || is_siteadmin($USER)) {
    
    //LCMS데이터
    $lcms_progs = array();
    $lcmses = $DB->get_records('lcms', array('course' => $id), 'id asc', 'id,name,type');
    
    //유저데이터
    $sql = "SELECT DISTINCT u.id , u.username, u.email, u.lastname, u.firstname, e.courseid FROM {user} u 
            JOIN {user_enrolments} ue ON ue.userid = u.id 
            JOIN {enrol} e ON e.id = ue.enrolid 
            WHERE e.courseid = :courseid 
            order by u.username asc";
    $users = $DB->get_records_sql($sql, array('courseid' => $id));
    foreach($users as $user){
        foreach($lcmses as $lcms){
            if ($lcms->type == 'video' || $lcms->type == 'embed' || $lcms->type == 'html2') {
                $lcms_progs[$user->id][$lcms->id] = "0%";
            } else {
                $lcms_progs[$user->id][$lcms->id] = "X";
            }
        }
    }
    
    //진도데이터
    $conditions = array();
    $params = array('courseid' => $id);
    if ($name) {
        $conditionname[] = $DB->sql_like('cu.firstname', ':firstname', false);
        $conditionname[] = $DB->sql_like('cu.lastname', ':lastname', false);
        $conditionname[] = $DB->sql_like('cu.username', ':username', false);
        $conditionname[] = $DB->sql_like($DB->sql_fullname('cu.firstname', 'cu.lastname'), ':fullname', false);
        $conditionname[] = $DB->sql_like($DB->sql_fullname('cu.lastname', 'cu.firstname'), ':fullname1', false);
        $conditionname[] = $DB->sql_like($DB->sql_concat('cu.firstname', 'cu.lastname'), ':fullname2', false);
        $conditionname[] = $DB->sql_like($DB->sql_concat('cu.lastname', 'cu.firstname'), ':fullname3', false);
        $conditions[] = '(' . implode(' OR ', $conditionname) . ')';
        $params['firstname'] = '%' . $name . '%';
        $params['lastname'] = '%' . $name . '%';
        $params['fullname'] = '%' . $name . '%';
        $params['fullname1'] = '%' . $name . '%';
        $params['fullname2'] = '%' . $name . '%';
        $params['fullname3'] = '%' . $name . '%';
        $params['username'] = '%' . $name . '%';
    }
    $sql_where = '';
    if (!empty($conditions)) {
        $sql_where = ' WHERE ' . implode(' and ', $conditions);
    }
    
    $sql_select = "SELECT lt.id, lt.userid, lt.lcms ,lt.progress, lc.type ";
    $sql_from = " FROM {lcms_track} lt 
                  JOIN {lcms} lc on lc.id = lt.lcms 
                  WHERE lc.course = :courseid ";
    $sql_sort = "ORDER BY lt.lcms asc";

    $lcms_contents = $DB->get_records_sql($sql_select . $sql_from . $sql_where . $sql_sort, $params);
    
    foreach($lcms_contents as $lcms_content){
        if ($lcms_content->type == 'video' || $lcms_content->type == 'embed' || $lcms_content->type == 'html2') {
            $progress = ($lcms_content->progress) ? $lcms_content->progress . "%" : "0%";
        } else {
            $progress = ($lcms_content->progress == '100') ? "O" : "X";
        }
        $lcms_progs[$lcms_content->userid][$lcms_content->lcms] = $progress;
    }
    
    /* Progress Excel Start */
    //타이틀 영역
    $name= get_string('name');
    $currentdate = date('Ymd');
    $fields = array(
        $name
    );
    foreach ($lcmses as $lcms) {
        $fields[] = $lcms->name;
    }
    $filename = 'study_progress_'.$currentdate.'.xls'; 

    $workbook = new MoodleExcelWorkbook('-');
    $workbook->send($filename);

    $worksheet = array();

    $worksheet[0] = $workbook->add_worksheet('');
    $col = 0;
    foreach ($fields as $fieldname) {
        $worksheet[0]->write(0, $col, $fieldname);
        $col++;
    }
    
    //내용 영역
    $row = 1;
    foreach($users as $user){
        $col = 0;
        $worksheet[0]->write($row, $col++, fullname($user)."(".$user->username.")");
        foreach($lcmses as $lcms){
            $worksheet[0]->write($row, $col++, $lcms_progs[$user->id][$lcms->id]);
        }
        $row++;
    }

    $workbook->close();
    die;
}
