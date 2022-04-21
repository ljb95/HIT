<?php
    require_once("../../../config.php");
    
    Header("Access-Control-Allow-Origin: *"); 
    Header("Access-Control-Allow-Methods: POST, GET, OPTIONS"); 
    Header("Access-Control-Allow-Headers:orgin, x-requested-with"); 
    
    $user_id = optional_param('userid',0,PARAM_INT);
    $maxusers = optional_param('maxusers',5,PARAM_INT);
    
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    
    $data = new stdClass();
    $data->userid = $user_id;
    $data->ip = $ipaddress;
    $data->timecreated = time();
    
    $ipcnt = $DB->count_records('lcms_ffmpeg_list');
    
    if($ipcnt >= $maxusers){
       echo 1; 
    } else {
       echo 2;
       $DB->insert_record('lcms_ffmpeg_list',$data);
    }
    
    