<?php
    require(dirname(dirname(dirname(__FILE__))) . '/config.php');
    require_once dirname(dirname(__FILE__)) . '/lib/contents_lib.php';
    
    $modifytime = time();
            
    $data = new stdClass();
    foreach($_REQUEST as $key => $val){
        $data->$key = $val;
    }
    $data->userid = $USER->id;
    $data->timemodified = $modifytime;
    
    $id = $DB->update_record('jinoboard',$data);
    
        
        redirect('./list.php');