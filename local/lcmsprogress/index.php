<?php

require_once('../../config.php');

// For this type of page this is the course id.
$id = required_param('id', PARAM_INT);
$name = optional_param('name', '', PARAM_RAW);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
require_login($course);

$PAGE->set_url('/local/lcmsprogress/index.php', array('id' => $id));
$PAGE->set_pagelayout('incourse');
$PAGE->set_pagetype('mod-lcmsprogress');

// Print the header.
$strplural = get_string("modulenameplural", "lcmsprogress");
$strprogressname = get_string('lcmsprogressname', 'lcmsprogress');
$strdescription = get_string('description', 'lcmsprogress');

$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($strplural));

$context = context_course::instance($course->id);

if(is_siteadmin($USER)){
    $userid = optional_param('userid',0, PARAM_INT);
}else{
    if (has_capability('local/lcmsprogress:students', $context)) {
        $userid = $USER->id;
    }else if(has_capability('local/lcmsprogress:professor', $context)){
        $userid = optional_param('userid',0, PARAM_INT);
    }
}

//LCMS데이터
$lcmses = $DB->get_records('lcms', array('course' => $id), 'id asc', 'id,name,intro,type');

if ($userid) {
    if(has_capability('local/lcmsprogress:professor', $context) || is_siteadmin($USER)){
        $user = $DB->get_record('user',array('id'=>$userid));
        echo html_writer::start_tag('div',array('style'=>'padding:10px 0;font-weight:bold;'));
        echo html_writer::tag('span',fullname($user).' ('.$user->username.')',array('style'=>'float:left;'));
        echo html_writer::tag('button',get_string('prevpage','local_lcmsprogress'),array('class'=>'red-form','style'=>'float:right;cursor:pointer;margin-bottom:5px;','onclick'=>'location.href="?id='.$id.'"'));
        echo html_writer::end_tag('div');
    }
    /* Students Table Start */
    $table = new html_table();
    $table->head = array($strprogressname, $strdescription, get_string('studytime', 'local_lcmsprogress'), get_string('progressrate', 'local_lcmsprogress'));
    $table->align = array('left', 'left', 'center', 'center');
    $table->size = array('20%', '', '15%', '15%');
    if (!empty($lcmses)) {
        foreach ($lcmses as $lcms) {
            $track = $DB->get_record('lcms_track',array('lcms'=>$lcms->id,'userid'=>$userid));
            if ($lcms->type == 'video' || $lcms->type == 'embed' || $lcms->type == 'html2' || $lcms->type == 'media') {
                $playtime = ($track->playtime) ? gmdate("H:i:s", $track->playtime) : "00:00:00";
                if($lcms->type == 'html2' && $track->playpage) $playtime .= '<br/>('.$track->playpage.' Page)';
                $progress = ($track->progress) ? $track->progress . "%" : "0%";
            } else {
                $playtime = "-";
                $progress = ($track->progress == '100') ? "O" : "X";
            }
            $table->data[] = array($lcms->name, $lcms->intro, $playtime, $progress);
        }

        /* Students Table END */
    } else {
        $table->head = array(get_string('nocontent', 'local_lcmsprogress'));
        $table->align = array('center');
        $table->data[] = array(get_string('nocontent', 'local_lcmsprogress'));
    }
    echo html_writer::table($table);
}

if ((has_capability('local/lcmsprogress:professor', $context) || is_siteadmin($USER)) && !$userid) {
    
    /* Professor Table Start */
    $table = new html_table();
    $table->head = array();
    $table->align = array();
    $table->head[] = get_string('name');
    foreach ($lcmses as $lcms) {
        $table->head[] = $lcms->name;
        $table->align[] = 'center';
    }

    $conditions = array();
    $params = array('courseid' => $id, 'courseid2' => $id);
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

    $sql_select = "SELECT  cu.id as useid, lc.type , cu.username , cu.lastname , cu.firstname ,  lt.userid ,lt.playtime,lt.progress ";
    $sql_from = " FROM (SELECT DISTINCT u.id , u.username, u.email, u.lastname, u.firstname, e.courseid FROM {user} u 
                      JOIN {user_enrolments} ue ON ue.userid = u.id 
                      JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :courseid)) cu 
                      JOIN {lcms} lc on lc.course = :courseid2 
                      LEFT JOIN {lcms_track} lt on lt.lcms = lc.id and lt.userid = cu.id 
                       ";
    $sql_sort = "ORDER BY cu.username, lc.id asc";

    $lcms_contents = $DB->get_recordset_sql($sql_select . $sql_from . $sql_where . $sql_sort, $params);
    if (!empty($lcms_contents)) {
        foreach ($lcms_contents as $lcms_content) {
                if ($lcms_content->type == 'video' || $lcms_content->type == 'embed' || $lcms_content->type == 'html2' || $lcms->type == 'media') {
                    $progress = ($lcms_content->progress) ? $lcms_content->progress."%" : "0%";
                } else {
                    $progress = ($lcms_content->progress == '100') ? "O" : "X";
                }
            $table->data[$lcms_content->useid]['name'] = '<a href="?id='.$id.'&userid='.$lcms_content->useid.'">'.fullname($lcms_content).'<br/>('.$lcms_content->username.')</a>';
            $table->data[$lcms_content->useid][] = $progress;
        }
    } else {
        $table->head = array(get_string('nocontent', 'local_lcmsprogress'));
        $table->data[] = array(get_string('nocontent', 'local_lcmsprogress'));
    }

    echo html_writer::table($table);
    /* Professor Table END */
    $url = "excel.php?id=".$id;
    echo '<button onclick="location.href=\'' . $url . '\'" class="red-form">'.get_string('download').'</button>';
}


echo $OUTPUT->footer();
