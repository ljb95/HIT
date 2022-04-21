<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once('lib.php');
/* Lim */
// 타입
$type = required_param('type', PARAM_RAW);
//기타 데이타 
$userid = required_param('userid', PARAM_RAW);
$cmid = optional_param('cmid',0, PARAM_INT);
$contentid = optional_param('contentid',0, PARAM_INT);

$data = new stdClass();
if($cmid){
    if (!$cm = get_coursemodule_from_id('lcms', $cmid)) {
        $data->result->code = 400;
        $data->result->message = '"삭제된 활동 모듈"';
    } else {
        $lcms = $DB->get_record('lcms', array('id' => $cm->instance));
    }
    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        $data->result->code = 400;
        $data->result->message = '"삭제된 강좌"';
    }
    
     if (!$contents = $DB->get_record('lcms_contents', array('id' => $lcms->contents))) {
             $data->result->code = 400;
             $data->result->message = '"삭제된 콘텐츠"';
        }
     if (!$file = $DB->get_record('lcms_contents_file', array('con_seq' => $lcms->contents))) {
             $data->result->code = 400;
             $data->result->message = '"삭제된 콘텐츠 파일"';
    }
    
} else if($contentid){
    if ($contentid) {
        if (!$contents = $DB->get_record('lcms_contents', array('id' => $contentid))) {
             $data->result->code = 400;
             $data->result->message = '"삭제된 콘텐츠"';
        }
        if (!$file = $DB->get_record('lcms_contents_file', array('con_seq' => $contentid))) {
             $data->result->code = 400;
             $data->result->message = '"삭제된 콘텐츠 파일"';
        }
    }
    
} else {
     $data->result->code = 100;
     $data->result->message = '"Param Error[cmid or contentid is required]"';
}

$data->content= new stdClass();
$data->result = new stdClass();
if ($file) {
    
    $data->result->code = 200;
    $data->result->message = 'success';
    
    $data->content->tracking_yn = 'Y';
    $data->content->tracking_url = rawurlencode($CFG->wwwroot . '/local/repository/app_progress.php?content_id='.$lcms->id);
//     $data->content->tracking_url2 = rawurldecode($data->content->tracking_url);
    $data->content->tracking_interval = 60;
   $data->content->content_id = $lcms->id;
    $data->content->serviceid = 'HIT';
    $data->content->servicename = '대전보건대';

    $file->filename = str_replace('.mp4', '_hd.mp4', $file->filename);

    $data->content->vodurl = rawurlencode(str_replace('https://','http://',$CFG->vodserver) . '/uploads/' . $file->filepath . '/') . str_replace(' ', '', $file->filename);
    $data->content->vodfilename = $file->filename;

    $data->content->vodtitle = $contents->con_name;
    $data->content->vodtitlesub = $contents->con_name;
    $data->content->type = 'play';
    $data->content->user_id = $userid;
    $data->content->caption_url = null;
    $data->content->continue_time = 0;
    $data->content->course_id = $course->id;

} else {
     $data->result->code = 400;
     $data->result->message = '"콘텐츠 오류"';
}  
@header('Content-type: application/json; charset=utf-8');
echo json_encode($data);