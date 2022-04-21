<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/coursenote/locallib.php';
require_once $CFG->dirroot.'/lib/filestorage/file_storage.php';

$fileid = required_param('fileid', PARAM_INT);
$file  = $DB->get_record('coursenote_file', array('id' => $fileid));
$forder = $DB->get_record('coursenote_forder', array('id' => $file->forderid));

$courseinfo = $DB->get_record('lmsdata_class', array('course' => $forder->course));

if($CFG->webdiskuse == 'Y'){
    $uploaddir = '/webdisk/cdata/' . $lastchar . '/C_' . $dir . '/';
} else {
    $uploaddir = '/var/www/html/local/cousenote/files/';
}

if(is_file($uploaddir.$file->filename)){
    unlink($uploaddir.$file->filename);
}

require_course_login($forder->course);

$context = context_course::instance($forder->course);

$fs = get_file_storage();

$moodlefiles = $fs->get_area_files($context->id, 'coursenote', 'coursenote', $file->id);

foreach($moodlefiles as $moodlefile){
    $moodlefile->delete();
}

 $DB->delete_records('coursenote_file',array('id'=>$file->id));


