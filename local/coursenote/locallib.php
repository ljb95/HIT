<?php
define('WEBDISKDIR','/webdisk');

/* LOCAL FUNCTIONS */

function local_coursenote_create_defaultforder(){
    global $DB,$COURSE,$USER;
    
    $forder = $DB->get_records('coursenote_forder',array('course'=>$COURSE->id));
    
    if(!$forder){
        
        $forder = new stdClass();
        $forder->one = new stdClass();
        $forder->one->name = $COURSE->fullname;
        $forder->one->course = $COURSE->id;
        $forder->one->userid = $USER->id;
        $forder->one->isopend = 1;
        $forder->one->timemodified = time();
        $newid = $DB->insert_record('coursenote_forder',$forder->one);
        $forder->one->id = $newid;
        return $forder;
    } else {
        return $forder;
    }
}





/* WEB DISK FUNCTIONS */

function webdisk_delete($webdiskroot,$filedir){
    
}


function coursenote_client_ip() { 
    $ipaddress = '';

    if (getenv('HTTP_IV_REMOTE_ADDRESS')) {
        $ipaddress = getenv('HTTP_IV_REMOTE_ADDRESS');
    }else if (getenv('HTTP_CLIENT_IP')) {
        $ipaddress = getenv('HTTP_CLIENT_IP');
    } else if(getenv('HTTP_X_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    } else if(getenv('HTTP_X_FORWARDED')) {
        $ipaddress = getenv('HTTP_X_FORWARDED');
    } else if(getenv('HTTP_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    } else if(getenv('HTTP_FORWARDED')) {
       $ipaddress = getenv('HTTP_FORWARDED');
    } else if(getenv('REMOTE_ADDR')) {
        $ipaddress = getenv('REMOTE_ADDR');
    } else {
        $ipaddress = 'UNKNOWN';
    }

    return $ipaddress;
}