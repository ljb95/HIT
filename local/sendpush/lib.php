<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
global $CFG, $DB;

function local_sendpush_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB;

    $fileareas = array('attachment');
    if (!in_array($filearea, $fileareas)) {
        return false;
    }


    $fs = get_file_storage();
    $relativepath = implode('/', $args);

    $fullpath = "/$context->id/local_sendpush/$filearea/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }


    // finally send the file
    send_stored_file($file, 0, 0, true); // download MUST be forced - security!
}

function send_push_local($user, $push_data, $msg_no) {
    global $USER,$DB;
    
    // Push 1  = 210.125.136.185
    // Push 2  = 210.125.136.186
   
    
    $ip = 'https://push.hit.ac.kr';

    //$ip = 'http://210.125.136.173';
    
    $url = $ip.'/api/rcv_register_message.ctl';
    
    
    //Required                   
    
    $post_data = array();
    $post_data["CUID"] = $userto->username; // 보건대학교 이주성선생님 CUID
    $post_data["APP_ID"] = "com.hit.portal"; // 앱 아이디
    $post_data["MESSAGE"] = $push_data->contents; // 메시지
    $post_data["SERVICECODE"] = "ALL"; 
    $post_data["PRIORITY"] = "3"; // 우선순위
    $post_data["BADGENO"] = "1";
    $post_data["RESERVEDATE"] = "";
    $post_data["SOUNDFILE"] = "alert.aif";
    $post_data["SENDERCODE"] = "admin";
    $post_data["TYPE"] = "E";
    $post_data["BADGENO"] = "1"; 

    header("Content-type: application/json; charset=utf-8");
    
    $ch = curl_init(); //curl 사용 전 초기화 필수(curl handle)

    curl_setopt($ch, CURLOPT_URL, $url); //URL 지정하기
    curl_setopt($ch, CURLOPT_POST, 1); //0이 default 값이며 POST 통신을 위해 1로 설정해야 함
    curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_data); //POST로 보낼 데이터 지정하기
    curl_setopt ($ch, CURLOPT_POSTFIELDSIZE, 0); //이 값을 0으로 해야 알아서 &post_data 크기를 측정하는듯
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 0); ////이 옵션이 0으로 지정되면 curl_exec의 결과값을 브라우저에 바로 보여줌. 이 값을 1로 하면 결과값을 return하게 되어 변수에 저장 가능(테스트 시 기본값은 1인듯?)
    $res = curl_exec ($ch);
    curl_close($ch);
    
}
