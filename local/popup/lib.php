<?php
function local_popup_pluginfile($course, $cm, $context, $filearea,
        $args, $forcedownload, array $options=array()){
    global $DB;
 
    $context = get_system_context();
    //require_capability('local/popup:managepopup', $context);

    if ($filearea !== 'popup') {
        return false;
    }

    /*if (!has_capability('local/popup:managepopup', $context)) {
        return false; 
    }*/ 

    $chid = (int)array_shift($args);

    if (!$popup = $DB->get_record('popup', array('id'=>$chid))) {
        return false;
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/local_popup/popup/$chid/$relativepath";
    
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }
    
    // finally send the file
    send_stored_file($file, 360, 0, $forcedownload, $options);
    
} 
