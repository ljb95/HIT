<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/coursenote/locallib.php';
require_once $CFG->dirroot.'/lib/filestorage/file_storage.php';

$fileid = required_param('fileid', PARAM_INT);

$file  = $DB->get_record('coursenote_file', array('id' => $fileid));

$forder = $DB->get_record('coursenote_forder', array('id' => $file->forderid));

require_course_login($forder->course);

$context = context_course::instance($forder->course);

$fs = get_file_storage();

$moodlefile = $fs->get_file($context->id, 'coursenote', 'coursenote', $file->id,'/',$file->filename);

$history = new stdClass();
$history->fileid = $fileid;
$history->userid = $USER->id;
$history->ip = coursenote_client_ip();
$history->timedownload = time();
$DB->insert_record('coursenote_history',$history);

send_file($moodlefile,$file->filename,null,0,false,true);