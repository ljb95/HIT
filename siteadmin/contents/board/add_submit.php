<?php
    require(dirname(dirname(dirname(__FILE__))) . '/config.php');
    require_once dirname(dirname(__FILE__)) . '/lib/contents_lib.php';
    
    $maketime = time();
            
    $data = new stdClass();
    foreach($_REQUEST as $key => $val){
        $data->$key = $val;
    }
    $data->required = 2;
    $data->userid = $USER->id;
    $data->timemodified = $maketime;
    
    $id = $DB->insert_record('jinoboard',$data);
    
    $allows = array(
            array_combine(
                    array('board','allowsecret', 'allowrole', 'allowview', 'allowdetail', 'allowwrite', 'allowreply', 'allowcomment', 'allowmodify', 'allowdelete', 'allowupload', 'timemodified'), 
                    array($id    , 'false'  ,'pr', 'true', 'true'  , 'true' , 'true' , 'true'   , 'true'  , 'true'  , 'true'  , $maketime)),
            array_combine(
                    array('board','allowsecret', 'allowrole', 'allowview', 'allowdetail', 'allowwrite', 'allowreply', 'allowcomment', 'allowmodify', 'allowdelete', 'allowupload', 'timemodified'), 
                    array($id, 'false','ad', 'true', 'true', 'true', 'true', 'true', 'true', 'true', 'true', $maketime)),
            array_combine(
                    array('board','allowsecret', 'allowrole', 'allowview', 'allowdetail', 'allowwrite', 'allowreply', 'allowcomment', 'allowmodify', 'allowdelete', 'allowupload', 'timemodified'), 
                    array($id, 'false','rs', 'true', 'true', 'false', 'false', 'true', 'false', 'false', 'false', $maketime)),
            array_combine(
                    array('board','allowsecret', 'allowrole', 'allowview', 'allowdetail', 'allowwrite', 'allowreply', 'allowcomment', 'allowmodify', 'allowdelete', 'allowupload', 'timemodified'), 
                    array($id, 'false','gu', 'true', 'true', 'false', 'false', 'false', 'false', 'false', 'false', $maketime)),
            array_combine(
                    array('board','allowsecret', 'allowrole', 'allowview', 'allowdetail', 'allowwrite', 'allowreply', 'allowcomment', 'allowmodify', 'allowdelete', 'allowupload', 'timemodified'), 
                    array($id, 'true','sa', 'true', 'true', 'true', 'true', 'true', 'true', 'true', 'true', $maketime)),
            array_combine(
                        array('board','allowsecret', 'allowrole', 'allowview', 'allowdetail', 'allowwrite', 'allowreply', 'allowcomment', 'allowmodify', 'allowdelete', 'allowupload', 'timemodified'), 
                        array($id, 'true','ma', 'true', 'true', 'true', 'true', 'true', 'true', 'true', 'true', $maketime)),
        );
        foreach ($allows as $allow) {
             $allowid = $DB->insert_record('jinoboard_allowd', $allow);
        }
        
        redirect('./list.php');